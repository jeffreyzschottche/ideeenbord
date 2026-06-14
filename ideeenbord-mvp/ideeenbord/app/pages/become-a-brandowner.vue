<template>
  <div v-if="ready" class="bg-[var(--color-bg)]">
    <!-- Hero -->
    <section class="relative overflow-hidden bg-[var(--color-nav)] text-white">
      <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full bg-[var(--color-brand)]/25 blur-3xl"></div>
      <div class="absolute -bottom-28 -left-24 w-80 h-80 rounded-full bg-[var(--color-brand)]/15 blur-3xl"></div>
      <div class="relative max-w-4xl mx-auto px-4 py-16 md:py-24 text-center" v-reveal="{ y: 24 }">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[var(--color-brand)]/20 border border-[var(--color-brand)]/30 text-sm font-semibold mb-4">
          <i class="fa-solid fa-bolt text-[var(--color-brand)]"></i>
          Vanaf €49/maand
        </span>
        <h1 class="text-3xl md:text-5xl font-extrabold leading-tight">
          Marktonderzoek zonder de kosten van marktonderzoek
        </h1>
        <p class="mt-5 text-lg text-gray-300 max-w-2xl mx-auto">
          Luister naar wat je klanten écht willen. Ideeënbord geeft je directe toegang tot feedback,
          wensen en innovatie-ideeën van je doelgroep — met slimme AI-rapporten die je vertellen wat je moet doen.
        </p>

        <!-- Value prop bullets -->
        <div class="mt-8 flex flex-wrap justify-center gap-x-8 gap-y-3 text-sm text-gray-300">
          <span class="flex items-center gap-2">
            <i class="fa-solid fa-check text-green-400"></i>
            Geen dure focusgroepen
          </span>
          <span class="flex items-center gap-2">
            <i class="fa-solid fa-check text-green-400"></i>
            Realtime klantinzichten
          </span>
          <span class="flex items-center gap-2">
            <i class="fa-solid fa-check text-green-400"></i>
            AI-gegenereerde actierapporten
          </span>
        </div>

        <div class="mt-8 flex flex-wrap gap-3 justify-center">
          <NuxtLink to="/register" class="cta px-7 py-3.5 text-base">Start nu — 14 dagen gratis</NuxtLink>
          <NuxtLink to="/voorbeeld-rapport" class="px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 font-semibold transition flex items-center gap-2">
            <i class="fa-solid fa-file-lines"></i>
            Bekijk een voorbeeldrapport
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Wat krijg je -->
    <section class="max-w-5xl mx-auto px-4 py-14 md:py-20">
      <div class="text-center max-w-2xl mx-auto mb-10" v-reveal="{ y: 20 }">
        <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
          Wat je koopt
        </span>
        <h2 class="text-2xl md:text-3xl font-extrabold text-[var(--color-nav)]">
          Alles wat je nodig hebt om je klanten te begrijpen
        </h2>
        <p class="mt-3 text-gray-600">
          Geen losse modules of verborgen kosten. Eén abonnement, volledige toegang.
        </p>
      </div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4" v-reveal="{ stagger: 0.08, y: 24 }">
        <div v-for="b in benefits" :key="b.title" class="rounded-2xl bg-white border border-gray-100 shadow-[0_8px_30px_rgba(31,41,55,0.06)] p-5 hover:shadow-lg hover:-translate-y-1 transition-all">
          <span class="w-10 h-10 rounded-lg bg-[var(--color-brand)]/10 text-[var(--color-brand)] flex items-center justify-center text-lg mb-3">
            <i class="fa-solid" :class="b.icon"></i>
          </span>
          <h3 class="font-bold text-[var(--color-nav)] mb-1 text-sm">{{ b.title }}</h3>
          <p class="text-xs text-gray-600 leading-relaxed">{{ b.text }}</p>
        </div>
      </div>

      <!-- Concrete waarde -->
      <div class="mt-12 grid md:grid-cols-3 gap-6" v-reveal="{ y: 20 }">
        <div class="rounded-2xl bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 p-6 text-center">
          <p class="text-3xl font-extrabold text-green-600 mb-2">€10.000+</p>
          <p class="text-sm text-gray-600">bespaard t.o.v. traditioneel marktonderzoek</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 p-6 text-center">
          <p class="text-3xl font-extrabold text-blue-600 mb-2">24/7</p>
          <p class="text-sm text-gray-600">continue stroom van klantfeedback</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-purple-50 to-violet-50 border border-purple-200 p-6 text-center">
          <p class="text-3xl font-extrabold text-purple-600 mb-2">3x</p>
          <p class="text-sm text-gray-600">sneller innoveren met klantvalidatie</p>
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
      <div class="absolute -bottom-24 -left-16 w-64 h-64 rounded-full bg-[var(--color-brand)]/15 blur-3xl"></div>
      <div class="relative max-w-3xl mx-auto px-4 py-16 md:py-20 text-center" v-reveal="{ y: 24 }">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-500/20 border border-green-400/30 text-green-300 text-sm font-semibold mb-4">
          <i class="fa-solid fa-gift"></i>
          14 dagen gratis uitproberen
        </div>
        <h2 class="text-2xl md:text-4xl font-extrabold leading-tight">
          Start vandaag met luisteren naar je klanten
        </h2>
        <p class="mt-4 text-gray-300 max-w-xl mx-auto">
          Geen creditcard nodig. Binnen 5 minuten live. Annuleer wanneer je wilt.
        </p>
        <div class="mt-8 flex flex-wrap gap-3 justify-center">
          <NuxtLink to="/register" class="cta px-7 py-3.5 text-base">Start gratis proefperiode</NuxtLink>
          <NuxtLink to="/voorbeeld-rapport" class="px-7 py-3.5 rounded-xl border-2 border-white/30 hover:bg-white/10 font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-file-lines"></i>
            Bekijk voorbeeldrapport
          </NuxtLink>
        </div>
        <p class="mt-6 text-sm text-gray-400">
          Vanaf €49/maand na de proefperiode
        </p>
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
  {
    icon: "fa-comments",
    title: "Klantideeën ontvangen",
    text: "Je klanten delen hun wensen, frustraties en innovatie-ideeën rechtstreeks met jou.",
  },
  {
    icon: "fa-chart-pie",
    title: "Slimme AI-rapporten",
    text: "Automatische analyses met trends, persona-profielen en concrete aanbevelingen voor actie.",
  },
  {
    icon: "fa-chart-line",
    title: "Live dashboard",
    text: "Realtime sentiment, demografische data en de meest gevraagde verbeteringen in één overzicht.",
  },
  {
    icon: "fa-download",
    title: "Volledige data-export",
    text: "Download alle inzichten als CSV, JSON of XML — perfect voor je eigen analyses.",
  },
  {
    icon: "fa-trophy",
    title: "Quizzes uitschrijven",
    text: "Activeer je community met winacties en vergroot je bereik op het platform.",
  },
  {
    icon: "fa-question-circle",
    title: "Hoofdvraag stellen",
    text: "Stel de vraag die er voor jou toe doet en ontvang gerichte antwoorden van je doelgroep.",
  },
  {
    icon: "fa-bell",
    title: "Notificaties & alerts",
    text: "Ontvang direct bericht bij populaire ideeën of sentiment-veranderingen.",
  },
  {
    icon: "fa-shield-check",
    title: "Je merkpagina claimen",
    text: "Beheer hoe je merk gepresenteerd wordt en reageer op klantideeën.",
  },
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
