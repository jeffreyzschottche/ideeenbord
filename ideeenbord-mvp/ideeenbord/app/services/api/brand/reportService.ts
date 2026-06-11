import { brandOwnerApiFetch } from "~/composables/brand/useBrandOwnerApi";

export type ReportPeriodType = "all" | "monthly" | "custom";

export type ReportSummary = {
  id: number;
  title: string;
  status: "processing" | "completed" | "failed";
  provider: string | null;
  model: string | null;
  period_type: ReportPeriodType | null;
  period_start: string | null;
  period_end: string | null;
  generated_at: string | null;
  created_at: string;
};

export type ReportThemeSentiment = "positief" | "negatief" | "gemengd";
export type ReportPriority = "hoog" | "middel" | "laag";

export type ReportAi = {
  executive_summary: string;
  key_findings: string[];
  participation_analysis: string;
  trend_analysis: string;
  sentiment_analysis: string;
  idea_themes: {
    theme: string;
    description: string;
    sentiment: ReportThemeSentiment;
    example_ideas: string[];
  }[];
  top_ideas_analysis: string;
  audience_insight: string;
  audience_segments: { segment: string; description: string }[];
  main_question_insights: string;
  opportunities: string[];
  risks: string[];
  strategic_outlook: string;
  recommendations: {
    title: string;
    detail: string;
    priority: ReportPriority;
    expected_impact: string;
  }[];
  action_plan: { phase: string; timeframe: string; actions: string[] }[];
  conclusion: string;
  suggested_main_question: string;
};

export type BrandReport = ReportSummary & {
  metrics: any;
  ai: ReportAi | null;
  error?: string | null;
};

export type GenerateReportPayload = {
  period_type?: ReportPeriodType;
  start?: string | null;
  end?: string | null;
};

export type ReportRange = {
  first: string | null;
  last: string | null;
  months: { value: string; label: string; ideas: number }[];
};

export const reportService = {
  async list(brandId: number): Promise<ReportSummary[]> {
    const res = await brandOwnerApiFetch<{ reports: ReportSummary[] }>(
      `/brands/${brandId}/reports`
    );
    return res.reports;
  },

  async generate(
    brandId: number,
    payload: GenerateReportPayload = {}
  ): Promise<BrandReport> {
    const res = await brandOwnerApiFetch<{ report: BrandReport }>(
      `/brands/${brandId}/reports`,
      { method: "POST", body: JSON.stringify(payload) }
    );
    return res.report;
  },

  async get(reportId: number): Promise<BrandReport> {
    const res = await brandOwnerApiFetch<{ report: BrandReport }>(
      `/reports/${reportId}`
    );
    return res.report;
  },

  /** Live statistics (no AI) — same analytics shape as a report's `metrics`. */
  async stats(brandId: number): Promise<any> {
    const res = await brandOwnerApiFetch<{ stats: any }>(
      `/brands/${brandId}/stats`
    );
    return res.stats;
  },

  /** Months/date-bounds with actual data, to drive the period pickers. */
  async range(brandId: number): Promise<ReportRange> {
    return brandOwnerApiFetch<ReportRange>(`/brands/${brandId}/report-range`);
  },
};
