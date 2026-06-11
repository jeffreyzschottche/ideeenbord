<script setup lang="ts">
/*
  Modal om merkgegevens te bewerken: tekstvelden, abonnement, socials (als
  losse rijen i.p.v. ruwe JSON) en logo-upload met potlood-knop.
*/

import { ref, watch, computed } from "vue";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { useBrand } from "~/composables/brand/useBrand";
import { brandOwnerService } from "~/services/api/brand/brandOwnerService";
import { storageBaseFromApiBase } from "~/utils/apiUrl";
import type { Brand, SocialItem } from "~/types/brand";

const props = defineProps<{ open: boolean; brand: Brand }>();
const emit = defineEmits(["close", "updated"]);

const { triggerByKey } = useResponseDisplay();
const { updateBrand } = useBrand();

const imageBase = storageBaseFromApiBase(
  useRuntimeConfig().public.apiBaseUrl as string
);

const saving = ref(false);
const uploadingLogo = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const logoPath = ref<string | null>(null);

const subscriptions = ["Brons", "Zilver", "Goud"];
const socialPlatforms = ["instagram", "facebook", "x", "linkedin", "tiktok", "youtube", "website"];

const form = ref({
  title: "",
  category: "",
  website_url: "",
  intro: "",
  intro_short: "",
  email: "",
  subscription: "",
});
const socialRows = ref<SocialItem[]>([]);

const logoUrl = computed(() => (logoPath.value ? `${imageBase}/${logoPath.value}` : null));

watch(
  () => props.brand,
  (b) => {
    if (!b) return;
    form.value = {
      title: b.title || "",
      category: b.category || "",
      website_url: b.website_url || "",
      intro: b.intro || "",
      intro_short: b.intro_short || "",
      email: b.email || "",
      subscription: b.subscription || "Brons",
    };
    socialRows.value = Array.isArray(b.socials)
      ? b.socials.map((s) => ({ platform: s.platform, url: s.url }))
      : [];
    logoPath.value = b.logo_path || null;
  },
  { immediate: true }
);

function addSocial() {
  socialRows.value.push({ platform: "instagram", url: "" });
}
function removeSocial(i: number) {
  socialRows.value.splice(i, 1);
}

function pickLogo() {
  fileInput.value?.click();
}

async function onLogoChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (!file) return;
  uploadingLogo.value = true;
  try {
    const res: any = await brandOwnerService.uploadBrandLogo(props.brand.id, file);
    logoPath.value = res?.brand?.logo_path ?? logoPath.value;
    triggerByKey("brand-updated");
    emit("updated");
  } catch {
    triggerByKey("brand-update-failed");
  } finally {
    uploadingLogo.value = false;
    if (fileInput.value) fileInput.value.value = "";
  }
}

function closeModal() {
  emit("close");
}

async function submitForm() {
  saving.value = true;
  try {
    const socials = socialRows.value
      .filter((s) => s.platform && s.url)
      .map((s) => ({ platform: s.platform, url: s.url }));
    await updateBrand(props.brand.id, { ...form.value, socials });
    triggerByKey("brand-updated");
    emit("updated");
    closeModal();
  } catch (e: any) {
    const rawErrors = e?.validationErrors;
    const allMessages = rawErrors ? Object.values(rawErrors).flat() : [];
    if (allMessages.includes("profanity-detected")) {
      triggerByKey("profanity-detected");
    } else {
      triggerByKey("brand-update-failed");
    }
  } finally {
    saving.value = false;
  }
}
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
      @click.self="closeModal"
    >
      <div class="bg-white w-full max-w-2xl max-h-[90vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h2 class="text-lg font-extrabold text-[var(--color-nav)]">Merk bewerken</h2>
          <button
            class="w-9 h-9 rounded-full hover:bg-gray-100 text-gray-500 flex items-center justify-center transition"
            @click="closeModal"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 overflow-y-auto space-y-5">
          <!-- Logo -->
          <div class="flex items-center gap-4">
            <button
              type="button"
              class="relative group w-20 h-20 shrink-0"
              title="Logo wijzigen"
              @click="pickLogo"
            >
              <img
                v-if="logoUrl"
                :src="logoUrl"
                alt="Merklogo"
                class="w-full h-full rounded-2xl object-contain bg-gray-50 border border-gray-200 p-2"
              />
              <span
                v-else
                class="w-full h-full rounded-2xl bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400"
              >
                <i class="fa-solid fa-image text-xl"></i>
              </span>
              <span
                class="absolute -bottom-1.5 -right-1.5 w-7 h-7 rounded-full bg-[var(--color-brand)] text-white flex items-center justify-center shadow ring-2 ring-white group-hover:scale-110 transition"
              >
                <i v-if="uploadingLogo" class="fa-solid fa-spinner fa-spin text-[11px]"></i>
                <i v-else class="fa-solid fa-pen text-[11px]"></i>
              </span>
            </button>
            <div>
              <p class="font-semibold text-[var(--color-nav)]">Logo</p>
              <p class="text-sm muted-text">PNG, JPG of WebP — klik op het potlood om te wijzigen.</p>
            </div>
            <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onLogoChange" />
          </div>

          <hr class="divider !my-0" />

          <!-- Velden -->
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="form-label">Titel</label>
              <input v-model="form.title" class="input" />
            </div>
            <div>
              <label class="form-label">Categorie</label>
              <input v-model="form.category" class="input" />
            </div>
            <div>
              <label class="form-label">Website URL</label>
              <input v-model="form.website_url" class="input" placeholder="https://…" />
            </div>
            <div>
              <label class="form-label">E-mail</label>
              <input v-model="form.email" type="email" class="input" />
            </div>
            <div>
              <label class="form-label">Abonnement</label>
              <select v-model="form.subscription" class="select-input">
                <option v-for="s in subscriptions" :key="s" :value="s">{{ s }}</option>
              </select>
            </div>
            <div>
              <label class="form-label">Korte introductie</label>
              <input v-model="form.intro_short" class="input" maxlength="160" />
            </div>
          </div>

          <div>
            <label class="form-label">Introductie</label>
            <textarea v-model="form.intro" class="textarea-input"></textarea>
          </div>

          <!-- Socials -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="form-label !mb-0">Socials</label>
              <button type="button" class="btn-link text-sm" @click="addSocial">
                <i class="fa-solid fa-plus mr-1"></i> Toevoegen
              </button>
            </div>
            <p v-if="!socialRows.length" class="text-sm muted-text">Nog geen socials toegevoegd.</p>
            <div v-for="(s, i) in socialRows" :key="i" class="flex gap-2 mb-2">
              <select v-model="s.platform" class="select-input" style="max-width: 10rem">
                <option v-for="p in socialPlatforms" :key="p" :value="p">{{ p }}</option>
              </select>
              <input v-model="s.url" class="input" placeholder="https://…" />
              <button
                type="button"
                class="w-10 shrink-0 rounded-xl border border-gray-200 text-gray-400 hover:text-red-600 hover:border-red-200 transition"
                @click="removeSocial(i)"
              >
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50">
          <button class="btn btn--ghost btn--sm" @click="closeModal">Annuleer</button>
          <button class="btn btn--sm" :disabled="saving" @click="submitForm">
            <i v-if="saving" class="fa-solid fa-spinner fa-spin"></i>
            <span>{{ saving ? "Opslaan…" : "Opslaan" }}</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
