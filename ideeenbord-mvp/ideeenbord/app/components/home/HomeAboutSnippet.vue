<template>
  <section class="py-16 md:py-24">
    <div class="flex flex-col-reverse md:flex-row items-center gap-10 md:gap-16">
      <!-- Tekst -->
      <div class="flex-1">
        <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
          Wat is Ideeenbord?
        </span>
        <h2 class="text-3xl md:text-4xl font-extrabold text-[var(--color-nav)] leading-tight">
          De brug tussen consumenten en merken
        </h2>
        <p class="mt-5 text-lg text-gray-600 leading-relaxed max-w-xl">
          Ideeenbord is het platform waar jij als consument direct invloed hebt op producten en diensten.
          Geen enquetes die in een la belanden, maar echte gesprekken die leiden tot echte veranderingen.
        </p>

        <ul class="mt-6 space-y-4">
          <li class="flex items-start gap-3 text-gray-700">
            <span class="w-8 h-8 rounded-lg bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0 mt-0.5">
              <i class="fa-solid fa-check"></i>
            </span>
            <div>
              <strong class="text-[var(--color-nav)]">Deel wat jij wilt zien</strong>
              <p class="text-sm text-gray-500 mt-0.5">Een nieuw product, betere service, andere openingstijden - alles mag.</p>
            </div>
          </li>
          <li class="flex items-start gap-3 text-gray-700">
            <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 mt-0.5">
              <i class="fa-solid fa-users"></i>
            </span>
            <div>
              <strong class="text-[var(--color-nav)]">Stem en geef gewicht</strong>
              <p class="text-sm text-gray-500 mt-0.5">Like ideeen die je aanspreken. Hoe meer stemmen, hoe groter de kans dat het gebeurt.</p>
            </div>
          </li>
          <li class="flex items-start gap-3 text-gray-700">
            <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0 mt-0.5">
              <i class="fa-solid fa-rocket"></i>
            </span>
            <div>
              <strong class="text-[var(--color-nav)]">Zie je idee werkelijkheid worden</strong>
              <p class="text-sm text-gray-500 mt-0.5">Merken nemen actie en houden je op de hoogte van de voortgang.</p>
            </div>
          </li>
        </ul>

        <div class="mt-8 flex flex-wrap gap-3">
          <NuxtLink to="/register" class="cta px-6 py-3">
            Start nu gratis
          </NuxtLink>
          <NuxtLink to="/about" class="px-6 py-3 rounded-xl border-2 border-gray-200 text-[var(--color-nav)] hover:bg-gray-50 font-semibold transition">
            Lees meer
          </NuxtLink>
        </div>
      </div>

      <!-- Visual / Success Story -->
      <div class="flex-1 w-full">
        <div class="relative">
          <div class="absolute -inset-3 rounded-[2rem] bg-gradient-to-br from-[var(--color-brand)]/10 to-amber-100/30 -z-10 rotate-2"></div>

          <!-- Success story card -->
          <div class="rounded-3xl bg-white border border-gray-100 shadow-2xl p-6 md:p-8">
            <div class="flex items-center gap-2 mb-4">
              <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold uppercase tracking-wide">
                Succes verhaal
              </span>
            </div>

            <blockquote class="text-lg md:text-xl text-[var(--color-nav)] font-medium leading-relaxed mb-6">
              "Ik vroeg om haverdrank als standaardoptie bij mijn koffie. Drie weken later was het er. Mijn idee, hun actie."
            </blockquote>

            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center text-white font-bold">
                S
              </div>
              <div>
                <p class="font-bold text-[var(--color-nav)]">Sophie, 28</p>
                <p class="text-sm text-gray-500">Amsterdam</p>
              </div>
            </div>

            <!-- Impact stats -->
            <div class="mt-6 pt-6 border-t border-gray-100 grid grid-cols-3 gap-4 text-center">
              <div>
                <p class="text-2xl font-extrabold text-[var(--color-brand)]">47</p>
                <p class="text-xs text-gray-500">likes</p>
              </div>
              <div>
                <p class="text-2xl font-extrabold text-green-500">21</p>
                <p class="text-xs text-gray-500">dagen</p>
              </div>
              <div>
                <p class="text-2xl font-extrabold text-purple-500">
                  <i class="fa-solid fa-check"></i>
                </p>
                <p class="text-xs text-gray-500">gerealiseerd</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useCmsContent } from "~/composables/content/useCmsContent";
const { content } = useCmsContent("home");
</script>
