// This service handles brand-related operations accessible to public users,
// such as requesting a new brand or claiming an existing one.

import type { RequestBrandForm } from "~/types/brand";
import type { ClaimForm } from "~/types/brand";
import { apiFetch } from "~/composables/adapter/useApi";

export const brandService = {
  // Submit a request to register a new brand
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

    return await apiFetch("/brands/request", {
      method: "POST",
      body: formData,
    });
  },

  // Submit a claim to become the owner of an existing brand
  async claimBrand(form: ClaimForm) {
    return await apiFetch("/brands/claim", {
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
