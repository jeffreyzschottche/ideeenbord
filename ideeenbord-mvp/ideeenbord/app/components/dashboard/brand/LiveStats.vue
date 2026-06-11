<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { Bar, Line, Doughnut } from "vue-chartjs";
import { reportService } from "~/services/api/brand/reportService";

const props = defineProps<{ brandId: number }>();

const metrics = ref<any | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);
const updatedAt = ref<Date | null>(null);

const ORANGE = "#f78a1d";
const ORANGE_SOFT = "rgba(247,138,29,0.18)";
const NAVY = "#1f2937";
const BLUE = "#3b82f6";
const GREEN = "#22c55e";
const RED = "#ef4444";
const PALETTE = [ORANGE, NAVY, BLUE, GREEN, "#a855f7", RED, "#14b8a6", "#eab308"];

const totals = computed(() => metrics.value?.totals ?? null);

async function load() {
  loading.value = true;
  error.value = null;
  try {
    metrics.value = await reportService.stats(props.brandId);
    updatedAt.value = new Date();
  } catch (e: any) {
    error.value = e?.message ?? "Statistieken ophalen mislukt.";
  } finally {
    loading.value = false;
  }
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { labels: { color: NAVY } } },
  scales: {
    x: { ticks: { color: NAVY }, grid: { display: false } },
    y: { ticks: { color: NAVY }, grid: { color: "rgba(0,0,0,0.05)" }, beginAtZero: true },
  },
};
const hBarOptions = { ...chartOptions, indexAxis: "y" as const };
const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: "bottom" as const, labels: { color: NAVY } } },
};

const statusChart = computed(() => {
  const s = metrics.value?.status_distribution ?? {};
  return {
    labels: ["In afwachting", "In behandeling", "Afgerond", "Afgewezen"],
    datasets: [
      {
        label: "Ideeën",
        backgroundColor: [ORANGE, BLUE, GREEN, RED],
        data: [s.pending ?? 0, s.in_progress ?? 0, s.completed ?? 0, s.rejected ?? 0],
        borderRadius: 6,
      },
    ],
  };
});

const engagementChart = computed(() => {
  const rows = metrics.value?.ideas_over_time ?? [];
  return {
    labels: rows.map((r: any) => r.month),
    datasets: [
      { label: "Ideeën", data: rows.map((r: any) => r.count), borderColor: ORANGE, backgroundColor: ORANGE_SOFT, fill: true, tension: 0.35, pointBackgroundColor: ORANGE },
      { label: "Likes", data: rows.map((r: any) => r.likes), borderColor: GREEN, backgroundColor: "transparent", tension: 0.35, pointBackgroundColor: GREEN },
      { label: "Dislikes", data: rows.map((r: any) => r.dislikes), borderColor: RED, backgroundColor: "transparent", tension: 0.35, pointBackgroundColor: RED },
    ],
  };
});

const topIdeasChart = computed(() => {
  const rows = metrics.value?.top_ideas_by_likes ?? [];
  return {
    labels: rows.map((r: any) => r.title),
    datasets: [
      { label: "Likes", backgroundColor: GREEN, data: rows.map((r: any) => r.likes), borderRadius: 6 },
      { label: "Dislikes", backgroundColor: RED, data: rows.map((r: any) => r.dislikes), borderRadius: 6 },
    ],
  };
});

const categoriesChart = computed(() => {
  const rows = metrics.value?.categories ?? [];
  return {
    labels: rows.map((r: any) => r.category),
    datasets: [{ label: "Ideeën", backgroundColor: ORANGE, data: rows.map((r: any) => r.count), borderRadius: 6 }],
  };
});

const ageChart = computed(() => {
  const rows = metrics.value?.demographics?.age_brackets ?? [];
  return {
    labels: rows.map((r: any) => r.label),
    datasets: [{ label: "Deelnemers", backgroundColor: NAVY, data: rows.map((r: any) => r.count), borderRadius: 6 }],
  };
});

const genderChart = computed(() => {
  const rows = metrics.value?.demographics?.gender ?? [];
  return { labels: rows.map((r: any) => r.label), datasets: [{ backgroundColor: PALETTE, data: rows.map((r: any) => r.count) }] };
});

const educationChart = computed(() => {
  const rows = metrics.value?.demographics?.education ?? [];
  return { labels: rows.map((r: any) => r.label), datasets: [{ backgroundColor: PALETTE, data: rows.map((r: any) => r.count) }] };
});

const sectorChart = computed(() => {
  const rows = metrics.value?.demographics?.sector ?? [];
  return { labels: rows.map((r: any) => r.label), datasets: [{ label: "Deelnemers", backgroundColor: BLUE, data: rows.map((r: any) => r.count), borderRadius: 6 }] };
});

const citiesChart = computed(() => {
  const rows = metrics.value?.demographics?.cities ?? [];
  return { labels: rows.map((r: any) => r.label), datasets: [{ label: "Deelnemers", backgroundColor: "#14b8a6", data: rows.map((r: any) => r.count), borderRadius: 6 }] };
});

