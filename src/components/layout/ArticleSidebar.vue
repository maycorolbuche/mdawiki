<script setup>
import { BButton } from 'bootstrap-vue-next'
import ArticleToc from '@/components/article/ArticleToc.vue'
import WikiLink from '@/components/wiki/WikiLink.vue'
import { useArticleToc } from '@/composables/useArticleToc'
import { useUiStore } from '@/stores/ui'
const props = defineProps({ article: { type: Object, required: true } })
defineEmits(['print', 'copy', 'cite'])
const { activeSection } = useArticleToc(() => props.article.toc)
const ui = useUiStore()
</script>
<template>
  <aside class="rightbar" aria-label="Índice e ferramentas">
    <div class="right-inner">
      <div v-if="article.toc.length" class="side-card">
        <h2>Nesta página</h2>
        <ArticleToc :sections="article.toc" :active-section="activeSection" />
      </div>
      <div class="side-card contribute-card">
        <h2>Compartilhe o que você sabe</h2>
        <p>Ajude a melhorar esta página com fontes e experiências.</p>
        <BButton variant="outline-success" size="sm" class="w-100" @click="ui.helpOpen = true"
          >Como colaborar</BButton
        >
      </div>
      <div class="side-card tools">
        <h2>Ferramentas</h2>
        <button @click="$emit('print')">
          <i class="bi bi-printer" aria-hidden="true" />Imprimir página</button
        ><button @click="$emit('copy')">
          <i class="bi bi-link-45deg" aria-hidden="true" />Copiar link da página</button
        ><button @click="$emit('cite')">
          <i class="bi bi-quote" aria-hidden="true" />Citar esta página
        </button>
      </div>
      <div
        v-for="[label, items] in [
          ['Páginas relacionadas', article.relatedPages],
          ['Páginas que fazem referência', article.backlinks],
          ['Links para páginas inexistentes', article.missingLinks],
        ]"
        :key="label"
      >
        <div v-if="items.length" class="side-card">
          <h2>{{ label }}</h2>
          <ul class="small ps-3">
            <li v-for="link in items" :key="link.slug"><WikiLink :link="link" /></li>
          </ul>
        </div>
      </div>
    </div>
  </aside>
</template>
