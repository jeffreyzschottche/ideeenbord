export interface CmsField {
  id?: number;
  page_id: number;
  label: string;
  key: string;
  type: "text" | "image" | "html" | "link";
  value: string;
  created_at?: string;
  updated_at?: string;
}
