import { apiFetch } from "~/composables/useApi";
export const messageService = {
  async getGreeting(): Promise<string> {
    const res: string = await apiFetch("/hi");
    return res;
  },
};
