<script setup>
import WikiLink from './WikiLink.vue'
defineProps({ items: { type: Array, default: () => [] } })
</script>
<template>
  <div class="catalog-grid">
    <article v-for="item in items" :key="item.slug" class="catalog-card">
      <span v-if="item.type" class="category-label">{{ item.type }}</span>
      <h2><WikiLink :link="item" /></h2>
      <p v-if="item.description" class="text-secondary mb-0">{{ item.description }}</p>
    </article>
  </div>
</template>
