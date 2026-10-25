<script setup>
defineProps({ article: { type: Object, required: true } })
</script>
<template>
  <header>
    <nav aria-label="Caminho da página">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <RouterLink :to="{ name: 'specialties' }">Especialidades</RouterLink>
        </li>
        <li class="breadcrumb-item active" aria-current="page">{{ article.area }}</li>
      </ol>
    </nav>
    <span class="category-label">ESPECIALIDADE</span>
    <h1>{{ article.title }}</h1>
    <p class="lead-copy mb-0">{{ article.description }}</p>
    <div class="contributors">
      <div class="avatar-stack" aria-hidden="true">
        <span v-for="person in article.contributors" :key="person" class="avatar">{{
          person
        }}</span>
      </div>
      <span>Construído pela comunidade · Prévia do layout</span>
    </div>
  </header>
</template>
