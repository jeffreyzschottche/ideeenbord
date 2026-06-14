<template>
  <div class="about-page">
    <!-- ───── HERO SECTION ───── -->
    <section class="hero-section relative overflow-hidden">
      <div class="hero-glow" aria-hidden="true"></div>
      <div class="hero-glow-2" aria-hidden="true"></div>

      <div class="container mx-auto px-4 pt-28 pb-20 md:pt-32 md:pb-28 relative">
        <div class="text-center max-w-3xl mx-auto">
          <p ref="eyebrow" class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-gray-200 text-xs font-semibold uppercase tracking-[0.15em] mb-5">
            <span class="w-2 h-2 rounded-full bg-[var(--color-brand)] animate-pulse"></span>
            Over Ideeënbord
          </p>
          <h1 ref="heading" class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight text-white">
            {{ content['hero-title'] || 'Over Ideeënbord' }}
          </h1>
          <p ref="subline" class="mt-6 text-lg md:text-xl text-gray-300 leading-relaxed">
            {{ content['hero-subtitle'] || 'Wij verbinden merken met de slimste ideeën van fans en klanten. Zo wordt goede feedback zichtbaar, meetbaar en echt gebouwd.' }}
          </p>
        </div>
      </div>
    </section>

    <!-- ───── MAIN CONTENT ───── -->
    <section class="relative -mt-10 pb-20">
      <div class="container mx-auto px-4">
        <div ref="cardsWrap" class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">

          <!-- ─── BRAND CARD ─── -->
          <div class="feature-card">
            <div class="card-icon card-icon-brand">
              <i class="fa-solid fa-building"></i>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 mb-3">
              {{ brandData.title || 'Voor merken' }}
            </h2>
            <p class="text-gray-600 mb-8">
              {{ brandData.description || 'Bouw wat ertoe doet. Verzamel ideeën, prioriteer met je community en lever transparant.' }}
            </p>

            <!-- Steps -->
            <div class="space-y-6 mb-8">
              <div v-for="step in brandSteps" :key="step.id" class="step-item">
                <div class="step-number">{{ step.id }}</div>
                <div>
                  <h3 class="font-semibold text-gray-900">{{ step.title }}</h3>
                  <p class="text-sm text-gray-500 mt-1">{{ step.description }}</p>
                </div>
              </div>
            </div>

            <NuxtLink
              :to="brandData.cta?.to || '/become'"
              class="cta-button cta-primary"
            >
              <i class="fa-solid fa-rocket mr-2"></i>
              {{ brandData.cta?.label || 'Word deelnemend merk' }}
            </NuxtLink>
          </div>

          <!-- ─── FAN CARD ─── -->
          <div class="feature-card">
            <div class="card-icon card-icon-fan">
              <i class="fa-solid fa-users"></i>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 mb-3">
              {{ fanData.title || 'Voor fans' }}
            </h2>
            <p class="text-gray-600 mb-8">
              {{ fanData.description || 'Laat je idee landen. Dien je idee in, stem mee en volg de voortgang.' }}
            </p>

            <!-- Features list -->
            <div class="space-y-4 mb-8">
              <div class="feature-item">
                <i class="fa-solid fa-lightbulb text-amber-500"></i>
                <span>Deel je beste ideeën met merken</span>
              </div>
              <div class="feature-item">
                <i class="fa-solid fa-arrow-up text-green-500"></i>
                <span>Stem op ideeën van anderen</span>
              </div>
              <div class="feature-item">
                <i class="fa-solid fa-chart-line text-blue-500"></i>
                <span>Volg de voortgang in real-time</span>
              </div>
              <div class="feature-item">
                <i class="fa-solid fa-trophy text-purple-500"></i>
                <span>Win prijzen als je idee wint</span>
              </div>
            </div>

            <!-- CTA buttons -->
            <div class="flex flex-col gap-3">
              <NuxtLink to="/register" class="cta-button cta-primary">
                <i class="fa-solid fa-user-plus mr-2"></i>
                Account aanmaken
              </NuxtLink>
              <NuxtLink to="/ideas" class="cta-button cta-secondary">
                <i class="fa-solid fa-compass mr-2"></i>
                Bekijk ideeën
              </NuxtLink>
            </div>
          </div>
        </div>

        <!-- ───── STATS SECTION ───── -->
        <div ref="statsWrap" class="mt-20 max-w-4xl mx-auto">
          <div class="stats-card">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
              <div v-for="stat in stats" :key="stat.label" class="stat-item">
                <div class="stat-value">{{ stat.value }}</div>
                <div class="stat-label">{{ stat.label }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- ───── HOW IT WORKS ───── -->
        <div ref="howItWorks" class="mt-20 max-w-4xl mx-auto text-center">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Hoe het werkt</h2>
          <p class="text-gray-600 mb-12 max-w-2xl mx-auto">
            Van idee tot werkelijkheid in drie simpele stappen
          </p>

          <div class="grid md:grid-cols-3 gap-8">
            <div v-for="(step, i) in howItWorksSteps" :key="i" class="how-step">
              <div class="how-step-icon" :style="{ background: step.gradient }">
                <i :class="step.icon"></i>
              </div>
              <h3 class="font-bold text-gray-900 mt-4 mb-2">{{ step.title }}</h3>
              <p class="text-sm text-gray-500">{{ step.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useCmsContent } from "~/composables/content/useCmsContent";
import { useGsap } from "~/composables/useGsap";

usePageSeo({
  title: "Over Ideeënbord",
  description:
    "Ontdek waar Ideeënbord voor staat: consumenten en merken samenbrengen rond échte ideeën, wensen en feedback.",
});

useJsonLd([
  { "@type": "AboutPage", name: "Over Ideeënbord" },
  breadcrumbLd([
    { name: "Home", path: "/" },
    { name: "Over", path: "/about" },
  ]),
]);

const { content } = useCmsContent("uitleg");

const eyebrow = ref<HTMLElement | null>(null);
const heading = ref<HTMLElement | null>(null);
const subline = ref<HTMLElement | null>(null);
const cardsWrap = ref<HTMLElement | null>(null);
const statsWrap = ref<HTMLElement | null>(null);
const howItWorks = ref<HTMLElement | null>(null);

onMounted(() => {
  const { gsap, prefersReducedMotion } = useGsap();
  if (prefersReducedMotion.value || !gsap) return;

  // Hero animations
  gsap.from([eyebrow.value, heading.value, subline.value], {
    y: 30,
    opacity: 0,
    duration: 0.7,
    stagger: 0.12,
    ease: "power3.out",
  });

  // Cards animation
  if (cardsWrap.value) {
    gsap.from(cardsWrap.value.querySelectorAll(".feature-card"), {
      y: 60,
      opacity: 0,
      duration: 0.8,
      stagger: 0.15,
      ease: "power3.out",
      delay: 0.3,
    });
  }

  // Stats animation
  if (statsWrap.value) {
    gsap.from(statsWrap.value, {
      y: 40,
      opacity: 0,
      duration: 0.6,
      ease: "power3.out",
      scrollTrigger: {
        trigger: statsWrap.value,
        start: "top 85%",
      },
    });
  }

  // How it works animation
  if (howItWorks.value) {
    gsap.from(howItWorks.value.querySelectorAll(".how-step"), {
      y: 40,
      opacity: 0,
      duration: 0.6,
      stagger: 0.1,
      ease: "power3.out",
      scrollTrigger: {
        trigger: howItWorks.value,
        start: "top 85%",
      },
    });
  }
});

const brandData = computed(() => {
  const c = content.value;
  return {
    title: c["brand-title"] ?? "Voor merken: bouw wat ertoe doet",
    description: c["brand-description"] ?? "Ideeënbord helpt je om ideeën te verzamelen, te prioriteren met community-stemmen en transparant te leveren. Minder ruis, meer impact.",
    cta: {
      label: c["brand-cta-label"] ?? "Word deelnemend merk",
      to: c["brand-cta-link"] ?? "/become"
    },
  };
});

const brandSteps = computed(() => {
  const c = content.value;
  return [
    {
      id: 1,
      title: c["brand-step1-title"] ?? "Verzamel & prioriteer",
      description: c["brand-step1-description"] ?? "Ontvang ideeën op één plek, laat de community stemmen en kies wat je eerst bouwt.",
    },
    {
      id: 2,
      title: c["brand-step2-title"] ?? "Lever transparant",
      description: c["brand-step2-description"] ?? "Toon status, updates en releases zodat fans betrokken blijven en vertrouwen groeit.",
    },
  ];
});

const fanData = computed(() => {
  const c = content.value;
  return {
    title: c["fan-title"] ?? "Voor fans: laat je idee landen",
    description: c["fan-description"] ?? "Dien je idee in, stem mee en volg de voortgang. Jouw input helpt features sneller werkelijkheid te worden.",
  };
});

const stats = [
  { value: "42+", label: "Merken" },
  { value: "3.1k+", label: "Gebruikers" },
  { value: "8.7k+", label: "Ideeën" },
  { value: "156", label: "Gerealiseerd" },
];

const howItWorksSteps = [
  {
    icon: "fa-solid fa-pen",
    title: "1. Deel je idee",
    description: "Beschrijf je idee, wens of verbetering voor je favoriete merk.",
    gradient: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
  },
  {
    icon: "fa-solid fa-heart",
    title: "2. Krijg stemmen",
    description: "Andere fans stemmen op jouw idee. Hoe meer stemmen, hoe hoger de prioriteit.",
    gradient: "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)",
  },
  {
    icon: "fa-solid fa-check-circle",
    title: "3. Zie het gebouwd",
    description: "Merken bouwen de beste ideeën en je volgt de voortgang live.",
    gradient: "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)",
  },
];
</script>

<style scoped>
/* Hero Section */
.hero-section {
  background:
    radial-gradient(1100px 520px at 50% -10%, #2b3a52 0%, transparent 60%),
    linear-gradient(180deg, #1f2937 0%, #161e29 100%);
}

.hero-glow {
  position: absolute;
  top: -120px;
  right: 10%;
  width: 520px;
  height: 520px;
  border-radius: 9999px;
  background: radial-gradient(circle, rgba(247, 138, 29, 0.3), transparent 65%);
  filter: blur(40px);
  pointer-events: none;
}

.hero-glow-2 {
  position: absolute;
  bottom: -100px;
  left: 10%;
  width: 400px;
  height: 400px;
  border-radius: 9999px;
  background: radial-gradient(circle, rgba(255, 187, 0, 0.15), transparent 65%);
  filter: blur(50px);
  pointer-events: none;
}

/* Feature Cards */
.feature-card {
  background: white;
  border-radius: 24px;
  padding: 32px;
  box-shadow:
    0 4px 24px rgba(0, 0, 0, 0.06),
    0 1px 2px rgba(0, 0, 0, 0.04);
  border: 1px solid rgba(0, 0, 0, 0.04);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-4px);
  box-shadow:
    0 12px 40px rgba(0, 0, 0, 0.1),
    0 2px 4px rgba(0, 0, 0, 0.05);
}

.card-icon {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
  margin-bottom: 20px;
}

.card-icon-brand {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card-icon-fan {
  background: linear-gradient(135deg, #f97316 0%, #fbbf24 100%);
}

/* Steps */
.step-item {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  text-align: left;
}

.step-number {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #f97316 0%, #fbbf24 100%);
  color: white;
  font-weight: 700;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* Feature items */
.feature-item {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 15px;
  color: #374151;
}

.feature-item i {
  width: 20px;
  text-align: center;
}

/* CTA Buttons */
.cta-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 14px 24px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 15px;
  transition: all 0.2s ease;
  text-decoration: none;
}

.cta-primary {
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  color: white;
  box-shadow: 0 4px 14px rgba(249, 115, 22, 0.35);
}

.cta-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
}

.cta-secondary {
  background: #f3f4f6;
  color: #374151;
}

.cta-secondary:hover {
  background: #e5e7eb;
}

/* Stats Card */
.stats-card {
  background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
  border-radius: 24px;
  padding: 40px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.stat-item {
  text-align: center;
}

.stat-value {
  font-size: 32px;
  font-weight: 800;
  background: linear-gradient(135deg, #f97316 0%, #fbbf24 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.stat-label {
  font-size: 14px;
  color: #9ca3af;
  margin-top: 4px;
}

/* How it works */
.how-step {
  padding: 24px;
}

.how-step-icon {
  width: 64px;
  height: 64px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
  margin: 0 auto;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}
</style>
