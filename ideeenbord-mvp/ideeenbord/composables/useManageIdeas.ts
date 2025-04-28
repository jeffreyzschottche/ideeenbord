// ~/composables/useManageIdeas.ts
import { ref } from "vue";
import { brandOwnerApiFetch } from "~/composables/useBrandOwnerApi"; // we fixen API calls als owner
import { useResponseDisplay } from "~/composables/useResponseDisplay";

export function useManageIdeas(brandId: number) {
  const ideas = ref<any[]>([]);
  const error = ref<string | null>(null);
  const { trigger } = useResponseDisplay();

  async function fetchIdeas() {
    try {
      ideas.value = await brandOwnerApiFetch(`/brands/${brandId}/ideas`);
    } catch (err: any) {
      error.value = err?.message || "Kon ideeën niet laden.";
      trigger(`Fout bij ophalen ideeën: ${err}`, "error");
    }
  }

  async function updateIdeaStatus(ideaId: number, status: string) {
    try {
      await brandOwnerApiFetch(`/ideas/${ideaId}`, {
        method: "PATCH",
        body: JSON.stringify({ status }),
      });
      trigger("Status geüpdatet!", "success");
      await fetchIdeas(); // Refresh na update
    } catch (err: any) {
      error.value = err?.message || "Fout bij updaten status.";
      trigger(`Fout: ${err}`, "error");
    }
  }

  return { ideas, fetchIdeas, updateIdeaStatus, error };
}
