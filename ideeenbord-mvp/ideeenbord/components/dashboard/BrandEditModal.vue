<template>
  <!-- 
    Modal for editing brand information.
    Shown only when `open` is true.
  -->
  <div
    v-if="open"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="bg-white w-full max-w-xl p-6 rounded shadow relative">
      <h2 class="text-2xl font-bold mb-4">Bewerk Merkgegevens</h2>

      <!-- Title input -->
      <label class="block font-semibold mb-1">Titel</label>
      <input v-model="form.title" class="w-full border p-2 mb-3 rounded" />

      <!-- Category input -->
      <label class="block font-semibold mb-1">Categorie</label>
      <input v-model="form.category" class="w-full border p-2 mb-3 rounded" />

      <!-- Website URL input -->
      <label class="block font-semibold mb-1">Website URL</label>
      <input
        v-model="form.website_url"
        class="w-full border p-2 mb-3 rounded"
      />

      <!-- Long intro input -->
      <label class="block font-semibold mb-1">Introductie</label>
      <textarea
        v-model="form.intro"
        class="w-full border p-2 mb-3 rounded"
      ></textarea>

      <!-- Short intro input -->
      <label class="block font-semibold mb-1">Korte Introductie</label>
      <input
        v-model="form.intro_short"
        class="w-full border p-2 mb-3 rounded"
      />

      <!-- Email input -->
      <label class="block font-semibold mb-1">Email</label>
      <input v-model="form.email" class="w-full border p-2 mb-3 rounded" />

      <!-- Subscription type input -->
      <label class="block font-semibold mb-1">Abonnement</label>
      <input
        v-model="form.subscription"
        class="w-full border p-2 mb-3 rounded"
      />

      <!-- JSON string input for socials -->
      <label class="block font-semibold mb-1">Socials (JSON)</label>
      <textarea
        v-model="form.socials"
        class="w-full border p-2 mb-4 rounded"
        rows="3"
      ></textarea>

      <!-- Action buttons -->
      <div class="flex justify-between">
        <button @click="closeModal" class="text-gray-500">Annuleer</button>
        <button
          @click="submitForm"
          class="bg-blue-600 text-white px-4 py-2 rounded"
        >
          Opslaan
        </button>
      </div>

      <!-- Close button in top-right -->
      <button class="absolute top-3 right-3 text-gray-600" @click="closeModal">
        ✖️
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
/*
  A modal component for editing brand information. 
  It preloads the form with existing brand data, allows editing,
  and handles submission including JSON parsing of the `socials` field.
*/

import { ref, watch } from "vue";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import { useBrand } from "~/composables/useBrand";
import type { Brand, SocialItem } from "~/types/brand";

// Props: whether the modal is open, and the brand to edit
const props = defineProps<{ open: boolean; brand: Brand }>();
const emit = defineEmits(["close", "updated"]);

const { triggerByKey } = useResponseDisplay();
const { updateBrand } = useBrand();

// Reactive form state
const form = ref({
  title: "",
  category: "",
  website_url: "",
  intro: "",
  intro_short: "",
  email: "",
  subscription: "",
  socials: "",
});

// Sync form values with brand prop whenever it changes
watch(
  () => props.brand,
  (newBrand) => {
    if (newBrand) {
      form.value = {
        title: newBrand.title || "",
        category: newBrand.category || "",
        website_url: newBrand.website_url || "",
        intro: newBrand.intro || "",
        intro_short: newBrand.intro_short || "",
        email: newBrand.email || "",
        subscription: newBrand.subscription || "",
        socials: "", // Reset input to raw JSON string
      };
    }
  },
  { immediate: true }
);

// Close the modal
function closeModal() {
  emit("close");
}

/*
  Handle form submission:
  - Parses the JSON in the `socials` field safely.
  - Sends the updated brand data via the updateBrand composable.
  - Triggers UI feedback and closes modal on success.
*/
async function submitForm() {
  let socialsParsed: SocialItem[] = [];

  try {
    // Parse the JSON string; fallback to empty array if invalid
    socialsParsed = JSON.parse(form.value.socials);
    if (!Array.isArray(socialsParsed)) socialsParsed = [];
  } catch (e) {
    socialsParsed = [];
  }

  try {
    const updates = {
      ...form.value,
      socials: socialsParsed, // Inject parsed array into update payload
    };
    await updateBrand(props.brand.id, updates);
    triggerByKey("brand-updated"); // Show success message
    emit("updated"); // Notify parent component
    closeModal(); // Close modal after save
  } catch (e) {
    triggerByKey("brand-update-failed"); // Show error message
  }
}
</script>
