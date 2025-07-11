<template>
  <header
    :class="[
      'fixed w-full h-20 z-50 bg-nav text-white transition-transform duration-300',
      { '-translate-y-full': !showHeader, 'translate-y-0': showHeader },
    ]"
  >
    <div class="container mx-auto flex justify-between items-center py-4 px-2">
      <!-- Logo -->
      <NuxtLink to="/" class="relative text-3xl font-bold">
        IDEEEN<span class="font-light">BORD</span>
        <i
          class="fa-solid fa-lightbulb ml-2 absolute text-orange-400 lamp-glow"
        ></i>
      </NuxtLink>

      <!-- Desktop nav -->
      <nav class="hidden md:flex space-x-6 text-md items-center">
        <SearchSearchbarnav />

        <NuxtLink to="/about" class="nav-link">Uitleg</NuxtLink>
        <NuxtLink to="/news" class="nav-link">Nieuws</NuxtLink>
        <NuxtLink to="/win" class="nav-link">Winacties</NuxtLink>
        <NuxtLink to="/ideas" class="nav-link">Ideeën</NuxtLink>
        <NuxtLink to="/participants" class="nav-link">Deelnemers</NuxtLink>

        <!-- NIET ingelogd -->
        <template v-if="!isBrandAuth && !isUserAuth">
          <NuxtLink to="/register" class="nav-link">Registreren</NuxtLink>
          <NuxtLink to="/login" class="nav-link">Inloggen</NuxtLink>
        </template>

        <!-- BRAND-OWNER -->
        <template v-else-if="isBrandAuth">
          <NuxtLink :to="`/dashboard/${owner.brand.slug}`" class="nav-link">
            Dashboard – {{ brandLabel }}
          </NuxtLink>
          <button @click="handleLogout" class="nav-link">Uitloggen</button>
        </template>

        <!-- USER -->
        <template v-else>
          <NuxtLink :to="`/user/${user.username}`" class="nav-link">
            Mijn Profiel
          </NuxtLink>
          <button @click="handleLogout" class="nav-link">Uitloggen</button>
        </template>

        <Button class="cta w-35 text-center" @click="openModal"
          >+ Plaats idee</Button
        >
        <IdeasIdeaSubmitModal v-if="showModal" @close="closeModal" />
      </nav>

      <!-- Hamburger -->
      <button @click="toggleMenu" class="md:hidden focus:outline-none">
        <svg
          v-if="!menuOpen"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          class="w-8 h-8"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 6h16M4 12h16m-7 6h7"
          />
        </svg>
        <svg
          v-else
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          class="w-8 h-8 transform rotate-90"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>

      <!-- Mobile nav -->
      <transition name="slide">
        <div
          v-if="menuOpen"
          class="absolute top-16 left-0 w-full bg-gray-800 text-white p-4 flex flex-col space-y-4 md:hidden"
        >
          <SearchSearchBarNav class="mr-auto nav-link" />

          <NuxtLink to="/about" class="nav-link" @click="toggleMenu"
            >Uitleg</NuxtLink
          >
          <NuxtLink to="/news" class="nav-link" @click="toggleMenu"
            >Nieuws</NuxtLink
          >
          <NuxtLink to="/win" class="nav-link" @click="toggleMenu"
            >Winacties</NuxtLink
          >
          <NuxtLink to="/ideas" class="nav-link" @click="toggleMenu"
            >Ideeën</NuxtLink
          >
          <NuxtLink to="/participants" class="nav-link" @click="toggleMenu"
            >Deelnemers</NuxtLink
          >

          <!-- NIET ingelogd -->
          <template v-if="!isBrandAuth && !isUserAuth">
            <NuxtLink to="/register" class="nav-link" @click="toggleMenu"
              >Registreren</NuxtLink
            >
            <NuxtLink to="/login" class="nav-link" @click="toggleMenu"
              >Inloggen</NuxtLink
            >
          </template>

          <!-- BRAND-OWNER -->
          <template v-else-if="isBrandAuth">
            <NuxtLink
              :to="`/dashboard/${owner.brand.slug}`"
              class="nav-link"
              @click="toggleMenu"
            >
              Dashboard – {{ brandLabel }}
            </NuxtLink>
            <button @click="handleLogout" class="nav-link text-left">
              Uitloggen
            </button>
          </template>

          <!-- USER -->
          <template v-else>
            <NuxtLink
              :to="`/user/${user.username}`"
              class="nav-link"
              @click="toggleMenu"
            >
              Mijn Profiel
            </NuxtLink>
            <button @click="handleLogout" class="nav-link text-left">
              Uitloggen
            </button>
          </template>

          <NuxtLink to="/app" class="cta text-center">+ Plaats idee</NuxtLink>
        </div>
      </transition>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import { storeToRefs } from "pinia";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { SearchSearchbarnav } from "#components";

/* Stores */
const userStore = useUserAuthStore();
const boStore = useBrandOwnerAuthStore();
const router = useRouter();

const showModal = ref(false);
const auth = useUserAuthStore();
const { trigger } = useResponseDisplay();

function openModal() {
  if (!auth.token) {
    trigger("Log in om een idee te plaatsen.", "warning");
    return;
  }
  showModal.value = true;
}
const closeModal = () => (showModal.value = false);

const { token: userToken, user } = storeToRefs(userStore);
const { token: boToken, owner } = storeToRefs(boStore);

const isUserAuth = computed(() => !!userToken.value && !!user.value);
const isBrandAuth = computed(() => !!boToken.value && !!owner.value);
const brandLabel = computed(
  () => owner.value?.brand.title || owner.value?.brand.slug || "Dashboard"
);

/* UI state */
const menuOpen = ref(false);
const showHeader = ref(true);
let lastScrollY = 0;

const toggleMenu = () => (menuOpen.value = !menuOpen.value);

function handleScroll() {
  const current = window.scrollY;
  showHeader.value = !(current > lastScrollY && current > 100);
  lastScrollY = current;
}

function handleLogout() {
  if (isBrandAuth.value) boStore.logout();
  if (isUserAuth.value) userStore.logout();
  menuOpen.value = false;
  router.push("/");
}

/* Lifecycle */
onMounted(async () => {
  await boStore.initAuth(); // eerst brand-owner
  await userStore.initAuth(); // dan gewone user
  window.addEventListener("scroll", handleScroll);
});
onUnmounted(() => window.removeEventListener("scroll", handleScroll));
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s, opacity 0.3s;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}
</style>
