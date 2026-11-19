import { reactive, watch } from 'vue'
import { createPageCache } from './page-cache'
import type { Snapshot } from '../types/page'
export interface User { ID: number; NOME: string; APELIDO: string; EMAIL: string; NIVEL: number; photo?: string; [key: string]: unknown }
export class RequestError extends Error { constructor(public status: number, public code: string, message: string) { super(message) } }
export function createSession(snapshot: Snapshot) {
  const state = reactive({ user: null as User | null, csrf: '', ready: false })
  const articles = createPageCache(snapshot.apiBaseUrl)
  watch(() => state.user?.ID, () => articles.clearPrivate(), { flush: 'sync' })
  let initialization: Promise<void> | undefined
  async function request(path: string, method = 'GET', body?: unknown): Promise<any> {
    if (method !== 'GET' && !state.csrf) await initialize()
    const response = await fetch(snapshot.apiBaseUrl.replace(/\/$/, '') + '/v1' + path, {
      method, credentials: 'include', cache: 'no-store', signal: AbortSignal.timeout(30000),
      headers: { Accept: 'application/json', ...(body === undefined ? {} : { 'Content-Type': 'application/json' }), ...(method === 'GET' ? {} : { 'X-CSRF-Token': state.csrf }) },
      body: body === undefined ? undefined : JSON.stringify(body),
    })
    let payload: any
    try { payload = await response.json() } catch { throw new RequestError(response.status, 'invalid_response', 'A API retornou uma resposta inválida.') }
    if (!response.ok) {
      if (response.status === 401) state.user = null
      if (payload.error?.code === 'csrf_failed') { state.csrf = ''; initialization = undefined }
      throw new RequestError(response.status, payload.error?.code || 'request_failed', payload.error?.message || 'Não foi possível concluir a solicitação.')
    }
    const data = payload.data
    if (data && typeof data.csrf_token === 'string') { state.csrf = data.csrf_token; state.user = data.user || null }
    return data
  }
  function initialize(): Promise<void> {
    return initialization ??= request('/auth/session').then(() => { state.ready = true }).catch(error => { state.ready = true; initialization = undefined; throw error })
  }
  async function logout() { await request('/auth/logout', 'POST', {}); state.user = null }
  return { state, request, initialize, logout, articles }
}
export type SessionClient = ReturnType<typeof createSession>
