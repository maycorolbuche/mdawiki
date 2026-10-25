import { ApiError } from './client.js'
import { validAnchor, validSlug } from '../../utils/wikiRoutes.js'
const invalid = () => {
  throw new ApiError('Os dados recebidos não seguem o contrato da wiki.', {
    code: 'INVALID_RESPONSE',
  })
}
const array = (value) => (Array.isArray(value) ? value : [])
export function readPage(value) {
  if (
    !value ||
    typeof value.id !== 'string' ||
    typeof value.title !== 'string' ||
    !(value.slug === '' || validSlug(value.slug))
  )
    invalid()
  const sections = array(value.sections)
  if (
    sections.some(
      (section) =>
        !validAnchor(section.id) ||
        typeof section.title !== 'string' ||
        !Array.isArray(section.blocks),
    )
  )
    invalid()
  if (new Set(sections.map((section) => section.id)).size !== sections.length) invalid()
  return {
    ...value,
    type: value.type || 'article',
    description: value.description || '',
    sections,
    toc: array(value.toc).filter((item) => validAnchor(item.id)),
    categories: array(value.categories),
    contributors: array(value.contributors),
    relatedPages: array(value.relatedPages),
    backlinks: array(value.backlinks),
    missingLinks: array(value.missingLinks),
    members: array(value.members),
    capabilities: value.capabilities || {},
  }
}
export function readSite(value) {
  if (
    !value ||
    typeof value.name !== 'string' ||
    !Array.isArray(value.navigation) ||
    !value.footer ||
    !value.collaboration
  )
    invalid()
  if (
    value.navigation.some(
      (group) =>
        !Array.isArray(group.items) ||
        group.items.some((item) => !(item.slug === '' || validSlug(item.slug))),
    )
  )
    invalid()
  return value
}
