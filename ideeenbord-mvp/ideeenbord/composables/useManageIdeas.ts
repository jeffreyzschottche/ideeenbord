import type { Idea } from "~/types/idea";

export function useManageIdeas(brandId: number) {
  type EditableIdea = Idea & { newStatus?: string };
  const ideas = ref<EditableIdea[]>([]);
  const error = ref<string | null>(null);
  const { trigger } = useResponseDisplay();

  async function fetchIdeas() {
    try {
      ideas.value = await brandOwnerApiFetch<Idea[]>(
        `/brands/${brandId}/ideas`
      );
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
      await fetchIdeas();
    } catch (err: any) {
      error.value = err?.message || "Fout bij updaten status.";
      trigger(`Fout: ${err}`, "error");
    }
  }

  async function pinIdea(ideaId: number) {
    try {
      await brandOwnerApiFetch(`/ideas/${ideaId}/pin`, {
        method: "PATCH",
      });
      trigger("Idee vastgezet!", "success");
      await fetchIdeas();
    } catch (err: any) {
      error.value = err?.message || "Fout bij vastzetten.";
      trigger(`Fout: ${err}`, "error");
    }
  }

  async function unpinIdea(ideaId: number) {
    try {
      await brandOwnerApiFetch(`/ideas/${ideaId}/unpin`, {
        method: "PATCH",
      });
      trigger("Idee losgemaakt!", "success");
      await fetchIdeas();
    } catch (err: any) {
      error.value = err?.message || "Fout bij losmaken.";
      trigger(`Fout: ${err}`, "error");
    }
  }

  return { ideas, fetchIdeas, updateIdeaStatus, pinIdea, unpinIdea, error };
}
