import { defineEventHandler, setHeader } from "h3";
import { useRuntimeConfig } from "#imports";

// Dynamic sitemap: static public routes + brand detail pages from the API.
export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig();
  const site = (config.public.siteUrl || "https://ideeenbord.nl").replace(/\/$/, "");
  const api = config.public.apiBaseUrl;

  const staticPaths = [
    "/",
    "/about",
    "/brands",
    "/news",
    "/participants",
    "/subscriptions",
    "/win",
    "/become-a-brandowner",
    "/privacy-statement",
  ];

  const urls = new Set<string>(staticPaths.map((p) => site + p));

  // Best-effort: pull brand slugs so brand pages are indexable.
  try {
    if (api) {
      const brands = await $fetch<any[]>(`${api}/v1/brands`, { timeout: 5000 });
      for (const b of brands ?? []) {
        if (b?.slug) urls.add(`${site}/brands/${b.slug}`);
      }
    }
  } catch {
    // ignore — fall back to static routes only
  }

  const body =
    `<?xml version="1.0" encoding="UTF-8"?>\n` +
    `<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n` +
    [...urls]
      .map((u) => `  <url><loc>${u}</loc></url>`)
      .join("\n") +
    `\n</urlset>`;

  setHeader(event, "Content-Type", "application/xml; charset=utf-8");
  setHeader(event, "Cache-Control", "public, max-age=3600");
  return body;
});
