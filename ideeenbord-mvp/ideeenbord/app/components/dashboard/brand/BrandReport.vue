<script setup lang="ts">
import { ref, computed, reactive, onMounted } from "vue";
import { Bar, Line, Doughnut } from "vue-chartjs";
import {
  reportService,
  type ReportSummary,
  type BrandReport,
  type ReportPeriodType,
  type ReportRange,
  type ReportQuota,
} from "~/services/api/brand/reportService";

const props = defineProps<{
  brandId: number;
  brandName?: string;
  brandLogoUrl?: string | null;
}>();

const reports = ref<ReportSummary[]>([]);
const current = ref<BrandReport | null>(null);
const loading = ref(true);
const generating = ref(false);
const error = ref<string | null>(null);
const quota = ref<ReportQuota | null>(null);
const quotaReached = computed(() => !!quota.value && quota.value.remaining <= 0);

function fmtMonthDay(d?: string | null) {
  if (!d) return "";
  return new Date(d).toLocaleDateString("nl-NL", { day: "numeric", month: "long" });
}

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

const tocChapters = [
  "Managementsamenvatting",
  "Deelname & engagement",
  "Ontwikkeling t.o.v. vorige periode",
  "Sentimentanalyse",
  "Analyse van best & slechtst ontvangen ideeën",
  "Thema's in de ideeën",
  "Inzicht in de doelgroep & koopgedrag",
  "Doelgroepsegmenten & persona's",
  "Inzichten uit de hoofdvraag",
  "Kansen & aandachtspunten",
  "Strategische vooruitblik",
  "Aanbevelingen",
  "Actieplan",
  "Conclusie",
];

/* ----------------------------- Period controls ---------------------------- */

const period = reactive<{ type: ReportPeriodType; start: string; end: string }>({
  type: "all",
  start: "",
  end: "",
});

const range = ref<ReportRange | null>(null);
const months = computed(() => range.value?.months ?? []);

const periodModes: { value: ReportPeriodType; label: string }[] = [
  { value: "all", label: "Volledig" },
  { value: "monthly", label: "Maandelijks" },
  { value: "custom", label: "Aangepast" },
];

// Schakel modus en vul echte standaarddata uit de beschikbare periode in.
function setMode(mode: ReportPeriodType) {
  period.type = mode;
  if (mode === "monthly" && months.value.length) {
    period.start = period.start || months.value[months.value.length - 1].value;
    period.end = period.end || months.value[0].value;
  } else if (mode === "custom" && range.value?.first) {
    period.start = period.start || range.value.first;
    period.end = period.end || (range.value.last ?? "");
  }
}

async function loadRange() {
  try {
    range.value = await reportService.range(props.brandId);
  } catch {
    /* niet kritisch — pickers vallen terug op vrije invoer */
  }
}

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
    const res = await reportService.list(props.brandId);
    reports.value = res.reports;
    quota.value = res.quota;
    const latest = res.reports.find((r) => r.status === "completed");
    if (latest) current.value = await reportService.get(latest.id);
  } catch (e: any) {
    error.value = e?.message ?? "Rapporten ophalen mislukt.";
  } finally {
    loading.value = false;
  }
}

async function refreshList() {
  const res = await reportService.list(props.brandId);
  reports.value = res.reports;
  quota.value = res.quota;
}

async function generate() {
  generating.value = true;
  error.value = null;
  try {
    const payload = buildPayload();
    current.value = await reportService.generate(props.brandId, payload);
    await refreshList();
  } catch (e: any) {
    error.value = e?.message ?? "Rapport genereren mislukt.";
    // Limiet kan bereikt zijn — quota verversen zodat de UI klopt.
    try {
      await refreshList();
    } catch {
      /* negeren */
    }
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
  return { text: `${up ? "+" : ""}${d.change_pct}%`, positive: up };
}

const kpiCards = computed(() => {
  const t = totals.value;
  if (!t) return [];
  return [
    { label: "Ideeën", value: t.ideas, icon: "fa-lightbulb", accent: ORANGE, delta: deltaLabel("ideas") },
    { label: "Deelnemers", value: t.participants, icon: "fa-users", accent: NAVY, delta: deltaLabel("participants") },
    { label: "Netto sentiment", value: `${t.net_sentiment >= 0 ? "+" : ""}${t.net_sentiment}`, icon: "fa-heart", accent: GREEN, delta: deltaLabel("net_sentiment") },
    { label: "Positief sentiment", value: t.sentiment_ratio != null ? `${t.sentiment_ratio}%` : "—", icon: "fa-thumbs-up", accent: GREEN, delta: null },
    { label: "Likes", value: t.idea_likes, icon: "fa-thumbs-up", accent: GREEN, delta: null },
    { label: "Dislikes", value: t.idea_dislikes, icon: "fa-thumbs-down", accent: RED, delta: null },
    { label: "Hoofdvraag-antwoorden", value: t.main_question_responses, icon: "fa-comments", accent: BLUE, delta: null },
    { label: "Gem. beoordeling", value: t.avg_rating ?? "—", icon: "fa-star", accent: ORANGE, delta: null },
  ];
});

function priorityColor(p: string) {
  return p === "hoog" ? RED : p === "middel" ? ORANGE : "#9ca3af";
}

const health = computed(() => current.value?.metrics?.health ?? null);
const healthColor = computed(() => {
  const s = health.value?.score ?? 0;
  return s >= 75 ? GREEN : s >= 50 ? ORANGE : s >= 30 ? "#f59e0b" : RED;
});
const healthGauge = computed(() => {
  const s = health.value?.score ?? 0;
  return {
    labels: ["score", "rest"],
    datasets: [{ data: [s, 100 - s], backgroundColor: [healthColor.value, "#eef0f3"], borderWidth: 0 }],
  };
});
const gaugeOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: "78%",
  plugins: { legend: { display: false }, tooltip: { enabled: false } },
};

