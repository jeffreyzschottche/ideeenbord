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
}

export interface LoginForm {
  email: string;
  password: string;
}
