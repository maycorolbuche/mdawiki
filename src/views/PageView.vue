<script setup>
import { computed, nextTick, onUnmounted, reactive, ref, watch } from "vue";
import {
  onBeforeRouteLeave,
  onBeforeRouteUpdate,
  useRoute,
  useRouter,
} from "vue-router";
import { BButton, BCollapse, BModal, BTab, BTabs } from "bootstrap-vue-next";
import ArticleRichEditor from "../components/ArticleRichEditor.vue";
import PageMetadataFields from "../components/PageMetadataFields.vue";
import pathfinderEmblem from "../assets/images/pathfinder-emblem.svg";
import { pagesApi } from "../services/pages.js";
import { sanitizeArticleHtml } from "../utils/articleHtml.js";
import {
  articleSlug,
  metadataPayload,
  slugify,
  titleFromPath,
  titlePathSegment,
  articlePath,
  sameArticlePath,
  validatePageDraft,
} from "../utils/page.js";
import { unsupportedEditorTags } from "../utils/editorExtensions.js";

const route = useRoute();
const router = useRouter();
const article = ref(null);
const schema = ref(null);
const state = ref("loading");
const loadError = ref("");
const activeTab = ref(0);
const isEditModalOpen = ref(false);
const isHelpModalOpen = ref(false);
const isHistoryModalOpen = ref(false);
const isInformationOpen = ref(true);
const isCollaborationOpen = ref(true);
const isDiscussionOpen = ref(true);
const saving = ref(false);
const preparingEditor = ref(false);
const editError = ref("");
const draft = reactive({
  title: "",
  content: "",
  type: "",
  categories: "",
  metadata: {},
});
const draftVersion = ref("");
const metadataByType = new Map();
const notice = ref("");
const bodyElement = ref(null);
let noticeTimer;
let loadController;
let saveController;
let generation = 0;
let disposed = false;
const requestedSlug = computed(() => articleSlug(route.path));
const typeDefinition = computed(() =>
  schema.value?.types.find((type) => type.value === article.value?.type),
);
const draftFields = computed(
  () =>
    schema.value?.types.find((type) => type.value === draft.type)?.fields ?? [],
);
const contentHtml = computed(() =>
  sanitizeArticleHtml(article.value?.content, schema.value?.html),
);
const generatedSlug = computed(() => titlePathSegment(draft.title));
const isCreating = computed(() => !article.value);