const coverageNote = computed(() => {
  const dp = current.value?.metrics?.data_profile;
  const participants = totals.value?.participants ?? 0;
  if (!dp || !participants) return null;
  const shared = dp.shared_count ?? 0;
  if (!shared) return null;
  return { shared, participants, pct: Math.round((shared / participants) * 100) };
});

/* --------------------------------- Charts --------------------------------- */

const FONT = { family: "Poppins, sans-serif" } as const;
const tooltipStyle = {
  backgroundColor: NAVY,
  padding: 10,
  cornerRadius: 8,
  titleFont: FONT,
  bodyFont: FONT,
  displayColors: true,
  usePointStyle: true,
};
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { labels: { color: NAVY, usePointStyle: true, boxWidth: 8, font: FONT } },
    tooltip: tooltipStyle,
  },
  scales: {
    x: { ticks: { color: "#6b7280", font: FONT }, grid: { display: false } },
    y: { ticks: { color: "#6b7280", font: FONT }, grid: { color: "rgba(0,0,0,0.05)" }, border: { display: false }, beginAtZero: true },
  },
};
const hBarOptions = {
  ...chartOptions,
  indexAxis: "y" as const,
};
const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: "62%",
  plugins: {
    legend: { position: "bottom" as const, labels: { color: NAVY, usePointStyle: true, boxWidth: 8, font: FONT } },
    tooltip: tooltipStyle,
  },
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

function dpRows(key: string) {
  return current.value?.metrics?.data_profile?.[key] ?? [];
}
const politicalChart = computed(() => ({
  labels: dpRows("political_preference").map((r: any) => r.label),
  datasets: [{ backgroundColor: PALETTE, data: dpRows("political_preference").map((r: any) => r.count) }],
}));
const orderFreqChart = computed(() => ({
  labels: dpRows("order_frequency").map((r: any) => r.label),
  datasets: [{ label: "Deelnemers", backgroundColor: ORANGE, data: dpRows("order_frequency").map((r: any) => r.count), borderRadius: 6 }],
}));
const techSpendChart = computed(() => ({
  labels: dpRows("tech_spend").map((r: any) => r.label),
  datasets: [{ label: "Deelnemers", backgroundColor: BLUE, data: dpRows("tech_spend").map((r: any) => r.count), borderRadius: 6 }],
}));
const grocerySpendChart = computed(() => ({
  labels: dpRows("grocery_spend").map((r: any) => r.label),
  datasets: [{ label: "Deelnemers", backgroundColor: GREEN, data: dpRows("grocery_spend").map((r: any) => r.count), borderRadius: 6 }],
}));
const householdChart = computed(() => ({
  labels: dpRows("household_size").map((r: any) => r.label),
  datasets: [{ label: "Huishoudens", backgroundColor: NAVY, data: dpRows("household_size").map((r: any) => r.count), borderRadius: 6 }],
}));
const hasDataProfile = computed(() => (current.value?.metrics?.data_profile?.shared_count ?? 0) > 0);

const has = (rows: any) => Array.isArray(rows) && rows.length > 0;
const hasDemographics = computed(() => {
  const d = current.value?.metrics?.demographics;
  return d && (d.gender?.length || d.age_brackets?.some((b: any) => b.count > 0));
});

