export interface Quiz {
  id: number;
  brand_id: number;
  title: string;
  status: string;
  description: string;
  prize: string;
  quiz_questions: { id: number; title: string }[];
  quiz_answers: QuizAnswerMap[];
  participants?: { user_id: number }[];
  winner_id?: number;
}
export interface QuizAnswerOption {
  text: string;
  correct: boolean;
}

export interface QuizQuestionInput {
  title: string;
  answers: QuizAnswerOption[];
}

export interface NewQuizForm {
  brand_id: number;
  title: string;
  description: string;
  prize: string;
  questions: QuizQuestionInput[];
}

export interface QuizParticipant {
  user_id: number;
  name: string;
}

export interface QuizWithParticipants extends Quiz {
  participants: QuizParticipant[];
}

export interface QuizAnswerMap {
  idQuestion: number;
  answers: Record<string, boolean>; // string = antwoordtekst
}
