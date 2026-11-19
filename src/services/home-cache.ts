import { isHomeData, type HomeData } from '../types/home.ts'

const maxAge = 24 * 60 * 60 * 1000
const maxBytes = 256 * 1024

// /home contains only public data. Keep one response per API in this browser tab.
export function createHomeCache(apiBase: string, storage: () => Storage | null = () => typeof window === 'undefined' ? null : window.sessionStorage) {
  const key = 'mdawiki:home:v1:' + apiBase.replace(/\/$/, '')
  let entry: { data: HomeData; saved: number } | null = null
  let loaded = false
  function valid(value: any): value is NonNullable<typeof entry> {
    return value && typeof value.saved === 'number' && value.saved <= Date.now()
      && Date.now() - value.saved < maxAge && isHomeData(value.data)
  }
  return {
    read(): HomeData | null {
      if (!loaded) {
        loaded = true
        try {
          const raw = storage()?.getItem(key)
          if (raw && raw.length <= maxBytes) {
            const value = JSON.parse(raw)
            if (valid(value)) entry = value
          }
        } catch { /* Storage may be unavailable or invalid. */ }
      }
      if (!valid(entry)) { entry = null; return null }
      return entry.data
    },
    write(data: HomeData) {
      if (!isHomeData(data)) return
      const value = { data, saved: Date.now() }
      const raw = JSON.stringify(value)
      if (raw.length > maxBytes) return
      loaded = true; entry = value
      try { storage()?.setItem(key, raw) } catch { /* Memory cache still works. */ }
    },
  }
}
