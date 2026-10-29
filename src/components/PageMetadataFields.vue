<script setup>
const props = defineProps({
  fields: { type: Array, required: true },
  modelValue: { type: Object, required: true },
});
const emit = defineEmits(["update:modelValue"]);
function update(field, event) {
  emit("update:modelValue", {
    ...props.modelValue,
    [field.key]:
      field.input === "boolean" ? event.target.checked : event.target.value,
  });
}
function value(field) {
  const item = props.modelValue[field.key];
  return Array.isArray(item) ? item.join(", ") : (item ?? "");
}
</script>
<template>
  <fieldset v-if="fields.length" class="border rounded p-3 mb-3">
    <legend class="float-none w-auto px-2 fs-6">Informações do tipo</legend>
    <div class="row g-3">
      <div v-for="field in fields" :key="field.key" class="col-12 col-md-6">
        <div v-if="field.input === 'boolean'" class="form-check mt-4">
          <input
            :id="`meta-${field.key}`"
            type="checkbox"
            class="form-check-input"
            :checked="modelValue[field.key] === true"
            @change="update(field, $event)"
          />
          <label :for="`meta-${field.key}`" class="form-check-label">{{
            field.label
          }}</label>
        </div>
        <template v-else>
          <label :for="`meta-${field.key}`" class="form-label"
            >{{ field.label }}{{ field.required ? " *" : "" }}</label
          >
          <select
            v-if="field.input === 'select'"
            :id="`meta-${field.key}`"
            class="form-select"
            :value="value(field)"
            :required="field.required"
            @change="update(field, $event)"
          >
            <option value="">Selecione</option>
            <option
              v-for="option in field.options"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
          <input
            v-else
            :id="`meta-${field.key}`"
            class="form-control"
            :type="
              ['number', 'date'].includes(field.input) ? field.input : 'text'
            "
            :value="value(field)"
            :required="field.required"
            :min="field.min"
            :max="field.max"
            :maxlength="field.input === 'tags' ? undefined : field.maxLength"
            :step="field.input === 'number' ? 1 : undefined"
            @input="update(field, $event)"
          />
          <small v-if="field.input === 'tags'" class="form-text"
            >Separe por vírgulas.</small
          >
        </template>
      </div>
    </div>
  </fieldset>
</template>
