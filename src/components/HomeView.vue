<script setup lang="ts">
import { inject, onBeforeUnmount, onMounted, ref } from 'vue'
import type { Snapshot } from '../types/page'
import type { SessionClient } from '../services/session'
import { homeImage, isHomeData, type HomeData } from '../types/home'
import HomeList from './HomeList.vue'

const snapshot = inject<Snapshot>('snapshot')!
const api = inject<SessionClient>('session')!
const base = import.meta.env.BASE_URL
const home = ref<HomeData>({
  random: [], top_today: [], top_week: [], top_month: [], missing: [], recent: [],
})
const hasData = ref(false)
let disposed = false
onBeforeUnmount(() => { disposed = true })
const failed = ref(false)
const loading = ref(true)
async function refresh() {
  if (disposed) return
  loading.value = true
  failed.value = false
  try {
    const data: unknown = await api.request('/home')
    if (!isHomeData(data)) throw new Error('Invalid home response')
    if (disposed) return
    home.value = data
    hasData.value = true
    api.homeCache.write(data)
  } catch { if (!disposed) failed.value = true } finally { if (!disposed) loading.value = false }
}
onMounted(() => {
  document.title = 'MDAWiki — Tudo sobre Desbravadores e Aventureiros'
  document.querySelector('meta[name="robots"]')?.setAttribute('content', 'index,follow')
  document.querySelector('meta[name="description"]')?.setAttribute('content', 'A Wikipedia dos Desbravadores e Aventureiros')
  document.querySelector('meta[property="og:title"]')?.setAttribute('content', 'MDAWiki')
  document.querySelector('meta[property="og:description"]')?.setAttribute('content', 'A Wikipedia dos Desbravadores e Aventureiros')
  document.querySelector('meta[property="og:url"]')?.setAttribute('content', snapshot.siteUrl + '/')
  document.querySelector('link[rel="canonical"]')?.setAttribute('href', snapshot.siteUrl + '/')
  const cached = api.homeCache.read()
  if (cached) { home.value = cached; hasData.value = true }
  void refresh()
})
</script>

<template>
  <div class="wiki-home" :aria-busy="loading">
    <div class="home-cover"><img :src="`${base}assets/logo_mdawiki_capa.svg`" alt="MDAWiki" width="315" height="80" fetchpriority="high"></div>
    <h1 class="home-title">A Wikipedia dos Desbravadores e Aventureiros</h1>

    <p v-if="loading" class="home-loading" role="status"><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>{{ hasData ? 'Atualizando conteúdo da página inicial…' : 'Carregando conteúdo da página inicial…' }}</p>

    <section class="home-panel home-random">
      <header class="home-panel-heading text-center"><h2>Páginas Aleatórias</h2></header>
      <div class="home-random-body">
        <article v-for="item in home.random" :key="item.id" class="home-random-card">
          <RouterLink v-if="homeImage(item.image, snapshot.apiBaseUrl, snapshot.siteUrl)" class="home-random-image" :to="`/${encodeURIComponent(item.slug)}/`" tabindex="-1" aria-hidden="true"><img :src="homeImage(item.image, snapshot.apiBaseUrl, snapshot.siteUrl)" alt="" width="80" loading="lazy"></RouterLink>
          <RouterLink class="home-random-text" :to="`/${encodeURIComponent(item.slug)}/`"><h3>{{ item.title }}</h3><p v-if="item.description">{{ item.description }}</p></RouterLink>
        </article>
        <p v-if="!loading && !failed && !home.random.length" class="home-empty">Nenhuma página para exibir no momento.</p>
      </div>
    </section>

    <div class="row g-4 home-rankings">
      <div class="col-md-4"><HomeList title="Top 10 de Hoje" :pending="loading || failed" :items="home.top_today" /></div>
      <div class="col-md-4"><HomeList title="Top 10 dos últimos 7 dias" :pending="loading || failed" :items="home.top_week" /></div>
      <div class="col-md-4"><HomeList title="Top 10 dos últimos 30 dias" :pending="loading || failed" :items="home.top_month" /></div>
    </div>
    <div class="row g-4 home-contributions">
      <div class="col-md-6"><HomeList title="Nos ajude com esses conteúdos" :pending="loading || failed" :items="home.missing" danger /></div>
      <div class="col-md-6"><HomeList title="Últimas alterações" :pending="loading || failed" :items="home.recent" /></div>
    </div>
    <div v-if="failed" class="alert alert-warning home-update-error" role="status">{{ hasData ? 'Não foi possível atualizar a página inicial. Exibindo os dados salvos.' : 'Não foi possível carregar a página inicial.' }} <button class="btn btn-sm btn-outline-secondary ms-2" :disabled="loading" @click="refresh">Tentar novamente</button></div>
  </div>
</template>

