import { nextTick, onMounted, onUnmounted, ref, toValue, watch } from 'vue'
export function useArticleToc(sections) {
  const activeSection = ref('')
  function update() {
    const list = toValue(sections) || []
    const visible = list.filter(({ id }) => {
      const rect = document.getElementById(id)?.getBoundingClientRect()
      return rect && rect.height > 0 && rect.top <= window.innerHeight * 0.4
    })
    activeSection.value = visible.at(-1)?.id ?? list[0]?.id ?? ''
  }
  watch(
    () => toValue(sections),
    async () => {
      await nextTick()
      update()
    },
    { immediate: true },
  )
  onMounted(() => {
    window.addEventListener('scroll', update, { passive: true })
    window.addEventListener('resize', update)
  })
  onUnmounted(() => {
    window.removeEventListener('scroll', update)
    window.removeEventListener('resize', update)
  })
  return { activeSection }
}
