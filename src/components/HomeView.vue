<script setup lang="ts">
import { inject, onMounted, ref } from 'vue'
import type { Snapshot, Page } from '../types/page'
const snapshot = inject<Snapshot>('snapshot')!
const pages = ref<Page[]>(snapshot.pages)
const failed = ref(false)
onMounted(async () => {
  document.title = 'MDAWiki — Tudo sobre Desbravadores e Aventureiros'
  document.querySelector('meta[name="robots"]')?.setAttribute('content', 'index,follow')
  document.querySelector('meta[name="description"]')?.setAttribute('content', 'A Wikipedia dos Desbravadores e Aventureiros')
  document.querySelector('meta[property="og:title"]')?.setAttribute('content', 'MDAWiki')
  document.querySelector('meta[property="og:description"]')?.setAttribute('content', 'A Wikipedia dos Desbravadores e Aventureiros')
  document.querySelector('meta[property="og:url"]')?.setAttribute('content', snapshot.siteUrl + '/')
  document.querySelector('link[rel="canonical"]')?.setAttribute('href', snapshot.siteUrl + '/')
  if (!snapshot.apiBaseUrl) return
  try {
    const response = await fetch(`${snapshot.apiBaseUrl}/v1/pages?limit=100`, { cache: 'no-store', signal: AbortSignal.timeout(15000) })
    if (!response.ok) throw new Error('API unavailable')
    const body = await response.json()
    if (!Array.isArray(body.data)) throw new Error('Invalid response')
    pages.value = body.data
  } catch { failed.value = true }
})
</script>

<template>
  <section class="card article-card">
    <div class="card-body p-4">
      <h1>A Wikipedia dos Desbravadores e Aventureiros</h1>
      <p v-if="failed" class="alert alert-warning" role="status">Não foi possível atualizar a lista. O conteúdo publicado continua disponível.</p>
      <ul class="article-list">
        <li v-for="page in pages" :key="page.id"><RouterLink :to="`/${encodeURIComponent(page.slug)}/`">{{ page.title }}</RouterLink></li>
      </ul>
      <p v-if="!pages.length && !failed" class="text-muted">Os artigos serão exibidos quando o conteúdo estiver disponível.</p>
    </div>
  </section>
</template>
