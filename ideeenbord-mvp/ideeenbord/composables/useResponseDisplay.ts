import { ref } from "vue";
import {
  messages,
  type MessageKey,
  getCurrentLanguage,
} from "~/utils/messages";

// State for displaying response messages
const show = ref(false);
const message = ref("");
const type = ref<"success" | "error" | "warning">("success");

// Display a custom message with a specific type (success, error, warning)
function trigger(
  newMessage: string,
  newType: "success" | "error" | "warning" = "success"
) {
  message.value = newMessage;
  type.value = newType;
  show.value = true;

  // Auto-hide message after 4 seconds
  setTimeout(() => {
    show.value = false;
  }, 4000);
}

// Display a predefined message using a key from the message catalog
function triggerByKey(key: MessageKey, lang?: "nl" | "en") {
  const msg = messages[key];
  if (!msg) {
    console.warn(`⚠️ Message key "${key}" does not exist`);
    return;
  }

  const chosenLang = lang || getCurrentLanguage();

  message.value = msg.text[chosenLang] || msg.text["nl"];
  type.value = msg.type;
  show.value = true;

  // Auto-hide message after 4 seconds
  setTimeout(() => (show.value = false), 4000);
}

// Composable export with state and trigger functions
export const useResponseDisplay: () => {
  show: typeof show;
  message: typeof message;
  type: typeof type;
  trigger: typeof trigger;
  triggerByKey: typeof triggerByKey;
} = () => ({
  show,
  message,
  type,
  trigger,
  triggerByKey,
});
