<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Consultas Atendidas" :icon="CheckCircleIcon" icon-bg-color="blue"
      description="Análise detalhada das consultas atendidas" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Consultas Atendidas' }
      ]" class="mb-8">
      <template #actions>
        <div class="text-sm text-gray-600 font-medium">
          {{ new Date().toLocaleDateString('pt-BR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
          }}
        </div>
      </template>
    </PageHeader>
    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <Users class="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Atendidas</p>
            <p class="text-2xl font-bold text-gray-900">{{ totalConsultas }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <Calendar class="w-6 h-6 text-green-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Este Mês</p>
            <p class="text-2xl font-bold text-gray-900">{{ consultasEsteMes }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
            <TrendingUp class="w-6 h-6 text-indigo-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Média Diária</p>
            <p class="text-2xl font-bold text-gray-900">{{ mediaDiaria }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
            <Star class="w-6 h-6 text-yellow-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Satisfação</p>
            <p class="text-2xl font-bold text-gray-900">98%</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Controles -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-4 flex-1">
          <!-- Busca -->
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input type="text" v-model="searchTerm" placeholder="Buscar por paciente, ID ou profissional..."
              class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" />
          </div>

          <!-- Filtro por Data -->
          <div class="relative">
            <Calendar class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input type="date" v-model="filtroData"
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" />
          </div>

          <!-- Filtro por Profissional -->
          <div class="relative">
            <UserCheck class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <select v-model="filtroProfissional"
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm appearance-none bg-white min-w-[200px]">
              <option value="">Todos os profissionais</option>
              <option v-for="prof in profissionais" :key="prof" :value="prof">{{ prof }}</option>
            </select>
          </div>
        </div>

        <!-- Ações -->
        <div class="flex space-x-3">
          <button @click="exportarDados"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center space-x-2 text-sm font-medium">
            <Download class="w-4 h-4" />
            <span>Exportar</span>
          </button>

          <button @click="imprimirRelatorio"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors flex items-center space-x-2 text-sm font-medium">
            <Printer class="w-4 h-4" />
            <span>Imprimir</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Lista de Consultas -->
    <div class="space-y-4">
      <div v-for="consulta in consultasFiltradas" :key="consulta.id"
        class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="p-6">
          <div class="flex items-start justify-between">
            <!-- Informações do Paciente -->
            <div class="flex items-start space-x-4 flex-1">
              <!-- Avatar -->
              <div class="relative">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                  <User class="w-6 h-6 text-blue-600" />
                </div>
                <div
                  class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full flex items-center justify-center border-2 border-white">
                  <Check class="w-2 h-2 text-white" />
                </div>
              </div>

              <!-- Dados do Paciente -->
              <div class="flex-1 space-y-3">
                <div class="flex items-center space-x-3">
                  <h3 class="text-lg font-semibold text-gray-900">{{ consulta.nomePaciente }}</h3>
                  <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs font-medium">
                    ID: {{ consulta.idPaciente }}
                  </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div class="flex items-center space-x-2">
                    <Calendar class="w-4 h-4 text-gray-400" />
                    <span class="text-sm text-gray-600">{{ formatarData(consulta.data) }}</span>
                  </div>

                  <div class="flex items-center space-x-2">
                    <Clock class="w-4 h-4 text-gray-400" />
                    <span class="text-sm text-gray-600">{{ consulta.horario }}</span>
                  </div>

                  <div class="flex items-center space-x-2">
                    <UserCheck class="w-4 h-4 text-gray-400" />
                    <span class="text-sm text-gray-600">{{ consulta.profissional }}</span>
                  </div>
                </div>

                <!-- Informações Adicionais -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-gray-100">
                  <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                      <Phone class="w-4 h-4 text-gray-400" />
                      <span class="text-sm text-gray-600">{{ consulta.telefone }}</span>
                    </div>

                    <div class="flex items-center space-x-2">
                      <Mail class="w-4 h-4 text-gray-400" />
                      <span class="text-sm text-gray-600">{{ consulta.email }}</span>
                    </div>
                  </div>

                  <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                      <FileText class="w-4 h-4 text-gray-400" />
                      <span class="text-sm text-gray-600">{{ consulta.tipoConsulta }}</span>
                    </div>

                    <div class="flex items-center space-x-2">
                      <DollarSign class="w-4 h-4 text-gray-400" />
                      <span class="text-sm text-gray-600">R$ {{ consulta.valor }}</span>
                    </div>
                  </div>
                </div>

                <!-- Observações -->
                <div v-if="consulta.observacoes" class="p-3 bg-gray-50 rounded-md border border-gray-200">
                  <p class="text-sm text-gray-700">
                    <span class="font-medium text-gray-900">Observações:</span> {{ consulta.observacoes }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Ações -->
            <div class="flex flex-col space-y-2 ml-4">
              <button @click="verDetalhes(consulta)"
                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors"
                title="Ver detalhes">
                <Eye class="w-4 h-4" />
              </button>

              <button @click="editarConsulta(consulta)"
                class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition-colors"
                title="Editar consulta">
                <Edit class="w-4 h-4" />
              </button>

              <button @click="gerarRecibo(consulta)"
                class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-md transition-colors"
                title="Gerar recibo">
                <FileText class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Paginação -->
    <div class="mt-6 flex items-center justify-between">
      <div class="text-sm text-gray-500">
        Mostrando {{ (paginaAtual - 1) * itensPorPagina + 1 }} a {{ Math.min(paginaAtual * itensPorPagina,
        totalConsultas)
        }} de {{ totalConsultas }} consultas
      </div>

      <div class="flex items-center space-x-2">
        <button @click="paginaAnterior" :disabled="paginaAtual === 1"
          class="px-3 py-2 border border-gray-300 text-gray-500 rounded-md hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
          <ChevronLeft class="w-4 h-4" />
        </button>

        <span class="px-3 py-2 bg-blue-600 text-white rounded-md text-sm font-medium">
          {{ paginaAtual }} de {{ totalPaginas }}
        </span>

        <button @click="proximaPagina" :disabled="paginaAtual === totalPaginas"
          class="px-3 py-2 border border-gray-300 text-gray-500 rounded-md hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
          <ChevronRight class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>

  <!-- Modal de Detalhes -->
  <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Detalhes da Consulta</h3>
          <button @click="showModal = false" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
      </div>

      <div class="p-6">
        <div v-if="consultaSelecionada" class="space-y-6">
          <!-- Informações do Paciente -->
          <div class="text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <User class="w-8 h-8 text-blue-600" />
            </div>
            <h4 class="text-xl font-semibold text-gray-900">{{ consultaSelecionada.nomePaciente }}</h4>
            <p class="text-gray-500">ID: {{ consultaSelecionada.idPaciente }}</p>
          </div>

          <!-- Detalhes da Consulta -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data e Horário</label>
                <p class="text-sm text-gray-900">{{ formatarData(consultaSelecionada.data) }} às {{
                  consultaSelecionada.horario }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Profissional</label>
                <p class="text-sm text-gray-900">{{ consultaSelecionada.profissional }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Consulta</label>
                <p class="text-sm text-gray-900">{{ consultaSelecionada.tipoConsulta }}</p>
              </div>
            </div>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <p class="text-sm text-gray-900">{{ consultaSelecionada.telefone }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                <p class="text-sm text-gray-900">{{ consultaSelecionada.email }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valor</label>
                <p class="text-sm text-gray-900">R$ {{ consultaSelecionada.valor }}</p>
              </div>
            </div>
          </div>

          <!-- Observações -->
          <div v-if="consultaSelecionada.observacoes">
            <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
            <div class="p-4 bg-gray-50 rounded-md border border-gray-200">
              <p class="text-sm text-gray-700">{{ consultaSelecionada.observacoes }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="p-6 border-t border-gray-200 bg-gray-50">
        <div class="flex justify-end space-x-3">
          <button @click="showModal = false"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
            Fechar
          </button>
          <button @click="gerarRecibo(consultaSelecionada)"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
            Gerar Recibo
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { CheckCircleIcon } from '@heroicons/vue/24/outline'
import {
  CheckCircle, Users, Calendar, TrendingUp, Star, Search, UserCheck,
  Download, Printer, User, Check, Clock, Phone, Mail, FileText,
  DollarSign, Eye, Edit, ChevronLeft, ChevronRight, X
} from 'lucide-vue-next'

// ===== INTEGRAÇÃO COM BANCO DE DADOS E API =====
// Carregar consultas atendidas da API quando disponível
// 
// Exemplo de integração:
// import { consultasService } from '@/services/consultasService'
// import { useConsultasStore } from '@/stores/consultasStore'
// 
// const consultasStore = useConsultasStore()
// 
// const carregarConsultas = async () => {
//   try {
//     loading.value = true
//     const response = await consultasService.buscarConsultasAtendidas({
//       page: paginaAtual.value,
//       limit: itensPorPagina.value,
//       search: searchTerm.value,
//       date: filtroData.value,
//       professional: filtroProfissional.value
//     })
//     consultas.value = response.data
//     totalConsultas.value = response.total
//   } catch (error) {
//     console.error('Erro ao carregar consultas:', error)
//   } finally {
//     loading.value = false
//   }
// }
//
// const exportarDados = async () => {
//   try {
//     const response = await consultasService.exportarConsultas({
//       format: 'excel', // ou 'pdf'
//       filters: {
//         search: searchTerm.value,
//         date: filtroData.value,
//         professional: filtroProfissional.value
//       }
//     })
//     // Download do arquivo
//   } catch (error) {
//     console.error('Erro ao exportar:', error)
//   }
// }

// Estado
const searchTerm = ref('')
const filtroData = ref('')
const filtroProfissional = ref('')
const showModal = ref(false)
const consultaSelecionada = ref(null)
const paginaAtual = ref(1)
const itensPorPagina = ref(10)
const loading = ref(false)

const consultas = ref([])
const profissionais = ref([])

// Computed
const consultasFiltradas = computed(() => {
  let filtered = consultas.value

  if (searchTerm.value) {
    const search = searchTerm.value.toLowerCase()
    filtered = filtered.filter(consulta =>
      consulta.nomePaciente.toLowerCase().includes(search) ||
      consulta.idPaciente.toLowerCase().includes(search) ||
      consulta.profissional.toLowerCase().includes(search)
    )
  }

  if (filtroData.value) {
    filtered = filtered.filter(consulta => consulta.data === filtroData.value)
  }

  if (filtroProfissional.value) {
    filtered = filtered.filter(consulta => consulta.profissional === filtroProfissional.value)
  }

  // Paginação
  const start = (paginaAtual.value - 1) * itensPorPagina.value
  const end = start + itensPorPagina.value
  return filtered.slice(start, end)
})

const totalConsultas = computed(() => consultas.value.length)
const consultasEsteMes = computed(() => {
  const mesAtual = new Date().getMonth()
  return consultas.value.filter(consulta =>
    new Date(consulta.data).getMonth() === mesAtual
  ).length
})
const mediaDiaria = computed(() => Math.round(totalConsultas.value / 30))
const totalPaginas = computed(() => Math.ceil(consultas.value.length / itensPorPagina.value))

// Funções
const formatarData = (data) => {
  return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const verDetalhes = (consulta) => {
  consultaSelecionada.value = consulta
  showModal.value = true
}

const editarConsulta = (consulta) => {
  // TODO: Implementar edição
  // router.push(`/consultas/editar/${consulta.id}`)
  console.log('Editar consulta:', consulta.id)
}

const gerarRecibo = (consulta) => {
  // TODO: Implementar geração de recibo
  console.log('Gerar recibo para:', consulta.id)
  showModal.value = false
}

const exportarDados = () => {
  // TODO: Implementar exportação real
  console.log('Exportando dados...')
}

const imprimirRelatorio = () => {
  // TODO: Implementar impressão
  window.print()
}

const paginaAnterior = () => {
  if (paginaAtual.value > 1) {
    paginaAtual.value--
  }
}

const proximaPagina = () => {
  if (paginaAtual.value < totalPaginas.value) {
    paginaAtual.value++
  }
}

// Lifecycle
onMounted(() => {
  // TODO: Carregar dados da API
  // carregarConsultas()
})
</script>