import sanitizeHtml from 'sanitize-html'

export function cleanArticle(html: string): string {
  return sanitizeHtml(html, {
    allowedTags: [...sanitizeHtml.defaults.allowedTags, 'img', 'figure', 'figcaption', 'video', 'source'],
    allowedAttributes: {
      '*': ['class', 'id', 'name'],
      a: ['href', 'title', 'target', 'rel'],
      img: ['src', 'alt', 'width', 'height', 'loading'],
      td: ['colspan', 'rowspan'], th: ['colspan', 'rowspan', 'scope'],
      ol: ['start', 'type'], video: ['src', 'controls', 'poster'], source: ['src', 'type'],
    },
    allowedSchemes: ['http', 'https', 'mailto'],
    allowProtocolRelative: false,
    transformTags: {
      a: (_tag, attributes) => ({ tagName: 'a', attribs: { ...attributes, ...(attributes.target === '_blank' ? { rel: 'noopener noreferrer' } : {}) } }),
    },
  })
}
