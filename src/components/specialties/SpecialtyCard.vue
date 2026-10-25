<script setup>
defineProps({ article: { type: Object, required: true } })
</script>
<template>
  <article class="catalog-card">
    <span class="category-label">{{ article.area }} · Nível {{ article.level }}</span>
    <h2>
      <RouterLink :to="{ name: 'specialty', params: { slug: article.slug } }">{{
        article.title
      }}</RouterLink>
    </h2>
    <p class="text-secondary">{{ article.description }}</p>
    <RouterLink :to="{ name: 'specialty', params: { slug: article.slug } }"
      >Explorar especialidade <i class="bi bi-arrow-right" aria-hidden="true"
    /></RouterLink>
  </article>
</template>
