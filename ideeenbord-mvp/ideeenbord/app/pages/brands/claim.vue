<script setup lang="ts">
/*
  Claim page: toont alleen het merk uit ?brand_id=... als dat claimbaar is.
  - Geen brand_id in URL  -> geen opties in select.
  - Wel brand_id maar niet claimbaar -> geen opties.
  - Wel brand_id en claimbaar -> precies die ene optie (en voorgevuld).
*/

import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import type { ClaimForm } from "~/types/brand";
import { useBrand } from "~/composables/brand/useBrand";
import { apiFetch } from "~/composables/adapter/useApi";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";

// Form data
const form = ref<ClaimForm>({
  brandId: "",
  name: "",
  email: "",
  phone: "",
  url: "",
  password: "",
});

const { claimBrand } = useBrand();
const brands = ref<{ id: number; title: string }[]>([]);
const { triggerByKey, trigger } = useResponseDisplay();
const route = useRoute();

// Helper: lees brand_id uit query (als nummer)
function getBrandIdFromQuery(): number | null {
  const q = route.query.brand_id;
  const raw = Array.isArray(q) ? q[0] : q;
  const n = Number(raw);
  return Number.isFinite(n) && n > 0 ? n : null;
}

// Fetch alleen de gevraagde brand (als die claimbaar is)
onMounted(async () => {
  const wantedId = getBrandIdFromQuery();

  if (!wantedId) {
    brands.value = []; // geen brand_id -> niets tonen
    return;
  }

  try {
    // Probeer server-side te filteren: accepted=1, verified=0, id = wantedId
    // Als je index niet op id filtert, filteren we hieronder nogmaals client-side.
    const data = await apiFetch<
      Array<{
        id: number;
        title: string;
        accepted?: boolean;
        verified?: boolean;
      }>
    >("/brands?accepted=1", {
      params: { verified: 0, id: wantedId },
    });

    const list = Array.isArray(data) ? data : [];
    // Extra client-side safety: alleen exact die id én (voor zover meegekomen) claimbaar
    const filtered = list.filter(
      (b) =>
        b.id === wantedId &&
        (b.accepted === undefined || b.accepted === true) &&
        (b.verified === undefined || b.verified === false)
    );

    brands.value = filtered;

    // Als precies 1 brand gevonden -> preselecteer
    if (brands.value.length === 1) {
      form.value.brandId = String(brands.value[0].id);
    } else {
      form.value.brandId = "";
    }
  } catch {
    triggerByKey("claim-load-failed");
    brands.value = [];
  }
});

// Submit
async function handleSubmit() {
  try {
    await claimBrand(form.value);
    triggerByKey("claim-submitted");
  } catch (e: any) {
    const rawErrors = e?.data?.errors || e?.response?._data?.errors;
    if (rawErrors) {
      const allMessages = Object.values(rawErrors).flat();
      if (allMessages.includes("profanity-detected")) {
        triggerByKey("profanity-detected");
      } else if (allMessages.includes("The email has already been taken.")) {
        trigger("Deze email is al in gebruik voor een merk.", "error");
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
  <form @submit.prevent="handleSubmit" class="page-block">
    <!-- Merk selectie: toont alleen het merk uit ?brand_id=..., of niets -->
    <div class="form-field">
      <label class="form-label required-dot">Merk</label>
      <select
        v-model="form.brandId"
        required
        class="select-input"
        :disabled="brands.length === 0"
      >
        <option value="" disabled>
          {{ brands.length ? "Kies het merk" : "Geen claimbaar merk gevonden" }}
        </option>
        <option v-for="brand in brands" :value="brand.id" :key="brand.id">
          {{ brand.title }}
        </option>
      </select>
      <p v-if="!brands.length" class="form-help">
        Voeg <code>?brand_id=123</code> toe aan de URL om een merk te claimen.
      </p>
    </div>

    <hr class="form-divider" />

    <div class="form-row">
      <div class="form-field">
        <label class="form-label required-dot">Naam</label>
        <input
          v-model="form.name"
          placeholder="Naam"
          required
          class="form-input"
        />
      </div>

      <div class="form-field">
        <label class="form-label required-dot">Email</label>
        <input
          v-model="form.email"
          type="email"
          placeholder="Email"
          required
          class="form-input"
        />
      </div>

      <div class="form-field">
        <label class="form-label">Telefoonnummer</label>
        <input
          v-model="form.phone"
          placeholder="Telefoonnummer"
          class="form-input"
        />
      </div>

      <div class="form-field">
        <label class="form-label">Website</label>
        <input v-model="form.url" placeholder="Website" class="form-input" />
      </div>

      <div class="form-field">
        <label class="form-label required-dot">Wachtwoord</label>
        <input
          v-model="form.password"
          type="password"
          placeholder="Wachtwoord"
          required
          class="form-input"
        />
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn--block" :disabled="!form.brandId">
        Merk claimen
      </button>
      <p class="form-note">
        Je aanvraag wordt beoordeeld door een administrator.
      </p>
    </div>
  </form>
</template>
