<script setup lang="ts">
import { computed, inject, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { SessionClient } from '../services/session'
const session = inject<SessionClient>('session')!
const route = useRoute(), router = useRouter()
const open = ref(false), busy = ref(false), error = ref(''), failedPhoto = ref('')
const root = ref<HTMLElement>(), trigger = ref<HTMLButtonElement>()
const name = computed(() => session.state.user?.APELIDO || session.state.user?.NOME || 'Minha conta')
const photo = computed(() => session.state.user?.photo && failedPhoto.value !== session.state.user.photo ? session.state.user.photo : null)
function outside(event: Event) { if (!root.value?.contains(event.target as Node)) open.value = false }
function escape() { open.value = false; trigger.value?.focus() }
async function firstItem() { open.value = true; await nextTick(); root.value?.querySelector<HTMLAnchorElement>('.dropdown-item')?.focus() }
async function logout() {
  busy.value = true; error.value = ''
  try { await session.logout(); open.value = false; await router.push('/') }
  catch { error.value = 'Não foi possível sair. Tente novamente.' }
  finally { busy.value = false }
}
watch(() => route.fullPath, () => { open.value = false })
watch(() => session.state.user?.ID, () => { open.value = false; failedPhoto.value = '' })
onMounted(() => document.addEventListener('pointerdown', outside))
onBeforeUnmount(() => document.removeEventListener('pointerdown', outside))
</script>

<template>
  <nav v-if="session.state.user" ref="root" class="user-account" aria-label="Conta" @keydown.esc.stop.prevent="escape" @focusout="event => { if (!root?.contains(event.relatedTarget as Node)) open = false }">
    <button ref="trigger" class="account-trigger" type="button" :aria-label="`Menu de ${name}`" :aria-expanded="open" aria-controls="account-dropdown" @click="open = !open" @keydown.down.prevent="firstItem">
      <img v-if="photo" :src="photo" class="account-avatar" alt="Seu avatar" width="32" height="32" @error="failedPhoto = photo || ''">
      <i v-else class="bi bi-person-circle account-avatar-fallback" aria-hidden="true" />
      <span class="account-name">{{ name }}</span><i class="bi bi-chevron-down account-chevron" aria-hidden="true" />
    </button>
    <Transition name="account-dropdown">
      <ul v-if="open" id="account-dropdown" class="dropdown-menu show account-dropdown">
        <li><RouterLink class="dropdown-item" to="/page@Perfil"><i class="bi bi-person-fill" aria-hidden="true" /> Meu Perfil</RouterLink></li>
        <li><RouterLink class="dropdown-item" to="/page@AlterarEmail"><i class="bi bi-envelope-fill" aria-hidden="true" /> Alterar E-mail</RouterLink></li>
        <li><RouterLink class="dropdown-item" to="/page@AlterarSenha"><i class="bi bi-lock-fill" aria-hidden="true" /> Alterar Senha</RouterLink></li>
        <li><hr class="dropdown-divider"></li>
        <li><button type="button" class="dropdown-item" :disabled="busy" @click="logout"><i class="bi bi-power" aria-hidden="true" /> {{ busy ? 'Saindo…' : 'Sair' }}</button></li>
        <li v-if="error" class="px-3 py-2 text-danger" role="alert">{{ error }}</li>
      </ul>
    </Transition>
  </nav>
  <nav v-else class="account-links" aria-label="Conta"><RouterLink to="/page@Acesso"><i class="bi bi-person-fill" aria-hidden="true" /> Login</RouterLink><RouterLink to="/page@Cadastre-se"><i class="bi bi-person-plus-fill" aria-hidden="true" /> Cadastre-se</RouterLink></nav>
</template>

<style scoped>
.user-account { position: relative; margin-left: auto; flex-shrink: 0; }
.account-trigger { display: flex; align-items: center; gap: 12px; background: transparent; border: 0; color: white; padding: 8px 0 8px 8px; font: inherit; }
.account-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }
.account-avatar-fallback { font-size: 32px; line-height: 1; }
.account-name { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.account-chevron { font-size: 10px; opacity: .7; }
.account-dropdown { left: auto; right: 0; top: 100%; min-width: 250px; padding: 0; box-shadow: 0 6px 15px #0003; }
.account-dropdown .dropdown-item { display: flex; align-items: center; gap: 10px; padding: 12px 16px; font-size: 13px; }
.account-dropdown li + li { border-top: 1px solid #eee; }
.account-dropdown .dropdown-divider { margin: 0; }
.account-dropdown-enter-active, .account-dropdown-leave-active { transition: opacity .15s ease, transform .15s ease; }
.account-dropdown-enter-from, .account-dropdown-leave-to { opacity: 0; transform: translateY(-5px); }
@media(max-width:575px) { .account-name, .account-chevron { display: none; } .account-trigger { padding-left: 0; } .account-dropdown { min-width: 230px; } }
@media(prefers-reduced-motion:reduce) { .account-dropdown-enter-active, .account-dropdown-leave-active { transition: none; } }
</style>
