<script setup>
import ArticleSection from './ArticleSection.vue'
import SpecialtyMetadata from './SpecialtyMetadata.vue'
import ArticleToc from './ArticleToc.vue'
defineProps({ article: { type: Object, required: true } })
defineEmits(['edit'])
</script>
<template>
  <SpecialtyMetadata :article="article" />
  <details class="mobile-toc border rounded-3 p-3 mb-4">
    <summary class="fw-semibold">Nesta página</summary>
    <ArticleToc />
  </details>
  <ArticleSection id="requisitos" title="Requisitos" @edit="$emit('edit', $event)"
    ><p class="demo-note">
      <i class="bi bi-info-circle me-1" aria-hidden="true" />Conteúdo resumido para demonstração do
      layout. Consulte os requisitos oficiais antes de aplicar a especialidade.
    </p>
    <ol class="requirements">
      <li v-for="(requirement, index) in article.requirements" :key="index">
        {{ requirement.text }}
        <ol v-if="requirement.items" class="hazards" type="a">
          <li v-for="item in requirement.items" :key="item">{{ item }}</li>
        </ol>
        <div v-if="requirement.links" class="partner-links">
          <a
            v-for="link in requirement.links"
            :key="link.url"
            :href="link.url"
            target="_blank"
            rel="noopener noreferrer"
            >{{ link.title }} ↗</a
          >
        </div>
      </li>
    </ol></ArticleSection
  >
  <ArticleSection
    id="materiais"
    title="Materiais de estudo"
    icon="book-fill"
    @edit="$emit('edit', $event)"
    ><div class="study-grid">
      <a
        v-for="resource in article.resources"
        :key="resource.url"
        class="resource"
        :href="resource.url"
        target="_blank"
        rel="noopener noreferrer"
        ><i :class="`bi bi-${resource.icon}`" aria-hidden="true" /><span class="flex-grow-1"
          ><strong>{{ resource.title }}</strong
          ><small>{{ resource.description }}</small></span
        ><i class="bi bi-box-arrow-up-right small" aria-hidden="true"
      /></a></div
  ></ArticleSection>
  <ArticleSection id="referencias" title="Referências" icon="book" @edit="$emit('edit', $event)"
    ><ol class="references">
      <li v-for="reference in article.references" :key="reference.url">
        <a :href="reference.url" target="_blank" rel="noopener noreferrer">{{ reference.title }}</a>
      </li>
    </ol></ArticleSection
  >
  <div class="categories">
    <i class="bi bi-tags" aria-hidden="true" />Categorias:<RouterLink
      v-for="category in article.categories"
      :key="category"
      class="tag"
      :to="{ name: 'specialties' }"
      >{{ category }}</RouterLink
    >
  </div>
</template>
