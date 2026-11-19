import type { Page } from '../types/page'

type Entry = { page: Page; saved: number }
const maxAge = 24 * 60 * 60 * 1000
const maxPages = 30
const maxBytes = 2 * 1024 * 1024

// One cache per session client. Only anonymous API responses reach sessionStorage.
export function createPageCache(apiBase: string, storage: () => Storage | null = () => typeof window === 'undefined' ? null : window.sessionStorage) {
  const key = 'mdawiki:articles:v1:' + apiBase.replace(/\/$/, '')
  const publicPages = new Map<string, Entry>()
  const privatePages = new Map<string, Entry>()
  let loaded = false
  function valid(entry: any, slug: string): entry is Entry {
    return entry && typeof entry.saved === 'number' && entry.saved <= Date.now() && Date.now() - entry.saved < maxAge
      && entry.page?.slug === slug && typeof entry.page.html === 'string' && typeof entry.page.title === 'string'
  }
  function load() {
    if (loaded) return
    loaded = true
    try {
      const raw = storage()?.getItem(key)
      if (!raw || raw.length > maxBytes) return
      const entries = JSON.parse(raw)
      if (!Array.isArray(entries)) return
      for (const [slug, entry] of entries.slice(-maxPages)) if (valid(entry, slug) && entry.page.is_public !== false) publicPages.set(slug, entry)
    } catch { /* Storage may be unavailable or contain an obsolete entry. */ }
  }
  function trim(pages: Map<string, Entry>) {
    for (const [slug, entry] of pages) if (!valid(entry, slug)) pages.delete(slug)
    while (pages.size > maxPages || JSON.stringify([...pages]).length > maxBytes) pages.delete(pages.keys().next().value!)
  }
  function persist() {
    trim(publicPages)
    try { storage()?.setItem(key, JSON.stringify([...publicPages])) } catch { /* Memory cache still works. */ }
  }
  return {
    read(slug: string, authenticated: boolean): Page | null {
      load()
      const pages = authenticated ? privatePages : publicPages
      const entry = pages.get(slug)
      if (!valid(entry, slug)) { pages.delete(slug); return null }
      // Move recently visited pages to the end for bounded LRU eviction.
      pages.delete(slug); pages.set(slug, entry)
      return entry.page
    },
    write(page: Page, authenticated: boolean) {
      load()
      if (!authenticated && page.is_public === false) return
      if (authenticated && page.is_public === false) { publicPages.delete(page.slug); persist() }
      const pages = authenticated ? privatePages : publicPages
      pages.delete(page.slug)
      pages.set(page.slug, { page, saved: Date.now() })
      trim(pages)
      if (!authenticated) persist()
    },
    remove(slug: string) {
      load(); publicPages.delete(slug); privatePages.delete(slug); persist()
    },
    clearPrivate() { privatePages.clear() },
  }
}