onMounted(() => {
  loadList();
  loadRange();
});
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
              @click="setMode(m.value)"
            >
              {{ m.label }}
            </button>
          </div>
        </div>

        <template v-if="period.type === 'monthly'">
          <template v-if="months.length">
            <div>
              <p class="text-xs text-gray-500 mb-1">Van maand</p>
              <select v-model="period.start" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }} ({{ m.ideas }} ideeën)</option>
              </select>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">Tot maand</p>
              <select v-model="period.end" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }} ({{ m.ideas }} ideeën)</option>
              </select>
            </div>
          </template>
          <p v-else class="text-xs text-gray-400 self-center">Nog geen data om een maand te kiezen.</p>
        </template>

        <template v-else-if="period.type === 'custom'">
          <div>
            <p class="text-xs text-gray-500 mb-1">Van datum</p>
            <input
              v-model="period.start"
              type="date"
              :min="range?.first ?? undefined"
              :max="range?.last ?? undefined"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            />
          </div>
          <div>
            <p class="text-xs text-gray-500 mb-1">Tot datum</p>
            <input
              v-model="period.end"
              type="date"
              :min="range?.first ?? undefined"
              :max="range?.last ?? undefined"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            />
          </div>
        </template>
      </div>

      <div class="mt-4 flex flex-wrap items-center gap-3">
        <button
          class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold bg-gradient-to-b from-orange-400 to-orange-600 disabled:opacity-60 disabled:cursor-not-allowed shadow-sm"
          :disabled="generating || quotaReached"
          @click="generate"
        >
          <span v-if="generating"><i class="fas fa-spinner fa-spin mr-1"></i> Genereren…</span>
          <span v-else><i class="fas fa-wand-magic-sparkles mr-1"></i> Genereren</span>
        </button>

        <span
          v-if="quota"
          class="text-xs font-semibold px-2.5 py-1 rounded-full"
          :class="quotaReached ? 'bg-red-100 text-red-700' : 'bg-[var(--color-bg)] text-gray-600'"
        >
          <i class="fa-solid fa-gauge-high mr-1"></i>
          Nog {{ quota.remaining }} van {{ quota.limit }} deze maand
        </span>
      </div>
      <p v-if="quotaReached" class="text-xs text-red-600 mt-2">
        Je maandlimiet is bereikt. Je kunt vanaf {{ fmtMonthDay(quota?.resets_at) }} weer rapporten genereren.
      </p>
      <p v-else class="text-xs text-gray-400 mt-2">
        Een uitgebreid rapport genereren duurt doorgaans 20–60 seconden · limiet {{ quota?.limit ?? 10 }} per maand.
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
      <NuxtLink
        to="/voorbeeld-rapport"
        target="_blank"
        class="inline-flex items-center gap-1.5 mt-4 text-sm font-semibold text-[var(--color-brand)] hover:underline"
      >
        <i class="fa-solid fa-eye"></i> Bekijk eerst een voorbeeldrapport
      </NuxtLink>
    </div>

    <!-- Report -->
    <div v-if="current?.status === 'completed'" class="report-printable space-y-10">
      <!-- PDF voorblad (alleen zichtbaar bij printen) -->
      <section class="report-cover">
        <div class="report-cover__bar">
          <img src="/ideeenbord-logo-footer-and-nav.png" alt="Ideeënbord" class="report-cover__brand" />
        </div>
        <div class="report-cover__body">
          <img v-if="brandLogoUrl" :src="brandLogoUrl" alt="Merklogo" class="report-cover__logo" />
          <p class="report-cover__eyebrow">Merkrapport</p>
          <h1 class="report-cover__title">{{ brandName || current.metrics?.brand?.title || "" }}</h1>
          <p class="report-cover__period">{{ current.metrics?.period?.label }}</p>
          <p class="report-cover__date">Gegenereerd op {{ fmtDate(current.generated_at) }}</p>
        </div>
        <div class="report-cover__footer">
          <span>Ideeënbord</span>
          <span>Vertrouwelijk — uitsluitend voor {{ brandName || "het merk" }}</span>
        </div>
      </section>

      <!-- Inhoudsopgave (alleen bij printen) -->
      <section class="report-toc">
        <h2 class="report-toc__title">Inhoudsopgave</h2>
        <ol class="report-toc__list">
          <li v-for="(c, i) in tocChapters" :key="i">{{ c }}</li>
        </ol>
      </section>

      <!-- Titelblok -->
      <header class="rep-hero">
        <p class="rep-hero__eyebrow">
          <i class="fa-solid fa-chart-pie"></i> Merkrapport
          <span v-if="current.metrics?.period?.label"> · {{ current.metrics.period.label }}</span>
        </p>
        <h2 class="rep-hero__title">{{ brandName || current.metrics?.brand?.title || current.title }}</h2>
        <p class="rep-hero__meta">
          Gegenereerd op {{ fmtDate(current.generated_at) }}
          <span v-if="current.model"> · model {{ current.model }}</span>
        </p>
      </header>

      <!-- KPI band -->
      <section v-if="kpiCards.length" class="rep-kpis">
        <div v-for="k in kpiCards" :key="k.label" class="rep-kpi">
          <span class="rep-kpi__icon" :style="{ background: k.accent }">
            <i class="fa-solid" :class="k.icon"></i>
          </span>
          <div class="min-w-0">
            <p class="rep-kpi__value">{{ k.value }}</p>
            <p class="rep-kpi__label">{{ k.label }}</p>
          </div>
          <span
            v-if="k.delta"
            class="rep-kpi__delta"
            :class="k.delta.positive ? 'is-up' : 'is-down'"
          >
            <i class="fa-solid" :class="k.delta.positive ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'"></i>
            {{ k.delta.text }}
          </span>
        </div>
      </section>

      <!-- Merkgezondheid -->
      <section v-if="health" class="rep-health">
        <div class="rep-health__gauge">
          <Doughnut :data="healthGauge" :options="gaugeOptions" />
          <div class="rep-health__center">
            <span class="rep-health__score" :style="{ color: healthColor }">{{ health.score }}</span>
            <span class="rep-health__max">/ 100</span>
          </div>
        </div>
        <div class="rep-health__body">
          <p class="rep-health__eyebrow">Merkgezondheid</p>
          <p class="rep-health__label" :style="{ color: healthColor }">{{ health.label }}</p>
          <div class="rep-health__bars">
            <div v-for="c in health.components" :key="c.label">
              <div class="flex justify-between text-xs text-gray-500 mb-0.5">
                <span>{{ c.label }}</span><span class="font-semibold">{{ c.value }}</span>
              </div>
              <div class="rep-health__track">
                <div class="rep-health__fill" :style="{ width: c.value + '%', background: healthColor }"></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 1. Managementsamenvatting -->
      <section v-if="ai" class="rep-section">
        <div class="rep-section__head">
          <span class="rep-section__num">01</span>
          <h3 class="rep-section__title">Managementsamenvatting</h3>
        </div>
        <div class="rep-lead">
          <p class="report-text">{{ ai.executive_summary }}</p>
        </div>
        <ul v-if="ai.key_findings?.length" class="rep-findings">
          <li v-for="(k, i) in ai.key_findings" :key="i">
            <i class="fa-solid fa-circle-check"></i><span>{{ k }}</span>
          </li>
        </ul>
      </section>

      <!-- Quick wins -->
      <section v-if="ai?.quick_wins?.length" class="rep-quickwins">
        <p class="rep-quickwins__title"><i class="fa-solid fa-bolt"></i> Quick wins — direct aan de slag</p>
        <div class="grid sm:grid-cols-3 gap-3">
          <div v-for="(q, i) in ai.quick_wins" :key="i" class="rep-quickwin">
            <span class="rep-quickwin__num">{{ i + 1 }}</span>
            <p>{{ q }}</p>
          </div>
        </div>
      </section>

      <!-- 2. Deelname & engagement -->
      <section v-if="ai?.participation_analysis" class="rep-section">
        <div class="rep-section__head">
          <span class="rep-section__num">02</span>
          <h3 class="rep-section__title">Deelname &amp; engagement</h3>
        </div>
        <p class="report-text">{{ ai.participation_analysis }}</p>
        <div class="rep-charts">
          <div class="rep-card">
            <p class="rep-card__title"><i class="fa-solid fa-chart-line"></i> Ideeën, likes &amp; dislikes over tijd</p>
            <div class="h-64"><Line :data="engagementChart" :options="chartOptions" /></div>
          </div>
          <div class="rep-card">
            <p class="rep-card__title"><i class="fa-solid fa-list-check"></i> Status van ideeën</p>
            <div class="h-64"><Bar :data="statusChart" :options="chartOptions" /></div>
          </div>
        </div>
      </section>

      <!-- 3. Ontwikkeling -->
      <section v-if="ai?.trend_analysis" class="rep-section">
        <div class="rep-section__head">
          <span class="rep-section__num">03</span>
          <h3 class="rep-section__title">Ontwikkeling t.o.v. vorige periode</h3>
        </div>
        <p class="report-text">{{ ai.trend_analysis }}</p>
      </section>

      <!-- 4. Sentiment -->
      <section v-if="ai?.sentiment_analysis" class="rep-section">
        <div class="rep-section__head">
          <span class="rep-section__num">04</span>
          <h3 class="rep-section__title">Sentimentanalyse</h3>
        </div>
        <p class="report-text">{{ ai.sentiment_analysis }}</p>
        <div v-if="has(current?.metrics?.top_ideas_by_likes)" class="rep-card mt-4">
          <p class="rep-card__title"><i class="fa-solid fa-trophy"></i> Best ontvangen ideeën</p>
          <div class="h-72"><Bar :data="topIdeasChart" :options="chartOptions" /></div>
        </div>
      </section>

      <!-- 5. Analyse top-ideeën -->
      <section v-if="ai?.top_ideas_analysis" class="rep-section">
        <div class="rep-section__head">
          <span class="rep-section__num">05</span>
          <h3 class="rep-section__title">Analyse van best &amp; slechtst ontvangen ideeën</h3>
        </div>
        <p class="report-text">{{ ai.top_ideas_analysis }}</p>
        <div v-if="has(current?.metrics?.categories)" class="rep-card mt-4">
          <p class="rep-card__title"><i class="fa-solid fa-tags"></i> Categorieën van ideeën</p>
          <div class="h-64"><Bar :data="categoriesChart" :options="chartOptions" /></div>
        </div>
      </section>

      <!-- 6. Thema's -->
      <section v-if="ai?.idea_themes?.length" class="rep-section">
        <div class="rep-section__head">
          <span class="rep-section__num">06</span>
          <h3 class="rep-section__title">Thema's in de ideeën</h3>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
          <div v-for="(t, i) in ai.idea_themes" :key="i" class="rep-theme" :data-sentiment="t.sentiment">
            <div class="flex items-center justify-between mb-1.5">
              <h4 class="font-bold text-[var(--color-nav)]">{{ t.theme }}</h4>
              <span
                class="rep-badge"
                :class="{
                  'is-pos': t.sentiment === 'positief',
                  'is-neg': t.sentiment === 'negatief',
                  'is-mix': t.sentiment === 'gemengd',
                }"
              >{{ t.sentiment }}</span>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed">{{ t.description }}</p>
            <div v-if="t.example_ideas?.length" class="flex flex-wrap gap-1.5 mt-3">
              <span v-for="(ex, j) in t.example_ideas" :key="j" class="rep-chip">{{ ex }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- 7. Doelgroep -->
      <section v-if="ai?.audience_insight" class="rep-section">
        <div class="rep-section__head">
          <span class="rep-section__num">07</span>
          <h3 class="rep-section__title">Inzicht in de doelgroep</h3>
        </div>
        <p class="report-text">{{ ai.audience_insight }}</p>
        <div class="rep-charts mt-4">
          <div v-if="hasDemographics" class="rep-card">
            <p class="rep-card__title"><i class="fa-solid fa-cake-candles"></i> Leeftijdsopbouw</p>
            <div class="h-56"><Bar :data="ageChart" :options="chartOptions" /></div>
          </div>
          <div v-if="genderChart.labels.length" class="rep-card">
            <p class="rep-card__title"><i class="fa-solid fa-venus-mars"></i> Geslacht</p>
            <div class="h-56"><Doughnut :data="genderChart" :options="doughnutOptions" /></div>
          </div>
          <div v-if="educationChart.labels.length" class="rep-card">
            <p class="rep-card__title"><i class="fa-solid fa-graduation-cap"></i> Opleidingsniveau</p>
            <div class="h-56"><Doughnut :data="educationChart" :options="doughnutOptions" /></div>
          </div>
          <div v-if="sectorChart.labels.length" class="rep-card">
            <p class="rep-card__title"><i class="fa-solid fa-briefcase"></i> Sector</p>
            <div class="h-56"><Bar :data="sectorChart" :options="hBarOptions" /></div>
          </div>
          <div v-if="citiesChart.labels.length" class="rep-card">
            <p class="rep-card__title"><i class="fa-solid fa-location-dot"></i> Steden</p>
            <div class="h-56"><Bar :data="citiesChart" :options="hBarOptions" /></div>
          </div>
        </div>

        <!-- Koopgedrag & huishouden (optionele datavoorkeuren) -->
        <div v-if="ai?.buying_behavior_insight || hasDataProfile" class="mt-7">
          <h4 class="rep-subhead"><i class="fa-solid fa-cart-shopping"></i> Koopgedrag &amp; huishouden</h4>
          <p v-if="ai?.buying_behavior_insight" class="report-text mt-1">{{ ai.buying_behavior_insight }}</p>
          <p v-if="coverageNote" class="text-xs text-gray-400 mt-2">
            <i class="fa-solid fa-circle-info mr-1"></i>
            Gebaseerd op {{ coverageNote.shared }} van {{ coverageNote.participants }} deelnemers
            ({{ coverageNote.pct }}%) die optionele datavoorkeuren deelden.
          </p>
          <div v-if="hasDataProfile" class="rep-charts mt-4">
            <div v-if="orderFreqChart.labels.length" class="rep-card">
              <p class="rep-card__title"><i class="fa-solid fa-truck-fast"></i> Bestelfrequentie</p>
              <div class="h-56"><Bar :data="orderFreqChart" :options="chartOptions" /></div>
            </div>
            <div v-if="techSpendChart.labels.length" class="rep-card">
              <p class="rep-card__title"><i class="fa-solid fa-microchip"></i> Uitgaven technologie</p>
              <div class="h-56"><Bar :data="techSpendChart" :options="chartOptions" /></div>
            </div>
            <div v-if="grocerySpendChart.labels.length" class="rep-card">
              <p class="rep-card__title"><i class="fa-solid fa-basket-shopping"></i> Uitgaven boodschappen</p>
              <div class="h-56"><Bar :data="grocerySpendChart" :options="chartOptions" /></div>
            </div>
            <div v-if="householdChart.labels.length" class="rep-card">
              <p class="rep-card__title"><i class="fa-solid fa-house-user"></i> Huishoudgrootte</p>
              <div class="h-56"><Bar :data="householdChart" :options="chartOptions" /></div>
            </div>
            <div v-if="politicalChart.labels.length" class="rep-card">
              <p class="rep-card__title"><i class="fa-solid fa-landmark"></i> Politieke voorkeur</p>
              <div class="h-56"><Doughnut :data="politicalChart" :options="doughnutOptions" /></div>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400 mt-2">
            Nog geen deelnemers hebben optionele datavoorkeuren gedeeld.
          </p>
        </div>
      </section>

      <!-- 8. Segmenten -->
      <section v-if="ai && (ai.audience_segments?.length || ai.personas?.length)" class="rep-section">
        <div class="rep-section__head">
          <span class="rep-section__num">08</span>
          <h3 class="rep-section__title">Doelgroepsegmenten &amp; persona's</h3>
        </div>
        <div v-if="ai.audience_segments?.length" class="grid md:grid-cols-2 gap-4">
          <div v-for="(s, i) in ai.audience_segments" :key="i" class="rep-segment">
            <span class="rep-segment__icon"><i class="fa-solid fa-user-group"></i></span>
            <div>
              <h4 class="font-bold text-[var(--color-nav)] mb-0.5">{{ s.segment }}</h4>
              <p class="text-sm text-gray-600 leading-relaxed">{{ s.description }}</p>
            </div>
          </div>
        </div>

        <!-- Persona's -->
        <div v-if="ai.personas?.length" class="mt-6">
          <h4 class="rep-subhead"><i class="fa-solid fa-user-tag"></i> Persona's</h4>
          <div class="grid md:grid-cols-2 gap-4 mt-3">
            <div v-for="(p, i) in ai.personas" :key="i" class="rep-persona">
              <span class="rep-persona__avatar"><i class="fa-solid fa-user"></i></span>
              <div class="min-w-0">
                <h5 class="font-bold text-[var(--color-nav)]">{{ p.name }}</h5>
                <p class="text-xs text-gray-500 mb-1.5">{{ p.demographics }}</p>
                <p class="text-sm text-gray-600 leading-relaxed">{{ p.description }}</p>
                <p class="text-sm text-gray-700 mt-2 italic">
                  <i class="fa-solid fa-quote-left text-[var(--color-brand)] mr-1 text-xs"></i>{{ p.motivation }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 9. Hoofdvraag -->
      <section v-if="ai?.main_question_insights" class="rep-section">
        <div class="rep-section__head">
          <span class="rep-section__num">09</span>
          <h3 class="rep-section__title">Inzichten uit de hoofdvraag</h3>
        </div>
        <p class="report-text">{{ ai.main_question_insights }}</p>
        <div v-if="has(current?.metrics?.quizzes)" class="rep-card mt-4">
          <p class="rep-card__title"><i class="fa-solid fa-circle-question"></i> Quiz-deelname</p>
          <div class="h-56"><Bar :data="quizChart" :options="chartOptions" /></div>
        </div>
      </section>

      <!-- 10. Kansen & aandachtspunten -->
      <section v-if="ai && (ai.opportunities?.length || ai.risks?.length)" class="rep-section">
        <div class="rep-section__head">
          <span class="rep-section__num">10</span>
          <h3 class="rep-section__title">Kansen &amp; aandachtspunten</h3>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
          <div v-if="ai.opportunities?.length" class="rep-callout is-pos">
            <p class="rep-callout__title"><i class="fa-solid fa-arrow-trend-up"></i> Kansen</p>
            <ul>
              <li v-for="(o, i) in ai.opportunities" :key="i"><i class="fa-solid fa-check"></i><span>{{ o }}</span></li>
            </ul>
          </div>
          <div v-if="ai.risks?.length" class="rep-callout is-neg">
            <p class="rep-callout__title"><i class="fa-solid fa-triangle-exclamation"></i> Aandachtspunten</p>
            <ul>
              <li v-for="(r, i) in ai.risks" :key="i"><i class="fa-solid fa-exclamation"></i><span>{{ r }}</span></li>
            </ul>
          </div>
        </div>
      </section>

      <!-- 11. Strategische vooruitblik -->
      <section v-if="ai?.strategic_outlook" class="rep-section">
        <div class="rep-section__head">
          <span class="rep-section__num">11</span>
          <h3 class="rep-section__title">Strategische vooruitblik</h3>
        </div>
        <p class="report-text">{{ ai.strategic_outlook }}</p>
      </section>

      <!-- 12. Aanbevelingen -->
      <section v-if="ai?.recommendations?.length" class="rep-section">
        <div class="rep-section__head">
          <span class="rep-section__num">12</span>
          <h3 class="rep-section__title">Aanbevelingen</h3>
        </div>
        <div class="space-y-3">
          <div
            v-for="(r, i) in ai.recommendations"
            :key="i"
            class="rep-rec"
            :style="{ '--rec-accent': priorityColor(r.priority) }"
          >
            <div class="flex items-center justify-between gap-3 mb-1">
              <h4 class="font-bold text-[var(--color-nav)]">{{ r.title }}</h4>
              <span class="rep-rec__badge" :style="{ background: priorityColor(r.priority) }">{{ r.priority }}</span>
            </div>
            <p class="text-sm text-gray-700 leading-relaxed">{{ r.detail }}</p>
            <p v-if="r.expected_impact" class="rep-rec__impact">
              <i class="fa-solid fa-bullseye"></i>
              <span><strong>Verwachte impact:</strong> {{ r.expected_impact }}</span>
            </p>
          </div>
        </div>
      </section>

      <!-- 13. Actieplan -->
      <section v-if="ai?.action_plan?.length" class="rep-section">
        <div class="rep-section__head">
          <span class="rep-section__num">13</span>
          <h3 class="rep-section__title">Actieplan</h3>
        </div>
        <div class="rep-timeline">
          <div v-for="(p, i) in ai.action_plan" :key="i" class="rep-timeline__item">
            <span class="rep-timeline__dot">{{ i + 1 }}</span>
            <div class="rep-timeline__body">
              <div class="flex items-center justify-between gap-2 mb-1.5">
                <h4 class="font-bold text-[var(--color-nav)]">{{ p.phase }}</h4>
                <span class="rep-chip rep-chip--solid">{{ p.timeframe }}</span>
              </div>
              <ul class="space-y-1">
                <li v-for="(a, j) in p.actions" :key="j" class="flex gap-2 text-sm text-gray-700">
                  <i class="fa-solid fa-angle-right text-[var(--color-brand)] mt-1 text-xs"></i><span>{{ a }}</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <!-- 14. Conclusie -->
      <section v-if="ai?.conclusion" class="rep-section">
        <div class="rep-section__head">
          <span class="rep-section__num">14</span>
          <h3 class="rep-section__title">Conclusie</h3>
        </div>
        <p class="report-text">{{ ai.conclusion }}</p>
      </section>

      <!-- Voorgestelde volgende hoofdvraag -->
      <section v-if="ai?.suggested_main_question" class="rep-cta">
        <div class="rep-cta__glow"></div>
        <p class="rep-cta__eyebrow"><i class="fa-solid fa-lightbulb"></i> Voorgestelde volgende hoofdvraag</p>
        <p class="rep-cta__q">{{ ai.suggested_main_question }}</p>
      </section>
    </div>

    <div v-else-if="current?.status === 'failed'" class="text-red-600">
      Dit rapport kon niet worden gegenereerd. {{ current.error }}
    </div>
  </div>
</template>

<style scoped>
.report-printable {
  font-family: var(--font-default);
}
.report-text {
  color: #374151;
  line-height: 1.75;
  white-space: pre-line;
}

/* Titelblok */
.rep-hero {
  position: relative;
  overflow: hidden;
  border-radius: 1.25rem;
  padding: 1.75rem 2rem;
  color: #fff;
  background: linear-gradient(135deg, var(--color-nav), #111827);
}
.rep-hero__eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--color-brand);
}
.rep-hero__title {
  font-size: clamp(1.6rem, 3vw, 2.2rem);
  font-weight: 800;
  line-height: 1.1;
  margin: 0.35rem 0 0.4rem;
}
.rep-hero__meta {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
}

/* KPI band */
.rep-kpis {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}
@media (min-width: 768px) {
  .rep-kpis {
    grid-template-columns: repeat(4, 1fr);
  }
}
.rep-kpi {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: #fff;
  border: 1px solid #eef0f3;
  border-radius: 1rem;
  padding: 0.9rem 1rem;
  box-shadow: 0 4px 14px rgba(31, 41, 55, 0.05);
}
.rep-kpi__icon {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.7rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 0.85rem;
  flex-shrink: 0;
}
.rep-kpi__value {
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--color-nav);
  line-height: 1;
}
.rep-kpi__label {
  font-size: 0.72rem;
  color: #6b7280;
  margin-top: 0.2rem;
}
.rep-kpi__delta {
  position: absolute;
  top: 0.55rem;
  right: 0.55rem;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 0.1rem 0.4rem;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  gap: 0.2rem;
}
.rep-kpi__delta.is-up {
  background: #dcfce7;
  color: #15803d;
}
.rep-kpi__delta.is-down {
  background: #fee2e2;
  color: #b91c1c;
}

