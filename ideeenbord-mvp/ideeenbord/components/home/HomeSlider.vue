<template>
  <div
    class="relative w-full h-64 md:h-96 overflow-hidden select-none mb-5"
    @mouseenter="pauseCarousel"
    @mouseleave="resumeCarousel"
    @mousedown="startDrag"
    @mousemove="dragSlide"
    @mouseup="endDrag"
    @touchstart="startDrag"
    @touchmove="dragSlide"
    @touchend="endDrag"
  >
    <div
      class="absolute top-0 left-0 flex w-full h-full transition-transform duration-700 ease-in-out"
      :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
    >
      <div
        v-for="(image, index) in images"
        :key="index"
        class="w-full h-full flex-shrink-0"
      >
        <img :src="image" class="w-full h-full object-contain" />
      </div>
    </div>

    <div
      class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 bg-gray-900 bg-opacity-50 p-2.5 rounded-full"
    >
      <button
        v-for="(_, index) in images"
        :key="index"
        @click="goToSlide(index)"
        class="w-4 h-4 rounded-full transition-transform duration-300 focus:outline-none hover:scale-150"
        :class="index === currentSlide ? 'bg-orange-500' : 'bg-white'"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from "vue";
const props = defineProps<{
  content: Record<string, string>;
}>();

const imageKeys = [
  "home-slider-1",
  "home-slider-2",
  "home-slider-3",
  "home-slider-4",
];
const images = computed(() => {
  return imageKeys
    .map((key) => props.content[key])
    .filter((img) => typeof img === "string" && img.trim() !== "");
});

const currentSlide = ref(0);
let interval: any,
  startX = 0,
  isDragging = false;

const goToSlide = (index: number) => {
  currentSlide.value = index;
  resetCarousel();
};
const nextSlide = () =>
  (currentSlide.value = (currentSlide.value + 1) % images.value.length);
const startCarousel = () => (interval = setInterval(nextSlide, 5500));
const resetCarousel = () => {
  clearInterval(interval);
  startCarousel();
};
const pauseCarousel = () => clearInterval(interval);
const resumeCarousel = () => startCarousel();
const startDrag = (e: MouseEvent | TouchEvent) => {
  isDragging = true;
  startX = "touches" in e ? e.touches[0].clientX : e.clientX;
};
const dragSlide = (e: MouseEvent | TouchEvent) => {
  if (!isDragging) return;
  const currentX = "touches" in e ? e.touches[0].clientX : e.clientX;
  const diff = startX - currentX;
  if (diff > 50) {
    nextSlide();
    isDragging = false;
  }
  if (diff < -50) {
    currentSlide.value =
      (currentSlide.value - 1 + images.value.length) % images.value.length;
    isDragging = false;
  }
};
const endDrag = () => (isDragging = false);

onMounted(() => {
  startCarousel();
});
onBeforeUnmount(() => clearInterval(interval));
</script>
