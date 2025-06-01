<script setup lang="ts">
import { ref } from "vue";
import type { RequestBrandForm } from "~/types/brand";
import { useRequestBrand } from "~/composables/useBrand";
import { useResponseDisplay } from "~/composables/useResponseDisplay";

const form = ref<RequestBrandForm>({
  title: "",
  category: "",
  websiteUrl: "",
  intro: "",
  introShort: "",
  email: "",
  logo: null,
  socials: [],
});

const { requestBrand, error } = useRequestBrand();
const { triggerByKey } = useResponseDisplay(); // ✅ vervangen

async function handleSubmit() {
  try {
    await requestBrand(form.value);
    triggerByKey("request-submitted");
  } catch (e) {
    triggerByKey("request-failed");
  }
}
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <input v-model="form.title" placeholder="Merknaam" required />
    <input v-model="form.category" placeholder="Categorie" required />
    <input v-model="form.websiteUrl" placeholder="Website URL" />

    <textarea v-model="form.intro" placeholder="Introductie" />
    <input
      v-model="form.introShort"
      placeholder="Korte Intro (max 160 tekens)"
    />
    <input v-model="form.email" type="email" placeholder="Email" required />

    <input
      type="file"
      @change="e => form.logo = (e.target as HTMLInputElement).files?.[0] || null"
    />

    <button type="submit">Verstuur aanvraag</button>
  </form>
</template>
