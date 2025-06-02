// ~/utils/messages.ts
export function getCurrentLanguage(): "nl" | "en" {
  if (typeof window === "undefined") return "nl";

  const fromStorage = localStorage.getItem("language");
  const fromHtml = document.documentElement.lang;
  const lang = fromStorage || fromHtml || "nl";

  return lang.startsWith("en") ? "en" : "nl";
}

export type MessageKey =
  | "login-approved"
  | "login-failed"
  | "idea-posted"
  | "idea-failed"
  | "question-already-answered"
  | "question-saved"
  | "question-login-required"
  | "question-save-failed"
  | "quiz-load-failed"
  | "quiz-submitted"
  | "quiz-submit-failed"
  | "unauthorized"
  | "account-updated"
  | "account-update-failed"
  | "brand-updated"
  | "brand-update-failed"
  | "main-question-set"
  | "main-question-failed"
  | "quiz-created"
  | "quiz-create-failed"
  | "quiz-closed"
  | "quiz-close-failed"
  | "quiz-winner-selected"
  | "quiz-winner-failed"
  | "profile-updated"
  | "profile-update-failed"
  | "admin-owner-verified"
  | "admin-owner-verification-failed"
  | "admin-fetch-failed"
  | "brand-owner-login-success"
  | "brand-owner-login-failed"
  | "ideas-fetch-failed"
  | "idea-liked"
  | "idea-like-failed"
  | "idea-disliked"
  | "idea-dislike-failed"
  | "manage-ideas-fetch-failed"
  | "idea-status-updated"
  | "idea-status-update-failed"
  | "idea-pinned"
  | "idea-pin-failed"
  | "idea-unpinned"
  | "idea-unpin-failed"
  | "register-success"
  | "register-failed"
  | "brand-rating-failed"
  | "brand-rating-saved"
  | "brand-already-rated"
  | "brand-load-failed"
  | "claim-load-failed"
  | "claim-submitted"
  | "claim-failed"
  | "request-submitted"
  | "request-failed"
  | "server-error";

type MessageType = "success" | "error" | "warning";

export const messages: Record<
  MessageKey,
  {
    type: MessageType;
    text: {
      nl: string;
      en: string;
    };
  }
