<script setup>
import { articleSections } from '@/data/navigation'
defineProps({ activeSection: { type: String, default: '' } })
</script>
<template>
  <nav class="toc mt-3" aria-label="Índice do artigo">
    <RouterLink
      v-for="section in articleSections"
      :key="section.id"
      :to="{ hash: `#${section.id}` }"
      :class="{ active: section.id === activeSection }"
      :aria-current="section.id === activeSection ? 'location' : undefined"
      >{{ section.label }}</RouterLink
    >
  </nav>
</template>
