export function useQuizBuilder() {
  async function createQuiz(quiz: {
    brand_id: number;
    title: string;
    questions: {
      title: string;
      answers: { text: string; correct: boolean }[];
    }[];
  }) {
    const quiz_questions = quiz.questions.map((q, i) => ({
      id: i + 1,
      title: q.title,
    }));

    const quiz_answers = quiz.questions.map((q, i) => ({
      idQuestion: i + 1,
      answers: q.answers.reduce((acc, answer) => {
        acc[answer.text] = answer.correct;
        return acc;
      }, {} as Record<string, boolean>),
    }));

    return await brandOwnerApiFetch("/quizzes", {
      method: "POST",
      body: JSON.stringify({
        brand_id: quiz.brand_id,
        title: quiz.title,
        quiz_questions,
        quiz_answers,
      }),
    });
  }

  return { createQuiz };
}
