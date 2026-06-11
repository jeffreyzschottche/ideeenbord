import { useRuntimeConfig, useCookie } from "#app";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";

/**
 * Custom API fetch function for authenticated Brand Owner requests.
 * Automatically attaches the Brand Owner's token (from store or cookie).
 *
 * @param url - API endpoint path (relative to /v1)
 * @param options - Optional fetch configuration (method, headers, body, etc.)
 * @returns Parsed JSON response
 * @throws Error if response is not OK
 */
export async function brandOwnerApiFetch<T = any>(
  url: string,
  options: RequestInit = {}
): Promise<T> {
  const config = useRuntimeConfig();
  const baseUrl = config.public.apiBaseUrl;

  const store = useBrandOwnerAuthStore();
  const token = store.token || useCookie<string | null>("bo_token").value;

  // Laat de browser de juiste multipart Content-Type (met boundary) zetten bij FormData.
  const isFormData =
    typeof FormData !== "undefined" && options.body instanceof FormData;
  const headers: Record<string, string> = {
    ...((options.headers as Record<string, string>) || {}),
    Authorization: `Bearer ${token}`,
  };
  if (!isFormData) {
    headers["Content-Type"] = "application/json";
  }

  const res = await fetch(`${baseUrl}/v1${url}`, {
    ...options,
    headers,
  });

  // if (!res.ok) {
  //   const errText = await res.text();
  //   console.log(errText);
  //   throw new Error(`API error: ${errText}`);
  // }
  if (!res.ok) {
    // Read the body exactly once; a Response body can't be consumed twice.
    const raw = await res.text();
    let errorBody: any = null;
    try {
      errorBody = raw ? JSON.parse(raw) : null;
    } catch {
      // Not JSON (HTML error page, proxy timeout, empty body, ...)
    }

    const error = new Error(
      errorBody?.message ||
        raw ||
        `API error ${res.status} ${res.statusText}`
    );
    (error as any).status = res.status;
    (error as any).validationErrors = errorBody?.errors || null;
    throw error;
  }

  return await res.json();
}
