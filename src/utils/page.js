export function slugify(title) {
  return title
    .normalize("NFD")
    .replace(/\p{M}/gu, "")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-|-$/g, "");
}
// A escrita da URL é independente do slug normalizado usado pela API.
export function titleFromPath(path) {
  const parts = path.split("/").filter(Boolean);
  if (parts.length !== 1) return null;
  try {
    const segment = decodeURIComponent(parts[0]);
    if (/[\/\\\u0000-\u001f\u007f]/.test(segment)) return null;
    return segment.replaceAll("-", " ").trim();
  } catch {
    return null;
  }
}
export function articleSlug(path) {
  const title = titleFromPath(path);
  if (title === null) return null;
  const slug = slugify(title);
  return slug.length >= 2 ? slug : null;
}
export function titlePathSegment(title) {
  return title
    .normalize("NFC")
    .trim()
    .replace(/[^\p{L}\p{N}\p{M}]+/gu, "-")
    .replace(/^-|-$/g, "");
}
export function articlePath(title) {
  return "/" + encodeURIComponent(titlePathSegment(title));
}
export function sameArticlePath(path, canonical) {
  try {
    return decodeURIComponent(path) === decodeURIComponent(canonical);
  } catch {
    return false;
  }
}
export function metadataPayload(fields, values) {
  const output = {};
  for (const field of fields) {
    const value = values[field.key];
    if (value === "" || value === undefined || value === null) continue;
    if (field.input === "number") {
      const number = Number(value);
      if (!Number.isSafeInteger(number))
        throw new Error(`${field.label} deve ser um número inteiro válido.`);
      output[field.key] = number;
    } else if (field.input === "tags")
      output[field.key] = Array.isArray(value)
        ? value
        : value
            .split(",")
            .map((item) => item.trim())
            .filter(Boolean);
    else output[field.key] = value;
  }
  return output;
}
export function validatePageDraft(draft, schema) {
  const title = draft.title.trim();
  if (
    title.length < schema.page.titleMinLength ||
    title.length > schema.page.titleMaxLength
  )
    return `O título deve ter entre ${schema.page.titleMinLength} e ${schema.page.titleMaxLength} caracteres.`;
  const slug = slugify(title);
  if (
    slug.length < schema.page.slugMinLength ||
    slug.length > schema.page.slugMaxLength
  )
    return "O título precisa gerar um endereço com pelo menos dois caracteres.";
  if (!schema.types.some((type) => type.value === draft.type))
    return "Selecione um tipo válido.";
  return "";
}
