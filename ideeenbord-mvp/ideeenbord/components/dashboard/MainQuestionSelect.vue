<template>
  <div class="mt-8">
    <h2 class="text-xl font-bold mb-4">Kies een algemene vraag</h2>

    <!-- <div v-if="currentQuestion" class="mb-4 text-gray-700">
      <strong>Huidige vraag:</strong> {{ currentQuestion.text }}
    </div> -->

    <select v-model="selectedId" class="w-full border rounded p-2 mb-4">
      <option disabled value="" v-if="currentQuestion?.text">
        {{ currentQuestion.text }}
      </option>
      <option v-for="q in questions" :key="q.id" :value="q.id">
        {{ q.text }}
      </option>
    </select>

    <button
      @click="submit"
      :disabled="!selectedId"
      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
    >
      Vraag instellen
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useMainQuestions } from "~/composables/useMainQuestions";
// import { brandOwnerApiFetch } from "~/composables/useBrandOwnerApi";
import { useBrandOwnerAuthStore } from "~/store/brandOwnerAuth";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
const brandId = computed(() => brandOwnerAuth.owner?.brand?.id);
const { questions, fetchMainQuestions, setMainQuestionForBrand } =
  useMainQuestions();

const { trigger } = useResponseDisplay();
const brandOwnerAuth = useBrandOwnerAuthStore();
const selectedId = ref<string>("");

const currentQuestion = computed(() => {
  const id = brandOwnerAuth.owner?.brand?.main_question_id;
  if (!id) return null;
  return questions.value.find((q) => q.id === id) || null;
});

onMounted(fetchMainQuestions);
async function submit() {
  if (!selectedId.value) return;

  try {
    await setMainQuestionForBrand(brandId.value, Number(selectedId.value));
    trigger("Vraag succesvol opgeslagen!", "success");
  } catch (err: any) {
    trigger("Fout bij opslaan vraag: " + err.message, "error");
  }
}

// async function submit() {
//   if (!selectedId.value) return;

//   const selected = questions.value.find(
//     (q) => q.id === Number(selectedId.value)
//   );
//   if (!selected) return;

//   try {
//     await brandOwnerApiFetch(`/brands/${brandId.value}/main-questions`, {
//       method: "PATCH",
//       body: JSON.stringify({ main_question_id: selectedId.value }),
//     });
//     trigger("Vraag succesvol opgeslagen!", "success");
//   } catch (err: any) {
//     trigger("Fout bij opslaan vraag: " + err.message, "error");
//   }
// }
</script>
