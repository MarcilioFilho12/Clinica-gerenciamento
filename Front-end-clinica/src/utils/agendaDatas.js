/**
 * Helpers de data locais para a agenda (evita UTC em YYYY-MM-DD).
 */

export function parseLocalDate(iso) {
  if (!iso || typeof iso !== 'string') return new Date()
  const [y, m, d] = iso.split('-').map(Number)
  return new Date(y, m - 1, d)
}

export function formatDateISO(dateObj) {
  const y = dateObj.getFullYear()
  const m = String(dateObj.getMonth() + 1).padStart(2, '0')
  const d = String(dateObj.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

export function formatDateShort(iso) {
  return parseLocalDate(iso).toLocaleDateString('pt-BR')
}

export function obterDataAtualISO() {
  return formatDateISO(new Date())
}

export function normalizeTime(value) {
  if (!value) return ''
  if (typeof value === 'string') {
    if (value.includes('T')) {
      const dateObj = new Date(value)
      return dateObj.toTimeString().substring(0, 5)
    }
    return value.substring(0, 5)
  }
  return String(value).substring(0, 5)
}

/** Converte "HH:mm" em minutos desde 00:00 */
export function timeToMinutes(hhmm) {
  const t = normalizeTime(hhmm)
  if (!t || !t.includes(':')) return 0
  const [h, m] = t.split(':').map(Number)
  return (h || 0) * 60 + (m || 0)
}

export function minutesToTime(total) {
  const h = Math.floor(total / 60)
  const m = total % 60
  return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`
}
