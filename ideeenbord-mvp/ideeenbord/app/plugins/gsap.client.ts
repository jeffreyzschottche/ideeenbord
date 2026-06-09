import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

/**
 * GSAP plugin (client-only).
 *
 * - Registers ScrollTrigger.
 * - Exposes `$gsap` and `$ScrollTrigger` via the Nuxt app.
 * - Adds a `v-reveal` directive that animates elements into view on scroll.
 *   Usage: `<div v-reveal>` or `<div v-reveal="{ y: 40, delay: 0.1 }">`.
 *
 * Respects `prefers-reduced-motion`: when the user prefers reduced motion we
 * simply show elements without animating.
 */
export default defineNuxtPlugin((nuxtApp) => {
  gsap.registerPlugin(ScrollTrigger);

  const reduceMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  type RevealOptions = {
    y?: number;
    x?: number;
    opacity?: number;
    duration?: number;
    delay?: number;
    scale?: number;
    start?: string;
    stagger?: number;
  };

  nuxtApp.vueApp.directive("reveal", {
    mounted(el: HTMLElement, binding) {
      if (reduceMotion) {
        el.style.opacity = "1";
        return;
      }

      const opts: RevealOptions = binding.value || {};
      const targets =
        opts.stagger && el.children.length ? Array.from(el.children) : el;

      gsap.set(targets, {
        opacity: opts.opacity ?? 0,
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
        scrollTrigger: {
          trigger: el,
          start: opts.start ?? "top 85%",
          once: true,
        },
      });
    },
  });

  return {
    provide: {
      gsap,
      ScrollTrigger,
    },
  };
});
