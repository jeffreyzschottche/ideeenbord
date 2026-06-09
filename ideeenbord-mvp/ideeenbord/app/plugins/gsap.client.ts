import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

/**
 * GSAP plugin (client-only). Registers ScrollTrigger and exposes
 * `$gsap` / `$ScrollTrigger` on the Nuxt app. The `v-reveal` directive is
 * registered separately (plugins/reveal.ts) so it is SSR-safe.
 */
export default defineNuxtPlugin(() => {
  gsap.registerPlugin(ScrollTrigger);

  return {
    provide: {
      gsap,
      ScrollTrigger,
    },
  };
});
