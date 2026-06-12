<script setup lang="ts">
/*
  Autocomplete voor Nederlandse woonplaatsen via de PDOK Locatieserver
  (gratis, geen API-key). v-model bindt de gekozen plaatsnaam.
*/
import { ref, watch } from "vue";

const props = defineProps<{ modelValue: string; placeholder?: string }>();
const emit = defineEmits(["update:modelValue"]);

const query = ref(props.modelValue || "");
const suggestions = ref<string[]>([]);
const open = ref(false);
const loading = ref(false);
let timer: any = null;

watch(
  () => props.modelValue,
  (v) => {
    if ((v || "") !== query.value) query.value = v || "";
  }
);

function onInput() {
  emit("update:modelValue", query.value);
  if (timer) clearTimeout(timer);
  const q = query.value.trim();
  if (q.length < 2) {
    suggestions.value = [];
    open.value = false;
    return;
  }
  timer = setTimeout(fetchSuggestions, 250);
}

async function fetchSuggestions() {
  loading.value = true;
  try {
    const url =
      "https://api.pdok.nl/bzk/locatieserver/search/v3_1/suggest" +
      "?fq=type:woonplaats&rows=8&q=" +
      encodeURIComponent(query.value.trim());
    const res = await fetch(url);
    const data = await res.json();
    const docs = data?.response?.docs ?? [];
    const seen = new Set<string>();
    suggestions.value = docs
      .map((d: any) => String(d.weergavenaam || "").split(",")[0].trim())
      .filter((n: string) => {
        if (!n || seen.has(n.toLowerCase())) return false;
        seen.add(n.toLowerCase());
        return true;
      });
    open.value = suggestions.value.length > 0;
  } catch {
    suggestions.value = [];
    open.value = false;
  } finally {
    loading.value = false;
  }
}

function pick(name: string) {
  query.value = name;
  emit("update:modelValue", name);
  open.value = false;
}

function closeSoon() {
  setTimeout(() => (open.value = false), 150);
}
</script>

<template>
  <div class="relative">
    <input
      v-model="query"
      type="text"
      class="input"
      autocomplete="off"
      :placeholder="placeholder || 'Begin te typen…'"
      @input="onInput"
      @focus="open = suggestions.length > 0"
      @blur="closeSoon"
    />
    <span v-if="loading" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
      <i class="fa-solid fa-spinner fa-spin text-xs"></i>
    </span>
    <ul v-if="open" class="ca-list">
      <li v-for="s in suggestions" :key="s" class="ca-item" @mousedown.prevent="pick(s)">
        <i class="fa-solid fa-location-dot text-[var(--color-brand)] mr-2 text-xs"></i>{{ s }}
      </li>
    </ul>
  </div>
</template>

<style scoped>
.ca-list {
  position: absolute;
  z-index: 30;
  left: 0;
  right: 0;
  margin-top: 0.25rem;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  box-shadow: 0 12px 28px rgba(31, 41, 55, 0.12);
  max-height: 14rem;
  overflow-y: auto;
  padding: 0.25rem;
}
.ca-item {
  padding: 0.5rem 0.65rem;
  border-radius: 0.5rem;
  font-size: 0.92rem;
  color: #374151;
  cursor: pointer;
}
.ca-item:hover {
  background: var(--color-bg);
}
</style>
