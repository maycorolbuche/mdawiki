export interface PageLink { id: string; slug: string; title: string }
export interface Page {
  id: string
  slug: string
  title: string
  html: string
  study_material_url?: string | null
  description: string
  updated_at: string | null
  canonical_url: string
  is_public?: boolean
  redirect_slug?: string | null
  image_usage?: PageLink[]
  categories?: PageLink[]
  mentions?: PageLink[]
  related_pages?: PageLink[]
  category_members?: PageLink[]
  missing_links?: PageLink[]
}
export interface Snapshot {
  page: Page | null
  pages: Page[]
  apiBaseUrl: string
  siteUrl: string
  basePath: string
}



