<template>
  <div>
    <Nav />
    <main>
      <slot />
    </main>
    <Footer />
  </div>
</template>

<script setup>
import Nav from "~/components/navbar/Nav.vue";
import Footer from "~/components/footer/footer.vue";
</script>

<style scoped></style>
