// ~/composables/useApi.ts
import { useUserAuthStore } from "~/store/useUserAuthStore";

/**
 * Utility for making API requests with optional authentication and automatic error handling.
 * Automatically applies base API URL, token, headers, query params, and redirects on common HTTP errors.
 */
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

  // Append any query parameters to the request URL
  if (options.params) {
    Object.entries(options.params).forEach(([key, value]) => {
      url.searchParams.append(key, String(value));
    });
  }

  const headers: HeadersInit = {
    ...(options.headers || {}),
  };

  // Add JSON content-type header unless the body is FormData
  if (!(options.body instanceof FormData)) {
    headers["Content-Type"] = "application/json";
  }

  // Add Authorization header if the user is authenticated
  if (auth.token) {
    headers["Authorization"] = `Bearer ${auth.token}`;
  }

  try {
    // Perform the actual fetch using Nuxt's $fetch
    return await $fetch(url.toString(), {
      method: options.method || "GET",
      headers,
      body: options.body,
    });
  } catch (err: any) {
    // Extract HTTP status code
    const statusCode = err?.statusCode || err?.response?.status;

    // Handle specific error cases with redirection
    switch (statusCode) {
      case 401: // Unauthorized — redirect to login
        navigateTo("/login");
        break;
      case 403: // Forbidden — no redirection, user is not allowed
        break;
      case 404: // Not Found — redirect to custom 404 page
        navigateTo("/404");
        break;
      case 500: // Internal Server Error — redirect to home or fallback
        navigateTo("/");
        break;
      default:
        // Let other errors bubble up
        break;
    }

    throw err; // Propagate error for handling in calling code
  }
}
