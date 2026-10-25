<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { BFormInput } from 'bootstrap-vue-next'
import { useRoute, useRouter } from 'vue-router'
import { wikiService } from '@/services/wikiService'
import { pageLocation } from '@/utils/wikiRoutes'
const query = ref('')
const results = ref([])
const open = ref(false)
const loading = ref(false)
const failed = ref(false)
const input = ref(null)
const form = ref(null)
const route = useRoute()
const router = useRouter()
const hasQuery = computed(() => query.value.trim().length > 0)
let requestId = 0
let timer
let controller
watch(query, (value) => {
  const id = ++requestId
  clearTimeout(timer)
  controller?.abort()
  results.value = []
  failed.value = false
  open.value = Boolean(value.trim())
  loading.value = open.value
  if (!open.value) return
  timer = setTimeout(async () => {
    controller = new AbortController()
    try {
      const result = await wikiService.search(
        { q: value.trim(), perPage: 7 },
        { signal: controller.signal },
      )
      if (id === requestId) results.value = result.data
    } catch (error) {
      if (id === requestId && error.name !== 'AbortError') failed.value = true
    } finally {
      if (id === requestId) loading.value = false
    }
  }, 250)
})
watch(
  () => route.fullPath,
  () => {
    open.value = false
  },
)
function search() {
  if (hasQuery.value) {
    open.value = false
    router.push({ name: 'search', query: { q: query.value.trim() } })
  }
}
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
  clearTimeout(timer)
  controller?.abort()
  document.removeEventListener('keydown', keyboard)
  document.removeEventListener('click', outside)
})
</script>
<template>
  <form ref="form" class="search-wrap mx-lg-auto" role="search" @submit.prevent="search">
    <label class="visually-hidden" for="wiki-search">Pesquisar na wiki</label
    ><i class="bi bi-search search-icon" aria-hidden="true" /><BFormInput
      id="wiki-search"
      ref="input"
      v-model="query"
      type="search"
      placeholder="Pesquisar na MDAWiki…"
      autocomplete="off"
      aria-controls="search-results"
      :aria-expanded="open && hasQuery"
      @focus="open = hasQuery"
    /><span class="search-key" aria-hidden="true">Ctrl K</span>
    <div v-if="open && hasQuery" id="search-results" class="search-results" aria-live="polite">
      <p v-if="loading" class="small m-2">Pesquisando…</p>
      <p v-else-if="failed" class="small m-2">Não foi possível pesquisar. Tente novamente.</p>
      <template v-else
        ><RouterLink
          v-for="page in results"
          :key="page.slug"
          :to="pageLocation(page.slug)"
          @click="open = false"
          >{{ page.title
          }}<small class="d-block text-secondary">{{ page.description }}</small></RouterLink
        >
        <p v-if="!results.length" class="small m-2">Nenhuma página encontrada.</p>
        <button type="submit" class="btn btn-link">Ver todos os resultados</button></template
      >
    </div>
  </form>
</template>
