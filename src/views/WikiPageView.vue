<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { BAlert, BButton } from 'bootstrap-vue-next'
import { wikiService } from '@/services/wikiService'
import { useSiteStore } from '@/stores/site'
import { useAsyncResource } from '@/composables/useAsyncResource'
import { useClipboard } from '@/composables/useClipboard'
import { pageLocation, validAnchor } from '@/utils/wikiRoutes'
import ArticleHeader from '@/components/article/ArticleHeader.vue'
import ArticleContent from '@/components/article/ArticleContent.vue'
import ArticleDialogs from '@/components/article/ArticleDialogs.vue'
import ArticleSidebar from '@/components/layout/ArticleSidebar.vue'
import PageActivity from '@/components/wiki/PageActivity.vue'
import ResourceState from '@/components/common/ResourceState.vue'
const props = defineProps({ slug: { type: String, default: '' } })
const route = useRoute()
const router = useRouter()
const store = useSiteStore()
const {
  data: article,
  loading,
  error,
  reload,
} = useAsyncResource(
  () => props.slug,
  (slug, options) => wikiService.page(slug, options),
)
const dialogs = ref(null)
const redirectedFrom = ref('')
let canonicalTarget = null
const { feedback, copy } = useClipboard()
const action = computed(() =>
  ['historico', 'discussao'].includes(route.query.acao) ? route.query.acao : 'artigo',
)
const pageUrl = () =>
  `${window.location.origin}${router.resolve(pageLocation(article.value.slug)).href}`
watch(article, async (value) => {
  if (!value) {
    document.title = store.site.name
    document.querySelector('link[rel="canonical"]')?.remove()
    return
  }
  document.title = `${value.title} · ${store.site.name}`
  const description = document.querySelector('meta[name="description"]')
  description?.setAttribute('content', value.description || value.title)
  let canonical = document.querySelector('link[rel="canonical"]')
  if (!canonical) {
    canonical = document.createElement('link')
    canonical.rel = 'canonical'
    document.head.append(canonical)
  }
  canonical.href = pageUrl()
  if (value.slug !== props.slug) {
    redirectedFrom.value = value.redirectFrom || props.slug
    canonicalTarget = value.slug
    await router.replace({ ...pageLocation(value.slug, route.query), hash: route.hash })
  } else if (!value.redirectFrom && !redirectedFrom.value) redirectedFrom.value = ''
  await nextTick()
  if (validAnchor(route.hash.slice(1)))
    document.getElementById(route.hash.slice(1))?.scrollIntoView()
})
watch(
  () => props.slug,
  () => {
    if (props.slug !== canonicalTarget) redirectedFrom.value = ''
    canonicalTarget = null
  },
)
async function printArticle() {
  await router.replace(pageLocation(article.value.slug))
  await nextTick()
  window.print()
}
</script>
<template>
  <main v-if="loading || error" id="conteudo" class="main" tabindex="-1">
    <template v-if="error?.status === 404"
      ><h1>Página não encontrada</h1>
      <p>Este endereço ainda não possui uma página na wiki.</p>
      <RouterLink :to="pageLocation('')">Voltar ao início</RouterLink></template
    ><ResourceState v-else :loading="loading" :error="error" @retry="reload" />
  </main>
  <div v-else-if="article" class="content-layout">
    <main id="conteudo" class="main" tabindex="-1">
      <p v-if="redirectedFrom" class="small text-secondary">
        Redirecionado de {{ redirectedFrom }}.
      </p>
      <ArticleHeader :article="article" />
      <div v-if="article.type !== 'home'" class="article-actions">
        <BButton
          v-if="article.capabilities.suggest"
          variant="link"
          size="sm"
          @click="dialogs.edit()"
          >Sugerir alteração</BButton
        ><BButton
          v-if="article.capabilities.history"
          variant="link"
          size="sm"
          :to="pageLocation(article.slug, { acao: 'historico' })"
          >Histórico</BButton
        ><BButton variant="link" size="sm" aria-label="Imprimir página" @click="printArticle"
          ><i class="bi bi-printer" aria-hidden="true" /></BButton
        ><BButton variant="link" size="sm" aria-label="Copiar link" @click="copy(pageUrl())"
          ><i class="bi bi-link-45deg" aria-hidden="true" /></BButton
        ><BButton variant="link" size="sm" aria-label="Citar página" @click="dialogs.cite()"
          ><i class="bi bi-quote" aria-hidden="true"
        /></BButton>
      </div>
      <nav
        v-if="article.type !== 'home'"
        class="wiki-page-tabs nav nav-tabs mb-4"
        aria-label="Ações da página"
      >
        <RouterLink
          class="nav-link"
          active-class=""
          exact-active-class=""
          :class="{ active: action === 'artigo' }"
          :aria-current="action === 'artigo' ? 'page' : undefined"
          :to="pageLocation(article.slug)"
          >Artigo</RouterLink
        ><RouterLink
          v-if="article.capabilities.discussion"
          class="nav-link"
          active-class=""
          exact-active-class=""
          :class="{ active: action === 'discussao' }"
          :to="pageLocation(article.slug, { acao: 'discussao' })"
          >Discussão</RouterLink
        >
      </nav>
      <PageActivity
        v-if="
          (action === 'historico' && article.capabilities.history) ||
          (action === 'discussao' && article.capabilities.discussion)
        "
        :page-id="article.id"
        :kind="action === 'historico' ? 'revisions' : 'discussions'"
      /><ArticleContent v-else :article="article" @edit="dialogs.edit($event)" />
    </main>
    <ArticleSidebar
      :article="article"
      @print="printArticle"
      @copy="copy(pageUrl())"
      @cite="dialogs.cite()"
    /><ArticleDialogs ref="dialogs" :key="article.id" :article="article" /><BAlert
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
