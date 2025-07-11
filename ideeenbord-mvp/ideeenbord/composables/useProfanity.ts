import { ref } from "vue";
import { apiFetch } from "~/composables/adapter/useApi";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";

const { triggerByKey } = useResponseDisplay();

let cache: string[] = []; // blijft in memory

/** Eén keer laden – ALTIJD via apiFetch */
async function load() {
  if (cache.length) return cache;

  const res = await apiFetch<unknown>("/profanity"); // → backend route /api/v1/profanity

  // res kan een array of { bad_words: [...] } zijn
  cache = Array.isArray(res)
    ? res
    : Array.isArray((res as any)?.bad_words)
    ? (res as any).bad_words
    : [];

  return cache;
}

export function useProfanity() {
  const bad = ref<string[]>([]);
  const ready = ref(false);

  async function init() {
    bad.value = await load();
    ready.value = true;
  }

  function contains(text: string) {
    if (!Array.isArray(bad.value)) return false;
    const t = text.toLowerCase();
    return bad.value.some((w) => t.includes(w));
  }

  /** Valideer velden – geeft `true` wanneer ALLES ok is */
  function validate(...fields: string[]) {
    if (!ready.value) return false; // lijst nog niet binnen
    if (fields.some(contains)) {
      triggerByKey("profanity-detected");
      return false;
    }
    return true;
  }

  return { init, validate, ready };
}
