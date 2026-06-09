<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { Bar, Line, Doughnut } from "vue-chartjs";
import {
  reportService,
  type ReportSummary,
  type BrandReport,
} from "~/services/api/brand/reportService";

const props = defineProps<{ brandId: number }>();

const reports = ref<ReportSummary[]>([]);
const current = ref<BrandReport | null>(null);
const loading = ref(true);
const generating = ref(false);
const error = ref<string | null>(null);

const ORANGE = "#f78a1d";
const ORANGE_SOFT = "rgba(247,138,29,0.18)";
const NAVY = "#1f2937";
const GREEN = "#22c55e";
const RED = "#ef4444";
const PALETTE = [ORANGE, NAVY, "#3b82f6", "#22c55e", "#a855f7", "#ef4444", "#14b8a6", "#eab308"];

const totals = computed(() => current.value?.metrics?.totals ?? null);
const ai = computed(() => current.value?.ai ?? null);

async function loadList() {
  loading.value = true;
  error.value = null;
  try {
    reports.value = await reportService.list(props.brandId);
    const latest = reports.value.find((r) => r.status === "completed");
    if (latest) current.value = await reportService.get(latest.id);
  } catch (e: any) {
    error.value = e?.message ?? "Rapporten ophalen mislukt.";
  } finally {
    loading.value = false;
  }
}

async function generate() {
  generating.value = true;
  error.value = null;
  try {
    current.value = await reportService.generate(props.brandId);
    reports.value = await reportService.list(props.brandId);
  } catch (e: any) {
    error.value = e?.message ?? "Rapport genereren mislukt.";
  } finally {
    generating.value = false;
  }
}

async function select(id: number) {
  loading.value = true;
  try {
    current.value = await reportService.get(id);
  } finally {
    loading.value = false;
  }
}

function exportPdf() {
  document.body.classList.add("print-report");
  const done = () => {
    document.body.classList.remove("print-report");
    window.removeEventListener("afterprint", done);
  };
  window.addEventListener("afterprint", done);
  window.print();
}

function fmtDate(d: string | null) {
  if (!d) return "";
  return new Date(d).toLocaleString("nl-NL", {
    day: "numeric",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
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
const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: "bottom" as const, labels: { color: NAVY } } },
};

const statusChart = computed(() => {
  const s = current.value?.metrics?.status_distribution ?? {};
  return {
    labels: ["In afwachting", "In behandeling", "Afgerond", "Afgewezen"],
    datasets: [
      {
        label: "Ideeën",
        backgroundColor: [ORANGE, "#3b82f6", GREEN, RED],
        data: [s.pending ?? 0, s.in_progress ?? 0, s.completed ?? 0, s.rejected ?? 0],
        borderRadius: 6,
      },
    ],
  };
});

const overTimeChart = computed(() => {
  const rows = current.value?.metrics?.ideas_over_time ?? [];
  return {
    labels: rows.map((r: any) => r.month),
    datasets: [
      {
        label: "Ideeën",
        data: rows.map((r: any) => r.count),
        borderColor: ORANGE,
        backgroundColor: ORANGE_SOFT,
        fill: true,
        tension: 0.35,
        pointBackgroundColor: ORANGE,
      },
    ],
  };
});

const topIdeasChart = computed(() => {
  const rows = current.value?.metrics?.top_ideas_by_likes ?? [];
  return {
    labels: rows.map((r: any) => r.title),
    datasets: [
      { label: "Likes", backgroundColor: GREEN, data: rows.map((r: any) => r.likes), borderRadius: 6 },
      { label: "Dislikes", backgroundColor: RED, data: rows.map((r: any) => r.dislikes), borderRadius: 6 },
    ],
  };
});

const ageChart = computed(() => {
  const rows = current.value?.metrics?.demographics?.age_brackets ?? [];
  return {
    labels: rows.map((r: any) => r.label),
    datasets: [{ label: "Deelnemers", backgroundColor: NAVY, data: rows.map((r: any) => r.count), borderRadius: 6 }],
  };
});

const genderChart = computed(() => {
  const rows = current.value?.metrics?.demographics?.gender ?? [];
  return {
    labels: rows.map((r: any) => r.label),
    datasets: [{ backgroundColor: PALETTE, data: rows.map((r: any) => r.count) }],
  };
});

const hasDemographics = computed(() => {
  const d = current.value?.metrics?.demographics;
  return d && (d.gender?.length || d.age_brackets?.some((b: any) => b.count > 0));
});

