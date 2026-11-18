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
