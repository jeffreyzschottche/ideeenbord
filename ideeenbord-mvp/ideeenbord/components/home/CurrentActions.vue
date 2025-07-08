<template>
  <section class="py-16 px-10">
    <div
      class="flex flex-col md:flex-row items-stretch <!-- ⇽ nieuw --> justify-center gap-6 md:gap-10 max-w-6xl mx-auto"
    >
      <!-- ───────── LINKERKAART – nieuwe merken ───────── -->
      <div class="flex-1">
        <div
          class="bg-nav glow-box p-8 rounded-lg h-full <!-- ⇽ h-full --> flex flex-col items-center"
        >
          <h3 class="text-xl md:text-2xl font-bold text-white text-center mb-6">
            {{
              content["currentactions-left-title"] ||
              "Nieuwe deelnemende merken"
            }}
          </h3>

          <ul class="w-full max-w-xs space-y-2">
            <li
              v-for="(name, idx) in recentBrands"
              :key="idx"
              class="odd:bg-nav/80 even:bg-nav/60 rounded-md transition hover:bg-[var(--color-brand)]/70 bg-brand"
            >
              <a
                :href="`/brands/${name}`"
                class="block px-4 py-2 text-white text-center text-base md:text-lg hover:underline"
              >
                {{ name }}
              </a>
            </li>

            <li
              v-if="!recentBrands.length"
              class="bg-nav/80 rounded-md h-12 flex items-center justify-center text-white"
            >
              Nog geen nieuwe merken.
            </li>
          </ul>
        </div>
      </div>

      <!-- ───────── MIDDEN – knipperende lamp ───────── -->
      <div
        class="flex flex-col md:flex-row items-center justify-center gap-6 p-20 md:gap-10 max-w-6xl mx-auto"
      >
        <i
          class="fa-regular fa-lightbulb absolute text-8xl md:text-[7rem] regular-bulb"
        />
        <i
          class="fa-solid fa-lightbulb absolute text-8xl md:text-[7rem] text-[var(--color-brand)] solid-bulb"
        />
      </div>

      <!-- ───────── RECHTERKAART – nieuwste quizzes ───────── -->
      <div class="flex-1">
        <div
          class="bg-nav glow-box p-8 rounded-lg h-full <!-- ⇽ h-full --> flex flex-col items-center"
        >
          <h3 class="text-xl md:text-2xl font-bold text-white text-center mb-6">
            {{
              content["currentactions-right-title"] ||
              "Laatste winacties & quizzes"
            }}
          </h3>

          <ul class="w-full max-w-xs space-y-2">
            <li
              v-for="quiz in recentQuizzes"
              :key="quiz.id"
              class="odd:bg-nav/80 even:bg-nav/60 rounded-md transition hover:bg-[var(--color-brand)]/70 bg-brand"
            >
              <NuxtLink
                :to="`/brands/${quiz.brand.slug}`"
                class="block px-4 py-2 text-white text-center text-base md:text-lg hover:underline"
              >
                {{ quiz.title }}
              </NuxtLink>
            </li>

            <li
              v-if="!recentQuizzes.length"
              class="bg-nav/80 rounded-md h-12 flex items-center justify-center text-white"
            >
              Momenteel geen acties.
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { apiFetch } from "~/composables/adapter/useApi";
import { useCmsContent } from "~/composables/content/useCmsContent";
import type { Brand } from "~/types/brand";

const { content } = useCmsContent("home");

const recentBrands = ref<string[]>([]);

interface QuizPreview {
  id: number;
  title: string;
  brand: { slug: string; title: string };
}
const recentQuizzes = ref<QuizPreview[]>([]);

onMounted(async () => {
  try {
    const brands = await apiFetch<Brand[]>("/brands", {
      params: { accepted: 1, limit: 5, order: "desc" },
    });
    recentBrands.value = brands.map((b) => b.title);

    recentQuizzes.value = await apiFetch<QuizPreview[]>("/quizzes", {
      params: { limit: 5, order: "desc" },
    });
  } catch (e) {
    console.error(e);
  }
});
</script>

<style scoped>
/* ───────── kaart-glow + rand ───────── */
.glow-box {
  position: relative;
  border-radius: 0.75rem;
  box-shadow: 0 0 32px 0 var(--color-brand), 0 0 0 4px var(--color-brand);
}
.glow-box::after {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: inherit;
  background: radial-gradient(
    circle at 50% 50%,
    var(--color-brand) 0%,
    transparent 70%
  );
  opacity: 0.22;
  z-index: -1;
}

/* ───────── LAMP ANIMATIES ───────── */
@keyframes blink-off {
  0%,
  45% {
    opacity: 1;
  }
  60%,
  90% {
    opacity: 0;
  }
  100% {
    opacity: 1;
  }
}
@keyframes blink-on {
  0%,
  45% {
    opacity: 0;
    filter: drop-shadow(0 0 4px var(--color-brand));
  }
  60%,
  90% {
    opacity: 1;
    filter: drop-shadow(0 0 14px var(--color-brand));
  }
  100% {
    opacity: 0;
    filter: drop-shadow(0 0 4px var(--color-brand));
  }
}
.regular-bulb {
  animation: blink-off 3s ease-in-out infinite;
}
.solid-bulb {
  animation: blink-on 3s ease-in-out infinite;
}
</style>
