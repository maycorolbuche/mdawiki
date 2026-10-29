import createDOMPurify from "dompurify";

export function allowedUrl(value, attribute, policy) {
  if (/[\u0000-\u0020\u007f]/.test(value)) return false;
  const match = value.match(/^([a-z][a-z0-9+.-]*:)/i);
  if (match) {
    const protocol = match[1].toLowerCase();
    return (
      (policy.urls.protocols.includes(protocol) &&
        /^https?:\/\//i.test(value)) ||
      (attribute === "href" && policy.urls.linkProtocols.includes(protocol))
    );
  }
  return (
    (policy.urls.rootRelative && /^\/(?!\/)/.test(value)) ||
    (attribute === "href" && policy.urls.anchors && value.startsWith("#"))
  );
}

export function sanitizeArticleHtml(html, policy) {
  if (!policy?.tags || !policy?.attributes || !policy.urls) return "";
  // Instância isolada: hooks de uma política nunca vazam para outra.
  const purifier = createDOMPurify(window);
  purifier.addHook("uponSanitizeAttribute", (node, data) => {
    const tag = node.nodeName.toLowerCase();
    if (!policy.attributes[tag]?.includes(data.attrName)) data.keepAttr = false;
    if (
      ["href", "src"].includes(data.attrName) &&
      !allowedUrl(data.attrValue, data.attrName, policy)
    )
      data.keepAttr = false;
    if (
      data.attrName === "data-media-id" &&
      (!/^[1-9]\d*$/.test(data.attrValue) ||
        !Number.isSafeInteger(Number(data.attrValue)))
    )
      data.keepAttr = false;
  });
  return purifier.sanitize(html || "", {
    ALLOWED_TAGS: policy.tags,
    ALLOWED_ATTR: [...new Set(Object.values(policy.attributes).flat())],
    ALLOW_DATA_ATTR: false,
    ALLOW_ARIA_ATTR: false,
    FORBID_TAGS: [
      "script",
      "style",
      "iframe",
      "object",
      "svg",
      "math",
      "template",
    ],
  });
}
