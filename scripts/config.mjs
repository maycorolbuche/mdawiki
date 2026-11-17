import defaults from '../config/public.json' with { type: 'json' }

export function resolveBuildConfig(env) {
  const config = {
    apiBaseUrl: (env.VITE_API_BASE_URL?.trim() || defaults.apiBaseUrl).replace(/\/$/, ''),
    siteUrl: (env.VITE_SITE_URL?.trim() || defaults.siteUrl).replace(/\/$/, ''),
    basePath: env.VITE_BASE_PATH?.trim() || defaults.basePath,
  }
  for (const [name, value] of [['VITE_API_BASE_URL', config.apiBaseUrl], ['VITE_SITE_URL', config.siteUrl]]) {
    let valid = false
    try {
      const url = new URL(value)
      valid = ['https:', 'http:'].includes(url.protocol) && !url.username && !url.password && !url.search && !url.hash
    } catch { /* Report the configuration field, not an opaque URL parser error. */ }
    if (!valid) throw new Error(`${name} deve ser uma URL HTTP(S) absoluta, sem credenciais, query ou fragmento. Confira .env.production.local ou as variáveis API_BASE_URL/SITE_URL do repositório.`)
  }
  if (!config.basePath.startsWith('/') || !config.basePath.endsWith('/') || config.basePath.includes('..')) throw new Error('VITE_BASE_PATH deve começar e terminar com /.')
  return config
}
