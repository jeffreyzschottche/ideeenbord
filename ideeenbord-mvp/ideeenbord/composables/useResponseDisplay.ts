import { ref } from "vue";

// Alleen één globale instantie
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

// LET OP: geen functie returnen, maar gewoon direct exporteren
export const useResponseDisplay = () => ({
  show,
  message,
  type,
  trigger,
});
