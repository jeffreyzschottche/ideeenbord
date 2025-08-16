<script setup lang="ts">
import { computed, ref } from "vue";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import { useCookie, useRuntimeConfig } from "#app";

const props = defineProps<{
  brandId: number;
  brandSlug: string;
}>();

// ==== brondata ====
const config = useRuntimeConfig();
const baseApi = computed(() =>
  String(config.public.apiBaseUrl || "").replace(/\/$/, "")
); // bv http://localhost:8000/api
const apiPrefix = "/v1";
const store = useBrandOwnerAuthStore();
const tokenCookie = useCookie<string | null>("bo_token");
const bearer = computed(() => store.token || tokenCookie.value || "");
const showToken = ref(false);

const endpoints = computed(() => {
  const id = props.brandId;
  return [
    {
      key: "raw-export",
      label: "Raw export (alles)",
      method: "GET",
      path: `/brands/${id}/raw-export`,
      desc: "Volledige dataset voor dit merk (brand + ideas + participants).",
    },
    {
      key: "ideas",
      label: "Ideas voor brand",
      method: "GET",
      path: `/brands/${id}/ideas`,
      desc: "Alle ideeën die bij dit merk horen.",
    },
    {
      key: "participants",
      label: "Participants",
      method: "GET",
      path: `/brands/${id}/participants`,
      desc: "Gebruikers (whitelisted velden) die bij dit merk betrokken zijn.",
    },
  ];
});

type Lang = "curl" | "js-fetch" | "axios" | "python" | "php-curl";
const languages: { key: Lang; label: string }[] = [
  { key: "curl", label: "cURL" },
  { key: "js-fetch", label: "JS fetch" },
  { key: "axios", label: "Axios" },
  { key: "python", label: "Python" },
  { key: "php-curl", label: "PHP cURL" },
];

const activeEndpointKey = ref(endpoints.value[0].key);
const activeLang = ref<Lang>("curl");

const activeEndpoint = computed(() => {
  return endpoints.value.find((e) => e.key === activeEndpointKey.value)!;
});

const fullUrl = computed(
  () => `${baseApi.value}${apiPrefix}${activeEndpoint.value.path}`
);

// ==== codegen helpers ====
function h(name: string, value: string) {
  return `'${name}: ${value.replace(/'/g, "\\'")}'`;
}
function authHeader(val: string) {
  const t = val || "YOUR_TOKEN_HERE";
  return `Authorization: Bearer ${t}`;
}

const code = computed(() => {
  const url = fullUrl.value;
  const tok = bearer.value;

  switch (activeLang.value) {
    case "curl":
      return [
        `curl -X ${activeEndpoint.value.method} '${url}' \\`,
        `  -H ${h("Accept", "application/json")} \\`,
        `  -H ${h("Authorization", `Bearer ${tok || "YOUR_TOKEN_HERE"}`)}`,
      ].join("\n");

    case "js-fetch":
      return [
        `const url = '${url}';`,
        `const res = await fetch(url, {`,
        `  method: '${activeEndpoint.value.method}',`,
        `  headers: {`,
        `    'Accept': 'application/json',`,
        `    'Authorization': 'Bearer ${tok || "YOUR_TOKEN_HERE"}'`,
        `  }`,
        `});`,
        `const data = await res.json();`,
        `console.log(data);`,
      ].join("\n");

    case "axios":
      return [
        `import axios from 'axios';`,
        ``,
        `const client = axios.create({`,
        `  baseURL: '${baseApi.value}${apiPrefix}',`,
        `  headers: {`,
        `    'Accept': 'application/json',`,
        `    'Authorization': 'Bearer ${tok || "YOUR_TOKEN_HERE"}'`,
        `  }`,
        `});`,
        ``,
        `const { data } = await client.${activeEndpoint.value.method.toLowerCase()}('${
          activeEndpoint.value.path
        }');`,
        `console.log(data);`,
      ].join("\n");

    case "python":
      return [
        `import requests`,
        ``,
        `headers = {`,
        `    'Accept': 'application/json',`,
        `    'Authorization': 'Bearer ${tok || "YOUR_TOKEN_HERE"}'`,
        `}`,
        `r = requests.${activeEndpoint.value.method.toLowerCase()}('${url}', headers=headers)`,
        `print(r.status_code)`,
        `print(r.json())`,
      ].join("\n");

    case "php-curl":
      return [
        `<?php`,
        `$ch = curl_init();`,
        `curl_setopt_array($ch, [`,
        `  CURLOPT_URL => '${url}',`,
        `  CURLOPT_RETURNTRANSFER => true,`,
        `  CURLOPT_HTTPHEADER => [`,
        `    'Accept: application/json',`,
        `    '${authHeader(tok)}',`,
        `  ],`,
        `]);`,
        `$resp = curl_exec($ch);`,
        `if ($resp === false) {`,
        `  throw new Exception('cURL error: ' . curl_error($ch));`,
        `}`,
        `curl_close($ch);`,
        `header('Content-Type: application/json');`,
        `echo $resp;`,
      ].join("\n");

    default:
      return "";
  }
});

