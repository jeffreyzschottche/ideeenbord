<script setup lang="ts">
/*
  Zelfstandig VOORBEELDRAPPORT met verzonnen (maar realistische) data en echte
  grafieken. Geen API/AI — puur om te tonen wat een Ideeënbord-rapport biedt.
*/
import { Bar, Line, Doughnut } from "vue-chartjs";

const ORANGE = "#f78a1d";
const NAVY = "#1f2937";
const BLUE = "#3b82f6";
const GREEN = "#22c55e";
const RED = "#ef4444";
const FONT = { family: "Poppins, sans-serif" } as const;

const opts = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { labels: { color: NAVY, usePointStyle: true, boxWidth: 8, font: FONT } } },
  scales: {
    x: { ticks: { color: "#6b7280", font: FONT }, grid: { display: false } },
    y: { ticks: { color: "#6b7280", font: FONT }, grid: { color: "rgba(0,0,0,0.05)" }, beginAtZero: true },
  },
};
const doughnutOpts = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: "62%",
  plugins: { legend: { position: "bottom" as const, labels: { color: NAVY, usePointStyle: true, boxWidth: 8, font: FONT } } },
};

const kpis = [
  { label: "Ideeën", value: 142, icon: "fa-lightbulb", accent: ORANGE, delta: "+18%" },
  { label: "Deelnemers", value: 89, icon: "fa-users", accent: NAVY, delta: "+11%" },
  { label: "Netto sentiment", value: "+213", icon: "fa-heart", accent: GREEN, delta: "+24%" },
  { label: "Positief sentiment", value: "84%", icon: "fa-thumbs-up", accent: GREEN, delta: null },
];

const engagement = {
  labels: ["Dec", "Jan", "Feb", "Mrt", "Apr", "Mei"],
  datasets: [
    { label: "Ideeën", data: [12, 18, 22, 26, 31, 33], borderColor: ORANGE, backgroundColor: "rgba(247,138,29,0.18)", fill: true, tension: 0.35, pointBackgroundColor: ORANGE },
    { label: "Likes", data: [30, 52, 64, 88, 120, 142], borderColor: GREEN, backgroundColor: "transparent", tension: 0.35, pointBackgroundColor: GREEN },
  ],
};
const status = {
  labels: ["In afwachting", "In behandeling", "Afgerond", "Afgewezen"],
  datasets: [{ label: "Ideeën", backgroundColor: [ORANGE, BLUE, GREEN, RED], data: [40, 30, 60, 12], borderRadius: 6 }],
};
const ages = {
  labels: ["18-24", "25-34", "35-44", "45-54", "55+"],
  datasets: [{ backgroundColor: [ORANGE, NAVY, BLUE, GREEN, "#a855f7"], data: [22, 34, 18, 9, 6] }],
};

const themes = [
  { theme: "Plantaardig assortiment", sentiment: "positief", desc: "Veel vraag naar meer vegan opties en haverdranken — consistent hoog gewaardeerd." },
  { theme: "Snelheid in de spits", sentiment: "negatief", desc: "Klanten ervaren lange wachttijden tussen 8:00 en 9:30; meest genoemde frustratie." },
  { theme: "Loyaliteit & sparen", sentiment: "gemengd", desc: "Enthousiasme voor een spaarprogramma, maar verdeeldheid over de vorm (app vs. kaart)." },
];
const quickWins = [
  "Introduceer deze week een haverdrank-optie — meest geliket idee (47 likes).",
  "Plaats een tweede pinzuil in de ochtendspits om wachttijd te halveren.",
  "Peil via de hoofdvraag of klanten een app- of kaart-spaarprogramma willen.",
];
</script>

