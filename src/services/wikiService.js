import { readPage, readSite } from './api/contracts.js'
import { runtime } from '../config/runtime.js'
import { createApiClient } from './api/client.js'

export function createWikiService(client) {
  const segment = (value) => encodeURIComponent(value)
  return {
    async site(options) {
      return readSite((await client.get('/site', options)).data)
    },
    async page(slug, options) {
      return readPage((await client.get(`/pages/${segment(slug || '~home')}`, options)).data)
    },
    search(query, options = {}) {
      return client.get('/search', { ...options, query })
    },
    revisions(id, options = {}) {
      return client.get(`/pages/${segment(id)}/revisions`, options)
    },
    discussions(id, options = {}) {
      return client.get(`/pages/${segment(id)}/discussions`, options)
    },
    async source(id, options = {}) {
      return (await client.get(`/pages/${segment(id)}/source`, options)).data
    },
  }
}
const fetcher =
  runtime.dataSource === 'mock'
    ? async (...args) => (await import('../mocks/transport.js')).mockFetch(...args)
    : (...args) => globalThis.fetch(...args)
export const wikiService = createWikiService(
  createApiClient({ baseUrl: runtime.apiBaseUrl, fetcher, timeout: runtime.requestTimeout }),
)
