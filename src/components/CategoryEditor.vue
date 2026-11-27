<script setup lang="ts">
import { computed, ref } from "vue";

type Category = { slug?: string; title: string };
const props = defineProps<{ options: Category[]; disabled: boolean }>();
const categories = defineModel<Category[]>({ required: true });
const input = ref("");
const open = ref(false);

function key(value: string) {
  return value.normalize("NFD").replace(/\p{M}/gu, "").toLowerCase()
    .replace(/[^\p{L}\p{N}]+/gu, "-").replace(/^-|-$/g, "");
}

const query = computed(() => key(input.value));
const suggestions = computed(() => props.options.filter(
  (option) => !categories.value.some((item) => key(item.slug || item.title) === key(option.slug || option.title))
    && (key(option.title).includes(query.value) || key(option.slug || "").includes(query.value)),
).slice(0, 12));

function add(category: Category) {
  const identity = key(category.slug || category.title);
  if (!categories.value.some((item) => key(item.slug || item.title) === identity)) {
    categories.value = [...categories.value, category];
  }
  input.value = "";
  open.value = false;
}

function commit() {
  const title = input.value.trim();
  if (title) {
    add(props.options.find((option) => key(option.title) === query.value || key(option.slug || "") === query.value)
      ?? { title });
  }
}

defineExpose({ commit });
</script>

<template>
  <aside class="card">
    <div class="card-body">
      <h2 class="h6 card-title">Categorias</h2>
      <ul v-if="categories.length" class="list-unstyled d-flex flex-wrap gap-1 mb-2">
        <li v-for="(category, index) in categories" :key="category.slug || category.title"
          class="badge text-bg-secondary d-inline-flex align-items-center gap-1 text-wrap text-start">
          {{ category.title }}
          <button type="button" class="btn-close btn-close-white" :disabled="disabled"
            :aria-label="'Remover ' + category.title"
            @click="categories = categories.filter((_, position) => position !== index)" />
        </li>
      </ul>
      <div class="input-group input-group-sm">
        <input v-model="input" type="text" class="form-control" aria-label="Nome da categoria"
          placeholder="Buscar ou digitar" maxlength="250" :disabled="disabled" autocomplete="off"
          @focus="open = true" @input="open = true" @keydown.escape.stop.prevent="open = false"
          @keydown.enter.prevent="commit" />
        <button type="button" class="btn btn-outline-secondary" :disabled="disabled || !input.trim()"
          @click="commit">Adicionar</button>
      </div>
      <ul v-if="open && suggestions.length" class="list-group mt-2 mb-0" aria-label="Categorias sugeridas">
        <li v-for="option in suggestions" :key="option.slug" class="list-group-item p-0">
          <button type="button" class="btn btn-sm w-100 text-start" :disabled="disabled"
            @click="add(option)">{{ option.title }}</button>
        </li>
      </ul>
    </div>
  </aside>
</template>
