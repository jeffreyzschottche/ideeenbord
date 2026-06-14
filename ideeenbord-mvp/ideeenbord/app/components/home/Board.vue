<template>
  <section class="hero-gradient relative overflow-hidden select-none">
    <div class="hero-glow" aria-hidden="true"></div>
    <div class="hero-glow-2" aria-hidden="true"></div>

    <div class="container mx-auto px-4 pt-28 pb-16 md:pt-32 md:pb-24 relative">
      <div class="grid lg:grid-cols-2 gap-10 lg:gap-14 items-center">
        <!-- Copy -->
        <div class="text-center lg:text-left">
          <p ref="eyebrow" class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-gray-200 text-xs font-semibold uppercase tracking-[0.15em] mb-5">
            <span class="w-2 h-2 rounded-full bg-[var(--color-brand)] animate-pulse"></span>
            {{ content["home-tagline"] || "Consumenten en merken verbonden" }}
          </p>
          <h1 ref="heading" class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-[1.08] text-white">
            Jouw stem.<br />
            <span class="bg-gradient-to-r from-orange-400 to-amber-300 bg-clip-text text-transparent">Hun volgende stap.</span>
          </h1>
          <p ref="subline" class="mt-6 text-lg md:text-xl text-gray-300 leading-relaxed max-w-xl mx-auto lg:mx-0">
            Vertel merken wat jij wilt zien. Duurzamere verpakkingen? Langere openingstijden?
            Een vegan optie? <strong class="text-white">Jouw idee kan realiteit worden.</strong>
          </p>
          <div ref="cta" class="mt-8 flex flex-wrap items-center justify-center lg:justify-start gap-3">
            <NuxtLink to="/register" class="cta px-7 py-3.5 text-base">Gratis aanmelden</NuxtLink>
            <NuxtLink to="/about" class="px-7 py-3.5 rounded-xl border-2 border-white/25 text-white hover:bg-white/10 font-bold transition">
              Hoe werkt het?
            </NuxtLink>
          </div>
          <!-- Trust strip with real impact -->
          <div ref="trustStrip" class="mt-9 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 sm:gap-6">
            <div class="flex items-center gap-6 text-sm text-gray-400">
              <span><strong class="text-white text-base">42+</strong> merken</span>
              <span class="w-px h-5 bg-white/15"></span>
              <span><strong class="text-white text-base">3.1k+</strong> gebruikers</span>
              <span class="w-px h-5 bg-white/15"></span>
              <span><strong class="text-white text-base">8.7k+</strong> ideeën</span>
            </div>
          </div>
          <!-- Social proof -->
          <div class="mt-4 text-sm text-gray-400 flex items-center gap-2 justify-center lg:justify-start">
            <i class="fa-solid fa-check-circle text-green-400"></i>
            <span>127 ideeën al gerealiseerd door merken</span>
          </div>
        </div>

        <!-- Modern floating cards visual -->
        <div ref="boardWrap" class="relative w-full max-w-[540px] mx-auto h-[400px] md:h-[480px]">
          <!-- Glow achter de cards -->
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-72 h-72 rounded-full bg-gradient-to-br from-orange-500/30 to-amber-400/20 blur-[80px]"></div>
          </div>

          <!-- Floating idea cards -->
          <div
            v-for="(card, i) in cards"
            :key="i"
            :ref="el => cardRefs[i] = el"
            class="idea-card absolute"
            :class="card.size"
            :style="{ left: card.x, top: card.y, zIndex: card.z }"
          >
            <div class="card-inner">
              <!-- Header met avatar en merk -->
              <div class="card-header">
                <div class="card-avatar" :style="{ background: card.avatarGradient }">
                  {{ card.initials }}
                </div>
                <div class="card-meta">
                  <span class="card-author">{{ card.author }}</span>
                  <span class="card-brand">{{ card.brand }}</span>
                </div>
              </div>

              <!-- Idee tekst -->
              <p class="card-text">{{ card.text }}</p>

              <!-- Footer met votes -->
              <div class="card-footer">
                <div class="card-votes">
                  <i class="fa-solid fa-arrow-up"></i>
                  <span>{{ card.votes.toLocaleString() }}</span>
                </div>
                <div class="card-status" :class="card.statusClass">
                  {{ card.status }}
                </div>
              </div>
            </div>
          </div>

          <!-- Centrale lightbulb accent -->
          <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none">
            <div ref="centralIcon" class="central-icon">
              <i class="fa-solid fa-lightbulb"></i>
            </div>
          </div>

          <!-- Decorative elements -->
          <div class="sparkle sparkle-1" aria-hidden="true"></div>
          <div class="sparkle sparkle-2" aria-hidden="true"></div>
          <div class="sparkle sparkle-3" aria-hidden="true"></div>
        </div>
      </div>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 hidden md:flex flex-col items-center gap-2 text-white/40">
      <span class="text-xs uppercase tracking-widest">Scroll</span>
      <i class="fa-solid fa-chevron-down text-sm animate-bounce"></i>
    </div>
  </section>
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
const trustStrip = ref<HTMLElement | null>(null);
const boardWrap = ref<HTMLElement | null>(null);
const cardRefs = ref<(HTMLElement | null)[]>([]);
const centralIcon = ref<HTMLElement | null>(null);

