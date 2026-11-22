<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { RequestError, type SessionClient } from '../services/session'

interface ImageDetails {
  slug: string
  title: string
  url: string | null
  version: string
  meta: { extension?: string; width?: number; height?: number; size_bytes?: number }
  pages: { id: string; slug: string; title: string }[]
  history: { id: string; url: string; created_at: string | null }[]
}

const session = inject<SessionClient>('session')!
const route = useRoute()
const router = useRouter()
const slug = computed(() => String(route.params.slug))
const endpoint = computed(() => '/images/' + encodeURIComponent(slug.value))
const image = ref<ImageDetails | null>(null)
const missing = ref(false)
const busy = ref(true)
const showHistory = ref(false)
const error = ref('')
const picker = ref<HTMLInputElement | null>(null)
let request = 0

async function load() {
  const current = ++request
  busy.value = true
  image.value = null
  missing.value = false
  showHistory.value = false
  error.value = ''
  try {
    const result = await session.request(endpoint.value)
    if (current !== request) return
    image.value = result
    if (result.slug !== slug.value) await router.replace('/i/' + encodeURIComponent(result.slug))
  } catch (cause) {
    if (current !== request) return
    if (cause instanceof RequestError && cause.status === 404) missing.value = true
    else error.value = 'Não foi possível carregar a imagem.'
  } finally {
    if (current === request) busy.value = false
  }
}
watch(slug, load, { immediate: !import.meta.env.SSR })

async function selectFile() {
  error.value = ''
  try {
    await session.initialize()
    if (!session.state.user) {
      await router.push({ path: '/page@Acesso', query: { redirect: route.fullPath } })
      return
    }
    picker.value?.click()
  } catch {
    error.value = 'Não foi possível verificar a sessão.'
  }
}

async function upload(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  input.value = ''
  if (!file) return
  if (file.size > 5_000_000) {
    error.value = 'Selecione uma imagem de até 5 MB.'
    return
  }
  const current = request
  const target = endpoint.value
  const version = image.value?.version ?? ''
  busy.value = true
  error.value = ''
  try {
    const data = await new Promise<string>((resolve, reject) => {
      const reader = new FileReader()
      reader.onload = () => resolve(String(reader.result))
      reader.onerror = () => reject(new Error('Não foi possível ler o arquivo.'))
      reader.readAsDataURL(file)
    })
    const result = await session.request(target, 'PUT', { data, base_version: version })
    if (current !== request) return
    image.value = result
    missing.value = false
    if (result.slug !== slug.value) await router.replace('/i/' + encodeURIComponent(result.slug))
  } catch (cause) {
    if (current === request) error.value = (cause as Error).message
  } finally {
    if (current === request) busy.value = false
  }
}
</script>

<template>
  <div class="article-layout" :class="{ 'has-relations': image }">
    <article class="card article-card" :aria-busy="busy">
      <div class="article-body">
        <h1>{{ missing ? 'Imagem não encontrada' : image?.title ?? slug }}</h1>
        <p v-if="busy" role="status">Processando imagem…</p>
        <p v-if="error" class="alert alert-danger" role="alert">{{ error }}</p>
        <img v-if="image?.url" :src="image.url" :alt="image.title" class="img-fluid d-block mb-3" />
        <p v-if="missing">Ainda não há uma imagem neste endereço.</p>
        <div v-if="image || missing" class="d-flex gap-2 flex-wrap mb-3">
          <button class="btn btn-primary" type="button" :disabled="busy" @click="selectFile">
            {{ image?.url ? 'Substituir imagem' : 'Enviar imagem' }}
          </button>
          <button v-if="image" class="btn btn-outline-secondary" type="button" :aria-expanded="showHistory"
            @click="showHistory = !showHistory">Histórico</button>
        </div>
        <input ref="picker" type="file" accept="image/png,image/jpeg,image/gif,image/webp" hidden @change="upload" />
        <section v-if="image && showHistory">
          <h2>Histórico</h2>
          <p v-if="!image.history.length">Nenhuma versão anterior.</p>
          <div class="d-flex flex-wrap gap-3">
            <figure v-for="version in image.history" :key="version.id">
              <a :href="version.url"><img :src="version.url" :alt="'Versão ' + version.id" width="160" class="img-fluid" loading="lazy" /></a>
              <figcaption>{{ version.created_at || 'Data indisponível' }}</figcaption>
            </figure>
          </div>
        </section>
      </div>
    </article>
    <aside v-if="image" class="page-relations" aria-label="Informações da imagem">
      <section class="relation-panel relation-blue">
        <h2>Propriedades</h2>
        <dl class="px-3 py-2 mb-0">
          <dt>Extensão</dt><dd>{{ image.meta.extension ?? '—' }}</dd>
          <dt>Largura</dt><dd>{{ image.meta.width == null ? '—' : image.meta.width + ' px' }}</dd>
          <dt>Altura</dt><dd>{{ image.meta.height == null ? '—' : image.meta.height + ' px' }}</dd>
          <dt>Tamanho</dt><dd>{{ image.meta.size_bytes == null ? '—' : new Intl.NumberFormat('pt-BR').format(image.meta.size_bytes) + ' bytes' }}</dd>
        </dl>
      </section>
      <section class="relation-panel relation-green">
        <h2>Páginas Vinculadas</h2>
        <p v-if="!image.pages.length" class="px-3 py-2 mb-0">Nenhuma página vinculada.</p>
        <ul v-else><li v-for="page in image.pages" :key="page.id">
          <RouterLink :to="'/' + encodeURIComponent(page.slug) + '/'">{{ page.title }}</RouterLink>
        </li></ul>
      </section>
    </aside>
  </div>
</template>
