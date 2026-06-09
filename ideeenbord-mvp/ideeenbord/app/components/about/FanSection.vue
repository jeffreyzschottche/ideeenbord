<template>
  <div
    class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center p-6 md:p-12 rounded-2xl border border-gray-200 light-bg shadow-sm"
  >
    <!-- IMAGE -->
    <div class="order-2 md:order-1 flex justify-center md:justify-start">
      <img
        :src="correctImageUrl(data.image)"
        alt=""
        class="w-full max-w-xs md:max-w-sm object-contain"
      />
    </div>

    <!-- TEXT -->
    <div class="order-1 md:order-2 space-y-6">
      <h2 class="text-2xl md:text-3xl font-bold dark-text">{{ data.title }}</h2>
      <p class="font-alt text-base md:text-lg main-text">
        {{ data.description }}
      </p>
      <NuxtLink :to="data.cta.to" class="cta inline-block">{{
        data.cta.label
      }}</NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRuntimeConfig } from "nuxt/app";
interface FanData {
  title: string;
  description: string;
  cta: { label: string; to: string };
  image?: string;
}
defineProps<{ data: FanData }>();

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
