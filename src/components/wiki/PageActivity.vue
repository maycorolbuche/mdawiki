<script setup>
import { computed, ref, watch } from 'vue'
import { BButton } from 'bootstrap-vue-next'
import ResourceState from '@/components/common/ResourceState.vue'
import { useAsyncResource } from '@/composables/useAsyncResource'
import { wikiService } from '@/services/wikiService'
const props = defineProps({
  pageId: { type: String, required: true },
  kind: { type: String, required: true },
})
const page = ref(1)
watch(
  () => [props.pageId, props.kind],
  () => {
    page.value = 1
  },
)
const { data, error, loading, reload } = useAsyncResource(
  () => JSON.stringify([props.pageId, props.kind, page.value]),
  (_, options) =>
    wikiService[props.kind](props.pageId, { ...options, query: { page: page.value, perPage: 10 } }),
)
const isHistory = computed(() => props.kind === 'revisions')
</script>
<template>
  <h2 class="mb-3">{{ isHistory ? 'Histórico da página' : 'Discussão da página' }}</h2>
  <ResourceState :loading="loading" :error="error" @retry="reload">
    <ul v-if="data?.data.length" class="list-group">
      <li v-for="entry in data.data" :key="entry.id" class="list-group-item">
        <strong>{{ entry.author }}</strong> ·
        {{ new Date(entry.date).toLocaleString('pt-BR', { timeZone: 'America/Sao_Paulo' }) }}
        <p class="mb-0">{{ entry.summary || entry.text }}</p>
      </li>
    </ul>
    <p v-else>{{ isHistory ? 'Nenhuma revisão disponível.' : 'Nenhum tópico disponível.' }}</p>
    <nav class="d-flex gap-3 my-3" aria-label="Paginação da atividade">
      <BButton v-if="page > 1" variant="outline-success" @click="page--">Anterior</BButton
      ><BButton v-if="data?.meta.hasMore" variant="outline-success" @click="page++"
        >Próxima</BButton
      >
    </nav>
    <p class="small text-secondary mt-3">
      {{
        isHistory
          ? 'Comparação e restauração de versões serão implementadas com a API de edição.'
          : 'A publicação de discussões será implementada com a autenticação da API.'
      }}
    </p>
  </ResourceState>
</template>
