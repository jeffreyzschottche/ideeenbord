// ~/services/api/quizService.ts
import { brandOwnerApiFetch } from "~/composables/useBrandOwnerApi";
import type { NewQuizForm, Quiz } from "~/types/quiz";

export const quizService = {
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

  async getQuizzes(brandId: number): Promise<Quiz[]> {
    return await brandOwnerApiFetch(`/brands/${brandId}/quizzes`);
  },

  async getParticipants(quizId: number) {
    return await brandOwnerApiFetch(`/quizzes/${quizId}/participants`);
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
};
