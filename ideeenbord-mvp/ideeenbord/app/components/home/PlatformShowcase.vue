<script setup lang="ts">
/*
  "Wat kun je doen op Ideeënbord" — toont de mogelijkheden voor zowel bezoekers
  als merken, met concrete voorbeelddata. Geen CMS/API; puur showcase.
*/
const userFeatures = [
  { icon: "fa-lightbulb", title: "Deel je ideeën", text: "Plaats wat jij anders of beter wilt zien bij je favoriete merken." },
  { icon: "fa-thumbs-up", title: "Stem mee", text: "Like of dislike ideeën van anderen — samen bepaal je wat telt." },
  { icon: "fa-circle-question", title: "Beantwoord de hoofdvraag", text: "Merken stellen je dé vraag die er voor hen toe doet." },
  { icon: "fa-gift", title: "Doe mee &amp; win", text: "Speel mee met merk-quizzes en maak kans op prijzen." },
];
const brandFeatures = [
  { icon: "fa-chart-line", title: "Live statistieken", text: "Realtime inzicht in ideeën, sentiment en je doelgroep." },
  { icon: "fa-file-lines", title: "AI-rapporten", text: "Diepgaand advies met grafieken, persona's en concrete acties." },
  { icon: "fa-database", title: "Data-export", text: "Download alles als CSV, JSON of XML — per periode." },
  { icon: "fa-bullhorn", title: "Quizzes & hoofdvraag", text: "Activeer je publiek en stuur de gesprekken die jij wilt." },
];

const sampleIdea = {
  title: "Haverdrank als standaardoptie",
  brand: "Koffiebar Lumen",
  likes: 47,
  dislikes: 3,
  status: "In behandeling",
};
</script>

<template>
  <section class="py-16 md:py-24">
    <div class="container mx-auto px-4">
      <div class="max-w-2xl mx-auto text-center mb-12" v-reveal="{ y: 20 }">
        <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
          Wat kun je doen?
        </span>
        <h2 class="text-3xl md:text-4xl font-extrabold text-[var(--color-nav)]">
          Eén platform, twee kanten van hetzelfde gesprek
        </h2>
        <p class="mt-4 text-lg text-gray-600">
          Bezoekers delen wat ze willen, merken luisteren en handelen. Zo ziet dat eruit.
        </p>
      </div>

      <div class="grid lg:grid-cols-2 gap-6 max-w-5xl mx-auto">
        <!-- Voor jou -->
        <div class="rounded-3xl bg-white border border-gray-100 shadow-[0_8px_30px_rgba(31,41,55,0.06)] p-7" v-reveal="{ x: -24 }">
          <h3 class="text-xl font-extrabold text-[var(--color-nav)] mb-5">
            <i class="fa-solid fa-user text-[var(--color-brand)] mr-2"></i> Voor jou
          </h3>
          <ul class="space-y-4" v-reveal="{ stagger: 0.08, y: 16 }">
            <li v-for="f in userFeatures" :key="f.title" class="flex gap-3">
              <span class="w-9 h-9 shrink-0 rounded-xl bg-[var(--color-brand)]/10 text-[var(--color-brand)] flex items-center justify-center">
                <i class="fa-solid" :class="f.icon"></i>
              </span>
              <div>
                <p class="font-semibold text-[var(--color-nav)]" v-html="f.title"></p>
                <p class="text-sm text-gray-600" v-html="f.text"></p>
              </div>
            </li>
          </ul>

          <!-- Voorbeeld idee-kaart -->
          <div class="mt-6 rounded-2xl border border-gray-100 bg-[var(--color-bg)] p-4">
            <p class="text-[11px] uppercase tracking-wide text-gray-400 mb-2">Voorbeeld idee</p>
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-semibold text-[var(--color-nav)]">{{ sampleIdea.title }}</p>
                <p class="text-xs text-gray-500">bij {{ sampleIdea.brand }}</p>
              </div>
              <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 whitespace-nowrap">{{ sampleIdea.status }}</span>
            </div>
            <div class="flex items-center gap-4 mt-3 text-sm">
              <span class="inline-flex items-center gap-1.5 text-green-600 font-semibold"><i class="fa-solid fa-thumbs-up"></i> {{ sampleIdea.likes }}</span>
              <span class="inline-flex items-center gap-1.5 text-red-500 font-semibold"><i class="fa-solid fa-thumbs-down"></i> {{ sampleIdea.dislikes }}</span>
            </div>
          </div>

          <NuxtLink to="/brands" class="inline-flex items-center gap-1.5 mt-5 font-semibold text-[var(--color-brand)]">
            Ontdek merken <i class="fa-solid fa-arrow-right text-xs"></i>
          </NuxtLink>
        </div>

        <!-- Voor merken -->
        <div class="rounded-3xl bg-[var(--color-nav)] text-white shadow-[0_8px_30px_rgba(31,41,55,0.18)] p-7 relative overflow-hidden" v-reveal="{ x: 24 }">
          <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full bg-[var(--color-brand)]/20 blur-2xl"></div>
          <h3 class="relative text-xl font-extrabold mb-5">
            <i class="fa-solid fa-store text-[var(--color-brand)] mr-2"></i> Voor merken
          </h3>
          <ul class="relative space-y-4" v-reveal="{ stagger: 0.08, y: 16 }">
            <li v-for="f in brandFeatures" :key="f.title" class="flex gap-3">
              <span class="w-9 h-9 shrink-0 rounded-xl bg-white/10 text-[var(--color-brand)] flex items-center justify-center">
                <i class="fa-solid" :class="f.icon"></i>
              </span>
              <div>
                <p class="font-semibold">{{ f.title }}</p>
                <p class="text-sm text-gray-300">{{ f.text }}</p>
              </div>
            </li>
          </ul>

          <div class="relative mt-6 rounded-2xl bg-white/5 border border-white/10 p-4 flex items-center justify-between gap-3">
            <div>
              <p class="font-semibold">Bekijk een voorbeeldrapport</p>
              <p class="text-xs text-gray-400">Zie precies wat je krijgt — met echte grafieken.</p>
            </div>
            <NuxtLink to="/voorbeeld-rapport" class="shrink-0 cta px-4 py-2 text-sm">Bekijken</NuxtLink>
          </div>

          <NuxtLink to="/become-a-brandowner" class="relative inline-flex items-center gap-1.5 mt-5 font-semibold text-[var(--color-brand)]">
            Word merkeigenaar <i class="fa-solid fa-arrow-right text-xs"></i>
          </NuxtLink>
        </div>
      </div>
    </div>
  </section>
</template>
