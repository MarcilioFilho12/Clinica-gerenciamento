<template>
  <div class="max-w-5xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Visualizar Ficha Clínica" description="Detalhes completos da ficha clínica"
      :icon="ClipboardDocumentIcon" icon-bg-color="yellow" :show-breadcrumbs="true" :breadcrumbs="breadcrumbs"
      class="mb-6">
      <template #actions>
        <div class="flex gap-2">
          <BaseButton v-if="fichaClinica && !isLoading" type="button" variant="outline" @click="imprimirFicha">
            Imprimir
          </BaseButton>
          <BaseButton v-if="fichaClinica && !isLoading" type="button" variant="primary" @click="editarFicha">
            Editar Ficha
          </BaseButton>
        </div>
      </template>
    </PageHeader>

    <!-- Loading State -->
    <BaseCard v-if="isLoading" padding="lg" class="text-center">
      <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Carregando ficha clínica...</h3>
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
          <h3 class="text-red-800 font-medium">Erro ao carregar ficha clínica</h3>
          <p class="text-red-600 text-sm mt-1">{{ error }}</p>
        </div>
      </div>
      <BaseButton type="button" variant="danger" size="sm" @click="voltar">
        Voltar
      </BaseButton>
    </BaseCard>

    <!-- Conteúdo da Ficha -->
    <div v-else-if="fichaClinica" class="space-y-6">
      <!-- Informações da Consulta -->
      <BaseCard padding="lg">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-user-check mr-2 text-[#D4AF37]">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <polyline points="16 11 18 13 22 9" />
          </svg>
          Informações da Consulta
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Paciente</label>
            <p class="text-gray-900 font-medium">{{ fichaClinica.cadastro?.nome || 'Não informado' }}</p>
            <p v-if="fichaClinica.cadastro?.cpf" class="text-sm text-gray-500 mt-1">
              CPF: {{ formatCPF(fichaClinica.cadastro.cpf) }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Data da Consulta</label>
            <p class="text-gray-900 font-medium">{{ formatDate(fichaClinica.data_consulta) || 'Não informado' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Profissional</label>
            <p class="text-gray-900 font-medium">{{ fichaClinica.user?.name || 'Não informado' }}</p>
            <p v-if="fichaClinica.user?.email" class="text-sm text-gray-500 mt-1">
              {{ fichaClinica.user.email }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Data de Criação</label>
            <p class="text-gray-900 font-medium">{{ formatDateTime(fichaClinica.created_at) || 'Não informado' }}</p>
          </div>

          <div v-if="fichaClinica.observacoes" class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-500 mb-1">Observações Gerais</label>
            <p class="text-gray-900 whitespace-pre-wrap bg-gray-50 p-3 rounded-md">{{ fichaClinica.observacoes }}</p>
          </div>
        </div>
      </BaseCard>

      <!-- Anamnese -->
      <BaseCard v-if="fichaClinica.anamnese" padding="lg">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-clipboard-list mr-2 text-[#D4AF37]">
            <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
            <path d="M12 11h4" />
            <path d="M12 16h4" />
            <path d="M8 11h.01" />
            <path d="M8 16h.01" />
          </svg>
          Anamnese
        </h2>

        <div class="space-y-4">
          <div v-if="fichaClinica.anamnese.motivo_consulta">
            <label class="block text-sm font-medium text-gray-500 mb-1">Motivo da Consulta</label>
            <p class="text-gray-900 whitespace-pre-wrap bg-gray-50 p-3 rounded-md">{{
              fichaClinica.anamnese.motivo_consulta
              }}</p>
          </div>

          <div v-if="fichaClinica.anamnese.ultimo_controle" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Último Controle Optométrico</label>
              <p class="text-gray-900">{{ formatDate(fichaClinica.anamnese.ultimo_controle) }}</p>
            </div>
          </div>

          <div v-if="fichaClinica.anamnese.antecedentes_pessoais">
            <label class="block text-sm font-medium text-gray-500 mb-1">Antecedentes Pessoais</label>
            <p class="text-gray-900 whitespace-pre-wrap bg-gray-50 p-3 rounded-md">{{
              fichaClinica.anamnese.antecedentes_pessoais }}</p>
          </div>

          <div v-if="fichaClinica.anamnese.antecedentes_familiares">
            <label class="block text-sm font-medium text-gray-500 mb-1">Antecedentes Familiares</label>
            <p class="text-gray-900 whitespace-pre-wrap bg-gray-50 p-3 rounded-md">{{
              fichaClinica.anamnese.antecedentes_familiares }}</p>
          </div>
        </div>
      </BaseCard>

      <!-- Acuidade Visual -->
      <BaseCard v-if="fichaClinica.acuidades_visuais && fichaClinica.acuidades_visuais.length > 0" padding="lg">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-eye mr-2 text-[#D4AF37]">
            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
            <circle cx="12" cy="12" r="3" />
          </svg>
          Acuidade Visual
        </h2>

        <div class="overflow-x-auto">
          <table class="w-full border-collapse border border-gray-300">
            <thead>
              <tr class="bg-gray-50">
                <th class="border border-gray-300 px-3 py-2 text-left">Olho</th>
                <th class="border border-gray-300 px-3 py-2 text-center">VL</th>
                <th class="border border-gray-300 px-3 py-2 text-center">VP</th>
                <th class="border border-gray-300 px-3 py-2 text-center">PH</th>
                <th class="border border-gray-300 px-3 py-2 text-center">Observações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="acuidade in fichaClinica.acuidades_visuais" :key="acuidade.id">
                <td class="border border-gray-300 px-3 py-2 font-medium">{{ acuidade.olho.toUpperCase() }}</td>
                <td class="border border-gray-300 px-3 py-2 text-center">{{ acuidade.vl || '-' }}</td>
                <td class="border border-gray-300 px-3 py-2 text-center">{{ acuidade.vp || '-' }}</td>
                <td class="border border-gray-300 px-3 py-2 text-center">{{ acuidade.ph || '-' }}</td>
                <td class="border border-gray-300 px-3 py-2">{{ acuidade.observacoes || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </BaseCard>

      <!-- Refração -->
      <BaseCard v-if="fichaClinica.refracoes && fichaClinica.refracoes.length > 0" padding="lg">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-glasses mr-2 text-[#D4AF37]">
            <circle cx="6" cy="15" r="4" />
            <circle cx="18" cy="15" r="4" />
            <path d="M14 15a2 2 0 0 0-2-2 2 2 0 0 0-2 2" />
            <path d="M2.5 13 5 7c.7-1.3 1.4-2 3-2" />
            <path d="M21.5 13 19 7c-.7-1.3-1.5-2-3-2" />
          </svg>
          Refração
        </h2>

        <div class="space-y-6">
          <div v-for="tipo in tiposRefracao" :key="tipo">
            <h3 class="text-lg font-medium text-gray-800 mb-3">{{ tipo }}</h3>
            <div class="overflow-x-auto">
              <table class="w-full border-collapse border border-gray-300">
                <thead>
                  <tr class="bg-gray-50">
                    <th class="border border-gray-300 px-3 py-2 text-left">Olho</th>
                    <th class="border border-gray-300 px-3 py-2 text-center">ESF</th>
                    <th class="border border-gray-300 px-3 py-2 text-center">CIL</th>
                    <th class="border border-gray-300 px-3 py-2 text-center">EIXO</th>
                    <th class="border border-gray-300 px-3 py-2 text-center">ADD</th>
                    <th class="border border-gray-300 px-3 py-2 text-center">AV</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="refracao in getRefracoesPorTipo(tipo)" :key="refracao.id">
                    <td class="border border-gray-300 px-3 py-2 font-medium">{{ refracao.olho.toUpperCase() }}</td>
                    <td class="border border-gray-300 px-3 py-2 text-center">{{ refracao.esf || '-' }}</td>
                    <td class="border border-gray-300 px-3 py-2 text-center">{{ refracao.cil || '-' }}</td>
                    <td class="border border-gray-300 px-3 py-2 text-center">{{ refracao.eixo || '-' }}</td>
                    <td class="border border-gray-300 px-3 py-2 text-center">{{ refracao.add || '-' }}</td>
                    <td class="border border-gray-300 px-3 py-2 text-center">{{ refracao.av || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </BaseCard>

      <!-- Biomicroscopia -->
      <BaseCard v-if="fichaClinica.biomicroscopias && fichaClinica.biomicroscopias.length > 0" padding="lg">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-microscope mr-2 text-[#D4AF37]">
            <path d="M6 18h8" />
            <path d="M3 22h18" />
            <path d="M14 22a7 7 0 1 0 0-14h-1" />
            <path d="M9 14h2" />
            <path d="M9 12a2 2 0 0 1-2-2V6h6v4a2 2 0 0 1-2 2Z" />
            <path d="M12 6V3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3" />
          </svg>
          Biomicroscopia
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-for="bio in fichaClinica.biomicroscopias" :key="bio.id">
            <h3 class="text-lg font-medium text-gray-800 mb-3">Olho {{ bio.olho.toUpperCase() }}</h3>
            <div class="space-y-3">
              <div v-if="bio.cornea">
                <label class="block text-sm font-medium text-gray-500 mb-1">Córnea</label>
                <p class="text-gray-900 bg-gray-50 p-2 rounded-md">{{ bio.cornea }}</p>
              </div>
              <div v-if="bio.iris">
                <label class="block text-sm font-medium text-gray-500 mb-1">Íris</label>
                <p class="text-gray-900 bg-gray-50 p-2 rounded-md">{{ bio.iris }}</p>
              </div>
              <div v-if="bio.conjuntiva">
                <label class="block text-sm font-medium text-gray-500 mb-1">Conjuntiva</label>
                <p class="text-gray-900 bg-gray-50 p-2 rounded-md">{{ bio.conjuntiva }}</p>
              </div>
              <div v-if="bio.cristalino">
                <label class="block text-sm font-medium text-gray-500 mb-1">Cristalino</label>
                <p class="text-gray-900 bg-gray-50 p-2 rounded-md">{{ bio.cristalino }}</p>
              </div>
              <div v-if="bio.pupilas">
                <label class="block text-sm font-medium text-gray-500 mb-1">Pupilas</label>
                <p class="text-gray-900 bg-gray-50 p-2 rounded-md">{{ bio.pupilas }}</p>
              </div>
            </div>
          </div>
        </div>
      </BaseCard>

      <!-- Prescrição -->
      <BaseCard v-if="fichaClinica.prescricao" padding="lg">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-file-text mr-2 text-[#D4AF37]">
            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
            <path d="M10 9H8" />
            <path d="M16 13H8" />
            <path d="M16 17H8" />
          </svg>
          Prescrição e Conduta
        </h2>

        <div class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div v-if="fichaClinica.prescricao.material">
              <label class="block text-sm font-medium text-gray-500 mb-1">Material</label>
              <p class="text-gray-900 bg-gray-50 p-2 rounded-md">{{ fichaClinica.prescricao.material }}</p>
            </div>

            <div v-if="fichaClinica.prescricao.tipo_lente">
              <label class="block text-sm font-medium text-gray-500 mb-1">Tipo de Lente</label>
              <p class="text-gray-900 bg-gray-50 p-2 rounded-md">{{ fichaClinica.prescricao.tipo_lente }}</p>
            </div>

            <div v-if="fichaClinica.prescricao.filtro">
              <label class="block text-sm font-medium text-gray-500 mb-1">Filtro</label>
              <p class="text-gray-900 bg-gray-50 p-2 rounded-md">{{ fichaClinica.prescricao.filtro }}</p>
            </div>
          </div>

          <div v-if="fichaClinica.prescricao.diagnostico">
            <label class="block text-sm font-medium text-gray-500 mb-1">Diagnóstico</label>
            <p class="text-gray-900 whitespace-pre-wrap bg-gray-50 p-3 rounded-md">{{
              fichaClinica.prescricao.diagnostico }}
            </p>
          </div>

          <div v-if="fichaClinica.prescricao.conduta">
            <label class="block text-sm font-medium text-gray-500 mb-1">Conduta</label>
            <p class="text-gray-900 whitespace-pre-wrap bg-gray-50 p-3 rounded-md">{{ fichaClinica.prescricao.conduta }}
            </p>
          </div>

          <div v-if="fichaClinica.prescricao.encaminhamento">
            <label class="block text-sm font-medium text-gray-500 mb-1">Encaminhamento</label>
            <p class="text-gray-900 whitespace-pre-wrap bg-gray-50 p-3 rounded-md">{{ fichaClinica.prescricao.encaminhamento }}
            </p>
          </div>

          <div v-if="fichaClinica.prescricao.proximo_controle">
            <label class="block text-sm font-medium text-gray-500 mb-1">Próxima Consulta</label>
            <p class="text-gray-900 bg-gray-50 p-2 rounded-md">{{ formatDate(fichaClinica.prescricao.proximo_controle)
              }}
            </p>
          </div>
        </div>
      </BaseCard>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ClipboardDocumentIcon } from '@heroicons/vue/24/outline'
import { toast } from 'vue3-toastify'
import axios from '../../services/axios.js'

const route = useRoute()
const router = useRouter()

const fichaClinicaId = computed(() => route.params.idFichaClinica || route.params.fichaClinicaId || route.params.id)
const idPaciente = computed(() => route.params.idPaciente)
const isLoading = ref(false)
const error = ref(null)
const fichaClinica = ref(null)

// Breadcrumbs dinâmicos
const breadcrumbs = computed(() => {
  const items = [
    { label: 'Início', to: '/home' },
    { label: 'Pacientes', to: '/pacientes/gerenciar' }
  ]

  if (fichaClinica.value?.cadastro) {
    items.push({
      label: fichaClinica.value.cadastro.nome,
      to: `/pacientes/detalhes/${fichaClinica.value.cadastro.id}`
    })
  }

  items.push({ label: 'Visualizar Ficha Clínica' })
  return items
})

// Funções auxiliares
const formatCPF = (cpf) => {
  if (!cpf) return null
  return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
}

const formatDate = (date) => {
  if (!date) return null
  return new Date(date).toLocaleDateString('pt-BR')
}

const formatDateTime = (date) => {
  if (!date) return null
  return new Date(date).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const tiposRefracao = computed(() => {
  if (!fichaClinica.value?.refracoes) return []
  const tipos = [...new Set(fichaClinica.value.refracoes.map(r => r.tipo))]
  return tipos.map(tipo => {
    if (tipo === 'autorrefacao') return 'Autorrefração'
    if (tipo === 'subjetiva') return 'Refração Subjetiva'
    return tipo
  })
})

const getRefracoesPorTipo = (tipoLabel) => {
  if (!fichaClinica.value?.refracoes) return []

  let tipo = tipoLabel.toLowerCase()
  if (tipo === 'autorrefração') tipo = 'autorrefacao'
  if (tipo === 'refração subjetiva') tipo = 'subjetiva'

  return fichaClinica.value.refracoes.filter(r => r.tipo === tipo)
}

// Carregar dados da ficha clínica
const loadFichaClinicaData = async (id) => {
  isLoading.value = true
  error.value = null
  try {
    const response = await axios.get(`/fichas-clinicas/${id}`)

    if (response.data.success) {
      fichaClinica.value = response.data.data
    } else {
      error.value = response.data.message || 'Erro ao carregar ficha clínica'
      toast.error(error.value)
    }
  } catch (err) {
    console.error('Erro ao carregar ficha clínica:', err)

    if (err.response?.status === 404) {
      error.value = 'Ficha clínica não encontrada'
    } else if (err.response?.status === 403) {
      error.value = 'Você não tem permissão para visualizar esta ficha clínica'
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Erro ao carregar ficha clínica. Tente novamente.'
    }

    toast.error(error.value)
  } finally {
    isLoading.value = false
  }
}

// Ações
const editarFicha = () => {
  const pacienteId = idPaciente.value || fichaClinica.value?.cadastro?.id
  if (pacienteId) {
    router.push(`/pacientes/detalhes/${pacienteId}/ficha-clinica/${fichaClinicaId.value}`)
  } else {
    router.push(`/pacientes/gerenciar`)
  }
}

const voltar = () => {
  if (fichaClinica.value?.cadastro?.id) {
    router.push(`/pacientes/detalhes/${fichaClinica.value.cadastro.id}`)
  } else {
    router.push('/pacientes/gerenciar')
  }
}

const imprimirFicha = () => {
  if (fichaClinicaId.value) {
    const url = `/imprimir-ficha-clinica/${fichaClinicaId.value}`
    window.open(url, '_blank')
  }
}

// Lifecycle
onMounted(async () => {
  if (fichaClinicaId.value) {
    await loadFichaClinicaData(fichaClinicaId.value)
  } else {
    error.value = 'ID da ficha clínica não fornecido'
    isLoading.value = false
  }
})
</script>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }

  .container {
    max-width: none !important;
    padding: 0 !important;
  }

  .shadow-sm {
    box-shadow: none !important;
  }

  .bg-gray-50 {
    background-color: white !important;
  }
}
</style>
