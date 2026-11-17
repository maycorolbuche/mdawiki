export interface Page {
  id: string
  slug: string
  title: string
  html: string
  description: string
  updated_at: string | null
  canonical_url: string
}
export interface Snapshot {
  page: Page | null
  pages: Page[]
  apiBaseUrl: string
  siteUrl: string
  basePath: string
}
