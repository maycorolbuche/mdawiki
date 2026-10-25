<script setup>
import { computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { BButton } from 'bootstrap-vue-next'
import { useSiteStore } from '@/stores/site'
import { wikiService } from '@/services/wikiService'
import { useAsyncResource } from '@/composables/useAsyncResource'
import ResourceState from '@/components/common/ResourceState.vue'
import PageCards from '@/components/wiki/PageCards.vue'
const route = useRoute()
const siteStore = useSiteStore()
watch(
  () => route.query.q,
  () => {
    document.title = `Busca · ${siteStore.site.name}`
    document.querySelector('link[rel="canonical"]')?.remove()
  },
  { immediate: true },
)
const q = computed(() => (typeof route.query.q === 'string' ? route.query.q.trim() : ''))
const page = computed(() => Math.max(1, Number(route.query.pagina) || 1))
const { data, loading, error, reload } = useAsyncResource(
  () => JSON.stringify([q.value, page.value]),
  (_, options) =>
    q.value
      ? wikiService.search({ q: q.value, page: page.value, perPage: 10 }, options)
      : Promise.resolve({ data: [], meta: { total: 0 } }),
)
</script>
<template>
  <main id="conteudo" class="main" tabindex="-1">
    <h1>Buscar na wiki</h1>
    <p v-if="q" class="lead-copy">Resultados para: {{ q }}</p>
    <p v-else>Digite um termo na busca do topo.</p>
    <ResourceState :loading="loading" :error="error" @retry="reload"
      ><template v-if="q"
        ><p>{{ data?.meta.total }} resultado(s).</p>
        <PageCards v-if="data?.data.length" :items="data.data" />
        <p v-else>Nenhuma página encontrada.</p>
        <nav class="d-flex gap-3 my-4" aria-label="Paginação da busca">
          <BButton
            v-if="page > 1"
            variant="outline-success"
            :to="{ name: 'search', query: { q, pagina: page - 1 } }"
            >Anterior</BButton
          ><BButton
            v-if="data?.meta.hasMore"
            variant="outline-success"
            :to="{ name: 'search', query: { q, pagina: page + 1 } }"
            >Próxima</BButton
          >
        </nav></template
      ></ResourceState
    >
  </main>
</template>
