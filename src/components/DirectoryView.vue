<script setup lang="ts">
import { computed, inject, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { RequestError, type SessionClient } from '../services/session'
const route = useRoute(), router = useRouter(), api = inject<SessionClient>('session')!
const mode = computed(() => String(route.meta.action)), rows = ref<any[]>([]), stats = ref<Record<string, number>>({}), busy = ref(false), error = ref(''), loginRequired = ref(false), next = ref<number | null>(null), term = ref(''), selected = ref<any>(null)
const types = [['paginas','Todas as páginas'],['categorias','Categorias'],['imagens','Imagens'],['videos','Vídeos'],['documentacao','Documentação'],['listas','Listas'],['links_inexistentes','Links inexistentes'],['paginas_sem_categoria','Páginas sem categoria'],['redirecionamentos','Redirecionamentos'],['imagens_sem_pagina','Imagens sem página'],['imagens_inexistentes','Imagens inexistentes']]
const type = computed(() => String(route.query.type || route.meta.catalogType || 'paginas'))
const title = computed(() => ({ search: 'Localizar na MDAWiki', catalog: types.find(([key]) => key === type.value)?.[1] || 'Listas', statistics: 'Estatísticas', users: 'Usuários', rebuild: 'Atualizar páginas', donate: 'Faça uma Doação', create: 'Nova página' }[mode.value] || 'MDAWiki'))
const filters = ref<Record<string, string>>({})
const userFields = [['NOME','Nome'],['APELIDO','Apelido'],['EMAIL','E-mail'],['CIDADE','Cidade'],['ESTADO','Estado'],['CLUBE','Clube'],['ASSOCIACAO','Associação'],['TIPO_CLUBE','Tipo de clube'],['ATIVO','Ativo'],['HAS_PHOTO','Tem foto']] as const
let generation = 0
async function load(append = false) {
  const current = ++generation; busy.value = true; error.value = ''; loginRequired.value = false
  if (!append) { rows.value = []; next.value = null; selected.value = null }
  document.title = `${title.value} — MDAWiki`
  document.querySelector('meta[name="robots"]')?.setAttribute('content', 'noindex,follow')
  try {
    let endpoint = ''
    const params = new URLSearchParams({ limit: '50', offset: String(append ? next.value : 0) })
    if (['donate','create'].includes(mode.value)) return
    if (mode.value === 'search') { term.value = String(route.query.q || route.params.term || '').replace(/^buscar:/i, ''); params.set('q', term.value); endpoint = '/search' }
    if (mode.value === 'catalog' || mode.value === 'rebuild') { endpoint = '/catalog'; params.set('type', mode.value === 'rebuild' ? 'atualizar' : type.value) }
    if (mode.value === 'users') { endpoint = '/users'; for (const [key, value] of Object.entries(filters.value)) if (value) params.set(key, value) }
    if (['users','rebuild'].includes(mode.value)) await api.initialize()
    if (mode.value === 'statistics') { stats.value = await api.request('/statistics'); return }
    const result = await api.request(endpoint + '?' + params)
    if (current !== generation) return
    rows.value = append ? [...rows.value, ...result.items] : result.items; next.value = result.next_offset
  } catch (e) { if (current === generation) { error.value = (e as Error).message; loginRequired.value = e instanceof RequestError && e.status === 401 } }
  finally { if (current === generation) busy.value = false }
}
onMounted(() => load()); watch(() => route.fullPath, () => load())
function search() { void router.push({ path: '/page@Busca', query: { q: term.value } }) }
function createPage() { const slug = term.value.trim().replaceAll(' ', '_'); if (!slug || /[/\\?#\x00-\x1f]/.test(slug) || slug.length > 250) { error.value = 'Informe um título válido, sem barras, ? ou #.'; return }; void router.push('/' + encodeURIComponent(slug) + '/editar') }
function changeType(e: Event) { void router.push({ path: '/page@Listas', query: { type: (e.target as HTMLSelectElement).value } }) }
async function details(user: any) { busy.value = true; try { selected.value = await api.request('/users/' + user.ID) } catch (e) { error.value = (e as Error).message } finally { busy.value = false } }
async function rebuild(row: any) { busy.value = true; error.value = ''; try { await api.request('/pages/' + encodeURIComponent(row.slug) + '/rebuild', 'POST', {}); rows.value = rows.value.filter(item => item.id !== row.id) } catch (e) { error.value = (e as Error).message } finally { busy.value = false } }
const labels: Record<string, string> = { pages: 'Páginas', images: 'Imagens', categories: 'Categorias', redirects: 'Redirecionamentos', uncategorized: 'Sem categoria', unused_images: 'Imagens sem uso', missing_images: 'Imagens inexistentes', videos: 'Vídeos', documentation: 'Documentação', missing_links: 'Links inexistentes', views: 'Acessos' }
</script>
<template>
  <section class="card article-card"><div class="article-body">
    <h1>{{ title }}</h1><p v-if="error" class="alert alert-danger" role="alert">{{ error }}</p><p v-if="busy" role="status">Carregando…</p>
    <RouterLink v-if="loginRequired" :to="{ path: '/page@Acesso', query: { redirect: route.fullPath } }">Fazer login para continuar</RouterLink>
    <form v-if="mode === 'search'" class="input-group mb-4" @submit.prevent="search"><input v-model="term" class="form-control" type="search" aria-label="Termo da busca" maxlength="100" required><button class="btn btn-primary">Pesquisar</button></form>
    <form v-if="mode === 'create'" class="account-fields" @submit.prevent="createPage"><label>Título da nova página<input v-model="term" class="form-control" maxlength="250" required></label><button class="btn btn-primary">Abrir editor</button></form>
    <select v-if="mode === 'catalog'" class="form-select mb-4" :value="type" aria-label="Lista de páginas" @change="changeType"><option v-for="[key, label] in types" :key="key" :value="key">{{ label }}</option></select>
    <dl v-if="mode === 'statistics'" class="statistics-list"><template v-for="(count, key) in stats" :key="key"><dt>{{ labels[key] || key }}</dt><dd>{{ count }}</dd></template></dl>
    <template v-if="mode === 'users' && !loginRequired">
      <form class="user-filters" @submit.prevent="load()"><label v-for="[key, label] in userFields" :key="key">{{ label }}<select v-if="['TIPO_CLUBE','ATIVO','HAS_PHOTO'].includes(key)" v-model="filters[key]" class="form-select"><option value="">Todos</option><template v-if="key === 'TIPO_CLUBE'"><option>Desbravadores</option><option>Aventureiros</option></template><template v-else><option value="S">Sim</option><option value="N">Não</option></template></select><input v-else v-model="filters[key]" class="form-control" maxlength="150"></label><button class="btn btn-primary" :disabled="busy">Filtrar</button><button type="button" class="btn btn-outline-secondary" @click="filters = {}; load()">Limpar</button></form>
      <div class="table-responsive"><table class="table"><thead><tr><th>Nome</th><th>E-mail</th><th>Cidade</th><th>Clube</th></tr></thead><tbody><tr v-for="user in rows" :key="user.ID"><td><button class="btn btn-link" @click="details(user)">{{ user.NOME }}</button></td><td>{{ user.EMAIL }}</td><td>{{ user.CIDADE }} / {{ user.ESTADO }}</td><td>{{ user.CLUBE }}</td></tr></tbody></table></div>
    </template>
    <ul v-else-if="['search','catalog','rebuild'].includes(mode)" class="directory-list"><li v-for="row in rows" :key="row.id + row.slug"><RouterLink :to="`/${encodeURIComponent(row.slug)}/`">{{ row.title }}</RouterLink><span v-if="row.count" class="badge text-bg-secondary ms-2">{{ row.count }}</span><p v-if="row.description">{{ row.description }}</p><span v-if="row.destination"> → {{ row.destination }}</span><button v-if="mode === 'rebuild'" class="btn btn-sm btn-outline-primary ms-3" :disabled="busy" @click="rebuild(row)">Reprocessar</button></li></ul>
    <p v-if="!busy && !error && !rows.length && ['search','catalog','rebuild','users'].includes(mode)">Nenhum resultado encontrado.</p>
    <button v-if="next !== null" class="btn btn-outline-primary" :disabled="busy" @click="load(true)">Carregar mais</button>
    <template v-if="mode === 'donate'"><h2>Por que doar?</h2><p>A MDAWiki foi desenvolvida por voluntário, sem remuneração. O projeto tem custos de hospedagem e domínio. Sua contribuição ajuda a manter os materiais disponíveis para desbravadores e aventureiros.</p><form action="https://www.paypal.com/donate" method="post"><input type="hidden" name="business" value="UA3MMHHS898G2"><input type="hidden" name="currency_code" value="BRL"><button class="btn btn-primary">Doar pelo PayPal</button></form></template>
    <div v-if="selected" class="modal d-block" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="user-detail-title" @keydown.esc="selected = null"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h2 id="user-detail-title" class="modal-title fs-5">{{ selected.NOME }}</h2><button class="btn-close" aria-label="Fechar" @click="selected = null" /></div><div class="modal-body"><img v-if="selected.photo" :src="selected.photo" class="profile-photo" alt="Foto do usuário"><dl><template v-for="(value, key) in selected" :key="key"><template v-if="key !== 'photo'"><dt>{{ key }}</dt><dd>{{ value }}</dd></template></template></dl></div><div class="modal-footer"><button class="btn btn-secondary" @click="selected = null">Fechar</button></div></div></div></div><div v-if="selected" class="modal-backdrop show" />
  </div></section>
</template>
