// ~/composables/useSearch.ts
import { ref, computed } from "vue";
import { apiFetch } from "~/composables/adapter/useApi";
import { useCookie } from "#app";
import type { Brand } from "~/types/brand";

type Page = { title: string; route: string };

const staticPages: Page[] = [
  { title: "Nieuws", route: "/news" },
  { title: "Uitleg", route: "/about" },
  { title: "Contact", route: "/contact" },
  { title: "Vacatures", route: "/jobs" },
  { title: "Veelgestelde vragen", route: "/faq" },
];

/* ---------------- Similarity helpers ---------------- */
function levenshtein(a: string, b: string): number {
  const m = a.length,
    n = b.length;
  if (!m) return n;
  if (!n) return m;
  const v0 = new Array(n + 1).fill(0).map((_, i) => i);
  let v1 = new Array(n + 1).fill(0);

  for (let i = 0; i < m; i++) {
    v1[0] = i + 1;
    for (let j = 0; j < n; j++) {
      const cost = a[i] === b[j] ? 0 : 1;
      v1[j + 1] = Math.min(v1[j] + 1, v0[j + 1] + 1, v0[j] + cost);
    }
    v0.splice(0, n + 1, ...v1);
  }
  return v0[n];
}

function similarity(query: string, target: string): number {
  const q = query.toLowerCase();
  const t = target.toLowerCase();
  if (t.includes(q)) return 1; // exact substring
  const dist = levenshtein(q, t);
  return 1 - dist / Math.max(q.length, t.length);
}

/* --------------- Composable ---------------- */
export function useSearch() {
  const brands = ref<Brand[]>([]);
  const pages = ref<Page[]>([]);
  const loading = ref(false);
  const directRoute = ref<string | null>(null);

  const LIST_THRESHOLD = 0.6;
  const DIRECT_THRESHOLD = 0.8;

  const search = async (query: string) => {
    directRoute.value = null;
    if (!query) {
      brands.value = [];
      pages.value = [];
      return;
    }
    loading.value = true;

    try {
      /* Cache brands in cookie (12 u) */
      const cache = useCookie<Brand[] | null>("cached_brands", {
        default: () => null,
        maxAge: 60 * 60 * 12,
      });

      let allBrands: Brand[] = cache.value ?? [];
      if (allBrands.length === 0) {
        allBrands = await apiFetch<Brand[]>("/brands");
        cache.value = allBrands;
      }

      /* ---- filter met similarity ---- */
      brands.value = allBrands.filter(
        (b) => similarity(query, b.title) >= LIST_THRESHOLD
      );

      pages.value = staticPages.filter(
        (p) => similarity(query, p.title) >= LIST_THRESHOLD
      );

      /* ---- direct match voor Enter ---- */
      const bestPage = pages.value.find(
        (p) => similarity(query, p.title) >= DIRECT_THRESHOLD
      );
      if (bestPage) {
        directRoute.value = bestPage.route;
      } else {
        const bestBrand = brands.value.find(
          (b) => similarity(query, b.title) >= DIRECT_THRESHOLD
        );
        if (bestBrand) directRoute.value = `/brands/${bestBrand.slug}`;
      }
    } catch (err) {
      console.error("Search error:", err);
    } finally {
      loading.value = false;
    }
  };

  return {
    search,
    brands,
    pages,
    loading,
    directRoute,
    canSubmit: computed(() => !!directRoute.value),
  };
}
