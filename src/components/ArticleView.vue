<script setup lang="ts">
import { shareImageTags } from "../services/share-image";
import {
  computed,
  inject,
  nextTick,
  onBeforeUnmount,
  onMounted,
  ref,
  watch,
} from "vue";
import { useRoute, useRouter } from "vue-router";
import { ApiError, getPage } from "../services/api";
import { cleanArticle } from "../services/content";
import { RequestError, userRole, type SessionClient } from "../services/session";
import PageRelations from "./PageRelations.vue";
import type { Page, Snapshot } from "../types/page";

const route = useRoute();
const router = useRouter();
const snapshot = inject<Snapshot>("snapshot")!;
const session = inject<SessionClient>("session")!;
const slug = computed(() => String(route.params.slug || "").replace(/\/$/, ""));
const page = ref<Page | null>(
  snapshot.page?.slug === slug.value ? snapshot.page : null,
);
const loading = ref(false);
const rebuilding = ref(false);
const rebuildError = ref("");
const missing = ref(false);
const failed = ref(false);
const topicsExpanded = ref(false);
const html = computed(() =>
  cleanArticle(page.value?.html || "", topicsExpanded.value),
);
const studyMaterial = computed(() => {
  try {
    const url = new URL(page.value?.study_material_url || "");
    return url.protocol === "https:" &&
      url.hostname === "plus.mda.wiki.br" &&
      !url.username &&
      !url.password &&
      !url.port
      ? url.href
      : null;
  } catch {
    return null;
  }
});
const hasRelations = computed(
  () =>
    page.value &&
    [
      page.value.categories,
      page.value.mentions,
      page.value.related_pages,
      page.value.missing_links,
      page.value.image_usage,
    ].some((links) => links?.length),
);
const reserved = computed(() => Array.from(slug.value).length === 1);
const articlePath = computed(() => `/${encodeURIComponent(page.value?.slug || slug.value)}/`);
function printArticle() {
  window.print();
}
let controller: AbortController | undefined;
let mounted = false;
let redirects = 0;
let lastVisited = "";

function restoreCachedPage() {
  page.value =
    session.articles.read(slug.value, !!session.state.user) ||
    (snapshot.page?.slug === slug.value ? snapshot.page : null);
  if (page.value) updateMetadata(page.value);
}

function updateShareImage(value: Page | null) {
  document.head.querySelectorAll("[data-share-image]").forEach(tag => tag.remove());
  const image = value?.is_public === false ? null : value?.image;
  const tags = shareImageTags(image, value?.title || "");
  document.querySelector('meta[name="twitter:card"]')
    ?.setAttribute("content", tags["og:image"] ? "summary_large_image" : "summary");
  for (const [name, content] of Object.entries(tags)) {
    const tag = document.createElement("meta");
    tag.setAttribute(name.startsWith("og:") ? "property" : "name", name);
    tag.content = content;
    tag.dataset.shareImage = "";
    document.head.append(tag);
  }
}

function updateMetadata(value: Page | null) {
  updateShareImage(value);
  document.querySelector('meta[property="og:type"]')?.setAttribute("content", value ? "article" : "website");
  document.title = value
    ? `${value.title} — MDAWiki`
    : "Página não encontrada — MDAWiki";
  for (const [selector, attribute, content] of [
    ['meta[name="description"]', "content", value?.description || ""],
    ['meta[property="og:title"]', "content", value?.title || "MDAWiki"],
    ['meta[property="og:description"]', "content", value?.description || ""],
    ['meta[property="og:url"]', "content", value?.canonical_url || ""],
    ['link[rel="canonical"]', "href", value?.canonical_url || ""],
    [
      'meta[name="robots"]',
      "content",
      value && value.is_public !== false ? "index,follow" : "noindex,follow",
    ],
  ])
    document.querySelector(selector!)?.setAttribute(attribute!, content!);
}

