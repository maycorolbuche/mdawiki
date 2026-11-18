<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import $ from 'jquery'
import installMarkItUp from '../vendor/markitup/jquery.markitup.js'
import settings from '../vendor/markitup/settings.js'
import '../vendor/markitup/skin.css'
import '../vendor/markitup/wiki.css'

defineProps<{ modelValue: string; disabled?: boolean; label?: string }>()
const emit = defineEmits<{ 'update:modelValue': [value: string]; error: [message: string] }>()
const textarea = ref<HTMLTextAreaElement>()
const host = ref<HTMLDivElement>()
let editor: JQuery<HTMLTextAreaElement> | undefined
onMounted(() => {
  if (!textarea.value) return
  try {
    installMarkItUp($)
    editor = $(textarea.value)
    editor.markItUp({ ...settings, markupSet: structuredClone(settings.markupSet), root: import.meta.env.BASE_URL,
      // markItUp changes the native value directly; inform Vue after every insertion.
      afterInsert: () => textarea.value?.dispatchEvent(new Event('input', { bubbles: true })),
    })
    // Keep the original dropdowns inside narrow viewports without clipping them.
    $(host.value!).on('mouseenter.vueMarkItUp', '.markItUpDropMenu', function () {
      const menu = $(this).children('ul')[0]
      if (!menu) return
      menu.style.left = '0px'
      const bounds = menu.getBoundingClientRect()
      let shift = Math.min(0, window.innerWidth - 12 - bounds.right)
      if (bounds.left + shift < 12) shift += 12 - bounds.left - shift
      menu.style.left = `${shift}px`
    })
  } catch { emit('error', 'Não foi possível carregar a barra do editor. O código continua disponível para edição.') }
})
onBeforeUnmount(() => { if (host.value) $(host.value).off('.vueMarkItUp'); editor?.markItUp('remove') })
</script>

<template>
  <div ref="host" class="legacy-editor-host" :aria-disabled="disabled || undefined">
    <textarea id="page_code" ref="textarea" name="page_code" class="page_code_editor wiki-editor" :aria-label="label || 'Código wiki'" :value="modelValue" :disabled="disabled" spellcheck="false" maxlength="500000" @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)" />
  </div>
</template>
