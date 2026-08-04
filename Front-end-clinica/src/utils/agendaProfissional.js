/**
 * Contexto de profissional na agenda.
 * Regra de produto:
 * - 1 profissional → padrão silencioso (nunca perguntar)
 * - N profissionais → exige escolha explícita do contexto
 */

export function mapProfissionalAgenda(prof) {
  if (!prof) return null
  return {
    id: prof.id,
    name: prof.name,
    specialty: prof.especialidade || prof.profile?.name || 'Sem especialidade',
    crm: prof.crm || 'CRM não informado',
    email: prof.email,
  }
}

export function mapListaProfissionais(lista) {
  if (!Array.isArray(lista)) return []
  return lista.map(mapProfissionalAgenda).filter(Boolean)
}

/** @returns {object|null} único profissional ou null se 0 ou N>1 */
export function resolverProfissionalUnico(lista) {
  const items = mapListaProfissionais(lista)
  return items.length === 1 ? items[0] : null
}

/**
 * Resolve o id do profissional para agendar / pausa / slot.
 * @param {object} opts
 * @param {Array} opts.lista - profissionais da clínica (fonte estável)
 * @param {string|number} [opts.preferido]
 * @param {string|number} [opts.selectedDoctor]
 * @param {string[]} [opts.panoramaIds]
 */
export function resolverProfissionalContexto({
  lista = [],
  preferido = '',
  selectedDoctor = '',
  panoramaIds = [],
} = {}) {
  const unico = resolverProfissionalUnico(lista)
  if (unico) return String(unico.id)

  if (preferido !== '' && preferido != null) return String(preferido)
  if (selectedDoctor !== '' && selectedDoctor != null) return String(selectedDoctor)
  if (panoramaIds?.length) return String(panoramaIds[0])
  return ''
}

export function exigeEscolhaProfissional(lista) {
  return mapListaProfissionais(lista).length > 1
}

/** Slots sugeridos livres (exclui ocupados). */
export function filtrarHorariosLivres(horarios = []) {
  return (horarios || []).filter((h) => h && h.ocupado !== true && h.disponivel !== false)
}