onMounted(() => {
  const { gsap, prefersReducedMotion } = useGsap();
  if (prefersReducedMotion.value || !gsap) return;

  const tl = gsap.timeline({ defaults: { ease: "power3.out" } });
  tl.from([eyebrow.value, heading.value, subline.value, cta.value, trustStrip.value], {
    y: 30,
    opacity: 0,
    duration: 0.7,
    stagger: 0.12,
  });

  // Cards staggered entrance
  const cardElements = cardRefs.value.filter(Boolean);
  if (cardElements.length) {
    gsap.from(cardElements, {
      y: 60,
      opacity: 0,
      scale: 0.9,
      duration: 0.8,
      stagger: 0.12,
      ease: "power3.out",
      delay: 0.4,
    });

    // Floating animation per card
    cardElements.forEach((card, i) => {
      const yOffset = 8 + Math.random() * 8;
      const xOffset = 3 + Math.random() * 4;
      const duration = 4 + Math.random() * 2;
      const delay = Math.random() * 2;

      gsap.to(card, {
        y: `+=${yOffset}`,
        x: `+=${i % 2 === 0 ? xOffset : -xOffset}`,
        duration: duration,
        ease: "sine.inOut",
        repeat: -1,
        yoyo: true,
        delay: 1.5 + delay,
      });
    });
  }

  // Central icon pulse
  if (centralIcon.value) {
    gsap.to(centralIcon.value, {
      scale: 1.1,
      duration: 2.5,
      ease: "sine.inOut",
      repeat: -1,
      yoyo: true,
    });
  }

  // Sparkles animation
  const sparkles = boardWrap.value?.querySelectorAll(".sparkle");
  if (sparkles?.length) {
    sparkles.forEach((sparkle, i) => {
      gsap.to(sparkle, {
        opacity: 0.3 + Math.random() * 0.4,
        scale: 0.8 + Math.random() * 0.4,
        duration: 1.5 + Math.random(),
        ease: "sine.inOut",
        repeat: -1,
        yoyo: true,
        delay: i * 0.3,
      });
    });
  }
});

type CardDef = {
  x: string;
  y: string;
  z: number;
  size: string;
  text: string;
  author: string;
  initials: string;
  avatarGradient: string;
  brand: string;
  votes: number;
  status: string;
  statusClass: string;
};

const cards: CardDef[] = [
  {
    x: "0%",
    y: "8%",
    z: 3,
    size: "card-lg",
    text: "Duurzame verpakkingen voor alle producten - minder plastic, meer recyclebaar materiaal",
    author: "Emma",
    initials: "E",
    avatarGradient: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
    brand: "Albert Heijn",
    votes: 2847,
    status: "Gerealiseerd",
    statusClass: "status-done",
  },
  {
    x: "55%",
    y: "0%",
    z: 2,
    size: "card-md",
    text: "Loyaliteitspunten ook inzetten voor bezorgkosten",
    author: "Thijs",
    initials: "T",
    avatarGradient: "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)",
    brand: "Bol",
    votes: 1523,
    status: "In ontwikkeling",
    statusClass: "status-progress",
  },
  {
    x: "5%",
    y: "55%",
    z: 2,
    size: "card-md",
    text: "Meer vegan opties in kant-en-klaar maaltijden",
    author: "Lisa",
    initials: "L",
    avatarGradient: "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)",
    brand: "Jumbo",
    votes: 891,
    status: "Bekeken",
    statusClass: "status-review",
  },
  {
    x: "52%",
    y: "48%",
    z: 4,
    size: "card-lg",
    text: "Bezorging op zaterdag en zondag als optie toevoegen",
    author: "Mark",
    initials: "M",
    avatarGradient: "linear-gradient(135deg, #fa709a 0%, #fee140 100%)",
    brand: "Coolblue",
    votes: 3201,
    status: "Gerealiseerd",
    statusClass: "status-done",
  },
  {
    x: "30%",
    y: "30%",
    z: 1,
    size: "card-sm",
    text: "Studentenkorting!",
    author: "Noah",
    initials: "N",
    avatarGradient: "linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)",
    brand: "HEMA",
    votes: 456,
    status: "Nieuw",
    statusClass: "status-new",
  },
];
</script>

