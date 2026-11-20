<script setup lang="ts">
import { computed, ref } from 'vue'
import RevisionMetadata from './RevisionMetadata.vue'
type RevisionInfo = { author?: string | null; author_photo?: string | null; created_at?: string | null }
import { splitRevisionDiff, type DiffPart } from '../services/revision-diff'
const props = defineProps<{ changes: DiffPart[]; older: number | null; newer: number | null; olderRevision?: RevisionInfo; newerRevision?: RevisionInfo }>()
const mode = ref('merged')
const rows = computed(() => splitRevisionDiff(props.changes))
</script>
<template>
  <div class="comparison-options"><label for="revision-comparison-mode">Modo de visualização</label><select id="revision-comparison-mode" v-model="mode" class="form-select form-select-sm"><option value="merged">Mesclado</option><option value="split">Dividido</option></select></div>
  <p>Revisão {{ older }} → Revisão {{ newer }}</p>
  <div v-if="mode === 'merged'" class="comparison-metadata">
    <section aria-label="Dados da revisão anterior"><strong>Anterior — Revisão {{ older }}</strong><RevisionMetadata :revision="olderRevision" /></section>
    <section aria-label="Dados da revisão nova"><strong>Nova — Revisão {{ newer }}</strong><RevisionMetadata :revision="newerRevision" /></section>
  </div>
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
.split-comparison .removed { background: #ffe3e3; }
.split-comparison .added { background: #d3f9d8; }
.empty-diff-cell { background: #f3f3f3; }
</style>
