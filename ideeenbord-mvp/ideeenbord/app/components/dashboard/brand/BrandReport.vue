<script setup lang="ts">
import { ref, computed, reactive, onMounted } from "vue";
import { Bar, Line, Doughnut } from "vue-chartjs";
import {
  reportService,
  type ReportSummary,
  type BrandReport,
  type ReportPeriodType,
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
const BLUE = "#3b82f6";
const GREEN = "#22c55e";
const RED = "#ef4444";
const PALETTE = [ORANGE, NAVY, BLUE, GREEN, "#a855f7", RED, "#14b8a6", "#eab308"];

const totals = computed(() => current.value?.metrics?.totals ?? null);
const comparison = computed(() => current.value?.metrics?.comparison ?? null);
const ai = computed(() => current.value?.ai ?? null);

/* ----------------------------- Period controls ---------------------------- */

const period = reactive<{ type: ReportPeriodType; start: string; end: string }>({
  type: "all",
  start: "",
  end: "",
});

const periodModes: { value: ReportPeriodType; label: string }[] = [
  { value: "all", label: "Volledig" },
  { value: "monthly", label: "Maandelijks" },
  { value: "custom", label: "Aangepast" },
];

function buildPayload() {
  if (period.type === "all") return { period_type: "all" as const };

  if (!period.start || !period.end) {
    throw new Error(
      period.type === "monthly"
        ? "Kies een begin- en eindmaand."
        : "Kies een begin- en einddatum."
    );
  }

  if (period.type === "monthly") {
    // <input type="month"> → "YYYY-MM"; backend snapt naar hele maanden.
    return { period_type: "monthly" as const, start: `${period.start}-01`, end: `${period.end}-01` };
  }
  return { period_type: "custom" as const, start: period.start, end: period.end };
}

/* -------------------------------- Data flow ------------------------------- */

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
    const payload = buildPayload();
    current.value = await reportService.generate(props.brandId, payload);
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

function deltaLabel(key: string): { text: string; positive: boolean } | null {
  const d = comparison.value?.deltas?.[key];
  if (!d || d.change_pct === null || d.change_pct === undefined) return null;
  const up = d.change >= 0;
  return { text: `${up ? "+" : ""}${d.change_pct}% vs vorige periode`, positive: up };
}

/* --------------------------------- Charts --------------------------------- */

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { labels: { color: NAVY } } },
  scales: {
    x: { ticks: { color: NAVY }, grid: { display: false } },
    y: { ticks: { color: NAVY }, grid: { color: "rgba(0,0,0,0.05)" }, beginAtZero: true },
  },
};
const hBarOptions = {
  ...chartOptions,
  indexAxis: "y" as const,
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
        backgroundColor: [ORANGE, BLUE, GREEN, RED],
        data: [s.pending ?? 0, s.in_progress ?? 0, s.completed ?? 0, s.rejected ?? 0],
        borderRadius: 6,
      },
    ],
  };
});

