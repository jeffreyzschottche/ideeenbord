<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
  <ResponseDisplay />
</template>
<script setup lang="ts">
import { useUserAuthStore } from "~/store/useUserAuthStore";
import ResponseDisplay from "~/components/notifications/ResponseDisplay.vue";
import { useBrandOwnerAuth } from "~/composables/brand/useBrandOwnerAuth";
import { useJsonLd, useCanonical } from "~/composables/useSeo";
useBrandOwnerAuth().initAuth();

useUserAuthStore().initAuth();

// Site-wide structured data: Organization + WebSite (with search action).
const siteUrl = useCanonical("/");
useJsonLd([
  {
    "@type": "Organization",
    name: "Ideeënbord",
    url: siteUrl,
    logo: `${siteUrl.replace(/\/$/, "")}/favicon.png`,
    description:
      "Platform waar consumenten ideeën, wensen en verbeterpunten delen met merken.",
  },
  {
    "@type": "WebSite",
    name: "Ideeënbord",
    url: siteUrl,
    inLanguage: "nl-NL",
    potentialAction: {
      "@type": "SearchAction",
      target: `${siteUrl.replace(/\/$/, "")}/brands?q={search_term_string}`,
      "query-input": "required name=search_term_string",
    },
  },
]);
</script>
