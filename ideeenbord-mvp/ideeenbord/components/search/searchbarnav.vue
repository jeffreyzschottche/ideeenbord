<template>
  <div>
    <div class="flex w-full h-full justify-end md:mt-1 items-center">
      <button @click="openModal" class="">
        <i class="fa-solid fa-search text-xl text-gray-300"></i>
      </button>
    </div>

    <teleport to="body">
      <transition name="fade" class="">
        <div
          v-if="showModal"
          class="fixed inset-0 bg-black/80 z-100 flex justify-center items-start pt-24 px-4"
        >
          <div
            class="bg-white border-2 border-grey-900 rounded-lg w-full max-w-lg p-6"
          >
            <div class="flex items-center mb-4">
              <input
                v-model="query"
                autofocus
                @input="onInput"
                placeholder="Zoek naar merken of pagina’s…"
                @keyup.enter="directRoute && navigateTo(directRoute)"
                class="flex-1 border-2 border-orange-500 rounded px-4 py-2"
              />
              <button @click="closeModal" class="ml-3">
                <i class="fa-solid fa-xmark text-2xl text-orange-500"></i>
              </button>
            </div>

            <div class="space-y-6">
              <div>
                <h4 class="font-semibold text-orange-500">Merken:</h4>
                <ul v-if="brands.length">
                  <li v-for="brand in brands" :key="brand.id">
                    <NuxtLink
                      :to="`/brands/${brand.slug}`"
                      class="text-blue-600 underline"
                    >
                      {{ brand.title }}
                    </NuxtLink>
                  </li>
                </ul>
                <p v-else class="text-gray-400">Geen resultaten</p>
              </div>

              <div>
                <h4 class="font-semibold text-orange-500">Pagina’s:</h4>
                <ul v-if="pages.length">
                  <li v-for="page in pages" :key="page.id">
                    <NuxtLink :to="page.route" class="text-blue-600 underline">
                      {{ page.title }}
                    </NuxtLink>
                  </li>
                </ul>
                <p v-else class="text-gray-400">Geen resultaten</p>
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
