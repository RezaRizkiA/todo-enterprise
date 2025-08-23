<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue"; // Breeze default
import { Head, usePage } from "@inertiajs/vue3";
import AddTodoForm from "@/Components/todos/AddTodoForm.vue";
import TodoItem from "@/Components/todos/TodoItem.vue";
import FiltersBar from "@/Components/todos/FiltersBar.vue";
import Toast from "@/Components/ui/Toast.vue";
import { useTodos } from "@/composables/useTodos";
import { onMounted } from "vue";
import { useToast } from "@/composables/useToast";

const props = defineProps({
  todos: Object, // paginated collection from server
  filters: Object, // { q, status }
  routes: Object, // { index, store, update, toggle, destroy }
});

const { create, toggle, update, destroy, filter } = useTodos(props.routes);

// flash -> toast
const { props: pageProps } = usePage();
const { push } = useToast();
onMounted(() => {
  if (pageProps.value?.flash?.success) push(pageProps.value.flash.success);
});

// keyboard: Ctrl/Cmd+K fokus add
function focusQuickAdd() {
  const el = document.querySelector('input[placeholder="Add a task… (press Enter)"]');
  if (el) el.focus();
}
window.addEventListener("keydown", (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "k") {
    e.preventDefault();
    focusQuickAdd();
  }
});
</script>

<template>
  <Head title="Todos" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Todos</h2>
    </template>

    <div class="mx-auto max-w-4xl space-y-6 p-6">
      <!-- Quick Add -->
      <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
          <AddTodoForm @submit="(payload) => create(payload)" />
          <div class="text-xs text-gray-500">
            Shortcut: <kbd class="rounded bg-gray-100 px-1">Ctrl/Cmd + K</kbd>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <FiltersBar :initial="filters" @change="(params) => filter(params)" />
      </div>

      <!-- List -->
      <div class="space-y-3">
        <div
          v-if="!todos?.data?.length"
          class="rounded-2xl border border-dashed border-gray-300 p-10 text-center text-gray-500"
        >
          No tasks yet. Add your first one!
        </div>
        <TodoItem
          v-for="t in todos.data"
          :key="t.id"
          :todo="t"
          @toggle="({ id, done }) => toggle(id, done)"
          @edit="({ id, title }) => update(id, { title })"
          @remove="(id) => destroy(id)"
        />
      </div>

      <!-- Pagination (simple) -->
      <div v-if="todos?.links?.length" class="flex justify-center gap-2 pt-4">
        <template v-for="link in todos.links" :key="link.url ?? link.label">
          <inertia-link
            v-if="link.url"
            :href="link.url"
            class="rounded-lg px-3 py-1 text-sm"
            :class="
              link.active
                ? 'bg-primary-600 text-white'
                : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50'
            "
          >
            <span v-html="link.label" />
          </inertia-link>
          <span
            v-else
            class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-1 text-sm text-gray-400"
            v-html="link.label"
          />
        </template>
      </div>
    </div>

    <Toast />
  </AuthenticatedLayout>
</template>
