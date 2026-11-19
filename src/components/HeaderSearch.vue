<script setup lang="ts">
import { inject, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { Snapshot } from '../types/page'
type Suggestion = { id: string; slug: string; title: string }
const snapshot = inject<Snapshot>('snapshot')!
const router = useRouter(), route = useRoute()
const term = ref(''), items = ref<Suggestion[]>([]), expanded = ref(false), loading = ref(false), failed = ref(false)
const root = ref<HTMLFormElement>(), input = ref<HTMLInputElement>()
let timer: ReturnType<typeof setTimeout> | undefined
let controller: AbortController | undefined
function cancel() { clearTimeout(timer); controller?.abort(); controller = undefined; loading.value = false }
function close() { expanded.value = false; cancel() }
function lookup(value: string) {
  cancel(); items.value = []; failed.value = false
  const query = value.trim()
  expanded.value = query.length >= 2
  if (query.length < 2) return
  loading.value = true
  timer = setTimeout(async () => {
    const request = new AbortController(); controller = request
    const timeout = setTimeout(() => request.abort(), 8000)
    try {
      const response = await fetch(snapshot.apiBaseUrl.replace(/\/$/, '') + '/v1/search?' + new URLSearchParams({ q: query, limit: '8' }), { headers: { Accept: 'application/json' }, signal: request.signal, cache: 'no-store' })
      if (!response.ok) throw new Error('Search failed')
      const body = await response.json()
      if (!Array.isArray(body.data?.items)) throw new Error('Invalid results')
      if (controller !== request) return
      items.value = body.data.items.filter((item: any) => typeof item.slug === 'string' && typeof item.title === 'string').slice(0, 8)
    } catch { if (controller === request) failed.value = true }
    finally { clearTimeout(timeout); if (controller === request) loading.value = false }
  }, 250)
}
watch(term, lookup)
function show() { if (term.value.trim().length >= 2) { expanded.value = true; if (!items.value.length) lookup(term.value) } }
function submit() { const query = term.value.trim(); close(); if (query) void router.push({ path: '/page@Busca', query: { q: query } }) }
async function focusOption(offset: number, event?: KeyboardEvent) {
  if (!expanded.value || !items.value.length) return
  await nextTick()
  const links = [...root.value!.querySelectorAll<HTMLAnchorElement>('[role="option"]')]
  const index = event ? links.indexOf(event.currentTarget as HTMLAnchorElement) + offset : 0
  if (index < 0) input.value?.focus()
  else links[Math.min(index, links.length - 1)]?.focus()
}
watch(() => route.fullPath, close)
onBeforeUnmount(cancel)
</script>

<template>
  <form ref="root" class="site-search" role="search" @submit.prevent="submit" @keydown.esc.prevent="input?.focus(); close()" @focusout="event => { if (!root?.contains(event.relatedTarget as Node)) close() }">
    <label class="visually-hidden" for="site-search">Localizar na MDAWiki</label>
    <input id="site-search" ref="input" v-model="term" type="search" role="combobox" aria-autocomplete="list" aria-controls="search-suggestions" :aria-expanded="expanded" maxlength="100" placeholder="Localizar…" autocomplete="off" @focus="show" @keydown.down.prevent="focusOption(1)">
    <button class="visually-hidden" type="submit">Pesquisar</button>
    <div v-if="expanded" class="search-suggestions">
      <p v-if="loading" class="search-message" role="status">Buscando…</p>
      <p v-else-if="failed" class="search-message" role="status">Não foi possível carregar as sugestões. Pressione Enter para a busca completa.</p>
      <p v-else-if="!items.length" class="search-message" role="status">Nenhum resultado encontrado.</p>
      <ul id="search-suggestions" role="listbox" aria-label="Sugestões de páginas">
        <li v-for="(item, index) in items" :key="item.id || item.slug" role="presentation"><RouterLink :id="`search-result-${index}`" role="option" :aria-selected="false" :to="`/${encodeURIComponent(item.slug)}/`" @click="close" @keydown.down.prevent="focusOption(1, $event)" @keydown.up.prevent="focusOption(-1, $event)"><i class="bi bi-file-earmark-text" aria-hidden="true" />{{ item.title }}</RouterLink></li>
      </ul>
      <button class="search-all" type="submit"><i class="bi bi-search" aria-hidden="true" /> Busca completa por “{{ term.trim() }}”</button>
    </div>
  </form>
</template>

<style scoped>
.site-search { position: relative; min-width: 0; }
.search-suggestions { position: absolute; top: calc(100% + 9px); left: 0; width: 360px; max-width: calc(100vw - 30px); background: white; border: 1px solid #ddd; box-shadow: 0 5px 15px #0003; border-radius: 3px; color: #555; }
.search-suggestions ul { list-style: none; padding: 0; margin: 0; max-height: 360px; overflow-y: auto; }
.search-suggestions a { display: flex; align-items: baseline; gap: 8px; color: #555; padding: 9px 12px; text-decoration: none; overflow-wrap: anywhere; }
.search-suggestions a:hover, .search-suggestions a:focus { background: #edf3fb; color: #3476ca; outline-offset: -2px; }
.search-message { margin: 0; padding: 12px; font-size: 12px; }
.search-all { border: 0; border-top: 1px solid #ddd; width: 100%; text-align: left; padding: 10px 12px; background: #f5f5f5; color: #3476ca; font: inherit; overflow-wrap: anywhere; }
@media(max-width:575px) { .search-suggestions { position: fixed; top: 54px; left: 10px; right: 10px; width: auto; max-width: none; } }
</style>