/* Secties */
.rep-section__head {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.85rem;
}
.rep-section__num {
  font-size: 0.8rem;
  font-weight: 800;
  color: var(--color-brand);
  background: rgba(247, 138, 29, 0.12);
  border-radius: 0.6rem;
  padding: 0.35rem 0.55rem;
  line-height: 1;
}
.rep-section__title {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--color-nav);
}

/* Lead / samenvatting */
.rep-lead {
  border-left: 4px solid var(--color-brand);
  background: var(--color-bg);
  border-radius: 0 1rem 1rem 0;
  padding: 1rem 1.25rem;
}
.rep-lead .report-text {
  font-size: 1.02rem;
}
.rep-findings {
  margin-top: 1rem;
  display: grid;
  gap: 0.5rem;
}
@media (min-width: 768px) {
  .rep-findings {
    grid-template-columns: 1fr 1fr;
  }
}
.rep-findings li {
  display: flex;
  gap: 0.55rem;
  color: #374151;
  font-size: 0.92rem;
}
.rep-findings li i {
  color: var(--color-success);
  margin-top: 0.2rem;
}

/* Grafiek-kaarten */
.rep-charts {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.25rem;
  margin-top: 1rem;
}
@media (min-width: 1024px) {
  .rep-charts {
    grid-template-columns: 1fr 1fr;
  }
}
.rep-card {
  border: 1px solid #eef0f3;
  border-radius: 1rem;
  padding: 1.1rem 1.1rem 0.9rem;
  background: #fff;
  box-shadow: 0 4px 14px rgba(31, 41, 55, 0.04);
}
.rep-card__title {
  font-weight: 700;
  font-size: 0.85rem;
  color: var(--color-nav);
  margin-bottom: 0.85rem;
  display: flex;
  align-items: center;
  gap: 0.45rem;
}
.rep-card__title i {
  color: var(--color-brand);
}
.rep-subhead {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1rem;
  font-weight: 800;
  color: var(--color-nav);
  padding-top: 0.5rem;
  margin-bottom: 0.25rem;
  border-top: 1px solid #eef0f3;
}
.rep-subhead i {
  color: var(--color-brand);
}

