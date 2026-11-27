export const templateLabels = {
  pathfinder_honor: "Especialidade de Desbravadores",
  adventurer_honor: "Especialidade de Aventureiros",
  person: "Pessoa",
};

export type TemplateType = keyof typeof templateLabels;
export type Position = {
  title: string;
  start_date: string;
  end_date: string;
  predecessor: string;
  successor: string;
};
export type TemplateMeta = Record<string, string | boolean | string[] | Position[] | undefined> & {
  achievements?: string[];
  positions?: Position[];
  is_obsolete?: boolean;
};
export type PageTemplate = { template: TemplateType; meta: TemplateMeta };

export const honorFields = {
  area_code: "Área",
  code: "Código",
  icon: "Ícone (nome da imagem)",
  level: "Nível",
  year: "Ano",
  origin_institution: "Instituição de origem",
  color_scheme: "Esquema de cores",
};
export const personFields = {
  display_name: "Nome",
  photo: "Foto (nome da imagem)",
  birth_date: "Nascimento",
  death_date: "Morte",
};
export const positionFields = {
  title: "Cargo",
  start_date: "Início",
  end_date: "Fim",
  predecessor: "Antecessor",
  successor: "Sucessor",
};

export function describeTemplate(item: PageTemplate): string {
  const meta = item.meta;
  const fields = item.template === "person" ? personFields : honorFields;
  const details = Object.entries(fields)
    .filter(([key]) => meta[key])
    .map(([key, label]) => `${label}: ${meta[key]}`);
  if (meta.is_obsolete) details.push("Especialidade obsoleta");
  for (const achievement of meta.achievements ?? []) {
    if (achievement) details.push(`Feito: ${achievement}`);
  }
  for (const position of meta.positions ?? []) {
    details.push(Object.entries(positionFields)
      .filter(([key]) => position[key as keyof Position])
      .map(([key, label]) => `${label}: ${position[key as keyof Position]}`).join(" · "));
  }
  return details.join("\n");
}
