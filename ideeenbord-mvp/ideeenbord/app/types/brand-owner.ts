import type { Brand } from "./brand";

export interface BrandOwner {
  id: number;
  brand_id: number;
  name: string;
  email: string;
  phone?: string;
  url?: string;
  subscription_plan?: string;
  verified_owner?: boolean;
  brand: BrandSummary;
}

export interface BrandOwnerLoginResponse {
  message: string;
  owner: BrandOwner;
  token: string;
}

export interface UpdateBrandOwnerForm {
  email: string;
  phone: string;
  password?: string;
  password_confirmation?: string;
}
export interface BrandSummary {
  id: number;
  title: string;
  slug: string;
  logo_path?: string;
  main_question_id?: number;
}
