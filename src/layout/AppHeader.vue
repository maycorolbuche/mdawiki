<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { BButton, BCollapse, BDropdown, BDropdownItem, BFormInput } from 'bootstrap-vue-next'

const emit = defineEmits(['navigate', 'notice', 'open-discussion', 'open-history', 'open-help'])

const isMenuOpen = ref(false)
const search = ref('')
const areResultsVisible = ref(false)
const headerElement = ref(null)
let observer

const normalize = (text) =>
  text
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
const results = computed(() => {
  const term = normalize(search.value.trim())
  return term
    ? [
        {
          id: 'requisitos',
          title: 'Requisitos',
        },
        {
          id: 'materiais',
          title: 'Materiais de estudo',
        },
        { id: 'referencias', title: 'Referências', content: 'Fontes e dados da especialidade' },
        { id: 'relacionadas', title: 'Páginas relacionadas', content: 'Artigos relacionados' },
        { id: 'categorias', title: 'Categorias', content: 'ADRA Nível 1 Especialidades' },
      ]
    : []
})
function goTo(id) {
  isMenuOpen.value = false
  areResultsVisible.value = false
  emit('navigate', id)
}
function showNotice(message) {
  emit('notice', message)
}
function closeResultsOnFocusOut(event) {
  if (!event.currentTarget.contains(event.relatedTarget)) areResultsVisible.value = false
}
onMounted(() => {
  if (typeof ResizeObserver !== 'undefined' && headerElement.value) {
    observer = new ResizeObserver(([entry]) => {
      document.documentElement.style.setProperty(
        '--header-height',
        `${entry.target.getBoundingClientRect().height}px`,
      )
    })
    observer.observe(headerElement.value)
  }
})
onUnmounted(() => observer?.disconnect())
</script>