function showNotice(message) {
  notice.value = message;
  clearTimeout(noticeTimer);
  noticeTimer = setTimeout(() => {
    notice.value = "";
  }, 6000);
}
async function loadPage() {
  const current = ++generation;
  loadController?.abort();
  loadController = new AbortController();
  article.value = null;
  state.value = "loading";
  loadError.value = "";
  activeTab.value = 0;
  isEditModalOpen.value = false;
  const slug = requestedSlug.value;
  if (!slug) {
    state.value = "reserved";
    return;
  }
  const typedPath = articlePath(titleFromPath(route.path));
  if (!sameArticlePath(route.path, typedPath)) {
    await router.replace({
      path: typedPath,
      query: route.query,
      hash: route.hash,
    });
    return;
  }
  const [rules, page] = await Promise.allSettled([
    pagesApi.schema(),
    pagesApi.get(slug, loadController.signal),
  ]);
  if (disposed || current !== generation) return;
  if (rules.status === "fulfilled") schema.value = rules.value;
  if (rules.status === "rejected") {
    state.value = "error";
    loadError.value = rules.reason.message;
    return;
  }
  if (page.status === "rejected") {
    state.value = page.reason.status === 404 ? "missing" : "error";
    loadError.value = page.reason.message;
    return;
  }
  if (!sameArticlePath(route.path, articlePath(page.value.title))) {
    await router.replace({
      path: articlePath(page.value.title),
      query: route.query,
      hash: route.hash,
    });
    return;
  }
  article.value = page.value;
  state.value = "ready";
}
watch(() => route.path, loadPage, { immediate: true });
function switchType(event) {
  metadataByType.set(draft.type, { ...draft.metadata });
  draft.type = event.target.value;
  draft.metadata = { ...(metadataByType.get(draft.type) ?? {}) };
}
async function openEdit() {
  if (
    preparingEditor.value ||
    saving.value ||
    !["ready", "missing"].includes(state.value)
  )
    return;
  preparingEditor.value = true;
  const current = generation;
  try {
    const rules = await pagesApi.schema({ revalidate: true });
    if (disposed || generation !== current) return;
    schema.value = rules;
    const unsupported = unsupportedEditorTags(rules.html);
    if (unsupported.length)
      throw new Error(
        `O editor precisa ser atualizado para estas tags: ${unsupported.join(", ")}.`,
      );
    const type = article.value?.type ?? rules.page.defaultType;
    const fields = rules.types.find((item) => item.value === type)?.fields;
    if (!fields)
      throw new Error(
        "O tipo desta página não está configurado para edição na API.",
      );
    const unknown = Object.keys(article.value?.metadata ?? {}).filter(
      (key) => !fields.some((field) => field.key === key),
    );
    if (unknown.length)
      throw new Error(
        `A API precisa configurar estes metadados antes da edição: ${unknown.join(", ")}.`,
      );
    metadataByType.clear();
    Object.assign(draft, {
      title: article.value?.title ?? titleFromPath(route.path),
      type,
      content: article.value?.content ?? "",
      categories: (article.value?.categories ?? [])
        .map((item) => item.name)
        .join(", "),
      metadata: { ...(article.value?.metadata ?? {}) },
    });
    draftVersion.value = rules.version;
    editError.value = "";
    isEditModalOpen.value = true;
  } catch (error) {
    if (!disposed) showNotice(error.message);
  } finally {
    preparingEditor.value = false;
  }
}
async function saveArticle() {
  if (saving.value) return;
  editError.value = validatePageDraft(draft, schema.value);
  if (editError.value) return;
  saving.value = true;
  saveController = new AbortController();
  const current = generation;
  try {
    const rules = await pagesApi.schema({ revalidate: true });
    if (disposed || generation !== current) return;
    if (rules.version !== draftVersion.value)
      throw new Error(
        "As diretrizes foram alteradas na API. Feche e reabra o editor para revisar as novas regras. Seu rascunho continua aberto.",
      );
    const categories = [
      ...new Set(
        draft.categories
          .split(",")
          .map((value) => value.trim())
          .filter(Boolean),
      ),
    ];
    if (categories.length > rules.page.maxCategories)
      throw new Error(`Use no máximo ${rules.page.maxCategories} categorias.`);
    const data = await pagesApi.save(
      article.value?.slug,
      {
        title: draft.title.trim(),
        type: draft.type,
        content: sanitizeArticleHtml(draft.content, rules.html),
        categories,
        metadata: metadataPayload(draftFields.value, draft.metadata),
      },
      saveController.signal,
    );
    if (disposed || generation !== current) return;
    article.value = data;
    state.value = "ready";
    isEditModalOpen.value = false;
    saving.value = false;
    if (!sameArticlePath(route.path, articlePath(data.title)))
      await router.replace({
        path: articlePath(data.title),
        query: route.query,
        hash: route.hash,
      });
    showNotice("Página salva com sucesso.");
  } catch (error) {
    if (!disposed && generation === current) editError.value = error.message;
  } finally {
    saving.value = false;
  }
}
function preventEditorClose(event) {
  if (saving.value) event.preventDefault();
}
async function goTo(id) {
  activeTab.value = 0;
  await nextTick();
  const target =
    document.getElementById(id) ??
    [...(bodyElement.value?.querySelectorAll("h2,h3,h4,h5,h6") ?? [])].find(
      (element) => slugify(element.textContent) === id,
    );
  target?.scrollIntoView({ behavior: "smooth", block: "start" });
}
async function printArticle() {
  activeTab.value = 0;
  await nextTick();
  window.print();
}
// Evita desmontar um formulário enquanto uma gravação está em andamento.
onBeforeRouteLeave(() => !saving.value);
onBeforeRouteUpdate(() => !saving.value);
onUnmounted(() => {
  disposed = true;
  ++generation;
  loadController?.abort();
  saveController?.abort();
  clearTimeout(noticeTimer);
});
defineExpose({
  goTo,
  showNotice,
  openDiscussion: () => {
    activeTab.value = 1;
  },
  openHistory: () => {
    isHistoryModalOpen.value = true;
  },
  openHelp: () => {
    isHelpModalOpen.value = true;
  },
});
</script>

