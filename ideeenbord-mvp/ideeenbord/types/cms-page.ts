import type { CmsField } from "./cms-field";
export interface CmsPage {
  id: number;
  title: string;
  slug: string;
  fields?: CmsField[];
  created_at: string;
  updated_at: string;
}
