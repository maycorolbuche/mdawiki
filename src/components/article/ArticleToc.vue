<script setup>
import { useRoute } from 'vue-router'
import { validAnchor } from '@/utils/wikiRoutes'
defineProps({
  sections: { type: Array, default: () => [] },
  activeSection: { type: String, default: '' },
})
const route = useRoute()
</script>
<template>
  <nav class="toc mt-3" aria-label="Índice do artigo">
    <RouterLink
      v-for="section in sections.filter((section) => validAnchor(section.id))"
      :key="section.id"
      :to="{ path: route.path, query: {}, hash: `#${section.id}` }"
      :class="{ active: section.id === activeSection }"
      :aria-current="section.id === activeSection ? 'location' : undefined"
      >{{ section.label }}</RouterLink
    >
  </nav>
</template>
