<template>
  <div v-if="ready" class="bg-[var(--color-bg)]">
    <!-- Hero -->
    <section class="relative overflow-hidden bg-[var(--color-nav)] text-white">
      <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full bg-[var(--color-brand)]/25 blur-3xl"></div>
      <div class="absolute -bottom-28 -left-24 w-80 h-80 rounded-full bg-[var(--color-brand)]/15 blur-3xl"></div>
      <div class="relative max-w-4xl mx-auto px-4 py-16 md:py-24 text-center" v-reveal="{ y: 24 }">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
          Word merkeigenaar
        </p>
        <h1 class="text-3xl md:text-5xl font-extrabold leading-tight">
          {{ content["become-title"] }}
        </h1>
        <p class="mt-5 text-lg text-gray-300 max-w-2xl mx-auto">
          Claim je merk, ga in gesprek met je klanten en zet hun ideeën om in
          concrete inzichten — met live statistieken en diepgaande AI-rapporten.
        </p>
        <div class="mt-8 flex flex-wrap gap-3 justify-center">
          <NuxtLink to="/register" class="cta px-7 py-3.5 text-base">Claim je merk</NuxtLink>
          <NuxtLink to="/voorbeeld-rapport" class="px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 font-semibold transition">
            Bekijk een voorbeeldrapport
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Wat krijg je -->
    <section class="max-w-5xl mx-auto px-4 py-14 md:py-20">
      <div class="text-center max-w-2xl mx-auto mb-10" v-reveal="{ y: 20 }">
        <h2 class="text-2xl md:text-3xl font-extrabold text-[var(--color-nav)]">
          Wat krijg je als merkeigenaar?
        </h2>
        <p class="mt-3 text-gray-600">
          Eén abonnement, toegang tot alles wat je nodig hebt om naar je klanten te luisteren.
        </p>
      </div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5" v-reveal="{ stagger: 0.1, y: 24 }">
        <div v-for="b in benefits" :key="b.title" class="rounded-2xl bg-white border border-gray-100 shadow-[0_8px_30px_rgba(31,41,55,0.06)] p-6">
          <span class="w-12 h-12 rounded-xl bg-[var(--color-brand)]/10 text-[var(--color-brand)] flex items-center justify-center text-xl mb-4">
            <i class="fa-solid" :class="b.icon"></i>
          </span>
          <h3 class="font-bold text-[var(--color-nav)] mb-1">{{ b.title }}</h3>
          <p class="text-sm text-gray-600">{{ b.text }}</p>
        </div>
      </div>
    </section>

    <!-- Stappen -->
    <section class="bg-white border-y border-gray-100">
      <div class="max-w-3xl mx-auto px-4 py-14 md:py-20">
        <div class="text-center mb-12" v-reveal="{ y: 20 }">
          <h2 class="text-2xl md:text-3xl font-extrabold text-[var(--color-nav)]">
            Zo werkt het
          </h2>
          <p class="mt-3 text-gray-600">In een paar stappen ben je live.</p>
        </div>

        <ol class="relative border-l-2 border-[var(--color-brand)]/20 ml-4 space-y-10" v-reveal="{ stagger: 0.12, y: 28 }">
          <li v-for="(step, i) in steps" :key="step.id" class="relative pl-8">
            <span class="absolute -left-[1.30rem] top-0 w-9 h-9 rounded-full bg-[var(--color-brand)] text-white font-bold flex items-center justify-center shadow-md">
              {{ i + 1 }}
            </span>
            <h3 class="text-lg font-bold mb-1 text-[var(--color-nav)]">{{ step.title }}</h3>
            <p class="text-gray-600 mb-3">{{ step.description }}</p>
            <NuxtLink v-if="step.link" :to="step.link" class="inline-flex items-center gap-1.5 font-semibold text-[var(--color-brand)]">
              {{ step.linkLabel }} <i class="fa-solid fa-arrow-right text-xs"></i>
            </NuxtLink>
          </li>
        </ol>
      </div>
    </section>

    <!-- Closing CTA -->
    <section class="relative overflow-hidden bg-[var(--color-nav)] text-white">
      <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-[var(--color-brand)]/25 blur-3xl"></div>
      <div class="relative max-w-3xl mx-auto px-4 py-16 md:py-20 text-center" v-reveal="{ y: 24 }">
        <i class="fa-solid fa-store text-4xl text-[var(--color-brand)] mb-4"></i>
        <h2 class="text-2xl md:text-4xl font-extrabold leading-tight">
          Klaar om je klanten écht te horen?
        </h2>
        <p class="mt-4 text-gray-300 max-w-xl mx-auto">
          Maak een account aan en claim vandaag nog je merk op Ideeënbord.
        </p>
        <div class="mt-8 flex flex-wrap gap-3 justify-center">
          <NuxtLink to="/register" class="cta px-7 py-3.5 text-base">Claim je merk</NuxtLink>
          <NuxtLink to="/subscriptions" class="px-7 py-3.5 rounded-xl border-2 border-white/30 hover:bg-white/10 font-bold transition">
            Bekijk het abonnement
          </NuxtLink>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
usePageSeo({
  title: "Word merkeigenaar",
  description:
    "Claim je merk op Ideeënbord en ga in gesprek met je klanten via hun ideeën, wensen en feedback.",
});
useJsonLd(
  breadcrumbLd([
    { name: "Home", path: "/" },
    { name: "Word merkeigenaar", path: "/become-a-brandowner" },
  ])
);
import { computed } from "vue";
import { useCmsContent } from "~/composables/content/useCmsContent";

const { content, isLoading } = useCmsContent("become-a-brandowner");
const ready = computed(
  () => !isLoading.value && Object.keys(content.value).length > 0
);

const benefits = [
  { icon: "fa-chart-line", title: "Live statistieken", text: "Realtime inzicht in ideeën, sentiment en je doelgroep." },
  { icon: "fa-file-lines", title: "AI-rapporten", text: "Diepgaand advies met grafieken, persona's en concrete acties." },
  { icon: "fa-database", title: "Data-export", text: "Download alles als CSV, JSON of XML — per periode." },
  { icon: "fa-bullhorn", title: "Quizzes & hoofdvraag", text: "Activeer je publiek en stuur de gesprekken die jij wilt." },
];

const steps = computed(() => {
  const c = content.value;
  return [
    {
      id: 1,
      title: c["step1-title"] ?? "",
      description: c["step1-description"] ?? "",
      link: c["step1-link"] ?? null,
      linkLabel: c["step1-link-label"] ?? null,
    },
    {
      id: 2,
      title: c["step2-title"] ?? "",
      description: c["step2-description"] ?? "",
    },
    {
      id: 3,
      title: c["step3-title"] ?? "",
      description: c["step3-description"] ?? "",
      link: c["step3-link"] ?? null,
      linkLabel: c["step3-link-label"] ?? null,
    },
    {
      id: 4,
      title: c["step4-title"] ?? "",
      description: c["step4-description"] ?? "",
    },
    {
      id: 5,
      title: c["step5-title"] ?? "",
      description: c["step5-description"] ?? "",
    },
  ];
});
</script>
