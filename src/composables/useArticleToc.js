import { onMounted, onUnmounted, ref } from 'vue'
import { articleSections } from '@/data/navigation'
export function useArticleToc() {
  const activeSection = ref('requisitos')
  function update() {
    const visible = articleSections.filter(({ id }) => {
      const element = document.getElementById(id)
      return (
        element &&
        element.getBoundingClientRect().height > 0 &&
        element.getBoundingClientRect().top <= window.innerHeight * 0.4
      )
    })
    activeSection.value = visible.at(-1)?.id ?? 'requisitos'
  }
  onMounted(() => {
    window.addEventListener('scroll', update, { passive: true })
    update()
  })
  onUnmounted(() => window.removeEventListener('scroll', update))
  return { activeSection }
}
