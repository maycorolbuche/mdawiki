const env = import.meta.env ?? {}
export const runtime = Object.freeze({
  dataSource: env.VITE_DATA_SOURCE || (env.DEV ? 'mock' : 'api'),
  apiBaseUrl: env.VITE_API_BASE_URL || '/api/v1',
  requestTimeout: 15000,
})
if (!['api', 'mock'].includes(runtime.dataSource))
  throw new Error('VITE_DATA_SOURCE deve ser api ou mock.')
