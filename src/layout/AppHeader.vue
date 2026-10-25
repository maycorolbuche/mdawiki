<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { BButton, BDropdown, BDropdownItem, BFormInput, BModal } from 'bootstrap-vue-next'

const emit = defineEmits(['search', 'contribute', 'login'])
const query = ref('')
const searchInput = ref(null)
const dialogOpen = ref(false)
const dialogTitle = ref('')
const dialogMessage = ref('')

// Apenas a interface do header nesta etapa; sem API, autenticação ou navegação nova.
function showMessage(title, message) {
  dialogTitle.value = title
  dialogMessage.value = message
  dialogOpen.value = true
}
function search() {
  const term = query.value.trim()
  if (!term) {
    searchInput.value?.focus()
    return
  }
  emit('search', term)
  showMessage(
    'Pesquisar na MDAWiki',
    `Busca por “${term}”. A pesquisa será conectada à API em uma próxima etapa.`,
  )
}
function contribute() {
  emit('contribute')
  showMessage(
    'Conhecimento se constrói junto',
    'Compartilhe suas experiências e confira as fontes oficiais. A área de contribuições será integrada em uma próxima etapa.',
  )
}
function login() {
  emit('login')
  showMessage(
    'Entrar ou criar conta',
    'O acesso à conta será disponibilizado após a integração com a autenticação.',
  )
}
function keyboard(event) {
  if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k' && !dialogOpen.value) {
    event.preventDefault()
    searchInput.value?.focus()
  }
}
onMounted(() => document.addEventListener('keydown', keyboard))
onUnmounted(() => document.removeEventListener('keydown', keyboard))
</script>

<template>
  <div class="app-header-space">
    <header class="app-header" aria-label="Cabeçalho principal">
      <BDropdown class="header-navigation" variant="link" no-caret aria-label="Navegação">
        <template #button-content>
          <svg class="header-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <span class="visually-hidden">Abrir navegação</span>
        </template>
        <BDropdownItem to="/">Página inicial</BDropdownItem>
        <BDropdownItem to="/about">Sobre</BDropdownItem>
      </BDropdown>

      <RouterLink class="header-brand" to="/" aria-label="MDAWiki, página inicial">
        <svg class="header-brand-mark" viewBox="0 0 64 64" aria-hidden="true">
          <path d="M3 56 29 8 44 35 35 35 28 22 10 56Z" fill="#416747" />
          <path
            d="m30 7 8 10 7-2 18 41H38L27 37l-9 19h12L18 56 30 28 47 56h8L34 16Z"
            fill="#164b35"
          />
          <path d="M9 58h47" stroke="#a6b393" stroke-width="2" />
        </svg>
        <span
          ><span class="header-brand-name">MDAWiki</span
          ><span class="header-brand-tag">Conhecimento ao ar livre</span></span
        >
      </RouterLink>

      <form class="header-search" role="search" @submit.prevent="search">
        <label for="header-search" class="visually-hidden">Pesquisar na MDAWiki</label>
        <svg class="header-icon header-search-icon" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="10.5" cy="10.5" r="6.5" />
          <path d="m16 16 5 5" />
        </svg>
        <BFormInput
          id="header-search"
          ref="searchInput"
          v-model="query"
          type="search"
          placeholder="Pesquisar na MDAWiki…"
          autocomplete="off"
          aria-keyshortcuts="Control+k Meta+k"
        />
        <kbd class="header-search-key" aria-hidden="true">Ctrl K</kbd>
      </form>

      <div class="header-actions">
        <BButton variant="success" class="header-contribute" @click="contribute">
          <svg class="header-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="m15 4 5 5M4 20l5-1L20 8a3.5 3.5 0 0 0-5-5L4 14z" />
          </svg>
          Contribuir
        </BButton>
        <BDropdown variant="link" class="header-dropdown" no-caret end aria-label="Menu do usuário">
          <template #button-content>
            <span class="header-avatar" aria-hidden="true">MW</span>
            <svg class="header-chevron" viewBox="0 0 24 24" aria-hidden="true">
              <path d="m6 9 6 6 6-6" />
            </svg>
            <span class="visually-hidden">Menu do usuário</span>
          </template>
          <li class="dropdown-header">Visitante · demonstração</li>
          <BDropdownItem @click="login">Entrar ou criar conta</BDropdownItem>
          <BDropdownItem @click="contribute">Como colaborar</BDropdownItem>
        </BDropdown>
      </div>
    </header>
    <BModal
      v-model="dialogOpen"
      :title="dialogTitle"
      centered
      ok-only
      ok-title="Entendi"
      ok-variant="success"
    >
      <p class="mb-0">{{ dialogMessage }}</p>
    </BModal>
  </div>
</template>

