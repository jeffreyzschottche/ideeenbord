<template>
  <teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70"
    >
      <form
        @submit.prevent="submit"
        class="space-y-4 w-full max-w-md bg-white p-6 rounded-lg"
      >
        <h3 class="text-xl font-bold text-center">Nieuw idee</h3>

        <input
          v-model="title"
          placeholder="Titel van je idee"
          class="w-full p-2 rounded border brandColorBorder"
          required
        />
        <textarea
          v-model="description"
          rows="4"
          placeholder="Beschrijving (optioneel)"
          class="w-full p-2 rounded border brandColorBorder"
        />

        <button type="submit" class="cta w-full">Plaats idee</button>

        <p
          class="text-center text-sm text-gray-500 mt-2 cursor-pointer"
          @click="emit('close')"
        >
          Sluiten
        </p>
      </form>
    </div>
  </teleport>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useIdeas } from "~/composables/ideas/useIdeas";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { useProfanity } from "~/composables/useProfanity";

/* ─ props & emits ─────────────────────────────────────────── */
const props = defineProps<{ brandId: number; open: boolean }>();
const emit = defineEmits(["close"]);

/* ─ helpers ───────────────────────────────────────────────── */
const { trigger, triggerByKey } = useResponseDisplay();
const { init: initProfanity, validate } = useProfanity();
const { submitIdea } = useIdeas(props.brandId);

/* ─ state ─────────────────────────────────────────────────── */
const title = ref("");
const description = ref("");

onMounted(() => initProfanity());

async function submit() {
  /* client-side filters */
  if (!validate(title.value, description.value)) return;

  try {
    await submitIdea(title.value, description.value);
    trigger("Idee succesvol geplaatst!", "success");
    emit("close");
  } catch {
    trigger("Plaatsen mislukt, probeer opnieuw.", "error");
  }
}
</script>

<style scoped>
.brandColorBorder {
  border: 2px solid var(--color-brand);
}
</style>
