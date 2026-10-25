// O slug é um identificador opaco fornecido pela API. Não recriar a normalização do PHP.
export function pageLocation(slug, query = {}) {
  return slug === '' ? { name: 'home', query } : { name: 'wiki-page', params: { slug }, query }
}
export function validSlug(slug) {
  return (
    typeof slug === 'string' &&
    slug.length > 0 &&
    slug.length <= 240 &&
    !/[/\\?#]/.test(slug) &&
    ![...slug].some((char) => char.charCodeAt(0) < 32) &&
    !['.', '..'].includes(slug)
  )
}
export function validAnchor(id) {
  return typeof id === 'string' && /^[\p{L}\p{N}_-]+$/u.test(id)
}
export function safeUrl(value) {
  if (typeof value !== 'string') return ''
  const url = value.trim()
  if (/^https?:\/\//i.test(url)) return url
  if (
    url.startsWith('/') &&
    !url.startsWith('//') &&
    !url.includes('\\') &&
    ![...url].some((char) => char.charCodeAt(0) <= 32)
  )
    return url
  return ''
}
