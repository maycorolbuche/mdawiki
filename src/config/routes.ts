import systemPages from '../../config/system-pages.json' with { type: 'json' }
import type { RouteRecordRaw } from 'vue-router'
// Browsers encode Unicode paths; legacy links also encode the reserved @.
// Register aliases for both forms so reloads and SSG match the same component.
export function encodedRoutes(routes: RouteRecordRaw[]): RouteRecordRaw[] {
  return routes.map(route => {
    const aliases = typeof route.alias === 'string' ? [route.alias] : route.alias || []
    const all = [route.path, ...aliases]
    const encoded = all.flatMap(path => [encodeURI(path), encodeURI(path).replaceAll('@', '%40')])
    return { ...route, alias: [...new Set([...aliases, ...encoded])].filter(path => path !== route.path) } as RouteRecordRaw
  })
}

export function legacySystemRoutes(): RouteRecordRaw[] {
  return Object.entries(systemPages).map(([legacy, slug]) => ({
    path: `/page@${legacy}/:extra?`,
    redirect: to => ({
      path: `/p/${slug}` + (to.params.extra ? '/' + encodeURIComponent(String(to.params.extra)) : ''),
      query: to.query,
      hash: to.hash,
    }),
  }))
}
