export type IdeaStatus = "pending" | "rejected" | "in_progress" | "completed";

export interface Idea {
  id: number;
  brand_id: number;
  user_id: number;
  title: string;
  description: string;
  likes: number;
  dislikes: number;
  is_pinned: boolean;
  status: IdeaStatus;
  created_at: string;
  updated_at: string;
}

export interface IdeaNotification {
  type: "idea_status" | "idea_like";
  message: string;
  timestamp: string;
}

export interface NewIdeaForm {
  brand_id: number;
  title: string;
  description: string;
}