<template>
  <header ref="headerElement" class="app-header position-sticky top-0 z-3 shadow-sm">
    <div
      class="brandbar d-flex align-items-center gap-3 gap-lg-4 px-3 px-lg-4 py-2 text-white bg-primary"
    >
      <a
        class="brand d-flex align-items-center gap-1 text-decoration-none fw-semibold flex-shrink-0"
        href="/"
      >
        <img src="@/assets/images/logo.png" style="height: 40px" />
        <img src="@/assets/images/wordmark.png" style="height: 40px" />
      </a>
      <form
        class="site-search position-relative mx-lg-auto"
        role="search"
        @submit.prevent="areResultsVisible = true"
        @focusout="closeResultsOnFocusOut"
      >
        <label class="visually-hidden" for="searchWiki">Pesquisar neste artigo</label>
        <i class="bi bi-search" aria-hidden="true"> </i>
        <BFormInput
          id="searchWiki"
          v-model="search"
          type="search"
          placeholder="Pesquisar…"
          autocomplete="off"
          aria-controls="searchResults"
          :aria-expanded="areResultsVisible && !!search.trim()"
          @input="areResultsVisible = true"
          @focus="areResultsVisible = true"
        />
        <div
          v-if="areResultsVisible && search.trim()"
          id="searchResults"
          class="search-results position-absolute start-0 end-0 bg-white border rounded shadow-sm p-2"
          aria-live="polite"
        >
          <button v-for="result in results" :key="result.id" type="button" @click="goTo(result.id)">
            {{ result.title }}
          </button>
          <p v-if="!results.length" class="small text-secondary m-2">
            Nenhuma seção encontrada neste artigo.
          </p>
        </div>
      </form>
      <div class="account d-flex align-items-center gap-3 flex-shrink-0">
        <a href="#" @click.prevent="showNotice('Conecte este link à tela de login da sua wiki.')">
          Entrar
        </a>
        <BButton
          variant="outline-light"
          @click="showNotice('Conecte este botão ao cadastro de usuários.')"
        >
          Criar conta
        </BButton>
      </div>
    </div>
    <nav class="navbar navbar-expand-lg site-nav bg-white px-3 px-lg-4" aria-label="Menu principal">
      <BButton
        variant="light"
        class="navbar-toggler"
        aria-controls="menu-principal"
        :aria-expanded="isMenuOpen"
        :aria-label="isMenuOpen ? 'Fechar menu' : 'Abrir menu'"
        @click="isMenuOpen = !isMenuOpen"
      >
        <span class="navbar-toggler-icon"> </span>
      </BButton>
      <BCollapse id="menu-principal" v-model="isMenuOpen" is-nav class="navbar-collapse">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="#artigo" @click.prevent="goTo('artigo')">
              <i class="bi bi-house-door"> </i>
              Início
            </a>
          </li>
          <li class="nav-item">
            <BDropdown variant="link" toggle-class="nav-link" no-caret>
              <template #button-content>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="16px"
                  height="16px"
                  viewBox="0 0 16 16"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="0.8"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  aria-hidden="true"
                  focusable="false"
                >
                  <path d="M1 1.4h14v2.1L8 15 1 3.5z" />
                  <path
                    d="M3.8 4.2c1.5.4 2.7.3 4.2-.1 1.5.4 2.7.5 4.2.1-.2 2.5-1.1 4.3-4.2 6.2C4.9 8.5 4 6.7 3.8 4.2z"
                  />
                  <path d="M4.3 8.4 12.8 5.2 5 9.2z" fill="currentColor" stroke="none" />
                  <path d="m3.5 8.5 1 1.4M4.2 7.5l1.2 2.6M3.2 9.4l.5.7" />
                </svg>

                Desbravadores
                <i class="bi bi-chevron-down small"></i>
              </template>
              <BDropdownItem @click="showNotice('Adicione a rota de classes.')">
                Classes
              </BDropdownItem>
              <BDropdownItem @click="goTo('requisitos')">Especialidades</BDropdownItem>
              <BDropdownItem @click="showNotice('Adicione a rota de materiais para clubes.')">
                Materiais para clubes
              </BDropdownItem>
            </BDropdown>
          </li>
          <li class="nav-item">
            <BDropdown variant="link" toggle-class="nav-link" no-caret>
              <template #button-content>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="16px"
                  height="16px"
                  viewBox="0 0 16 16"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="0.8"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  aria-hidden="true"
                  focusable="false"
                >
                  <path
                    d="M8 1C4.2 1 1 3.2 1 5.8c0 2 1.5 4.3 3.1 6.2C5.4 13.6 6.4 15 8 15s2.6-1.4 3.9-3C13.5 10.1 15 7.8 15 5.8 15 3.2 11.8 1 8 1z"
                  />
                  <ellipse cx="8" cy="6.7" rx="5.3" ry="4.2" />
                  <path d="M8 3.5v3.1M7.2 4.4h1.6" />
                  <path d="m3.1 8.4 2-1.8 1.3 1 1.1-.8 2 1.4 1.1-.8 2.3 1.7" />
                  <path d="M7.1 10.8c.2-.8 1.9-.8 2-1.6" />
                </svg>

                Aventureiros
                <i class="bi bi-chevron-down small"></i>
              </template>
              <BDropdownItem @click="showNotice('Adicione a rota das classes de Aventureiros.')"
                >Classes</BDropdownItem
              >
              <BDropdownItem
                @click="showNotice('Adicione a rota das especialidades de Aventureiros.')"
                >Especialidades</BDropdownItem
              >
            </BDropdown>
          </li>
          <li class="nav-item">
            <a
              class="nav-link active"
              href="#requisitos"
              aria-current="page"
              @click.prevent="goTo('requisitos')"
            >
              <i class="bi bi-award"> </i>Especialidades</a
            >
          </li>
          <li class="nav-item">
            <a
              class="nav-link"
              href="#"
              @click.prevent="showNotice('Adicione a rota de acampamento.')"
            >
              <i class="bi bi-tree"> </i>Acampamento</a
            >
          </li>
          <li class="nav-item">
            <BDropdown variant="link" toggle-class="nav-link" no-caret>
              <template #button-content>
                <i class="bi bi-people"> </i> Comunidade
                <i class="bi bi-chevron-down small"> </i>
              </template>
              <BDropdownItem @click="emit('open-discussion')">Discussão</BDropdownItem>
              <BDropdownItem @click="emit('open-history')">Mudanças recentes</BDropdownItem>
              <BDropdownItem @click="emit('open-help')">Como colaborar</BDropdownItem>
            </BDropdown>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" @click.prevent="emit('open-help')">
              <i class="bi bi-question-circle"> </i>Ajuda</a
            >
          </li>
        </ul>
      </BCollapse>
      <BButton variant="primary" class="contribute ms-lg-3" @click="emit('open-help')">
        <i class="bi bi-pencil me-2"> </i>Contribuir</BButton
      >
    </nav>
  </header>
