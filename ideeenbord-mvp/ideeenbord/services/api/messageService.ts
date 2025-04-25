export const messageService = {
  async getGreeting(): Promise<string> {
    const res = await $fetch<string>("http://127.0.0.1:8000/api/hi");
    return res;
  },
};
