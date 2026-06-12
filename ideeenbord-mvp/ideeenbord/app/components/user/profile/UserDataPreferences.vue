<script setup lang="ts">
/*
  Optionele datavoorkeuren — extra, vrijwillige gegevens die merken helpen hun
  publiek beter te begrijpen (koopgedrag, huishouden, politieke voorkeur).
  Slaat op via hetzelfde profiel-update endpoint als UserProfileEdit.
*/
import { ref, watch } from "vue";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import { apiFetch } from "~/composables/adapter/useApi";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { dataProfileFields } from "~/constants/profileOptions";

const auth = useUserAuthStore();
const { triggerByKey } = useResponseDisplay();

const form = ref<Record<string, string>>({});
const saving = ref(false);

watch(
  () => auth.user,
  (u) => {
    if (!u) return;
    const next: Record<string, string> = {};
    for (const f of dataProfileFields) next[f.key] = (u as any)[f.key] ?? "";
    form.value = next;
  },
  { immediate: true }
);

async function save() {
  if (!auth.user) return;
  saving.value = true;
  try {
    const updated: any = await apiFetch(`/users/${auth.user.username}`, {
      method: "PATCH",
      body: { ...form.value },
    });
    auth.user = updated.user;
    triggerByKey("profile-updated");
  } catch {
    triggerByKey("profile-update-failed");
  } finally {
    saving.value = false;
  }
}
</script>

<template>
  <div class="mt-8">
    <div class="mb-5 rounded-2xl bg-[var(--color-bg)] p-4 flex gap-3">
      <i class="fa-solid fa-shield-halved text-[var(--color-brand)] text-lg mt-0.5"></i>
      <p class="text-sm text-gray-600">
        Deze gegevens zijn <strong>volledig optioneel</strong> en anoniem-geaggregeerd. Ze helpen merken
        hun doelgroep en koopgedrag beter te begrijpen — hoe meer je deelt, hoe waardevoller de inzichten.
      </p>
    </div>

    <form @submit.prevent="save" class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div v-for="f in dataProfileFields" :key="f.key">
          <label class="form-label flex items-center gap-2">
            <i class="fa-solid text-[var(--color-brand)]" :class="f.icon"></i>{{ f.label }}
          </label>
          <select v-model="form[f.key]" class="select-input">
            <option value="">Zeg ik liever niet</option>
            <option v-for="o in f.options" :key="o" :value="o">{{ o }}</option>
          </select>
        </div>
      </div>

      <button type="submit" class="btn btn--block" :disabled="saving">
        <i v-if="saving" class="fa-solid fa-spinner fa-spin"></i>
        <span>{{ saving ? "Opslaan…" : "Datavoorkeuren opslaan" }}</span>
      </button>
    </form>
  </div>
</template>
