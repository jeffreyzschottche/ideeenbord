<template>
  <section
    class="w-full flex flex-col md:flex-row items-center justify-between gap-8 md:gap-16 py-10 md:py-20"
  >
    <!-- DATA BOX -->
    <div class="flex-1 flex items-center justify-center">
      <div class="relative rounded-2xl bg-nav px-8 py-10 shadow-xl glow-box">
        <div
          class="grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-16 min-w-[320px]"
        >
          <div class="flex flex-col items-center">
            <span
              class="text-4xl font-extrabold text-[var(--color-brand)] drop-shadow"
            >
              +{{ content["stat-companies"] }}
            </span>
            <span class="mt-1 text-lg font-medium light-text text-center">
              {{ content["stat-companies-label"] }}
            </span>
          </div>
          <div class="flex flex-col items-center">
            <span
              class="text-4xl font-extrabold text-[var(--color-brand)] drop-shadow"
            >
              +{{ content["stat-idea-makers"] }}
            </span>
            <span class="mt-1 text-lg font-medium light-text text-center">
              {{ content["stat-idea-makers-label"] }}
            </span>
          </div>
          <div class="flex flex-col items-center">
            <span
              class="text-4xl font-extrabold text-[var(--color-brand)] drop-shadow"
            >
              +{{ content["stat-ideas-submitted"] }}
            </span>
            <span class="mt-1 text-lg font-medium light-text text-center">
              {{ content["stat-ideas-submitted-label"] }}
            </span>
          </div>
          <div class="flex flex-col items-center">
            <span
              class="text-4xl font-extrabold text-[var(--color-brand)] drop-shadow"
            >
              +{{ content["stat-ideas-implemented"] }}
            </span>
            <span class="mt-1 text-lg font-medium light-text text-center">
              {{ content["stat-ideas-implemented-label"] }}
            </span>
          </div>
        </div>
        <div
          class="absolute -inset-2 rounded-2xl pointer-events-none z-0 glow-overlay"
        ></div>
      </div>
    </div>

    <!-- MOTIVATIE TEKST -->
    <div class="flex-1 flex flex-col items-start gap-4 max-w-xl z-10">
      <h2
        v-if="content['motivation-title']"
        class="text-2xl md:text-3xl font-bold dark-text mb-2"
      >
        {{ content["motivation-title"] }}
      </h2>
      <p
        v-if="content['motivation-description']"
        class="text-base md:text-lg dark-text"
      >
        {{ content["motivation-description"] }}
      </p>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useCmsContent } from "~/composables/content/useCmsContent";
const { content } = useCmsContent("home");
</script>

<style scoped>
.glow-box {
  box-shadow: 0 0 32px 0 var(--color-brand), 0 0 0 4px var(--color-brand);
  position: relative;
  z-index: 2;
}
.glow-overlay {
  background: radial-gradient(
    circle at 50% 50%,
    var(--color-brand) 0%,
    transparent 80%
  );
  opacity: 0.18;
  z-index: 1;
}
</style>
