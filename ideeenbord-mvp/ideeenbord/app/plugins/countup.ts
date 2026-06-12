/**
 * `v-countup` — animeert een getal van 0 naar zijn eindwaarde wanneer het in
 * beeld scrollt (GSAP + ScrollTrigger). SSR-safe en respecteert
 * prefers-reduced-motion. Behoudt prefix/suffix en decimalen.
 *
 * Werkt op de bestaande tekst van het element, bv:
 *   <span v-countup>+42</span>      → telt naar 42, met "+" ervoor
 *   <span v-countup>8.7k+</span>    → telt naar 8.7, met "k+" erachter
 *   <span v-countup="{ duration: 2 }">1250</span>
 */
type CountUpOptions = { duration?: number; start?: string };

function parse(text: string) {
  const m = text.match(/-?\d[\d.,]*/);
  if (!m) return null;
  const raw = m[0];
  const idx = m.index ?? 0;
  const prefix = text.slice(0, idx);
  const suffix = text.slice(idx + raw.length);
  // NL-notatie: punt = duizendtal? Houd het simpel — strip duizend-puntjes,
  // gebruik komma als decimaal indien aanwezig.
  const normalized = raw.includes(",")
    ? raw.replace(/\./g, "").replace(",", ".")
    : raw;
  const decimals = (normalized.split(".")[1] || "").length;
  return { value: parseFloat(normalized), decimals, prefix, suffix };
}

export default defineNuxtPlugin((nuxtApp) => {
  function run(el: HTMLElement & { __cuDone?: boolean }, binding: any) {
    if (el.__cuDone) return;
    const gsap = (nuxtApp as any).$gsap;
    const reduce =
      typeof window !== "undefined" &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (!gsap || reduce) return;

    const parsed = parse(el.textContent || "");
    // Waarden komen vaak async uit de CMS — wacht tot er een écht (>0) getal staat.
    if (!parsed || parsed.value === 0) return;

    el.__cuDone = true;
    const opts: CountUpOptions = binding.value || {};
    const obj = { n: 0 };
    const fmt = (n: number) =>
      parsed.prefix +
      n.toLocaleString("nl-NL", {
        minimumFractionDigits: parsed.decimals,
        maximumFractionDigits: parsed.decimals,
      }) +
      parsed.suffix;

    el.textContent = fmt(0);
    gsap.to(obj, {
      n: parsed.value,
      duration: opts.duration ?? 1.6,
      ease: "power2.out",
      onUpdate: () => (el.textContent = fmt(obj.n)),
      scrollTrigger: { trigger: el, start: opts.start ?? "top 90%", once: true },
    });
  }

  nuxtApp.vueApp.directive("countup", {
    getSSRProps: () => ({}),
    mounted: (el, binding) => run(el as any, binding),
    updated: (el, binding) => run(el as any, binding),
  });
});
