<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { BFormInput } from 'bootstrap-vue-next'
import { useRoute } from 'vue-router'
import { articleService } from '@/services/articleService'
const query = ref('')
const results = ref([])
const open = ref(false)
const loading = ref(false)
const failed = ref(false)
const input = ref(null)
const form = ref(null)
const route = useRoute()
const hasQuery = computed(() => query.value.trim().length > 0)
let requestId = 0
watch(query, async (value) => {
  const id = ++requestId
  open.value = Boolean(value.trim())
  if (!value.trim()) {
    results.value = []
    loading.value = false
    return
  }
  loading.value = true
  failed.value = false
  try {
    const data = await articleService.list(value)
    if (id === requestId) results.value = data
  } catch {
    if (id === requestId) failed.value = true
  } finally {
    if (id === requestId) loading.value = false
  }
})
watch(
  () => route.fullPath,
  () => {
    open.value = false
  },
)
function keyboard(event) {
  if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
    event.preventDefault()
    input.value?.focus()
  }
  if (event.key === 'Escape') open.value = false
}
function outside(event) {
  if (!form.value?.contains(event.target)) open.value = false
}
onMounted(() => {
  document.addEventListener('keydown', keyboard)
  document.addEventListener('click', outside)
})
onUnmounted(() => {
  requestId++
  document.removeEventListener('keydown', keyboard)
  document.removeEventListener('click', outside)
})
</script>
<template>
  <form ref="form" class="search-wrap mx-lg-auto" role="search" @submit.prevent="open = hasQuery">
    <label class="visually-hidden" for="wiki-search">Pesquisar especialidades</label
    ><i class="bi bi-search search-icon" aria-hidden="true" />
    <BFormInput
      id="wiki-search"
      ref="input"
      v-model="query"
      type="search"
      placeholder="Pesquisar na MDAWiki…"
      autocomplete="off"
      aria-controls="search-results"
      :aria-expanded="open && hasQuery"
      @focus="open = hasQuery"
    />
    <span class="search-key" aria-hidden="true">Ctrl K</span>
    <div v-if="open && hasQuery" id="search-results" class="search-results" aria-live="polite">
      <p v-if="loading" class="small m-2">Pesquisando…</p>
      <p v-else-if="failed" class="small m-2">Não foi possível pesquisar. Tente novamente.</p>
      <template v-else
        ><RouterLink
          v-for="article in results"
          :key="article.slug"
          :to="{ name: 'specialty', params: { slug: article.slug } }"
          @click="open = false"
          >{{ article.title
          }}<small class="d-block text-secondary">{{ article.area }}</small></RouterLink
        >
        <p v-if="!results.length" class="small m-2">Nenhuma especialidade encontrada.</p></template
      >
    </div>
  </form>
</template>
