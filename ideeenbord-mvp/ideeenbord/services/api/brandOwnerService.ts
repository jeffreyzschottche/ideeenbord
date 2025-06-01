import { brandOwnerApiFetch } from "~/composables/useBrandOwnerApi";
import type { UpdateBrandOwnerForm, BrandOwner } from "~/types/brand-owner";
import type { Idea } from "~/types/idea";
import type { Quiz, NewQuizForm } from "~/types/quiz";
import type { MainQuestion } from "~/types/main-question";

export const brandOwnerService = {
  // Account info bijwerken
  async updateAccount(data: UpdateBrandOwnerForm) {
    return await brandOwnerApiFetch("/brand-owner/account", {
      method: "PATCH",
      body: JSON.stringify(data),
    });
  },

  // Ideeën ophalen en beheren
  async getIdeas(brandId: number): Promise<Idea[]> {
    return await brandOwnerApiFetch(`/brands/${brandId}/ideas`);
  },

  async updateIdeaStatus(id: number, status: string) {
    return await brandOwnerApiFetch(`/ideas/${id}`, {
      method: "PATCH",
      body: JSON.stringify({ status }),
    });
  },

  async pinIdea(id: number) {
    return await brandOwnerApiFetch(`/ideas/${id}/pin`, { method: "PATCH" });
  },

  async unpinIdea(id: number) {
    return await brandOwnerApiFetch(`/ideas/${id}/unpin`, { method: "PATCH" });
  },

  // Quiz functionaliteit
  async getQuizzes(brandId: number): Promise<Quiz[]> {
    return await brandOwnerApiFetch(`/brands/${brandId}/quizzes`);
  },

  async closeQuiz(quizId: number) {
    return await brandOwnerApiFetch(`/quizzes/${quizId}/close`, {
      method: "POST",
    });
  },

  async selectWinner(quizId: number, userId: number) {
    return await brandOwnerApiFetch(`/quizzes/${quizId}/select-winner`, {
      method: "POST",
      body: JSON.stringify({ winner_id: userId }),
    });
  },

  async getQuizParticipants(quizId: number) {
    return await brandOwnerApiFetch(`/quizzes/${quizId}/participants`);
  },

  // Quiz aanmaken
  async createQuiz(form: NewQuizForm) {
    const quiz_questions = form.questions.map((q, i) => ({
      id: i + 1,
      title: q.title,
    }));

    const quiz_answers = form.questions.map((q, i) => ({
      idQuestion: i + 1,
      answers: q.answers.reduce((acc, answer) => {
        acc[answer.text] = answer.correct;
        return acc;
      }, {} as Record<string, boolean>),
    }));

    return await brandOwnerApiFetch("/quizzes", {
      method: "POST",
      body: JSON.stringify({
        brand_id: form.brand_id,
        title: form.title,
        prize: form.prize,
        description: form.description,
        quiz_questions,
        quiz_answers,
      }),
    });
  },
  async updateBrand(brandId: number, updates: Record<string, any>) {
    return await brandOwnerApiFetch(`/brands/${brandId}`, {
      method: "PATCH",
      body: JSON.stringify(updates),
    });
  },
  async getMainQuestions(): Promise<MainQuestion[]> {
    return await brandOwnerApiFetch("/main-questions");
  },

  async setMainQuestionForBrand(brandId: number, questionId: number) {
    return await brandOwnerApiFetch(`/brands/${brandId}/main-questions`, {
      method: "PATCH",
      body: JSON.stringify({ main_question_id: questionId }),
    });
  },

  async getMainQuestionById(id: number | string): Promise<MainQuestion> {
    return await apiFetch(`/main-questions/${id}`);
  },
};
