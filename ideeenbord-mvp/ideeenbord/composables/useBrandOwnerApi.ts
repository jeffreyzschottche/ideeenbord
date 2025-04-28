// ~/composables/useBrandOwnerApi.ts
import { useBrandOwnerAuthStore } from "~/store/brandOwnerAuth";

export async function brandOwnerApiFetch<T = any>(
  url: string,
  options: RequestInit = {}
): Promise<T> {
  const store = useBrandOwnerAuthStore();
  const token = store.token || localStorage.getItem("brand-owner-token");

  const headers = {
    ...(options.headers || {}),
    Authorization: `Bearer ${token}`,
    "Content-Type": "application/json",
  };

  const res = await fetch(`http://localhost:8000/api/v1${url}`, {
    ...options,
    headers,
  });

  if (!res.ok) {
    throw new Error("API error");
  }

  return await res.json();
}
