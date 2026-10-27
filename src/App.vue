<script setup>
import { nextTick, onUnmounted, ref } from 'vue'
import AppHeader from './layout/AppHeader.vue'
import pathfinderEmblem from './assets/images/pathfinder-emblem.svg'
import specialtyEmblem from './assets/images/specialty-ad004.png'
import AppFooter from './layout/AppFooter.vue'
import AppSidebar from './layout/AppSidebar.vue'
import {
  BApp,
  BButton,
  BCollapse,
  BFormTextarea,
  BModal,
  BOffcanvas,
  BTab,
  BTabs,
} from 'bootstrap-vue-next'
import 'bootstrap-vue-next/dist/bootstrap-vue-next.css'
import 'bootstrap-icons/font/bootstrap-icons.css'

// Dados demonstrativos: substitua-os pelos dados da API da MDAWiki.
const article = {
  title: 'Especialidade de Resposta a Emergências e Desastres',
  summary: 'Conhecimento e preparo para servir em situações de emergência.',
  area: 'ADRA',
  code: 'AD 004',
  level: '1',
  year: '2005',
  origin: 'Associação Geral',
}
const requirements = [
  'Explique os danos causados pelos principais tipos de emergências e desastres.',
  'Descreva os serviços prestados pela ADRA e por órgãos de resposta a emergências.',
  'Pesquise uma notícia recente da ADRA e discuta como responder à situação apresentada.',
  'Explique o significado da sigla ADRA e seu papel na resposta a desastres.',
  'Conheça as organizações que atuam em parceria com a ADRA.',
]
const events = [
  'Ciclones (furacões e tufões)',
  'Tornados',
  'Inundações',
  'Terremotos',
  'Tsunamis',
  'Incêndios',
  'Guerras e conflitos',
]
const materials = [
  { title: 'ADRA Brasil — projetos e ações humanitárias', url: 'https://adra.org.br/' },
  { title: 'Comitê Internacional da Cruz Vermelha', url: 'https://www.icrc.org/pt' },
  { title: 'ACNUR — proteção e assistência a refugiados', url: 'https://www.acnur.org/br/' },
  { title: 'Programa Mundial de Alimentos', url: 'https://pt.wfp.org/' },
]
const sections = [
  {
    id: 'requisitos',
    title: 'Requisitos',
    content: requirements.join(' ') + ' ' + events.join(' '),
  },
  {
    id: 'materiais',
    title: 'Materiais de estudo',
    content: materials.map((x) => x.title).join(' '),
  },
  { id: 'referencias', title: 'Referências', content: 'Fontes e dados da especialidade' },
  { id: 'relacionadas', title: 'Páginas relacionadas', content: 'Artigos relacionados' },
  { id: 'categorias', title: 'Categorias', content: 'ADRA Nível 1 Especialidades' },
]
const isTableOfContentsOpen = ref(false)
const isHelpModalOpen = ref(false)
const isEditModalOpen = ref(false)
const isHistoryModalOpen = ref(false)
const isCollaborationOpen = ref(true)
const isDiscussionOpen = ref(true)
const isInformationOpen = ref(true)
const activeTab = ref(0)
const editText = ref('')
const editSection = ref('página')
const notice = ref('')
let noticeTimer
function showNotice(message) {
  notice.value = message
  clearTimeout(noticeTimer)
  noticeTimer = setTimeout(() => {
    notice.value = ''
  }, 4000)
}
async function goTo(id) {
  activeTab.value = 0
  isTableOfContentsOpen.value = false
  await nextTick()
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}
function openEdit(section = 'página') {
  editSection.value = section
  if (section === 'página')
    editText.value = [article.title, article.summary, ...requirements].join('\n\n')
  else if (section === 'requisitos')
    editText.value = requirements.map((item, i) => `${i + 1}. ${item}`).join('\n\n')
  else if (section === 'materiais')
    editText.value = materials.map((item) => `${item.title} — ${item.url}`).join('\n')
  else editText.value = 'Adicione suas referências aqui.'
  isEditModalOpen.value = true
}
function downloadSuggestion() {
  const file = new Blob([`Sugestão para: ${editSection.value}\n\n${editText.value}`], {
    type: 'text/plain;charset=utf-8',
  })
  const url = URL.createObjectURL(file)
  const link = document.createElement('a')
  link.href = url
  link.download = 'sugestao-mdawiki.txt'
  link.click()
  setTimeout(() => URL.revokeObjectURL(url), 1000)
  isEditModalOpen.value = false
  showNotice('Sugestão baixada. Nenhuma alteração foi publicada.')
}
async function printArticle() {
  activeTab.value = 0
  await nextTick()
  window.print()
}
onUnmounted(() => {
  clearTimeout(noticeTimer)
})
</script>

