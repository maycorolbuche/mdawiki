import DOMPurify from 'isomorphic-dompurify'

// Shared policy: a browser DOM in Vite, jsdom only in the Node static build.
const tags = [
  'address', 'article', 'aside', 'footer', 'header', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hgroup', 'main', 'nav', 'section',
  'blockquote', 'dd', 'div', 'dl', 'dt', 'figcaption', 'figure', 'hr', 'li', 'menu', 'ol', 'p', 'pre', 'ul',
  'a', 'abbr', 'b', 'bdi', 'bdo', 'br', 'cite', 'code', 'data', 'dfn', 'em', 'i', 'kbd', 'mark', 'q', 'rb', 'rp', 'rt', 'rtc', 'ruby',
  's', 'samp', 'small', 'span', 'strong', 'sub', 'sup', 'time', 'u', 'var', 'wbr',
  'caption', 'col', 'colgroup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'img', 'video', 'source',
  'iframe',
]
const globalAttributes = ['class', 'id', 'name', 'style']
const attributes: Record<string, string[]> = {
  a: ['href', 'title', 'target', 'rel'],
  img: ['src', 'alt', 'width', 'height', 'loading'],
  td: ['colspan', 'rowspan'], th: ['colspan', 'rowspan', 'scope'],
  ol: ['start', 'type'], video: ['src', 'controls', 'poster'], source: ['src', 'type'],
  table: ['width', 'cellpadding', 'cellspacing', 'border'],
  iframe: ['src', 'width', 'height', 'allowfullscreen', 'loading', 'title'],
}
attributes.td!.push('width', 'height', 'align', 'valign')
attributes.th!.push('width', 'height', 'align', 'valign')
const urlAttributes = new Set(['href', 'src', 'poster'])

// Preserve presentation from stored templates, without accepting arbitrary CSS.
const color = /^(?:#[\da-f]{3,8}|[a-z]+)$/i
const length = /^(?:0|auto|\d{1,4}(?:\.\d+)?(?:px|em|rem|%|pt))$/i
const safeStyles: Record<string, RegExp> = {
  color, background: color, 'background-color': color,
  width: length, height: length, 'max-width': length, 'min-width': length,
  'font-size': length, 'font-weight': /^(?:normal|bold|[1-9]00)$/,
  'font-style': /^(?:normal|italic)$/, 'text-align': /^(?:left|right|center|justify)$/,
  'vertical-align': /^(?:top|middle|bottom|baseline)$/,
  'border-collapse': /^(?:collapse|separate)$/, overflow: /^(?:auto|scroll)$/, float: /^(?:left|right|none)$/,
}
for (const name of ['padding', 'margin', 'padding-left', 'padding-right', 'padding-top', 'padding-bottom', 'margin-left', 'margin-right', 'margin-top', 'margin-bottom']) safeStyles[name] = length
for (const name of ['border', 'border-top', 'border-right', 'border-bottom', 'border-left']) safeStyles[name] = /^\d{1,2}px (?:solid|dashed|dotted) (?:#[\da-f]{3,8}|[a-z]+)$/i
function cleanStyle(value: string) {
  return value.split(';').flatMap(declaration => {
    const [property, ...parts] = declaration.split(':')
    const name = property!.trim().toLowerCase()
    const val = parts.join(':').trim()
    return safeStyles[name]?.test(val) ? [`${name}:${val}`] : []
  }).join(';')
}

DOMPurify.addHook('uponSanitizeAttribute', (node, data) => {
  const tag = node.nodeName.toLowerCase()
  if (data.attrName === 'style') {
    data.attrValue = cleanStyle(data.attrValue)
    data.keepAttr = Boolean(data.attrValue)
    return
  }
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
  if (node.nodeName.toLowerCase() === 'iframe') {
    if (!/^https:\/\/(?:www\.)?youtube(?:-nocookie)?\.com\/embed\/[\w-]{11}(?:\?[\w&=;%-]*)?$/.test(element.getAttribute('src') || '')) { element.remove(); return }
    element.setAttribute('sandbox', 'allow-scripts allow-same-origin allow-presentation')
    element.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin')
    element.setAttribute('loading', 'lazy')
    if (!element.getAttribute('title')) element.setAttribute('title', 'Vídeo do YouTube')
  }
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


