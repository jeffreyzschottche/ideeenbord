<script setup lang="ts">
// Import reactive display state from composable
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { computed } from "vue";

// Destructure shared state from composable
const { show, message, type } = useResponseDisplay();
</script>
<template>
  <Transition name="slide-fade">
    <div v-if="show" :class="['response-display', type]">
      <span class="message">{{ message }}</span>
    </div>
  </Transition>
</template>
<style scoped>
/* Base notification container styling */
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
  font-size: 16px;
}

/* Style per notification type */
.success {
  border: 3px solid #28a745; /* Green for success */
  background: white;
}

.error {
  border: 3px solid #dc3545; /* Red for error */
  background: white;
}

.warning {
  border: 3px solid #fd7e14; /* Orange for warning */
  background: white;
}

/* Message container */
.message {
  flex: 1;
  text-align: center;
}

/* Slide and fade animation */
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
