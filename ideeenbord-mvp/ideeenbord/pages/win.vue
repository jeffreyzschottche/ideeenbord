<template>
  <div v-if="ready" class="font-default py-12">
    <!-- HERO / INTRO -->
    <section class="max-w-4xl mx-auto text-center space-y-6 px-4">
      <img
        v-if="content['hero-image']"
        :src="imageUrl(content['hero-image'])"
        class="mx-auto w-full max-w-md rounded-xl shadow"
        alt="Winactie"
      />

      <h1 class="text-3xl md:text-4xl font-bold dark-text">
        {{ content["hero-title"] }}
      </h1>
      <p class="font-alt text-lg main-text">
        {{ content["hero-paragraph"] }}
      </p>

      <!-- CTA naar deelnemers / brands -->
      <NuxtLink to="/participants" class="cta inline-block mb-8">
        {{ content["cta-label"] || "Zoek brands" }}
      </NuxtLink>
    </section>

    <!-- HOE WERKT HET? -->
    <section class="max-w-5xl mx-auto mt-16 px-4">
      <h2 class="text-2xl md:text-3xl font-bold text-center mb-10 dark-text">
        {{ content["steps-title"] }}
      </h2>

      <ol class="grid md:grid-cols-3 gap-8">
        <li
          v-for="step in steps"
          :key="step.id"
          class="about-card text-center space-y-3"
        >
          <span
            class="w-10 h-10 inline-flex items-center justify-center rounded-full bg-brand text-white font-bold"
          >
            {{ step.id }}
          </span>
          <h3 class="font-semibold text-lg">{{ step.title }}</h3>
          <p class="text-sm opacity-80">{{ step.desc }}</p>
        </li>
      </ol>
    </section>

    <!-- LAATSTE WINACTIES / QUIZZES -->
    <CurrentActions class="mt-20" />
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useRuntimeConfig } from "nuxt/app";
import { useCmsContent } from "~/composables/content/useCmsContent";
import CurrentActions from "~/components/home/CurrentActions.vue"; // pad evt. aanpassen

/* ---------- CMS ---------- */
const { content, isLoading } = useCmsContent("win");
const ready = computed(
  () => !isLoading.value && Object.keys(content.value).length
);

/* stappen (3) */
const steps = computed(() => {
  const c = content.value;
  return [
    { id: 1, title: c["step1-title"], desc: c["step1-desc"] },
    { id: 2, title: c["step2-title"], desc: c["step2-desc"] },
    { id: 3, title: c["step3-title"], desc: c["step3-desc"] },
  ];
});

function imageUrl(path?: string) {
  if (!path) return "";
  if (
    path.startsWith("http") ||
    path.startsWith("//") ||
    path.startsWith("/img")
  )
    return path;
  const base = (useRuntimeConfig().public.apiBaseUrl as string).replace(
    "/api",
    "/storage"
  );
  return `${base}/${path.replace(/^\/+/, "")}`;
}
</script>
