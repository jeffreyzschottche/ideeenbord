/**
 * Universal `v-reveal` directive.
 *
 * Registered on both server and client (with a no-op getSSRProps so SSR doesn't
 * choke), but the animation only runs in the browser's `mounted` hook.
 *
 * Usage: `<div v-reveal>` or `<div v-reveal="{ y: 40, stagger: 0.1 }">`.
 * Respects `prefers-reduced-motion`.
 */
type RevealOptions = {
  y?: number;
  x?: number;
  duration?: number;
  delay?: number;
  scale?: number;
  start?: string;
  stagger?: number;
};

export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.vueApp.directive("reveal", {
    // Keeps Vue's SSR renderer happy for a client-only directive.
    getSSRProps: () => ({}),

    mounted(el: HTMLElement, binding) {
      const gsap = (nuxtApp as any).$gsap;
      const reduceMotion =
        typeof window !== "undefined" &&
        window.matchMedia("(prefers-reduced-motion: reduce)").matches;

      if (!gsap || reduceMotion) {
        el.style.opacity = "1";
        return;
      }

      const opts: RevealOptions = binding.value || {};
      const targets =
        opts.stagger && el.children.length ? Array.from(el.children) : el;

      gsap.set(targets, {
        opacity: 0,
        y: opts.y ?? 32,
        x: opts.x ?? 0,
        scale: opts.scale ?? 1,
      });

      gsap.to(targets, {
        opacity: 1,
        y: 0,
        x: 0,
        scale: 1,
        duration: opts.duration ?? 0.7,
        delay: opts.delay ?? 0,
        ease: "power3.out",
        stagger: opts.stagger ?? 0,
        scrollTrigger: { trigger: el, start: opts.start ?? "top 85%", once: true },
      });
    },
  });
});
