export interface UserNotification {
  type: "quiz" | "idea" | string;
  message: string;
  timestamp: string;
}
