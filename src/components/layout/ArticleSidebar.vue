<script setup>
import { BButton } from 'bootstrap-vue-next'
import ArticleToc from '@/components/article/ArticleToc.vue'
import PageRelationCard from '@/components/wiki/PageRelationCard.vue'
import { useArticleToc } from '@/composables/useArticleToc'
import { useUiStore } from '@/stores/ui'
const props = defineProps({ article: { type: Object, required: true } })
defineEmits(['print', 'copy', 'cite'])
const { activeSection } = useArticleToc(() => props.article.toc)
const ui = useUiStore()
</script>
<template>
  <aside class="rightbar article-sidebar" aria-label="Informações da página">
    <div class="right-inner">
      <section v-if="article.media" class="side-card media-data" aria-label="Dados da imagem">
        <h2>Dados da imagem</h2>
        <dl>
          <div v-if="article.media.width">
            <dt>Largura</dt>
            <dd>{{ article.media.width }} px</dd>
          </div>
          <div v-if="article.media.height">
            <dt>Altura</dt>
            <dd>{{ article.media.height }} px</dd>
          </div>
          <div v-if="article.media.format">
            <dt>Formato</dt>
            <dd>{{ article.media.format }}</dd>
          </div>
        </dl>
      </section>
      <PageRelationCard title="Categorias" :items="article.categories" categories />
      <PageRelationCard title="Páginas não existentes" :items="article.missingLinks" missing />
      <PageRelationCard title="Menções a esta página" :items="article.backlinks" />
      <PageRelationCard title="Páginas relacionadas" :items="article.relatedPages" />
      <section v-if="article.toc.length" class="side-card">
        <h2>Nesta página</h2>
        <ArticleToc :sections="article.toc" :active-section="activeSection" />
      </section>
      <section class="side-card contribute-card">
        <h2>Compartilhe o que você sabe</h2>
        <p>Ajude a melhorar esta página com fontes e experiências.</p>
        <BButton variant="outline-success" size="sm" class="w-100" @click="ui.helpOpen = true"
          >Como colaborar</BButton
        >
      </section>
      <section class="side-card tools">
        <h2>Ferramentas</h2>
        <button @click="$emit('print')">
          <i class="bi bi-printer" aria-hidden="true" />Imprimir página</button
        ><button @click="$emit('copy')">
          <i class="bi bi-link-45deg" aria-hidden="true" />Copiar link da página</button
        ><button @click="$emit('cite')">
          <i class="bi bi-quote" aria-hidden="true" />Citar esta página
        </button>
      </section>
    </div>
  </aside>
</template>