> = {
  "login-approved": {
    type: "success",
    text: {
      nl: "Je bent succesvol ingelogd!",
      en: "You have logged in successfully!",
    },
  },
  "login-failed": {
    type: "error",
    text: {
      nl: "Inloggen mislukt. Controleer je gegevens.",
      en: "Login failed. Check your credentials.",
    },
  },
  "idea-posted": {
    type: "success",
    text: {
      nl: "Je idee is succesvol geplaatst!",
      en: "Your idea has been posted!",
    },
  },
  "idea-failed": {
    type: "error",
    text: {
      nl: "Je idee kon niet worden opgeslagen.",
      en: "Failed to save your idea.",
    },
  },
  "question-already-answered": {
    type: "warning",
    text: {
      nl: "Je hebt deze vraag al beantwoord.",
      en: "You already answered this question.",
    },
  },
  "question-saved": {
    type: "success",
    text: {
      nl: "Je antwoord is opgeslagen!",
      en: "Your answer has been saved!",
    },
  },
  "question-login-required": {
    type: "warning",
    text: {
      nl: "Log in om te reageren.",
      en: "Log in to respond.",
    },
  },
  "question-save-failed": {
    type: "error",
    text: {
      nl: "Fout bij opslaan van je antwoord.",
      en: "Failed to save your answer.",
    },
  },
  unauthorized: {
    type: "error",
    text: {
      nl: "Niet geautoriseerd. Log opnieuw in.",
      en: "Unauthorized. Please log in again.",
    },
  },
  "server-error": {
    type: "error",
    text: {
      nl: "Er is een serverfout opgetreden.",
      en: "A server error occurred.",
    },
  },
  "quiz-load-failed": {
    type: "error",
    text: {
      nl: "Quiz laden mislukt.",
      en: "Failed to load quiz.",
    },
  },
  "quiz-submitted": {
    type: "success",
    text: {
      nl: "Quiz succesvol verzonden!",
      en: "Quiz submitted successfully!",
    },
  },
  "quiz-submit-failed": {
    type: "error",
    text: {
      nl: "Verzenden van quiz mislukt.",
      en: "Failed to submit quiz.",
    },
  },
  "account-updated": {
    type: "success",
    text: {
      nl: "Gegevens bijgewerkt!",
      en: "Account details updated!",
    },
  },
  "account-update-failed": {
    type: "error",
    text: {
      nl: "Fout bij bijwerken van gegevens.",
      en: "Failed to update account details.",
    },
  },
  "brand-updated": {
    type: "success",
    text: {
      nl: "Merkgegevens bijgewerkt!",
      en: "Brand details updated!",
    },
  },
  "brand-update-failed": {
    type: "error",
    text: {
      nl: "Fout bij bijwerken merkgegevens.",
      en: "Failed to update brand details.",
    },
  },
  "main-question-set": {
    type: "success",
    text: {
      nl: "Vraag succesvol opgeslagen!",
      en: "Question successfully saved!",
    },
  },
  "main-question-failed": {
    type: "error",
    text: {
      nl: "Fout bij opslaan van de vraag.",
      en: "Failed to save the question.",
    },
  },
  "quiz-created": {
    type: "success",
    text: {
      nl: "Quiz succesvol aangemaakt!",
      en: "Quiz created successfully!",
    },
  },
  "quiz-create-failed": {
    type: "error",
    text: {
      nl: "Fout bij opslaan quiz.",
      en: "Failed to save the quiz.",
    },
  },
  "quiz-closed": {
    type: "success",
    text: {
      nl: "Quiz gesloten!",
      en: "Quiz closed!",
    },
  },
  "quiz-close-failed": {
    type: "error",
    text: {
      nl: "Quiz sluiten mislukt.",
      en: "Failed to close quiz.",
    },
  },
  "quiz-winner-selected": {
    type: "success",
    text: {
      nl: "Winnaar gekozen!",
      en: "Winner selected!",
    },
  },
  "quiz-winner-failed": {
    type: "error",
    text: {
      nl: "Fout bij kiezen winnaar.",
      en: "Failed to select winner.",
    },
  },
  "profile-updated": {
    type: "success",
    text: {
      nl: "Je profiel is bijgewerkt.",
      en: "Your profile has been updated.",
    },
  },
  "profile-update-failed": {
    type: "error",
    text: {
      nl: "Bijwerken van je profiel is mislukt.",
      en: "Failed to update your profile.",
    },
  },
  "admin-owner-verified": {
    type: "success",
    text: {
      nl: "Eigenaar succesvol geverifieerd!",
      en: "Owner successfully verified!",
    },
  },
  "admin-owner-verification-failed": {
    type: "error",
    text: {
      nl: "Verifiëren van eigenaar mislukt.",
      en: "Owner verification failed.",
    },
  },
  "admin-fetch-failed": {
    type: "error",
    text: {
      nl: "Fout bij ophalen van eigenaren.",
      en: "Failed to fetch owners.",
    },
  },
  "brand-owner-login-success": {
    type: "success",
    text: {
      nl: "Ingelogd als eigenaar!",
      en: "Logged in as brand owner!",
    },
  },
  "brand-owner-login-failed": {
    type: "error",
    text: {
      nl: "Inloggen als eigenaar mislukt.",
      en: "Brand owner login failed.",
    },
  },
  "ideas-fetch-failed": {
    type: "error",
    text: {
      nl: "Fout bij laden van ideeën.",
      en: "Error fetching ideas.",
    },
  },
  "idea-liked": {
    type: "success",
    text: {
      nl: "Je hebt het idee geliket! ✅",
      en: "You liked the idea! ✅",
    },
  },
  "idea-like-failed": {
    type: "error",
    text: {
      nl: "Je hebt je mening al gegeven bij dit idee.",
      en: "Already gave your opinion this idea.",
    },
  },
  "idea-disliked": {
    type: "warning",
    text: {
      nl: "Je hebt het idee gedisliket! ❌",
      en: "You disliked the idea! ❌",
    },
  },
  "idea-dislike-failed": {
    type: "error",
    text: {
      nl: "Fout bij disliken van idee.",
      en: "Error disliking idea.",
    },
  },
  "manage-ideas-fetch-failed": {
    type: "error",
    text: {
      nl: "Fout bij laden van ideeën.",
      en: "Error fetching ideas.",
    },
  },
  "idea-status-updated": {
    type: "success",
    text: {
      nl: "Status geüpdatet!",
      en: "Status updated!",
    },
  },
  "idea-status-update-failed": {
    type: "error",
    text: {
      nl: "Fout bij updaten van status.",
      en: "Error updating status.",
    },
  },
  "idea-pinned": {
    type: "success",
    text: {
      nl: "Idee vastgezet!",
      en: "Idea pinned!",
    },
  },
  "idea-pin-failed": {
    type: "error",
    text: {
      nl: "Fout bij vastzetten van idee.",
      en: "Error pinning idea.",
    },
  },
  "idea-unpinned": {
    type: "success",
    text: {
      nl: "Idee losgemaakt!",
      en: "Idea unpinned!",
    },
  },
  "idea-unpin-failed": {
    type: "error",
    text: {
      nl: "Fout bij losmaken van idee.",
      en: "Error unpinning idea.",
    },
  },
  "register-success": {
    type: "success",
    text: {
      nl: "Registratie gelukt! Bevestig je e-mailadres via de mail.",
      en: "Registration successful! Please confirm your email.",
    },
  },
  "register-failed": {
    type: "error",
    text: {
      nl: "Registratie mislukt. Probeer het opnieuw.",
      en: "Registration failed. Please try again.",
    },
  },
  "brand-load-failed": {
    type: "error",
    text: {
      nl: "Fout bij ophalen van het merk.",
      en: "Failed to load brand data.",
    },
  },
  "brand-already-rated": {
    type: "warning",
    text: {
      nl: "Je hebt al een beoordeling gegeven!",
      en: "You already rated this brand!",
    },
  },
  "brand-rating-saved": {
    type: "success",
    text: {
      nl: "Je beoordeling is succesvol geplaatst!",
      en: "Your rating was submitted successfully!",
    },
  },
  "brand-rating-failed": {
    type: "error",
    text: {
      nl: "Beoordeling opslaan mislukt.",
      en: "Failed to submit rating.",
    },
  },
  "claim-load-failed": {
    type: "error",
    text: {
      nl: "Merken ophalen mislukt.",
      en: "Failed to load brands.",
    },
  },
  "claim-submitted": {
    type: "success",
    text: {
      nl: "Merkclaim succesvol verstuurd!",
      en: "Brand claim submitted successfully!",
    },
  },
  "claim-failed": {
    type: "error",
    text: {
      nl: "Merkclaim mislukt.",
      en: "Brand claim failed.",
    },
  },
  "request-submitted": {
    type: "success",
    text: {
      nl: "Merk succesvol aangevraagd!",
      en: "Brand request submitted successfully!",
    },
  },
  "request-failed": {
    type: "error",
    text: {
      nl: "Aanvraag mislukt.",
      en: "Brand request failed.",
    },
  },
};
