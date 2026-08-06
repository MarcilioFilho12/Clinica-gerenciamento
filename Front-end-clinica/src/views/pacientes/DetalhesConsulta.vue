<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Detalhes da Consulta"
      description="Visualize informações completas da consulta e ficha clínica vinculada" :icon="CalendarIcon"
      icon-bg-color="blue" :show-breadcrumbs="true" :breadcrumbs="breadcrumbs" class="mb-6">
      <template #actions>
        <div class="flex gap-2">
          <BaseButton v-if="consulta && !isLoading" type="button" variant="ghost" size="sm" @click="recarregarDados"
            title="Recarregar dados">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-refresh-cw">
              <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8" />
              <path d="M21 3v5h-5" />
              <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16" />
              <path d="M8 16H3v5" />
            </svg>
          </BaseButton>
          <BaseButton type="button" variant="outline" @click="voltar">
            Voltar
          </BaseButton>
        </div>
      </template>
    </PageHeader>

    <!-- Loading State -->
    <BaseCard v-if="isLoading" padding="lg" class="text-center">
      <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Carregando dados...</h3>
      <p class="text-gray-500">Aguarde enquanto buscamos as informações</p>
    </BaseCard>

    <!-- Error State -->
    <BaseCard v-else-if="error" padding="lg" class="border-red-200 bg-red-50">
      <div class="flex items-center space-x-3 mb-4">
        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <h3 class="text-red-800 font-medium">Erro ao carregar dados</h3>
          <p class="text-red-600 text-sm mt-1">{{ error }}</p>
        </div>
      </div>
      <BaseButton type="button" variant="danger" size="sm" @click="voltar">
        Voltar
      </BaseButton>
    </BaseCard>

    <!-- Conteúdo Principal -->
    <div v-else-if="consulta" class="space-y-6">
      <!-- Tabs -->
      <BaseCard padding="none">
        <div class="border-b border-gray-200">
          <nav class="flex -mb-px" aria-label="Tabs">
            <button @click="activeTab = 'detalhes'" :class="[
              activeTab === 'detalhes'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap border-b-2 py-4 px-6 text-sm font-medium'
            ]">
              Detalhes da Consulta
            </button>
            <button @click="activeTab = 'ficha'" :class="[
              activeTab === 'ficha'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap border-b-2 py-4 px-6 text-sm font-medium'
            ]">
              Ficha Clínica
              <span v-if="consulta.tem_ficha_clinica"
                class="ml-2 inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                ✓
              </span>
            </button>
          </nav>
        </div>

        <!-- Tab Content: Detalhes da Consulta -->
        <div v-if="activeTab === 'detalhes'" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Data</label>
              <p class="text-gray-900">{{ formatDate(consulta.data) }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Horário</label>
              <p class="text-gray-900">{{ consulta.horario_inicio }} - {{ consulta.horario_fim }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Médico</label>
              <p class="text-gray-900">{{ consulta.medico?.name || consulta.medico_nome || 'N/A' }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Paciente</label>
              <p class="text-gray-900">{{ consulta.paciente?.nome || consulta.nome_paciente || 'N/A' }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Procedimento</label>
              <p class="text-gray-900">{{ consulta.procedimento || 'Consulta' }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Prioridade</label>
              <span :class="getPrioridadeBadgeClass(consulta.prioridade)"
                class="px-2 py-1 text-xs font-semibold rounded-full">
                {{ formatPrioridade(consulta.prioridade) }}
              </span>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
              <span :class="getStatusBadgeClass(consulta.situacao?.id || consulta.status_id)"
                class="px-2 py-1 text-xs font-semibold rounded-full">
                {{ formatStatus(consulta.situacao?.nome || consulta.status_nome) }}
              </span>
            </div>

            <div v-if="consulta.parceiro" class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-500 mb-1">Convênio</label>
              <p class="text-gray-900">{{ consulta.parceiro.nome || 'N/A' }}</p>
            </div>

            <div v-if="(consulta.situacao?.id === 5 || consulta.status_id === 5) && consulta.motivo_cancelamento"
              class="md:col-span-3">
              <label class="block text-sm font-medium text-gray-500 mb-1">Motivo do Cancelamento</label>
              <p class="whitespace-pre-wrap bg-red-50 border border-red-200 p-3 rounded-md text-red-800">
                {{ consulta.motivo_cancelamento }}
              </p>
            </div>

            <div v-if="consulta.observacoes" class="md:col-span-3">
              <label class="block text-sm font-medium text-gray-500 mb-1">Observações</label>
              <p class="text-gray-900 whitespace-pre-wrap bg-gray-50 p-3 rounded-md">{{ consulta.observacoes }}</p>
            </div>
          </div>

          <!-- Botões de Ação -->
          <div class="mt-6 flex gap-2">
            <BaseButton type="button" variant="ghost" @click="voltar">
              Voltar
            </BaseButton>
            <BaseButton v-if="(consulta.situacao?.id === 1 || consulta.status_id === 1)" type="button" variant="outline"
              @click="editarConsulta">
              Editar
            </BaseButton>
            <BaseButton v-if="consulta.situacao?.id === 6 || consulta.status_id === 6" type="button" variant="primary"
              @click="encerrarConsulta">
              Encerrar
            </BaseButton>
          </div>
        </div>

        <!-- Tab Content: Ficha Clínica -->
        <div v-if="activeTab === 'ficha'" class="p-6">
          <div v-if="consulta.tem_ficha_clinica && consulta.ficha_clinica">
            <!-- Preview da Ficha Clínica -->
            <div class="space-y-4">
              <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-clipboard-list mr-2 text-green-600">
                    <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                    <path d="M12 11h4" />
                    <path d="M12 16h4" />
                    <path d="M8 11h.01" />
                    <path d="M8 16h.01" />
                  </svg>
                  Preview da Ficha Clínica
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Data da Consulta</label>
                    <p class="text-gray-900">{{ formatDate(consulta.ficha_clinica.data_consulta) }}</p>
                  </div>

                  <div v-if="consulta.ficha_clinica.created_at">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Data de Criação</label>
                    <p class="text-gray-900 text-sm">{{ formatDate(consulta.ficha_clinica.created_at) }}</p>
                  </div>

                  <div v-if="consulta.ficha_clinica.observacoes" class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Observações</label>
                    <p class="text-gray-900 text-sm whitespace-pre-wrap bg-white p-3 rounded-md border border-gray-200">
                      {{
                        consulta.ficha_clinica.observacoes }}</p>
                  </div>
                </div>
              </div>

              <div class="flex gap-2">
                <BaseButton type="button" variant="primary" @click="verFichaCompleta">
                  Ver Completa
                </BaseButton>
                <BaseButton type="button" variant="outline" @click="editarFichaClinica">
                  Editar
                </BaseButton>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-12">
            <!-- Mensagem para consulta cancelada -->
            <div v-if="isConsultaCancelada" class="space-y-4">
              <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  class="lucide lucide-x-circle text-red-600">
                  <circle cx="12" cy="12" r="10" />
                  <path d="m15 9-6 6" />
                  <path d="m9 9 6 6" />
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900 mb-2">Consulta Cancelada</h3>
              <p class="text-gray-500 mb-4 max-w-md mx-auto">
                Não é possível criar uma ficha clínica para uma consulta cancelada.
                Fichas clínicas só podem ser vinculadas a consultas que foram realizadas ou estão em andamento.
              </p>
            </div>

            <!-- Mensagem normal para consulta não cancelada -->
            <div v-else class="space-y-4">
              <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  class="lucide lucide-clipboard-x text-gray-400">
                  <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                  <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                  <path d="M9 14l2 2 4-4" />
                  <path d="M9 10h6" />
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma ficha clínica vinculada</h3>
              <p class="text-gray-500 mb-4">Esta consulta ainda não possui uma ficha clínica vinculada.</p>
              <BaseButton type="button" variant="primary" @click="criarFichaClinica">
                Criar Ficha Clínica
              </BaseButton>
            </div>
          </div>
        </div>
      </BaseCard>
    </div>

    <!-- Modal de Encerramento de Consulta -->
    <ActionModal :open="showEncerrarModal" titulo="Encerrar Consulta"
      subtitulo="Confirme o encerramento da consulta. Você pode adicionar observações finais (opcional)."
      action-label="Encerrar Consulta" action-variant="blue" border-color="blue" :action-disabled="encerrandoConsulta"
      modal-width="sm:max-w-md" @acao="confirmarEncerrarConsulta" @cancel="fecharModalEncerrar">

      <div class="space-y-4">
        <div v-if="consulta" class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
          <p class="text-sm text-gray-700">
            <span class="font-medium">Paciente:</span> {{ consulta.paciente?.nome || consulta.nome_paciente || 'N/A' }}
          </p>
          <p class="text-sm text-gray-700 mt-1">
            <span class="font-medium">Data/Hora:</span> {{ formatDate(consulta.data) }} - {{ consulta.horario_inicio }}
          </p>
          <p class="text-sm text-gray-700 mt-1">
            <span class="font-medium">Médico:</span> {{ consulta.medico?.name || consulta.medico_nome || 'N/A' }}
          </p>
        </div>

        <div>
          <BaseTextarea v-model="observacoesFinais" label="Observações Finais (Opcional)"
            placeholder="Adicione observações sobre o encerramento da consulta..." :rows="4" />
          <p class="text-xs text-gray-500 mt-1">
            As observações serão adicionadas às observações da consulta.
          </p>
        </div>
      </div>
    </ActionModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { CalendarIcon } from '@heroicons/vue/24/outline'
import { toast } from 'vue3-toastify'
import axios from '../../services/axios.js'

const route = useRoute()
const router = useRouter()

const pacienteId = computed(() => route.params.idPaciente || route.params.pacienteId)
const consultaId = computed(() => route.params.consultaId || route.params.id)

const isLoading = ref(false)
const error = ref(null)
const consulta = ref(null)
const activeTab = ref('detalhes')

// Modal de encerrar consulta
const showEncerrarModal = ref(false)
const observacoesFinais = ref('')
const encerrandoConsulta = ref(false)

// Breadcrumbs dinâmicos
const breadcrumbs = computed(() => {
  const items = [
    { label: 'Início', to: '/home' },
    { label: 'Pacientes', to: '/pacientes/gerenciar' }
  ]

  if (pacienteId.value) {
    items.push({ label: 'Detalhes do Paciente', to: `/pacientes/detalhes/${pacienteId.value}` })
  }

  items.push({ label: 'Detalhes da Consulta' })

  return items
})

// Verificar se a consulta está cancelada
const isConsultaCancelada = computed(() => {
  if (!consulta.value) return false
  return consulta.value.situacao?.id === 5 || consulta.value.status_id === 5
})

// Funções auxiliares
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('pt-BR')
}

const formatPrioridade = (prioridade) => {
  if (prioridade === 'alta') return 'Alta'
  if (prioridade === 'normal') return 'Normal'
  if (prioridade === 'baixa') return 'Baixa'
  return prioridade || 'Normal'
}

// Função para formatar o nome do status
const formatStatus = (statusNome) => {
  if (!statusNome) return 'N/A'

  const statusMap = {
    'ativo': 'Agendado',
    'em_atendimento': 'Em Atendimento',
    'agendada': 'Agendado',
    'confirmada': 'Confirmada',
    'encerrado': 'Encerrada',
    'cancelado': 'Cancelada',
    'suspenso': 'Suspenso'
  }

  // Converter para lowercase para fazer a comparação case-insensitive
  const statusLower = statusNome.toLowerCase().trim()

  // Retornar o valor mapeado ou o valor original se não houver mapeamento
  return statusMap[statusLower] || statusNome
}

const getStatusBadgeClass = (statusId) => {
  const classes = {
    1: 'bg-yellow-100 text-yellow-800', // Agendada
    2: 'bg-blue-100 text-blue-800', // Confirmada
    4: 'bg-gray-100 text-gray-800', // Encerrada
    6: 'bg-green-100 text-green-800' // Em Atendimento
  }
  return classes[statusId] || 'bg-gray-100 text-gray-800'
}

const getPrioridadeBadgeClass = (prioridade) => {
  const classes = {
    alta: 'bg-orange-100 text-orange-800',
    normal: 'bg-blue-100 text-blue-800',
    baixa: 'bg-gray-100 text-gray-800'
  }
  return classes[prioridade] || 'bg-gray-100 text-gray-800'
}

// Carregar detalhes da consulta
const carregarDetalhesConsulta = async () => {
  if (!consultaId.value) {
    error.value = 'ID da consulta não fornecido'
    return
  }

  isLoading.value = true
  error.value = null
  try {
    const response = await axios.get(`/consultas/${consultaId.value}/detalhes`)

    if (response.data.success) {
      consulta.value = response.data.data
    } else {
      error.value = response.data.message || 'Erro ao carregar detalhes da consulta'
      toast.error(error.value)
    }
  } catch (err) {
    console.error('Erro ao carregar detalhes da consulta:', err)

    if (err.response?.status === 404) {
      error.value = 'Consulta não encontrada'
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Erro ao carregar detalhes da consulta. Tente novamente.'
    }

    toast.error(error.value)
  } finally {
    isLoading.value = false
  }
}

// Recarregar dados
const recarregarDados = async () => {
  await carregarDetalhesConsulta()
}

// Voltar
const voltar = () => {
  if (pacienteId.value) {
    router.push(`/pacientes/detalhes/${pacienteId.value}`)
  } else {
    router.push('/pacientes/gerenciar')
  }
}

// Editar consulta
const editarConsulta = () => {
  if (!consulta.value) {
    toast.error('Erro: Consulta não encontrada')
    return
  }

  // Verificar se a consulta pode ser editada (apenas situacao_id === 1)
  const situacaoId = consulta.value.situacao?.id || consulta.value.status_id
  if (situacaoId !== 1) {
    toast.error('Apenas consultas agendadas podem ser editadas.')
    return
  }

  // Reutiliza o modal de edição já existente na Agenda (mesma tela usada ao clicar num horário ocupado)
  router.push({ path: '/agenda', query: { editar_consulta_id: consulta.value.id } })
}

// Encerrar consulta
const encerrarConsulta = () => {
  if (!consulta.value) {
    toast.error('Erro: Consulta não encontrada')
    return
  }

  observacoesFinais.value = ''
  showEncerrarModal.value = true
}

// Confirmar encerramento de consulta
const confirmarEncerrarConsulta = async () => {
  if (!consulta.value) {
    toast.error('Erro: Consulta não encontrada')
    return
  }

  encerrandoConsulta.value = true
  try {
    const payload = {}
    if (observacoesFinais.value && observacoesFinais.value.trim()) {
      payload.observacoes_finais = observacoesFinais.value.trim()
    }

    const response = await axios.put(`/consultas/${consulta.value.id}/encerrar`, payload)

    if (response.data.success) {
      toast.success('Consulta encerrada com sucesso!')

      // Recarregar dados da consulta para atualizar o status
      await carregarDetalhesConsulta()

      // Fechar modal
      fecharModalEncerrar()
    } else {
      toast.error(response.data.message || 'Erro ao encerrar consulta')
    }
  } catch (err) {
    console.error('Erro ao encerrar consulta:', err)

    if (err.response?.data?.message) {
      toast.error(err.response.data.message)
    } else if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      Object.keys(errors).forEach(key => {
        toast.error(errors[key][0])
      })
    } else {
      toast.error('Erro ao encerrar consulta. Tente novamente.')
    }
  } finally {
    encerrandoConsulta.value = false
  }
}

// Fechar modal de encerrar consulta
const fecharModalEncerrar = () => {
  showEncerrarModal.value = false
  observacoesFinais.value = ''
}

// Criar ficha clínica
const criarFichaClinica = () => {
  if (isConsultaCancelada.value) {
    toast.error('Não é possível criar ficha clínica para uma consulta cancelada.')
    return
  }

  if (pacienteId.value && consultaId.value) {
    router.push(`/pacientes/ficha-clinica/${pacienteId.value}?consulta_id=${consultaId.value}`)
  }
}

// Ver ficha completa
const verFichaCompleta = () => {
  if (pacienteId.value && consulta.value?.ficha_clinica?.id) {
    router.push(`/pacientes/detalhes/${pacienteId.value}/ficha-clinica/${consulta.value.ficha_clinica.id}/visualizar`)
  }
}

// Editar ficha clínica
const editarFichaClinica = () => {
  if (pacienteId.value && consulta.value?.ficha_clinica?.id) {
    router.push(`/pacientes/detalhes/${pacienteId.value}/ficha-clinica/${consulta.value.ficha_clinica.id}`)
  }
}

// Lifecycle
onMounted(async () => {
  await carregarDetalhesConsulta()
})
</script>
