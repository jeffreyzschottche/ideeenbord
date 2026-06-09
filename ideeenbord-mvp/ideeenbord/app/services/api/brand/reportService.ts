import { brandOwnerApiFetch } from "~/composables/brand/useBrandOwnerApi";

export type ReportSummary = {
  id: number;
  title: string;
  status: "processing" | "completed" | "failed";
  provider: string | null;
  model: string | null;
  generated_at: string | null;
  created_at: string;
};

export type ReportThemeSentiment = "positief" | "negatief" | "gemengd";

export type BrandReport = ReportSummary & {
  metrics: any;
  ai: {
    executive_summary: string;
    key_findings: string[];
    idea_themes: { theme: string; description: string; sentiment: ReportThemeSentiment }[];
    audience_insight: string;
    opportunities: string[];
    risks: string[];
    recommendations: { title: string; detail: string; priority: "hoog" | "middel" | "laag" }[];
    suggested_main_question: string;
  } | null;
  error?: string | null;
};

export const reportService = {
  async list(brandId: number): Promise<ReportSummary[]> {
    const res = await brandOwnerApiFetch<{ reports: ReportSummary[] }>(
      `/brands/${brandId}/reports`
    );
    return res.reports;
  },

  async generate(brandId: number): Promise<BrandReport> {
    const res = await brandOwnerApiFetch<{ report: BrandReport }>(
      `/brands/${brandId}/reports`,
      { method: "POST" }
    );
    return res.report;
  },

  async get(reportId: number): Promise<BrandReport> {
    const res = await brandOwnerApiFetch<{ report: BrandReport }>(
      `/reports/${reportId}`
    );
    return res.report;
  },
};
