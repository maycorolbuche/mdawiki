import DOMPurify from 'isomorphic-dompurify'

// Shared policy: a browser DOM in Vite, jsdom only in the Node static build.
const tags = [
  'address', 'article', 'aside', 'footer', 'header', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hgroup', 'main', 'nav', 'section',
  'blockquote', 'dd', 'div', 'dl', 'dt', 'figcaption', 'figure', 'hr', 'li', 'menu', 'ol', 'p', 'pre', 'ul',
  'a', 'abbr', 'b', 'bdi', 'bdo', 'br', 'cite', 'code', 'data', 'dfn', 'em', 'i', 'kbd', 'mark', 'q', 'rb', 'rp', 'rt', 'rtc', 'ruby',
  's', 'samp', 'small', 'span', 'strong', 'sub', 'sup', 'time', 'u', 'var', 'wbr',
  'caption', 'col', 'colgroup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'img', 'video', 'source',
]
const globalAttributes = ['class', 'id', 'name']
const attributes: Record<string, string[]> = {
  a: ['href', 'title', 'target', 'rel'],
  img: ['src', 'alt', 'width', 'height', 'loading'],
  td: ['colspan', 'rowspan'], th: ['colspan', 'rowspan', 'scope'],
  ol: ['start', 'type'], video: ['src', 'controls', 'poster'], source: ['src', 'type'],
}
const urlAttributes = new Set(['href', 'src', 'poster'])

DOMPurify.addHook('uponSanitizeAttribute', (node, data) => {
  const tag = node.nodeName.toLowerCase()
  if (!globalAttributes.includes(data.attrName) && !attributes[tag]?.includes(data.attrName)) {
    data.keepAttr = false
    return
  }
  if (urlAttributes.has(data.attrName)) {
    const value = data.attrValue.replace(/[\u0000-\u0020\u007f-\u009f]/g, '')
    // Keep relative/anchor URLs and the previously allowed protocols only.
    if (/^[\/\\]{2}/.test(value) || value.startsWith('\\') || !/^(?:https?:|mailto:|[^:/?#]*(?:[/?#]|$))/i.test(value)) {
      data.keepAttr = false
    }
  }
})

DOMPurify.addHook('afterSanitizeAttributes', node => {
  const element = node as Element
  if (node.nodeName.toLowerCase() === 'a' && element.getAttribute('target') === '_blank') {
    element.setAttribute('rel', 'noopener noreferrer')
  }
})

export function cleanArticle(html: string): string {
  return DOMPurify.sanitize(html, {
    ALLOWED_TAGS: tags,
    ALLOWED_ATTR: [...new Set([...globalAttributes, ...Object.values(attributes).flat()])],
    ALLOW_DATA_ATTR: false,
    ALLOW_ARIA_ATTR: false,
  })
}
