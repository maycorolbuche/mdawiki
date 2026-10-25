export class ApiError extends Error {
  constructor(message, { status = 0, code = 'NETWORK_ERROR' } = {}) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.code = code
  }
}

export function createApiClient({ baseUrl, fetcher = globalThis.fetch, timeout = 15000 }) {
  return {
    async get(path, { query = {}, signal } = {}) {
      if (signal?.aborted) throw new DOMException('Requisição cancelada.', 'AbortError')
      const params = new URLSearchParams()
      for (const [key, value] of Object.entries(query)) {
        if (value !== undefined && value !== null && value !== '') params.set(key, String(value))
      }
      const url = `${baseUrl.replace(/\/$/, '')}/${path.replace(/^\//, '')}${params.size ? `?${params}` : ''}`
      const controller = new AbortController()
      const cancel = () => controller.abort()
      signal?.addEventListener('abort', cancel, { once: true })
      let timedOut = false
      const timer = setTimeout(() => {
        timedOut = true
        controller.abort()
      }, timeout)
      try {
        const response = await fetcher(url, {
          method: 'GET',
          headers: { Accept: 'application/json' },
          credentials: 'omit',
          signal: controller.signal,
        })
        let payload
        try {
          payload = await response.json()
        } catch {
          throw new ApiError('A API retornou uma resposta inválida.', {
            status: response.status,
            code: 'INVALID_RESPONSE',
          })
        }
        if (!response.ok)
          throw new ApiError(payload?.error?.message || 'Não foi possível carregar os dados.', {
            status: response.status,
            code: payload?.error?.code || 'HTTP_ERROR',
          })
        if (!payload || !Object.hasOwn(payload, 'data'))
          throw new ApiError('Resposta sem o campo data.', { code: 'INVALID_RESPONSE' })
        return payload
      } catch (error) {
        if (signal?.aborted) throw new DOMException('Requisição cancelada.', 'AbortError')
        if (timedOut)
          throw new ApiError('A API demorou para responder. Tente novamente.', { code: 'TIMEOUT' })
        if (error instanceof ApiError) throw error
        throw new ApiError('Não foi possível conectar à API. Tente novamente.')
      } finally {
        clearTimeout(timer)
        signal?.removeEventListener('abort', cancel)
      }
    },
  }
}
