<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { RequestError, type SessionClient } from '../services/session'

interface PublicProfile {
  name: string | null
  display_name: string | null
  photo: string | null
  birth_date: string | null
  city: string | null
  state_code: string | null
  club: string | null
  association: string | null
  club_type: string | null
  biography: string | null
  edited_pages?: { slug: string; title: string; edited_at: string | null }[]
  next_edits_offset?: number | null
}

const route = useRoute()
const session = inject<SessionClient>('session')!
const profile = ref<PublicProfile>()
const loading = ref(true)
const error = ref('')
const loadingEdits = ref(false)
const editsError = ref('')
const name = computed(() => profile.value?.name || profile.value?.display_name || 'Usuário')
const details = computed(() => {
  const user = profile.value
  if (!user) return []
  return [
    { label: 'Nascimento', value: user.birth_date?.split('-').reverse().join('/') },
    { label: 'Localidade', value: [user.city, user.state_code].filter(Boolean).join(' — ') },
    { label: 'Clube', value: user.club },
    { label: 'Tipo de clube', value: user.club_type === 'D' ? 'Desbravadores' : user.club_type === 'A' ? 'Aventureiros' : user.club_type },
    { label: 'Associação', value: user.association },
  ].filter(item => item.value)
})

async function loadMoreEdits() {
  const current = profile.value
  const offset = current?.next_edits_offset
  if (!current || offset == null || loadingEdits.value) return
  loadingEdits.value = true
  editsError.value = ''
  try {
    const data: PublicProfile = await session.request('/public-users/' + route.params.publicId + '?edits_offset=' + offset)
    if (profile.value === current) {
      current.edited_pages = [...(current.edited_pages ?? []), ...(data.edited_pages ?? [])]
      current.next_edits_offset = data.next_edits_offset
    }
  } catch {
    if (profile.value === current) editsError.value = 'Não foi possível carregar mais páginas.'
  } finally {
    if (profile.value === current) loadingEdits.value = false
  }
}

if (!import.meta.env.SSR) {
  watch(() => route.params.publicId, async (id, _, onCleanup) => {
    let current = true
    onCleanup(() => { current = false })
    profile.value = undefined
    loadingEdits.value = false
    editsError.value = ''
    loading.value = true
    error.value = ''
    document.title = 'Perfil — MDAWiki'
    try {
      if (typeof id !== 'string' || !/^[a-f0-9]{32}$/.test(id)) {
        error.value = 'Perfil não encontrado.'
        return
      }
      const data: PublicProfile = await session.request('/public-users/' + id)
      if (!current) return
      profile.value = data
      document.title = name.value + ' — MDAWiki'
    } catch (cause) {
      if (current) error.value = cause instanceof RequestError && cause.status === 404
        ? 'Perfil não encontrado.'
        : 'Não foi possível carregar o perfil.'
    } finally {
      if (current) loading.value = false
    }
  }, { immediate: true })
}
</script>

<template>
  <article class="card article-card overflow-hidden" :aria-busy="loading">
    <div v-if="loading || error" class="article-body">
      <p v-if="loading" role="status">Carregando perfil…</p>
      <h1 v-else>{{ error }}</h1>
    </div>
    <template v-else-if="profile">
      <header class="profile-header d-flex align-items-center gap-4 flex-wrap">
        <img v-if="profile.photo" :key="profile.photo" :src="profile.photo" alt="" class="profile-avatar" width="104" height="104"
          @error="profile.photo = null" />
        <span v-else class="profile-avatar d-inline-grid place-avatar" aria-hidden="true">
          <i class="bi bi-person-fill"></i>
        </span>
        <div>
          <h1 class="border-0 p-0 mb-1">{{ name }}</h1>
          <p v-if="profile.display_name && profile.display_name !== name" class="mb-0 opacity-75">{{ profile.display_name }}</p>
        </div>
      </header>
      <div v-if="details.length || profile.biography || profile.edited_pages?.length" class="article-body">
        <dl v-if="details.length" class="profile-details mb-0">
          <div v-for="item in details" :key="item.label">
            <dt>{{ item.label }}</dt><dd class="mb-0">{{ item.value }}</dd>
          </div>
        </dl>
        <section v-if="profile.biography" :class="{ 'mt-4': details.length }">
          <h2>Biografia</h2>
          <p class="profile-bio mb-0">{{ profile.biography }}</p>
        </section>
        <section v-if="profile.edited_pages?.length" :class="{ 'mt-4': details.length || profile.biography }">
          <h2>Páginas editadas</h2>
          <ul class="list-group list-group-flush mb-3">
            <li v-for="page in profile.edited_pages" :key="page.slug" class="list-group-item px-0">
              <RouterLink :to="'/' + encodeURIComponent(page.slug) + '/'">{{ page.title }}</RouterLink>
            </li>
          </ul>
          <p v-if="editsError" role="alert">{{ editsError }}</p>
          <button v-if="profile.next_edits_offset != null" type="button" class="btn btn-outline-primary" :disabled="loadingEdits" @click="loadMoreEdits">
            {{ loadingEdits ? 'Carregando…' : 'Ver mais páginas' }}
          </button>
        </section>
      </div>
    </template>
  </article>
</template>

<style scoped>
.profile-header {
  padding: clamp(1.5rem, 5vw, 3rem);
  color: #fff;
  background: linear-gradient(120deg, #253951, #365675);
}
.profile-header h1 { color: inherit; overflow-wrap: anywhere; }
.profile-avatar {
  width: 104px;
  height: 104px;
  border: 3px solid #fff;
  border-radius: 50%;
  object-fit: cover;
  background: #e6edf5;
  color: #365675;
}
.place-avatar { place-items: center; font-size: 3rem; }
.profile-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 12rem), 1fr));
  gap: 1.25rem;
}
.profile-bio { white-space: pre-wrap; overflow-wrap: anywhere; }
</style>
