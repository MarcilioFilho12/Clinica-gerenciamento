<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Relatório de Consultas" description="Análise detalhada das consultas médicas"
      :icon="CalendarIcon" icon-bg-color="blue" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Relatório de Consultas' }
      ]" class="mb-8">
      <template #actions>
        <div class="flex items-center space-x-4">
          <select v-model="periodoSelecionado" @change="atualizarDados"
            class="text-sm border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="hoje">Hoje</option>
            <option value="semana">Esta Semana</option>
            <option value="mes">Este Mês</option>
          </select>
          <button @click="exportarRelatorio"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center space-x-2 text-sm font-medium">
            <Download class="w-4 h-4" />
            <span>Exportar</span>
          </button>
        </div>
      </template>
    </PageHeader>
    <!-- KPIs Principais -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Total de Consultas -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <Calendar class="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total de Consultas</p>
            <p class="text-2xl font-bold text-gray-900">{{ totalConsultas }}</p>
            <p class="text-xs text-blue-600 mt-1">{{ consultasRealizadas }} realizadas</p>
          </div>
        </div>
      </div>

      <!-- Taxa de Comparecimento -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <UserCheck class="w-6 h-6 text-green-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Taxa de Comparecimento</p>
            <p class="text-2xl font-bold text-gray-900">{{ taxaComparecimento }}%</p>
            <p class="text-xs text-green-600 mt-1">{{ consultasRealizadas }}/{{ totalAgendadas }} agendadas</p>
          </div>
        </div>
      </div>

      <!-- Duração Média -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
            <Clock class="w-6 h-6 text-purple-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Duração Média</p>
            <p class="text-2xl font-bold text-gray-900">{{ duracaoMedia }}min</p>
            <p class="text-xs text-purple-600 mt-1">Por consulta</p>
          </div>
        </div>
      </div>

      <!-- Consultas Canceladas -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
            <XCircle class="w-6 h-6 text-red-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Cancelamentos</p>
            <p class="text-2xl font-bold text-gray-900">{{ consultasCanceladas }}</p>
            <p class="text-xs text-red-600 mt-1">{{ percentualCancelamentos }}% do total</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Consultas por Profissional -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Consultas por Profissional</h3>
        <div class="space-y-4">
          <div v-for="profissional in consultasPorProfissional" :key="profissional.nome"
            class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <User class="w-5 h-5 text-blue-600" />
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">{{ profissional.nome }}</p>
                <p class="text-xs text-gray-500">{{ profissional.especialidade }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-4">
              <span class="text-sm font-semibold text-gray-900">{{ profissional.consultas }}</span>
              <div class="w-24 bg-gray-200 rounded-full h-2">
                <div class="bg-blue-500 h-2 rounded-full transition-all duration-500"
                  :style="{ width: `${profissional.percentual}%` }"></div>
              </div>
              <span class="text-xs text-gray-500 w-8">{{ profissional.percentual }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Status das Consultas -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Status das Consultas</h3>
        <div class="space-y-4">
          <div v-for="status in statusConsultas" :key="status.nome"
            class="flex items-center justify-between p-4 rounded-lg" :class="status.bgClass">
            <div class="flex items-center space-x-3">
              <component :is="status.icone" :class="`w-5 h-5 ${status.iconClass}`" />
              <div>
                <p class="text-sm font-medium text-gray-900">{{ status.nome }}</p>
                <p class="text-xs text-gray-600">{{ status.descricao }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-lg font-bold text-gray-900">{{ status.quantidade }}</p>
              <p class="text-xs text-gray-600">{{ status.percentual }}%</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Consultas por Dia da Semana -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
      <h3 class="text-lg font-semibold text-gray-900 mb-6">Consultas por Dia da Semana</h3>
      <div class="grid grid-cols-7 gap-4">
        <div v-for="dia in consultasPorDia" :key="dia.nome"
          class="text-center p-4 bg-gray-50 rounded-lg hover:bg-blue-50 transition-colors">
          <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
            {{ dia.nome }}
          </div>
          <div class="text-2xl font-bold text-gray-900 mb-2">{{ dia.consultas }}</div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-500 h-2 rounded-full transition-all duration-500"
              :style="{ width: `${dia.percentual}%` }">
            </div>
          </div>
          <div class="text-xs text-gray-500 mt-2">{{ dia.percentual }}%</div>
        </div>
      </div>
    </div>

    <!-- Tabela Detalhada -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Detalhamento por Período</h3>
        <div class="flex items-center space-x-2">
          <Search class="w-4 h-4 text-gray-400" />
          <input type="text" v-model="filtroTabela" placeholder="Filtrar..."
            class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dia da Semana
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agendadas</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Realizadas</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Canceladas</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taxa</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in tabelaFiltrada" :key="item.data" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ formatarData(item.data) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ item.diaSemana }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ item.agendadas }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                {{ item.realizadas }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">
                {{ item.canceladas }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="item.taxa >= 80 ? 'bg-green-100 text-green-800' : item.taxa >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'">
                  {{ item.taxa }}%
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { CalendarIcon } from '@heroicons/vue/24/outline'
import {
  Calendar, Download, UserCheck, Clock, XCircle, User, Search,
  CheckCircle, AlertCircle
} from 'lucide-vue-next'

// ===== INTEGRAÇÃO COM AGENDA.VUE =====
// TODO: Receber dados da página Agenda.vue
// 
// import { useAgendaStore } from '@/stores/agendaStore'
// 
// const agendaStore = useAgendaStore()
// 
// const dadosConsultas = computed(() => agendaStore.getRelatorioConsultas(periodoSelecionado.value))

// Estado
const periodoSelecionado = ref('mes')
const filtroTabela = ref('')

const dadosConsultas = ref({
  totalConsultas: 0,
  consultasRealizadas: 0,
  consultasCanceladas: 0,
  totalAgendadas: 0,
  duracaoMediaMinutos: 0,
  consultasPorProfissional: [],
  consultasPorDia: [
    { nome: 'Dom', consultas: 0, percentual: 0 },
    { nome: 'Seg', consultas: 0, percentual: 0 },
    { nome: 'Ter', consultas: 0, percentual: 0 },
    { nome: 'Qua', consultas: 0, percentual: 0 },
    { nome: 'Qui', consultas: 0, percentual: 0 },
    { nome: 'Sex', consultas: 0, percentual: 0 },
    { nome: 'Sáb', consultas: 0, percentual: 0 }
  ],
  detalhamentoPorData: []
})

// Computed Properties
const totalConsultas = computed(() => dadosConsultas.value.totalConsultas)
const consultasRealizadas = computed(() => dadosConsultas.value.consultasRealizadas)
const consultasCanceladas = computed(() => dadosConsultas.value.consultasCanceladas)
const totalAgendadas = computed(() => dadosConsultas.value.totalAgendadas)
const duracaoMedia = computed(() => dadosConsultas.value.duracaoMediaMinutos)

const taxaComparecimento = computed(() => {
  if (totalAgendadas.value === 0) return 0
  return Math.round((consultasRealizadas.value / totalAgendadas.value) * 100)
})

const percentualCancelamentos = computed(() => {
  if (totalConsultas.value === 0) return 0
  return Math.round((consultasCanceladas.value / totalConsultas.value) * 100)
})

const consultasPorProfissional = computed(() => dadosConsultas.value.consultasPorProfissional)

const consultasPorDia = computed(() => dadosConsultas.value.consultasPorDia)

const statusConsultas = computed(() => [
  {
    nome: 'Realizadas',
    descricao: 'Consultas concluídas',
    quantidade: consultasRealizadas.value,
    percentual: totalConsultas.value === 0 ? 0 : Math.round((consultasRealizadas.value / totalConsultas.value) * 100),
    icone: CheckCircle,
    iconClass: 'text-green-600',
    bgClass: 'bg-green-50'
  },
  {
    nome: 'Agendadas',
    descricao: 'Aguardando atendimento',
    quantidade: totalAgendadas.value - consultasRealizadas.value - consultasCanceladas.value,
    percentual: totalConsultas.value === 0 ? 0 : Math.round(((totalAgendadas.value - consultasRealizadas.value - consultasCanceladas.value) / totalConsultas.value) * 100),
    icone: Calendar,
    iconClass: 'text-blue-600',
    bgClass: 'bg-blue-50'
  },
  {
    nome: 'Canceladas',
    descricao: 'Consultas canceladas',
    quantidade: consultasCanceladas.value,
    percentual: percentualCancelamentos.value,
    icone: XCircle,
    iconClass: 'text-red-600',
    bgClass: 'bg-red-50'
  }
])

const tabelaFiltrada = computed(() => {
  if (!filtroTabela.value) return dadosConsultas.value.detalhamentoPorData

  const filtro = filtroTabela.value.toLowerCase()
  return dadosConsultas.value.detalhamentoPorData.filter(item =>
    item.diaSemana.toLowerCase().includes(filtro) ||
    formatarData(item.data).toLowerCase().includes(filtro)
  )
})

// Funções
const formatarData = (data) => {
  return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR')
}

const atualizarDados = () => {
  // TODO: Implementar atualização real dos dados baseada no período
  console.log('Atualizando dados para período:', periodoSelecionado.value)
}

const exportarRelatorio = () => {
  const periodo = periodoSelecionado.value
  const data = new Date().toLocaleString('pt-BR')

  let conteudo = `RELATÓRIO DE CONSULTAS - ${periodo.toUpperCase()}\n`
  conteudo += `Gerado em: ${data}\n\n`

  conteudo += `=== RESUMO GERAL ===\n`
  conteudo += `Total de Consultas: ${totalConsultas.value}\n`
  conteudo += `Consultas Realizadas: ${consultasRealizadas.value}\n`
  conteudo += `Consultas Canceladas: ${consultasCanceladas.value}\n`
  conteudo += `Taxa de Comparecimento: ${taxaComparecimento.value}%\n`
  conteudo += `Duração Média: ${duracaoMedia.value} minutos\n\n`

  conteudo += `=== CONSULTAS POR PROFISSIONAL ===\n`
  consultasPorProfissional.value.forEach(prof => {
    conteudo += `${prof.nome}: ${prof.consultas} consultas (${prof.percentual}%)\n`
  })

  conteudo += `\n=== CONSULTAS POR DIA DA SEMANA ===\n`
  consultasPorDia.value.forEach(dia => {
    conteudo += `${dia.nome}: ${dia.consultas} consultas (${dia.percentual}%)\n`
  })

  conteudo += `\n=== STATUS DAS CONSULTAS ===\n`
  statusConsultas.value.forEach(status => {
    conteudo += `${status.nome}: ${status.quantidade} (${status.percentual}%)\n`
  })

  // Criar e baixar arquivo
  const elemento = document.createElement('a')
  elemento.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(conteudo)
  elemento.download = `relatorio-consultas-${periodo}-${new Date().toISOString().split('T')[0]}.txt`
  elemento.click()

  console.log('Relatório exportado:', {
    periodo,
    arquivo: `relatorio-consultas-${periodo}-${new Date().toISOString().split('T')[0]}.txt`
  })
}
</script>