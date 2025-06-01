import { ref } from "vue";
import {
  messages,
  type MessageKey,
  getCurrentLanguage,
} from "~/utils/messages";

const show = ref(false);
const message = ref("");
const type = ref<"success" | "error" | "warning">("success");

function trigger(
  newMessage: string,
  newType: "success" | "error" | "warning" = "success"
) {
  message.value = newMessage;
  type.value = newType;
  show.value = true;

  setTimeout(() => {
    show.value = false;
  }, 4000);
}

function triggerByKey(key: MessageKey, lang?: "nl" | "en") {
  const msg = messages[key];
  if (!msg) {
    console.warn(`⚠️ Message key "${key}" bestaat niet`);
    return;
  }

  const chosenLang = lang || getCurrentLanguage();

  message.value = msg.text[chosenLang] || msg.text["nl"];
  type.value = msg.type;
  show.value = true;

  setTimeout(() => (show.value = false), 4000);
}

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
