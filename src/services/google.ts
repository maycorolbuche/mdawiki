import { nextTick } from 'vue'
import type { Router, RouteLocationNormalizedLoaded } from 'vue-router'

declare global {
  interface Window {
    dataLayer?: unknown[]
    gtag?: (...args: unknown[]) => void
  }
}
export interface GoogleConfig { enabled: boolean; analyticsId: string; adsenseClient: string }
const installed = new WeakSet<Router>()

function script(id: string, src: string, crossOrigin?: string) {
  if (document.getElementById(id)) return
  const element = document.createElement('script')
  element.id = id; element.src = src; element.async = true
  if (crossOrigin) element.crossOrigin = crossOrigin
  // Blocking Google scripts must not interrupt the wiki or trigger repeated downloads.
  element.onerror = () => { element.dataset.blocked = 'true' }
  document.head.append(element)
}
function withoutQuery(value: string): string {
  try { const url = new URL(value); return url.origin + url.pathname } catch { return '' }
}

export function installGoogleServices(router: Router, config: GoogleConfig): () => void {
  if (!config.enabled || installed.has(router)) return () => {}
  installed.add(router)
  const analyticsId = /^G-[A-Z0-9]+$/.test(config.analyticsId) ? config.analyticsId : ''
  const adsenseClient = /^ca-pub-\d{16}$/.test(config.adsenseClient) ? config.adsenseClient : ''
  let initialized = false
  let previousRoute = ''
  let referrer = withoutQuery(document.referrer)

  async function track(route: RouteLocationNormalizedLoaded) {
    const editing = route.meta.action === 'editor'
    const changed = previousRoute !== route.path
    previousRoute = route.path
    if (adsenseClient) script('mdawiki-adsense', `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${adsenseClient}`, 'anonymous')
    if (editing || !analyticsId || !changed) return // The legacy Analytics header excludes editing pages.
    await nextTick()
    if (router.currentRoute.value !== route) return
    const location = window.location.origin + router.resolve(route).href.split(/[?#]/, 1)[0]
    if (!initialized) {
      window.dataLayer ||= []
      window.gtag ||= function (..._args: unknown[]) { window.dataLayer!.push(arguments) }
      window.gtag('js', new Date())
      window.gtag('config', analyticsId, { send_page_view: false, page_location: location, page_referrer: referrer })
      script('mdawiki-analytics', `https://www.googletagmanager.com/gtag/js?id=${analyticsId}`)
      initialized = true
    }
    const title = /^Carregando/.test(document.title) ? String(route.params.slug || route.path).replaceAll('_', ' ').replace(/^\//, '') + ' — MDAWiki' : document.title
    window.gtag!('event', 'page_view', { send_to: analyticsId, page_location: location, page_referrer: referrer, page_title: title })
    referrer = location
  }
  const remove = router.afterEach((to, _from, failure) => { if (!failure) void track(to) })
  void track(router.currentRoute.value)
  return () => { remove(); installed.delete(router) }
}
