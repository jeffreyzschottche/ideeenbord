<template>
  <Transition name="slide-fade">
    <div v-if="show" :class="['response-display', type]">
      <span class="message">{{ message }}</span>
      <button @click="close" class="close-button" aria-label="Sluiten">
        &times;
      </button>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import { computed } from "vue";

const { show, message, type } = useResponseDisplay();

function close() {
  show.value = false;
}
</script>

<style scoped>
.response-display {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  max-width: 400px;
  width: 90%;
  padding: 15px 20px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-weight: bold;
  color: white;
  font-size: 16px;
}

/* Kleuren per type */
.success {
  background-color: #28a745; /* Mooie groene kleur */
}

.error {
  background-color: #dc3545; /* Mooie rode kleur */
}

.warning {
  background-color: #fd7e14; /* Mooie oranje kleur */
}

.message {
  flex: 1;
  text-align: center;
}

.close-button {
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  margin-left: 10px;
  cursor: pointer;
}

/* Animatie */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.4s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-20px) translateX(-50%);
}
.slide-fade-enter-to,
.slide-fade-leave-from {
  opacity: 1;
  transform: translateY(0) translateX(-50%);
}
</style>
