<script setup lang="ts">
import { computed, inject, onMounted, ref } from 'vue'
import type { SessionClient, UserRole } from '../services/session'

type Account = { id: number | string; name: string | null; display_name: string | null; email: string; public_id: string; created_at: string | null; is_active: number | string | null; role: UserRole; photo?: string | null }
type Result = { items: Account[]; total: number; page: number; page_size: number }

const api = inject<SessionClient>('session')!
const result = ref<Result>()
const search = ref(''), appliedSearch = ref('')
const photo = ref(''), roleFilter = ref(''), statusFilter = ref('')
const failedPhotos = ref(new Set<string>())
const sort = ref<'name' | 'created_at'>('name'), direction = ref<'asc' | 'desc'>('asc')
const busy = ref(false), error = ref(''), success = ref('')
const selected = ref<{ account: Account; action: 'delete' | 'role'; role?: UserRole }>()
const roles: Record<UserRole, string> = { reader: 'Leitor', user: 'Usuário', admin: 'Administrador' }
const title = (account: Account) => account.name || account.display_name || account.email
const isSelf = (account: Account) => String(account.id) === String(api.state.user?.ID)
const pages = computed(() => result.value ? Math.max(1, Math.ceil(result.value.total / result.value.page_size)) : 1)

function date(value: string | null) {
  if (!value) return '—'
  const [year, month, day] = value.slice(0, 10).split('-')
  return `${day}/${month}/${year}`
}

async function load(page = 1) {
  busy.value = true; error.value = ''
  try {
    const query = new URLSearchParams({ q: appliedSearch.value, photo: photo.value, role: roleFilter.value, status: statusFilter.value, sort: sort.value, direction: direction.value, page: String(page) })
    result.value = await api.request('/admin/users?' + query)
  } catch (failure) {
    error.value = (failure as Error).message
  } finally { busy.value = false }
}

function applySearch() {
  appliedSearch.value = search.value
  void load()
}

function order(field: typeof sort.value) {
  direction.value = sort.value === field && direction.value === 'asc' ? 'desc' : 'asc'
  sort.value = field
  void load()
}

function chooseRole(account: Account, event: Event) {
  const input = event.target as HTMLSelectElement
  const role = input.value as UserRole
  input.value = account.role
  if (role !== account.role) selected.value = { account, action: 'role', role }
}

async function confirm() {
  const change = selected.value
  if (!change) return
  busy.value = true; error.value = ''; success.value = ''
  try {
    await api.request('/admin/users', 'POST', { id: change.account.id, action: change.action, role: change.role })
    selected.value = undefined
    success.value = change.action === 'delete' ? 'Usuário excluído.' : 'Papel atualizado.'
    await load(result.value?.page)
  } catch (failure) {
    error.value = (failure as Error).message
  } finally { busy.value = false }
}

onMounted(() => { void load() })
</script>

