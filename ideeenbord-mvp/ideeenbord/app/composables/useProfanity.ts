import { ref } from "vue";
import { apiFetch } from "~/composables/adapter/useApi";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";

const { triggerByKey } = useResponseDisplay();

// Lokale fallback wordlist (gratis, geen API nodig)
const LOCAL_BAD_WORDS = [
  // Nederlands
  "kut", "hoer", "lul", "eikel", "klootzak", "kanker", "tyfus", "tering",
  "godverdomme", "kutwijf", "slet", "teef", "mongool", "debiel", "idioot",
  "sukkel", "trut", "reet", "kak", "schijt", "pis", "neuk", "neuken",
  "flikker", "homo", "nikker", "neger", "nazi", "fascist",
  // Engels
  "fuck", "shit", "ass", "bitch", "bastard", "damn", "crap", "dick",
  "cock", "pussy", "whore", "slut", "nigger", "faggot", "retard",
  "cunt", "asshole", "motherfucker", "bullshit", "piss",
];

let cache: string[] = []; // blijft in memory

/** Eén keer laden – probeert API, valt terug op lokale lijst */
async function load() {
  if (cache.length) return cache;

  try {
    const res = await apiFetch<unknown>("/profanity"); // → backend route /api/v1/profanity

    // res kan een array of { bad_words: [...] } zijn
    const apiWords = Array.isArray(res)
      ? res
      : Array.isArray((res as any)?.bad_words)
      ? (res as any).bad_words
      : [];

    // Combineer API woorden met lokale fallback
    cache = [...new Set([...apiWords, ...LOCAL_BAD_WORDS])];
  } catch {
    // API faalt? Gebruik alleen lokale lijst
    cache = [...LOCAL_BAD_WORDS];
  }

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
