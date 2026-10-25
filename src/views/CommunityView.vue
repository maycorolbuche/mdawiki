<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
const route = useRoute()
const descriptions = {
  pathfinders: 'Aprendizado, serviço e aventura para os Desbravadores.',
  adventurers: 'Descobertas e atividades para aprender em família.',
  'recent-changes': 'Acompanhe as contribuições que constroem a MDAWiki.',
  contributors: 'Pessoas que compartilham conhecimento e experiências.',
  help: 'Saiba como explorar e colaborar com a MDAWiki.',
}
const description = computed(() => descriptions[route.name])
</script>
<template>
  <main id="conteudo" class="main" tabindex="-1">
    <header class="page-heading">
      <span class="category-label">MDAWIKI</span>
      <h1>{{ route.meta.title }}</h1>
      <p class="lead-copy">{{ description }}</p>
    </header>
    <template v-if="route.name === 'help'"
      ><h2 class="mb-3">Como usar</h2>
      <p>
        Explore Especialidades no menu ou pesquise pelo título na barra superior. No artigo, use o
        índice para navegar e consulte os materiais de estudo.
      </p>
      <h2 class="my-3">Como colaborar</h2>
      <p>
        Confira as fontes oficiais e prepare sua sugestão em “Editar seção”. Nesta prévia é possível
        baixar a sugestão, mas ela não será publicada.
      </p></template
    >
    <ul v-else-if="route.name === 'recent-changes'" class="list-group">
      <li class="list-group-item">
        25/10/2026 · Equipe MDAWiki · Criação da prévia do artigo de ADRA (exemplo).
      </li>
    </ul>
    <div v-else-if="route.name === 'contributors'" class="catalog-card">
      <h2>Equipe MDAWiki</h2>
      <p class="mb-0 mt-2">Colaborador fictício da demonstração.</p>
    </div>
    <div v-else class="catalog-card">
      <h2>Explore e aprenda</h2>
      <p class="mt-3">
        Esta seção receberá os conteúdos da comunidade quando a API estiver disponível.
      </p>
      <RouterLink :to="{ name: 'specialties' }">Ver especialidades disponíveis</RouterLink>
    </div>
  </main>
</template>
