<template>
  <section class="py-20">
    <div
      class="max-w-6xl mx-auto flex flex-col lg:flex-row items-center gap-10 px-4"
    >
      <!-- VIDEO --------------------------------------------------------- -->
      <div class="w-full lg:w-7/12">
        <div
          class="rounded-xl overflow-hidden ring-4 ring-[var(--color-brand)] shadow-xl"
        >
          <!-- Youtube-embed: 16/9 aspect ratio -->
          <iframe
            class="w-full aspect-video"
            :src="embedUrl"
            title="Demo-video"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
          />
        </div>
      </div>

      <!-- TEKST ---------------------------------------------------------- -->
      <div class="w-full lg:w-5/12">
        <h2 class="text-2xl md:text-3xl font-bold dark-text mb-4">
          {{ content["video-title"] }}
        </h2>
        <p class="text-base md:text-lg leading-relaxed">
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

/* Zet gewone YouTube-URL uit het CMS om naar een embed-URL */
const embedUrl = computed(() => {
  const raw = content.value["video-url"] as string | undefined;

  /* fallback → Rick Astley 😉 */
  const url = raw?.trim() || "https://www.youtube.com/watch?v=dQw4w9WgXcQ";

  /**  ▸ https://www.youtube.com/watch?v=ID   ->  https://www.youtube.com/embed/ID
   *  ▸ https://youtu.be/ID                 ->  https://www.youtube.com/embed/ID
   */
  const idMatch =
    url.match(/youtu\.be\/([\w-]{11})/) ?? url.match(/v=([\w-]{11})/);
  const id = idMatch ? idMatch[1] : "dQw4w9WgXcQ";

  return `https://www.youtube.com/embed/${id}?rel=0`;
});
</script>

<style scoped>
.dark-text {
  color: var(--color-text-dark, #111);
}
.light-text {
  color: var(--color-text-light, #555);
}

/* brand-kleur lichtere variant indien nodig */
:root {
  --color-brand-light: color-mix(in srgb, var(--color-brand) 50%, #fff);
}
</style>
