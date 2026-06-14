export interface RegisterForm {
  name: string;
  email: string;
  username: string;
  password: string;
  gender?: string;
  birthdate?: string;
  education_level?: string;
  education?: string;
  job?: string;
  sector?: string;
  city?: string;
  birth_city?: string;
  relationship_status?: string;
  postal_code?: string;
  // Step 3 - Huishoud/koopgedrag
  political_preference?: string;
  household_role?: string;
  purchase_decision?: string;
  order_frequency?: string;
  tech_spend?: string;
  grocery_spend?: string;
  household_size?: string;
}

export interface LoginForm {
  email: string;
  password: string;
}

export interface ForgotPasswordForm {
  email: string;
}

export interface ResetPasswordForm {
  email: string;
  token: string;
  password: string;
  password_confirmation: string;
}
