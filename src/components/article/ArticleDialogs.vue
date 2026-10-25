<script setup>
import { computed, ref } from 'vue'
import { BButton, BFormTextarea, BModal } from 'bootstrap-vue-next'
import { useClipboard } from '@/composables/useClipboard'
import { useAsyncResource } from '@/composables/useAsyncResource'
import ResourceState from '@/components/common/ResourceState.vue'
import { wikiService } from '@/services/wikiService'
const props = defineProps({ article: { type: Object, required: true } })
const editOpen = ref(false)
const citationOpen = ref(false)
const section = ref('página')
const suggestion = ref('')
const { feedback, copy } = useClipboard()
const source = useAsyncResource(
  () => (editOpen.value ? props.article.id : null),
  async (id, options) => {
    const result = await wikiService.source(id, options)
    suggestion.value = result.text
    return result
  },
)
const citation = computed(
  () =>
    `MDAWIKI. ${props.article.title}. Disponível em: ${window.location.href.split('?')[0].split('#')[0]}. Acesso em: ${new Date().toLocaleDateString('pt-BR')}.`,
)
function edit(id = 'página') {
  section.value = id
  editOpen.value = true
}
function download() {
  const blob = new Blob(
    [`Sugestão para: ${props.article.title} — ${section.value}\n\n${suggestion.value}`],
    { type: 'text/plain;charset=utf-8' },
  )
  const url = URL.createObjectURL(blob)
  const anchor = document.createElement('a')
  anchor.href = url
  anchor.download = `sugestao-${props.article.slug}.txt`
  anchor.click()
  setTimeout(() => URL.revokeObjectURL(url), 1000)
}
defineExpose({
  edit,
  cite: () => {
    feedback.value = ''
    citationOpen.value = true
  },
})
</script>
<template>
  <BModal v-model="editOpen" :title="`Sugerir alteração — ${section}`" size="lg" centered
    ><p class="small text-secondary">
      Conteúdo-fonte recebido da API. Baixe sua sugestão; nenhuma alteração será publicada.
    </p>
    <ResourceState
      :loading="source.loading.value"
      :error="source.error.value"
      @retry="source.reload"
      ><label for="suggestion" class="form-label">Conteúdo da sugestão</label
      ><BFormTextarea id="suggestion" v-model="suggestion" rows="12" /></ResourceState
    ><template #footer
      ><BButton variant="light" @click="editOpen = false">Cancelar</BButton
      ><BButton
        variant="success"
        :disabled="source.loading.value || !!source.error.value"
        @click="download"
        >Baixar sugestão (.txt)</BButton
      ></template
    ></BModal
  ><BModal v-model="citationOpen" title="Citar esta página" centered
    ><label for="citation" class="form-label">Referência sugerida</label
    ><BFormTextarea id="citation" :model-value="citation" rows="5" readonly />
    <p class="small mt-2 mb-0" role="status">{{ feedback }}</p>
    <template #footer
      ><BButton variant="success" @click="copy(citation)">Copiar referência</BButton></template
    ></BModal
  >
</template>
