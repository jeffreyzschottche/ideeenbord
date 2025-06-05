import { ref, onMounted } from "vue";
import { messageService } from "~/services/api/messages/messageService";

export function useMessage() {
  // Holds the greeting message from the API
  const message = ref("");

  // Holds any error message in case of failure
  const error = ref("");

  // Fetch greeting message when the component is mounted
  onMounted(async () => {
    try {
      message.value = await messageService.getGreeting();
    } catch (err: any) {
      error.value = err.message;
    }
  });

  return { message, error };
}
