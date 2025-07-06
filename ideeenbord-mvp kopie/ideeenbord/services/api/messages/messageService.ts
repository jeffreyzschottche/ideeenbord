import { apiFetch } from "~/composables/adapter/useApi";
export const messageService = {
  async getGreeting(): Promise<string> {
    const res: string = await apiFetch("/hi");
    return res;
  },
};
