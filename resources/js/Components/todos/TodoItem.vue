<script setup>
import Checkbox from "@/Components/ui/Checkbox.vue";
import Button from "@/Components/ui/Button.vue";
import { ref } from "vue";

const props = defineProps({
  todo: { type: Object, required: true },
});
const emit = defineEmits(["toggle", "edit", "remove"]);

const editing = ref(false);
const draft = ref(props.todo.title);
function save() {
  emit("edit", { id: props.todo.id, title: draft.value });
  editing.value = false;
}
</script>

<template>
  <div
    class="group flex items-start gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"
  >
    <Checkbox
      :model-value="props.todo.done"
      @update:modelValue="(v) => emit('toggle', { id: props.todo.id, done: v })"
    />
    <div class="flex-1">
      <div v-if="!editing" class="flex items-start justify-between gap-3">
        <div
          :class="[
            'text-sm',
            props.todo.done ? 'line-through text-gray-400' : 'text-gray-800',
          ]"
        >
          {{ props.todo.title }}
        </div>
        <div class="opacity-0 transition group-hover:opacity-100 shrink-0 flex gap-2">
          <Button
            variant="ghost"
            size="sm"
            @click="
              () => {
                editing = true;
                draft = props.todo.title;
              }
            "
            >Edit</Button
          >
          <Button variant="danger" size="sm" @click="emit('remove', props.todo.id)"
            >Delete</Button
          >
        </div>
      </div>

      <div v-else class="flex items-center gap-2">
        <input
          v-model="draft"
          class="h-9 flex-1 rounded-xl border border-gray-300 px-3 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
        />
        <Button size="sm" @click="save">Save</Button>
        <Button size="sm" variant="ghost" @click="editing = false">Cancel</Button>
      </div>

      <div class="mt-1 text-xs text-gray-500">
        <span class="mr-2">Priority: {{ props.todo.priority ?? "normal" }}</span>
        <span v-if="props.todo.due_date">• Due: {{ props.todo.due_date }}</span>
      </div>
    </div>
  </div>
</template>
