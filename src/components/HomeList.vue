<script setup lang="ts">
import { inject } from 'vue'
import type { HomeItem } from '../types/home'
import { homeImage } from '../types/home'
import type { Snapshot } from '../types/page'
defineProps<{ title: string; items: HomeItem[]; danger?: boolean }>()
const snapshot = inject<Snapshot>('snapshot')!
</script>

<template>
  <section class="home-panel">
    <header class="home-panel-heading"><h2>{{ title }}</h2></header>
    <table v-if="items.length" class="table home-table mb-0" :aria-label="title">
      <tbody><tr v-for="item in items" :key="item.id">
        <td class="home-table-image"><RouterLink v-if="homeImage(item.image, snapshot.apiBaseUrl, snapshot.siteUrl)" :to="`/${encodeURIComponent(item.slug)}/`" tabindex="-1" aria-hidden="true"><img :src="homeImage(item.image, snapshot.apiBaseUrl, snapshot.siteUrl)" alt="" width="30" loading="lazy"></RouterLink></td>
        <td><RouterLink :to="`/${encodeURIComponent(item.slug)}/`" :class="{ 'text-danger': danger }">{{ item.title }}</RouterLink></td>
      </tr></tbody>
    </table>
    <p v-else class="home-empty">Nenhuma página para exibir no momento.</p>
  </section>
</template>
