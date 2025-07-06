export interface MainQuestion {
  id: number;
  text: string;
  answers: string[];
  created_at: string;
  updated_at: string;
}

export interface MainQuestionResponse {
  id: number;
  user_id: number;
  brand_id: number;
  main_question_id: number;
  answer: string;
  created_at: string;
}
