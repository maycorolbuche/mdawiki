<script setup lang="ts">
import { computed, inject, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { RequestError, type SessionClient } from "../services/session";

interface ImageDetails {
  slug: string;
  title: string;
  url: string | null;
  version: string;
  meta: {
    extension?: string;
    width?: number;
    height?: number;
    size_bytes?: number;
  };
  pages: { id: string; slug: string; title: string }[];
  history: {
    id: string;
    url: string;
    created_at: string | null;
    author?: string | null;
    author_profile_id?: string | null;
  }[];
}

const session = inject<SessionClient>("session")!;
const route = useRoute();
const router = useRouter();
const slug = computed(() => String(route.params.slug));
const endpoint = computed(() => "/images/" + encodeURIComponent(slug.value));
const image = ref<ImageDetails | null>(null);
const missing = ref(false);
const busy = ref(true);
const historyMode = computed(() => route.meta.imageHistory === true);
const imagePath = computed(
  () => "/i/" + encodeURIComponent(image.value?.slug ?? slug.value),
);
const error = ref("");
const picker = ref<HTMLInputElement | null>(null);
let request = 0;

async function load() {
  const current = ++request;
  busy.value = true;
  image.value = null;
  missing.value = false;
  error.value = "";
  try {
    const result = await session.request(endpoint.value);
    if (current !== request) return;
    image.value = result;
    if (result.slug !== slug.value)
      await router.replace(
        "/i/" +
          encodeURIComponent(result.slug) +
          (historyMode.value ? "/historico" : ""),
      );
  } catch (cause) {
    if (current !== request) return;
    if (cause instanceof RequestError && cause.status === 404)
      missing.value = true;
    else error.value = "Não foi possível carregar a imagem.";
  } finally {
    if (current === request) busy.value = false;
  }
}
watch(slug, load, { immediate: !import.meta.env.SSR });

async function selectFile() {
  error.value = "";
  try {
    await session.initialize();
    if (!session.state.user) {
      await router.push({
        path: "/page@Acesso",
        query: { redirect: route.fullPath },
      });
      return;
    }
    picker.value?.click();
  } catch {
    error.value = "Não foi possível verificar a sessão.";
  }
}

async function upload(event: Event) {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  input.value = "";
  if (!file) return;
  if (file.size > 5_000_000) {
    error.value = "Selecione uma imagem de até 5 MB.";
    return;
  }
  const current = request;
  const target = endpoint.value;
  const version = image.value?.version ?? "";
  busy.value = true;
  error.value = "";
  try {
    const data = await new Promise<string>((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(String(reader.result));
      reader.onerror = () =>
        reject(new Error("Não foi possível ler o arquivo."));
      reader.readAsDataURL(file);
    });
    const result = await session.request(target, "PUT", {
      data,
      base_version: version,
    });
    if (current !== request) return;
    image.value = result;
    missing.value = false;
    if (result.slug !== slug.value)
      await router.replace("/i/" + encodeURIComponent(result.slug));
  } catch (cause) {
    if (current === request) error.value = (cause as Error).message;
  } finally {
    if (current === request) busy.value = false;
  }
}

async function restore(revisionId: string) {
  if (!image.value || busy.value) return;
  const current = request;
  const version = image.value.version;
  const target = endpoint.value;
  busy.value = true;
  error.value = "";
  try {
    await session.initialize();
    if (current !== request) return;
    if (!session.state.user) {
      await router.push({
        path: "/page@Acesso",
        query: { redirect: route.fullPath },
      });
      return;
    }
    const result = await session.request(target, "PUT", {
      revision_id: revisionId,
      base_version: version,
    });
    if (current !== request) return;
    image.value = result;
    await router.push("/i/" + encodeURIComponent(result.slug));
  } catch (cause) {
    if (current === request) error.value = (cause as Error).message;
  } finally {
    if (current === request) busy.value = false;
  }
}
</script>

<template>
  <div
    class="article-layout"
    :class="{ 'has-relations': image && !historyMode }"
  >
    <article class="card article-card" :aria-busy="busy">
      <nav v-if="image" class="article-toolbar" aria-label="Ações da imagem">
        <RouterLink v-if="historyMode" class="article-action" :to="imagePath">
          <i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar para a
          imagem
        </RouterLink>
        <template v-else>
          <button
            class="article-action"
            type="button"
            :disabled="busy"
            @click="selectFile"
          >
            <i class="bi bi-pencil-fill" aria-hidden="true"></i>
            {{ image.url ? "Substituir Imagem" : "Enviar imagem" }}
          </button>
          <RouterLink class="article-action" :to="imagePath + '/historico'">
            <i class="bi bi-clock-history" aria-hidden="true"></i> Histórico
          </RouterLink>
        </template>
      </nav>
      <div class="article-body">
        <h1 v-if="historyMode && image">
          Histórico de Edições <small>{{ image.title }}</small>
        </h1>
        <h1 v-else>
          {{ missing ? "Imagem não encontrada" : (image?.title ?? slug) }}
        </h1>
        <p v-if="busy" role="status">Processando imagem…</p>
        <p v-if="error" class="alert alert-danger" role="alert">{{ error }}</p>
        <img
          v-if="image?.url && !historyMode"
          :src="image.url"
          :alt="image.title"
          class="img-fluid d-block mb-3"
        />
        <template v-if="missing">
          <p>Ainda não há uma imagem neste endereço.</p>
          <button
            class="btn btn-primary"
            type="button"
            :disabled="busy"
            @click="selectFile"
          >
            Enviar imagem
          </button>
        </template>
        <input
          ref="picker"
          type="file"
          accept="image/png,image/jpeg,image/gif,image/webp"
          hidden
          @change="upload"
        />
        <template v-if="image && historyMode">
          <p v-if="!image.history.length">Nenhuma versão anterior.</p>
          <div v-else class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>Revisão</th>
                  <th>Autor</th>
                  <th>Imagem</th>
                  <th>Data</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="version in image.history" :key="version.id">
                  <td>{{ version.id }}</td>
                  <td>
                    <RouterLink v-if="version.author_profile_id" :to="'/u/' + version.author_profile_id">{{ version.author || "Usuário" }}</RouterLink>
                    <span v-else>{{ version.author || "Autor desconhecido" }}</span>
                  </td>
                  <td>
                    <img
                      :src="version.url"
                      :alt="'Versão ' + version.id"
                      width="120"
                      class="img-fluid"
                      loading="lazy"
                    />
                  </td>
                  <td>{{ version.created_at || "Data indisponível" }}</td>
                  <td>
                    <a
                      class="btn btn-sm btn-outline-primary me-2"
                      :href="version.url"
                      target="_blank"
                      rel="noopener noreferrer"
                      >Visualizar</a
                    >
                    <button
                      class="btn btn-sm btn-outline-secondary"
                      type="button"
                      :disabled="busy"
                      @click="restore(version.id)"
                    >
                      Restaurar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
      </div>
    </article>
    <aside
      v-if="image && !historyMode"
      class="page-relations"
      aria-label="Informações da imagem"
    >
      <section class="relation-panel relation-blue">
        <h2>Propriedades</h2>
        <dl class="px-3 py-2 mb-0">
          <dt>Extensão</dt>
          <dd>{{ image.meta.extension ?? "—" }}</dd>
          <dt>Largura</dt>
          <dd>
            {{ image.meta.width == null ? "—" : image.meta.width + " px" }}
          </dd>
          <dt>Altura</dt>
          <dd>
            {{ image.meta.height == null ? "—" : image.meta.height + " px" }}
          </dd>
          <dt>Tamanho</dt>
          <dd>
            {{
              image.meta.size_bytes == null
                ? "—"
                : new Intl.NumberFormat("pt-BR").format(image.meta.size_bytes) +
                  " bytes"
            }}
          </dd>
        </dl>
      </section>
      <section class="relation-panel relation-green">
        <h2>Páginas Vinculadas</h2>
        <p v-if="!image.pages.length" class="px-3 py-2 mb-0">
          Nenhuma página vinculada.
        </p>
        <ul v-else>
          <li v-for="page in image.pages" :key="page.id">
            <RouterLink :to="'/' + encodeURIComponent(page.slug) + '/'">{{
              page.title
            }}</RouterLink>
          </li>
        </ul>
      </section>
    </aside>
  </div>
</template>
