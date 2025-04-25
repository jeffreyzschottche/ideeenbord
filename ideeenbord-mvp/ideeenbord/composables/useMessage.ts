import { ref, onMounted } from "vue";
import { messageService } from "~/services/api/messageService";

export function useMessage() {
  const message = ref("");
  const error = ref("");

  onMounted(async () => {
    try {
      message.value = await messageService.getGreeting();
    } catch (err: any) {
      error.value = err.message;
    }
  });

  return { message, error };
}