async function refresh() {
  controller?.abort();
  const request = new AbortController();
  const requestedSlug = slug.value;
  const requestedUser = session.state.user?.ID;
  controller = request;
  loading.value = true;
  failed.value = false;
  missing.value = false;
  const timeout = setTimeout(() => request.abort(new Error("timeout")), 15000);
  try {
    if (!snapshot.apiBaseUrl) throw new Error("API not configured");
    const value = requestedUser
      ? await session.request("/view/" + encodeURIComponent(requestedSlug))
      : await getPage(snapshot.apiBaseUrl, requestedSlug, request.signal);
    if (controller !== request || requestedUser !== session.state.user?.ID)
      return;
    if (value.redirect_slug && value.redirect_slug !== slug.value) {
      session.articles.remove(requestedSlug);
      if (++redirects > 8) throw new Error("Redirecionamento circular.");
      await router.replace("/" + encodeURIComponent(value.redirect_slug) + "/");
      return;
    }
    redirects = 0;
    session.articles.write(value, !!requestedUser);
    page.value = value;
    updateMetadata(value);
    if (value.slug !== requestedSlug) {
      session.articles.remove(requestedSlug);
      await router.replace({ path: "/" + encodeURIComponent(value.slug) + "/", query: route.query, hash: route.hash });
      if (controller !== request || requestedUser !== session.state.user?.ID || slug.value !== value.slug) return;
    }
    if (!value.read_only && value.is_public !== false && lastVisited !== slug.value) {
      lastVisited = slug.value;
      const visitedSlug = slug.value;
      void session
        .initialize()
        .then(() => session.request("/visits", "POST", { slug: visitedSlug }))
        .catch(() => {});
    }
  } catch (error) {
    if (controller !== request || requestedUser !== session.state.user?.ID)
      return;
    if (
      (error instanceof ApiError || error instanceof RequestError) &&
      [401, 403, 404].includes(error.status)
    ) {
      session.articles.remove(requestedSlug);
      page.value = null;
      missing.value = true;
      updateMetadata(null);
    } else {
      failed.value = true;
    }
  } finally {
    clearTimeout(timeout);
    if (controller === request) loading.value = false;
  }
}

async function updateArticle() {
  rebuildError.value = "";
  if (!page.value || page.value.read_only || userRole(session.state.user) !== "admin") {
    await refresh();
    return;
  }
  const target = slug.value;
  rebuilding.value = true;
  try {
    await session.request("/pages/" + encodeURIComponent(target) + "/rebuild", "POST", {});
    session.articles.remove(target);
    if (slug.value === target) await refresh();
  } catch (error) {
    if (slug.value === target) {
      rebuildError.value = error instanceof Error ? error.message : "Não foi possível reprocessar a página.";
    }
  } finally {
    rebuilding.value = false;
  }
}

watch(slug, () => {
  // Canonical replacement already has the response: keep it without a second fetch.
  if (page.value?.slug === slug.value) { updateMetadata(page.value); return; }
  rebuildError.value = "";
  topicsExpanded.value = false;
  page.value = snapshot.page?.slug === slug.value ? snapshot.page : null;
  missing.value = false;
  failed.value = false;
  if (mounted) {
    restoreCachedPage();
    document.title = page.value
      ? `${page.value.title} — MDAWiki`
      : "Carregando — MDAWiki";
    void refresh();
  }
});
onMounted(() => {
  mounted = true;
  restoreCachedPage();
  void refresh();
});
watch(
  () => session.state.user?.ID,
  () => {
    if (mounted) {
      restoreCachedPage();
      void refresh();
    }
  },
);
onBeforeUnmount(() => {
  updateShareImage(null);
  document.querySelector('meta[property="og:type"]')?.setAttribute("content", "website");
  controller?.abort();
  controller = undefined;
});