onMounted(loadList);
</script>

<template>
  <div>
    <!-- Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
      <div class="flex items-center gap-3">
        <select
          v-if="reports.length"
          class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
          :value="current?.id"
          @change="select(Number(($event.target as HTMLSelectElement).value))"
        >
          <option v-for="r in reports" :key="r.id" :value="r.id">
            {{ r.title }} ({{ r.status }})
          </option>
        </select>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="current?.status === 'completed'"
          class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-semibold hover:bg-gray-50"
          @click="exportPdf"
        >
          <i class="fas fa-file-pdf mr-1"></i> Download PDF
        </button>
        <button
          class="px-4 py-2 rounded-lg text-white text-sm font-semibold bg-gradient-to-b from-orange-400 to-orange-600 disabled:opacity-60"
          :disabled="generating"
          @click="generate"
        >
          <span v-if="generating"><i class="fas fa-spinner fa-spin mr-1"></i> Genereren…</span>
          <span v-else><i class="fas fa-wand-magic-sparkles mr-1"></i> Nieuw rapport genereren</span>
        </button>
      </div>
    </div>

    <p v-if="error" class="text-[var(--color-error)] mb-4">{{ error }}</p>
    <p v-if="loading" class="text-gray-500">Laden…</p>

    <div
      v-if="!loading && !current"
      class="text-center py-16 border border-dashed border-gray-300 rounded-2xl"
    >
      <p class="text-5xl mb-3">📊</p>
      <p class="text-gray-600 mb-1 font-semibold">Nog geen rapport</p>
      <p class="text-gray-500 text-sm">
        Genereer een AI-rapport met analyses en aanbevelingen op basis van jouw merkdata.
      </p>
    </div>

    <!-- Report -->
    <div v-if="current?.status === 'completed'" class="report-printable space-y-8">
      <header class="border-b border-gray-200 pb-4">
        <h2 class="text-2xl font-bold" style="color: var(--color-nav)">{{ current.title }}</h2>
        <p class="text-sm text-gray-500">
          Gegenereerd op {{ fmtDate(current.generated_at) }}
          <span v-if="current.model"> · {{ current.model }}</span>
        </p>
      </header>

      <!-- KPI cards -->
      <section v-if="totals" class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold" style="color: var(--color-brand)">{{ totals.ideas }}</p>
          <p class="text-xs text-gray-600">Ideeën</p>
        </div>
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold" style="color: var(--color-nav)">{{ totals.participants }}</p>
          <p class="text-xs text-gray-600">Deelnemers</p>
        </div>
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold text-green-600">+{{ totals.net_sentiment }}</p>
          <p class="text-xs text-gray-600">Netto sentiment</p>
        </div>
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold" style="color: var(--color-nav)">
            {{ totals.avg_rating ?? "—" }}
          </p>
          <p class="text-xs text-gray-600">Gem. beoordeling</p>
        </div>
      </section>

      <!-- Executive summary -->
      <section v-if="ai">
        <h3 class="text-lg font-bold mb-2" style="color: var(--color-nav)">Samenvatting</h3>
        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ ai.executive_summary }}</p>
        <ul v-if="ai.key_findings?.length" class="mt-3 space-y-1">
          <li v-for="(k, i) in ai.key_findings" :key="i" class="flex gap-2 text-gray-700">
            <span style="color: var(--color-brand)">▪</span><span>{{ k }}</span>
          </li>
        </ul>
      </section>

      <!-- Charts -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-gray-100 p-4 shadow-sm">
          <h4 class="font-semibold mb-3 text-sm" style="color: var(--color-nav)">Status van ideeën</h4>
          <div class="h-56"><Bar :data="statusChart" :options="chartOptions" /></div>
        </div>
        <div class="rounded-2xl border border-gray-100 p-4 shadow-sm">
          <h4 class="font-semibold mb-3 text-sm" style="color: var(--color-nav)">Ideeën over tijd</h4>
          <div class="h-56"><Line :data="overTimeChart" :options="chartOptions" /></div>
        </div>
        <div class="rounded-2xl border border-gray-100 p-4 shadow-sm">
          <h4 class="font-semibold mb-3 text-sm" style="color: var(--color-nav)">Best ontvangen ideeën</h4>
          <div class="h-56"><Bar :data="topIdeasChart" :options="chartOptions" /></div>
        </div>
        <div v-if="hasDemographics" class="rounded-2xl border border-gray-100 p-4 shadow-sm">
          <h4 class="font-semibold mb-3 text-sm" style="color: var(--color-nav)">Leeftijdsopbouw</h4>
          <div class="h-56"><Bar :data="ageChart" :options="chartOptions" /></div>
        </div>
        <div v-if="genderChart.labels.length" class="rounded-2xl border border-gray-100 p-4 shadow-sm">
          <h4 class="font-semibold mb-3 text-sm" style="color: var(--color-nav)">Geslacht</h4>
          <div class="h-56"><Doughnut :data="genderChart" :options="doughnutOptions" /></div>
        </div>
      </section>

      <!-- Themes -->
      <section v-if="ai?.idea_themes?.length">
        <h3 class="text-lg font-bold mb-3" style="color: var(--color-nav)">Thema's in de ideeën</h3>
        <div class="grid md:grid-cols-2 gap-3">
          <div v-for="(t, i) in ai.idea_themes" :key="i" class="rounded-xl border border-gray-100 p-4">
            <div class="flex items-center justify-between mb-1">
              <h4 class="font-semibold" style="color: var(--color-nav)">{{ t.theme }}</h4>
              <span
                class="text-xs px-2 py-0.5 rounded-full"
                :class="{
                  'bg-green-100 text-green-700': t.sentiment === 'positief',
                  'bg-red-100 text-red-700': t.sentiment === 'negatief',
                  'bg-gray-100 text-gray-600': t.sentiment === 'gemengd',
                }"
              >{{ t.sentiment }}</span>
            </div>
            <p class="text-sm text-gray-600">{{ t.description }}</p>
          </div>
        </div>
      </section>

      <!-- Audience -->
      <section v-if="ai?.audience_insight">
        <h3 class="text-lg font-bold mb-2" style="color: var(--color-nav)">Inzicht in de doelgroep</h3>
        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ ai.audience_insight }}</p>
      </section>

      <!-- Opportunities & risks -->
      <section v-if="ai" class="grid md:grid-cols-2 gap-6">
        <div v-if="ai.opportunities?.length">
          <h3 class="text-lg font-bold mb-2 text-green-700">Kansen</h3>
          <ul class="space-y-1">
            <li v-for="(o, i) in ai.opportunities" :key="i" class="flex gap-2 text-gray-700">
              <span class="text-green-600">↑</span><span>{{ o }}</span>
            </li>
          </ul>
        </div>
        <div v-if="ai.risks?.length">
          <h3 class="text-lg font-bold mb-2 text-red-700">Aandachtspunten</h3>
          <ul class="space-y-1">
            <li v-for="(r, i) in ai.risks" :key="i" class="flex gap-2 text-gray-700">
              <span class="text-red-600">!</span><span>{{ r }}</span>
            </li>
          </ul>
        </div>
      </section>

      <!-- Recommendations -->
      <section v-if="ai?.recommendations?.length">
        <h3 class="text-lg font-bold mb-3" style="color: var(--color-nav)">Aanbevelingen</h3>
        <div class="space-y-3">
          <div
            v-for="(r, i) in ai.recommendations"
            :key="i"
            class="rounded-xl border-l-4 p-4 bg-[var(--color-bg)]"
            :style="{ borderColor: r.priority === 'hoog' ? '#ef4444' : r.priority === 'middel' ? '#f78a1d' : '#9ca3af' }"
          >
            <div class="flex items-center justify-between mb-1">
              <h4 class="font-semibold" style="color: var(--color-nav)">{{ r.title }}</h4>
              <span class="text-xs uppercase tracking-wide text-gray-500">prioriteit: {{ r.priority }}</span>
            </div>
            <p class="text-sm text-gray-700">{{ r.detail }}</p>
          </div>
        </div>
      </section>

      <!-- Suggested main question -->
      <section v-if="ai?.suggested_main_question" class="rounded-2xl p-5 text-white" style="background: var(--color-nav)">
        <p class="text-xs uppercase tracking-wide opacity-70 mb-1">Voorgestelde volgende hoofdvraag</p>
        <p class="text-lg font-semibold">{{ ai.suggested_main_question }}</p>
      </section>
    </div>

    <div v-else-if="current?.status === 'failed'" class="text-[var(--color-error)]">
      Dit rapport kon niet worden gegenereerd. {{ current.error }}
    </div>
  </div>
</template>
