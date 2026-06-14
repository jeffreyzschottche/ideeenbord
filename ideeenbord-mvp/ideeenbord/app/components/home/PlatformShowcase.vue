<script setup lang="ts">
/*
  "Wat kun je doen op Ideeënbord" — toont de mogelijkheden voor zowel bezoekers
  als merken, met concrete voorbeelddata. Geen CMS/API; puur showcase.
*/
const userFeatures = [
  { icon: "fa-bullhorn", title: "Jouw stem telt écht", text: "Geen enquête die in de la verdwijnt — merken zien en reageren op jouw input." },
  { icon: "fa-lightbulb", title: "Deel concrete ideeën", text: "\"Maak langere openingstijden\" of \"Voeg vegan opties toe\" — alles mag." },
  { icon: "fa-users", title: "Samen sterker", text: "Hoe meer mensen stemmen op jouw idee, hoe groter de kans dat het gebeurt." },
  { icon: "fa-eye", title: "Volg de voortgang", text: "Zie wanneer een merk je idee bekijkt, overweegt of realiseert." },
];
const brandFeatures = [
  { icon: "fa-comments", title: "Directe feedback van klanten", text: "Geen dure focusgroepen — hoor wat je doelgroep écht wil." },
  { icon: "fa-chart-pie", title: "Slimme AI-rapporten", text: "Automatische analyses met trends, persona's en concrete aanbevelingen." },
  { icon: "fa-bolt", title: "Sneller innoveren", text: "Valideer ideeën voordat je investeert — bespaar tijd en geld." },
  { icon: "fa-heart", title: "Bouw klantloyaliteit", text: "Klanten voelen zich gehoord en blijven langer trouw." },
];

const sampleIdea = {
  title: "Langere openingstijden in het weekend",
  brand: "Bakkerij Zondag",
  likes: 89,
  dislikes: 7,
  status: "Gerealiseerd",
};

const impactStats = [
  { value: "127", label: "ideeën gerealiseerd" },
  { value: "21", label: "dagen gemiddeld" },
  { value: "94%", label: "tevreden merken" },
];
</script>

<template>
  <section class="py-16 md:py-24">
    <div class="container mx-auto px-4">
      <div class="max-w-2xl mx-auto text-center mb-12" v-reveal="{ y: 20 }">
        <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
          Twee kanten, één platform
        </span>
        <h2 class="text-3xl md:text-4xl font-extrabold text-[var(--color-nav)]">
          Consumenten krijgen een stem.<br class="hidden sm:block" />
          Merken krijgen inzicht.
        </h2>
        <p class="mt-4 text-lg text-gray-600">
          Gratis voor consumenten. Waardevol voor bedrijven.
        </p>
      </div>

      <!-- Impact stats -->
      <div class="flex flex-wrap justify-center gap-6 md:gap-12 mb-12" v-reveal="{ y: 16 }">
        <div v-for="stat in impactStats" :key="stat.label" class="text-center">
          <p class="text-3xl md:text-4xl font-extrabold text-[var(--color-brand)]">{{ stat.value }}</p>
          <p class="text-sm text-gray-500 mt-1">{{ stat.label }}</p>
        </div>
      </div>

      <div class="grid lg:grid-cols-2 gap-6 max-w-5xl mx-auto">
        <!-- Voor consumenten -->
        <div class="rounded-3xl bg-white border border-gray-100 shadow-[0_8px_30px_rgba(31,41,55,0.06)] p-7" v-reveal="{ x: -24 }">
          <div class="flex items-center gap-3 mb-2">
            <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold uppercase">Gratis</span>
          </div>
          <h3 class="text-xl font-extrabold text-[var(--color-nav)] mb-2">
            <i class="fa-solid fa-user text-[var(--color-brand)] mr-2"></i> Jouw macht als consument
          </h3>
          <p class="text-gray-600 mb-5">Eindelijk een manier om écht gehoord te worden door je favoriete merken.</p>

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

          <!-- Voorbeeld succesverhaal -->
          <div class="mt-6 rounded-2xl border-2 border-green-200 bg-green-50 p-4">
            <div class="flex items-center gap-2 mb-2">
              <i class="fa-solid fa-check-circle text-green-500"></i>
              <p class="text-xs font-bold uppercase tracking-wide text-green-700">Succesverhaal</p>
            </div>
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-semibold text-[var(--color-nav)]">{{ sampleIdea.title }}</p>
                <p class="text-xs text-gray-500">bij {{ sampleIdea.brand }}</p>
              </div>
              <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-green-200 text-green-800 whitespace-nowrap">{{ sampleIdea.status }}</span>
            </div>
            <div class="flex items-center gap-4 mt-3 text-sm">
              <span class="inline-flex items-center gap-1.5 text-green-600 font-semibold"><i class="fa-solid fa-thumbs-up"></i> {{ sampleIdea.likes }}</span>
              <span class="text-gray-400 text-xs">89 mensen kregen waar ze om vroegen</span>
            </div>
          </div>

          <NuxtLink to="/register" class="cta inline-flex items-center gap-2 mt-5 px-5 py-2.5">
            Gratis aanmelden <i class="fa-solid fa-arrow-right text-xs"></i>
          </NuxtLink>
        </div>

        <!-- Voor merken -->
        <div class="rounded-3xl bg-[var(--color-nav)] text-white shadow-[0_8px_30px_rgba(31,41,55,0.18)] p-7 relative overflow-hidden" v-reveal="{ x: 24 }">
          <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full bg-[var(--color-brand)]/20 blur-2xl"></div>

          <div class="relative flex items-center gap-3 mb-2">
            <span class="px-3 py-1 rounded-full bg-[var(--color-brand)] text-white text-xs font-bold uppercase">Vanaf €49/maand</span>
          </div>
          <h3 class="relative text-xl font-extrabold mb-2">
            <i class="fa-solid fa-store text-[var(--color-brand)] mr-2"></i> Wat je koopt als merk
          </h3>
          <p class="text-gray-300 mb-5">Directe toegang tot de mening van je doelgroep — zonder dure marktonderzoeken.</p>

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

          <!-- Sample report CTA - prominenter -->
          <div class="relative mt-6 rounded-2xl bg-gradient-to-r from-[var(--color-brand)]/20 to-amber-500/20 border border-[var(--color-brand)]/30 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row items-start gap-3 sm:gap-4">
              <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-[var(--color-brand)] flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-file-lines text-white text-base sm:text-lg"></i>
              </div>
              <div class="flex-1">
                <p class="font-bold text-base sm:text-lg">Bekijk een voorbeeldrapport</p>
                <p class="text-sm text-gray-300 mt-1">Zie exact welke inzichten je krijgt: trends, doelgroepanalyse en concrete aanbevelingen.</p>
                <NuxtLink to="/voorbeeld-rapport" class="inline-flex items-center gap-2 mt-3 px-4 py-2 bg-white text-[var(--color-nav)] rounded-lg font-semibold text-sm hover:bg-gray-100 transition">
                  <i class="fa-solid fa-eye"></i>
                  Rapport bekijken
                </NuxtLink>
              </div>
            </div>
          </div>

          <NuxtLink to="/become-a-brandowner" class="relative inline-flex items-center gap-1.5 mt-5 font-semibold text-[var(--color-brand)]">
            Meer over onze pakketten <i class="fa-solid fa-arrow-right text-xs"></i>
          </NuxtLink>
        </div>
      </div>
    </div>
  </section>
</template>
