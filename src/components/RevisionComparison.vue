<script setup lang="ts">
import { computed, ref } from 'vue'
import RevisionMetadata from './RevisionMetadata.vue'
type Category = { key: string; title: string }
import { describeTemplate, templateLabels, type PageTemplate } from "../services/page-templates"
type RevisionInfo = { templates?: PageTemplate[]; categories?: Category[]; author?: string | null; author_photo?: string | null; created_at?: string | null }
import { splitRevisionDiff, type DiffPart } from '../services/revision-diff'
const props = defineProps<{ changes: DiffPart[]; older: number | null; newer: number | null; olderRevision?: RevisionInfo; newerRevision?: RevisionInfo }>()
const mode = ref('split')
const rows = computed(() => splitRevisionDiff(props.changes))
const categoryColumns = computed(() => [
  { label: 'Anterior', categories: props.olderRevision?.categories,
    other: props.newerRevision?.categories, change: 'removed', status: 'Removida' },
  { label: 'Nova', categories: props.newerRevision?.categories,
    other: props.olderRevision?.categories, change: 'added', status: 'Adicionada' },
].map(column => {
  const otherKeys = new Set(column.other?.map(category => category.key))
  return {
    ...column,
    items: column.categories?.map(category => ({
      ...category,
      changed: column.other !== undefined && !otherKeys.has(category.key),
    })),
  }
}))
const templateChanges = computed(() => {
  if (!props.olderRevision?.templates || !props.newerRevision?.templates) return []
  const before = new Map(props.olderRevision.templates.map(item => [item.template, describeTemplate(item)]))
  const after = new Map(props.newerRevision.templates.map(item => [item.template, describeTemplate(item)]))
  return [...new Set([...before.keys(), ...after.keys()])]
    .filter(type => before.get(type) !== after.get(type))
    .map(type => ({ type, before: before.get(type), after: after.get(type) }))
})
</script>
<template>
  <div class="comparison-options"><label for="revision-comparison-mode">Modo de visualização</label><select id="revision-comparison-mode" v-model="mode" class="form-select form-select-sm"><option value="merged">Mesclado</option><option value="split">Dividido</option></select></div>
  <p>Revisão {{ older }} → Revisão {{ newer }}</p>
  <div v-if="mode === 'merged'" class="comparison-metadata">
    <section aria-label="Dados da revisão anterior"><strong>Anterior — Revisão {{ older }}</strong><RevisionMetadata :revision="olderRevision" /></section>
    <section aria-label="Dados da revisão nova"><strong>Nova — Revisão {{ newer }}</strong><RevisionMetadata :revision="newerRevision" /></section>
  </div>
  <section v-if="categoryColumns.some(column => column.items)" class="mb-3" aria-labelledby="revision-categories-heading">
    <h3 id="revision-categories-heading" class="h6">Categorias</h3>
    <div class="comparison-metadata">
      <div v-for="column in categoryColumns" :key="column.label">
        <strong>{{ column.label }}</strong>
        <p v-if="!column.items" class="mb-0 text-muted">Categorias indisponíveis nesta revisão.</p>
        <p v-else-if="!column.items.length" class="mb-0 text-muted">Nenhuma categoria</p>
        <ul v-else class="list-unstyled d-flex flex-wrap gap-2 mt-1 mb-0">
          <li v-for="category in column.items" :key="category.key"
            class="category-chip" :class="category.changed ? column.change : undefined">
            {{ category.title }}<small v-if="category.changed"> · {{ column.status }}</small>
          </li>
        </ul>
      </div>
    </div>
  </section>
  <section v-if="templateChanges.length" class="mb-3" aria-labelledby="revision-template-heading">
    <h3 id="revision-template-heading" class="h6">Alterações nos templates</h3>
    <div v-for="item in templateChanges" :key="item.type" class="mb-2">
      <strong>{{ templateLabels[item.type] }}</strong>
      <div class="comparison-metadata">
        <div><small>Anterior</small><p class="category-chip removed template-summary">{{ item.before ?? 'Sem template' }}</p></div>
        <div><small>Nova</small><p class="category-chip added template-summary">{{ item.after ?? 'Sem template' }}</p></div>
      </div>
    </div>
  </section>
  <p class="small text-muted">Vermelho: removido da versão anterior. Verde: adicionado à versão nova.</p>
  <div v-if="mode === 'merged'" class="revision-diff"><span v-for="(part, i) in changes" :key="i" :class="{ added: part.added, removed: part.removed }">{{ part.value }}</span></div>
  <div v-else class="split-comparison-scroll">
    <table class="split-comparison"><caption class="visually-hidden">Comparação lado a lado das revisões {{ older }} e {{ newer }}</caption>
      <thead><tr><th scope="col">Anterior — Revisão {{ older }}<RevisionMetadata :revision="olderRevision" /></th><th scope="col">Nova — Revisão {{ newer }}<RevisionMetadata :revision="newerRevision" /></th></tr></thead>
      <tbody><tr v-for="(row, i) in rows" :key="i"><td :class="{ removed: row.changed && row.before !== null, 'empty-diff-cell': row.before === null }"><pre>{{ row.before ?? ' ' }}</pre></td><td :class="{ added: row.changed && row.after !== null, 'empty-diff-cell': row.after === null }"><pre>{{ row.after ?? ' ' }}</pre></td></tr></tbody>
    </table>
  </div>
</template>
<style scoped>
.comparison-metadata { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr)); gap: 12px 24px; }
.comparison-options { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 16px; }
.comparison-options select { width: auto; }
.split-comparison-scroll { max-width: 100%; overflow-x: auto; }
.split-comparison { width: 100%; min-width: 540px; table-layout: fixed; border-collapse: collapse; }
.split-comparison th, .split-comparison td { width: 50%; border: 1px solid #ddd; padding: 6px 10px; vertical-align: top; }
.split-comparison th { background: #f1f1f1; }
.split-comparison pre { margin: 0; font-family: ui-monospace, monospace; font-size: 13px; white-space: pre-wrap; overflow-wrap: anywhere; }
.split-comparison .removed, .category-chip.removed { background: #ffe3e3; }
.split-comparison .added, .category-chip.added { background: #d3f9d8; }
.category-chip { padding: 3px 7px; border: 1px solid #ddd; border-radius: 3px; }
.template-summary { white-space: pre-line; overflow-wrap: anywhere; }
.empty-diff-cell { background: #f3f3f3; }
</style>
