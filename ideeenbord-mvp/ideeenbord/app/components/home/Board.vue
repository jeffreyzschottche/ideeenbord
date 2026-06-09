<template>
  <div class="w-full flex flex-col items-center py-12 select-none mt-10">
    <!-- Hero copy -->
    <div class="text-center max-w-3xl px-4 mb-12">
      <p ref="eyebrow" class="uppercase tracking-[0.2em] text-sm font-semibold text-[var(--color-brand)] mb-4">
        {{ content["home-tagline"] || "Jouw stem telt bij merken" }}
      </p>
      <h1 ref="heading" class="text-4xl md:text-6xl font-extrabold leading-tight text-[var(--color-nav)]">
        Jouw idee.<br class="hidden md:block" />
        <span class="text-[var(--color-brand)]">Direct bij het merk.</span>
      </h1>
      <p ref="subline" class="mt-6 text-lg md:text-xl text-gray-600 leading-relaxed">
        Op Ideeënbord deel je ideeën, wensen en verbeterpunten met merken — en zij
        luisteren écht. Plaats een idee, stem op dat van anderen en bepaal samen
        wat merken morgen maken.
      </p>
      <div ref="cta" class="mt-8 flex flex-wrap items-center justify-center gap-3">
        <NuxtLink to="/brands" class="cta px-6 py-3 text-base">Ontdek merken</NuxtLink>
        <NuxtLink to="/about" class="btn-outline px-6 py-3 text-base">Zo werkt het</NuxtLink>
      </div>
    </div>

    <!-- Bord + frame -->
    <div ref="boardWrap" class="relative w-full max-w-[880px]">
      <!-- Rode pin -->
      <div class="pin" aria-hidden="true"></div>

      <!-- Schaduw van het hele bord -->
      <div
        class="absolute inset-0 -z-10 blur-[18px] opacity-50 shadow-bg"
      ></div>

      <!-- Frame -->
      <div class="frame rounded-[28px] p-3 md:p-4">
        <!-- Binnenrand (lip) -->
        <div class="inner-lip rounded-[22px] p-2 md:p-3">
          <!-- Kurkbord -->
          <div
            class="cork rounded-2xl relative h-[460px] md:h-[520px] overflow-hidden"
          >
            <!-- Licht vignette voor diepte -->
            <div class="absolute inset-0 pointer-events-none vignette"></div>

            <!-- Grote lamp in het midden -->
            <div class="absolute inset-0 flex items-center justify-center">
              <div class="bulb-wrap">
                <svg
                  class="bulb-svg"
                  viewBox="0 0 220 220"
                  xmlns="http://www.w3.org/2000/svg"
                  aria-hidden="true"
                >
                  <!-- Glow -->
                  <defs>
                    <radialGradient id="g1" cx="50%" cy="45%" r="55%">
                      <stop offset="0%" stop-color="#ffe08a" stop-opacity="1" />
                      <stop
                        offset="60%"
                        stop-color="#ffbf47"
                        stop-opacity="0.6"
                      />
                      <stop
                        offset="100%"
                        stop-color="#ffbf47"
                        stop-opacity="0"
                      />
                    </radialGradient>
                    <linearGradient id="glass" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0" stop-color="#fff4c5" />
                      <stop offset="1" stop-color="#ffd36a" />
                    </linearGradient>
                    <linearGradient id="metal" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0" stop-color="#444" />
                      <stop offset="1" stop-color="#1f1f1f" />
                    </linearGradient>
                  </defs>

                  <!-- ambient glow -->
                  <circle cx="110" cy="102" r="96" fill="url(#g1)" />

                  <!-- rays -->
                  <g
                    stroke="#ffb020"
                    stroke-width="8"
                    stroke-linecap="round"
                    opacity="0.9"
                  >
                    <line x1="110" y1="14" x2="110" y2="0" />
                    <line x1="110" y1="206" x2="110" y2="220" />
                    <line x1="24" y1="100" x2="6" y2="100" />
                    <line x1="204" y1="100" x2="220" y2="100" />
                    <line x1="38" y1="42" x2="26" y2="28" />
                    <line x1="182" y1="42" x2="194" y2="28" />
                    <line x1="38" y1="158" x2="26" y2="172" />
                    <line x1="182" y1="158" x2="194" y2="172" />
                  </g>

                  <!-- bulb glass -->
                  <ellipse
                    cx="110"
                    cy="98"
                    rx="58"
                    ry="66"
                    fill="url(#glass)"
                    stroke="#cc8a00"
                    stroke-width="6"
                  />
                  <!-- highlight -->
                  <path
                    d="M90 54c-16 8-24 26-22 44"
                    fill="none"
                    stroke="#fff"
                    stroke-opacity="0.7"
                    stroke-width="6"
                    stroke-linecap="round"
                  />
                  <!-- filament -->
                  <path
                    d="M86 114c8-18 40-18 48 0"
                    fill="none"
                    stroke="#7a4b00"
                    stroke-width="6"
                    stroke-linecap="round"
                  />
                  <!-- socket -->
                  <g transform="translate(80,148)">
                    <rect
                      x="0"
                      y="0"
                      width="60"
                      height="24"
                      rx="6"
                      fill="url(#metal)"
                    />
                    <rect
                      x="4"
                      y="8"
                      width="52"
                      height="6"
                      rx="3"
                      fill="#2a2a2a"
                    />
                    <rect
                      x="4"
                      y="14"
                      width="52"
                      height="6"
                      rx="3"
                      fill="#2f2f2f"
                    />
                  </g>
                  <!-- base -->
                  <rect
                    x="92"
                    y="172"
                    width="36"
                    height="18"
                    rx="8"
                    fill="#111"
                  />
                </svg>
                <div class="bulb-pulse"></div>
              </div>
            </div>

            <!-- Sticky notes (zonder JSX, gewoon v-for) -->
            <div
              v-for="(n, i) in notes"
              :key="i"
              class="note absolute bg-white rounded-md shadow-md flex items-center justify-center"
              :class="n.small ? 'w-20 h-20' : 'w-24 h-24'"
              :style="{ left: n.x, top: n.y, transform: `rotate(${n.r}deg)` }"
            >
              <i class="fa-regular fa-lightbulb text-2xl text-orange-400"></i>
              <span class="note-pin" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useCmsContent } from "~/composables/content/useCmsContent";
