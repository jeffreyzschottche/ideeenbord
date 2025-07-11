<script setup lang="ts">
/*
  This page allows users to request the creation of a new brand.
  The form includes brand details such as name, category, website, intro, email, and logo.
  On submit, the request is sent to the backend via the useBrand composable.
*/

import { ref } from "vue";
import type { RequestBrandForm } from "~/types/brand";
import { useBrand } from "~/composables/brand/useBrand";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";

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

const { requestBrand, error } = useBrand();
const { trigger, triggerByKey } = useResponseDisplay();

/* ---------- helper: extract first Laravel error message ---------- */
function firstLaravelMessage(raw: unknown): string | null {
  if (!raw) return null;

  if (typeof raw === "string") return raw;

  const obj = raw as { message?: string; errors?: Record<string, string[]> };

  if (obj.errors && Object.keys(obj.errors).length) {
    const firstField = Object.keys(obj.errors)[0];
    const firstMsg = obj.errors[firstField]?.[0];
    if (firstMsg) return firstMsg;
  }

  return obj.message ?? null;
}

// Submit the form to request a new brand
async function handleSubmit() {
  try {
    await requestBrand(form.value);
    triggerByKey("request-submitted");
  } catch (e) {
    const msg = firstLaravelMessage(error.value);

    if (msg === "profanity-detected") {
      triggerByKey(msg);
    } else if (msg) {
      trigger(msg, "error");
    } else {
      triggerByKey("request-failed");
    }
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
