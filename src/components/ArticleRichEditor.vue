<script setup>
import { watch } from "vue";
import { EditorContent, useEditor } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import { sanitizeArticleHtml } from "../utils/articleHtml";

const props = defineProps({ modelValue: { type: String, default: "" } });
const emit = defineEmits(["update:modelValue"]);
const editor = useEditor({
  extensions: [
    StarterKit.configure({
      code: false,
      codeBlock: false,
      strike: false,
      underline: false,
      horizontalRule: false,
      trailingNode: false,
      heading: { levels: [1, 2, 3, 4, 5, 6] },
      link: {
        openOnClick: false,
        autolink: false,
        HTMLAttributes: { target: null, rel: null },
      },
    }),
  ],
  content: sanitizeArticleHtml(props.modelValue),
  editorProps: {
    attributes: {
      role: "textbox",
      "aria-label": "Conteúdo do artigo",
      "aria-multiline": "true",
    },
  },
  onUpdate: ({ editor }) =>
    emit("update:modelValue", sanitizeArticleHtml(editor.getHTML())),
});
watch(
  () => props.modelValue,
  (value) => {
    if (editor.value && sanitizeArticleHtml(editor.value.getHTML()) !== value)
      editor.value.commands.setContent(sanitizeArticleHtml(value), {
        emitUpdate: false,
      });
  },
);
function heading(event) {
  const level = Number(event.target.value);
  if (level) editor.value.chain().focus().setHeading({ level }).run();
  else editor.value.chain().focus().setParagraph().run();
}
const tools = [
  { label: "Negrito", mark: "bold", command: "toggleBold", icon: "type-bold" },
  {
    label: "Itálico",
    mark: "italic",
    command: "toggleItalic",
    icon: "type-italic",
  },
  {
    label: "Lista",
    mark: "bulletList",
    command: "toggleBulletList",
    icon: "list-ul",
  },
  {
    label: "Lista numerada",
    mark: "orderedList",
    command: "toggleOrderedList",
    icon: "list-ol",
  },
  {
    label: "Citação",
    mark: "blockquote",
    command: "toggleBlockquote",
    icon: "quote",
  },
];
</script>

<template>
  <div v-if="editor" class="article-editor border rounded">
    <div
      class="d-flex flex-wrap gap-1 p-2 border-bottom bg-light"
      role="toolbar"
      aria-label="Formatação do conteúdo"
    >
      <select
        class="form-select form-select-sm w-auto"
        aria-label="Estilo do parágrafo"
        :value="
          editor.isActive('heading') ? editor.getAttributes('heading').level : 0
        "
        @change="heading"
      >
        <option :value="0">Parágrafo</option>
        <option v-for="level in 6" :key="level" :value="level">
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
}
</style>
