export function slugify(title) {
  return title
    .normalize("NFD")
    .replace(/\p{M}/gu, "")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-|-$/g, "");
}
// Artigos ocupam somente um segmento. Segmentos de um caractere e seus filhos são do sistema.
export function articleSlug(path) {
  const parts = path.split("/").filter(Boolean);
  if (parts.length !== 1) return null;
  try {
    const slug = decodeURIComponent(parts[0]);
    return /^[a-z0-9][a-z0-9-]*$/.test(slug) && slug.length >= 2 ? slug : null;
  } catch {
    return null;
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

const titleConnectives = new Set(
  "a as o os de da das do dos e em no na nos nas por para com sem sob sobre entre ao aos".split(" "),
);

// Sugestão inicial: acentos e siglas podem ser ajustados manualmente.
export function titleFromSlug(slug) {
  return slug
    .split("-")
    .filter(Boolean)
    .map((word, index) =>
      index > 0 && titleConnectives.has(word)
        ? word
        : word.charAt(0).toLocaleUpperCase("pt-BR") + word.slice(1),
    )
    .join(" ");
}
