<template>
  <div>
    <NavbarNav />
    <main>
      <div class="h-20"></div>
      <div class="mx-auto container ml-2 mr-2 md:ml-0 md:mr-0">
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
