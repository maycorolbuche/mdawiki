export interface DiffPart { value: string; added?: boolean; removed?: boolean }
export interface DiffRow { before: string | null; after: string | null; changed: boolean }
const lines = (value: string) => value.match(/[^\n]*\n|[^\n]+$/g) || []

// Pair each changed block, leaving an empty cell for insertions/deletions.
export function splitRevisionDiff(parts: DiffPart[]): DiffRow[] {
  const rows: DiffRow[] = []
  for (let i = 0; i < parts.length;) {
    const part = parts[i]!
    if (!part.added && !part.removed) {
      rows.push(...lines(part.value).map(line => ({ before: line, after: line, changed: false })))
      i++; continue
    }
    const before: string[] = [], after: string[] = []
    while (i < parts.length && (parts[i]!.added || parts[i]!.removed)) {
      const change = parts[i++]!
      ;(change.removed ? before : after).push(...lines(change.value))
    }
    for (let j = 0; j < Math.max(before.length, after.length); j++) {
      rows.push({ before: before[j] ?? null, after: after[j] ?? null, changed: true })
    }
  }
  return rows
}
