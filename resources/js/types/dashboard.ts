// Dashboard-specific types

export interface RepositoryData {
  id: string | number;
  name: string;
  description?: string;
  default_branch?: string;
  defaultBranch?: string;
  owner: { login: string } | string;
  full_name?: string;
  link?: string;
}

export interface BranchData {
  id?: string | number;
  name: string;
  sha: string;
  link?: string;
}

export interface LabelData {
  id: string | number;
  name: string;
  color?: string;
}

export interface ReviewerData {
  id: string | number;
  name: string;
  login?: string;
}

export interface TemplateData {
  id: number;
  user_id: number;
  name: string;
  title_template: string;
  body_template: string;
  is_default: boolean;
  default_labels?: string[];
}

export interface RecentBranchData {
  name: string;
  sha: string;
  repository: RepositoryData;
}

export interface JiraTicketData {
  key: string;
  summary: string;
  issueType: string;
} 