function followArticleLink(event: MouseEvent) {
  if ((event.target as Element).closest("button#indice_link")) {
    const content = event.currentTarget as HTMLElement;
    topicsExpanded.value = !topicsExpanded.value;
    void nextTick(() =>
      content.querySelector<HTMLButtonElement>("#indice_link")?.focus(),
    );
    return;
  }
  const anchor = (event.target as Element).closest("a");
  if (
    !anchor ||
    event.ctrlKey ||
    event.metaKey ||
    event.shiftKey ||
    event.altKey ||
    event.button !== 0 ||
    anchor.target === "_blank"
  )
    return;
  if (
    !anchor.getAttribute("href") ||
    anchor.getAttribute("href")?.startsWith("#")
  )
    return;
  const destination = new URL(anchor.href, window.location.href);
  const site = new URL(snapshot.siteUrl || window.location.origin);
  const legacy = destination.origin === "https://mda.wiki.br";
  if (
    !legacy &&
    (destination.origin !== site.origin ||
      !destination.pathname.startsWith(snapshot.basePath))
  )
    return;
  event.preventDefault();
  void router.push(
    (legacy
      ? destination.pathname
      : destination.pathname.slice(snapshot.basePath.length - 1)) +
      destination.search +
      destination.hash,
  );
}
</script>

<template>
  <div class="article-layout" :class="{ 'has-relations': hasRelations }">
    <article class="card article-card" :aria-busy="loading || rebuilding">
      <div class="article-toolbar">
        <RouterLink v-if="!reserved && !page?.read_only && userRole(session.state.user) !== 'reader'" class="article-action" :to="articlePath + 'editar'"
          ><i class="bi bi-pencil-fill" aria-hidden="true"></i>
          {{ page ? "Editar Página" : "Criar Página" }}</RouterLink
        >
        <RouterLink
          v-if="page && !page.read_only"
          class="article-action"
          :to="articlePath + 'historico'"
          ><i class="bi bi-clock-history" aria-hidden="true"></i> Histórico de
          Edições</RouterLink
        >
        <button class="article-action" :disabled="loading || rebuilding" @click="updateArticle">
          <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
          {{ loading || rebuilding ? "Atualizando…" : "Atualizar" }}
        </button>
        <button v-if="page" class="article-action" @click="printArticle">
          <i class="bi bi-printer-fill" aria-hidden="true"></i> Imprimir
        </button>
      </div>
      <div class="article-body">
        <div v-if="rebuildError" class="alert alert-warning" role="alert">{{ rebuildError }}</div>
        <div v-if="failed" class="alert alert-warning" role="alert">
          Não foi possível consultar a versão atual.<span v-if="page">
            Exibindo o conteúdo publicado.</span
          >
          <button class="btn btn-link p-0 ms-2" @click="refresh">
            Tentar novamente
          </button>
        </div>
        <template v-if="page">
          <h1 :class="{ 'has-study-material': studyMaterial }">
            {{ page.title }}
          </h1>
          <p v-if="studyMaterial" class="study-material">
            Material de apoio para estudos:
            <a
              :href="studyMaterial"
              target="_blank"
              rel="noopener noreferrer"
              >{{ studyMaterial }}</a
            >
          </p>
          <div class="wiki-content" @click="followArticleLink" v-html="html" />
          <section
            v-if="page.category_members?.length"
            class="category-members"
          >
            <h2>
              Páginas nesta Categoria
              <span class="badge text-bg-info">{{
                page.category_members.length
              }}</span>
            </h2>
            <ul class="article-list">
              <li v-for="member in page.category_members" :key="member.id">
                <RouterLink :to="`/${encodeURIComponent(member.slug)}/`">{{
                  member.title
                }}</RouterLink>
              </li>
            </ul>
          </section>
        </template>
        <template v-else-if="missing">
          <h1>Página não encontrada</h1>
          <p>Este artigo não existe ou não está disponível.</p>
          <div v-if="!reserved && userRole(session.state.user) !== 'reader'" class="text-center mt-4">
            <RouterLink class="btn btn-primary" :to="articlePath + 'editar'">
              <i class="bi bi-pencil-fill" aria-hidden="true"></i> Criar Página
            </RouterLink>
          </div>
        </template>
        <p v-else-if="!failed" role="status">Carregando artigo…</p>
      </div>
    </article>
    <PageRelations v-if="page" :page="page" />
  </div>
</template>

<style scoped>
.article-card h1.has-study-material {
  border-bottom: 0;
  padding-bottom: 0;
  margin-bottom: 16px;
}
.study-material {
  border-bottom: 1px solid #e7e7e7;
  padding-bottom: 18px;
  margin-bottom: 20px;
  overflow-wrap: anywhere;
}
</style>
