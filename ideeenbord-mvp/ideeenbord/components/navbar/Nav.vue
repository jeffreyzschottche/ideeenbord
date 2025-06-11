<template>
  <header
    :class="[
      'fixed w-full h-20 z-50 bg-gray-900 text-white transition-transform duration-300',
      { '-translate-y-full': !showHeader, 'translate-y-0': showHeader },
    ]"
  >
    <div class="container mx-auto flex justify-between items-center py-4 px-6">
      <!-- Logo -->
      <NuxtLink to="/" class="relative text-3xl font-bold">
        IDEEEN<span class="font-light">BORD</span>
        <i
          class="fa-solid fa-lightbulb ml-2 absolute flex items-center justify-center text-orange-400 lamp-glow"
        ></i>
      </NuxtLink>

      <!-- Desktop nav -->
      <nav class="hidden md:flex space-x-6 text-md items-center">
        <NuxtLink to="/news" class="nav-link">Nieuws</NuxtLink>
        <NuxtLink to="/win" class="nav-link">Winacties</NuxtLink>
        <NuxtLink to="/ideas" class="nav-link">Ideeën</NuxtLink>
        <NuxtLink to="/participants" class="nav-link">Deelnemers</NuxtLink>

        <template v-if="!isAuthenticated">
          <NuxtLink to="/register" class="nav-link">Registreren</NuxtLink>
          <NuxtLink to="/login" class="nav-link">Inloggen</NuxtLink>
        </template>
        <template v-else>
          <div class="relative" @click="toggleProfileDropdown">
            <button class="nav-link flex items-center">
              Mijn profiel <i class="fa-solid fa-caret-down ml-1"></i>
            </button>
            <transition name="fade">
              <div
                v-if="profileOpen"
                class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-lg py-2 z-50"
              >
                <NuxtLink
                  to="/my-account"
                  class="block px-4 py-2 text-gray-200 hover:bg-gray-700"
                  >Account</NuxtLink
                >

                <button
                  @click="logout"
                  class="w-full text-left px-4 py-2 text-gray-200 hover:bg-gray-700"
                >
                  Uitloggen
                </button>
              </div>
            </transition>
          </div>
        </template>

        <NuxtLink
          to="/app"
          class="cta hover:orange-box-shadow bg-gradient-to-b from-orange-400 to-orange-600 text-white px-2 py-2 rounded-xl w-35 font-bold text-center transition duration-300"
          >+ Plaats idee</NuxtLink
        >
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

          <template v-if="!isAuthenticated">
            <NuxtLink to="/register" class="nav-link" @click="toggleMenu"
              >Registreren</NuxtLink
            >
            <NuxtLink to="/login" class="nav-link" @click="toggleMenu"
              >Inloggen</NuxtLink
            >
          </template>
          <template v-else>
            <NuxtLink to="/my-account" class="nav-link" @click="toggleMenu"
              >Mijn profiel</NuxtLink
            >
            <button
              @click="
                logout;
                toggleMenu;
              "
              class="nav-link text-left"
            >
              Uitloggen
            </button>
          </template>

          <NuxtLink
            to="/app"
            class="cta hover:orange-box-shadow bg-orange-500 text-white px-2 py-2 rounded-xl w-35 font-bold text-center transition duration-300"
            >+ Plaats idee</NuxtLink
          >
        </div>
      </transition>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import Searchbarnav from "../search/searchbarnav.vue";

const menuOpen = ref(false);
const profileOpen = ref(false);
const showHeader = ref(true);
const isAuthenticated = ref(false);
let lastScrollY = 0;
const router = useRouter();

const toggleMenu = () => (menuOpen.value = !menuOpen.value);
const toggleProfileDropdown = () => (profileOpen.value = !profileOpen.value);

const handleScroll = () => {
  const current = window.scrollY;
  showHeader.value = !(current > lastScrollY && current > 100);
  lastScrollY = current;
};

const logout = () => {
  localStorage.removeItem("token");
  localStorage.removeItem("userId");
  router.push("/");
  isAuthenticated.value = false;
};

onMounted(() => {
  window.addEventListener("scroll", handleScroll);
  isAuthenticated.value = !!(
    localStorage.getItem("token") && localStorage.getItem("userId")
  );
});
onUnmounted(() => window.removeEventListener("scroll", handleScroll));
</script>

<style>
@import "tailwindcss";
.nav-link {
  @apply text-gray-300 hover:text-white transition duration-300;
}
.cta:hover {
  box-shadow: 0 0 15px 5px var(--color-orange-500);
}
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s, opacity 0.3s;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
@media only screen and (max-width: 600px) {
  .searchbar {
    margin-left: 6.5em;
    margin-top: 0.5em;
  }
}
</style>
