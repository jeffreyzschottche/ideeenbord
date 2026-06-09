<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { brandOwnerApiFetch } from "~/composables/brand/useBrandOwnerApi";
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

async function loadAll() {
  loading.value = true;
  error.value = null;
  try {
    raw.value = await brandOwnerApiFetch<RawExportResponse>(
      `/brands/${props.brandId}/raw-export`
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
  const v =
    value === null || value === undefined
      ? ""
      : typeof value === "object"
      ? JSON.stringify(value)
      : String(value);
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

onMounted(loadAll);
</script>

<template>
  <div class="card p-4 md:p-6">
    <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
      <h3 class="title-md">Rauwe gegevens</h3>

      <div class="flex items-center gap-2">
        <button class="btn btn--ghost btn--sm" @click="loadAll">
          ↻ Vernieuwen
        </button>
        <span class="h-5 w-px bg-neutral-200" />
        <button
          class="btn btn--sm"
          :disabled="!canDownloadAnything"
          @click="downloadCombinedCSV"
        >
          ⬇️ CSV · gecombineerd
        </button>
        <button
          class="btn btn--sm"
          :disabled="!canDownloadAnything"
          @click="downloadCombinedJSON"
        >
          ⬇️ JSON · gecombineerd
        </button>
        <button
          class="btn btn--sm"
          :disabled="!canDownloadAnything"
          @click="downloadCombinedXML"
        >
          ⬇️ XML · gecombineerd
        </button>
      </div>
    </div>

    <p class="muted-text mb-4">
      Exporteer alle data die bij jouw merk hoort. E-mails en andere privévelden
      van gebruikers worden niet meegestuurd.
    </p>

    <div v-if="loading" class="muted-text">Gegevens laden…</div>
    <div v-else-if="error" class="text-red-600">{{ error }}</div>

    <div v-else class="space-y-8">
      <!-- Brand -->
      <section>
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-semibold">Brand</h4>
          <div class="flex gap-2">
            <button
              class="btn btn--sm"
              :disabled="!brandExport"
              @click="downloadJSON('brand', brandExport)"
            >
              Download JSON
            </button>
            <button
              class="btn btn--sm"
              :disabled="!brandExport"
              @click="downloadCSV('brand', brandExport ? [brandExport] : [])"
            >
              Download CSV
            </button>
            <button
              class="btn btn--sm"
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
              class="btn btn--sm"
              :disabled="!ideasExport.length"
              @click="downloadJSON('ideas', ideasExport)"
            >
              Download JSON
            </button>
            <button
              class="btn btn--sm"
              :disabled="!ideasExport.length"
              @click="downloadCSV('ideas', ideasExport)"
            >
              Download CSV
            </button>
            <button
              class="btn btn--sm"
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
              class="btn btn--sm"
              :disabled="!participantsExport.length"
              @click="downloadJSON('participants', participantsExport)"
            >
              Download JSON
            </button>
            <button
              class="btn btn--sm"
              :disabled="!participantsExport.length"
              @click="downloadCSV('participants', participantsExport)"
            >
              Download CSV
            </button>
            <button
              class="btn btn--sm"
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