<template>
  <main
    id="artigo"
    class="page-wrap d-flex justify-content-center"
    tabindex="-1"
    :aria-busy="state === 'loading'"
  >
    <div class="w-100 article-container">
      <article
        v-if="state !== 'ready'"
        class="paper bg-white border rounded-1 shadow-sm p-4 page-state"
      >
        <p v-if="state === 'loading'" role="status">
          <span class="spinner-border spinner-border-sm me-2"></span>Carregando
          página…
        </p>
        <template v-else-if="state === 'error'">
          <h1>Não foi possível carregar a página</h1>
          <p role="alert">{{ loadError }}</p>
          <BButton variant="primary" @click="loadPage"
            >Tentar novamente</BButton
          >
        </template>
        <template v-else-if="state === 'missing'">
          <h1>{{ titleFromPath(route.path) }}</h1>
          <p>Ainda não existe um artigo neste endereço.</p>
          <BButton
            variant="primary"
            :disabled="preparingEditor"
            @click="openEdit"
            >{{
              preparingEditor ? "Preparando editor…" : "Criar página"
            }}</BButton
          >
        </template>
        <template v-else
          ><h1>Endereço não disponível</h1>
          <p>
            Este endereço pertence às rotas do sistema ou não é um endereço
            válido de artigo.
          </p></template
        >
        <RouterLink v-if="state !== 'loading'" to="/" class="d-block mt-3"
          >Voltar ao início</RouterLink
        >
      </article>
      <template v-else>
        <div
          class="mobile-tools d-flex d-xl-none justify-content-end gap-2 mb-3"
        >
          <BButton variant="light" class="border" @click="printArticle"
            ><i class="bi bi-printer me-2"></i>Imprimir</BButton
          >
        </div>
        <article class="paper bg-white border rounded-1 shadow-sm">
          <div class="toolbar-edit position-relative text-end">
            <BButton
              variant="link"
              class="edit-btn border-0 bg-transparent text-secondary"
              :disabled="preparingEditor"
              @click="openEdit"
              ><i class="bi bi-pencil me-1"></i>Editar</BButton
            >
          </div>
          <BTabs
            v-model="activeTab"
            class="article-tabs-container"
            nav-class="article-tabs"
            content-class="article-content"
          >
            <BTab>
              <template #title
                ><i class="bi bi-file-earmark-text me-2"></i>Artigo</template
              >
              <p class="eyebrow">
                {{ typeDefinition?.label ?? article.type
                }}<template v-if="article.metadata?.area">
                  / <strong>{{ article.metadata.area }}</strong></template
                >
              </p>
              <h1>{{ article.title }}</h1>
              <p v-if="article.summary" class="article-intro">
                {{ article.summary }}
              </p>
              <hr class="article-divider" />
              <div
                v-if="article.type === 'specialty'"
                class="specialty-wrapper"
                role="region"
                aria-label="Dados da especialidade"
                tabindex="0"
              >
                <table class="specialty">
                  <tbody>
                    <tr>
                      <th
                        v-if="article.metadata.club_type === 'pathfinders'"
                        rowspan="2"
                        class="specialty-image"
                      >
                        <img
                          :src="pathfinderEmblem"
                          alt="Emblema dos Desbravadores"
                        />
                      </th>
                      <th scope="col">Área</th>
                      <th scope="col">Código</th>
                      <th scope="col">Nível</th>
                      <th scope="col">Ano</th>
                      <th scope="col">Instituição de Origem</th>
                    </tr>
                    <tr>
                      <td>{{ article.metadata.area ?? "—" }}</td>
                      <td>{{ article.metadata.code ?? "—" }}</td>
                      <td>{{ article.metadata.level ?? "—" }}</td>
                      <td>{{ article.metadata.introduction_year ?? "—" }}</td>
                      <td>{{ article.metadata.origin ?? "—" }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Conteúdo filtrado pela política retornada pela API. -->
              <div
                ref="bodyElement"
                class="article-copy article-body article-section"
                v-html="contentHtml"
              ></div>
              <div
                id="categorias"
                class="d-flex flex-wrap align-items-center gap-2 small article-section"
              >
                <i class="bi bi-tags text-secondary"></i
                ><span class="text-secondary">Categorias:</span
                ><span
                  v-for="category in article.categories"
                  :key="category.id"
                  class="badge text-bg-light border fw-normal"
                  >{{ category.name }}</span
                ><span v-if="!article.categories.length" class="text-secondary"
                  >Nenhuma</span
                >
              </div>
            </BTab>
            <BTab
              ><template #title
                ><i class="bi bi-chat-square-text me-2"></i>Discussão</template
              >
              <h2 class="mb-3">Discussão do artigo</h2>
              <p class="article-copy">
                Comentários ainda não integrados à API.
              </p></BTab
            >
            <BTab
              ><template #title
                ><i class="bi bi-clock-history me-2"></i>Histórico</template
              >
              <h2 class="mb-3">Histórico do artigo</h2>
              <p class="article-copy">
                A consulta ao histórico ainda não está integrada à API.
              </p></BTab
            >
          </BTabs>
          <footer class="paper-footer d-flex justify-content-between">
            <span>MDAWiki · Conhecimento construído em comunidade</span
            ><span>{{ typeDefinition?.label ?? article.type }}</span>
          </footer>
        </article>
      </template>
    </div>
  </main>
  <aside
    v-if="article && state === 'ready'"
    class="right-sidebar d-flex flex-column gap-2"
    aria-label="Colaboração e informações"
  >
    <section class="side-panel bg-white border rounded-2 p-3">
      <div
        class="panel-head d-flex align-items-center justify-content-between mb-3"
      >
        <h2 class="panel-title">Colaboração</h2>
        <button
          type="button"
          class="border-0 bg-transparent text-secondary"
          :aria-expanded="isCollaborationOpen"
          aria-controls="colaboracao"
          aria-label="Expandir ou recolher colaboração"
          @click="isCollaborationOpen = !isCollaborationOpen"
        >
          <i
            :class="
              isCollaborationOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'
            "
          ></i>
        </button>
      </div>
      <BCollapse id="colaboracao" v-model="isCollaborationOpen"
        ><p class="caption">Página colaborativa</p>
        <p class="muted">Compartilhe fontes e ajude a melhorar o conteúdo.</p>
        <BButton
          variant="outline-primary"
          class="w-100"
          :disabled="preparingEditor"
          @click="openEdit"
          >Editar página</BButton
        ></BCollapse
      >
    </section>
    <section class="side-panel bg-white border rounded-2 p-3">
      <div
        class="panel-head d-flex align-items-center justify-content-between mb-3"
      >
        <h2 class="panel-title">Discussão</h2>
        <button
          type="button"
          class="border-0 bg-transparent text-secondary"
          :aria-expanded="isDiscussionOpen"
          aria-controls="comentarios"
          aria-label="Expandir ou recolher discussão"
          @click="isDiscussionOpen = !isDiscussionOpen"
        >
          <i
            :class="
              isDiscussionOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'
            "
          ></i>
        </button>
      </div>
      <BCollapse id="comentarios" v-model="isDiscussionOpen"
        ><p class="muted">Comentários ainda não integrados à API.</p>
        <BButton variant="link" class="p-0" @click="activeTab = 1"
          >Abrir discussão</BButton
        ></BCollapse
      >
    </section>
    <section class="side-panel bg-white border rounded-2 p-3">
      <div
        class="panel-head d-flex align-items-center justify-content-between mb-3"
      >
        <h2 class="panel-title">Informações</h2>
        <button
          type="button"
          class="border-0 bg-transparent text-secondary"
          :aria-expanded="isInformationOpen"
          aria-controls="informacoes"
          aria-label="Expandir ou recolher informações"
          @click="isInformationOpen = !isInformationOpen"
        >
          <i
            :class="
              isInformationOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'
            "
          ></i>
        </button>
      </div>
      <BCollapse id="informacoes" v-model="isInformationOpen"
        ><dl class="info-list">
          <div>
            <dt>Tipo:</dt>
            <dd>{{ typeDefinition?.label ?? article.type }}</dd>
          </div>
          <template
            v-for="field in typeDefinition?.fields ?? []"
            :key="field.key"
            ><div
              v-if="
                article.metadata[field.key] !== undefined &&
                article.metadata[field.key] !== null
              "
            >
              <dt>{{ field.label }}:</dt>
              <dd>
                {{
                  field.input === "boolean"
                    ? article.metadata[field.key]
                      ? "Sim"
                      : "Não"
                    : Array.isArray(article.metadata[field.key])
                      ? article.metadata[field.key].join(", ")
                      : article.metadata[field.key]
                }}
              </dd>
            </div></template
          >
        </dl>
        <hr />
        <BButton variant="light" class="border w-100" @click="printArticle"
          >Imprimir artigo</BButton
        ></BCollapse
      >
    </section>
  </aside>
  <BModal
    v-model="isEditModalOpen"
    :title="isCreating ? 'Criar página' : 'Editar página'"
    size="xl"
    centered
    no-footer
    :no-close-on-backdrop="saving"
    :no-close-on-esc="saving"
    :no-header-close="saving"
    @hide="preventEditorClose"
  >
    <form v-if="schema" @submit.prevent="saveArticle">
      <div v-if="editError" class="alert alert-danger" role="alert">
        {{ editError }}
      </div>
      <fieldset :disabled="saving">
        <label for="article-title" class="form-label">Título</label
        ><input
          id="article-title"
          v-model="draft.title"
          class="form-control"
          required
          :minlength="schema.page.titleMinLength"
          :maxlength="schema.page.titleMaxLength"
        />
        <p class="form-text">
          Endereço gerado: /{{ generatedSlug
          }}<template v-if="article && slugify(draft.title) !== article.slug"
            >. O endereço anterior redirecionará para o novo.</template
          >
        </p>
        <label for="article-type" class="form-label">Tipo</label
        ><select
          id="article-type"
          :value="draft.type"
          class="form-select mb-3"
          required
          @change="switchType"
        >
          <option
            v-for="type in schema.types"
            :key="type.value"
            :value="type.value"
          >
            {{ type.label }}
          </option>
        </select>
        <p class="form-text">
          Ao trocar o tipo, serão enviados somente os campos do tipo
          selecionado.
        </p>
        <PageMetadataFields v-model="draft.metadata" :fields="draftFields" />
        <label for="article-categories" class="form-label">Categorias</label
        ><input
          id="article-categories"
          v-model="draft.categories"
          class="form-control"
          aria-describedby="categories-help"
        />
        <p id="categories-help" class="form-text">
          Separe as categorias por vírgulas.
        </p>
        <p class="form-label">Conteúdo</p>
        <ArticleRichEditor
          v-if="isEditModalOpen"
          :key="draftVersion"
          v-model="draft.content"
          :policy="schema.html"
          :read-only="saving"
        />
        <p class="form-text">
          As formatações disponíveis seguem as diretrizes da API. O título
          principal é reservado à página.
        </p>
        <div class="d-flex justify-content-end gap-2 mt-3">
          <BButton variant="light" @click="isEditModalOpen = false"
            >Cancelar</BButton
          ><BButton type="submit" variant="primary">{{
            saving ? "Salvando…" : "Salvar página"
          }}</BButton>
        </div>
      </fieldset>
    </form>
  </BModal>
  <BModal v-model="isHelpModalOpen" title="Como colaborar" centered no-footer
    ><p>
      Use Editar para alterar título, conteúdo, categorias e as informações do
      tipo. Ao salvar, as alterações são gravadas na API.
    </p>
    <p>
      Comentários e consulta ao histórico serão integrados em outra etapa.
    </p></BModal
  >
  <BModal
    v-model="isHistoryModalOpen"
    title="Mudanças recentes"
    centered
    no-footer
    >A consulta às revisões ainda não está integrada à API.</BModal
  >
  <div v-if="notice" class="toast-container position-fixed bottom-0 end-0 p-3">
    <div class="toast show" role="status">
      <div class="d-flex">
        <div class="toast-body">{{ notice }}</div>
        <button
          type="button"
          class="btn-close me-2 m-auto"
          aria-label="Fechar aviso"
          @click="notice = ''"
        ></button>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
// Sem scoped: os estilos também alcançam elementos gerados pelo Bootstrap Vue e seus modais.
.page-state {
  min-height: 220px;
}
.article-body {
  h2,
  h3,
  h4,
  h5,
  h6 {
    margin: 1.5rem 0 0.8rem;
    scroll-margin-top: calc(var(--header-height) + 20px);
  }
  img {
    max-width: 100%;
    height: auto;
  }
  table {
    display: block;
    max-width: 100%;
    overflow-x: auto;
    border-collapse: collapse;
    th,
    td {
      border: 1px solid #ddd;
      padding: 0.5rem;
    }
  }
  blockquote {
    border-left: 3px solid #a9bca9;
    padding-left: 1rem;
  }
  pre {
    white-space: pre-wrap;
  }
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

  .nav {
    margin: 6px -30px 10px -30px;
    padding-right: 80px;
    gap: 10px;
    border-bottom: 1px solid var(--wiki-line);

    .nav-item button {
      padding: 6px 16px;
    }
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
  th,
  td {
    border: 1px solid rgba(255, 255, 255, 0.35);
    padding: 0.75rem 0.45rem;
    vertical-align: middle;
  }
  th {
    background: #0e0d34;
    font-weight: 700;
  }
  td,
  .specialty-image {
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
@media (max-width: 991.98px) {
  .right-sidebar {
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
@media print {
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
