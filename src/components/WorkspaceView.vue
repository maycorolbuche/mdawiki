<script setup lang="ts">
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter, onBeforeRouteLeave } from 'vue-router'
import { diffLines } from 'diff'
import { cleanArticle } from '../services/content'
import { RequestError, type SessionClient } from '../services/session'
const route = useRoute(), router = useRouter(), api = inject<SessionClient>('session')!
const slug = computed(() => String(route.params.slug)), historyMode = computed(() => route.meta.action === 'history')
const title = ref(''), source = ref(''), original = ref(''), version = ref(''), media = ref(false), busy = ref(false), loading = ref(true), error = ref(''), loginRequired = ref(false), preview = ref(''), revisions = ref<any[]>([]), before = ref<number | null>(null), older = ref<number | null>(null), newer = ref<number | null>(null), compare = ref(false), selected = ref<any>(null)
const originalTitle = ref(''), format = ref('wiki')
const dirty = computed(() => source.value !== original.value || title.value !== originalTitle.value)
const loaded = ref(false)
const changes = computed(() => {
  const a = revisions.value.find(r => Number(r.id) === Number(older.value))?.source || '', b = revisions.value.find(r => Number(r.id) === Number(newer.value))?.source || ''
  return compare.value ? diffLines(a, b, { timeout: 1000 }) || [] : []
})
const endpoint = computed(() => '/pages/' + encodeURIComponent(slug.value))
async function load() {
  loading.value = true; loginRequired.value = false; error.value = ''; preview.value = ''; compare.value = false; revisions.value = []; selected.value = null
  loaded.value = false
  document.title = `${historyMode.value ? 'Histórico' : 'Editar'} — ${slug.value.replaceAll('_', ' ')} — MDAWiki`
  document.querySelector('meta[name="robots"]')?.setAttribute('content', 'noindex,follow')
  try {
    await api.initialize()
    const data = await api.request(endpoint.value + (historyMode.value ? '/history' : '/editor'))
    title.value = data.title; version.value = data.base_version
    originalTitle.value = data.title
    if (historyMode.value) { revisions.value = data.revisions; before.value = data.next_cursor; older.value = data.revisions[1]?.id; newer.value = data.revisions[0]?.id }
    else { source.value = data.source; original.value = data.source; media.value = data.media; format.value = data.format || 'wiki' }
    loaded.value = true
  } catch (e) { error.value = (e as Error).message; loginRequired.value = e instanceof RequestError && e.status === 401 }
  finally { loading.value = false }
}
onMounted(load); watch(() => route.fullPath, load)
onBeforeRouteLeave(() => !dirty.value || historyMode.value || window.confirm('Descartar as alterações não salvas?'))
function unload(e: BeforeUnloadEvent) { if (dirty.value && !historyMode.value) { e.preventDefault(); e.returnValue = '' } }
onMounted(() => window.addEventListener('beforeunload', unload)); onBeforeUnmount(() => window.removeEventListener('beforeunload', unload))
async function save() {
  busy.value = true; error.value = ''
  try { await api.request(endpoint.value, 'PUT', { title: title.value, source: source.value, base_version: version.value }); original.value = source.value; originalTitle.value = title.value; await router.push('/' + encodeURIComponent(slug.value) + '/') }
  catch (e) { error.value = (e as Error).message } finally { busy.value = false }
}
async function renderPreview() { busy.value = true; error.value = ''; try { const p = await api.request(endpoint.value + '/preview', 'POST', { source: source.value }); preview.value = cleanArticle(p.html) } catch (e) { error.value = (e as Error).message } finally { busy.value = false } }
async function more() { busy.value = true; try { const data = await api.request(endpoint.value + '/history?before=' + before.value); revisions.value.push(...data.revisions); before.value = data.next_cursor } catch (e) { error.value = (e as Error).message } finally { busy.value = false } }
async function restore(id: number) {
  if (!window.confirm('Restaurar esta revisão como uma nova edição?')) return
  busy.value = true; error.value = ''
  try { await api.request(endpoint.value + '/restore', 'POST', { revision: id, base_version: version.value }); await load() } catch (e) { error.value = (e as Error).message } finally { busy.value = false }
}
async function upload(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]; if (!file) return
  if (file.size > 5000000) { error.value = 'Envie uma imagem de até 5 MB.'; return }
  busy.value = true
  try { const data = await new Promise<string>((resolve, reject) => { const r = new FileReader(); r.onload = () => resolve(String(r.result)); r.onerror = () => reject(new Error('Falha ao ler a imagem.')); r.readAsDataURL(file) }); const result = await api.request('/media', 'POST', { data }); source.value = media.value ? result.url : source.value + '\n<img src="' + result.url + '" alt="">\n' }
  catch (e) { error.value = (e as Error).message } finally { busy.value = false }
}
function insert(text: string) { source.value += '\n' + text + '\n' }
</script>
<template>
  <section class="card article-card"><div class="article-body">
    <h1>{{ historyMode ? 'Histórico de Edições' : 'Editar Página' }} <small>{{ title || slug.replaceAll('_', ' ') }}</small></h1>
    <p v-if="loading" role="status">Carregando…</p><p v-if="error" class="alert alert-danger" role="alert">{{ error }}</p>
    <RouterLink v-if="loginRequired" :to="{ path: '/page@Acesso', query: { redirect: route.fullPath } }">Fazer login para continuar</RouterLink>
    <template v-if="loaded && !loading && !loginRequired">
      <form v-if="!historyMode" @submit.prevent="save">
        <p class="alert alert-warning">Nas especialidades, mantenha os requisitos do manual, sem respostas.</p>
        <label class="d-block mb-3">Título<input v-model="title" class="form-control" required maxlength="250"></label>
        <div v-if="!media && format !== 'html'" class="editor-tools"><button type="button" @click="insert('== Seção ==')">Seção</button><button type="button" @click="insert('[[Página|Texto]]')">Link interno</button><button type="button" @click="insert('{{Categoria:Categoria}}')">Categoria</button><button type="button" @click="insert('# Primeiro item\n# Segundo item')">Lista</button><RouterLink to="/doc%40In%C3%ADcio/">Ajuda do código wiki</RouterLink></div>
        <label class="d-block">{{ media ? 'Endereço da imagem enviada' : format === 'html' ? 'Código HTML' : 'Código wiki' }}<textarea v-model="source" class="form-control wiki-editor" :rows="media ? 3 : 22" spellcheck="false" maxlength="500000" /></label>
        <label class="d-block my-3">Enviar imagem<input type="file" class="form-control" accept="image/png,image/jpeg,image/gif,image/webp,image/svg+xml" :disabled="busy" @change="upload"></label>
        <button v-if="media" type="button" class="btn btn-outline-danger mb-3" :disabled="busy" @click="source = ''">Remover imagem desta página</button>
        <div class="d-flex flex-wrap gap-2"><button class="btn btn-primary" :disabled="busy">{{ busy ? 'Aguarde…' : 'Salvar página' }}</button><button v-if="!media" type="button" class="btn btn-outline-primary" :disabled="busy" @click="renderPreview">Pré-visualizar</button><RouterLink class="btn btn-outline-secondary" :to="`/${encodeURIComponent(slug)}/`">Voltar ao artigo</RouterLink></div>
        <section v-if="preview" class="wiki-content preview-content" aria-label="Pré-visualização" v-html="preview" />
      </form>
      <template v-else>
        <p v-if="!revisions.length">Ainda não há revisões registradas.</p>
        <div class="table-responsive"><table v-if="revisions.length" class="table align-middle"><thead><tr><th>Comparar</th><th>Revisão</th><th>Autor</th><th>Data</th><th>Ações</th></tr></thead><tbody><tr v-for="r in revisions" :key="r.id"><td><input v-model="older" type="radio" :value="r.id" name="older" :aria-label="`Versão anterior ${r.id}`"> <input v-model="newer" type="radio" :value="r.id" name="newer" :aria-label="`Versão nova ${r.id}`"></td><td>{{ r.id }}</td><td>{{ r.author || 'Usuário' }}</td><td>{{ r.created_at }}</td><td><button class="btn btn-sm btn-outline-primary me-2" @click="selected = r">Visualizar</button><button class="btn btn-sm btn-outline-secondary" :disabled="busy" @click="restore(r.id)">Restaurar</button></td></tr></tbody></table></div>
        <button v-if="before" class="btn btn-outline-primary me-2" :disabled="busy" @click="more">Mais revisões</button><button v-if="revisions.length > 1" class="btn btn-primary" @click="compare = true">Comparar versões</button>
        <div v-if="compare" class="revision-diff"><span v-for="(part, i) in changes" :key="i" :class="{ added: part.added, removed: part.removed }">{{ part.value }}</span></div>
        <section v-if="selected" class="mt-4"><h2>Revisão {{ selected.id }}</h2><pre class="revision-source">{{ selected.source }}</pre></section>
      </template>
    </template>
    <div v-if="error && !loginRequired" class="mt-3"><button class="btn btn-outline-secondary" @click="load">Recarregar</button><p v-if="dirty">Copie o conteúdo antes de recarregar para preservar suas alterações.</p></div>
  </div></section>
</template>
