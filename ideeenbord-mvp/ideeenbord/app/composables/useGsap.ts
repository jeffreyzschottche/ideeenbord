import type { gsap as GsapType } from "gsap";

/**
 * Convenience accessor for GSAP inside components.
 *
 * Example:
 *   const { gsap, prefersReducedMotion } = useGsap();
 *   onMounted(() => {
 *     if (prefersReducedMotion.value) return;
 *     gsap.from(".hero-title", { y: 40, opacity: 0, duration: 0.8 });
 *   });
 */
export function useGsap() {
  const { $gsap, $ScrollTrigger } = useNuxtApp();

  const prefersReducedMotion = computed(
    () =>
      typeof window !== "undefined" &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches
  );

  return {
    gsap: $gsap as typeof GsapType,
    ScrollTrigger: $ScrollTrigger,
    prefersReducedMotion,
  };
}
