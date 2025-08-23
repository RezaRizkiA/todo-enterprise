<script setup>
import { onMounted, onUnmounted } from "vue";
import { useToast } from "@/composables/useToast";

const { toasts, remove } = useToast();
let timer;
onMounted(() => {
  timer = setInterval(() => {
    const now = Date.now();
    toasts.value = toasts.value.filter((t) => {
      if (!t.expiresAt || t.expiresAt > now) return true;
      return false;
    });
  }, 1000);
});
onUnmounted(() => clearInterval(timer));
</script>

<template>
  <div class="fixed bottom-4 right-4 z-[60] space-y-2">
    <div
      v-for="t in toasts"
      :key="t.id"
      class="rounded-xl bg-gray-900/90 px-4 py-3 text-sm text-white shadow-lg backdrop-blur"
    >
      <div class="flex items-start gap-3">
        <div class="mt-0.5">{{ t.message }}</div>
        <button class="ml-2 opacity-80 hover:opacity-100" @click="remove(t.id)">✕</button>
      </div>
    </div>
  </div>
</template>
