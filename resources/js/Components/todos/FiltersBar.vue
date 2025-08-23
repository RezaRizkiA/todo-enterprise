<script setup>
import Input from "@/Components/ui/Input.vue";
import Button from "@/Components/ui/Button.vue";
import { ref, watch } from "vue";

const props = defineProps({
  initial: { type: Object, default: () => ({ q: "", status: "all" }) },
});
const emit = defineEmits(["change"]);

const q = ref(props.initial.q || "");
const status = ref(props.initial.status || "all"); // all | active | done

watch([q, status], () => {
  emit("change", { q: q.value, status: status.value });
});
</script>

<template>
  <div class="flex flex-wrap items-center gap-2">
    <div class="w-64"><Input v-model="q" placeholder="Search…" /></div>
    <div class="flex gap-1">
      <Button
        size="sm"
        :variant="status === 'all' ? 'primary' : 'ghost'"
        @click="status = 'all'"
        >All</Button
      >
      <Button
        size="sm"
        :variant="status === 'active' ? 'primary' : 'ghost'"
        @click="status = 'active'"
        >Active</Button
      >
      <Button
        size="sm"
        :variant="status === 'done' ? 'primary' : 'ghost'"
        @click="status = 'done'"
        >Done</Button
      >
    </div>
  </div>
</template>
