import { useRuntimeConfig } from "#app";
import { useBrandOwnerAuthStore } from "~/store/brandOwnerAuth";

export async function brandOwnerApiFetch<T = any>(
  url: string,
  options: RequestInit = {}
): Promise<T> {
  const config = useRuntimeConfig();
  const baseUrl = config.public.apiBaseUrl; // Gebruik env
  const store = useBrandOwnerAuthStore();
  const token = store.token || localStorage.getItem("brand-owner-token");

  const headers = {
    ...(options.headers || {}),
    Authorization: `Bearer ${token}`,
    "Content-Type": "application/json",
  };

  const res = await fetch(`${baseUrl}/v1${url}`, {
    ...options,
    headers,
  });

  if (!res.ok) {
    const errText = await res.text();
    throw new Error(`API error: ${errText}`);
  }

  return await res.json();
}
