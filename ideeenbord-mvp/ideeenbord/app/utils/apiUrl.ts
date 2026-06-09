export function backendBaseFromApiBase(apiBase: string): string {
  return apiBase.replace(/\/api\/?$/, "");
}

export function storageBaseFromApiBase(apiBase: string): string {
  return apiBase.replace(/\/api\/?$/, "/storage");
}
