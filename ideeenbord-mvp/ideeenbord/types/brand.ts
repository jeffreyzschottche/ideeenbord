export interface SocialItem {
  platform: string; // bijv. "instagram"
  url: string; // de bijbehorende link
}

export interface RequestBrandForm {
  title: string;
  category: string;
  websiteUrl: string;
  intro: string;
  introShort: string;
  email: string;
  logo: File | null;
  socials: SocialItem[] | null;
}

export interface ClaimForm {
  brandId: string;
  name: string;
  email: string;
  phone: string;
  url: string;
  subscriptionPlan: "Brons" | "Zilver" | "Goud";
  password: string;
}