<template>
  <div class="sr">
    <!-- Titelblok -->
    <header class="sr-hero">
      <p class="sr-hero__eyebrow"><i class="fa-solid fa-chart-pie"></i> Voorbeeld merkrapport · Mei 2026</p>
      <h2 class="sr-hero__title">Koffiebar Lumen</h2>
      <p class="sr-hero__meta">Gegenereerd op 1 juni 2026 · op basis van 142 ideeën van 89 klanten</p>
    </header>

    <!-- KPI's -->
    <section class="sr-kpis">
      <div v-for="k in kpis" :key="k.label" class="sr-kpi">
        <span class="sr-kpi__icon" :style="{ background: k.accent }"><i class="fa-solid" :class="k.icon"></i></span>
        <div>
          <p class="sr-kpi__value">{{ k.value }}</p>
          <p class="sr-kpi__label">{{ k.label }}</p>
        </div>
        <span v-if="k.delta" class="sr-kpi__delta"><i class="fa-solid fa-arrow-trend-up"></i> {{ k.delta }}</span>
      </div>
    </section>

    <!-- Gezondheid + samenvatting -->
    <section class="grid md:grid-cols-3 gap-5">
      <div class="sr-health">
        <div class="sr-health__ring" :style="{ background: `conic-gradient(${GREEN} 0% 78%, #eef0f3 78% 100%)` }">
          <div class="sr-health__center"><span class="sr-health__score">78</span><span class="sr-health__max">/100</span></div>
        </div>
        <p class="sr-health__label">Merkgezondheid: <strong>Sterk</strong></p>
      </div>
      <div class="md:col-span-2 sr-lead">
        <h3 class="sr-h3">Managementsamenvatting</h3>
        <p>Lumen kent een sterk en groeiend engagement: in mei deelden 89 klanten 142 ideeën, met een
          overwegend positief sentiment (84%). De grootste kansen liggen in een uitgebreider plantaardig
          assortiment en het verkorten van de wachttijd in de ochtendspits. Een loyaliteitsprogramma leeft
          sterk, maar de vorm verdient eerst een gerichte peiling.</p>
      </div>
    </section>

    <!-- Quick wins -->
    <section class="sr-quickwins">
      <p class="sr-quickwins__title"><i class="fa-solid fa-bolt"></i> Quick wins — direct aan de slag</p>
      <div class="grid sm:grid-cols-3 gap-3">
        <div v-for="(q, i) in quickWins" :key="i" class="sr-quickwin"><span class="sr-quickwin__num">{{ i + 1 }}</span><p>{{ q }}</p></div>
      </div>
    </section>

    <!-- Grafieken -->
    <section class="grid lg:grid-cols-2 gap-5">
      <div class="sr-card"><p class="sr-card__title"><i class="fa-solid fa-chart-line"></i> Ideeën &amp; likes over tijd</p><div class="h-60"><Line :data="engagement" :options="opts" /></div></div>
      <div class="sr-card"><p class="sr-card__title"><i class="fa-solid fa-list-check"></i> Status van ideeën</p><div class="h-60"><Bar :data="status" :options="opts" /></div></div>
      <div class="sr-card"><p class="sr-card__title"><i class="fa-solid fa-cake-candles"></i> Leeftijdsopbouw deelnemers</p><div class="h-60"><Doughnut :data="ages" :options="doughnutOpts" /></div></div>
      <div class="sr-card sr-card--quote">
        <p class="sr-card__title"><i class="fa-solid fa-user-tag"></i> Persona</p>
        <p class="font-bold text-[var(--color-nav)] mt-1">Bewuste Bo (29)</p>
        <p class="text-xs text-gray-500 mb-1">HBO · stedelijk · komt 3× per week</p>
        <p class="text-sm text-gray-600">Hecht aan duurzaamheid en snelheid. Deelt graag ideeën als ze merkt dat
          het merk er écht iets mee doet. Gevoelig voor een goed spaarprogramma.</p>
      </div>
    </section>

    <!-- Thema's -->
    <section>
      <h3 class="sr-h3 mb-3">Thema's in de ideeën</h3>
      <div class="grid md:grid-cols-3 gap-4">
        <div v-for="(t, i) in themes" :key="i" class="sr-theme" :data-s="t.sentiment">
          <div class="flex items-center justify-between mb-1.5">
            <h4 class="font-bold text-[var(--color-nav)]">{{ t.theme }}</h4>
            <span class="sr-badge" :class="`is-${t.sentiment}`">{{ t.sentiment }}</span>
          </div>
          <p class="text-sm text-gray-600">{{ t.desc }}</p>
        </div>
      </div>
    </section>

    <!-- Kansen / risico's -->
    <section class="grid md:grid-cols-2 gap-4">
      <div class="sr-callout is-pos">
        <p class="sr-callout__title"><i class="fa-solid fa-arrow-trend-up"></i> Kansen</p>
        <ul>
          <li><i class="fa-solid fa-check"></i> Plantaardig assortiment uitbreiden — duidelijke, herhaalde vraag.</li>
          <li><i class="fa-solid fa-check"></i> Loyaliteitsprogramma lanceren na korte peiling.</li>
        </ul>
      </div>
      <div class="sr-callout is-neg">
        <p class="sr-callout__title"><i class="fa-solid fa-triangle-exclamation"></i> Aandachtspunten</p>
        <ul>
          <li><i class="fa-solid fa-exclamation"></i> Wachttijd in de ochtendspits drukt het sentiment.</li>
          <li><i class="fa-solid fa-exclamation"></i> 12 afgewezen ideeën zonder terugkoppeling — licht dit toe.</li>
        </ul>
      </div>
    </section>

    <!-- Aanbeveling -->
    <section>
      <h3 class="sr-h3 mb-3">Aanbeveling</h3>
      <div class="sr-rec">
        <div class="flex items-center justify-between mb-1">
          <h4 class="font-bold text-[var(--color-nav)]">Start een “Lumen Luistert”-spaarprogramma</h4>
          <span class="sr-rec__badge">prioriteit hoog</span>
        </div>
        <p class="text-sm text-gray-700">Combineer de twee sterkste signalen — loyaliteit en duurzaamheid — in
          één programma dat plantaardige keuzes beloont.</p>
        <p class="text-xs text-gray-500 mt-1.5"><i class="fa-solid fa-bullseye text-[var(--color-brand)]"></i>
          <strong> Verwachte impact:</strong> hogere terugkeerfrequentie en meetbaar positiever sentiment.</p>
      </div>
    </section>
  </div>
