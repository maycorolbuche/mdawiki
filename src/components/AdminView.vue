<script setup lang="ts">
import { computed, inject, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminMediaThumbnail from './AdminMediaThumbnail.vue'
import type { SessionClient } from '../services/session'

type Group = 'unlinked' | 'orphan' | 'missing'
type Item = { kind: 'file' | 'avatar' | 'object'; id?: number | string; media_key: string; size_bytes?: number | string; revisions?: number | string; images?: number | string; pages?: number | string; location?: string; locations?: string[]; avatar_in_use?: boolean; public_images?: { slug: string; title: string }[] }
type Audit = Record<Group, Item[]> & { locations: string[] }

const api = inject<SessionClient>('session')!
const route = useRoute(), router = useRouter()
const access = ref<'loading' | 'admin' | 'denied' | 'error'>('loading')
const audit = ref<Audit>()
const preview = ref<{ key: string; image: string }>()
const tab = ref<Group>('unlinked'), search = ref(''), page = ref(1)
const busy = ref(false), error = ref(''), success = ref(''), selected = ref<Item>()
const sections: { id: Group; label: string; help: string }[] = [
  { id: 'unlinked', label: 'Imagens sem artigo', help: 'Arquivos cadastrados sem vínculo atual com artigos. A API confere também referências diretas e do histórico antes de excluir.' },
  { id: 'orphan', label: 'Arquivos órfãos', help: 'Objetos sem cadastro em arquivos ou avatares. Cada linha identifica o local da cópia.' },
  { id: 'missing', label: 'Arquivos ausentes', help: 'Referências sem arquivo nos armazenamentos verificados.' },
]
const files = computed(() => route.meta.adminFiles === true)
const matched = computed(() => (audit.value?.[tab.value] ?? []).filter(item => item.media_key.toLowerCase().includes(search.value.trim().toLowerCase())))
const visible = computed(() => matched.value.slice((page.value - 1) * 25, page.value * 25))
const labels: Record<string, string> = { local: 'Disco local', r2: 'R2' }
const size = (bytes: Item['size_bytes']) => bytes == null ? '—' : new Intl.NumberFormat('pt-BR', { style: 'unit', unit: 'kilobyte', maximumFractionDigits: 1 }).format(Number(bytes) / 1024)
watch([tab, search], () => { page.value = 1; selected.value = undefined; preview.value = undefined })

async function refresh() {
  busy.value = true; error.value = ''; selected.value = undefined; preview.value = undefined
  try {
    audit.value = await api.request('/admin/files')
    page.value = Math.min(page.value, Math.max(1, Math.ceil(matched.value.length / 25)))
  } catch (failure) {
    audit.value = undefined
    error.value = String((failure as Error).message || 'Não foi possível verificar os arquivos.')
  } finally { busy.value = false }
}

async function showPreview(item: Item) {
  busy.value = true; error.value = ''; preview.value = undefined
  try {
    const query = new URLSearchParams({ media_key: item.media_key })
    if (item.location) query.set('location', item.location)
    const data = await api.request('/admin/files?' + query)
    preview.value = { key: item.media_key, image: data.image }
  } catch (failure) {
    error.value = (failure as Error).message
  } finally { busy.value = false }
}

async function remove() {
  const item = selected.value
  if (!item) return
  busy.value = true; error.value = ''; success.value = ''; selected.value = undefined
  try {
    const action = tab.value === 'missing' ? `missing_${item.kind}` : item.avatar_in_use ? 'avatar_record' : tab.value
    await api.request('/admin/files', 'POST', { action, id: item.id, media_key: item.media_key, location: item.location })
    success.value = 'Remoção concluída.'
    await refresh()
  } catch (failure) {
    error.value = String((failure as Error).message || 'Não foi possível remover o arquivo.')
  } finally { busy.value = false }
}

onMounted(async () => {
  try {
    await api.initialize()
    if (!api.state.user) {
      await router.replace({ path: '/p/login', query: { redirect: route.fullPath } })
      return
    }
    access.value = api.state.user.role === 'admin' ? 'admin' : 'denied'
    if (access.value === 'admin' && files.value) await refresh()
  } catch { access.value = 'error' }
})
watch(files, enabled => {
  if (enabled && access.value === 'admin') void refresh()
  else selected.value = undefined
})
</script>

<template>
  <section class="card p-3 p-md-4">
    <h1>{{ files ? 'Arquivos de mídia' : 'Administração' }}</h1>
    <p v-if="access === 'loading'" role="status">Verificando acesso…</p>
    <p v-else-if="access === 'denied'" class="alert alert-danger" role="alert">Acesso negado.</p>
    <p v-else-if="access === 'error'" class="alert alert-danger" role="alert">Não foi possível verificar sua sessão. Recarregue a página.</p>
    <template v-else-if="api.state.user?.role === 'admin'">
      <RouterLink v-if="!files" class="list-group-item list-group-item-action list-group" to="/a/files">Arquivos de mídia</RouterLink>
      <template v-else>
        <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center mb-3">
          <RouterLink to="/a">Voltar à administração</RouterLink>
          <button type="button" class="btn btn-outline-primary" :disabled="busy" @click="refresh">Verificar arquivos</button>
        </div>
        <p v-if="busy" role="status">Processando…</p>
        <p v-if="error" class="alert alert-danger" role="alert">{{ error }}</p>
        <p v-if="success" class="alert alert-success" role="status">{{ success }}</p>
        <template v-if="audit">
          <p>Armazenamentos verificados: {{ audit.locations.map(location => labels[location]).join(', ') }}</p>
          <nav class="nav nav-tabs flex-wrap mb-3" aria-label="Verificações de mídia">
            <button v-for="section in sections" :key="section.id" type="button" class="nav-link" :class="{ active: tab === section.id }" :aria-current="tab === section.id ? 'true' : undefined" :disabled="busy" @click="tab = section.id">
              {{ section.label }} ({{ audit[section.id].length }})
            </button>
          </nav>
          <p>{{ sections.find(section => section.id === tab)?.help }}</p>
          <label for="media-search" class="form-label">Filtrar pelo nome do arquivo</label>
          <input id="media-search" v-model="search" class="form-control mb-3" type="search">
          <figure v-if="preview" class="border rounded p-3">
            <figcaption class="text-break mb-2">{{ preview.key }}</figcaption>
            <img :src="preview.image" alt="Prévia da mídia selecionada" style="max-width: 100%; max-height: 18rem">
            <button type="button" class="btn btn-sm btn-outline-secondary d-block mt-2" @click="preview = undefined">Fechar prévia</button>
          </figure>
          <div v-if="selected" class="alert alert-warning" role="region" aria-label="Confirmar remoção">
            <p class="text-break"><strong>{{ tab === 'missing' ? 'Remover referência' : selected.avatar_in_use ? 'Remover cadastro em files' : 'Excluir definitivamente' }}</strong> {{ selected.media_key }} ({{ size(selected.size_bytes) }})</p>
            <p v-if="selected.location">Local: {{ labels[selected.location] }}</p>
            <p v-if="selected.avatar_in_use && tab !== 'missing'">O arquivo e o avatar do usuário serão preservados.</p>
            <p v-else-if="selected.kind === 'file'">{{ selected.revisions }} revisão(ões) serão removidas e {{ selected.images }} imagem(ns) perderão o arquivo atual.</p>
            <p v-else-if="selected.kind === 'avatar'">A conta será mantida. Apenas a referência ao avatar será removida.</p>
            <button type="button" class="btn btn-danger me-2" :disabled="busy" @click="remove">Confirmar</button>
            <button type="button" class="btn btn-outline-secondary" @click="selected = undefined">Cancelar</button>
          </div>
          <p v-if="!matched.length">Nenhum arquivo nesta lista.</p>
          <div v-else class="table-responsive">
            <table class="table align-middle">
              <thead><tr><th>Miniatura</th><th>Arquivo</th><th>Local</th><th>Tamanho</th><th>Referências</th><th>Ação</th></tr></thead>
              <tbody>
                <tr v-for="item in visible" :key="`${item.kind}:${item.id ?? item.location}:${item.media_key}`">
                  <td><AdminMediaThumbnail v-if="tab !== 'missing'" :media-key="item.media_key" :location="item.location" /><span v-else>—</span></td>
                  <td class="text-break">
                    <div v-for="image in item.public_images" :key="image.slug">
                      <RouterLink :to="'/i/' + encodeURIComponent(image.slug)" target="_blank" rel="noopener">{{ image.title || image.slug }} <i class="bi bi-box-arrow-up-right" aria-hidden="true" /></RouterLink>
                    </div>
                    {{ item.media_key }}
                    <small v-if="item.kind === 'avatar'" class="d-block">Usuário {{ item.id }}</small>
                  </td>
                  <td>{{ (item.locations ?? (item.location ? [item.location] : [])).map(location => labels[location]).join(', ') || '—' }}</td>
                  <td>{{ size(item.size_bytes) }}</td>
                  <td>{{ item.avatar_in_use ? 'Avatar em uso • ' : '' }}{{ item.kind === 'file' ? `${item.pages} página(s), ${item.images} imagem(ns), ${item.revisions} revisão(ões)` : '—' }}</td>
                  <td><button v-if="tab !== 'missing'" type="button" class="btn btn-sm btn-outline-secondary me-2" :disabled="busy" @click="showPreview(item)">Ver imagem</button><button type="button" class="btn btn-sm btn-outline-danger" :disabled="busy" @click="selected = item">{{ tab === 'missing' ? 'Remover referência' : item.avatar_in_use ? 'Remover cadastro' : 'Excluir' }}</button></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="matched.length > 25" class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary" :disabled="page === 1" @click="page--">Anterior</button>
            <span>{{ page }} de {{ Math.ceil(matched.length / 25) }}</span>
            <button type="button" class="btn btn-outline-secondary" :disabled="page * 25 >= matched.length" @click="page++">Próxima</button>
          </div>
        </template>
      </template>
    </template>
  </section>
</template>
