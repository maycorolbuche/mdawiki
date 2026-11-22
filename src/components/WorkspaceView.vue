<script setup lang="ts">
import {
  computed,
  defineAsyncComponent,
  inject,
  onBeforeUnmount,
  onMounted,
  ref,
  watch,
} from "vue";
import { useRoute, useRouter, onBeforeRouteLeave } from "vue-router";
import { diffLines } from "diff";
const LegacyWikiEditor = defineAsyncComponent(
  () => import("./LegacyWikiEditor.vue"),
);
import { RequestError, type SessionClient } from "../services/session";
import AuthorAvatar from "./AuthorAvatar.vue";
import RevisionComparison from "./RevisionComparison.vue";
import RevisionMetadata from "./RevisionMetadata.vue";
import WorkspaceModal from "./WorkspaceModal.vue";
import { cleanArticle } from "../services/content";
const route = useRoute(),
  router = useRouter(),
  api = inject<SessionClient>("session")!;
const slug = computed(() => String(route.params.slug)),
  historyMode = computed(() => route.meta.action === "history");
const title = ref(""),
  source = ref(""),
  original = ref(""),
  version = ref(""),
  media = ref(false),
  busy = ref(false),
  loading = ref(true),
  error = ref(""),
  loginRequired = ref(false),
  revisions = ref<any[]>([]),
  before = ref<number | null>(null),
  older = ref<number | null>(null),
  newer = ref<number | null>(null),
  compare = ref(false),
  selected = ref<any>(null);
const originalTitle = ref(""),
  format = ref("wiki"),
  saveNotice = ref(""),
  pageExists = ref(false);
const modal = ref<"preview" | "revision" | "compare" | "delete" | null>(null);
const previewHtml = ref(""),
  previewError = ref(""),
  previewBusy = ref(false);
