<script setup>
import Input from "@/Components/ui/Input.vue";
import Button from "@/Components/ui/Button.vue";
import { ref } from "vue";

const emit = defineEmits(["submit"]);
const title = ref("");
function submit() {
  if (!title.value?.trim()) return;
  emit("submit", { title: title.value.trim() });
  title.value = "";
}
</script>

<template>
  <form @submit.prevent="submit" class="flex items-center gap-2">
    <Input v-model="title" placeholder="Add a task… (press Enter)" />
    <Button type="submit">Add</Button>
  </form>
</template>