/* Merkgezondheid */
.rep-health {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
  border: 1px solid #eef0f3;
  border-radius: 1.25rem;
  padding: 1.25rem 1.5rem;
  background: #fff;
  box-shadow: 0 4px 14px rgba(31, 41, 55, 0.05);
}
.rep-health__gauge {
  position: relative;
  width: 9rem;
  height: 9rem;
  flex-shrink: 0;
}
.rep-health__center {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.rep-health__score {
  font-size: 2.1rem;
  font-weight: 800;
  line-height: 1;
}
.rep-health__max {
  font-size: 0.7rem;
  color: #9ca3af;
}
.rep-health__body {
  flex: 1;
  min-width: 14rem;
}
.rep-health__eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 0.7rem;
  font-weight: 700;
  color: #9ca3af;
}
.rep-health__label {
  font-size: 1.4rem;
  font-weight: 800;
  margin-bottom: 0.6rem;
}
.rep-health__bars {
  display: grid;
  gap: 0.55rem;
}
.rep-health__track {
  height: 0.5rem;
  border-radius: 999px;
  background: #eef0f3;
  overflow: hidden;
}
.rep-health__fill {
  height: 100%;
  border-radius: 999px;
}

/* Quick wins */
.rep-quickwins {
  border-radius: 1.25rem;
  padding: 1.25rem 1.5rem;
  background: linear-gradient(135deg, rgba(247, 138, 29, 0.1), rgba(247, 138, 29, 0.03));
  border: 1px solid rgba(247, 138, 29, 0.25);
}
.rep-quickwins__title {
  font-weight: 800;
  color: var(--color-nav);
  margin-bottom: 0.85rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.rep-quickwins__title i {
  color: var(--color-brand);
}
.rep-quickwin {
  display: flex;
  gap: 0.7rem;
  background: #fff;
  border: 1px solid rgba(247, 138, 29, 0.2);
  border-radius: 0.85rem;
  padding: 0.85rem 0.95rem;
}
.rep-quickwin__num {
  width: 1.6rem;
  height: 1.6rem;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--color-brand);
  color: #fff;
  font-weight: 800;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
}
.rep-quickwin p {
  font-size: 0.88rem;
  color: #374151;
  line-height: 1.45;
}

