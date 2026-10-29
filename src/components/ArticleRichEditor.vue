<script setup>
import { reactive, watch } from "vue";
import { EditorContent, useEditor } from "@tiptap/vue-3";
import { editorExtensions } from "../utils/editorExtensions.js";
import { sanitizeArticleHtml } from "../utils/articleHtml.js";
const props = defineProps({
  modelValue: { type: String, default: "" },
  policy: { type: Object, required: true },
  readOnly: { type: Boolean, default: false },
});
const emit = defineEmits(["update:modelValue"]);
const editor = useEditor({
  editable: !props.readOnly,
  extensions: editorExtensions(props.policy),
  content: sanitizeArticleHtml(props.modelValue, props.policy),
  editorProps: {
    attributes: {
      role: "textbox",
      "aria-label": "Conteúdo do artigo",
      "aria-multiline": "true",
    },
    transformPastedHTML: (html) => sanitizeArticleHtml(html, props.policy),
  },
  onUpdate: ({ editor }) =>
    emit(
      "update:modelValue",
      sanitizeArticleHtml(editor.getHTML(), props.policy),
    ),
});
watch(
  () => props.readOnly,
  (value) => editor.value?.setEditable(!value, false),
);
watch(
  () => props.modelValue,
  (value) => {
    if (
      editor.value &&
      sanitizeArticleHtml(editor.value.getHTML(), props.policy) !== value
    )
      editor.value.commands.setContent(
        sanitizeArticleHtml(value, props.policy),
        { emitUpdate: false },
      );
  },
);
const levels = [2, 3, 4, 5, 6].filter((level) =>
  props.policy.tags.includes(`h${level}`),
);
function heading(event) {
  const level = Number(event.target.value);
  if (level) editor.value.chain().focus().setHeading({ level }).run();
  else editor.value.chain().focus().setParagraph().run();
}
const tools = [
  {
    label: "Negrito",
    tag: "strong",
    mark: "bold",
    command: "toggleBold",
    icon: "type-bold",
  },
  {
    label: "Itálico",
    tag: "em",
    mark: "italic",
    command: "toggleItalic",
    icon: "type-italic",
  },
  {
    label: "Sublinhado",
    tag: "u",
    mark: "underline",
    command: "toggleUnderline",
    icon: "type-underline",
  },
  {
    label: "Riscado",
    tag: "s",
    mark: "strike",
    command: "toggleStrike",
    icon: "type-strikethrough",
  },
  {
    label: "Lista",
    tag: "ul",
    mark: "bulletList",
    command: "toggleBulletList",
    icon: "list-ul",
  },
  {
    label: "Lista numerada",
    tag: "ol",
    mark: "orderedList",
    command: "toggleOrderedList",
    icon: "list-ol",
  },
  {
    label: "Citação",
    tag: "blockquote",
    mark: "blockquote",
    command: "toggleBlockquote",
    icon: "quote",
  },
  {
    label: "Código",
    tag: "code",
    mark: "code",
    command: "toggleCode",
    icon: "code",
  },
  {
    label: "Subscrito",
    tag: "sub",
    mark: "subscript",
    command: "toggleSubscript",
    icon: "subscript",
  },
  {
    label: "Sobrescrito",
    tag: "sup",
    mark: "superscript",
    command: "toggleSuperscript",
    icon: "superscript",
  },
].filter((tool) => props.policy.tags.includes(tool.tag));
const link = { open: false, url: "", title: "" };
// Ref reativo separado dos estados internos do editor.
const linkForm = reactive(link);
function editLink() {
  linkForm.url = editor.value.getAttributes("link").href || "";
  linkForm.title = editor.value.getAttributes("link").title || "";
  linkForm.open = true;
}
function applyLink() {
  const html = sanitizeArticleHtml(
    `<a href="${linkForm.url.replaceAll('"', "&quot;")}">x</a>`,
    props.policy,
  );
  if (!html.includes("href=")) return;
  editor.value
    .chain()
    .focus()
    .extendMarkRange("link")
    .setLink({ href: linkForm.url, title: linkForm.title || null })
    .run();
  linkForm.open = false;
}
// Repassa a serialização atual ao salvar, mesmo quando só o título/metadados foram editados.
defineExpose({
  getHTML: () =>
    sanitizeArticleHtml(
      editor.value?.getHTML() ?? props.modelValue,
      props.policy,
    ),
});
</script>
<template>
  <div v-if="editor" class="article-editor border rounded">
    <div
      class="d-flex flex-wrap gap-1 p-2 border-bottom bg-light"
      role="toolbar"
      aria-label="Formatação do conteúdo"
    >
      <select
        v-if="levels.length"
        class="form-select form-select-sm w-auto"
        aria-label="Estilo do parágrafo"
        :value="
          editor.isActive('heading') ? editor.getAttributes('heading').level : 0
        "
        @change="heading"
      >
        <option :value="0">Parágrafo</option>
        <option v-for="level in levels" :key="level" :value="level">
          Título {{ level }}
        </option>
      </select>
      <button
        v-for="tool in tools"
        :key="tool.mark"
        type="button"
        class="btn btn-sm"
        :class="
          editor.isActive(tool.mark) ? 'btn-primary' : 'btn-outline-secondary'
        "
        :aria-label="tool.label"
        :title="tool.label"
        :aria-pressed="editor.isActive(tool.mark)"
        @click="editor.chain().focus()[tool.command]().run()"
      >
        <i :class="`bi bi-${tool.icon}`"></i>
      </button>
      <button
        v-if="policy.tags.includes('a')"
        type="button"
        class="btn btn-sm btn-outline-secondary"
        @click="editLink"
      >
        Link
      </button>
      <button
        v-if="policy.tags.includes('a') && editor.isActive('link')"
        type="button"
        class="btn btn-sm btn-outline-secondary"
        @click="editor.chain().focus().unsetLink().run()"
      >
        Remover link
      </button>
      <button
        v-if="policy.tags.includes('table')"
        type="button"
        class="btn btn-sm btn-outline-secondary"
        @click="
          editor
            .chain()
            .focus()
            .insertTable({
              rows: 3,
              cols: 3,
              withHeaderRow: policy.tags.includes('th'),
            })
            .run()
        "
      >
        Tabela
      </button>
      <button
        type="button"
        class="btn btn-sm btn-outline-secondary"
        :disabled="!editor.can().undo()"
        @click="editor.chain().focus().undo().run()"
      >
        Desfazer
      </button>
      <button
        type="button"
        class="btn btn-sm btn-outline-secondary"
        :disabled="!editor.can().redo()"
        @click="editor.chain().focus().redo().run()"
      >
        Refazer
      </button>
    </div>
    <div
      v-if="linkForm.open"
      class="d-flex flex-wrap gap-2 p-2 bg-light border-bottom"
    >
      <input
        v-model="linkForm.url"
        class="form-control"
        aria-label="Endereço do link"
        placeholder="https://..."
      />
      <input
        v-model="linkForm.title"
        class="form-control"
        aria-label="Título do link"
        placeholder="Título opcional"
      />
      <button type="button" class="btn btn-primary btn-sm" @click="applyLink">
        Aplicar link
      </button>
      <button
        type="button"
        class="btn btn-light btn-sm"
        @click="linkForm.open = false"
      >
        Cancelar link
      </button>
    </div>
    <EditorContent :editor="editor" />
  </div>
</template>
<style scoped lang="scss">
.article-editor :deep(.tiptap) {
  min-height: 280px;
  max-height: 50vh;
  overflow-y: auto;
  padding: 1rem;
  overflow-wrap: anywhere;
  &:focus {
    outline: 2px solid var(--bs-primary);
    outline-offset: -2px;
  }
  blockquote {
    border-left: 3px solid var(--bs-secondary);
    padding-left: 1rem;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    th,
    td {
      border: 1px solid #ccc;
      padding: 0.5rem;
    }
  }
  img {
    max-width: 100%;
    height: auto;
  }
  pre {
    white-space: pre-wrap;
  }
}
</style>
