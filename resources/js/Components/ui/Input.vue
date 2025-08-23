<script setup>
const props = defineProps({
  modelValue: [String, Number],
  placeholder: String,
  type: { type: String, default: "text" },
});
const emit = defineEmits(["update:modelValue"]);
</script>

<template>
  <input
    :type="type"
    class="h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
    :placeholder="placeholder"
    :value="modelValue"
    @input="emit('update:modelValue', $event.target.value)"
  />
</template>
