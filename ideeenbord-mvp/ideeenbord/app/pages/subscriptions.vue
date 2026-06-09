<template>
  <div v-if="ready" class="py-12 font-default">
    <!-- HERO -->
    <section class="max-w-4xl mx-auto text-center space-y-6 px-4 mb-12">
      <img
        v-if="c['hero-image']"
        :src="imageUrl(c['hero-image'])"
        class="mx-auto w-full max-w-md rounded-xl shadow"
        alt="Subscriptions"
      />
      <h1 class="text-3xl md:text-4xl font-bold dark-text">
        {{ c["hero-title"] }}
      </h1>
      <p class="font-alt text-lg">
        {{ c["hero-paragraph"] }}
      </p>
    </section>

    <!-- PLANS -->
    <section class="max-w-6xl mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
          v-for="p in plans"
          :key="p.key"
          class="rounded-2xl bg-white shadow p-6 flex flex-col border"
          :class="
            p.key === 'gold'
              ? 'border-[var(--color-brand)] ring-2 ring-[var(--color-brand)]'
              : 'border-gray-200'
          "
        >
          <h2 class="text-2xl font-bold mb-1 text-[var(--color-brand)]">
            {{ p.title }}
          </h2>
          <p class="text-sm text-gray-500 mb-4">{{ p.subtitle }}</p>

          <div class="text-3xl font-extrabold mb-4">
            €{{ p.price }}
            <span class="text-base font-medium text-gray-500">/ maand</span>
          </div>

          <p v-if="p.description" class="mb-4 main-text">{{ p.description }}</p>

          <ul class="space-y-2 mb-6">
            <li
              v-for="(f, i) in p.features"
              :key="i"
              class="flex items-start gap-2"
            >
              <i class="fa-solid fa-check mt-1 text-[var(--color-brand)]"></i>
              <span>{{ f }}</span>
            </li>
          </ul>

          <NuxtLink
            v-if="p.ctaLabel && p.ctaLink"
            :to="p.ctaLink"
            class="cta w-full text-center mt-auto"
          >
            {{ p.ctaLabel }}
          </NuxtLink>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
usePageSeo({
  title: "Abonnementen",
  description:
    "Bekijk de abonnementen voor merken op Ideeënbord: Brons, Zilver en Goud — elk met eigen voordelen.",
});
useJsonLd(
  breadcrumbLd([
    { name: "Home", path: "/" },
    { name: "Abonnementen", path: "/subscriptions" },
  ])
);
import { computed } from "vue";
import { useRuntimeConfig } from "nuxt/app";
import { useCmsContent } from "~/composables/content/useCmsContent";

const { content: c, isLoading } = useCmsContent("subscriptions");
const ready = computed(
  () => !isLoading.value && Object.keys(c.value).length > 0
);

/** helper voor images uit storage */
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

/** plans uit CMS bouwen */
type PlanKey = "bronze" | "silver" | "gold";
const order: PlanKey[] = ["bronze", "silver", "gold"];

const plans = computed(() => {
  const out: Array<{
    key: PlanKey;
    title: string;
    subtitle?: string;
    price: string;
    description?: string;
    features: string[];
    ctaLabel?: string;
    ctaLink?: string;
  }> = [];

  const data = c.value as Record<string, string>;
  for (const key of order) {
    const title = data[`plan-${key}-title`] ?? "";
    const subtitle = data[`plan-${key}-subtitle`] ?? "";
    const price = data[`plan-${key}-price`] ?? "";
    const description = data[`plan-${key}-description`] ?? "";

    const features: string[] = [];
    let i = 1;
    while (data[`plan-${key}-feature${i}`]) {
      features.push(data[`plan-${key}-feature${i}`]);
      i++;
    }

    out.push({
      key,
      title,
      subtitle,
      price,
      description,
      features,
      ctaLabel: data[`plan-${key}-cta-label`],
      ctaLink: data[`plan-${key}-cta-link`],
    });
  }
  return out;
});
</script>

<style scoped>
.dark-text {
  color: var(--color-text-dark, #111);
}
.main-text {
  color: var(--color-text-light, #555);
}
</style>
