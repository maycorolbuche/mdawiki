<script setup lang="ts">
import { computed, defineAsyncComponent, inject, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { SessionClient } from '../services/session'
import { RequestError } from '../services/session'
const AvatarCropModal = defineAsyncComponent(() => import('./AvatarCropModal.vue'))
const cropSource = ref('')
function closeCrop() { if (cropSource.value) URL.revokeObjectURL(cropSource.value); cropSource.value = '' }
onBeforeUnmount(closeCrop)
const route = useRoute(), router = useRouter(), api = inject<SessionClient>('session')!
const action = computed(() => String(route.meta.action))
const passwordMinimum = computed(() => action.value === 'register' ? 6 : action.value === 'login' ? 1 : 10)
const titles: Record<string, string> = { login: 'Entrar na MDAWiki', register: 'Criar conta', forgot: 'Recuperar senha', reset: 'Redefinir senha', activate: 'Ativar conta', profile: 'Meu perfil', password: 'Alterar senha', email: 'Alterar e-mail' }
const activationCode = ref<HTMLInputElement | null>(null)
const form = reactive<Record<string, any>>({}), busy = ref(false), loading = ref(false), error = ref(''), message = ref(''), loginRequired = ref(false)
const personal = [['NOME','Nome'],['APELIDO','Apelido'],['DATA_NASC','Data de nascimento'],['CIDADE','Cidade'],['ESTADO','Estado'],['CLUBE','Clube'],['ASSOCIACAO','Associação / Missão']] as const
const protectedAction = computed(() => ['profile','password','email'].includes(action.value))
async function load() {
  closeCrop()
  Object.keys(form).forEach(k => delete form[k]); error.value = ''; message.value = ''; loginRequired.value = false
  form.email = String(route.query.email || ''); form.token = String(route.query.token || ''); form.code = String(route.query.code || '')
  document.title = `${titles[action.value]} — MDAWiki`
  document.querySelector('meta[name="robots"]')?.setAttribute('content', 'noindex,follow')
  if (!protectedAction.value) return
  loading.value = true
  try { await api.initialize(); if (!api.state.user) { loginRequired.value = true; return }; if (action.value === 'profile') Object.assign(form, await api.request('/profile')) }
  catch (e) { error.value = (e as Error).message; loginRequired.value = e instanceof RequestError && e.status === 401 }
  finally { loading.value = false }
}
onMounted(load); watch(() => route.fullPath, load)
watch([activationCode, busy], ([input, pending]) => { if (!pending) input?.focus() }, { flush: 'post' })
async function submit() {
  busy.value = true; error.value = ''; message.value = ''
  try {
    const endpoint = { login: '/auth/login', register: '/auth/register', forgot: '/auth/forgot', reset: '/auth/reset', activate: '/auth/activate', profile: '/profile', password: '/profile/password', email: '/profile/email' }[action.value]
    if (!endpoint) return
    const result = await api.request(endpoint, action.value === 'profile' ? 'PUT' : 'POST', form)
    if (action.value === 'register') {
      await router.replace({ path: '/p/ativar-conta', query: { email: String(form.email).trim() } })
      return
    }
    message.value = result.message || 'Dados salvos.'
    if (action.value === 'profile' && api.state.user) Object.assign(api.state.user, result)
    if (action.value === 'login') { const redirect = String(route.query.redirect || '/'); await router.push(redirect.startsWith('/') && !redirect.startsWith('//') ? redirect : '/') }
    if (['reset','activate'].includes(action.value)) await router.push('/p/login')
    form.password = ''; form.password_confirmation = ''; form.current_password = ''
  } catch (e) { error.value = (e as Error).message }
  finally { busy.value = false }
}
async function resend() {
  busy.value = true; error.value = ''
  try { const result = await api.request('/auth/resend', 'POST', { email: form.email }); message.value = result.message }
  catch (e) { error.value = (e as Error).message } finally { busy.value = false }
}
function photo(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]; input.value = ''; if (!file) return
  error.value = ''; message.value = ''
  if (file.size > 5000000) { error.value = 'A imagem deve ter até 5 MB.'; return }
  if (!['image/png','image/jpeg','image/gif','image/webp','image/svg+xml'].includes(file.type)) { error.value = 'Escolha uma imagem PNG, JPEG, GIF, WebP ou SVG.'; return }
  closeCrop(); cropSource.value = URL.createObjectURL(file)
}
async function savePhoto(data: string) {
  const selected = cropSource.value
  busy.value = true; error.value = ''
  try { const result = await api.request('/profile/photo', 'POST', { data }); if (selected !== cropSource.value) return; form.photo = result.url; if (api.state.user) api.state.user.photo = result.url; message.value = 'Foto atualizada.'; closeCrop() }
  catch (e) { error.value = (e as Error).message } finally { busy.value = false }
}
</script>
<template>
  <section class="card article-card account-card"><div class="article-body">
    <h1>{{ titles[action] }}</h1>
    <p v-if="loading" role="status">Carregando…</p>
    <p v-if="error" class="alert alert-danger" role="alert">{{ error }}</p><p v-if="message" class="alert alert-success" role="status">{{ message }}</p>
    <p v-if="loginRequired">Entre para acessar sua conta. <RouterLink :to="{ path: '/p/login', query: { redirect: route.fullPath } }">Fazer login</RouterLink></p>
    <p v-if="action === 'activate'">Confira seu e-mail e informe o código de 6 dígitos. Você também pode abrir esta tela pelo link enviado.</p>
    <form v-if="!loading && !loginRequired" @submit.prevent="submit">
      <fieldset :disabled="busy" class="account-fields">
        <label v-if="['register'].includes(action)">Nome<input v-model="form.name" class="form-control" required maxlength="150" autocomplete="name"></label>
        <label v-if="['login','register','forgot','reset','email'].includes(action)">{{ action === 'email' ? 'Novo e-mail' : 'E-mail' }}<input v-model="form.email" type="email" class="form-control" required :maxlength="action === 'email' ? 50 : 150" autocomplete="email"></label>
        <label v-if="action === 'password'">Senha atual<input v-model="form.current_password" type="password" class="form-control" required autocomplete="current-password"></label>
        <label v-if="['login','register','reset','password'].includes(action)">{{ ['login','register'].includes(action) ? 'Senha' : 'Nova senha' }}<input v-model="form.password" type="password" class="form-control" required :minlength="passwordMinimum" maxlength="1024" :autocomplete="action === 'login' ? 'current-password' : 'new-password'"></label>
        <label v-if="['register','reset','password'].includes(action)">Confirme a senha<input v-model="form.password_confirmation" type="password" class="form-control" required :minlength="passwordMinimum" maxlength="1024" autocomplete="new-password"></label>
        <label v-if="action === 'activate'">E-mail do cadastro<input v-model="form.email" type="email" class="form-control" autocomplete="email"></label>
        <label v-if="action === 'activate'">Código de ativação<input ref="activationCode" v-model.trim="form.code" class="form-control" required inputmode="numeric" maxlength="20" pattern="[0-9]{6}|[a-f0-9]{20}" autocomplete="one-time-code"></label>
        <label v-if="action === 'email'">Código recebido por e-mail<input v-model="form.code" class="form-control" maxlength="32" autocomplete="one-time-code"></label>
        <template v-if="action === 'profile'">
          <img v-if="form.photo" :src="form.photo" class="profile-photo" alt="Sua foto">
          <label>Foto<input type="file" class="form-control" accept="image/png,image/jpeg,image/gif,image/webp,image/svg+xml" @change="photo"></label>
          <label v-for="[key, label] in personal" :key="key">{{ label }}<input v-model="form[key]" :type="key === 'DATA_NASC' ? 'date' : 'text'" :required="key === 'NOME'" class="form-control" :maxlength="key === 'ESTADO' ? 2 : key === 'APELIDO' || key === 'CLUBE' || key === 'ASSOCIACAO' ? 50 : 150"></label>
          <label>Tipo de clube<select v-model="form.TIPO_CLUBE" class="form-select"><option value=""></option><option>Desbravadores</option><option>Aventureiros</option></select></label>
          <label>Biografia<textarea v-model="form.BIOGRAFIA" class="form-control" rows="5" maxlength="10000" /></label>
          <label>Receber e-mails<select v-model="form.ENVIA_EMAIL" class="form-select"><option value="S">Sim</option><option value="N">Não</option></select></label>
        </template>
        <button class="btn btn-primary" type="submit">{{ busy ? 'Aguarde…' : action === 'login' ? 'Entrar' : action === 'register' ? 'Criar conta' : action === 'forgot' ? 'Enviar instruções' : action === 'activate' ? 'Ativar conta' : action === 'email' && !form.code ? 'Enviar código' : 'Salvar' }}</button>
        <label v-if="action === 'login'" class="d-flex flex-row gap-2"><input v-model="form.remember" type="checkbox">Manter conectado por 30 dias</label>
      </fieldset>
    </form>
    <button v-if="action === 'activate'" type="button" class="btn btn-outline-primary mt-4" :disabled="busy || !form.email" @click="resend">Reenviar código de ativação</button>
    <nav class="account-navigation"><RouterLink to="/p/login">Login</RouterLink><RouterLink to="/p/cadastro">Criar conta</RouterLink><RouterLink to="/p/esqueci-minha-senha">Esqueci minha senha</RouterLink><template v-if="api.state.user"><RouterLink to="/p/perfil">Perfil</RouterLink><RouterLink to="/p/alterar-senha">Alterar senha</RouterLink><RouterLink to="/p/alterar-email">Alterar e-mail</RouterLink></template></nav>
  </div></section>
  <AvatarCropModal v-if="cropSource" :source="cropSource" :busy="busy" :error="error" @close="closeCrop" @confirm="savePhoto" />
</template>