</template>

<style scoped>
.sr { font-family: var(--font-default); display: flex; flex-direction: column; gap: 2rem; }
.sr-h3 { font-size: 1.2rem; font-weight: 800; color: var(--color-nav); }
.sr-hero { position: relative; overflow: hidden; border-radius: 1.25rem; padding: 1.75rem 2rem; color: #fff; background: linear-gradient(135deg, var(--color-nav), #111827); }
.sr-hero__eyebrow { text-transform: uppercase; letter-spacing: .16em; font-size: .72rem; font-weight: 700; color: var(--color-brand); }
.sr-hero__title { font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; margin: .35rem 0 .3rem; }
.sr-hero__meta { font-size: .82rem; color: rgba(255,255,255,.6); }
.sr-kpis { display: grid; grid-template-columns: repeat(2,1fr); gap: .75rem; }
@media (min-width:768px){ .sr-kpis { grid-template-columns: repeat(4,1fr); } }
.sr-kpi { position: relative; display: flex; align-items: center; gap: .75rem; background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: .9rem 1rem; box-shadow: 0 4px 14px rgba(31,41,55,.05); }
.sr-kpi__icon { width: 2.25rem; height: 2.25rem; border-radius: .7rem; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .85rem; flex-shrink: 0; }
.sr-kpi__value { font-size: 1.5rem; font-weight: 800; color: var(--color-nav); line-height: 1; }
.sr-kpi__label { font-size: .72rem; color: #6b7280; margin-top: .2rem; }
.sr-kpi__delta { position: absolute; top: .55rem; right: .55rem; font-size: .65rem; font-weight: 700; padding: .1rem .4rem; border-radius: 999px; background: #dcfce7; color: #15803d; }
.sr-health { background: #fff; border: 1px solid #eef0f3; border-radius: 1.25rem; padding: 1.25rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .75rem; box-shadow: 0 4px 14px rgba(31,41,55,.05); }
.sr-health__ring { width: 9rem; height: 9rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.sr-health__center { width: 6.6rem; height: 6.6rem; border-radius: 50%; background: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.sr-health__score { font-size: 2.1rem; font-weight: 800; color: #22c55e; line-height: 1; }
.sr-health__max { font-size: .7rem; color: #9ca3af; }
.sr-health__label { font-size: .95rem; color: #374151; }
.sr-lead { border-left: 4px solid var(--color-brand); background: var(--color-bg); border-radius: 0 1rem 1rem 0; padding: 1.1rem 1.25rem; }
.sr-lead p { color: #374151; line-height: 1.7; margin-top: .4rem; }
.sr-quickwins { border-radius: 1.25rem; padding: 1.25rem 1.5rem; background: linear-gradient(135deg, rgba(247,138,29,.1), rgba(247,138,29,.03)); border: 1px solid rgba(247,138,29,.25); }
.sr-quickwins__title { font-weight: 800; color: var(--color-nav); margin-bottom: .85rem; display: flex; align-items: center; gap: .5rem; }
.sr-quickwins__title i { color: var(--color-brand); }
.sr-quickwin { display: flex; gap: .7rem; background: #fff; border: 1px solid rgba(247,138,29,.2); border-radius: .85rem; padding: .85rem .95rem; }
.sr-quickwin__num { width: 1.6rem; height: 1.6rem; flex-shrink: 0; border-radius: 50%; background: var(--color-brand); color: #fff; font-weight: 800; font-size: .8rem; display: flex; align-items: center; justify-content: center; }
.sr-quickwin p { font-size: .88rem; color: #374151; line-height: 1.45; }
.sr-card { min-width: 0; border: 1px solid #eef0f3; border-radius: 1rem; padding: 1.1rem; background: #fff; box-shadow: 0 4px 14px rgba(31,41,55,.04); }
.sr-card canvas { max-width: 100% !important; }
.sr-health, .sr-lead { min-width: 0; }
.sr-card__title { font-weight: 700; font-size: .85rem; color: var(--color-nav); margin-bottom: .85rem; display: flex; align-items: center; gap: .45rem; }
.sr-card__title i { color: var(--color-brand); }
.sr-card--quote { display: flex; flex-direction: column; }
.sr-theme { border: 1px solid #eef0f3; border-top: 3px solid #e5e7eb; border-radius: 1rem; padding: 1.1rem; background: #fff; box-shadow: 0 2px 8px rgba(31,41,55,.04); }
.sr-theme[data-s="positief"] { border-top-color: #22c55e; }
.sr-theme[data-s="negatief"] { border-top-color: #ef4444; }
.sr-theme[data-s="gemengd"] { border-top-color: #f59e0b; }
.sr-badge { font-size: .68rem; font-weight: 700; padding: .15rem .55rem; border-radius: 999px; text-transform: capitalize; }
.sr-badge.is-positief { background: #dcfce7; color: #15803d; }
.sr-badge.is-negatief { background: #fee2e2; color: #b91c1c; }
.sr-badge.is-gemengd { background: #fef3c7; color: #b45309; }
.sr-callout { border-radius: 1rem; padding: 1.1rem 1.25rem; }
.sr-callout.is-pos { background: #f0fdf4; border: 1px solid #bbf7d0; }
.sr-callout.is-neg { background: #fef2f2; border: 1px solid #fecaca; }
.sr-callout__title { font-weight: 800; margin-bottom: .6rem; display: flex; align-items: center; gap: .5rem; }
.sr-callout.is-pos .sr-callout__title { color: #15803d; }
.sr-callout.is-neg .sr-callout__title { color: #b91c1c; }
.sr-callout ul { display: grid; gap: .5rem; }
.sr-callout li { display: flex; gap: .55rem; font-size: .9rem; color: #374151; }
.sr-callout.is-pos li i { color: #22c55e; margin-top: .2rem; }
.sr-callout.is-neg li i { color: #ef4444; margin-top: .2rem; }
.sr-rec { border: 1px solid #eef0f3; border-left: 5px solid #ef4444; border-radius: .9rem; padding: 1rem 1.15rem; background: #fff; box-shadow: 0 2px 8px rgba(31,41,55,.04); }
.sr-rec__badge { color: #fff; font-size: .65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; padding: .2rem .55rem; border-radius: 999px; background: #ef4444; }
</style>
