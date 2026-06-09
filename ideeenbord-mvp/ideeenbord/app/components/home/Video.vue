<template>
  <section class="py-16 md:py-24">
    <div class="flex flex-col lg:flex-row items-center gap-10 lg:gap-16">
      <!-- Video -->
      <div class="w-full lg:w-7/12">
        <div class="relative">
          <div class="absolute -inset-3 rounded-3xl bg-[var(--color-brand)]/10 -z-10 -rotate-2"></div>
          <div class="rounded-2xl overflow-hidden shadow-2xl border border-gray-100">
            <iframe
              class="w-full aspect-video"
              :src="embedUrl"
              title="Demo-video"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowfullscreen
            />
          </div>
        </div>
      </div>

      <!-- Tekst -->
      <div class="w-full lg:w-5/12">
        <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
          Bekijk de demo
        </span>
        <h2 class="text-3xl md:text-4xl font-extrabold text-[var(--color-nav)] leading-tight">
          {{ content["video-title"] }}
        </h2>
        <p class="mt-5 text-lg text-gray-600 leading-relaxed">
          {{ content["video-description"] }}
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useCmsContent } from "~/composables/content/useCmsContent";

const { content } = useCmsContent("home");

const embedUrl = computed(() => {
  const raw = content.value["video-url"] as string | undefined;
  const url = raw?.trim() || "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
  const idMatch =
    url.match(/youtu\.be\/([\w-]{11})/) ?? url.match(/v=([\w-]{11})/);
  const id = idMatch ? idMatch[1] : "dQw4w9WgXcQ";
  return `https://www.youtube.com/embed/${id}?rel=0`;
});
</script>
