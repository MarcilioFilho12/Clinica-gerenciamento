import axios from '../services/axios.js'

/**
 * Fluxo médico: pré-cadastro → ficha clínica vinculada à consulta.
 */

export function urlPreCadastro(pacienteId, consultaId) {
  return `/pacientes/cadastro/${pacienteId}?consulta_id=${consultaId}&fluxo=atendimento`
}

export function urlFichaClinica(pacienteId, consultaId) {
  return `/pacientes/ficha-clinica/${pacienteId}?consulta_id=${consultaId}`
}

/** Nome, nascimento e telefone — mínimo do pré-cadastro. */
export function isPreCadastroCompleto(paciente) {
  if (!paciente) return false
  const nome = (paciente.nome || '').trim()
  const nascimento = paciente.data_nascimento || paciente.dataNascimento || ''
  const contato = (paciente.contato || paciente.telefone || '').trim()
  return Boolean(nome && nascimento && contato)
}

/**
 * Garante chegada (se ainda não) e marca em atendimento (situacao 6).
 * Idempotente o suficiente para o fluxo da UI.
 */
export async function prepararConsultaParaAtendimento(consultaId, { jaChegou = false, jaEmAtendimento = false } = {}) {
  if (!consultaId) {
    throw new Error('ID da consulta é obrigatório')
  }

  if (!jaChegou) {
    try {
      await axios.post(`/consultas/${consultaId}/confirmar-chegada`)
    } catch (error) {
      const msg = error.response?.data?.message || ''
      const jaConfirmada = error.response?.status === 400 && /já/i.test(msg)
      if (!jaConfirmada) {
        throw error
      }
    }
  }

  if (!jaEmAtendimento) {
    await axios.post(`/consultas/${consultaId}/chamar`)
  }

  return true
}
