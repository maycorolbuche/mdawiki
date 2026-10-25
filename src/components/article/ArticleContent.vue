<script setup>
import ArticleSection from './ArticleSection.vue'
import SpecialtyMetadata from './SpecialtyMetadata.vue'
import ArticleToc from './ArticleToc.vue'
import WikiBlocks from '@/components/wiki/WikiBlocks.vue'
import WikiLink from '@/components/wiki/WikiLink.vue'
import PageCards from '@/components/wiki/PageCards.vue'
import { safeUrl } from '@/utils/wikiRoutes'
defineProps({ article: { type: Object, required: true } })
defineEmits(['edit'])
</script>
<template>
  <p v-if="article.notice" class="demo-note">{{ article.notice }}</p>
  <SpecialtyMetadata v-if="article.metadata" :metadata="article.metadata" />
  <figure v-if="article.media && safeUrl(article.media.url)" class="wiki-media">
    <img :src="safeUrl(article.media.url)" :alt="article.media.alt" />
    <figcaption>
      {{ article.media.attribution }}
      <span v-if="article.media.width"
        >· {{ article.media.width }} × {{ article.media.height }} px ·
        {{ article.media.format }}</span
      >
    </figcaption>
  </figure>
  <details v-if="article.toc.length" class="mobile-toc border rounded-3 p-3 mb-4">
    <summary class="fw-semibold">Nesta página</summary>
    <ArticleToc :sections="article.toc" />
  </details>
  <ArticleSection
    v-for="section in article.sections"
    :id="section.id"
    :key="section.id"
    :title="section.title"
    :editable="article.capabilities.suggest"
    @edit="$emit('edit', $event)"
    ><WikiBlocks :blocks="section.blocks"
  /></ArticleSection>
  <section v-if="article.type === 'category'">
    <h2 class="mb-3">Páginas nesta categoria</h2>
    <PageCards v-if="article.members.length" :items="article.members" />
    <p v-else>Nenhuma página nesta categoria.</p>
  </section>
  <div v-if="article.categories.length" class="categories mt-4">
    <i class="bi bi-tags" aria-hidden="true" />Categorias:<WikiLink
      v-for="category in article.categories"
      :key="category.slug"
      class="tag"
      :link="category"
    />
  </div>
</template>
