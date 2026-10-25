<script setup>
import { BButton } from 'bootstrap-vue-next'
import SpecialtyCard from './SpecialtyCard.vue'
import { useArticleList } from '@/composables/useArticleList'
const { articles, loading, error, load } = useArticleList()
</script>
<template>
  <p v-if="loading" role="status">Carregando especialidades…</p>
  <div v-else-if="error" role="alert">
    <p>{{ error }}</p>
    <BButton variant="outline-success" @click="load">Tentar novamente</BButton>
  </div>
  <div v-else-if="articles.length" class="catalog-grid">
    <SpecialtyCard v-for="article in articles" :key="article.slug" :article="article" />
  </div>
  <p v-else>Nenhuma especialidade disponível.</p>
</template>
