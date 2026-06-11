<script setup lang="ts">
import { ref, reactive, onMounted, computed } from "vue";
import { brandOwnerApiFetch } from "~/composables/brand/useBrandOwnerApi";
import { reportService, type ReportRange, type ReportPeriodType } from "~/services/api/brand/reportService";
import type { Idea } from "~/types/idea";

type RawUser = {
  id: number;
  gender?: string | null;
  birthdate?: string | null;
  education_level?: string | null;
  education?: string | null;
  job?: string | null;
  sector?: string | null;
  city?: string | null;
  birth_city?: string | null;
  relationship_status?: string | null;
  postal_code?: string | null;
  ratings_given?: any[] | null;
  created_posts?: any[] | null;
};

type RawExportResponse = {
  brand: Record<string, any> | null;
  ideas: Idea[];
  participants: RawUser[];
};

const props = defineProps<{
  brandId: number;
  brandSlug: string;
}>();

const loading = ref(true);
const error = ref<string | null>(null);
const raw = ref<RawExportResponse | null>(null);

// ===== periode-selectie (zelfde idee als het rapport) =====
const period = reactive<{ type: ReportPeriodType; start: string; end: string }>({
  type: "all",
  start: "",
  end: "",
});
const range = ref<ReportRange | null>(null);
const months = computed(() => range.value?.months ?? []);
const periodModes: { value: ReportPeriodType; label: string }[] = [
  { value: "all", label: "Alles" },
  { value: "monthly", label: "Maandelijks" },
  { value: "custom", label: "Aangepast" },
];

function lastDayOfMonth(ym: string): string {
  const [y, m] = ym.split("-").map(Number);
  return new Date(y, m, 0).toISOString().slice(0, 10); // dag 0 van volgende maand = laatste dag
}

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

const periodQuery = computed(() => {
  if (period.type === "all" || !period.start || !period.end) return "";
  const start = period.type === "monthly" ? `${period.start}-01` : period.start;
  const end = period.type === "monthly" ? lastDayOfMonth(period.end) : period.end;
  return `?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`;
});

async function loadRange() {
  try {
    range.value = await reportService.range(props.brandId);
  } catch {
    /* niet kritisch */
  }
}

async function loadAll() {
  loading.value = true;
  error.value = null;
  try {
    raw.value = await brandOwnerApiFetch<RawExportResponse>(
      `/brands/${props.brandId}/raw-export${periodQuery.value}`
    );
  } catch (e: any) {
    error.value = e?.message || "Raw export ophalen mislukt.";
    raw.value = null;
  } finally {
    loading.value = false;
  }
}

