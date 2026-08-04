/**
 * Adapter e visual do panorama (semana/mês) — domínio consulta Marag, não "Event" genérico.
 */

import { normalizeTime, timeToMinutes, minutesToTime, parseLocalDate, formatDateISO } from './agendaDatas.js'

/** Situações de consulta (seed tenant) */
export const SITUACAO = {
  AGENDADA: 1,
  REALIZADA: 4,
  CANCELADA: 5,
  EM_ATENDIMENTO: 6,
}

export const LEGENDA_PANORAMA = [
  { key: 'agendada', label: 'Agendada', class: 'bg-blue-500' },
  { key: 'chegou', label: 'Chegou', class: 'bg-amber-500' },
  { key: 'atendimento', label: 'Em atendimento', class: 'bg-violet-500' },
  { key: 'realizada', label: 'Realizada', class: 'bg-slate-400' },
  { key: 'urgencia', label: 'Urgência', class: 'bg-red-500' },
  { key: 'pago', label: 'Pago', class: 'bg-emerald-500' },
]

/** Máx. profissionais ativos no panorama (meio-termo P3 — grade legível). */
export const MAX_PROFISSIONAIS_PANORAMA = 3

/**
 * Paleta estável por profissional (estilo calendários das referências).
 * Índice = hash do user id.
 */
export const CORES_PROFISSIONAL = [
  { soft: 'bg-indigo-100', text: 'text-indigo-950', bar: 'bg-indigo-500', dot: 'bg-indigo-500', ring: 'ring-indigo-300' },
  { soft: 'bg-emerald-100', text: 'text-emerald-950', bar: 'bg-emerald-500', dot: 'bg-emerald-500', ring: 'ring-emerald-300' },
  { soft: 'bg-amber-100', text: 'text-amber-950', bar: 'bg-amber-500', dot: 'bg-amber-500', ring: 'ring-amber-300' },
  { soft: 'bg-rose-100', text: 'text-rose-950', bar: 'bg-rose-500', dot: 'bg-rose-500', ring: 'ring-rose-300' },
  { soft: 'bg-sky-100', text: 'text-sky-950', bar: 'bg-sky-500', dot: 'bg-sky-500', ring: 'ring-sky-300' },
  { soft: 'bg-violet-100', text: 'text-violet-950', bar: 'bg-violet-500', dot: 'bg-violet-500', ring: 'ring-violet-300' },
  { soft: 'bg-teal-100', text: 'text-teal-950', bar: 'bg-teal-500', dot: 'bg-teal-500', ring: 'ring-teal-300' },
  { soft: 'bg-orange-100', text: 'text-orange-950', bar: 'bg-orange-500', dot: 'bg-orange-500', ring: 'ring-orange-300' },
]

export function corProfissionalPorId(userId) {
  const n = Number(userId)
  if (!Number.isFinite(n) || n <= 0) {
    return CORES_PROFISSIONAL[0]
  }
  return CORES_PROFISSIONAL[Math.abs(n) % CORES_PROFISSIONAL.length]
}

export function labelStatusPanorama(statusKey) {
  return LEGENDA_PANORAMA.find((i) => i.key === statusKey)?.label || 'Agendada'
}

/** Consulta sem paciente usada como bloqueio/pausa na grade. */
export function isConsultaPausa(consulta) {
  if (consulta?.paciente_id || consulta?.paciente) return false
  const proc = String(consulta?.procedimento || '').toLowerCase()
  return proc.startsWith('pausa') || proc.startsWith('bloqueio')
}

/**
 * Status visual para cor do bloco (prioridade: urgência > atendimento > realizada > chegou > agendada).
 * "pago" é badge separado, não substitui a cor principal.
 */
export function resolverStatusPanorama(consulta) {
  const situacaoId = Number(consulta?.situacao_id)
  const prioridade = consulta?.prioridade
  const chegou = !!consulta?.chegada_em

  if (prioridade === 'alta' && situacaoId !== SITUACAO.REALIZADA && situacaoId !== SITUACAO.EM_ATENDIMENTO) {
    return 'urgencia'
  }
  if (situacaoId === SITUACAO.EM_ATENDIMENTO) return 'atendimento'
  if (situacaoId === SITUACAO.REALIZADA) return 'realizada'
  if (chegou) return 'chegou'
  return 'agendada'
}

export function classesBlocoStatus(statusKey) {
  const map = {
    agendada: { bg: 'bg-blue-500', text: 'text-white', soft: 'bg-blue-100 text-blue-800 border-blue-200' },
    chegou: { bg: 'bg-amber-500', text: 'text-white', soft: 'bg-amber-100 text-amber-900 border-amber-200' },
    atendimento: { bg: 'bg-violet-500', text: 'text-white', soft: 'bg-violet-100 text-violet-900 border-violet-200' },
    realizada: { bg: 'bg-slate-400', text: 'text-white', soft: 'bg-slate-100 text-slate-700 border-slate-200' },
    urgencia: { bg: 'bg-red-500', text: 'text-white', soft: 'bg-red-100 text-red-800 border-red-200' },
  }
  return map[statusKey] || map.agendada
}

export function dataConsultaISO(consulta) {
  const raw = consulta?.data
  if (!raw) return ''
  if (typeof raw === 'string') return raw.substring(0, 10)
  return ''
}

/**
 * Consulta API → card de panorama.
 */
