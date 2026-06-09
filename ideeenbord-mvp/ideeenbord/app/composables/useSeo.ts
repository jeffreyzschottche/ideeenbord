import { useHead, useSeoMeta, useRuntimeConfig, useRoute } from "#app";

type PageSeo = {
  title?: string;
  description?: string;
  image?: string;
  type?: "website" | "article";
  noindex?: boolean;
};

/**
 * Inject one or more JSON-LD structured-data blocks into the document head.
 */
export function useJsonLd(data: Record<string, any> | Record<string, any>[]) {
  const blocks = Array.isArray(data) ? data : [data];
  useHead({
    script: blocks.map((block) => ({
      type: "application/ld+json",
      innerHTML: JSON.stringify({ "@context": "https://schema.org", ...block }),
    })),
  });
}

/** Absolute canonical URL for the current route. */
export function useCanonical(path?: string): string {
  const site = useRuntimeConfig().public.siteUrl || "https://ideeenbord.nl";
  const route = useRoute();
  return site.replace(/\/$/, "") + (path ?? route.path);
}

/**
 * Standard per-page SEO: title, description, Open Graph, Twitter, canonical,
 * and a robots directive. Pair with useJsonLd for structured data.
 */
export function usePageSeo(opts: PageSeo) {
  const site = useRuntimeConfig().public.siteUrl || "https://ideeenbord.nl";
  const canonical = useCanonical();
  const image = opts.image
    ? opts.image.startsWith("http")
      ? opts.image
      : site.replace(/\/$/, "") + opts.image
    : `${site.replace(/\/$/, "")}/og-image.png`;

  useSeoMeta({
    title: opts.title,
    description: opts.description,
    ogTitle: opts.title,
    ogDescription: opts.description,
    ogType: opts.type ?? "website",
    ogUrl: canonical,
    ogImage: image,
    ogSiteName: "Ideeënbord",
    ogLocale: "nl_NL",
    twitterCard: "summary_large_image",
    twitterTitle: opts.title,
    twitterDescription: opts.description,
    twitterImage: image,
    robots: opts.noindex ? "noindex, nofollow" : "index, follow",
  });

  useHead({
    link: [{ rel: "canonical", href: canonical }],
  });
}

/** BreadcrumbList JSON-LD helper. */
export function breadcrumbLd(items: { name: string; path: string }[]) {
  const site = (useRuntimeConfig().public.siteUrl || "https://ideeenbord.nl").replace(/\/$/, "");
  return {
    "@type": "BreadcrumbList",
    itemListElement: items.map((it, i) => ({
      "@type": "ListItem",
      position: i + 1,
      name: it.name,
      item: site + it.path,
    })),
  };
}
