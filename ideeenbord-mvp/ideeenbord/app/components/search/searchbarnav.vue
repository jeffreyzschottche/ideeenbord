<template>
  <div>
    <!-- Trigger: pill op desktop, icoon op mobiel -->
    <button
      @click="openModal"
      class="hidden md:inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 hover:bg-white/20 text-gray-200 text-sm transition w-48"
    >
      <i class="fa-solid fa-magnifying-glass"></i>
      <span class="opacity-80">Zoeken…</span>
    </button>
    <button @click="openModal" class="md:hidden text-gray-200">
      <i class="fa-solid fa-magnifying-glass text-xl"></i>
    </button>

    <teleport to="body">
      <transition name="fade">
        <div
          v-if="showModal"
          class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] flex justify-center items-start pt-24 px-4"
          @click.self="closeModal"
        >
          <div class="bg-white rounded-2xl w-full max-w-xl shadow-2xl overflow-hidden">
            <!-- Zoekveld -->
            <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
              <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
              <input
                v-model="query"
                autofocus
                placeholder="Zoek naar merken of pagina's…"
                class="flex-1 outline-none text-[var(--color-nav)] placeholder-gray-400"
              />
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark text-xl"></i>
              </button>
            </div>

            <div class="max-h-[60vh] overflow-y-auto p-3 space-y-5">
              <!-- Merken -->
              <div>
                <p class="px-2 text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Merken</p>
                <ul v-if="brands.length" class="space-y-1">
                  <li v-for="brand in brands" :key="brand.id">
                    <NuxtLink
                      :to="`/brands/${brand.slug}`"
                      class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--color-bg)] transition"
                      @click="closeModal"
                    >
                      <span class="w-8 h-8 rounded-lg bg-[var(--color-bg)] flex items-center justify-center text-[var(--color-brand)]">
                        <i class="fa-solid fa-tag text-xs"></i>
                      </span>
                      <span class="font-semibold text-[var(--color-nav)]">{{ brand.title }}</span>
                    </NuxtLink>
                  </li>
                </ul>
                <p v-else class="px-3 text-sm text-gray-400">Geen resultaten</p>
              </div>

              <!-- Pagina's -->
              <div>
                <p class="px-2 text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Pagina's</p>
                <ul v-if="pages.length" class="space-y-1">
                  <li v-for="page in pages" :key="page.id">
                    <NuxtLink
                      :to="page.route"
                      class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-[var(--color-bg)] transition"
                      @click="closeModal"
                    >
                      <span class="w-8 h-8 rounded-lg bg-[var(--color-bg)] flex items-center justify-center text-gray-400">
                        <i class="fa-regular fa-file text-xs"></i>
                      </span>
                      <span class="font-semibold text-[var(--color-nav)]">{{ page.title }}</span>
                    </NuxtLink>
                  </li>
                </ul>
                <p v-else class="px-3 text-sm text-gray-400">Geen resultaten</p>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </teleport>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useSearch } from "~/composables/useSearch";

const query = ref("");
const showModal = ref(false);
const openModal = () => (showModal.value = true);
const closeModal = () => (showModal.value = false);

const { search, brands, pages, loading } = useSearch();

watch(query, async (val) => {
  await search(val);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