/* Persona's */
.rep-persona {
  display: flex;
  gap: 0.9rem;
  border: 1px solid #eef0f3;
  border-radius: 1rem;
  padding: 1.1rem;
  background: #fff;
  box-shadow: 0 2px 8px rgba(31, 41, 55, 0.04);
}
.rep-persona__avatar {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  flex-shrink: 0;
  background: linear-gradient(135deg, var(--color-brand), #f97316);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

/* Thema's */
.rep-theme {
  border: 1px solid #eef0f3;
  border-top: 3px solid #e5e7eb;
  border-radius: 1rem;
  padding: 1.1rem;
  background: #fff;
  box-shadow: 0 2px 8px rgba(31, 41, 55, 0.04);
}
.rep-theme[data-sentiment="positief"] {
  border-top-color: var(--color-success);
}
.rep-theme[data-sentiment="negatief"] {
  border-top-color: var(--color-error);
}
.rep-theme[data-sentiment="gemengd"] {
  border-top-color: #f59e0b;
}
.rep-badge {
  font-size: 0.68rem;
  font-weight: 700;
  padding: 0.15rem 0.55rem;
  border-radius: 999px;
  text-transform: capitalize;
}
.rep-badge.is-pos {
  background: #dcfce7;
  color: #15803d;
}
.rep-badge.is-neg {
  background: #fee2e2;
  color: #b91c1c;
}
.rep-badge.is-mix {
  background: #fef3c7;
  color: #b45309;
}
.rep-chip {
  font-size: 0.72rem;
  background: var(--color-bg);
  color: #4b5563;
  border: 1px solid #e5e7eb;
  border-radius: 999px;
  padding: 0.15rem 0.6rem;
}
.rep-chip--solid {
  background: var(--color-nav);
  color: #fff;
  border-color: var(--color-nav);
  font-weight: 600;
}

/* Segmenten */
.rep-segment {
  display: flex;
  gap: 0.85rem;
  border: 1px solid #eef0f3;
  border-radius: 1rem;
  padding: 1rem;
  background: #fff;
}
.rep-segment__icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.8rem;
  background: rgba(59, 130, 246, 0.12);
  color: #3b82f6;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* Callouts */
.rep-callout {
  border-radius: 1rem;
  padding: 1.1rem 1.25rem;
}
.rep-callout.is-pos {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
}
.rep-callout.is-neg {
  background: #fef2f2;
  border: 1px solid #fecaca;
}
.rep-callout__title {
  font-weight: 800;
  margin-bottom: 0.6rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.rep-callout.is-pos .rep-callout__title {
  color: #15803d;
}
.rep-callout.is-neg .rep-callout__title {
  color: #b91c1c;
}
.rep-callout ul {
  display: grid;
  gap: 0.5rem;
}
.rep-callout li {
  display: flex;
  gap: 0.55rem;
  font-size: 0.9rem;
  color: #374151;
}
.rep-callout.is-pos li i {
  color: var(--color-success);
  margin-top: 0.2rem;
}
.rep-callout.is-neg li i {
  color: var(--color-error);
  margin-top: 0.2rem;
}

/* Aanbevelingen */
.rep-rec {
  border: 1px solid #eef0f3;
  border-left: 5px solid var(--rec-accent, #9ca3af);
  border-radius: 0.9rem;
  padding: 1rem 1.15rem;
  background: #fff;
  box-shadow: 0 2px 8px rgba(31, 41, 55, 0.04);
}
.rep-rec__badge {
  color: #fff;
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
}
.rep-rec__impact {
  display: flex;
  gap: 0.5rem;
  font-size: 0.8rem;
  color: #6b7280;
  margin-top: 0.6rem;
}
.rep-rec__impact i {
  color: var(--color-brand);
  margin-top: 0.15rem;
}

/* Tijdlijn (actieplan) */
.rep-timeline {
  position: relative;
}
.rep-timeline__item {
  position: relative;
  display: flex;
  gap: 1rem;
  padding-bottom: 1.25rem;
}
.rep-timeline__item:not(:last-child)::before {
  content: "";
  position: absolute;
  left: 1.05rem;
  top: 2.3rem;
  bottom: 0;
  width: 2px;
  background: #e5e7eb;
}
.rep-timeline__dot {
  width: 2.2rem;
  height: 2.2rem;
  border-radius: 50%;
  background: var(--color-nav);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.85rem;
  flex-shrink: 0;
  z-index: 1;
}
.rep-timeline__body {
  flex: 1;
  border: 1px solid #eef0f3;
  border-radius: 1rem;
  padding: 0.9rem 1.1rem;
  background: #fff;
}

/* CTA — volgende hoofdvraag */
.rep-cta {
  position: relative;
  overflow: hidden;
  border-radius: 1.25rem;
  padding: 1.75rem 2rem;
  color: #fff;
  background: linear-gradient(135deg, var(--color-nav), #0f172a);
}
.rep-cta__glow {
  position: absolute;
  top: -40%;
  right: -10%;
  width: 16rem;
  height: 16rem;
  border-radius: 50%;
  background: rgba(247, 138, 29, 0.25);
  filter: blur(60px);
}
.rep-cta__eyebrow {
  position: relative;
  text-transform: uppercase;
  letter-spacing: 0.16em;
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--color-brand);
  margin-bottom: 0.5rem;
}
.rep-cta__q {
  position: relative;
  font-size: 1.3rem;
  font-weight: 700;
  line-height: 1.35;
}
/* Voorblad + inhoudsopgave: alleen bij printen/PDF zichtbaar */
.report-cover,
.report-toc {
  display: none;
}

@media print {
  .no-print {
    display: none !important;
  }
  section {
    break-inside: avoid;
  }
  /* Behoud achtergronden/kleuren in de PDF (anders wordt witte tekst onzichtbaar) */
  .report-printable,
  .report-printable * {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  /* Voorblad — volledige pagina, navy met merkaccent */
  .report-cover {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 96vh;
    background: var(--color-nav);
    color: #fff;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    border-radius: 16px;
    overflow: hidden;
    page-break-after: always;
    break-after: page;
  }
  .report-cover__bar {
    padding: 2rem 2.5rem;
  }
  .report-cover__brand {
    height: 34px;
    width: auto;
  }
  .report-cover__body {
    padding: 0 2.5rem;
  }
  .report-cover__logo {
    max-height: 110px;
    max-width: 55%;
    object-fit: contain;
    background: #fff;
    padding: 12px;
    border-radius: 16px;
    margin-bottom: 1.75rem;
  }
  .report-cover__eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.22em;
    font-weight: 700;
    font-size: 0.8rem;
    color: var(--color-brand);
  }
  .report-cover__title {
    font-size: 2.8rem;
    font-weight: 800;
    line-height: 1.08;
    margin: 0.4rem 0;
  }
  .report-cover__period {
    font-size: 1.2rem;
    opacity: 0.85;
  }
  .report-cover__date {
    opacity: 0.6;
    margin-top: 0.4rem;
  }
  .report-cover__footer {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.75rem 2.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    font-size: 0.78rem;
    opacity: 0.7;
  }

  /* Inhoudsopgave */
  .report-toc {
    display: block;
    page-break-after: always;
    break-after: page;
  }
  .report-toc__title {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--color-nav);
    margin-bottom: 1rem;
  }
  .report-toc__list {
    margin: 0;
    padding-left: 1.25rem;
  }
  .report-toc__list li {
    list-style: decimal;
    padding: 0.5rem 0;
    border-bottom: 1px dashed #e5e7eb;
    color: #374151;
    font-weight: 600;
  }
}
</style>