export function consultaToPanoramaCard(consulta) {
  const statusKey = resolverStatusPanorama(consulta)
  const inicio = normalizeTime(consulta?.horario_inicio)
  const fim = normalizeTime(consulta?.horario_fim)
  const isPausa = isConsultaPausa(consulta)
  const paciente = isPausa
    ? (consulta?.observacoes || consulta?.procedimento || 'Pausa')
    : (consulta?.paciente?.nome || 'Paciente')
  const profissional = consulta?.user?.name || consulta?.profissional?.name || ''
  const userId = consulta?.user_id ?? consulta?.user?.id ?? null
  const corProf = isPausa
    ? { soft: 'bg-slate-200', text: 'text-slate-800', bar: 'bg-slate-500', dot: 'bg-slate-500', ring: 'ring-slate-300' }
    : corProfissionalPorId(userId)

  return {
    id: consulta.id,
    data: dataConsultaISO(consulta),
    horarioInicio: inicio,
    horarioFim: fim,
    paciente,
    profissional,
    userId,
    procedimento: consulta?.procedimento || '',
    prioridade: consulta?.prioridade || 'normal',
    pago: !!consulta?.pago,
    situacaoId: consulta?.situacao_id ?? null,
    chegadaEm: consulta?.chegada_em || null,
    statusKey: isPausa ? 'pausa' : statusKey,
    statusLabel: isPausa ? 'Pausa' : labelStatusPanorama(statusKey),
    tituloCurto: `${inicio} ${paciente}`,
    classes: isPausa
      ? { bg: 'bg-slate-400', text: 'text-white', soft: 'bg-slate-100 text-slate-800 border-slate-200' }
      : classesBlocoStatus(statusKey),
    corProf,
    isPausa,
  }
}

export function agruparConsultasPorData(consultas) {
  const map = {}
  ;(consultas || []).forEach((c) => {
    const card = consultaToPanoramaCard(c)
    if (!card.data) return
    if (!map[card.data]) map[card.data] = []
    map[card.data].push(card)
  })
  Object.keys(map).forEach((dia) => {
    map[dia].sort((a, b) => timeToMinutes(a.horarioInicio) - timeToMinutes(b.horarioInicio))
  })
  return map
}

/**
 * Gera eixos de horário para a grade semana a partir da config da clínica.
 * Fallback: 08:00–18:00 / 30 min se não houver config.
 */
export function gerarEixoHorarios(configuracao) {
  const inicioStr = configuracao?.horario_inicio || '08:00'
  const fimStr = configuracao?.horario_fim || '18:00'
  const duracao = Number(configuracao?.duracao_consulta) || 30
  const intervalo = Number(configuracao?.intervalo_consulta) || 0
  const passo = Math.max(duracao + intervalo, 15)

  let inicio = timeToMinutes(inicioStr)
  let fim = timeToMinutes(fimStr)
  if (fim <= inicio) {
    inicio = timeToMinutes('08:00')
    fim = timeToMinutes('18:00')
  }

  const slots = []
  for (let m = inicio; m < fim; m += passo) {
    slots.push({
      time: minutesToTime(m),
      minutes: m,
    })
  }
  return {
    slots,
    inicioMin: inicio,
    fimMin: fim,
    passoMin: passo,
  }
}

export function buildWeekDays(rangeInicioISO) {
  const start = parseLocalDate(rangeInicioISO)
  const nomes = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']
  return Array.from({ length: 7 }, (_, i) => {
    const d = new Date(start)
    d.setDate(start.getDate() + i)
    const iso = formatDateISO(d)
    return {
      date: iso,
      label: nomes[i],
      dayNumber: d.getDate(),
      isToday: iso === formatDateISO(new Date()),
    }
  })
}

export function buildMonthCells(rangeInicioISO, consultasPorData) {
  const first = parseLocalDate(rangeInicioISO)
  const startPad = first.getDay()
  const daysInMonth = new Date(first.getFullYear(), first.getMonth() + 1, 0).getDate()
  const today = formatDateISO(new Date())
  const cells = []

  for (let i = 0; i < startPad; i++) {
    cells.push({ key: `pad-${i}`, inMonth: false, dayNumber: '', date: '', cards: [], isToday: false })
  }

  for (let day = 1; day <= daysInMonth; day++) {
    const d = new Date(first.getFullYear(), first.getMonth(), day)
    const iso = formatDateISO(d)
    const cards = consultasPorData[iso] || []
    cells.push({
      key: iso,
      inMonth: true,
      dayNumber: day,
      date: iso,
      cards,
      total: cards.length,
      isToday: iso === today,
    })
  }

  while (cells.length % 7 !== 0) {
    cells.push({
      key: `end-${cells.length}`,
      inMonth: false,
      dayNumber: '',
      date: '',
      cards: [],
      isToday: false,
    })
  }

  return cells
}

/**
 * Posição do bloco na coluna do dia (porcentagem do eixo).
 */
export function posicaoBlocoNoEixo(card, inicioMin, fimMin) {
  const span = Math.max(fimMin - inicioMin, 1)
  const start = Math.max(timeToMinutes(card.horarioInicio), inicioMin)
  let end = timeToMinutes(card.horarioFim)
  if (!card.horarioFim || end <= start) {
    end = start + 30
  }
  end = Math.min(end, fimMin)
  const top = ((start - inicioMin) / span) * 100
  const height = (Math.max(end - start, 15) / span) * 100
  return {
    topPct: Math.max(0, top),
    heightPct: Math.min(100 - top, Math.max(height, 2.5)),
  }
}