<style scoped>
/* Hero achtergrond */
.hero-gradient {
  background:
    radial-gradient(1100px 520px at 75% -10%, #2b3a52 0%, transparent 60%),
    linear-gradient(180deg, #1f2937 0%, #161e29 100%);
  min-height: 100vh;
  display: flex;
  align-items: center;
}
.hero-glow {
  position: absolute;
  top: -120px;
  right: -80px;
  width: 520px;
  height: 520px;
  border-radius: 9999px;
  background: radial-gradient(circle, rgba(247, 138, 29, 0.35), transparent 65%);
  filter: blur(40px);
  pointer-events: none;
}
.hero-glow-2 {
  position: absolute;
  bottom: -160px;
  left: -120px;
  width: 460px;
  height: 460px;
  border-radius: 9999px;
  background: radial-gradient(circle, rgba(255, 187, 0, 0.18), transparent 65%);
  filter: blur(50px);
  pointer-events: none;
}

/* ============================================
   IDEA CARDS - Modern glassmorphism design
   ============================================ */
.idea-card {
  position: absolute;
  will-change: transform;
}

.card-inner {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-radius: 16px;
  padding: 16px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow:
    0 4px 24px rgba(0, 0, 0, 0.12),
    0 1px 2px rgba(0, 0, 0, 0.08),
    inset 0 1px 0 rgba(255, 255, 255, 0.8);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.idea-card:hover .card-inner {
  transform: translateY(-4px);
  box-shadow:
    0 12px 40px rgba(0, 0, 0, 0.15),
    0 2px 4px rgba(0, 0, 0, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

/* Card sizes */
.card-sm .card-inner {
  width: 140px;
  padding: 12px;
}
.card-md .card-inner {
  width: 180px;
}
.card-lg .card-inner {
  width: 220px;
}

/* Card header */
.card-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
}

.card-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 13px;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.card-sm .card-avatar {
  width: 26px;
  height: 26px;
  font-size: 11px;
}

.card-meta {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.card-author {
  font-size: 12px;
  font-weight: 600;
  color: #1f2937;
  line-height: 1.2;
}

.card-brand {
  font-size: 10px;
  color: #6b7280;
  font-weight: 500;
}

/* Card text */
.card-text {
  font-size: 13px;
  line-height: 1.5;
  color: #374151;
  margin-bottom: 12px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-sm .card-text {
  font-size: 12px;
  -webkit-line-clamp: 2;
  margin-bottom: 10px;
}

/* Card footer */
.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.card-votes {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 600;
  color: #f97316;
}

.card-votes i {
  font-size: 11px;
}

/* Status badges */
.card-status {
  font-size: 9px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 4px 8px;
  border-radius: 6px;
}

.status-new {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  color: #1d4ed8;
}

.status-review {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  color: #b45309;
}

.status-progress {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #047857;
}

.status-done {
  background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
  color: #7c3aed;
}

/* ============================================
   CENTRAL ICON
   ============================================ */
.central-icon {
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: radial-gradient(circle, rgba(251, 146, 60, 0.15) 0%, transparent 70%);
  border-radius: 50%;
}

.central-icon i {
  font-size: 40px;
  background: linear-gradient(135deg, #f97316 0%, #fbbf24 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  filter: drop-shadow(0 4px 12px rgba(249, 115, 22, 0.4));
}

/* ============================================
   SPARKLES - Decorative floating dots
   ============================================ */
.sparkle {
  position: absolute;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%);
  opacity: 0.5;
}

.sparkle-1 {
  top: 15%;
  right: 12%;
}

.sparkle-2 {
  bottom: 25%;
  left: 8%;
}

.sparkle-3 {
  top: 45%;
  right: 5%;
  width: 4px;
  height: 4px;
}

/* Responsive */
@media (max-width: 1024px) {
  .hero-gradient {
    min-height: auto;
  }
}

@media (max-width: 768px) {
  .card-lg .card-inner {
    width: 180px;
  }
  .card-md .card-inner {
    width: 150px;
  }
  .card-sm .card-inner {
    width: 120px;
  }
}
</style>
