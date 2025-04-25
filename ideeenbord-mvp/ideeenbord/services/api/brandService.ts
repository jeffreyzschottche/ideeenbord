import type { RequestBrandForm } from "~/types/brand";
import type { ClaimForm } from "~/types/brand";

const BASE = "http://localhost:8000/api";

export const brandService = {
  async requestBrand(form: RequestBrandForm) {
    const formData = new FormData();
    formData.append("title", form.title);
    formData.append("category", form.category);
    formData.append("website_url", form.websiteUrl);
    formData.append("intro", form.intro);
    formData.append("intro_short", form.introShort);
    formData.append("email", form.email);
    if (form.logo) {
      formData.append("logo", form.logo);
    }
    if (form.socials) {
      formData.append("socials", JSON.stringify(form.socials));
    }

    return await $fetch(`${BASE}/brands/request`, {
      method: "POST",
      body: formData,
    });
  },
  async claimBrand(form: ClaimForm) {
    return await $fetch("http://localhost:8000/api/brands/claim", {
      method: "POST",
      body: {
        brand_id: form.brandId,
        name: form.name,
        email: form.email,
        phone: form.phone,
        url: form.url,
        subscription_plan: form.subscriptionPlan,
        password: form.password,
      },
    });
  },
};
