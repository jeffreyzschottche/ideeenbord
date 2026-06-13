<script setup lang="ts">
usePageSeo({
  title: "Voorbeeldrapport",
  description:
    "Bekijk een voorbeeld van een AI-merkrapport op Ideeënbord: kerncijfers, grafieken, thema's, persona's en concrete aanbevelingen op basis van klantideeën.",
});
</script>

<template>
  <div class="bg-[var(--color-bg)] min-h-screen">
    <!-- Hero -->
    <section class="bg-[var(--color-nav)] text-white">
      <div class="max-w-5xl mx-auto px-4 py-14 md:py-20 text-center" v-reveal="{ y: 24 }">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
          Zo ziet het eruit
        </p>
        <h1 class="text-3xl md:text-5xl font-extrabold leading-tight">
          Een merkrapport dat je écht verder helpt
        </h1>
        <p class="mt-4 text-gray-300 max-w-2xl mx-auto text-lg">
          Op basis van de ideeën, stemmen en antwoorden van jouw klanten genereert Ideeënbord een
          diepgaand adviesrapport — met cijfers, grafieken, thema's, persona's en concrete acties.
          Hieronder een volledig voorbeeld met fictieve data.
        </p>
        <div class="mt-7 flex flex-wrap gap-3 justify-center">
          <NuxtLink to="/become-a-brandowner" class="cta px-6 py-3">Claim je merk</NuxtLink>
          <NuxtLink to="/subscriptions" class="px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 font-semibold transition">
            Bekijk het abonnement
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Voorbeeldrapport -->
    <section class="max-w-5xl mx-auto px-4 py-10 md:py-14">
      <div class="rounded-3xl bg-white border border-gray-100 shadow-[0_8px_30px_rgba(31,41,55,0.08)] p-5 md:p-8">
        <client-only>
          <SampleReport />
          <template #fallback>
            <p class="text-gray-400 text-center py-10">Voorbeeldrapport laden…</p>
          </template>
        </client-only>
      </div>

      <p class="text-center text-sm text-gray-500 mt-6">
        Dit is een voorbeeld met verzonnen data. Jouw rapport wordt opgebouwd uit echte klantinput.
      </p>
    </section>
  </div>
</template>
