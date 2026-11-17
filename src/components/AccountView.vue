<script setup lang="ts">
import { computed, inject, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { SessionClient } from '../services/session'
import { RequestError } from '../services/session'
const route = useRoute(), router = useRouter(), api = inject<SessionClient>('session')!
const action = computed(() => String(route.meta.action))
const titles: Record<string, string> = { login: 'Entrar na MDAWiki', register: 'Criar conta', forgot: 'Recuperar senha', reset: 'Redefinir senha', activate: 'Ativar conta', profile: 'Meu perfil', password: 'Alterar senha', email: 'Alterar e-mail' }
const form = reactive<Record<string, any>>({}), busy = ref(false), loading = ref(false), error = ref(''), message = ref(''), loginRequired = ref(false)
const personal = [['NOME','Nome'],['APELIDO','Apelido'],['DATA_NASC','Data de nascimento'],['CIDADE','Cidade'],['ESTADO','Estado'],['CLUBE','Clube'],['ASSOCIACAO','Associação / Missão']] as const
const protectedAction = computed(() => ['profile','password','email'].includes(action.value))
async function load() {
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
async function submit() {
  busy.value = true; error.value = ''; message.value = ''
  try {
    const endpoint = { login: '/auth/login', register: '/auth/register', forgot: '/auth/forgot', reset: '/auth/reset', activate: '/auth/activate', profile: '/profile', password: '/profile/password', email: '/profile/email' }[action.value]
    if (!endpoint) return
    const result = await api.request(endpoint, action.value === 'profile' ? 'PUT' : 'POST', form)
    message.value = result.message || 'Dados salvos.'
    if (action.value === 'profile' && api.state.user) Object.assign(api.state.user, result)
    if (action.value === 'login') { const redirect = String(route.query.redirect || '/'); await router.push(redirect.startsWith('/') && !redirect.startsWith('//') ? redirect : '/') }
    if (action.value === 'register') await router.push('/page@AtivaConta')
    if (['reset','activate'].includes(action.value)) await router.push('/page@Acesso')
    form.password = ''; form.password_confirmation = ''; form.current_password = ''
  } catch (e) { error.value = (e as Error).message }
  finally { busy.value = false }
}
async function resend() {
  busy.value = true; error.value = ''
  try { const result = await api.request('/auth/resend', 'POST', { email: form.email }); message.value = result.message }
  catch (e) { error.value = (e as Error).message } finally { busy.value = false }
}
async function photo(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]; if (!file) return
  if (file.size > 5000000) { error.value = 'A imagem deve ter até 5 MB.'; return }
  busy.value = true
  try { const data = await new Promise<string>((resolve, reject) => { const r = new FileReader(); r.onload = () => resolve(String(r.result)); r.onerror = () => reject(new Error('Não foi possível ler a imagem.')); r.readAsDataURL(file) }); const result = await api.request('/profile/photo', 'POST', { data }); form.photo = result.url; if (api.state.user) api.state.user.photo = result.url; message.value = 'Foto atualizada.' }
  catch (e) { error.value = (e as Error).message } finally { busy.value = false }
}
</script>
<template>
  <section class="card article-card account-card"><div class="article-body">
    <h1>{{ titles[action] }}</h1>
    <p v-if="loading" role="status">Carregando…</p>
    <p v-if="error" class="alert alert-danger" role="alert">{{ error }}</p><p v-if="message" class="alert alert-success" role="status">{{ message }}</p>
    <p v-if="loginRequired">Entre para acessar sua conta. <RouterLink :to="{ path: '/page@Acesso', query: { redirect: route.fullPath } }">Fazer login</RouterLink></p>
    <form v-if="!loading && !loginRequired" @submit.prevent="submit">
      <fieldset :disabled="busy" class="account-fields">
        <label v-if="['register'].includes(action)">Nome<input v-model="form.name" class="form-control" required maxlength="150" autocomplete="name"></label>
        <label v-if="['login','register','forgot','reset','email'].includes(action)">{{ action === 'email' ? 'Novo e-mail' : 'E-mail' }}<input v-model="form.email" type="email" class="form-control" required :maxlength="action === 'email' ? 50 : 150" autocomplete="email"></label>
        <label v-if="action === 'password'">Senha atual<input v-model="form.current_password" type="password" class="form-control" required autocomplete="current-password"></label>
        <label v-if="['login','register','reset','password'].includes(action)">{{ action === 'login' ? 'Senha' : 'Nova senha' }}<input v-model="form.password" type="password" class="form-control" required :minlength="action === 'login' ? 1 : 10" maxlength="1024" :autocomplete="action === 'login' ? 'current-password' : 'new-password'"></label>
        <label v-if="['register','reset','password'].includes(action)">Confirme a senha<input v-model="form.password_confirmation" type="password" class="form-control" required minlength="10" maxlength="1024" autocomplete="new-password"></label>
        <label v-if="['activate','email'].includes(action)">Código recebido por e-mail<input v-model="form.code" class="form-control" :required="action === 'activate'" maxlength="32" autocomplete="one-time-code"></label>
        <template v-if="action === 'profile'">
          <img v-if="form.photo" :src="form.photo" class="profile-photo" alt="Sua foto">
          <label>Foto<input type="file" class="form-control" accept="image/png,image/jpeg,image/gif,image/webp" @change="photo"></label>
          <label v-for="[key, label] in personal" :key="key">{{ label }}<input v-model="form[key]" :type="key === 'DATA_NASC' ? 'date' : 'text'" :required="key === 'NOME'" class="form-control" :maxlength="key === 'ESTADO' ? 2 : key === 'APELIDO' || key === 'CLUBE' || key === 'ASSOCIACAO' ? 50 : 150"></label>
          <label>Tipo de clube<select v-model="form.TIPO_CLUBE" class="form-select"><option value=""></option><option>Desbravadores</option><option>Aventureiros</option></select></label>
          <label>Biografia<textarea v-model="form.BIOGRAFIA" class="form-control" rows="5" maxlength="10000" /></label>
          <label>Receber e-mails<select v-model="form.ENVIA_EMAIL" class="form-select"><option value="S">Sim</option><option value="N">Não</option></select></label>
        </template>
        <button class="btn btn-primary" type="submit">{{ busy ? 'Aguarde…' : action === 'login' ? 'Entrar' : action === 'register' ? 'Criar conta' : action === 'forgot' ? 'Enviar instruções' : action === 'activate' ? 'Ativar conta' : action === 'email' && !form.code ? 'Enviar código' : 'Salvar' }}</button>
        <label v-if="action === 'login'" class="d-flex flex-row gap-2"><input v-model="form.remember" type="checkbox">Manter conectado por 30 dias</label>
      </fieldset>
    </form>
    <form v-if="action === 'activate'" class="mt-4" @submit.prevent="resend"><label for="resend-email">Reenviar código de ativação</label><div class="input-group"><input id="resend-email" v-model="form.email" type="email" class="form-control" placeholder="Seu e-mail" required><button class="btn btn-outline-primary" :disabled="busy">Reenviar</button></div></form>
    <nav class="account-navigation"><RouterLink to="/page@Acesso">Login</RouterLink><RouterLink to="/page@Cadastre-se">Criar conta</RouterLink><RouterLink to="/page@EsqueciMinhaSenha">Esqueci minha senha</RouterLink><template v-if="api.state.user"><RouterLink to="/page@Perfil">Perfil</RouterLink><RouterLink to="/page@AlterarSenha">Alterar senha</RouterLink><RouterLink to="/page@AlterarEmail">Alterar e-mail</RouterLink></template></nav>
  </div></section>
</template>
