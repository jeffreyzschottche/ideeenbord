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

export interface Brand {
  id: number;
  title: string;
  category: string;
  website_url: string;
  intro: string;
  intro_short: string;
  email: string;
  socials: SocialItem[];
  logo_path?: string;
  rating_sum?: number;
  rating_count?: number;
  slug: string;
  main_question_id?: number;
  subscription?: string;
  accepted?: boolean;
  verified?: boolean;
}
