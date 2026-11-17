<script setup lang="ts">
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ApiError, getPage } from '../services/api'
import { cleanArticle } from '../services/content'
import type { Page, Snapshot } from '../types/page'

const route = useRoute()
const router = useRouter()
const snapshot = inject<Snapshot>('snapshot')!
const slug = computed(() => String(route.params.slug || '').replace(/\/$/, ''))
const page = ref<Page | null>(snapshot.page?.slug === slug.value ? snapshot.page : null)
const loading = ref(false)
const missing = ref(false)
const failed = ref(false)
const html = computed(() => cleanArticle(page.value?.html || ''))
let controller: AbortController | undefined
let mounted = false

function updateMetadata(value: Page | null) {
  document.title = value ? `${value.title} — MDAWiki` : 'Página não encontrada — MDAWiki'
  for (const [selector, attribute, content] of [
    ['meta[name="description"]', 'content', value?.description || ''],
    ['meta[property="og:title"]', 'content', value?.title || 'MDAWiki'],
    ['meta[property="og:description"]', 'content', value?.description || ''],
    ['meta[property="og:url"]', 'content', value?.canonical_url || ''],
    ['link[rel="canonical"]', 'href', value?.canonical_url || ''],
    ['meta[name="robots"]', 'content', value ? 'index,follow' : 'noindex,follow'],
  ]) document.querySelector(selector!)?.setAttribute(attribute!, content!)
}

async function refresh() {
  controller?.abort()
  const request = new AbortController()
  controller = request
  loading.value = true
  failed.value = false
  missing.value = false
  const timeout = setTimeout(() => request.abort(new Error('timeout')), 15000)
  try {
    if (!snapshot.apiBaseUrl) throw new Error('API not configured')
    const value = await getPage(snapshot.apiBaseUrl, slug.value, request.signal)
    if (controller !== request) return
    page.value = value
    updateMetadata(value)
  } catch (error) {
    if (controller !== request) return
    if (error instanceof ApiError && error.status === 404) {
      page.value = null
      missing.value = true
      updateMetadata(null)
    } else {
      failed.value = true
    }
  } finally {
    clearTimeout(timeout)
    if (controller === request) loading.value = false
  }
}

watch(slug, () => {
  page.value = snapshot.page?.slug === slug.value ? snapshot.page : null
  missing.value = false
  failed.value = false
  if (mounted) {
    document.title = page.value ? `${page.value.title} — MDAWiki` : 'Carregando — MDAWiki'
    void refresh()
  }
})
onMounted(() => { mounted = true; void refresh() })
onBeforeUnmount(() => { controller?.abort(); controller = undefined })

function followArticleLink(event: MouseEvent) {
  const anchor = (event.target as Element).closest('a')
  if (!anchor || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0 || anchor.target === '_blank') return
  if (!anchor.getAttribute('href') || anchor.getAttribute('href')?.startsWith('#')) return
  const destination = new URL(anchor.href, window.location.href)
  const site = new URL(snapshot.siteUrl || window.location.origin)
  if (destination.origin !== site.origin || !destination.pathname.startsWith(snapshot.basePath)) return
  event.preventDefault()
  void router.push(destination.pathname.slice(snapshot.basePath.length - 1) + destination.search + destination.hash)
}
</script>

<template>
  <section class="card article-card" :aria-busy="loading">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Artigo</span>
      <button class="btn btn-sm btn-outline-secondary" :disabled="loading" @click="refresh">{{ loading ? 'Atualizando…' : 'Atualizar' }}</button>
    </div>
    <div class="card-body p-4">
      <div v-if="failed" class="alert alert-warning" role="alert">
        Não foi possível consultar a versão atual.<span v-if="page"> Exibindo o conteúdo publicado.</span>
        <button class="btn btn-link p-0 ms-2" @click="refresh">Tentar novamente</button>
      </div>
      <template v-if="page">
        <h1>{{ page.title }}</h1>
        <div class="wiki-content" @click="followArticleLink" v-html="html" />
      </template>
      <template v-else-if="missing"><h1>Página não encontrada</h1><p>Este artigo não existe ou não está disponível.</p></template>
      <p v-else-if="!failed" role="status">Carregando artigo…</p>
    </div>
  </section>
</template>
