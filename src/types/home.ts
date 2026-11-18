export interface HomeItem {
  id: string
  slug: string
  title: string
  description?: string | null
  image?: string | null
}
export interface HomeData {
  random: HomeItem[]
  top_today: HomeItem[]
  top_week: HomeItem[]
  top_month: HomeItem[]
  missing: HomeItem[]
  recent: HomeItem[]
}
export function isHomeData(value: unknown): value is HomeData {
  if (!value || typeof value !== 'object') return false
  return ['random', 'top_today', 'top_week', 'top_month', 'missing', 'recent'].every(key => {
    const items = (value as Record<string, unknown>)[key]
    return Array.isArray(items) && items.every(item => item && typeof item.id === 'string' && typeof item.slug === 'string' && typeof item.title === 'string' && (item.image == null || typeof item.image === 'string') && (item.description == null || typeof item.description === 'string'))
  })
}
export function homeImage(value: string | null | undefined, apiBaseUrl: string, siteUrl: string): string | undefined {
  if (!value) return undefined
  try {
    const url = new URL(value, new URL(apiBaseUrl.replace(/\/$/, '') + '/', siteUrl).href)
    return /^https?:$/.test(url.protocol) ? url.href : undefined
  } catch { return undefined }
}
