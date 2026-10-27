<script setup>
import { BDropdown, BDropdownItem } from 'bootstrap-vue-next'

defineProps({ mobile: { type: Boolean, default: false } })
const emit = defineEmits(['navigate', 'notice', 'open-discussion', 'open-history', 'open-help', 'select'])
function select() { emit('select') }
function goTo(id) { emit('navigate', id); select() }
function showNotice(message) { emit('notice', message); select() }
function act(event) { emit(event); select() }
</script>

<template>
        <ul :class="['navbar-nav me-auto', { 'top-menu-mobile': mobile }]">
          <li class="nav-item">
            <RouterLink class="nav-link" to="/" @click="select()">
              <i class="bi bi-house-door"> </i>
              Início
            </RouterLink>
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
              <BDropdownItem to="/especialidade-de-resposta-a-emergencias-e-desastres" @click="select()">
                Exemplo: Resposta a Emergências e Desastres
              </BDropdownItem>
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
              href="/acampamento"
              @click.prevent="$router.push('/acampamento'); select()"
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
              <BDropdownItem @click="act('open-discussion')">Discussão</BDropdownItem>
              <BDropdownItem @click="act('open-history')">Mudanças recentes</BDropdownItem>
              <BDropdownItem @click="act('open-help')">Como colaborar</BDropdownItem>
            </BDropdown>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" @click.prevent="act('open-help')">
              <i class="bi bi-question-circle"> </i>Ajuda</a
            >
          </li>
          <li v-if="mobile" class="nav-item">
            <a class="nav-link" href="#" @click.prevent="act('open-help')">
              <i class="bi bi-pencil"></i> Contribuir
            </a>
          </li>
        </ul>
</template>

<style lang="scss">
.top-menu-mobile {
  gap: 0;
  .nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0;
    color: #59635f;
    font-size: 0.8125rem;
    text-decoration: none;
    border: 0;
  }
  .nav-link:hover { color: var(--bs-primary); }
  .nav-link i { font-size: 1.15rem; }
  .dropdown-menu { max-width: min(90vw, 300px); white-space: normal; }
  .dropdown-item { white-space: normal; font-size: 0.8125rem; }
}
</style>