import { useGsap } from "~/composables/useGsap";

const { content } = useCmsContent("home");

const eyebrow = ref<HTMLElement | null>(null);
const heading = ref<HTMLElement | null>(null);
const subline = ref<HTMLElement | null>(null);
const cta = ref<HTMLElement | null>(null);
const boardWrap = ref<HTMLElement | null>(null);

onMounted(() => {
  const { gsap, prefersReducedMotion } = useGsap();
  if (prefersReducedMotion.value || !gsap) return;

  const tl = gsap.timeline({ defaults: { ease: "power3.out" } });
  tl.from([eyebrow.value, heading.value, subline.value, cta.value], {
    y: 30,
    opacity: 0,
    duration: 0.7,
    stagger: 0.12,
  }).from(
    boardWrap.value,
    { y: 40, opacity: 0, scale: 0.96, duration: 0.8 },
    "-=0.3"
  );

  if (boardWrap.value) {
    gsap.from(boardWrap.value.querySelectorAll(".note"), {
      scale: 0,
      opacity: 0,
      duration: 0.5,
      stagger: 0.08,
      ease: "back.out(1.7)",
      delay: 0.6,
    });
  }
});

type NoteDef = { x: string; y: string; r?: number | string; small?: boolean };

const notes: NoteDef[] = [
  { x: "6%", y: "6%", r: -6 },
  { x: "74%", y: "8%", r: 5 },
  { x: "10%", y: "68%", r: 4 },
  { x: "72%", y: "70%", r: -4 },
  { x: "38%", y: "12%", r: 2, small: true },
  { x: "40%", y: "72%", r: -2, small: true },
];
</script>

<style scoped>
/* subtiele glow voor het logo-icoon */
.lamp-glow {
  text-shadow: 0 0 10px #f97316, 0 0 22px #fb923c, 0 0 40px #fdba74;
}

