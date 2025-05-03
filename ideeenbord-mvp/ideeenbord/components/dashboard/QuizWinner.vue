<template>
  <div class="mt-10">
    <h3 class="text-xl font-bold mb-4">Kies een winnaar uit quiz deelnemers</h3>

    <div v-if="participants.length === 0">
      <p>Geen deelnemers gevonden.</p>
    </div>

    <ul v-else class="mb-4">
      <li
        v-for="participant in participants"
        :key="participant.id"
        class="mb-2 p-2 border rounded flex justify-between items-center"
      >
        <span>{{ participant.name || `User ${participant.id}` }}</span>
        <button
          @click="selectWinner(participant.id)"
          class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600"
        >
          Selecteer als winnaar
        </button>
      </li>
    </ul>

    <p v-if="winnerId" class="text-green-600 font-semibold">
      Geselecteerde winnaar ID: {{ winnerId }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { brandOwnerApiFetch } from "~/composables/useBrandOwnerApi";
import { useBrandOwnerAuthStore } from "~/store/brandOwnerAuth";
import { useResponseDisplay } from "~/composables/useResponseDisplay";

const participants = ref<any[]>([]);
const winnerId = ref<number | null>(null);
const brandId = useBrandOwnerAuthStore().owner?.brand?.id;
const { trigger } = useResponseDisplay();

onMounted(async () => {
  if (!brandId) return;
  try {
    participants.value = await brandOwnerApiFetch(
      `/brands/${brandId}/quiz/participants`
    );
  } catch (e) {
    trigger("Fout bij ophalen deelnemers", "error");
  }
});

async function selectWinner(id: number) {
  try {
    await brandOwnerApiFetch(`/brands/${brandId}/quiz/select-winner`, {
      method: "POST",
      body: JSON.stringify({ winner_id: id }),
    });
    winnerId.value = id;
    trigger("Winnaar gekozen!", "success");
  } catch (err: any) {
    trigger("Fout bij kiezen winnaar", "error");
  }
}
</script>
