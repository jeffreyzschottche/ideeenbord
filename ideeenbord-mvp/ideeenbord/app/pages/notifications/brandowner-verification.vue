<script setup lang="ts">
useHead({
  title: "E-mail Geverifieerd",
});
</script>

<style scoped>
.container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
}
</style>

<template>
  <div class="container mx-auto p-8 text-center">
    <div class="bg-white p-6 rounded-lg shadow-lg">
      <h1 class="text-2xl font-bold text-green-600">
        E-mailadres Geverifieerd!
      </h1>
      <p class="mt-4 text-gray-700">
        Je e-mailadres is succesvol bevestigd. Je account is nu volledig actief.
      </p>
      <p class="mt-2 text-gray-700">
        Je kunt nu inloggen op je brand owner dashboard.
      </p>

      <NuxtLink
        to="/brand-owner/login"
        class="mt-6 inline-block bg-blue-600 text-white font-bold px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors"
      >
        Inloggen als Brand Owner
      </NuxtLink>
    </div>
  </div>
</template>
