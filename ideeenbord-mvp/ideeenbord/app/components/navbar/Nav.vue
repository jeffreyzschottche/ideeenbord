<template>
  <header
    :class="[
      'fixed w-full h-24 z-50 bg-[#1c2434] text-white transition-all duration-300',
      { '-translate-y-full': !showHeader, 'translate-y-0': showHeader },
      scrolled ? 'shadow-xl' : '',
    ]"
  >
    <div
      ref="navInner"
      class="container mx-auto h-full flex justify-between items-center py-2 px-2"
    >
      <!-- Logo -->
      <NuxtLink to="/" class="flex items-center shrink-0">
        <img
          src="/ideeenbord-logo-footer-and-nav.png"
          alt="Ideeënbord"
          class="h-[48px] md:h-[54px] w-auto object-contain"
        />
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

        <!-- USER - Profile Dropdown -->
        <template v-else>
          <div class="relative" ref="profileDropdownRef">
            <button
              @click="toggleProfileDropdown"
              class="profile-trigger"
              :class="{ 'active': profileDropdownOpen }"
            >
              <span class="flex items-center gap-2">
                <span class="profile-avatar">
                  {{ user.username?.charAt(0).toUpperCase() }}
                </span>
                <span>Mijn Profiel</span>
                <!-- Notification Badge -->
                <span v-if="notificationCount > 0" class="notification-badge">
                  {{ notificationCount > 9 ? '9+' : notificationCount }}
                </span>
              </span>
              <i class="fa-solid fa-chevron-down text-xs ml-1 transition-transform" :class="{ 'rotate-180': profileDropdownOpen }"></i>
            </button>

            <!-- Dropdown Menu -->
            <transition name="dropdown">
              <div v-if="profileDropdownOpen" class="profile-dropdown">
                <div class="dropdown-header">
                  <span class="dropdown-avatar">
                    {{ user.username?.charAt(0).toUpperCase() }}
                  </span>
                  <div>
                    <p class="font-semibold text-gray-900">{{ user.name || user.username }}</p>
                    <p class="text-xs text-gray-500">@{{ user.username }}</p>
                  </div>
                </div>

                <div class="dropdown-divider"></div>

                <NuxtLink
                  :to="`/user/${user.username}`"
                  class="dropdown-item"
                  @click="closeProfileDropdown"
                >
                  <i class="fa-solid fa-user"></i>
                  <span>Bekijk profiel</span>
                </NuxtLink>

                <NuxtLink
                  :to="`/user/${user.username}#inbox`"
                  class="dropdown-item"
                  @click="closeProfileDropdown"
                >
                  <i class="fa-solid fa-inbox"></i>
                  <span>Inbox</span>
                  <span v-if="notificationCount > 0" class="dropdown-badge">
                    {{ notificationCount }}
                  </span>
                </NuxtLink>

                <NuxtLink
                  :to="`/user/${user.username}#ideas`"
                  class="dropdown-item"
                  @click="closeProfileDropdown"
                >
                  <i class="fa-solid fa-lightbulb"></i>
                  <span>Mijn ideeën</span>
                </NuxtLink>

                <div class="dropdown-divider"></div>

                <button @click="handleLogout" class="dropdown-item dropdown-logout">
                  <i class="fa-solid fa-right-from-bracket"></i>
                  <span>Uitloggen</span>
                </button>
              </div>
            </transition>
          </div>
        </template>

        <button class="cta w-35 text-center" @click="openModal">
          + Plaats idee
        </button>
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
          class="absolute top-24 left-0 w-full bg-gray-800 text-white p-4 flex flex-col space-y-4 md:hidden"
        >
          <SearchSearchbarnav class="mr-auto nav-link" />

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

          <!-- USER - Mobile -->
          <template v-else>
            <div class="mobile-profile-section">
              <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-700">
                <span class="mobile-avatar">
                  {{ user.username?.charAt(0).toUpperCase() }}
                </span>
                <div>
                  <p class="font-semibold">{{ user.name || user.username }}</p>
                  <p class="text-xs text-gray-400">@{{ user.username }}</p>
                </div>
              </div>
            </div>

            <NuxtLink
              :to="`/user/${user.username}`"
              class="nav-link flex items-center gap-2"
              @click="toggleMenu"
            >
              <i class="fa-solid fa-user text-sm"></i>
              Mijn Profiel
              <span v-if="notificationCount > 0" class="mobile-badge">
                {{ notificationCount }}
              </span>
            </NuxtLink>

            <NuxtLink
              :to="`/user/${user.username}#inbox`"
              class="nav-link flex items-center gap-2"
              @click="toggleMenu"
            >
              <i class="fa-solid fa-inbox text-sm"></i>
              Inbox
              <span v-if="notificationCount > 0" class="mobile-badge">
                {{ notificationCount }}
              </span>
            </NuxtLink>

            <button @click="handleLogout" class="nav-link text-left flex items-center gap-2 text-red-400">
              <i class="fa-solid fa-right-from-bracket text-sm"></i>
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
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { useRouter } from "vue-router";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import { storeToRefs } from "pinia";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { apiFetch } from "~/composables/adapter/useApi";
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
  () => owner.value?.brand.title || owner.value?.brand.slug || "Dashboard",
);

