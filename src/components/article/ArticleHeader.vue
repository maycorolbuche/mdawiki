<script setup>
import WikiLink from '@/components/wiki/WikiLink.vue'
defineProps({ article: { type: Object, required: true } })
</script>
<template>
  <header>
    <nav v-if="article.categories.length" aria-label="Caminho da página">
      <ol class="breadcrumb">
        <li
          v-for="category in article.categories.slice(0, 2)"
          :key="category.slug"
          class="breadcrumb-item"
        >
          <WikiLink :link="category" />
        </li>
      </ol>
    </nav>
    <span v-if="article.type !== 'home'" class="category-label">{{
      { specialty: 'Especialidade', category: 'Categoria', media: 'Imagem', article: 'Artigo' }[
        article.type
      ] || 'Página'
    }}</span>
    <h1>{{ article.title }}</h1>
    <p v-if="article.description" class="lead-copy mb-0">{{ article.description }}</p>
    <div v-if="article.contributors.length" class="contributors">
      <div class="avatar-stack" aria-hidden="true">
        <span v-for="person in article.contributors" :key="person.name" class="avatar">{{
          person.initials
        }}</span>
      </div>
      <span>Construído pela comunidade</span>
    </div>
  </header>
</template>