</template>

<style lang="scss">
.brandbar {
  min-height: 58px;
  background-image: linear-gradient(115deg, #064c36, #063d31);
}
.brand {
  font-size: 1.5rem;
  line-height: 1;
  white-space: nowrap;
  i {
    font-size: 1.875rem;
  }
}
.site-search {
  width: min(100%, 640px);
  > i {
    position: absolute;
    left: 15px;
    top: 5px;
    z-index: 1;
    pointer-events: none;
  }
  input {
    padding-left: 42px;
    font-size: 0.8125rem;
    color: white;
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.3);
    &::placeholder {
      color: #e1eee7;
      opacity: 1;
    }
    &:focus {
      color: white;
      background: rgba(255, 255, 255, 0.13);
      border-color: #b9d3c6;
      box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.08);
    }
  }
}
.search-results {
  top: calc(100% + 6px);
  z-index: 1050;
  max-height: 320px;
  overflow: auto;
  button {
    display: block;
    width: 100%;
    padding: 0.625rem;
    text-align: left;
    color: var(--bs-primary);
    background: transparent;
    border: 0;
    border-radius: var(--bs-border-radius-sm);
    &:hover,
    &:focus-visible {
      background: var(--bs-primary-bg-subtle);
    }
  }
}
.account {
  font-size: 0.75rem;
  white-space: nowrap;
  a {
    color: white;
    text-decoration: none;
    &:hover {
      text-decoration: underline;
    }
  }
  .btn {
    font-size: 0.75rem;
  }
}
.site-nav {
  min-height: 64px;
  .navbar-nav {
    gap: clamp(0.5rem, 1.3vw, 1.3rem);
  }
  .nav-link {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.25rem 0.3rem;
    font-size: 0.8125rem;
    white-space: nowrap;
    color: var(--bs-body-color);
    border: 0;
    border-bottom: 3px solid transparent;
    border-radius: 0;
    i {
      font-size: 1.2rem;
    }
    &:hover,
    &.active {
      color: var(--bs-primary);
    }
    &.active {
      border-bottom-color: var(--bs-primary);
      font-weight: 600;
    }
  }
  .dropdown-toggle {
    text-decoration: none;
  }
  .dropdown-menu {
    min-width: 185px;
  }
  .dropdown-item {
    font-size: 0.8125rem;
  }
  .contribute {
    font-size: 0.75rem;
    white-space: nowrap;
  }
}
@media (max-width: 1199.98px) {
  .site-nav .nav-link {
    gap: 0.3rem;
    font-size: 0.75rem;
    i {
      font-size: 1rem;
    }
  }
}
@media (max-width: 991.98px) {
  .site-nav {
    min-height: 54px;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    .navbar-collapse {
      flex-basis: 100%;
      order: 3;
      max-height: calc(100dvh - 140px);
      overflow: auto;
    }
    .navbar-nav {
      gap: 0;
      padding: 0.625rem 0;
    }
    .nav-link {
      padding: 0.75rem;
      border-bottom: 0;
      border-left: 3px solid transparent;
      &.active {
        background: var(--bs-primary-bg-subtle);
        border-left-color: var(--bs-primary);
      }
    }
    .contribute {
      margin-left: auto;
    }
    .dropdown-menu {
      border: 0;
      background: var(--bs-light);
    }
  }
  .site-search {
    max-width: 410px;
  }
  .account > a {
    display: none;
  }
}
@media (max-width: 575.98px) {
  .brandbar {
    flex-wrap: wrap;
  }
  .site-search {
    order: 3;
    width: 100%;
    max-width: none;
  }
  .account {
    margin-left: auto;
  }
  .brand {
    font-size: 1.375rem;
  }
}
@media print {
  .app-header {
    display: none !important;
  }
}
</style>
