<template>
  <p class="p1 text-2xl font-bold">Poppins</p>
  <p class="font-alt p2 text-2xl font-bold">Roboto</p>
  <pre class="font-default p3 italic text-2xl font-bold">code hier</pre>
</template>
<script setup lang="ts">
import { onMounted } from "vue";

onMounted(() => {
  const logFont = (className: string) => {
    const el = document.querySelector(`.${className}`);
    if (el) {
      const style = getComputedStyle(el);
      console.log(`Font for .${className}:`, style.fontFamily);
    } else {
      console.warn(`Element with class "${className}" not found`);
    }
  };

  logFont("p1");
  logFont("p2");
  logFont("p3");
});
</script>
