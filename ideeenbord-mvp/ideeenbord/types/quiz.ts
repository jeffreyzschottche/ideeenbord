export interface Quiz {
  id: number;
  title: string;
  status: string;
  description: string;
  prize: string;
  quiz_questions: { id: number; title: string }[];
  quiz_answers: { idQuestion: number; answers: Record<string, boolean> }[];
  participants?: { user_id: number }[];
}
