<script setup lang="ts">
import { computed, inject, onMounted, ref } from 'vue'
import type { SessionClient } from '../services/session'

type Link = { slug: string; title: string }
type Page = Link & { id: number | string; characters: number | string; categories: Link[] }
type Result = { items: Page[]; total: number; page: number; page_size: number }

const api = inject<SessionClient>('session')!
const result = ref<Result>()
const search = ref(''), appliedSearch = ref(''), content = ref('')
const busy = ref(false), error = ref(''), success = ref('')
const selected = ref<Page>()
const pages = computed(() => result.value ? Math.max(1, Math.ceil(result.value.total / result.value.page_size)) : 1)
const path = (slug: string) => '/' + encodeURIComponent(slug) + '/'
const number = new Intl.NumberFormat('pt-BR')

async function load(page = 1) {
  busy.value = true; error.value = ''
  try {
    const query = new URLSearchParams({ q: appliedSearch.value, content: content.value, page: String(page) })
    result.value = await api.request('/admin/pages?' + query)
  } catch (failure) {
    error.value = (failure as Error).message
  } finally { busy.value = false }
}

function applyFilters() {
  appliedSearch.value = search.value
  void load()
}

async function confirm() {
  if (!selected.value) return
  busy.value = true; error.value = ''; success.value = ''
  try {
    await api.request('/admin/pages', 'POST', { id: selected.value.id })
    selected.value = undefined
    success.value = 'Página e histórico excluídos.'
    await load(result.value?.page)
  } catch (failure) {
    error.value = (failure as Error).message
  } finally { busy.value = false }
}
onMounted(() => { void load() })
</script>

<template>
  <RouterLink to="/a">Voltar à administração</RouterLink>
  <form class="row g-2 my-3 align-items-end" @submit.prevent="applyFilters">
    <div class="col-sm">
      <label for="admin-page-search" class="form-label">Buscar pelo nome da página</label>
      <div class="input-group">
        <input id="admin-page-search" v-model="search" class="form-control" type="search" maxlength="250">
        <button type="submit" class="btn btn-outline-primary" :disabled="busy || !!selected">Buscar</button>
      </div>
    </div>
    <div class="col-sm-4">
      <label for="admin-page-content" class="form-label">Conteúdo</label>
      <select id="admin-page-content" v-model="content" class="form-select" :disabled="busy || !!selected" @change="applyFilters">
        <option value="">Todas</option><option value="yes">Com conteúdo</option><option value="no">Sem conteúdo</option>
      </select>
    </div>
  </form>
  <p v-if="busy" role="status">Processando…</p>
  <p v-if="error" class="alert alert-danger" role="alert">{{ error }}</p>
  <p v-if="success" class="alert alert-success" role="status">{{ success }}</p>
  <div v-if="selected" class="alert alert-warning" role="region" aria-label="Confirmar exclusão">
    <p>Excluir definitivamente <strong>{{ selected.title }}</strong> e todas as revisões do histórico?</p>
    <button type="button" class="btn btn-danger me-2" :disabled="busy" @click="confirm">Confirmar exclusão</button>
    <button type="button" class="btn btn-outline-secondary" :disabled="busy" @click="selected = undefined">Cancelar</button>
  </div>
  <template v-if="result">
    <p v-if="!result.items.length">Nenhuma página encontrada.</p>
    <div v-else class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th scope="col">Nome</th><th scope="col" title="Caracteres do código-fonte da página">Caracteres</th><th scope="col">Categorias</th><th scope="col">Ação</th></tr></thead>
        <tbody>
          <tr v-for="item in result.items" :key="item.id">
            <td class="text-break"><RouterLink :to="path(item.slug)" target="_blank" rel="noopener">{{ item.title }} <i class="bi bi-box-arrow-up-right" aria-hidden="true" /></RouterLink></td>
            <td>{{ number.format(Number(item.characters)) }}</td>
            <td>{{ item.categories.map(category => category.title).join(', ') || '—' }}</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" :disabled="busy || !!selected" @click="selected = item">Excluir definitivamente</button></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="pages > 1" class="d-flex align-items-center gap-2">
      <button type="button" class="btn btn-outline-secondary" :disabled="busy || !!selected || result.page <= 1" @click="load(result.page - 1)">Anterior</button>
      <span>{{ result.page }} de {{ pages }}</span>
      <button type="button" class="btn btn-outline-secondary" :disabled="busy || !!selected || result.page >= pages" @click="load(result.page + 1)">Próxima</button>
    </div>
  </template>
</template>