/* UI state */
const menuOpen = ref(false);
const showHeader = ref(true);
const scrolled = ref(false);
const navInner = ref<HTMLElement | null>(null);
const profileDropdownOpen = ref(false);
const profileDropdownRef = ref<HTMLElement | null>(null);
const notificationCount = ref(0);
let lastScrollY = 0;

const toggleMenu = () => (menuOpen.value = !menuOpen.value);
const toggleProfileDropdown = () => (profileDropdownOpen.value = !profileDropdownOpen.value);
const closeProfileDropdown = () => (profileDropdownOpen.value = false);

// Close dropdown when clicking outside
function handleClickOutside(event: MouseEvent) {
  if (profileDropdownRef.value && !profileDropdownRef.value.contains(event.target as Node)) {
    profileDropdownOpen.value = false;
  }
}

// Fetch notification count
async function fetchNotificationCount() {
  if (!user.value?.username) return;
  try {
    const data = await apiFetch<any[] | { notifications: any[] }>(
      `/users/${user.value.username}/notifications`
    );
    const notifications = Array.isArray(data) ? data : (data?.notifications || []);
    notificationCount.value = notifications.length;
  } catch {
    notificationCount.value = 0;
  }
}

// Watch for user changes to fetch notifications
watch(
  () => user.value?.username,
  (username) => {
    if (username) {
      fetchNotificationCount();
    } else {
      notificationCount.value = 0;
    }
  },
  { immediate: true }
);

function handleScroll() {
  const current = window.scrollY;
  showHeader.value = !(current > lastScrollY && current > 100);
  scrolled.value = current > 20;
  lastScrollY = current;
  // Close dropdown on scroll
  if (profileDropdownOpen.value) {
    profileDropdownOpen.value = false;
  }
}

function handleLogout() {
  if (isBrandAuth.value) boStore.logout();
  if (isUserAuth.value) userStore.logout();
  menuOpen.value = false;
  profileDropdownOpen.value = false;
  router.push("/");
}

/* Lifecycle */
onMounted(async () => {
  await boStore.initAuth();
  await userStore.initAuth();
  window.addEventListener("scroll", handleScroll);
  document.addEventListener("click", handleClickOutside);

  // Subtle entrance animation
  const { gsap, prefersReducedMotion } = useGsap();
  if (!prefersReducedMotion.value && gsap && navInner.value) {
    gsap.from(navInner.value.children, {
      y: -16,
      opacity: 0,
      duration: 0.5,
      stagger: 0.06,
      ease: "power2.out",
    });
  }
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
  document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition:
    transform 0.3s,
    opacity 0.3s;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}

/* Profile Dropdown Styles */
.profile-trigger {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 12px;
  font-weight: 500;
  transition: all 0.2s ease;
  cursor: pointer;
}

.profile-trigger:hover,
.profile-trigger.active {
  background: rgba(255, 255, 255, 0.1);
}

.profile-avatar {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
}

.notification-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  background: #ef4444;
  color: white;
  border-radius: 9px;
  font-size: 10px;
  font-weight: 700;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.profile-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 240px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15), 0 2px 10px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  z-index: 100;
}

.dropdown-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.05) 0%, rgba(251, 191, 36, 0.05) 100%);
}

.dropdown-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 700;
  color: white;
  flex-shrink: 0;
}

.dropdown-divider {
  height: 1px;
  background: #f3f4f6;
  margin: 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  color: #374151;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.15s ease;
  cursor: pointer;
  width: 100%;
  text-align: left;
}

.dropdown-item:hover {
  background: #f9fafb;
  color: #f97316;
}

.dropdown-item i {
  width: 16px;
  text-align: center;
  color: #9ca3af;
  transition: color 0.15s ease;
}

.dropdown-item:hover i {
  color: #f97316;
}

.dropdown-badge {
  margin-left: auto;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  height: 20px;
  padding: 0 6px;
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  color: white;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 700;
}

.dropdown-logout {
  color: #ef4444;
}

.dropdown-logout:hover {
  background: #fef2f2;
  color: #dc2626;
}

.dropdown-logout i {
  color: #ef4444;
}

/* Dropdown animation */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* Mobile styles */
.mobile-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 700;
  flex-shrink: 0;
}

.mobile-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  height: 20px;
  padding: 0 6px;
  background: #ef4444;
  color: white;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 700;
  margin-left: auto;
}

.mobile-profile-section {
  margin-bottom: 0;
}
</style>
