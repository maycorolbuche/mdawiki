<script setup lang="ts">
import { computed, ref } from "vue";
import {
  honorFields, personFields, positionFields, templateLabels,
  type PageTemplate, type TemplateType, type Position,
} from "../services/page-templates";

defineProps<{ disabled: boolean }>();
const templates = defineModel<PageTemplate[]>({ required: true });
const selection = ref<TemplateType | "">("");
const options = computed(() => (Object.keys(templateLabels) as TemplateType[])
  .filter(type => !templates.value.some(item => item.template === type)));

function add() {
  if (!selection.value || !options.value.includes(selection.value)) return;
  templates.value.push({ template: selection.value, meta: {} });
  selection.value = "";
}
function addPosition(item: PageTemplate) {
  const position: Position = { title: "", start_date: "", end_date: "", predecessor: "", successor: "" };
  item.meta.positions = [...(item.meta.positions ?? []), position];
}
</script>

<template>
  <div v-for="(item, index) in templates" :key="item.template" class="relation-panel relation-blue">
    <h2>
      {{ templateLabels[item.template] }}
      <button type="button" class="btn-close btn-close-white" :disabled="disabled"
        :aria-label="'Remover ' + templateLabels[item.template]" @click="templates.splice(index, 1)" />
    </h2>
    <fieldset class="p-3" :disabled="disabled">
      <template v-if="item.template === 'person'">
        <label v-for="(label, key) in personFields" :key="key" class="form-label d-block">
          {{ label }}
          <input v-model="item.meta[key]" class="form-control form-control-sm" maxlength="1000"
            :placeholder="key.endsWith('_date') ? 'AAAA, AAAA-MM ou AAAA-MM-DD' : undefined" />
        </label>
        <p class="fw-semibold mb-2">Feitos</p>
        <div v-for="(_, number) in item.meta.achievements" :key="number" class="input-group input-group-sm mb-2">
          <textarea v-model="item.meta.achievements![number]" class="form-control" maxlength="1000"
            :aria-label="'Feito ' + (number + 1)" />
          <button type="button" class="btn btn-outline-secondary" :aria-label="'Remover feito ' + (number + 1)"
            @click="item.meta.achievements?.splice(number, 1)">×</button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary mb-3"
          @click="item.meta.achievements = [...(item.meta.achievements ?? []), '']">Adicionar feito</button>
        <p class="fw-semibold mb-2">Cargos</p>
        <fieldset v-for="(position, number) in item.meta.positions" :key="number" class="border rounded p-2 mb-2">
          <legend class="float-none w-auto fs-6 px-1">Cargo {{ number + 1 }}</legend>
          <label v-for="(label, key) in positionFields" :key="key" class="form-label d-block">
            {{ label }}
            <input v-model="position[key]" class="form-control form-control-sm" maxlength="1000"
              :placeholder="key.endsWith('_date') ? 'AAAA, AAAA-MM ou AAAA-MM-DD' : undefined" />
          </label>
          <button type="button" class="btn btn-sm btn-outline-danger"
            @click="item.meta.positions?.splice(number, 1)">Remover cargo {{ number + 1 }}</button>
        </fieldset>
        <button type="button" class="btn btn-sm btn-outline-secondary" @click="addPosition(item)">Adicionar cargo</button>
      </template>
      <template v-else>
        <template v-for="(label, key) in honorFields" :key="key">
          <label v-if="item.template === 'pathfinder_honor' || !['level', 'year', 'origin_institution'].includes(key)"
            class="form-label d-block">
            {{ label }}
            <input v-model="item.meta[key]" class="form-control form-control-sm" maxlength="1000"
              :inputmode="key === 'year' || key === 'level' ? 'numeric' : undefined" />
          </label>
        </template>
        <label class="form-check">
          <input v-model="item.meta.is_obsolete" type="checkbox" class="form-check-input" />
          Especialidade obsoleta
        </label>
      </template>
    </fieldset>
  </div>
  <section v-if="options.length" class="relation-panel relation-blue">
    <h2>Adicionar template</h2>
    <div class="p-3">
      <select v-model="selection" class="form-select form-select-sm mb-2" aria-label="Tipo de template" :disabled="disabled">
        <option value="">Selecione um template</option>
        <option v-for="type in options" :key="type" :value="type">{{ templateLabels[type] }}</option>
      </select>
      <button type="button" class="btn btn-sm btn-outline-secondary"
        :disabled="disabled || !selection" @click="add">Adicionar template</button>
    </div>
  </section>
</template>
