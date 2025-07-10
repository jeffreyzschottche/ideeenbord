<template>
  <div
    class="bg-white py-24 px-6 flex flex-col items-center text-center relative"
  >
    <!-- LAMP -->
    <div class="relative w-max mb-10">
      <i class="fa-regular fa-lightbulb text-8xl md:text-[7rem] regular-bulb" />
      <i
        class="fa-solid fa-lightbulb text-8xl md:text-[7rem] text-[var(--color-brand)] solid-bulb absolute top-0 left-0"
      />
    </div>

    <!-- CONTENT -->
    <h1 class="text-6xl font-bold text-[var(--color-brand)] mb-4">404</h1>
    <p class="text-xl text-gray-600 mb-6">
      Er gaat bij ons geen lampje branden...
    </p>
    <NuxtLink to="/" class="cta">Terug naar home</NuxtLink>
  </div>
</template>

<style scoped>
@keyframes blink-off {
  0%,
  45% {
    opacity: 1;
  }
  60%,
  90% {
    opacity: 0;
  }
  100% {
    opacity: 1;
  }
}
@keyframes blink-on {
  0%,
  45% {
    opacity: 0;
    filter: drop-shadow(0 0 4px var(--color-brand));
  }
  60%,
  90% {
    opacity: 1;
    filter: drop-shadow(0 0 14px var(--color-brand));
  }
  100% {
    opacity: 0;
    filter: drop-shadow(0 0 4px var(--color-brand));
  }
}
.regular-bulb {
  animation: blink-off 3s ease-in-out infinite;
}
.solid-bulb {
  animation: blink-on 3s ease-in-out infinite;
}
</style>
