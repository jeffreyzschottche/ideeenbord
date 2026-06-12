import DOMPurify from "isomorphic-dompurify";

/**
 * Sanitize HTML before rendering met v-html. Werkt zowel server- als
 * client-side (isomorphic). Strikt nodig omdat de auth-token JS-leesbaar is:
 * ongesanitizede HTML in v-html = stored-XSS → tokendiefstal.
 */
export function sanitizeHtml(html: string | null | undefined): string {
  if (!html) return "";
  return DOMPurify.sanitize(html, {
    USE_PROFILES: { html: true },
    // Geen inline event-handlers of javascript:-urls toestaan.
    FORBID_ATTR: ["style"],
  });
}
