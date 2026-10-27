<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { BButton, BFormInput } from 'bootstrap-vue-next'
import TopMenu from './TopMenu.vue'

defineProps({ siteMenuOpen: { type: Boolean, default: false } })
const emit = defineEmits(['navigate', 'notice', 'open-discussion', 'open-history', 'open-help', 'open-site-menu'])

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
        class="d-xl-none me-2 border"
        aria-controls="menu-site-mobile"
        :aria-expanded="siteMenuOpen"
        @click="emit('open-site-menu')"
      >
        <i class="bi bi-list" aria-hidden="true"></i> Menu do site
      </BButton>
      <TopMenu class="d-none d-xl-flex" @navigate="emit('navigate', $event)"
        @notice="emit('notice', $event)" @open-discussion="emit('open-discussion')"
        @open-history="emit('open-history')" @open-help="emit('open-help')" />
      <BButton variant="primary" class="contribute ms-lg-3 d-none d-xl-block" @click="emit('open-help')">
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



