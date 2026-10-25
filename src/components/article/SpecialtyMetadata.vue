<script setup>
import { computed } from 'vue'
import pathfinders from '@/assets/images/pathfinders.png'
import adra from '@/assets/images/adra.png'
const props = defineProps({ article: { type: Object, required: true } })
const fields = computed(() => [
  ['Área', props.article.area],
  ['Código', props.article.code],
  ['Nível', props.article.level],
  ['Ano', props.article.year],
  ['Origem', props.article.origin],
])
</script>
<template>
  <div class="specialty-info" aria-label="Dados da especialidade">
    <img class="pathfinder" :src="pathfinders" alt="Emblema dos Desbravadores" />
    <dl class="metadata">
      <div v-for="[label, value] in fields" :key="label">
        <dt>{{ label }}</dt>
        <dd>{{ value }}</dd>
      </div>
    </dl>
    <img class="patch" :src="adra" alt="Emblema da especialidade ADRA" />
  </div>
</template>
