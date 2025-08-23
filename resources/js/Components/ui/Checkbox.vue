<script setup>
const props = defineProps({ modelValue: Boolean });
const emit = defineEmits(["update:modelValue"]);
</script>

<template>
  <label class="inline-flex items-center gap-2 select-none cursor-pointer">
    <input
      type="checkbox"
      class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
      :checked="modelValue"
      @change="emit('update:modelValue', $event.target.checked)"
    />
    <slot />
  </label>
</template>