<style scoped>
.app-header-space {
  grid-column: 1 / -1;
  min-height: 82px;
}
.app-header {
  --header-forest: #124d36;
  --header-muted: #66736e;
  --header-line: #e1e7e1;
  position: fixed;
  inset: 0 0 auto;
  z-index: 1030;
  display: flex;
  align-items: center;
  gap: 32px;
  min-height: 82px;
  padding: 12px 24px;
  border-bottom: 1px solid var(--header-line);
  background: rgba(255, 255, 255, 0.98);
  color: #34443e;
  font-family:
    system-ui,
    -apple-system,
    'Segoe UI',
    sans-serif;
}
.header-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
  color: #102f24;
  text-decoration: none;
  padding: 0;
}
.header-brand:hover {
  background: transparent;
}
.header-brand-mark {
  width: 51px;
  height: 51px;
  flex-shrink: 0;
}
.header-brand-name {
  display: block;
  font:
    bold 27px/1.1 Georgia,
    'Times New Roman',
    serif;
}
.header-brand-tag {
  display: block;
  color: var(--header-muted);
  margin-top: 4px;
  font-size: 12px;
}
.header-search {
  flex: 1;
  max-width: 650px;
  min-width: 0;
  margin-inline: auto;
  position: relative;
}
.header-search :deep(.form-control) {
  padding: 12px 70px 12px 43px;
  background: #f6f7f5;
  border-color: var(--header-line);
  color: #34443e;
  font-size: 14px;
  border-radius: 7px;
}
.header-search :deep(.form-control:focus) {
  border-color: #7da58c;
  box-shadow: 0 0 0 0.2rem #124d3617;
}
.header-icon,
.header-chevron {
  width: 20px;
  height: 20px;
  fill: none;
  stroke: currentColor;
  stroke-width: 1.7;
  stroke-linecap: round;
  stroke-linejoin: round;
  vertical-align: middle;
  flex-shrink: 0;
}
.header-search-icon {
  position: absolute;
  top: 14px;
  left: 14px;
  z-index: 1;
  color: #53655b;
}
.header-search-key {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 0 6px;
  border: 1px solid #dce2dd;
  border-radius: 5px;
  background: white;
  color: #77827c;
  font:
    12px/1.5 system-ui,
    sans-serif;
}
.header-actions {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-shrink: 0;
}
.header-contribute {
  --bs-btn-bg: var(--header-forest);
  --bs-btn-border-color: var(--header-forest);
  --bs-btn-hover-bg: #0d3928;
  --bs-btn-hover-border-color: #0d3928;
  --bs-btn-active-bg: #0d3928;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 17px;
  font-size: 14px;
  font-weight: 600;
}
.header-contribute .header-icon {
  width: 16px;
  height: 16px;
}
.header-dropdown :deep(.dropdown-toggle),
.header-navigation :deep(.dropdown-toggle) {
  border: 0;
  border-radius: 8px;
  color: #4b5c53;
  padding: 8px;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 6px;
  box-shadow: none;
}
.header-dropdown :deep(.dropdown-toggle:hover),
.header-navigation :deep(.dropdown-toggle:hover) {
  background: #e8f0e6;
}
.header-dropdown :deep(.dropdown-menu),
.header-navigation :deep(.dropdown-menu) {
  --bs-dropdown-link-hover-bg: #e8f0e6;
  --bs-dropdown-link-active-bg: #124d36;
  font-size: 14px;
  min-width: 220px;
}
.header-dropdown :deep(.dropdown-item),
.header-navigation :deep(.dropdown-item) {
  padding: 8px 16px;
  transition: none;
}
.header-avatar {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  background: #e8d7bd;
  display: inline-grid;
  place-items: center;
  color: #674c32;
  font-size: 12px;
  font-weight: 700;
  border: 2px solid white;
}
.header-chevron {
  width: 13px;
  height: 13px;
}
.header-notifications {
  max-width: 260px;
  padding: 10px 16px;
  color: var(--header-muted);
}
.header-navigation {
  display: none;
}
.app-header :deep(:focus-visible) {
  outline: 3px solid #d59c49;
  outline-offset: 3px;
}
@media (max-width: 1199.98px) {
  .app-header {
    gap: 20px;
  }
  .header-brand-tag,
  .header-search-key {
    display: none;
  }
  .header-brand-name {
    font-size: 23px;
  }
  .header-search :deep(.form-control) {
    padding-right: 12px;
  }
}
@media (max-width: 991.98px) {
  .app-header {
    gap: 12px;
    min-height: 72px;
  }
  .app-header-space {
    min-height: 72px;
  }
  .header-navigation {
    display: block;
  }
  .header-brand-mark {
    width: 38px;
    height: 38px;
  }
  .header-brand {
    gap: 7px;
  }
  .header-actions {
    gap: 8px;
  }
  .header-contribute {
    padding: 9px 12px;
  }
}
@media (max-width: 767.98px) {
  .header-contribute {
    display: none;
  }
}
@media (max-width: 575.98px) {
  .app-header-space {
    min-height: 122px;
  }
  .app-header {
    flex-wrap: wrap;
    padding: 12px 16px;
    gap: 8px;
  }
  .header-brand {
    flex: 1;
  }
  .header-search {
    order: 3;
    flex-basis: 100%;
    max-width: none;
  }
  .header-search :deep(.form-control) {
    padding-top: 9px;
    padding-bottom: 9px;
  }
  .header-search-icon {
    top: 11px;
  }
  .header-actions {
    gap: 0;
  }
  .header-dropdown :deep(.dropdown-toggle) {
    padding: 5px;
  }
}
@media (prefers-reduced-motion: no-preference) {
  .header-contribute {
    transition: background-color 0.15s;
  }
}
@media print {
  .app-header-space {
    display: none;
  }
}
</style>
