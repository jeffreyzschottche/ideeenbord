<template>
  <footer class="bg-nav text-light py-6 w-full">
    <div
      class="container mx-auto px-2 grid grid-cols-1 md:grid-cols-4 gap-6 text-center md:text-left"
    >
      <div>
        <h2 class="text-3xl font-bold light-text">
          IDEEEN<span class="font-light">BORD</span>
          <i
            class="fa-solid fa-lightbulb ml-2 absolute flex items-center justify-center text-orange-400 lamp-glow"
          ></i>
        </h2>
        <p class="text-sm light-text">
          &copy; {{ year }} Alle rechten voorbehouden.
        </p>
      </div>

      <div>
        <h3 class="font-semibold mb-2 light-text">Navigatie</h3>
        <ul class="space-y-1">
          <li><NuxtLink to="/" class="footer-link">Home</NuxtLink></li>
          <li><NuxtLink to="/nieuws" class="footer-link">Nieuws</NuxtLink></li>
          <li>
            <NuxtLink to="/voor-merken" class="footer-link"
              >Voor merken</NuxtLink
            >
          </li>
          <li>
            <NuxtLink to="/winacties" class="footer-link">Winacties</NuxtLink>
          </li>
          <li>
            <NuxtLink to="/register" class="footer-link">Aanmelden</NuxtLink>
          </li>
        </ul>
      </div>

      <div>
        <h3 class="font-semibold mb-2 light-text">Voor merken</h3>
        <ul class="space-y-1">
          <li>
            <NuxtLink to="/voorwaarden" class="footer-link"
              >Abonnementen</NuxtLink
            >
          </li>
          <li>
            <NuxtLink to="/voorwaarden" class="footer-link"
              >Voorwaarden</NuxtLink
            >
          </li>
          <li>
            <NuxtLink to="/voorwaarden" class="footer-link"
              >Privacy Statement</NuxtLink
            >
          </li>
          <li>
            <NuxtLink to="/over" class="footer-link"
              >Inloggen als merk</NuxtLink
            >
          </li>
          <li>
            <NuxtLink to="/contact" class="footer-link"
              >Dashboard voor merken</NuxtLink
            >
          </li>
          <li>
            <NuxtLink to="/privacy" class="footer-link"
              >Registreer merk</NuxtLink
            >
          </li>
          <li>
            <NuxtLink to="/privacy" class="footer-link"
              >Claim een pagina</NuxtLink
            >
          </li>
          <li>
            <NuxtLink to="/privacy" class="footer-link"
              >Meer informatie voor merken</NuxtLink
            >
          </li>
        </ul>
      </div>

      <div>
        <h3 class="font-semibold mb-2 light-text">Volg ons</h3>
        <div class="flex justify-center md:justify-start space-x-4 mb-4">
          <a href="https://facebook.com" target="_blank" class="social-link"
            ><i class="fab fa-facebook-f"></i
          ></a>
          <a href="https://twitter.com" target="_blank" class="social-link"
            ><i class="fab fa-x-twitter"></i
          ></a>
          <a href="https://instagram.com" target="_blank" class="social-link"
            ><i class="fab fa-instagram"></i
          ></a>
          <a href="https://linkedin.com" target="_blank" class="social-link"
            ><i class="fab fa-linkedin-in"></i
          ></a>
          <a href="https://tiktok.com" target="_blank" class="social-link"
            ><i class="fab fa-tiktok"></i
          ></a>
        </div>

        <h3 class="font-semibold mb-2 light-text">Nieuwsbrief</h3>
        <form class="flex flex-col items-center md:items-start gap-2">
          <input
            type="email"
            placeholder="E-mailadres"
            class="px-4 py-2 rounded w-60 border-2 border-white rounded-xl text-white placeholder-gray-500 sm:w-auto"
          />
          <button type="submit" class="cta sm:mt-2 md:mt-4 lg:mt-4 xl:mt-0">
            Inschrijven
          </button>
        </form>
      </div>
    </div>
  </footer>

  <div
    class="bg-gray-900 light-text justify-center text-md h-15 flex items-center mb-0"
  >
    Gemaakt met ❤️ door <span class="ml-1 font-semibold">Zschot</span
    ><span class="font-light font-italic">Media</span>
  </div>
</template>

<script setup>
const year = new Date().getFullYear();
</script>