<template>
  <BApp>
    <AppHeader
      @navigate="goTo"
      @notice="showNotice"
      @open-discussion="activeTab = 1"
      @open-history="isHistoryModalOpen = true"
      @open-help="isHelpModalOpen = true"
    />

    <div class="workspace">
      <AppSidebar />

      <main id="artigo" class="page-wrap d-flex justify-content-center" tabindex="-1">
        <div class="w-100 article-container">
          <div class="mobile-tools d-flex d-xl-none justify-content-end gap-2 mb-3">
            <BButton
              variant="light"
              class="border"
              aria-controls="indice-mobile"
              :aria-expanded="isTableOfContentsOpen"
              @click="isTableOfContentsOpen = true"
              ><i class="bi bi-list-ul me-2"></i>Índice do artigo</BButton
            ><BButton variant="light" class="border" @click="printArticle"
              ><i class="bi bi-printer me-2"></i>Imprimir</BButton
            >
          </div>
          <article class="paper bg-white border rounded-1 shadow-sm">
            <div class="toolbar-edit position-relative text-end">
              <BButton
                variant="link"
                class="edit-btn border-0 bg-transparent text-secondary"
                @click="openEdit()"
                ><i class="bi bi-pencil me-1"></i>Editar</BButton
              >
            </div>
            <BTabs
              v-model="activeTab"
              class="article-tabs-container"
              nav-class="article-tabs"
              content-class="article-content"
            >
              <BTab
                ><template #title><i class="bi bi-file-earmark-text me-2"></i>Artigo</template>
                <p class="eyebrow">
                  <a href="#categorias" @click.prevent="goTo('categorias')">Especialidades</a> /
                  <strong>{{ article.area }}</strong>
                </p>
                <h1>{{ article.title }}</h1>
                <p class="article-intro">{{ article.summary }}</p>
                <p class="article-support">
                  Material de apoio para estudos:
                  <a href="https://plus.mda.wiki.br/especialidade-de-resposta-a-emergencias-e-desastres/"
                    >https://plus.mda.wiki.br/especialidade-de-resposta-a-emergencias-e-desastres/</a>
                </p>
                <hr class="article-divider" />
                <div class="specialty-wrapper" role="region" aria-label="Tabela de dados da especialidade" tabindex="0">
                  <table class="specialty" aria-label="Dados da especialidade">
                    <tbody>
                      <tr>
                        <th rowspan="2" class="specialty-image">
                          <img :src="pathfinderEmblem" alt="Emblema dos Desbravadores" />
                        </th>
                        <th scope="col">Área</th>
                        <th scope="col">Código</th>
                        <th scope="col">Nível</th>
                        <th scope="col">Ano</th>
                        <th scope="col">Instituição de Origem</th>
                        <th rowspan="2" class="specialty-image">
                          <img :src="specialtyEmblem" alt="Insígnia da especialidade" />
                        </th>
                      </tr>
                      <tr>
                        <td>{{ article.area }}</td>
                        <td>{{ article.code }}</td>
                        <td>{{ article.level }}</td>
                        <td>{{ article.year }}</td>
                        <td>{{ article.origin }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <section id="requisitos" class="article-section">
                  <div
                    class="section-heading d-flex align-items-center justify-content-between gap-2"
                  >
                    <h2>Requisitos</h2>
                    <button
                      class="edit-btn border-0 bg-transparent text-secondary"
                      aria-label="Editar requisitos"
                      @click="openEdit('requisitos')"
                    >
                      <i class="bi bi-pencil"></i>
                    </button>
                  </div>
                  <div class="article-copy">
                    <ol class="requirements">
                      <li v-for="(requirement, index) in requirements" :key="requirement">
                        {{ requirement }}
                        <ol v-if="index === 0" type="a">
                          <li v-for="disaster in events" :key="disaster">{{ disaster }}.</li>
                        </ol>
                      </li>
                    </ol>
                  </div>
                </section>
                <section id="materiais" class="article-section">
                  <div
                    class="section-heading d-flex align-items-center justify-content-between gap-2"
                  >
                    <h2>Materiais de estudo</h2>
                    <button
                      class="edit-btn border-0 bg-transparent text-secondary"
                      aria-label="Editar materiais"
                      @click="openEdit('materiais')"
                    >
                      <i class="bi bi-pencil"></i>
                    </button>
                  </div>
                  <ul class="article-copy study-links">
                    <li v-for="item in materials" :key="item.url">
                      <a :href="item.url" target="_blank" rel="noopener noreferrer">{{
                        item.title
                      }}</a>
                    </li>
                  </ul>
                </section>
                <section id="referencias" class="article-section">
                  <div
                    class="section-heading d-flex align-items-center justify-content-between gap-2"
                  >
                    <h2>Referências</h2>
                    <button
                      class="edit-btn border-0 bg-transparent text-secondary"
                      aria-label="Editar referências"
                      @click="openEdit('referencias')"
                    >
                      <i class="bi bi-pencil"></i>
                    </button>
                  </div>
                  <ol class="article-copy">
                    <li>Dados da especialidade conforme a referência enviada.</li>
                    <li>Sites das organizações relacionados em Materiais de estudo.</li>
                  </ol>
                </section>
                <section id="relacionadas" class="article-section">
                  <div
                    class="section-heading d-flex align-items-center justify-content-between gap-2"
                  >
                    <h2>Páginas relacionadas</h2>
                  </div>
                  <p class="article-copy text-secondary">
                    Adicione aqui os links de artigos relacionados à especialidade.
                  </p>
                </section>
                <div
                  id="categorias"
                  class="d-flex flex-wrap align-items-center gap-2 small article-section"
                >
                  <i class="bi bi-tags text-secondary"></i
                  ><span class="text-secondary">Categorias:</span
                  ><span
                    v-for="item in ['ADRA', 'Nível 1', 'Especialidades']"
                    :key="item"
                    class="badge text-bg-light border fw-normal"
                    >{{ item }}</span
                  >
                </div>
                <p class="paper-note">
                  Prévia de layout: os requisitos foram resumidos para demonstração. Consulte o
                  conteúdo oficial antes de aplicar a especialidade.
                </p>
              </BTab>
              <BTab
                ><template #title><i class="bi bi-chat-square-text me-2"></i>Discussão</template>
                <div class="pb-5">
                  <h2 class="mb-3">Discussão do artigo</h2>
                  <p class="article-copy">
                    Use este espaço para conversar sobre fontes, conteúdo e melhorias.
                  </p>
                  <div class="discussion-box article-copy border rounded-2 p-3 bg-light">
                    <strong>Área demonstrativa</strong>
                    <p class="mb-0 mt-2">
                      Conecte esta aba ao sistema da wiki para carregar e enviar comentários.
                    </p>
                  </div>
                  <BButton variant="primary" class="mt-3" @click="isHelpModalOpen = true"
                    >Como participar</BButton
                  >
                </div></BTab
              >
              <BTab
                ><template #title><i class="bi bi-clock-history me-2"></i>Histórico</template>
                <div class="pb-5">
                  <h2 class="mb-3">Histórico do artigo</h2>
                  <p class="article-copy">
                    As revisões aparecerão aqui após a integração com o sistema.
                  </p>
                  <div class="discussion-box article-copy border rounded-2 p-3 bg-light">
                    Não há revisões nesta demonstração.
                  </div>
                </div></BTab
              >
            </BTabs>
            <footer class="paper-footer d-flex justify-content-between">
              <span>MDAWiki · Conhecimento construído em comunidade</span><span>Artigo</span>
            </footer>
          </article>
        </div>
      </main>

      <aside
        class="right-sidebar position-sticky d-flex flex-column gap-2"
        aria-label="Colaboração e informações"
      >
        <section class="side-panel bg-white border rounded-2 p-3">
          <div class="panel-head d-flex align-items-center justify-content-between mb-3">
            <h2 class="panel-title">Colaboração</h2>
            <button
              class="plain-btn border-0 bg-transparent p-0 text-secondary"
              :aria-expanded="isCollaborationOpen"
              aria-controls="colaboracao"
              aria-label="Expandir ou recolher colaboração"
              @click="isCollaborationOpen = !isCollaborationOpen"
            >
              <i :class="isCollaborationOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
            </button>
          </div>
          <BCollapse id="colaboracao" v-model="isCollaborationOpen"
            ><div class="avatars d-flex gap-2 mb-3" aria-label="Avatares ilustrativos">
              <span
                class="avatar d-inline-flex align-items-center justify-content-center rounded-circle text-white bg-primary"
                >JS</span
              ><span
                class="avatar d-inline-flex align-items-center justify-content-center rounded-circle text-white bg-info"
                >AM</span
              ><span
                class="avatar avatar-purple d-inline-flex align-items-center justify-content-center rounded-circle text-white"
                >RC</span
              ><span
                class="avatar d-inline-flex align-items-center justify-content-center rounded-circle bg-light border text-body"
                >+3</span
              >
            </div>
            <p class="caption">Página colaborativa</p>
            <p class="muted mb-4">Compartilhe fontes e ajude a melhorar o conteúdo.</p>
            <BButton
              variant="outline-primary"
              class="w-100"
              style="font-size: 11px"
              @click="openEdit()"
              ><i class="bi bi-pencil me-2"></i>Sugerir alteração</BButton
            ></BCollapse
          >
        </section>
        <section class="side-panel bg-white border rounded-2 p-3">
          <div class="panel-head d-flex align-items-center justify-content-between mb-3">
            <h2 class="panel-title">Discussão</h2>
            <button
              class="plain-btn border-0 bg-transparent p-0 text-secondary"
              :aria-expanded="isDiscussionOpen"
              aria-controls="comentarios"
              aria-label="Expandir ou recolher discussão"
              @click="isDiscussionOpen = !isDiscussionOpen"
            >
              <i :class="isDiscussionOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
            </button>
          </div>
          <BCollapse id="comentarios" v-model="isDiscussionOpen"
            ><div class="comment d-flex gap-2 border rounded-2 p-2 mb-2 bg-white">
              <span
                class="avatar d-inline-flex align-items-center justify-content-center rounded-circle text-white bg-primary"
                >JS</span
              >
              <div>
                <strong>Exemplo de comentário</strong>
                <p>Seria interessante incluir um exemplo prático desta situação.</p>
                <span class="muted" style="font-size: 9px">Conteúdo ilustrativo</span>
              </div>
            </div>
            <div class="comment d-flex gap-2 border rounded-2 p-2 mb-2 bg-white">
              <span
                class="avatar d-inline-flex align-items-center justify-content-center rounded-circle text-white bg-info"
                >AM</span
              >
              <div>
                <strong>Exemplo de comentário</strong>
                <p>Também podemos adicionar uma referência para complementar o estudo.</p>
                <span class="muted" style="font-size: 9px">Conteúdo ilustrativo</span>
              </div>
            </div>
            <BButton variant="link" class="p-0 mt-2" style="font-size: 11px" @click="activeTab = 1"
              >Abrir discussão <i class="bi bi-arrow-right ms-1"></i></BButton
          ></BCollapse>
        </section>
        <section class="side-panel bg-white border rounded-2 p-3">
          <div class="panel-head d-flex align-items-center justify-content-between mb-3">
            <h2 class="panel-title">Informações</h2>
            <button
              class="plain-btn border-0 bg-transparent p-0 text-secondary"
              :aria-expanded="isInformationOpen"
              aria-controls="informacoes"
              aria-label="Expandir ou recolher informações"
              @click="isInformationOpen = !isInformationOpen"
            >
              <i :class="isInformationOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
            </button>
          </div>
          <BCollapse id="informacoes" v-model="isInformationOpen"
            ><dl class="info-list">
              <div>
                <i class="bi bi-tag"></i>
                <dt>Área:</dt>
                <dd>{{ article.area }}</dd>
              </div>
              <div>
                <i class="bi bi-bar-chart"></i>
                <dt>Nível:</dt>
                <dd>{{ article.level }}</dd>
              </div>
              <div>
                <i class="bi bi-file-earmark-text"></i>
                <dt>Origem:</dt>
                <dd>{{ article.origin }}</dd>
              </div>
            </dl>
            <hr />
            <BButton
              variant="light"
              class="border w-100"
              style="font-size: 11px"
              @click="printArticle"
              ><i class="bi bi-printer me-2"></i>Imprimir artigo</BButton
            ></BCollapse
          >
        </section>
      </aside>
    </div>
    <AppFooter @notice="showNotice" />

    <BOffcanvas
      id="indice-mobile"
      v-model="isTableOfContentsOpen"
      placement="start"
      title="Neste artigo"
      ><nav class="toc" aria-label="Índice para celular">
        <a
          v-for="section in sections"
          :key="section.id"
          :href="`#${section.id}`"
          @click.prevent="goTo(section.id)"
          >{{ section.title }}</a
        >
      </nav></BOffcanvas
    >
    <BModal v-model="isHelpModalOpen" title="Como colaborar" centered no-footer
      ><p>
        Compartilhe fontes confiáveis, converse na aba Discussão e sugira melhorias para os artigos.
      </p>
      <div class="alert alert-light border small">
        Login, publicação, comentários e histórico precisam ser integrados ao sistema da wiki.
      </div>
      <div class="text-end">
        <BButton variant="primary" @click="isHelpModalOpen = false">Entendi</BButton>
      </div></BModal
    >
    <BModal v-model="isHistoryModalOpen" title="Mudanças recentes" centered no-footer
      >Após a integração, liste aqui as revisões dos artigos, com data, autor e resumo das
      alterações.</BModal
    >
    <BModal
      v-model="isEditModalOpen"
      :title="`Sugerir alteração — ${editSection}`"
      size="lg"
      centered
      no-footer
      ><p class="small text-secondary">
        Editor demonstrativo: baixe a sugestão em texto. Ela não será publicada.
      </p>
      <label class="form-label" for="editorSugestao">Conteúdo da sugestão</label
      ><BFormTextarea id="editorSugestao" v-model="editText" :rows="12" />
      <div class="d-flex justify-content-end gap-2 mt-3">
        <BButton variant="light" @click="isEditModalOpen = false">Cancelar</BButton
        ><BButton variant="primary" @click="downloadSuggestion"
          ><i class="bi bi-download me-2"></i>Baixar sugestão</BButton
        >
      </div></BModal
    >
    <div v-if="notice" class="toast-container position-fixed bottom-0 end-0 p-3">
      <div class="toast show" role="status">
        <div class="d-flex">
          <div class="toast-body">{{ notice }}</div>
          <button
            class="btn-close me-2 m-auto"
            aria-label="Fechar aviso"
            @click="notice = ''"
          ></button>
        </div>
      </div>
    </div>
  </BApp>
</template>

<style lang="scss">
// Tema global do Bootstrap. Para manter tudo em um arquivo nesta etapa, o
// Bootstrap é compilado aqui. Depois, mova estas linhas para um arquivo .scss global.
$primary: #07523b;
$secondary: #557b64;
$body-color: #303940;
$body-bg: #edf1f4;
$font-family-sans-serif: 'Open Sans', Helvetica, Arial, sans-serif;
$link-color: $primary;
$link-hover-color: #032d20;

@import 'bootstrap/scss/bootstrap';

@font-face {
  font-family: 'Open Sans';
  src: url('./assets/fonts/OpenSans-Light.ttf') format('truetype');
  font-style: normal;
  font-weight: 300;
  font-display: swap;
}
@font-face {
  font-family: 'Open Sans';
  src: url('./assets/fonts/OpenSans-Regular.ttf') format('truetype');
  font-style: normal;
  font-weight: 400;
  font-display: swap;
}
@font-face {
  font-family: 'Open Sans';
  src: url('./assets/fonts/OpenSans-SemiBold.ttf') format('truetype');
  font-style: normal;
  font-weight: 600;
  font-display: swap;
}
@font-face {
  font-family: 'Open Sans';
  src: url('./assets/fonts/OpenSans-Bold.ttf') format('truetype');
  font-style: normal;
  font-weight: 700;
  font-display: swap;
}

// Estilos próprios: apenas detalhes do layout que as utilidades não cobrem.
:root {
  --header-height: 122px;
  --wiki-line: #dfe5e9;
  --wiki-dark: #063e2e;
  --wiki-paper-width: 840px;
}

.workspace {
  display: grid;
  grid-template-columns: 250px minmax(0, 1fr) 264px;
  gap: 30px;
  max-width: 1800px;
  min-height: calc(100vh - 176px);
  margin: 0 auto;
  padding: 24px 22px 38px;
  align-items: start;
}
.left-sidebar,
.right-sidebar {
  top: calc(var(--header-height) + 22px);
}
.side-panel {
  border-color: #e1e7ec !important;
  hr {
    border-color: #c9d3d9;
    margin: 1.6rem 0;
  }
}
.panel-title {
  font-size: 0.875rem;
  font-weight: 650;
  color: var(--wiki-dark);
  margin: 0;
}
.toc {
  border-left: 1px solid #d8e0dc;
  a {
    display: block;
    position: relative;
    padding: 0.625rem 0.75rem 0.625rem 1.625rem;
    color: #5b6660;
    font-size: 0.75rem;
    text-decoration: none;
    &.active {
      color: var(--bs-primary);
      background: var(--bs-primary-bg-subtle);
      font-weight: 600;
      &::before {
        content: '';
        position: absolute;
        left: -2px;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--bs-primary);
        border-radius: 3px;
      }
    }
  }
}
.article-container {
  max-width: var(--wiki-paper-width);
}
.paper {
  width: 100%;
  min-height: 1050px;
  padding: 0 52px 24px;
  h1,
  h2 {
    font-family: inherit;
    color: #777;
  }
  h1 {
    font-size: clamp(1.75rem, 2.5vw, 2rem);
    line-height: 1.2;
    font-weight: 300;
    margin: 0 0 1rem;
  }
  h2 {
    font-size: 1.5rem;
    font-weight: 400;
    margin: 0;
  }
}
.toolbar-edit {
  z-index: 2;
  height: 0;
  margin-right: -18px;
  .edit-btn {
    margin-top: 9px;
    text-decoration: none;
  }
}
.article-tabs-container {
  position: relative;
  > .nav {
    margin: 0 -30px 30px;
    padding-right: 80px;
    gap: 20px;
    border-bottom: 1px solid var(--wiki-line);
  }
  .nav-link {
    padding: 15px 3px;
    font-size: 0.75rem;
    white-space: nowrap;
    color: #66716a;
    background: none;
    border: 0;
    border-bottom: 2px solid transparent;
    border-radius: 0;
    &.active {
      color: var(--bs-primary);
      background: none;
      border-bottom-color: var(--bs-primary);
      font-weight: 600;
    }
  }
}
.edit-btn {
  font-size: 0.75rem;
  white-space: nowrap;
}
.eyebrow {
  margin: 0 0 0.875rem;
  color: var(--bs-primary);
  font-size: 0.625rem;
  letter-spacing: 0.4px;
  text-transform: uppercase;
  a {
    color: inherit;
    text-decoration: none;
  }
}
.article-intro,
.article-support {
  color: #777;
  font-size: 13px;
  line-height: 1.5;
}
.article-support a,
.article-copy a {
  color: #4a89dc;
  overflow-wrap: anywhere;
}
.article-divider {
  margin: 1.25rem 0;
  border-color: #ccc;
}
.specialty-wrapper {
  overflow-x: auto;
  margin-bottom: 1.5rem;
}
.specialty {
  width: 100%;
  min-width: 590px;
  border-collapse: collapse;
  color: #fff;
  font-size: 13px;
  text-align: center;
  th, td {
    border: 1px solid rgba(255, 255, 255, 0.35);
    padding: 0.75rem 0.45rem;
    vertical-align: middle;
  }
  th {
    background: #0e0d34;
    font-weight: 700;
  }
  td, .specialty-image {
    background: #55577f;
  }
  .specialty-image {
    width: 100px;
    padding: 4px;
  }
  img {
    display: block;
    width: 90px;
    height: 90px;
    object-fit: contain;
    margin: auto;
  }
}
.article-section {
  scroll-margin-top: calc(var(--header-height) + 25px);
  margin-bottom: 2.25rem;
}
.section-heading {
  border-bottom: 1px solid #ddd;
  margin-bottom: 1rem;
  padding-bottom: 0.4375rem;
}
.article-copy {
  font-size: 13px;
  line-height: 1.5;
  color: #666;
}
.requirements {
  padding-left: 19px;
  > li {
    padding-left: 9px;
    margin-bottom: 9px;
  }
  ol {
    padding-left: 27px;
    margin-top: 4px;
    li {
      padding-left: 2px;
    }
  }
}
.study-links {
  padding-left: 20px;
  line-height: 1.95;
}
.paper-note {
  border-left: 3px solid #a9bca9;
  padding: 9px 12px;
  background: #f6f8f4;
  font-size: 0.625rem;
  color: #657264;
  margin: 23px 0;
}
.paper-footer {
  border-top: 1px solid var(--wiki-line);
  padding-top: 13px;
  margin-top: 30px;
  font-size: 0.625rem;
  color: #69766d;
}
.avatar {
  width: 33px;
  height: 33px;
  flex: none;
  font-size: 0.625rem;
}
.avatar-purple {
  background: #836594;
}
.caption {
  font-size: 0.75rem;
  font-weight: 600;
  margin: 0 0 4px;
}
.muted {
  color: #66766b;
  font-size: 0.6875rem;
  line-height: 1.7;
}
.comment {
  .avatar {
    width: 28px;
    height: 28px;
    font-size: 0.5625rem;
  }
  strong {
    font-size: 0.625rem;
    display: block;
    margin-bottom: 4px;
  }
  p {
    font-size: 0.625rem;
    line-height: 1.65;
    color: #55635a;
    margin: 0;
  }
}
.info-list {
  display: grid;
  gap: 17px;
  margin: 0;
  > div {
    display: flex;
    gap: 15px;
    align-items: center;
    font-size: 0.6875rem;
  }
  i {
    font-size: 1.0625rem;
  }
  dt {
    font-weight: 400;
    color: #66766b;
  }
  dd {
    margin: 0 0 0 auto;
    text-align: right;
    font-size: 0.625rem;
  }
}
@media (max-width: 1399.98px) {
  .workspace {
    grid-template-columns: 210px minmax(0, 1fr) 235px;
    gap: 22px;
    padding-left: 18px;
    padding-right: 18px;
  }
  .paper {
    padding-left: 32px;
    padding-right: 32px;
  }
  .article-tabs-container > .nav {
    margin-left: -15px;
    margin-right: -15px;
  }
  .toolbar-edit {
    margin-right: -8px;
  }
}
@media (max-width: 1199.98px) {
  .workspace {
    grid-template-columns: minmax(0, 1fr) 240px;
    max-width: 1160px;
  }
}
@media (max-width: 991.98px) {
  .workspace {
    display: block;
    max-width: 860px;
    padding: 20px;
  }
  .right-sidebar {
    position: static !important;
    display: grid !important;
    grid-template-columns: 1fr 1fr;
    margin: 22px auto 0;
    max-width: 800px;
    > .side-panel:last-child {
      grid-column: 1 / -1;
    }
  }
}
@media (max-width: 575.98px) {
  .workspace {
    padding: 14px 10px 25px;
  }
  .paper {
    padding: 0 21px 20px;
    min-height: auto;
    h1 {
      font-size: 1.875rem;
    }
    h2 {
      font-size: 1.5625rem;
    }
  }
  .article-tabs-container > .nav {
    margin: 0 -8px 25px;
    gap: 13px;
    padding-right: 50px;
  }
  .article-tabs-container .nav-link {
    font-size: 0.625rem;
    padding: 14px 0;
  }
  .toolbar-edit {
    margin-right: -6px;
  }
  .right-sidebar {
    grid-template-columns: 1fr;
    > .side-panel:last-child {
      grid-column: auto;
    }
  }
}
@media (prefers-reduced-motion: no-preference) {
  html {
    scroll-behavior: smooth;
  }
}
@media print {
  .left-sidebar,
  .right-sidebar,
  .mobile-tools,
  .toolbar-edit,
  .article-tabs-container > .nav,
  .edit-btn,
  .paper-note,
  .modal,
  .toast-container {
    display: none !important;
  }
  body,
  .workspace {
    background: white;
    padding: 0;
    display: block;
  }
  .paper {
    border: 0 !important;
    box-shadow: none !important;
    max-width: none;
    min-height: 0;
    padding: 0;
  }
  .article-section {
    break-inside: avoid;
  }
  .article-copy {
    font-size: 11pt;
  }
  .paper h1 {
    font-size: 26pt;
  }
  .specialty {
    print-color-adjust: exact;
  }
}
</style>
