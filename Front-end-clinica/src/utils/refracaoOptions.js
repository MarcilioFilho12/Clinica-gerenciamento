/**
 * Opções de refração com intervalos clínicos.
 * ESF: -30..+30 (0.25) | CIL: 0..-15 (0.25) | EIXO: 0..180 | ADD: +0.25..+10 (0.25)
 */

function rangeStep(min, max, step) {
  const values = []
  const decimals = String(step).includes('.') ? String(step).split('.')[1].length : 0
  for (let v = min; v <= max + 1e-9; v += step) {
    const n = Number(v.toFixed(decimals))
    values.push(n)
  }
  return values
}

function formatSigned(n, forcePlus = false) {
  if (Object.is(n, -0) || n === 0) return forcePlus ? '+0.00' : '0.00'
  const abs = Math.abs(n).toFixed(2)
  if (n > 0) return `+${abs}`
  return `-${abs}`
}

export const esfOptions = [
  { value: '', label: '—' },
  ...rangeStep(-30, 30, 0.25).map((n) => ({
    value: formatSigned(n, true),
    label: formatSigned(n, true),
  })),
]

export const cilOptions = [
  { value: '', label: '—' },
  ...rangeStep(-15, 0, 0.25).map((n) => ({
    value: n === 0 ? '-0.00' : formatSigned(n),
    label: n === 0 ? '-0.00' : formatSigned(n),
  })),
]

export const eixoOptions = [
  { value: '', label: '—' },
  ...rangeStep(0, 180, 1).map((n) => ({
    value: String(n),
    label: `${n}°`,
  })),
]

export const addOptions = [
  { value: '', label: '—' },
  ...rangeStep(0.25, 10, 0.25).map((n) => ({
    value: formatSigned(n, true),
    label: formatSigned(n, true),
  })),
]
