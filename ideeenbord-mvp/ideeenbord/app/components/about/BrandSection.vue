<template>
  <div
    class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center p-6 md:p-12 rounded-2xl border border-gray-200 light-bg shadow-sm"
  >
    <!-- TEXT -->
    <div class="space-y-6">
      <h2 class="text-2xl md:text-3xl font-bold dark-text">{{ data.title }}</h2>
      <p class="font-alt text-base md:text-lg main-text">
        {{ data.description }}
      </p>
      <ul class="space-y-3">
        <li
          v-for="step in data.steps"
          :key="step.id"
          class="flex items-start gap-4 bg-gray-50 border border-gray-200 rounded-xl p-4"
        >
          <span
            class="w-9 h-9 flex items-center justify-center rounded-full bg-brand text-white font-bold"
          >
            {{ step.id }}
          </span>
          <div>
            <h3 class="font-semibold">{{ step.title }}</h3>
            <p class="text-sm opacity-80">{{ step.description }}</p>
          </div>
        </li>
      </ul>
      <NuxtLink :to="data.cta.to" class="cta inline-block">{{
        data.cta.label
      }}</NuxtLink>
    </div>

    <!-- IMAGE -->
    <div class="flex justify-center md:justify-end">
      <img
        :src="correctImageUrl(data.image)"
        alt=""
        class="w-full max-w-xs md:max-w-sm object-contain"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRuntimeConfig } from "nuxt/app";
type Step = { id: number; title: string; description: string };
interface BrandData {
  title: string;
  description: string;
  steps: Step[];
  cta: { label: string; to: string };
  image?: string;
}
defineProps<{ data: BrandData }>();

function correctImageUrl(path?: string) {
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
