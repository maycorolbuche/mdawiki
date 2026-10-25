<script setup>
import { computed, ref } from 'vue'
import { BButton, BFormTextarea, BModal } from 'bootstrap-vue-next'
import { useClipboard } from '@/composables/useClipboard'
const props = defineProps({ article: { type: Object, required: true } })
const editOpen = ref(false)
const historyOpen = ref(false)
const citationOpen = ref(false)
const section = ref('')
const suggestion = ref('')
const { feedback, copy } = useClipboard()
const citation = computed(
  () =>
    `MDAWIKI. ${props.article.title}. Disponível em: ${window.location.href.split('#')[0]}. Acesso em: ${new Date().toLocaleDateString('pt-BR')}. Conteúdo demonstrativo.`,
)
function edit(id = 'página') {
  section.value = id
  const requirements = props.article.requirements
    .map(
      (item, index) =>
        `${index + 1}. ${item.text}${item.items ? '\n' + item.items.join('\n') : ''}`,
    )
    .join('\n\n')
  const resources = props.article.resources.map((item) => `${item.title}: ${item.url}`).join('\n')
  const references = props.article.references.map((item) => `${item.title} ${item.url}`).join('\n')
  suggestion.value =
    { requisitos: requirements, materiais: resources, referencias: references }[id] ??
    [props.article.title, props.article.description, requirements, resources, references].join(
      '\n\n',
    )
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
  history: () => {
    historyOpen.value = true
  },
  cite: () => {
    feedback.value = ''
    citationOpen.value = true
  },
})
</script>
<template>
  <BModal v-model="editOpen" :title="`Editar ${section}`" size="lg" centered
    ><p class="small text-secondary">
      Prévia do editor. Baixe sua sugestão; nenhuma alteração será publicada.
    </p>
    <label for="suggestion" class="form-label">Conteúdo da sugestão</label
    ><BFormTextarea id="suggestion" v-model="suggestion" rows="12" /><template #footer
      ><BButton variant="light" @click="editOpen = false">Cancelar</BButton
      ><BButton variant="success" @click="download">Baixar sugestão (.txt)</BButton></template
    ></BModal
  >
  <BModal v-model="historyOpen" title="Histórico da página" centered ok-only ok-title="Fechar"
    ><p class="demo-note">Revisões fictícias da demonstração.</p>
    <ul class="list-group">
      <li v-for="revision in article.revisions" :key="revision.id" class="list-group-item">
        <strong>{{ revision.date.split('-').reverse().join('/') }}</strong> · {{ revision.author }}
        <p class="mb-0 mt-1">{{ revision.summary }}</p>
      </li>
    </ul></BModal
  >
  <BModal v-model="citationOpen" title="Citar esta página" centered
    ><label for="citation" class="form-label">Referência sugerida</label
    ><BFormTextarea id="citation" :model-value="citation" rows="5" readonly />
    <p class="small mt-2 mb-0" role="status">{{ feedback }}</p>
    <template #footer
      ><BButton variant="success" @click="copy(citation)">Copiar referência</BButton></template
    ></BModal
  >
</template>