// ==== copy & postman ====
async function copyCode() {
  try {
    await navigator.clipboard.writeText(code.value);
  } catch {
    // fallback
    const ta = document.createElement("textarea");
    ta.value = code.value;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand("copy");
    ta.remove();
  }
}

function downloadPostmanCollection() {
  const base = `${baseApi.value}${apiPrefix}`;

  const collection = {
    info: {
      name: `${props.brandSlug || "brand"} API (owner)`,
      schema:
        "https://schema.getpostman.com/json/collection/v2.1.0/collection.json",
      _postman_id: crypto?.randomUUID?.() || undefined,
    },
    item: endpoints.value.map((e) => ({
      name: e.label,
      request: {
        method: e.method,
        header: [
          { key: "Accept", value: "application/json", type: "text" },
          { key: "Authorization", value: "Bearer {{token}}", type: "text" },
        ],
        url: {
          raw: `{{baseUrl}}${e.path}`,
          host: ["{{baseUrl}}"],
          path: e.path.replace(/^\//, "").split("/"),
        },
        description: e.desc,
      },
    })),
    variable: [
      { key: "baseUrl", value: base },
      { key: "token", value: bearer.value || "" },
    ],
  };

  const blob = new Blob([JSON.stringify(collection, null, 2)], {
    type: "application/json;charset=utf-8",
  });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `${props.brandSlug || "brand"}-api.postman_collection.json`;
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
}
</script>

<template>
  <div class="card p-4 md:p-6 space-y-4">
    <div class="flex items-center justify-between gap-3 flex-wrap">
      <h3 class="title-md">API-toegang</h3>
      <div class="flex items-center gap-2">
        <button
          class="btn btn--sm btn--ghost"
          @click="downloadPostmanCollection"
        >
          ⬇️ Download Postman collection
        </button>
      </div>
    </div>

    <div class="grid gap-3 md:grid-cols-2">
      <div class="field">
        <div class="label">Base URL</div>
        <code class="code">{{ baseApi }}{{ apiPrefix }}</code>
      </div>
      <div class="field">
        <div class="label">Bearer token (brand owner)</div>
        <div class="flex items-center gap-2">
          <code class="code">
            {{
              showToken
                ? bearer || "—"
                : bearer
                ? "••••••••••••••••"
                : "YOUR_TOKEN_HERE"
            }}
          </code>
          <button
            class="btn btn--sm btn--ghost"
            @click="showToken = !showToken"
          >
            {{ showToken ? "Verberg" : "Toon" }}
          </button>
        </div>
      </div>
    </div>

    <div class="grid md:grid-cols-12 gap-4">
      <!-- Endpoints lijst -->
      <aside class="md:col-span-4 lg:col-span-3">
        <div class="card-compact">
          <ul class="list">
            <li
              v-for="e in endpoints"
              :key="e.key"
              class="list-item mb-2"
              :class="activeEndpointKey === e.key ? 'active' : ''"
            >
              <button
                class="w-full text-left"
                @click="activeEndpointKey = e.key"
              >
                <span class="badge">{{ e.method }}</span>
                <span class="ml-2">{{ e.label }}</span>
                <div class="muted-text text-xs truncate">{{ e.path }}</div>
              </button>
            </li>
          </ul>
        </div>
      </aside>

      <!-- Codeblok -->
      <section class="md:col-span-8 lg:col-span-9">
        <div class="flex items-center justify-between mb-2">
          <div class="flex gap-2 flex-wrap">
            <button
              v-for="l in languages"
              :key="l.key"
              class="tab"
              :class="activeLang === l.key ? 'tab--active' : ''"
              @click="activeLang = l.key"
            >
              {{ l.label }}
            </button>
          </div>
          <button class="btn btn--sm" @click="copyCode">Kopieer</button>
        </div>

        <p class="muted-text mb-2">{{ activeEndpoint.desc }}</p>

        <pre class="codeblock"><code>{{ code }}</code></pre>
      </section>
    </div>
  </div>
</template>

<style scoped lang="postcss">
.card {
  @apply rounded-2xl border border-neutral-200 bg-white shadow-sm;
}
.title-md {
  @apply text-lg font-semibold;
}
.muted-text {
  @apply text-neutral-500;
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
.badge {
  @apply inline-block rounded-md border px-1.5 py-0.5 text-[11px] font-mono;
}
.list .list-item {
  @apply rounded-xl border border-transparent;
}
.list .list-item.active {
  @apply border-neutral-300 bg-neutral-50;
}
.field .label {
  @apply text-xs uppercase tracking-wider text-neutral-500 mb-1;
}
.code {
  @apply bg-neutral-100 rounded-md px-2 py-1 text-xs;
}
.tab {
  @apply rounded-lg border px-2 py-1 text-xs;
}
.tab--active {
  @apply bg-neutral-100;
}
.codeblock {
  @apply rounded-xl border bg-neutral-50 p-3 text-xs overflow-x-auto;
}
</style>