const engagementChart = computed(() => {
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
      {
        label: "Likes",
        data: rows.map((r: any) => r.likes),
        borderColor: GREEN,
        backgroundColor: "transparent",
        tension: 0.35,
        pointBackgroundColor: GREEN,
      },
      {
        label: "Dislikes",
        data: rows.map((r: any) => r.dislikes),
        borderColor: RED,
        backgroundColor: "transparent",
        tension: 0.35,
        pointBackgroundColor: RED,
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

const categoriesChart = computed(() => {
  const rows = current.value?.metrics?.categories ?? [];
  return {
    labels: rows.map((r: any) => r.category),
    datasets: [{ label: "Ideeën", backgroundColor: ORANGE, data: rows.map((r: any) => r.count), borderRadius: 6 }],
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

const educationChart = computed(() => {
  const rows = current.value?.metrics?.demographics?.education ?? [];
  return {
    labels: rows.map((r: any) => r.label),
    datasets: [{ backgroundColor: PALETTE, data: rows.map((r: any) => r.count) }],
  };
});

const sectorChart = computed(() => {
  const rows = current.value?.metrics?.demographics?.sector ?? [];
  return {
    labels: rows.map((r: any) => r.label),
    datasets: [{ label: "Deelnemers", backgroundColor: BLUE, data: rows.map((r: any) => r.count), borderRadius: 6 }],
  };
});

const citiesChart = computed(() => {
  const rows = current.value?.metrics?.demographics?.cities ?? [];
  return {
    labels: rows.map((r: any) => r.label),
    datasets: [{ label: "Deelnemers", backgroundColor: "#14b8a6", data: rows.map((r: any) => r.count), borderRadius: 6 }],
  };
});

const quizChart = computed(() => {
  const rows = current.value?.metrics?.quizzes ?? [];
  return {
    labels: rows.map((r: any) => r.title),
    datasets: [{ label: "Deelnemers", backgroundColor: "#a855f7", data: rows.map((r: any) => r.participants), borderRadius: 6 }],
  };
});

const has = (rows: any) => Array.isArray(rows) && rows.length > 0;
const hasDemographics = computed(() => {
  const d = current.value?.metrics?.demographics;
  return d && (d.gender?.length || d.age_brackets?.some((b: any) => b.count > 0));
});

onMounted(loadList);
</script>

<template>
  <div>
    <!-- Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
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
      <button
        v-if="current?.status === 'completed'"
        class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-semibold hover:bg-gray-50"
        @click="exportPdf"
      >
        <i class="fas fa-file-pdf mr-1"></i> Download PDF
      </button>
    </div>

    <!-- Generation panel -->
    <div class="rounded-2xl border border-gray-200 p-4 mb-5 bg-white no-print">
      <p class="text-sm font-semibold mb-3" style="color: var(--color-nav)">Nieuw rapport genereren</p>
      <div class="flex flex-wrap items-end gap-4">
        <div>
          <p class="text-xs text-gray-500 mb-1">Periode</p>
          <div class="inline-flex rounded-lg border border-gray-300 overflow-hidden">
            <button
              v-for="m in periodModes"
              :key="m.value"
              class="px-3 py-2 text-sm font-medium"
              :class="period.type === m.value ? 'bg-[var(--color-nav)] text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
              @click="period.type = m.value"
            >
              {{ m.label }}
            </button>
          </div>
        </div>

        <template v-if="period.type === 'monthly'">
          <div>
            <p class="text-xs text-gray-500 mb-1">Van maand</p>
            <input v-model="period.start" type="month" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <p class="text-xs text-gray-500 mb-1">Tot maand</p>
            <input v-model="period.end" type="month" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
        </template>

        <template v-else-if="period.type === 'custom'">
          <div>
            <p class="text-xs text-gray-500 mb-1">Van datum</p>
            <input v-model="period.start" type="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <p class="text-xs text-gray-500 mb-1">Tot datum</p>
            <input v-model="period.end" type="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
        </template>

        <button
          class="px-4 py-2 rounded-lg text-white text-sm font-semibold bg-gradient-to-b from-orange-400 to-orange-600 disabled:opacity-60"
          :disabled="generating"
          @click="generate"
        >
          <span v-if="generating"><i class="fas fa-spinner fa-spin mr-1"></i> Genereren…</span>
          <span v-else><i class="fas fa-wand-magic-sparkles mr-1"></i> Genereren</span>
        </button>
      </div>
      <p class="text-xs text-gray-400 mt-2">
        Een uitgebreid rapport genereren duurt doorgaans 20–60 seconden.
      </p>
    </div>

    <p v-if="error" class="text-red-600 mb-4">{{ error }}</p>
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
    <div v-if="current?.status === 'completed'" class="report-printable space-y-10">
      <header class="border-b border-gray-200 pb-4">
        <h2 class="text-2xl font-bold" style="color: var(--color-nav)">{{ current.title }}</h2>
        <p class="text-sm text-gray-500">
          Gegenereerd op {{ fmtDate(current.generated_at) }}
          <span v-if="current.model"> · {{ current.model }}</span>
          <span v-if="current.metrics?.period?.label"> · {{ current.metrics.period.label }}</span>
        </p>
      </header>

      <!-- KPI cards -->
      <section v-if="totals" class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold" style="color: var(--color-brand)">{{ totals.ideas }}</p>
          <p class="text-xs text-gray-600">Ideeën</p>
          <p v-if="deltaLabel('ideas')" class="text-[11px] mt-0.5" :class="deltaLabel('ideas')!.positive ? 'text-green-600' : 'text-red-600'">
            {{ deltaLabel('ideas')!.text }}
          </p>
        </div>
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold" style="color: var(--color-nav)">{{ totals.participants }}</p>
          <p class="text-xs text-gray-600">Deelnemers</p>
          <p v-if="deltaLabel('participants')" class="text-[11px] mt-0.5" :class="deltaLabel('participants')!.positive ? 'text-green-600' : 'text-red-600'">
            {{ deltaLabel('participants')!.text }}
          </p>
        </div>
        <div class="rounded-xl bg-[var(--color-bg)] p-4">
          <p class="text-2xl font-bold text-green-600">{{ totals.net_sentiment >= 0 ? "+" : "" }}{{ totals.net_sentiment }}</p>
          <p class="text-xs text-gray-600">Netto sentiment</p>
          <p v-if="deltaLabel('net_sentiment')" class="text-[11px] mt-0.5" :class="deltaLabel('net_sentiment')!.positive ? 'text-green-600' : 'text-red-600'">
            {{ deltaLabel('net_sentiment')!.text }}
          </p>
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

      <!-- 1. Executive summary -->
      <section v-if="ai">
        <h3 class="report-h3">1. Managementsamenvatting</h3>
        <p class="report-text">{{ ai.executive_summary }}</p>
        <ul v-if="ai.key_findings?.length" class="mt-3 space-y-1">
          <li v-for="(k, i) in ai.key_findings" :key="i" class="flex gap-2 text-gray-700">
            <span style="color: var(--color-brand)">▪</span><span>{{ k }}</span>
          </li>
        </ul>
      </section>

      <!-- 2. Participation -->
      <section v-if="ai?.participation_analysis">
        <h3 class="report-h3">2. Deelname &amp; engagement</h3>
        <p class="report-text">{{ ai.participation_analysis }}</p>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
          <div class="report-chart">
            <h4 class="report-chart-title">Ideeën, likes &amp; dislikes over tijd</h4>
            <div class="h-60"><Line :data="engagementChart" :options="chartOptions" /></div>
          </div>
          <div class="report-chart">
            <h4 class="report-chart-title">Status van ideeën</h4>
            <div class="h-60"><Bar :data="statusChart" :options="chartOptions" /></div>
          </div>
        </div>
      </section>

      <!-- 3. Trend / period comparison -->
      <section v-if="ai?.trend_analysis">
        <h3 class="report-h3">3. Ontwikkeling t.o.v. vorige periode</h3>
        <p class="report-text">{{ ai.trend_analysis }}</p>
      </section>

      <!-- 4. Sentiment -->
      <section v-if="ai?.sentiment_analysis">
        <h3 class="report-h3">4. Sentimentanalyse</h3>
        <p class="report-text">{{ ai.sentiment_analysis }}</p>
        <div v-if="has(current?.metrics?.top_ideas_by_likes)" class="report-chart mt-4">
          <h4 class="report-chart-title">Best ontvangen ideeën</h4>
          <div class="h-72"><Bar :data="topIdeasChart" :options="chartOptions" /></div>
        </div>
      </section>

      <!-- 5. Top ideas analysis -->
      <section v-if="ai?.top_ideas_analysis">
        <h3 class="report-h3">5. Analyse van best &amp; slechtst ontvangen ideeën</h3>
        <p class="report-text">{{ ai.top_ideas_analysis }}</p>
        <div v-if="has(current?.metrics?.categories)" class="report-chart mt-4">
          <h4 class="report-chart-title">Categorieën van ideeën</h4>
          <div class="h-64"><Bar :data="categoriesChart" :options="chartOptions" /></div>
        </div>
      </section>

      <!-- 6. Idea themes -->
      <section v-if="ai?.idea_themes?.length">
        <h3 class="report-h3">6. Thema's in de ideeën</h3>
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
            <ul v-if="t.example_ideas?.length" class="mt-2 space-y-0.5">
              <li v-for="(ex, j) in t.example_ideas" :key="j" class="text-xs text-gray-500">• {{ ex }}</li>
            </ul>
          </div>
        </div>
      </section>

      <!-- 7. Audience -->
      <section v-if="ai?.audience_insight">
        <h3 class="report-h3">7. Inzicht in de doelgroep</h3>
        <p class="report-text">{{ ai.audience_insight }}</p>
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
          <div v-if="hasDemographics" class="report-chart">
            <h4 class="report-chart-title">Leeftijdsopbouw</h4>
            <div class="h-56"><Bar :data="ageChart" :options="chartOptions" /></div>
          </div>
          <div v-if="genderChart.labels.length" class="report-chart">
            <h4 class="report-chart-title">Geslacht</h4>
            <div class="h-56"><Doughnut :data="genderChart" :options="doughnutOptions" /></div>
          </div>
          <div v-if="educationChart.labels.length" class="report-chart">
            <h4 class="report-chart-title">Opleidingsniveau</h4>
            <div class="h-56"><Doughnut :data="educationChart" :options="doughnutOptions" /></div>
          </div>
          <div v-if="sectorChart.labels.length" class="report-chart">
            <h4 class="report-chart-title">Sector</h4>
            <div class="h-56"><Bar :data="sectorChart" :options="hBarOptions" /></div>
          </div>
          <div v-if="citiesChart.labels.length" class="report-chart">
            <h4 class="report-chart-title">Steden</h4>
            <div class="h-56"><Bar :data="citiesChart" :options="hBarOptions" /></div>
          </div>
        </section>
      </section>

      <!-- 8. Audience segments -->
      <section v-if="ai?.audience_segments?.length">
        <h3 class="report-h3">8. Doelgroepsegmenten</h3>
        <div class="grid md:grid-cols-2 gap-3">
          <div v-for="(s, i) in ai.audience_segments" :key="i" class="rounded-xl border border-gray-100 p-4">
            <h4 class="font-semibold mb-1" style="color: var(--color-nav)">{{ s.segment }}</h4>
            <p class="text-sm text-gray-600">{{ s.description }}</p>
          </div>
        </div>
      </section>

      <!-- 9. Main question -->
      <section v-if="ai?.main_question_insights">
        <h3 class="report-h3">9. Inzichten uit de hoofdvraag</h3>
        <p class="report-text">{{ ai.main_question_insights }}</p>
        <div v-if="has(current?.metrics?.quizzes)" class="report-chart mt-4">
          <h4 class="report-chart-title">Quiz-deelname</h4>
          <div class="h-56"><Bar :data="quizChart" :options="chartOptions" /></div>
        </div>
      </section>

      <!-- 10. Opportunities & risks -->
      <section v-if="ai" class="grid md:grid-cols-2 gap-6">
        <div v-if="ai.opportunities?.length">
          <h3 class="report-h3">10. Kansen</h3>
          <ul class="space-y-1">
            <li v-for="(o, i) in ai.opportunities" :key="i" class="flex gap-2 text-gray-700">
              <span class="text-green-600">↑</span><span>{{ o }}</span>
            </li>
          </ul>
        </div>
        <div v-if="ai.risks?.length">
          <h3 class="report-h3">11. Aandachtspunten</h3>
          <ul class="space-y-1">
            <li v-for="(r, i) in ai.risks" :key="i" class="flex gap-2 text-gray-700">
              <span class="text-red-600">!</span><span>{{ r }}</span>
            </li>
          </ul>
        </div>
      </section>

      <!-- 12. Strategic outlook -->
      <section v-if="ai?.strategic_outlook">
        <h3 class="report-h3">12. Strategische vooruitblik</h3>
        <p class="report-text">{{ ai.strategic_outlook }}</p>
      </section>

      <!-- 13. Recommendations -->
      <section v-if="ai?.recommendations?.length">
        <h3 class="report-h3">13. Aanbevelingen</h3>
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
            <p v-if="r.expected_impact" class="text-xs text-gray-500 mt-1">
              <span class="font-semibold">Verwachte impact:</span> {{ r.expected_impact }}
            </p>
          </div>
        </div>
      </section>

      <!-- 14. Action plan -->
      <section v-if="ai?.action_plan?.length">
        <h3 class="report-h3">14. Actieplan</h3>
        <div class="space-y-3">
          <div v-for="(p, i) in ai.action_plan" :key="i" class="rounded-xl border border-gray-100 p-4">
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-semibold" style="color: var(--color-nav)">{{ p.phase }}</h4>
              <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ p.timeframe }}</span>
            </div>
            <ul class="space-y-1">
              <li v-for="(a, j) in p.actions" :key="j" class="flex gap-2 text-sm text-gray-700">
                <span style="color: var(--color-brand)">→</span><span>{{ a }}</span>
              </li>
            </ul>
          </div>
        </div>
      </section>

      <!-- 15. Conclusion -->
      <section v-if="ai?.conclusion">
        <h3 class="report-h3">15. Conclusie</h3>
        <p class="report-text">{{ ai.conclusion }}</p>
      </section>

      <!-- Suggested main question -->
      <section v-if="ai?.suggested_main_question" class="rounded-2xl p-5 text-white" style="background: var(--color-nav)">
        <p class="text-xs uppercase tracking-wide opacity-70 mb-1">Voorgestelde volgende hoofdvraag</p>
        <p class="text-lg font-semibold">{{ ai.suggested_main_question }}</p>
      </section>
    </div>

    <div v-else-if="current?.status === 'failed'" class="text-red-600">
      Dit rapport kon niet worden gegenereerd. {{ current.error }}
    </div>
  </div>
</template>

<style scoped>
.report-h3 {
  font-size: 1.125rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: var(--color-nav);
}
.report-text {
  color: #374151;
  line-height: 1.7;
  white-space: pre-line;
}
.report-chart {
  border: 1px solid #f3f4f6;
  border-radius: 1rem;
  padding: 1rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
.report-chart-title {
  font-weight: 600;
  font-size: 0.875rem;
  margin-bottom: 0.75rem;
  color: var(--color-nav);
}
@media print {
  .no-print {
    display: none !important;
  }
  section {
    break-inside: avoid;
  }
}
</style>
