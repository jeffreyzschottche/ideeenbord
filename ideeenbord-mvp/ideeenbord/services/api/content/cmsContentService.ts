import { apiFetch } from "~/composables/adapter/useApi";

type CmsFieldResponse = { key: string; value: string };

function normalizeCmsImageValue(value: string, backendBase: string): string {
  if (value.startsWith("/storage/")) {
    return `${backendBase}${value}`;
  }

  const storagePathMatch = value.match(/\/storage\/[^"')\s]+/);
  if (storagePathMatch) {
    return `${backendBase}${storagePathMatch[0]}`;
  }

  return value;
}

export async function getCmsContent(
  slug: string
): Promise<Record<string, string>> {
  const config = useRuntimeConfig();
  const backendBase = config.public.apiBaseUrl.replace("/api", "");

  const res = await apiFetch<{ data: CmsFieldResponse[] }>(`/content/${slug}`);

  const content: Record<string, string> = {};
  for (const field of res.data) {
    content[field.key] =
      typeof field.value === "string"
        ? normalizeCmsImageValue(field.value, backendBase)
        : field.value;
  }

  return content;
}