// ===== helpers =====
function escapeCSV(value: any): string {
  let v =
    value === null || value === undefined
      ? ""
      : typeof value === "object"
      ? JSON.stringify(value)
      : String(value);
  // Voorkom CSV/formule-injectie: waarden die met =, +, -, @, tab of CR beginnen
  // worden door Excel/Sheets als formule uitgevoerd. Prefix met een apostrof.
  if (/^[=+\-@\t\r]/.test(v)) v = `'${v}`;
  if (/[",\r\n]/.test(v)) return `"${v.replace(/"/g, '""')}"`;
  return v;
}

function objectsToCSV(rows: Array<Record<string, any>>): string {
  if (!rows || rows.length === 0) return "";
  const headerSet = new Set<string>();
  for (const row of rows)
    for (const key of Object.keys(row)) headerSet.add(key);
  const headers = Array.from(headerSet);
  const lines: string[] = [];
  lines.push(headers.map((h) => escapeCSV(h)).join(","));
  for (const row of rows)
    lines.push(headers.map((h) => escapeCSV((row as any)[h])).join(","));
  return lines.join("\r\n");
}

function downloadBlob(filename: string, content: string, type: string) {
  const blob = new Blob([content], { type });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
}

function downloadJSON(name: string, data: any) {
  const content = JSON.stringify(data, null, 2);
  downloadBlob(`${name}.json`, content, "application/json;charset=utf-8");
}
function downloadCSV(name: string, rows: any[]) {
  const csv = objectsToCSV(rows);
  downloadBlob(`${name}.csv`, csv, "text/csv;charset=utf-8");
}

// ===== XML =====
function escapeXML(value: any): string {
  const v =
    value === null || value === undefined
      ? ""
      : typeof value === "object"
      ? JSON.stringify(value)
      : String(value);
  return v
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}
function safeTag(key: string): string {
  const tag = String(key).replace(/[^a-zA-Z0-9_.-]/g, "_");
  return /^[a-zA-Z_]/.test(tag) ? tag : `_${tag}`;
}
function recordToXML(record: Record<string, any>, indent = "    "): string {
  return Object.entries(record)
    .map(([k, v]) => `${indent}<${safeTag(k)}>${escapeXML(v)}</${safeTag(k)}>`)
    .join("\n");
}
function objectsToXML(rows: any[], root: string, item: string): string {
  const body = rows
    .map((r) => `  <${item}>\n${recordToXML(r)}\n  </${item}>`)
    .join("\n");
  return `<?xml version="1.0" encoding="UTF-8"?>\n<${root}>\n${body}\n</${root}>`;
}
function downloadXML(name: string, rows: any[], root: string, item: string) {
  downloadBlob(`${name}.xml`, objectsToXML(rows, root, item), "application/xml;charset=utf-8");
}

// flatten voor combined CSV
function isPlainObject(val: any) {
  return val && typeof val === "object" && !Array.isArray(val);
}
function flattenRecord(
  input: Record<string, any>,
  prefix = ""
): Record<string, any> {
  const out: Record<string, any> = {};
  for (const [k, v] of Object.entries(input)) {
    const key = prefix ? `${prefix}.${k}` : k;
    if (Array.isArray(v)) out[key] = JSON.stringify(v);
    else if (isPlainObject(v)) Object.assign(out, flattenRecord(v, key));
    else out[key] = v;
  }
  return out;
}

// mappings
const brandExport = computed(() => raw.value?.brand ?? null);
const ideasExport = computed(() => raw.value?.ideas ?? []);
const participantsExport = computed(() => raw.value?.participants ?? []);
const filenameBase = computed(() =>
  props.brandSlug ? props.brandSlug : "export"
);
const canDownloadAnything = computed(
  () =>
    !!brandExport.value ||
    ideasExport.value.length > 0 ||
    participantsExport.value.length > 0
);

// combined downloads (één bestand)
function downloadCombinedJSON() {
  if (!canDownloadAnything.value) return;
  const payload = {
    brand: brandExport.value,
    ideas: ideasExport.value,
    participants: participantsExport.value,
    generatedAt: new Date().toISOString(),
  };
  downloadJSON(`${filenameBase.value}-raw-export`, payload);
}

function buildCombinedRows(): Array<Record<string, any>> {
  const rows: Record<string, any>[] = [];
  if (brandExport.value)
    rows.push({ dataset: "brand", ...flattenRecord(brandExport.value) });
  for (const i of ideasExport.value)
    rows.push({ dataset: "idea", ...flattenRecord(i as any) });
  for (const u of participantsExport.value)
    rows.push({ dataset: "participant", ...flattenRecord(u as any) });
  return rows;
}
function downloadCombinedCSV() {
  const rows = buildCombinedRows();
  if (!rows.length) return;
  downloadCSV(`${filenameBase.value}-raw-export-combined`, rows);
}
function downloadCombinedXML() {
  const rows = buildCombinedRows();
  if (!rows.length) return;
  downloadXML(`${filenameBase.value}-raw-export-combined`, rows, "export", "record");
}

onMounted(() => {
  loadRange();
  loadAll();
});
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
      <p class="font-semibold text-[var(--color-nav)]">
        <i class="fa-solid fa-box-archive mr-1.5 text-[var(--color-brand)]"></i>
        Volledige export
      </p>

      <div class="flex items-center gap-2">
        <button class="btn btn--ghost btn--sm" @click="loadAll">
          <i class="fa-solid fa-rotate-right"></i> Vernieuwen
        </button>
        <span class="h-5 w-px bg-neutral-200" />
        <button class="btn btn--ghost btn--sm" :disabled="!canDownloadAnything" @click="downloadCombinedCSV">
          <i class="fa-solid fa-download"></i> CSV
        </button>
        <button class="btn btn--ghost btn--sm" :disabled="!canDownloadAnything" @click="downloadCombinedJSON">
          <i class="fa-solid fa-download"></i> JSON
        </button>
        <button class="btn btn--ghost btn--sm" :disabled="!canDownloadAnything" @click="downloadCombinedXML">
          <i class="fa-solid fa-download"></i> XML
        </button>
      </div>
    </div>

    <p class="muted-text mb-3">
      Exporteer alle data die bij jouw merk hoort. E-mails en andere privévelden
      van gebruikers worden niet meegestuurd.
    </p>

    <!-- Periode-selectie -->
    <div class="flex flex-wrap items-end gap-3 mb-4 p-3 rounded-xl bg-neutral-50 border border-neutral-200">
      <div>
        <p class="text-xs text-neutral-500 mb-1">Periode</p>
        <div class="inline-flex rounded-lg border border-neutral-300 overflow-hidden">
          <button
            v-for="m in periodModes"
            :key="m.value"
            class="px-3 py-1.5 text-sm font-medium"
            :class="period.type === m.value ? 'bg-[var(--color-nav)] text-white' : 'bg-white text-neutral-600 hover:bg-neutral-50'"
            @click="setMode(m.value)"
          >
            {{ m.label }}
          </button>
        </div>
      </div>

      <template v-if="period.type === 'monthly' && months.length">
        <div>
          <p class="text-xs text-neutral-500 mb-1">Van maand</p>
          <select v-model="period.start" class="border border-neutral-300 rounded-lg px-3 py-1.5 text-sm">
            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
        </div>
        <div>
          <p class="text-xs text-neutral-500 mb-1">Tot maand</p>
          <select v-model="period.end" class="border border-neutral-300 rounded-lg px-3 py-1.5 text-sm">
            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
        </div>
      </template>

      <template v-else-if="period.type === 'custom'">
        <div>
          <p class="text-xs text-neutral-500 mb-1">Van datum</p>
          <input v-model="period.start" type="date" :min="range?.first ?? undefined" :max="range?.last ?? undefined" class="border border-neutral-300 rounded-lg px-3 py-1.5 text-sm" />
        </div>
        <div>
          <p class="text-xs text-neutral-500 mb-1">Tot datum</p>
          <input v-model="period.end" type="date" :min="range?.first ?? undefined" :max="range?.last ?? undefined" class="border border-neutral-300 rounded-lg px-3 py-1.5 text-sm" />
        </div>
      </template>

      <button class="btn btn--sm" @click="loadAll">Toepassen</button>
    </div>

    <div v-if="loading" class="muted-text">Gegevens laden…</div>
    <div v-else-if="error" class="text-red-600">{{ error }}</div>

    <div v-else class="space-y-8">
      <!-- Brand -->
      <section>
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-semibold">Brand</h4>
          <div class="flex gap-2">
            <button
              class="btn btn--ghost btn--sm"
              :disabled="!brandExport"
              @click="downloadJSON('brand', brandExport)"
            >
              Download JSON
            </button>
            <button
              class="btn btn--ghost btn--sm"
              :disabled="!brandExport"
              @click="downloadCSV('brand', brandExport ? [brandExport] : [])"
            >
              Download CSV
            </button>
            <button
              class="btn btn--ghost btn--sm"
              :disabled="!brandExport"
              @click="downloadXML('brand', brandExport ? [brandExport] : [], 'brand', 'field')"
            >
              Download XML
            </button>
          </div>
        </div>

        <div v-if="brandExport" class="overflow-x-auto">
          <!-- <table class="table w-full">
            <thead>
              <tr>
                <th class="text-left">Key</th>
                <th class="text-left">Value</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(val, key) in brandExport" :key="String(key)">
                <td class="align-top font-mono text-xs md:text-sm">
                  {{ key }}
                </td>
                <td class="align-top text-xs md:text-sm break-all">
                  <pre class="whitespace-pre-wrap">{{
                    typeof val === "object" ? JSON.stringify(val) : val
                  }}</pre>
                </td>
              </tr>
            </tbody>
          </table> -->
        </div>
        <div v-else class="muted-text">Geen brand gevonden.</div>
      </section>

      <hr class="divider" />

      <!-- Ideas -->
      <section>
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-semibold">Ideas ({{ ideasExport.length }})</h4>
          <div class="flex gap-2">
            <button
              class="btn btn--ghost btn--sm"
              :disabled="!ideasExport.length"
              @click="downloadJSON('ideas', ideasExport)"
            >
              Download JSON
            </button>
            <button
              class="btn btn--ghost btn--sm"
              :disabled="!ideasExport.length"
              @click="downloadCSV('ideas', ideasExport)"
            >
              Download CSV
            </button>
            <button
              class="btn btn--ghost btn--sm"
              :disabled="!ideasExport.length"
              @click="downloadXML('ideas', ideasExport, 'ideas', 'idea')"
            >
              Download XML
            </button>
          </div>
        </div>

        <div v-if="ideasExport.length" class="overflow-x-auto">
          <!-- <table class="table w-full">
            <thead>
              <tr>
                <th class="text-left">ID</th>
                <th class="text-left">Title</th>
                <th class="text-left">Status</th>
                <th class="text-left">Likes</th>
                <th class="text-left">Dislikes</th>
                <th class="text-left">User</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in ideasExport.slice(0, 10)" :key="row.id">
                <td class="text-xs md:text-sm">{{ row.id }}</td>
                <td class="text-xs md:text-sm">{{ row.title }}</td>
                <td class="text-xs md:text-sm">{{ row.status }}</td>
                <td class="text-xs md:text-sm">{{ row.likes }}</td>
                <td class="text-xs md:text-sm">{{ row.dislikes }}</td>
                <td class="text-xs md:text-sm">
                  {{ row.user?.username ?? "#" + row.user_id }}
                </td>
              </tr>
            </tbody>
          </table> -->
          <p v-if="ideasExport.length > 10" class="muted-text mt-1">
            Voorvertoning toont de eerste 10 regels.
          </p>
        </div>
        <div v-else class="muted-text">Geen ideeën gevonden.</div>
      </section>

      <hr class="divider" />

      <!-- Participants -->
      <section>
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-semibold">
            Deelnemers / Users ({{ participantsExport.length }})
          </h4>
          <div class="flex gap-2">
            <button
              class="btn btn--ghost btn--sm"
              :disabled="!participantsExport.length"
              @click="downloadJSON('participants', participantsExport)"
            >
              Download JSON
            </button>
            <button
              class="btn btn--ghost btn--sm"
              :disabled="!participantsExport.length"
              @click="downloadCSV('participants', participantsExport)"
            >
              Download CSV
            </button>
            <button
              class="btn btn--ghost btn--sm"
              :disabled="!participantsExport.length"
              @click="downloadXML('participants', participantsExport, 'participants', 'participant')"
            >
              Download XML
            </button>
          </div>
        </div>

        <div v-if="participantsExport.length" class="overflow-x-auto">
          <!-- <table class="table w-full">
            <thead>
              <tr>
                <th class="text-left">ID</th>
                <th class="text-left">Gender</th>
                <th class="text-left">Birthdate</th>
                <th class="text-left">Education</th>
                <th class="text-left">Job</th>
                <th class="text-left">City</th>
                <th class="text-left">Postal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in participantsExport.slice(0, 10)" :key="u.id">
                <td class="text-xs md:text-sm">{{ u.id }}</td>
                <td class="text-xs md:text-sm">{{ u.gender ?? "-" }}</td>
                <td class="text-xs md:text-sm">{{ u.birthdate ?? "-" }}</td>
                <td class="text-xs md:text-sm">
                  {{ u.education_level || u.education || "-" }}
                </td>
                <td class="text-xs md:text-sm">{{ u.job ?? "-" }}</td>
                <td class="text-xs md:text-sm">{{ u.city ?? "-" }}</td>
                <td class="text-xs md:text-sm">{{ u.postal_code ?? "-" }}</td>
              </tr>
            </tbody>
          </table> -->
          <p v-if="participantsExport.length > 10" class="muted-text mt-1">
            Voorvertoning toont de eerste 10 regels.
          </p>
        </div>
        <div v-else class="muted-text">Geen participants gevonden.</div>
      </section>
    </div>
  </div>
</template>

<style scoped lang="postcss">
.card {
  @apply rounded-2xl border border-neutral-200 bg-white shadow-sm;
}
.table th,
.table td {
  @apply px-3 py-2;
}
.btn {
  @apply inline-flex items-center justify-center rounded-xl border px-3 py-1.5 text-sm;
}
.btn--sm {
  @apply text-xs px-2 py-1;
}
.btn--ghost {
  @apply border-transparent hover:border-neutral-200;
}
.title-md {
  @apply text-lg font-semibold;
}
.muted-text {
  @apply text-neutral-500;
}
.divider {
  @apply my-4 border-t border-neutral-200;
}
</style>
