<template>
  <div v-if="ready" class="font-default py-12">
    <!-- ───── HERO ───── -->
    <section class="max-w-4xl mx-auto text-center space-y-6 px-4">
      <img
        v-if="content['hero-image']"
        :src="imageUrl(content['hero-image'])"
        class="mx-auto w-full max-w-md rounded-xl shadow"
        alt="Over ons"
      />

      <h1 class="text-3xl md:text-4xl font-bold dark-text">
        {{ content["hero-title"] }}
      </h1>
      <p class="text-lg main-text">
        {{ content["hero-subtitle"] }}
      </p>
    </section>

    <!-- ───── BRAND & FAN ───── -->
    <section class="max-w-6xl mx-auto mt-16 px-4 grid md:grid-cols-2 gap-8">
      <!-- ─── BRAND CARD ─── -->
      <div
        class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center space-y-6"
      >
        <img
          v-if="brandData.image"
          :src="imageUrl(brandData.image)"
          class="w-full max-w-md max-h-72 rounded-xl shadow"
          :alt="brandData.title"
        />

        <h2 class="text-2xl font-bold dark-text">{{ brandData.title }}</h2>
        <p class="main-text">{{ brandData.description }}</p>

        <!-- stappen -->
        <div class="space-y-6 w-full">
          <div
            v-for="step in brandData.steps"
            :key="step.id"
            class="text-center"
          >
            <span
              class="w-10 h-10 inline-flex items-center justify-center rounded-full bg-brand text-white font-bold"
            >
              {{ step.id }}
            </span>
            <h3 class="font-semibold mt-2">{{ step.title }}</h3>
            <p class="text-sm opacity-80">{{ step.description }}</p>
          </div>
        </div>

        <!-- één CTA -->
        <NuxtLink
          v-if="brandData.cta.label"
          :to="brandData.cta.to"
          class="cta inline-block mt-4"
        >
          {{ brandData.cta.label }}
        </NuxtLink>
      </div>

      <!-- ─── FAN CARD ─── -->
      <div
        class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center space-y-6"
      >
        <img
          v-if="fanData.image"
          :src="imageUrl(fanData.image)"
          class="w-full max-w-md max-h-72 rounded-xl shadow"
          :alt="fanData.title"
        />

        <h2 class="text-2xl font-bold dark-text">{{ fanData.title }}</h2>
        <p class="main-text">{{ fanData.description }}</p>

        <!-- vier CTA-knoppen in één loop -->
        <NuxtLink
          v-for="btn in fanData.ctas"
          :key="btn.to"
          :to="btn.to"
          class="cta inline-block w-50"
        >
          {{ btn.label }}
        </NuxtLink>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useCmsContent } from "~/composables/content/useCmsContent";
import { useRuntimeConfig } from "nuxt/app";

const { content, isLoading } = useCmsContent("uitleg");
const ready = computed(
  () => !isLoading.value && Object.keys(content.value).length > 0
);

/* ─── BRAND card data ─── */
const brandData = computed(() => {
  const c = content.value;
  return {
    title: c["brand-title"] ?? "",
    description: c["brand-description"] ?? "",
    steps: [
      {
        id: 1,
        title: c["brand-step1-title"] ?? "",
        description: c["brand-step1-description"] ?? "",
      },
      {
        id: 2,
        title: c["brand-step2-title"] ?? "",
        description: c["brand-step2-description"] ?? "",
      },
    ],
    cta: { label: c["brand-cta-label"] ?? "", to: c["brand-cta-link"] ?? "#" },
    image: c["brand-image"] ?? "",
  };
});

/* ─── FAN card data ─── */
const fanData = computed(() => {
  const c = content.value;

  const ctas = [
    { label: c["fan-cta-login-label"], to: c["fan-cta-login-link"] },
    { label: c["fan-cta-register-label"], to: c["fan-cta-register-link"] },
    {
      label: c["fan-cta-participants-label"],
      to: c["fan-cta-participants-link"],
    },
    { label: c["fan-cta-ideas-label"], to: c["fan-cta-ideas-link"] },
  ].filter((btn) => btn.label && btn.to); // alleen ingevulde knoppen

  return {
    title: c["fan-title"] ?? "",
    description: c["fan-description"] ?? "",
    image: c["fan-image"] ?? "",
    ctas,
  };
});

/* ─── helper voor images uit opslag ─── */
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
