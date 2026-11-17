<script setup lang="ts">
import { computed } from 'vue'
import type { Page } from '../types/page'
const props = defineProps<{ page: Page }>()
const panels = computed(() => [
  { key: 'categories', title: 'Categorias', links: props.page.categories || [], color: 'blue', tags: true },
  { key: 'mentions', title: 'Menções à esta página', links: props.page.mentions || [], color: 'cyan', tags: false },
  { key: 'related', title: 'Páginas Relacionadas', links: props.page.related_pages || [], color: 'green', tags: false },
  { key: 'missing', title: 'Links inexistentes', links: props.page.missing_links || [], color: 'red', tags: false },
].filter(panel => panel.links.length))
function path(slug: string) {
  const [name, ...hash] = slug.split('#')
  return '/' + encodeURIComponent(name!) + '/' + (hash.length ? '#' + encodeURIComponent(hash.join('#')) : '')
}
</script>

<template>
  <aside v-if="panels.length" class="page-relations" aria-label="Informações relacionadas ao artigo">
    <section v-for="panel in panels" :key="panel.key" class="relation-panel" :class="`relation-${panel.color}`">
      <h2>{{ panel.title }} <span class="relation-count">{{ panel.links.length }}</span></h2>
      <ul :class="{ 'category-tags': panel.tags }"><li v-for="link in panel.links" :key="link.id + link.slug"><RouterLink :to="path(link.slug)">{{ link.title }}</RouterLink></li></ul>
    </section>
  </aside>
</template>