const has = (rows: any) => Array.isArray(rows) && rows.length > 0;
const hasDemographics = computed(() => {
  const d = metrics.value?.demographics;
  return d && (d.gender?.length || d.age_brackets?.some((b: any) => b.count > 0));
});

onMounted(load);
</script>

<template>
  <div>
    <div class="flex items-center justify-between gap-3 mb-4">
      <p class="text-sm text-gray-500">
        <span v-if="updatedAt">Bijgewerkt: {{ updatedAt.toLocaleTimeString("nl-NL") }}</span>
        <span v-else>Live cijfers van je merk</span>
      </p>
      <button
        class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-semibold hover:bg-gray-50 disabled:opacity-60"
        :disabled="loading"
        @click="load"
      >
        <i class="fas fa-rotate-right mr-1"></i> Vernieuwen
      </button>
    </div>

    <p v-if="error" class="text-red-600 mb-4">{{ error }}</p>
    <p v-if="loading" class="text-gray-500">Laden…</p>

    <div v-if="!loading && totals" class="space-y-8">
      <!-- KPI cards -->
      <section class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold" style="color: var(--color-brand)">{{ totals.ideas }}</p>
          <p class="text-xs text-gray-600">Ideeën</p>
        </div>
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold" style="color: var(--color-nav)">{{ totals.participants }}</p>
          <p class="text-xs text-gray-600">Deelnemers</p>
        </div>
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold text-green-600">{{ totals.net_sentiment >= 0 ? "+" : "" }}{{ totals.net_sentiment }}</p>
          <p class="text-xs text-gray-600">Netto sentiment</p>
        </div>
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold" style="color: var(--color-nav)">{{ totals.main_question_responses }}</p>
          <p class="text-xs text-gray-600">Hoofdvraag-antwoorden</p>
        </div>
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold text-green-600">{{ totals.idea_likes }}</p>
          <p class="text-xs text-gray-600">Likes</p>
        </div>
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold text-red-600">{{ totals.idea_dislikes }}</p>
          <p class="text-xs text-gray-600">Dislikes</p>
        </div>
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold" style="color: var(--color-nav)">{{ totals.sentiment_ratio ?? "—" }}<span v-if="totals.sentiment_ratio" class="text-base">%</span></p>
          <p class="text-xs text-gray-600">Positief sentiment</p>
        </div>
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold" style="color: var(--color-nav)">{{ totals.avg_rating ?? "—" }}</p>
          <p class="text-xs text-gray-600">Gem. beoordeling</p>
        </div>
      </section>

      <!-- Charts -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="stat-chart">
          <h4 class="stat-chart-title">Ideeën, likes &amp; dislikes over tijd</h4>
          <div class="h-60"><Line :data="engagementChart" :options="chartOptions" /></div>
        </div>
        <div class="stat-chart">
          <h4 class="stat-chart-title">Status van ideeën</h4>
          <div class="h-60"><Bar :data="statusChart" :options="chartOptions" /></div>
        </div>
        <div v-if="has(metrics?.top_ideas_by_likes)" class="stat-chart">
          <h4 class="stat-chart-title">Best ontvangen ideeën</h4>
          <div class="h-72"><Bar :data="topIdeasChart" :options="chartOptions" /></div>
        </div>
        <div v-if="has(metrics?.categories)" class="stat-chart">
          <h4 class="stat-chart-title">Categorieën van ideeën</h4>
          <div class="h-64"><Bar :data="categoriesChart" :options="chartOptions" /></div>
        </div>
        <div v-if="hasDemographics" class="stat-chart">
          <h4 class="stat-chart-title">Leeftijdsopbouw</h4>
          <div class="h-56"><Bar :data="ageChart" :options="chartOptions" /></div>
        </div>
        <div v-if="genderChart.labels.length" class="stat-chart">
          <h4 class="stat-chart-title">Geslacht</h4>
          <div class="h-56"><Doughnut :data="genderChart" :options="doughnutOptions" /></div>
        </div>
        <div v-if="educationChart.labels.length" class="stat-chart">
          <h4 class="stat-chart-title">Opleidingsniveau</h4>
          <div class="h-56"><Doughnut :data="educationChart" :options="doughnutOptions" /></div>
        </div>
        <div v-if="sectorChart.labels.length" class="stat-chart">
          <h4 class="stat-chart-title">Sector</h4>
          <div class="h-56"><Bar :data="sectorChart" :options="hBarOptions" /></div>
        </div>
        <div v-if="citiesChart.labels.length" class="stat-chart">
          <h4 class="stat-chart-title">Steden</h4>
          <div class="h-56"><Bar :data="citiesChart" :options="hBarOptions" /></div>
        </div>
      </section>
    </div>

    <div v-else-if="!loading && !error" class="text-gray-500">
      Er is nog geen data om te tonen.
    </div>
  </div>
</template>

<style scoped>
.stat-chart {
  border: 1px solid #f3f4f6;
  border-radius: 1rem;
  padding: 1rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
.stat-chart-title {
  font-weight: 600;
  font-size: 0.875rem;
  margin-bottom: 0.75rem;
  color: var(--color-nav);
}
</style>
