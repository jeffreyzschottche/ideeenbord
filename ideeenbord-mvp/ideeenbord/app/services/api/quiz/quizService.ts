/**
 * Service for managing brand quizzes: creating, fetching, closing,
 * retrieving participants, and selecting winners.
 */

import { brandOwnerApiFetch } from "~/composables/brand/useBrandOwnerApi";
import type { NewQuizForm, Quiz } from "~/types/quiz";

export const quizService = {
  // Create a new quiz with structured questions and answers
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

  // Get all quizzes created by a brand
  async getQuizzes(brandId: number): Promise<Quiz[]> {
    return await brandOwnerApiFetch(`/brands/${brandId}/quizzes`);
  },

  // Get all participants of a specific quiz
  async getParticipants(quizId: number) {
    return await brandOwnerApiFetch(`/quizzes/${quizId}/participants`);
  },

  // Close a quiz so users can no longer submit answers
  async closeQuiz(quizId: number) {
    return await brandOwnerApiFetch(`/quizzes/${quizId}/close`, {
      method: "POST",
    });
  },

  // Select a specific user as the winner of a quiz
  async selectWinner(quizId: number, userId: number) {
    return await brandOwnerApiFetch(`/quizzes/${quizId}/select-winner`, {
      method: "POST",
      body: JSON.stringify({ winner_id: userId }),
    });
  },
};
