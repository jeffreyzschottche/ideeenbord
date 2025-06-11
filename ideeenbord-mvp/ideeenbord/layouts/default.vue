<template>
  <div>
    <NavbarNav />
    <main>
      <div class="h-20"></div>
      <div class="container">
        <slot />
      </div>
    </main>
    <Footer />
  </div>
</template>

<script setup></script>

<style scoped>
main {
  min-height: 80vh;
  padding: 1rem;
}
</style>
