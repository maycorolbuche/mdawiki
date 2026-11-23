export function shareImageTags(value: string | null | undefined, title: string): Record<string, string> {
  if (!value) return {}
  try {
    const url = new URL(value)
    if (!['http:', 'https:'].includes(url.protocol) || url.username || url.password) return {}
    return { 'og:image': url.href, 'og:image:alt': title, 'twitter:image': url.href, 'twitter:image:alt': title }
  } catch {
    return {}
  }
}