let previewGeneration = 0;
function closeModal() {
  modal.value = null;
  compare.value = false;
  selected.value = null;
  previewGeneration++;
  previewBusy.value = false;
}
async function preview() {
  closeModal();
  modal.value = "preview";
  previewHtml.value = "";
  previewError.value = "";
  if (media.value) return;
  const generation = ++previewGeneration;
  previewBusy.value = true;
  try {
    const result = await api.request(endpoint.value + "/preview", "POST", {
      source: source.value,
    });
    if (generation !== previewGeneration) return;
    if (typeof result?.html !== "string")
      throw new Error("A API retornou uma prévia inválida.");
    previewHtml.value = cleanArticle(result.html);
  } catch (e) {
    if (generation === previewGeneration)
      previewError.value = (e as Error).message;
  } finally {
    if (generation === previewGeneration) previewBusy.value = false;
  }
}
function showRevision(revision: any) {
  closeModal();
  selected.value = revision;
  modal.value = "revision";
}
function showComparison() {
  closeModal();
  compare.value = true;
  modal.value = "compare";
}
const imageInput = ref<HTMLInputElement>();
const mediaPreview = computed(() =>
  /^(?:https?:\/\/|data:image\/(?:png|jpeg|gif|webp|svg\+xml);base64,)/i.test(
    source.value,
  )
    ? source.value
    : "",
);
const dirty = computed(
  () => source.value !== original.value || title.value !== originalTitle.value,
);
const loaded = ref(false);
const changes = computed(() => {
  const a =
      revisions.value.find((r) => Number(r.id) === Number(older.value))
        ?.source || "",
    b =
      revisions.value.find((r) => Number(r.id) === Number(newer.value))
        ?.source || "";
  return compare.value ? diffLines(a, b, { timeout: 1000 }) || [] : [];
});
const olderRevision = computed(() =>
  revisions.value.find((r) => Number(r.id) === Number(older.value)),
);
const newerRevision = computed(() =>
  revisions.value.find((r) => Number(r.id) === Number(newer.value)),
);
const endpoint = computed(() => "/pages/" + encodeURIComponent(slug.value));
async function load() {
  closeModal();
  saveNotice.value = "";
  loading.value = true;
  loginRequired.value = false;
  error.value = "";
  compare.value = false;
  revisions.value = [];
  selected.value = null;
  loaded.value = false;
  document.title = `${historyMode.value ? "Histórico" : "Editar"} — ${slug.value.replaceAll("_", " ")} — MDAWiki`;
  document
    .querySelector('meta[name="robots"]')
    ?.setAttribute("content", "noindex,follow");
  try {
    await api.initialize();
    const data = await api.request(
      endpoint.value + (historyMode.value ? "/history" : "/editor"),
    );
    title.value = data.title;
    version.value = data.base_version;
    pageExists.value = data.exists !== false;
    originalTitle.value = data.title;
    if (historyMode.value) {
      revisions.value = data.revisions;
      before.value = data.next_cursor;
      older.value = data.revisions[1]?.id;
      newer.value = data.revisions[0]?.id;
    } else {
      source.value = data.source;
      original.value = data.source;
      media.value = data.media;
      format.value = data.format || "wiki";
    }
    loaded.value = true;
  } catch (e) {
    error.value = (e as Error).message;
    loginRequired.value = e instanceof RequestError && e.status === 401;
  } finally {
    loading.value = false;
  }
}
onMounted(load);
watch(() => route.fullPath, load);
onBeforeRouteLeave(
  () =>
    !dirty.value ||
    historyMode.value ||
    window.confirm("Descartar as alterações não salvas?"),
);
function unload(e: BeforeUnloadEvent) {
  if (dirty.value && !historyMode.value) {
    e.preventDefault();
    e.returnValue = "";
  }
}
onMounted(() => window.addEventListener("beforeunload", unload));
onBeforeUnmount(() => {
  closeModal();
  window.removeEventListener("beforeunload", unload);
});
async function save() {
  await persist(source.value);
}
async function persist(nextSource: string): Promise<boolean> {
  busy.value = true;
  error.value = "";
  saveNotice.value = "";
  try {
    const result = await api.request(endpoint.value, "PUT", {
      title: title.value,
      source: nextSource,
      base_version: version.value,
    });
    source.value = nextSource;
    original.value = nextSource;
    originalTitle.value = title.value;
    if (result.unchanged === true) {
      version.value = result.base_version;
      saveNotice.value =
        "Não foram detectadas alterações na página. Nenhuma revisão foi criada.";
      return true;
    }
    await router.push("/" + encodeURIComponent(result.slug || slug.value) + "/");
    return true;
  } catch (e) {
    error.value = (e as Error).message;
    return false;
  } finally {
    busy.value = false;
  }
}
function confirmDelete() {
  closeModal();
  error.value = "";
  modal.value = "delete";
}
async function deletePage() {
  if (busy.value) return;
  if (await persist("")) closeModal();
}
async function more() {
  busy.value = true;
  try {
    const data = await api.request(
      endpoint.value + "/history?before=" + before.value,
    );
    revisions.value.push(...data.revisions);
    before.value = data.next_cursor;
  } catch (e) {
    error.value = (e as Error).message;
  } finally {
    busy.value = false;
  }
}
async function restore(id: number) {
  if (!window.confirm("Restaurar esta revisão como uma nova edição?")) return;
  busy.value = true;
  error.value = "";
  try {
    await api.request(endpoint.value + "/restore", "POST", {
      revision: id,
      base_version: version.value,
    });
    await load();
  } catch (e) {
    error.value = (e as Error).message;
  } finally {
    busy.value = false;
  }
}
async function upload(event: Event) {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;
  error.value = "";
  if (file.size > 5000000) {
    error.value = "Envie uma imagem de até 5 MB.";
    input.value = "";
    return;
  }
  busy.value = true;
  try {
    const data = await new Promise<string>((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(String(reader.result));
      reader.onerror = () => reject(new Error("Falha ao ler a imagem."));
      reader.readAsDataURL(file);
    });
    source.value = data;
    await save();
  } catch (e) {
    error.value = (e as Error).message;
  } finally {
    busy.value = false;
    input.value = "";
  }
}
async function removeImage() {
  source.value = "";
  await save();
}
</script>
<template>
  <section
    class="card article-card"
    :class="{ 'legacy-edit-page': !historyMode }"
  >
    <nav class="article-toolbar" aria-label="Ações da página">
      <RouterLink class="article-action" :to="`/${encodeURIComponent(slug)}/`"
        ><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar</RouterLink
      >
      <button
        v-if="!historyMode"
        type="button"
        class="article-action"
        :disabled="!loaded || loading || busy"
        @click="preview"
      >
        <i class="bi bi-eye" aria-hidden="true"></i> Visualizar página
      </button>
      <button
        v-if="!historyMode && loaded && pageExists"
        type="button"
        class="article-action text-danger"
        :disabled="loading || busy"
        @click="confirmDelete"
      >
        <i class="bi bi-trash" aria-hidden="true"></i> Excluir página
      </button>
    </nav>
    <div class="article-body">
      <h1 v-if="historyMode">
        Histórico de Edições
        <small>{{ title || slug.replaceAll("_", " ") }}</small>
      </h1>
      <h1 v-else class="legacy-edit-title">
        {{ media ? "Imagem: " : "" }}{{ title || slug.replaceAll("_", " ") }}
        <span class="legacy-edit-badge"
          >{{ loaded && !pageExists ? "Criar" : "Editar" }} {{ media ? "Imagem" : "Página" }}</span
        >
      </h1>
      <p v-if="saveNotice" class="alert alert-info" role="status">
        {{ saveNotice }}
      </p>
      <p v-if="loading" role="status">Carregando…</p>
      <p v-if="error" class="alert alert-danger" role="alert">{{ error }}</p>
      <RouterLink
        v-if="loginRequired"
        :to="{ path: '/page@Acesso', query: { redirect: route.fullPath } }"
        >Fazer login para continuar</RouterLink
      >
      <template v-if="loaded && !loading && !loginRequired">
        <form v-if="!historyMode" id="frm_edit" @submit.prevent="save">
          <label v-if="!pageExists && !media" class="form-label d-block mb-3">
            Título da página
            <input v-model="title" name="nome" class="form-control" maxlength="250" required />
          </label>
          <input v-else type="hidden" name="nome" :value="title" /><input
            type="hidden"
            name="nome_url"
            :value="slug"
          />
          <p class="legacy-edit-warning">
            <span aria-hidden="true">×</span> Obs.: Não colocar respostas nas
            especialidades. O foco desta página é somente as perguntas conforme
            o manual.
          </p>
          <template v-if="media">
            <div class="legacy-image-editor">
              <button
                type="button"
                class="legacy-image-picker"
                :aria-label="
                  mediaPreview ? 'Trocar Imagem' : 'Adicionar imagem'
                "
                :disabled="busy"
                @click="imageInput?.click()"
              >
                <img v-if="mediaPreview" :src="mediaPreview" :alt="title" />
                <span v-else class="legacy-image-placeholder"
                  >adicionar imagem</span
                >
              </button>
              <input
                ref="imageInput"
                type="file"
                class="visually-hidden"
                aria-label="Enviar imagem"
                accept=".jpg,.jpeg,.png,.svg"
                :disabled="busy"
                @change="upload"
              />
            </div>
            <div v-if="mediaPreview" class="legacy-edit-actions">
              <button
                type="button"
                class="btn legacy-edit-remove"
                :disabled="busy"
                @click="removeImage"
              >
                Remover Imagem
              </button>
              <button
                type="button"
                class="btn legacy-edit-save"
                :disabled="busy"
                @click="imageInput?.click()"
              >
                Trocar Imagem
              </button>
            </div>
            <p v-if="busy" role="status">Aguarde…</p>
          </template>
          <template v-else>
            <LegacyWikiEditor
              v-model="source"
              :key="slug"
              :disabled="busy"
              :label="format === 'html' ? 'Código HTML' : 'Código wiki'"
              @error="error = $event"
            />
            <div class="legacy-edit-actions">
              <button class="btn legacy-edit-save" :disabled="busy">
                {{ busy ? "Aguarde…" : "Salvar" }}
              </button>
            </div>
          </template>
        </form>
        <template v-else>
          <p v-if="!revisions.length">Ainda não há revisões registradas.</p>
          <div class="table-responsive">
            <table v-if="revisions.length" class="table align-middle">
              <thead>
                <tr>
                  <th>Comparar</th>
                  <th>Revisão</th>
                  <th>Autor</th>
                  <th class="text-end">Caracteres</th>
                  <th>Data</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in revisions" :key="r.id">
                  <td>
                    <input
                      v-model="older"
                      type="radio"
                      :value="r.id"
                      name="older"
                      :aria-label="`Versão anterior ${r.id}`"
                    />
                    <input
                      v-model="newer"
                      type="radio"
                      :value="r.id"
                      name="newer"
                      :aria-label="`Versão nova ${r.id}`"
                    />
                  </td>
                  <td>{{ r.id }}</td>
                  <td>
                    <span class="history-author"
                      ><AuthorAvatar :photo="r.author_photo" /><span>{{
                        r.author || "Usuário"
                      }}</span></span
                    >
                  </td>
                  <td class="history-character-count text-end">
                    {{
                      Array.from(r.source || "").length.toLocaleString("pt-BR")
                    }}
                  </td>
                  <td>{{ r.created_at }}</td>
                  <td>
                    <button
                      class="btn btn-sm btn-outline-primary me-2"
                      @click="showRevision(r)"
                    >
                      Visualizar</button
                    ><button
                      class="btn btn-sm btn-outline-secondary"
                      :disabled="busy"
                      @click="restore(r.id)"
                    >
                      Restaurar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <button
            v-if="before"
            class="btn btn-outline-primary me-2"
            :disabled="busy"
            @click="more"
          >
            Mais revisões</button
          ><button
            v-if="revisions.length > 1"
            class="btn btn-primary"
            @click="showComparison"
          >
            Comparar versões
          </button>
        </template>
      </template>
      <div v-if="error && !loginRequired" class="mt-3">
        <button
          v-if="media && dirty"
          class="btn btn-info me-2"
          :disabled="busy"
          @click="save"
        >
          Salvar novamente</button
        ><button class="btn btn-outline-secondary" @click="load">
          Recarregar
        </button>
        <p v-if="dirty">
          Copie o conteúdo antes de recarregar para preservar suas alterações.
        </p>
      </div>
    </div>
  </section>
  <WorkspaceModal
    v-if="modal"
    :busy="modal === 'delete' && busy"
    :title="
      modal === 'delete'
        ? 'Excluir página'
        : modal === 'preview'
          ? 'Visualizar página'
          : modal === 'compare'
            ? 'Comparar versões'
            : `Revisão ${selected?.id}`
    "
    @close="closeModal"
  >
    <template v-if="modal === 'delete'">
      <p>
        Deseja excluir o conteúdo de <strong>{{ title }}</strong
        >?
      </p>
      <p v-if="dirty">
        As alterações ainda não salvas serão descartadas ao confirmar.
      </p>
      <p v-if="error" class="alert alert-danger" role="alert">{{ error }}</p>
    </template>
    <template v-else-if="modal === 'preview'">
      <p class="text-muted">Prévia das alterações ainda não salvas.</p>
      <p v-if="previewBusy" role="status">Carregando prévia…</p>
      <div v-else-if="previewError" class="alert alert-danger" role="alert">
        {{ previewError }}
        <button type="button" class="btn btn-link" @click="preview">
          Tentar novamente
        </button>
      </div>
      <template v-else
        ><h2>{{ title }}</h2>
        <img
          v-if="media && mediaPreview"
          class="workspace-image-preview"
          :src="mediaPreview"
          :alt="title" />
        <p v-else-if="media">Nenhuma imagem selecionada.</p>
        <div
          v-else
          class="wiki-content"
          @click="
            ($event.target as Element).closest('a') && $event.preventDefault()
          "
          v-html="previewHtml"
      /></template>
    </template>
    <RevisionComparison
      v-else-if="modal === 'compare'"
      :changes="changes"
      :older="older"
      :newer="newer"
      :older-revision="olderRevision"
      :newer-revision="newerRevision"
    />
    <template v-else-if="selected"
      ><RevisionMetadata :revision="selected" />
      <pre class="revision-source">{{ selected.source }}</pre>
    </template>
    <template v-if="modal === 'delete'" #footer>
      <button
        type="button"
        class="btn btn-secondary"
        :disabled="busy"
        @click="closeModal"
      >
        Cancelar
      </button>
      <button
        type="button"
        class="btn btn-danger"
        :disabled="busy"
        @click="deletePage"
      >
        {{ busy ? "Excluindo…" : "Excluir página" }}
      </button>
    </template>
  </WorkspaceModal>
</template>

<style scoped>
.workspace-image-preview {
  max-width: 100%;
  height: auto;
}
.history-author {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  white-space: nowrap;
}
</style>
