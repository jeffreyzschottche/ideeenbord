// ~/composables/useApi.ts
import { useUserAuthStore } from "~/store/useUserAuthStore";

export async function apiFetch<T>(
  endpoint: string,
  options: {
    method?: "GET" | "POST" | "PUT" | "PATCH" | "DELETE";
    body?: any;
    params?: Record<string, any>;
    headers?: HeadersInit;
  } = {}
): Promise<T> {
  const config = useRuntimeConfig();
  const API_VERSION = "v1";
  const BASE_URL = `${config.public.apiBaseUrl}/${API_VERSION}`;
  const auth = useUserAuthStore();

  const url = new URL(`${BASE_URL}${endpoint}`);

  if (options.params) {
    Object.entries(options.params).forEach(([key, value]) => {
      url.searchParams.append(key, String(value));
    });
  }

  const headers: HeadersInit = {
    ...(options.headers || {}),
  };

  // Alleen 'application/json' header als GEEN FormData
  if (!(options.body instanceof FormData)) {
    headers["Content-Type"] = "application/json";
  }

  if (auth.token) {
    headers["Authorization"] = `Bearer ${auth.token}`;
  }

  try {
    return await $fetch(url.toString(), {
      method: options.method || "GET",
      headers,
      body: options.body,
    });
  } catch (err: any) {
    const statusCode = err?.statusCode || err?.response?.status;

    switch (statusCode) {
      case 401: // Unauthorized
        navigateTo("/login");
        break;
      case 403: // Forbidden
        break;
      case 404: // Not found
        navigateTo("/404");
        break;
      case 500: // Server error
        navigateTo("/");
        break;
      default:
    }

    throw err; // andere fouten nog steeds laten doorvloeien
  }
}
