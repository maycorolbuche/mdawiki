<script setup>
import WikiLink from './WikiLink.vue'
defineProps({
  title: { type: String, required: true },
  items: { type: Array, default: () => [] },
  missing: Boolean,
  categories: Boolean,
})
</script>
<template>
  <section
    v-if="items.length"
    class="side-card relation-card"
    :class="{ 'relation-card-missing': missing }"
    :aria-label="title"
  >
    <h2>
      <span>{{ title }}</span
      ><span class="relation-count" :aria-label="`${items.length} itens`">{{ items.length }}</span>
    </h2>
    <div v-if="categories" class="relation-tags">
      <WikiLink v-for="link in items" :key="link.slug" :link="link" class="tag" />
    </div>
    <ul v-else class="relation-list">
      <li v-for="link in items" :key="link.slug"><WikiLink :link="link" :missing="missing" /></li>
    </ul>
  </section>
</template>
