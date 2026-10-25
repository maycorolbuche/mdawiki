<script setup>
import PageCards from './PageCards.vue'
import WikiLink from './WikiLink.vue'
import SafeHtml from './SafeHtml.vue'
import { safeUrl } from '@/utils/wikiRoutes'
defineProps({ blocks: { type: Array, default: () => [] } })
</script>
<template>
  <template v-for="(block, index) in blocks" :key="index">
    <p v-if="block.type === 'paragraph'">{{ block.text }}</p>
    <ol v-else-if="block.type === 'requirements'" class="requirements">
      <li v-for="(item, itemIndex) in block.items" :key="itemIndex">
        {{ item.text }}
        <ol v-if="item.items" class="hazards" type="a">
          <li v-for="text in item.items" :key="text">{{ text }}</li>
        </ol>
        <div v-if="item.links" class="partner-links">
          <WikiLink v-for="link in item.links" :key="link.url || link.slug" :link="link" />
        </div>
      </li>
    </ol>
    <div v-else-if="block.type === 'resources'" class="study-grid">
      <WikiLink
        v-for="item in block.items"
        :key="item.url || item.slug"
        :link="item"
        class="resource"
        ><i :class="`bi bi-${item.icon || 'book'}`" aria-hidden="true" /><span class="flex-grow-1"
          ><strong>{{ item.title }}</strong
          ><small>{{ item.description }}</small></span
        ><i class="bi bi-arrow-up-right small" aria-hidden="true"
      /></WikiLink>
    </div>
    <ol v-else-if="block.type === 'references'" class="references">
      <li v-for="item in block.items" :key="item.url || item.slug"><WikiLink :link="item" /></li>
    </ol>
    <ul v-else-if="block.type === 'links'">
      <li v-for="item in block.items" :key="item.slug || item.url"><WikiLink :link="item" /></li>
    </ul>
    <ul v-else-if="block.type === 'list'">
      <li v-for="item in block.items" :key="item">{{ item }}</li>
    </ul>
    <PageCards v-else-if="block.type === 'page-cards'" :items="block.items" />
    <figure v-else-if="block.type === 'image' && safeUrl(block.url)">
      <img :src="safeUrl(block.url)" :alt="block.alt || ''" loading="lazy" class="img-fluid" />
      <figcaption>{{ block.caption }}</figcaption>
    </figure>
    <SafeHtml v-else-if="block.type === 'html'" :html="block.html" />
    <p v-else class="demo-note">Este bloco de conteúdo ainda não é suportado nesta interface.</p>
  </template>
</template>