<template>
  <RouterLink to="/a">Voltar à administração</RouterLink>
  <form class="mt-3 mb-3" @submit.prevent="applySearch">
    <label class="form-label" for="admin-user-search">Buscar por nome, nome de exibição ou e-mail</label>
    <div class="input-group">
      <input id="admin-user-search" v-model="search" class="form-control" type="search" maxlength="150">
      <button type="submit" class="btn btn-outline-primary" :disabled="busy || !!selected">Buscar</button>
    </div>
    <div class="row g-2 mt-1">
      <div class="col-sm-4">
        <label for="admin-photo-filter" class="form-label">Foto</label>
        <select id="admin-photo-filter" v-model="photo" class="form-select" :disabled="busy || !!selected" @change="applySearch">
          <option value="">Todos</option><option value="yes">Com foto</option><option value="no">Sem foto</option>
        </select>
      </div>
      <div class="col-sm-4">
        <label for="admin-role-filter" class="form-label">Papel</label>
        <select id="admin-role-filter" v-model="roleFilter" class="form-select" :disabled="busy || !!selected" @change="applySearch">
          <option value="">Todos</option>
          <option v-for="(label, role) in roles" :key="role" :value="role">{{ label }}</option>
        </select>
      </div>
      <div class="col-sm-4">
        <label for="admin-status-filter" class="form-label">Status</label>
        <select id="admin-status-filter" v-model="statusFilter" class="form-select" :disabled="busy || !!selected" @change="applySearch">
          <option value="">Todos</option><option value="active">Ativos</option><option value="inactive">Inativos</option>
        </select>
      </div>
    </div>
  </form>
  <p v-if="busy" role="status">Processando…</p>
  <p v-if="error" class="alert alert-danger" role="alert">{{ error }}</p>
  <p v-if="success" class="alert alert-success" role="status">{{ success }}</p>
  <div v-if="selected" class="alert alert-warning" role="region" aria-label="Confirmar alteração">
    <p v-if="selected.action === 'delete'">Excluir definitivamente <strong>{{ title(selected.account) }}</strong> e seu avatar?</p>
    <p v-else>Alterar o papel de <strong>{{ title(selected.account) }}</strong> para <strong>{{ roles[selected.role!] }}</strong>?</p>
    <button type="button" class="btn me-2" :class="selected.action === 'delete' ? 'btn-danger' : 'btn-primary'" :disabled="busy" @click="confirm">Confirmar</button>
    <button type="button" class="btn btn-outline-secondary" :disabled="busy" @click="selected = undefined">Cancelar</button>
  </div>
  <template v-if="result">
    <p v-if="!result.items.length">Nenhum usuário encontrado.</p>
    <div v-else class="table-responsive">
      <table class="table align-middle">
        <thead><tr>
          <th scope="col">Foto</th>
          <th :aria-sort="sort === 'name' ? (direction === 'asc' ? 'ascending' : 'descending') : 'none'"><button type="button" class="btn btn-link p-0" :disabled="busy || !!selected" @click="order('name')">Nome {{ sort === 'name' ? (direction === 'asc' ? '↑' : '↓') : '' }}</button></th>
          <th>E-mail</th>
          <th :aria-sort="sort === 'created_at' ? (direction === 'asc' ? 'ascending' : 'descending') : 'none'"><button type="button" class="btn btn-link p-0" :disabled="busy || !!selected" @click="order('created_at')">Cadastro {{ sort === 'created_at' ? (direction === 'asc' ? '↑' : '↓') : '' }}</button></th>
          <th>Status</th><th>Papel</th><th>Ação</th>
        </tr></thead>
        <tbody>
          <tr v-for="account in result.items" :key="account.id">
            <td>
              <img v-if="account.photo && !failedPhotos.has(account.photo)" :src="account.photo" alt="" width="40" height="40" loading="lazy" class="rounded-circle object-fit-cover" @error="failedPhotos.add(account.photo!)">
              <i v-else class="bi bi-person-circle fs-4 text-secondary" aria-label="Sem foto" role="img" />
            </td>
            <td>
              <RouterLink v-if="Number(account.is_active) === 1" :to="'/u/' + encodeURIComponent(account.public_id)" target="_blank" rel="noopener">{{ title(account) }} <i class="bi bi-box-arrow-up-right" aria-hidden="true" /></RouterLink>
              <span v-else>{{ title(account) }}</span>
              <small v-if="isSelf(account)" class="text-muted d-block">Sua conta</small>
            </td>
            <td class="text-break">{{ account.email }}</td>
            <td>{{ date(account.created_at) }}</td>
            <td><span class="badge" :class="Number(account.is_active) === 1 ? 'text-bg-success' : 'text-bg-secondary'">
              {{ Number(account.is_active) === 1 ? 'Ativo' : 'Inativo' }}
            </span></td>
            <td><select class="form-select form-select-sm" :aria-label="'Papel de ' + title(account)" :value="account.role" :disabled="busy || !!selected || isSelf(account)" @change="chooseRole(account, $event)">
              <option v-for="(label, role) in roles" :key="role" :value="role">{{ label }}</option>
            </select></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" :disabled="busy || !!selected || isSelf(account)" @click="selected = { account, action: 'delete' }">Excluir</button></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="pages > 1" class="d-flex align-items-center gap-2">
      <button type="button" class="btn btn-outline-secondary" :disabled="busy || !!selected || result.page === 1" @click="load(result.page - 1)">Anterior</button>
      <span>{{ result.page }} de {{ pages }}</span>
      <button type="button" class="btn btn-outline-secondary" :disabled="busy || !!selected || result.page >= pages" @click="load(result.page + 1)">Próxima</button>
    </div>
  </template>
</template>
