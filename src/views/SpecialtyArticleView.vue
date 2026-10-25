<script setup>
import { nextTick, onUnmounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { BAlert, BButton, BTab, BTabs } from 'bootstrap-vue-next'
import { articleService } from '@/services/articleService'
import { useUiStore } from '@/stores/ui'
import { useClipboard } from '@/composables/useClipboard'
import ArticleHeader from '@/components/article/ArticleHeader.vue'
import ArticleContent from '@/components/article/ArticleContent.vue'
import ArticleDialogs from '@/components/article/ArticleDialogs.vue'
import ArticleSidebar from '@/components/layout/ArticleSidebar.vue'
const props = defineProps({ slug: { type: String, required: true } })
const route = useRoute()
const ui = useUiStore()
const article = ref(null)
const loading = ref(true)
const error = ref('')
const tab = ref(0)
const dialogs = ref(null)
const { feedback, copy } = useClipboard()
const pageUrl = () => window.location.href.split('#')[0]
let requestId = 0
onUnmounted(() => {
  requestId++
})
async function load() {
  const id = ++requestId
  loading.value = true
  error.value = ''
  article.value = null
  tab.value = 0
  try {
    const data = await articleService.findBySlug(props.slug)
    if (id !== requestId) return
    article.value = data
    document.title = `${data?.title ?? 'Especialidade não encontrada'} · MDAWiki`
  } catch {
    if (id === requestId) error.value = 'Não foi possível carregar esta especialidade.'
  } finally {
    if (id === requestId) {
      loading.value = false
      await nextTick()
      if (route.hash) document.getElementById(route.hash.slice(1))?.scrollIntoView()
    }
  }
}
watch(() => props.slug, load, { immediate: true })
watch(
  () => route.hash,
  () => {
    if (route.hash) tab.value = 0
  },
)
async function printArticle() {
  tab.value = 0
  await nextTick()
  window.print()
}
</script>
<template>
  <div v-if="loading" class="main" role="status">Carregando especialidade…</div>
  <main v-else-if="error" id="conteudo" class="main" tabindex="-1">
    <h1>Falha ao carregar</h1>
    <p role="alert">{{ error }}</p>
    <BButton variant="outline-success" @click="load">Tentar novamente</BButton>
  </main>
  <main v-else-if="!article" id="conteudo" class="main" tabindex="-1">
    <h1>Especialidade não encontrada</h1>
    <p>Este artigo ainda não está disponível.</p>
    <RouterLink :to="{ name: 'specialties' }">Ver especialidades</RouterLink>
  </main>
  <div v-else class="content-layout">
    <main id="conteudo" class="main" tabindex="-1">
      <ArticleHeader :article="article" />
      <div class="article-actions">
        <BButton variant="link" size="sm" @click="dialogs.edit()"
          ><i class="bi bi-pencil me-1" aria-hidden="true" />Editar página</BButton
        ><BButton variant="link" size="sm" @click="dialogs.history()"
          ><i class="bi bi-clock-history me-1" aria-hidden="true" />Histórico</BButton
        ><BButton variant="link" size="sm" aria-label="Imprimir página" @click="printArticle"
          ><i class="bi bi-printer" aria-hidden="true" /></BButton
        ><BButton variant="link" size="sm" aria-label="Copiar link" @click="copy(pageUrl())"
          ><i class="bi bi-link-45deg" aria-hidden="true" /></BButton
        ><BButton variant="link" size="sm" aria-label="Citar página" @click="dialogs.cite()"
          ><i class="bi bi-quote" aria-hidden="true"
        /></BButton>
      </div>
      <BTabs v-model:index="tab" class="article-tabs"
        ><BTab title="Artigo"
          ><ArticleContent :article="article" @edit="dialogs.edit($event)" /></BTab
        ><BTab title="Discussão"
          ><h2 class="mb-3">Discussão da especialidade</h2>
          <p class="text-secondary">
            Espaço para conversar sobre fontes, requisitos e melhorias no artigo.
          </p>
          <BAlert :model-value="true" variant="light"
            >Nenhum tópico nesta demonstração. A publicação de discussões será integrada à
            API.</BAlert
          ><BButton class="btn-forest" @click="ui.helpOpen = true">Como participar</BButton></BTab
        ></BTabs
      >
    </main>
    <ArticleSidebar @print="printArticle" @copy="copy(pageUrl())" @cite="dialogs.cite()" />
    <ArticleDialogs ref="dialogs" :article="article" />
    <BAlert
      v-if="feedback"
      :model-value="true"
      dismissible
      variant="light"
      class="feedback shadow border"
      @closed="feedback = ''"
      >{{ feedback }}</BAlert
    >
  </div>
</template>
