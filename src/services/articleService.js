import { articles } from '../mocks/articles.js'

// Contrato assíncrono: substituir somente esta implementação quando a API estiver disponível.
const normalize = (value) =>
  value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
const clone = (value) => structuredClone(value)
export const articleService = {
  async list(query = '') {
    const term = normalize(query.trim())
    return clone(
      articles.filter((article) =>
        normalize(`${article.title} ${article.area} ${article.description}`).includes(term),
      ),
    )
  },
  async findBySlug(slug) {
    return clone(articles.find((article) => article.slug === slug) ?? null)
  },
}