/* zachte drop op de muur */
.shadow-bg {
  box-shadow: 0 18px 50px rgba(0, 0, 0, 0.25);
}

/* FRAME: warme oranje/rode rand met diepte */
.frame {
  background: linear-gradient(180deg, #ff9e4a, #ff6a3d 60%, #d84a2a);
  box-shadow: inset 0 8px 14px rgba(255, 255, 255, 0.35),
    inset 0 -10px 16px rgba(0, 0, 0, 0.25), 0 10px 24px rgba(0, 0, 0, 0.25);
}

/* Binnenlip voor extra relief */
.inner-lip {
  background: linear-gradient(180deg, #ffd2a1, #ffb36b 55%, #f48a45);
  box-shadow: inset 0 10px 16px rgba(255, 255, 255, 0.45),
    inset 0 -10px 18px rgba(0, 0, 0, 0.2);
}

/* KURK: basis + stippenpatroon voor textuur (pure CSS) */
.cork {
  background: radial-gradient(
      120px 90px at 30% 25%,
      rgba(255, 255, 255, 0.08),
      rgba(0, 0, 0, 0)
    ),
    radial-gradient(
      100px 80px at 70% 75%,
      rgba(0, 0, 0, 0.07),
      rgba(0, 0, 0, 0)
    ),
    repeating-radial-gradient(
      circle at 20% 30%,
      rgba(0, 0, 0, 0.12) 0 1px,
      rgba(0, 0, 0, 0) 1px 6px
    ),
    repeating-radial-gradient(
      circle at 80% 60%,
      rgba(255, 255, 255, 0.12) 0 1px,
      rgba(0, 0, 0, 0) 1px 6px
    ),
    linear-gradient(180deg, #e3b179, #c78f5a 45%, #b97e49);
}

/* vignette */
.vignette {
  background: radial-gradient(
    80% 70% at 50% 45%,
    rgba(0, 0, 0, 0) 60%,
    rgba(0, 0, 0, 0.18) 100%
  );
  mix-blend-mode: multiply;
}

/* Rode pin + naald */
.pin {
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: radial-gradient(circle at 30% 30%, #ffb0b0, #ff3b3b 60%, #b41414);
  box-shadow: 0 10px 14px rgba(0, 0, 0, 0.35),
    inset 2px 3px 8px rgba(255, 255, 255, 0.65),
    inset -4px -6px 10px rgba(0, 0, 0, 0.35);
  z-index: 20;
}
.pin::before {
  /* highlight */
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background: radial-gradient(
    circle at 35% 35%,
    rgba(255, 255, 255, 0.75),
    rgba(255, 255, 255, 0) 40%
  );
  mix-blend-mode: screen;
}
.pin::after {
  /* naald in de muur */
  content: "";
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  top: 30px;
  width: 2px;
  height: 28px;
  background: linear-gradient(180deg, #999, #555);
  box-shadow: 0 16px 6px -4px rgba(0, 0, 0, 0.35);
  border-radius: 2px;
}

/* Note pin */
.note {
  position: absolute;
}
.note::before {
  /* schaduw */
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 8px;
  filter: drop-shadow(0 10px 10px rgba(0, 0, 0, 0.18));
}
.note-pin {
  position: absolute;
  top: -8px;
  left: 50%;
  transform: translateX(-50%);
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: radial-gradient(circle at 30% 30%, #ffd0d0, #ff6b6b 60%, #c03333);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.25);
}

/* BULB container + pulse */
.bulb-wrap {
  position: relative;
  width: clamp(140px, 26vw, 220px);
  filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.25));
}
.bulb-svg {
  display: block;
  width: 100%;
  height: auto;
}
.bulb-pulse {
  position: absolute;
  inset: 0;
  border-radius: 9999px;
  background: radial-gradient(
    circle at 50% 45%,
    rgba(255, 200, 80, 0.45),
    rgba(255, 200, 80, 0) 60%
  );
  animation: pulse 3s ease-in-out infinite;
  pointer-events: none;
}
@keyframes pulse {
  0%,
  100% {
    opacity: 0.5;
    transform: scale(1);
  }
  50% {
    opacity: 0.85;
    transform: scale(1.04);
  }
}
</style>
