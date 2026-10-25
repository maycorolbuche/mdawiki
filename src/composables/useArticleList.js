import { onMounted, ref } from 'vue'
import { articleService } from '@/services/articleService'
export function useArticleList() {
  const articles = ref([])
  const loading = ref(true)
  const error = ref('')
  async function load() {
    loading.value = true
    error.value = ''
    try {
      articles.value = await articleService.list()
    } catch {
      error.value = 'Não foi possível carregar as especialidades.'
    } finally {
      loading.value = false
    }
  }
  onMounted(load)
  return { articles, loading, error, load }
}
