<script setup lang="ts">
import { computed, inject, onMounted, ref } from 'vue'
import type { SessionClient } from '../services/session'
import type { Snapshot } from '../types/page'

type Image = {
  id: number | string; slug: string; title: string | null; created_at: string | null
  width: number | null; height: number | null; extension: string | null
  size_bytes: number | string | null; has_file: boolean; is_public: boolean
}
type Result = { items: Image[]; total: number; page: number; page_size: number }
const api = inject<SessionClient>('session')!
const snapshot = inject<Snapshot>('snapshot')!
const result = ref<Result>()
const order = ref('created_at:desc'), loading = ref(false), error = ref('')
const failed = ref(new Set<string>())
const pages = computed(() => result.value ? Math.max(1, Math.ceil(result.value.total / result.value.page_size)) : 1)
const original = (image: Image) => snapshot.apiBaseUrl.replace(/\/$/, '') + '/v1/admin/images?original=' + encodeURIComponent(image.id)
const title = (image: Image) => image.title || image.slug
const size = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 1, style: 'unit', unit: 'kilobyte' })

async function load(page = 1) {
  loading.value = true
  error.value = ''
  try {
    const [sort, direction] = order.value.split(':')
    result.value = await api.request('/admin/images?' + new URLSearchParams({ page: String(page), sort, direction }))
    failed.value.clear()
  } catch (failure) {
    error.value = (failure as Error).message
  } finally {
    loading.value = false
  }
}

onMounted(() => { void load() })
</script>

<template>
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
    <RouterLink to="/a">Voltar à administração</RouterLink>
    <div class="d-flex gap-2 align-items-center">
      <label for="admin-image-order" class="text-nowrap">Ordenar por</label>
      <select id="admin-image-order" v-model="order" class="form-select" :disabled="loading" @change="load()">
        <option value="created_at:desc">Cadastro: mais recentes</option>
        <option value="created_at:asc">Cadastro: mais antigas</option>
        <option value="size:asc">Tamanho: mais leves</option>
        <option value="size:desc">Tamanho: mais pesadas</option>
      </select>
    </div>
  </div>
  <p v-if="loading" role="status">Carregando imagens…</p>
  <p v-if="error" class="alert alert-danger" role="alert">{{ error }}</p>
  <template v-if="result">
    <p v-if="!result.items.length">Nenhuma imagem cadastrada.</p>
    <div v-else class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xxl-4 g-3">
      <div v-for="image in result.items" :key="image.id" class="col">
        <article class="card h-100 overflow-hidden">
          <a v-if="image.has_file" :href="original(image)" target="_blank" rel="noopener"
            class="image-tile bg-light" :aria-label="'Abrir imagem original de ' + title(image) + ' em nova aba'">
            <img v-if="!failed.has(String(image.id))" :src="original(image)" :alt="title(image)"
              loading="lazy" decoding="async" @error="failed.add(String(image.id))">
            <span v-else>Arquivo indisponível</span>
          </a>
          <div v-else class="image-tile bg-light text-body-secondary">Sem arquivo</div>
          <div class="card-body">
            <h2 class="h6 text-break">{{ title(image) }}</h2>
            <dl class="small mb-2">
              <div><dt class="d-inline">Tamanho</dt><dd class="d-inline ms-2">{{ image.size_bytes == null ? '—' : size.format(Number(image.size_bytes) / 1024) }}</dd></div>
              <div><dt class="d-inline">Dimensões</dt><dd class="d-inline ms-2">{{ image.width && image.height ? image.width + ' × ' + image.height + ' px' : '—' }}</dd></div>
              <div><dt class="d-inline">Formato</dt><dd class="d-inline ms-2">{{ image.extension?.toUpperCase() || '—' }}</dd></div>
              <div><dt class="d-inline">Cadastro</dt><dd class="d-inline ms-2">{{ image.created_at?.slice(0, 10).split('-').reverse().join('/') || '—' }}</dd></div>
            </dl>
            <RouterLink v-if="image.is_public" :to="'/i/' + encodeURIComponent(image.slug)" target="_blank" rel="noopener">Abrir página pública</RouterLink>
            <span v-else class="small text-body-secondary">Imagem restrita</span>
          </div>
        </article>
      </div>
    </div>
    <nav v-if="pages > 1" class="d-flex align-items-center gap-2 mt-3" aria-label="Paginação de imagens">
      <button type="button" class="btn btn-outline-secondary" :disabled="loading || result.page <= 1" @click="load(result.page - 1)">Anterior</button>
      <span>{{ result.page }} de {{ pages }}</span>
      <button type="button" class="btn btn-outline-secondary" :disabled="loading || result.page >= pages" @click="load(result.page + 1)">Próxima</button>
    </nav>
  </template>
</template>

<style scoped>
.image-tile { display: grid; place-items: center; aspect-ratio: 4 / 3; }
.image-tile img { width: 100%; height: 100%; min-height: 0; object-fit: contain; }
</style>
