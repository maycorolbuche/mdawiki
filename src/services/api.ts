import type { Page } from '../types/page'

export class ApiError extends Error {
  constructor(public readonly status: number) {
    super(status === 404 ? 'Página não encontrada.' : 'Não foi possível atualizar o conteúdo.')
  }
}

export async function getPage(base: string, slug: string, signal?: AbortSignal): Promise<Page> {
  const response = await fetch(`${base.replace(/\/$/, '')}/v1/pages/${encodeURIComponent(slug)}`, {
    headers: { Accept: 'application/json' }, cache: 'no-store', signal,
  })
  if (!response.ok) throw new ApiError(response.status)
  const body = await response.json()
  if (!body.data || typeof body.data.html !== 'string' || typeof body.data.slug !== 'string' || typeof body.data.title !== 'string') {
    throw new ApiError(502)
  }
  return body.data
}
