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
const { triggerByKey } = useResponseDisplay();

// Submit the form to request a new brand
async function handleSubmit() {
  try {
    await requestBrand(form.value);
    triggerByKey("request-submitted");
  } catch (e: any) {
    console.log(e.messages);
    const rawErrors = e?.data?.errors || e?.response?._data?.errors;

    if (rawErrors) {
      const allMessages = Object.values(rawErrors).flat();

      if (allMessages.includes("profanity-detected")) {
        triggerByKey("profanity-detected");
      } else {
        triggerByKey("request-failed");
      }
    } else {
      triggerByKey("request-failed");
    }
  }
}
</script>

<template>
  <section class="register-section">
    <div class="register-container">
      <div class="register-card">
        <header>
          <h1 class="register-title">Nieuw merk aanvragen</h1>
          <p class="register-subtitle">
            Vraag een merk aan en laat het beoordelen door ons team.
          </p>
        </header>

        <form @submit.prevent="handleSubmit">
          <div class="form-row">
            <div class="form-field">
              <label for="title" class="form-label required-dot"
                >Merknaam</label
              >
              <input
                id="title"
                v-model="form.title"
                type="text"
                class="form-input"
                placeholder="Bijv. FEBO"
                required
              />
            </div>

            <div class="form-field">
              <label for="category" class="form-label required-dot"
                >Categorie</label
              >
              <input
                id="category"
                v-model="form.category"
                type="text"
                class="form-input"
                placeholder="Bijv. Fastfood, Retail…"
                required
              />
            </div>

            <div class="form-field full-width">
              <label for="websiteUrl" class="form-label">Website URL</label>
              <input
                id="websiteUrl"
                v-model="form.websiteUrl"
                type="url"
                class="form-input"
                placeholder="https://voorbeeld.nl"
              />
            </div>

            <div class="form-field full-width">
              <label for="intro" class="form-label">Introductie</label>
              <textarea
                id="intro"
                v-model="form.intro"
                class="form-input textarea-input"
                placeholder="Korte beschrijving van het merk"
              />
            </div>

            <div class="form-field full-width">
              <label for="introShort" class="form-label"
                >Korte Intro (max 160 tekens)</label
              >
              <input
                id="introShort"
                v-model="form.introShort"
                type="text"
                class="form-input"
                placeholder="One-liner over het merk"
              />
            </div>

            <div class="form-field full-width">
              <label for="email" class="form-label required-dot">E-mail</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="form-input"
                placeholder="contact@merk.nl"
                required
              />
            </div>

            <div class="form-field full-width">
              <label for="logo" class="form-label">Logo (optioneel)</label>
              <input
                id="logo"
                type="file"
                class="form-input"
                @change="e => form.logo = (e.target as HTMLInputElement).files?.[0] || null"
              />
              <p class="form-help">Ondersteund: jpg, png, gif (max 2MB).</p>
            </div>
          </div>

          <hr class="form-divider" />

          <div class="form-actions">
            <button type="submit" class="btn-submit">Verstuur aanvraag</button>
            <span class="form-note">
              Na goedkeuring wordt het merk zichtbaar op het platform.
            </span>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>
