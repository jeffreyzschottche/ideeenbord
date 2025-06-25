import { apiFetch } from "~/composables/adapter/useApi";

type CmsFieldResponse = { key: string; value: string };

export async function getCmsContent(
  slug: string
): Promise<Record<string, string>> {
  const config = useRuntimeConfig();
  const backendBase = config.public.apiBaseUrl.replace("/api", "");

  const res = await apiFetch<{ data: CmsFieldResponse[] }>(`/content/${slug}`);

  const content: Record<string, string> = {};
  for (const field of res.data) {
    const isImage =
      typeof field.value === "string" && field.value.startsWith("/storage/");
    content[field.key] = isImage ? `${backendBase}${field.value}` : field.value;
  }

  return content;
}
