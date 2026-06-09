<script setup lang="ts">
/*
  This page confirms that the user's email has been successfully verified.
  Optionally, an animation or auto-redirect can be added here.
*/
</script>

<template>
  <div class="max-w-md mx-auto mt-20 text-center">
    <h1 class="text-2xl font-bold text-green-600">✅ E-mail geverifieerd!</h1>
    <p class="mt-4">
      Je kunt nu <NuxtLink to="/dashboard">inloggen</NuxtLink> met je gegevens.
    </p>
  </div>
</template>
