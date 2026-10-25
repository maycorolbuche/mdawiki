<script setup>
import { BButton } from 'bootstrap-vue-next'
import ArticleToc from '@/components/article/ArticleToc.vue'
import { useArticleToc } from '@/composables/useArticleToc'
import { useUiStore } from '@/stores/ui'
defineEmits(['print', 'copy', 'cite'])
const { activeSection } = useArticleToc()
const ui = useUiStore()
</script>
<template>
  <aside class="rightbar" aria-label="Índice e ferramentas">
    <div class="right-inner">
      <div class="side-card">
        <h2>Nesta página</h2>
        <ArticleToc :active-section="activeSection" />
      </div>
      <div class="side-card contribute-card">
        <h2><i class="bi bi-lightbulb me-2" aria-hidden="true" />Compartilhe o que você sabe</h2>
        <p>Ajude a melhorar esta especialidade com fontes e experiências.</p>
        <BButton variant="outline-success" size="sm" class="w-100" @click="ui.helpOpen = true"
          >Como colaborar <i class="bi bi-arrow-up-right ms-2" aria-hidden="true"
        /></BButton>
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
    </div>
  </aside>
</template>
