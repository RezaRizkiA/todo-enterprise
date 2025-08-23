<script setup>
import { computed } from "vue";

const props = defineProps({
  as: { type: String, default: "button" },
  type: { type: String, default: "button" },
  variant: { type: String, default: "primary" }, // primary | ghost | danger
  size: { type: String, default: "md" }, // sm | md | lg
  disabled: { type: Boolean, default: false },
});

const klass = computed(() => {
  const base =
    "inline-flex items-center justify-center rounded-2xl font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed";
  const sizes = {
    sm: "h-9 px-3 text-sm",
    md: "h-10 px-4 text-sm",
    lg: "h-11 px-5 text-base",
  };
  const variants = {
    primary: "bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500",
    ghost: "bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-300",
    danger: "bg-red-600 text-white hover:bg-red-700 focus:ring-red-500",
  };
  return [base, sizes[props.size], variants[props.variant]].join(" ");
});
</script>

<template>
  <component
    :is="as"
    :type="as === 'button' ? type : undefined"
    :class="klass"
    :disabled="disabled"
  >
    <slot />
  </component>
</template>
