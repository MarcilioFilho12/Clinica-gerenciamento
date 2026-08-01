<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Dashboard" :description="`Visão geral da clínica - ${dataAtual}`" :icon="ChartBarIcon"
      icon-bg-color="blue" class="mb-8">
      <template #actions>
        <div class="flex items-center space-x-3">
          <select v-model="periodoSelecionado" @change="atualizarDados"
            class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
            <option value="hoje">Hoje</option>
            <option value="semana">Esta Semana</option>
            <option value="mes">Este Mês</option>
            <option value="trimestre">Trimestre</option>
          </select>
          <button @click="atualizarDados" :disabled="carregando"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium">
            <svg v-if="carregando" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none"
              viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
            {{ carregando ? 'Atualizando...' : 'Atualizar' }}
          </button>
        </div>
      </template>
    </PageHeader>

    <!-- Toast de Notificação -->
    <div v-if="toast.show" :class="[
      'fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300',
      toast.type === 'success' ? 'bg-green-500 text-white' :
        toast.type === 'warning' ? 'bg-yellow-500 text-white' : 'bg-red-500 text-white'
    ]">
      <div class="flex items-center">
        <component :is="getToastIcon(toast.type)" class="w-5 h-5 mr-2" />
        {{ toast.message }}
      </div>
    </div>

    <!-- Alertas Importantes -->
    <div v-if="alertas.length > 0" class="mb-6">
      <div v-for="alerta in alertas" :key="alerta.id" :class="[
        'p-4 rounded-lg border-l-4 mb-3',
        alerta.tipo === 'urgente' ? 'bg-red-50 border-red-400' :
          alerta.tipo === 'atencao' ? 'bg-yellow-50 border-yellow-400' :
            'bg-blue-50 border-blue-400'
      ]">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <component :is="getAlertIcon(alerta.tipo)" :class="[
              'w-5 h-5 mr-3',
              alerta.tipo === 'urgente' ? 'text-red-600' :
                alerta.tipo === 'atencao' ? 'text-yellow-600' :
                  'text-blue-600'
            ]" />
            <div>
              <h4 :class="[
                'font-medium',
                alerta.tipo === 'urgente' ? 'text-red-800' :
                  alerta.tipo === 'atencao' ? 'text-yellow-800' :
                    'text-blue-800'
              ]">{{ alerta.titulo }}</h4>
              <p :class="[
                'text-sm mt-1',
                alerta.tipo === 'urgente' ? 'text-red-700' :
                  alerta.tipo === 'atencao' ? 'text-yellow-700' :
                    'text-blue-700'
              ]">{{ alerta.mensagem }}</p>
            </div>
          </div>
          <button @click="dispensarAlerta(alerta.id)" :class="[
            'p-1 rounded hover:bg-opacity-20',
            alerta.tipo === 'urgente' ? 'text-red-600 hover:bg-red-600' :
              alerta.tipo === 'atencao' ? 'text-yellow-600 hover:bg-yellow-600' :
                'text-blue-600 hover:bg-blue-600'
          ]">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Cards de Métricas Principais -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="metrica in metricas" :key="metrica.id"
        class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div :class="['w-8 h-8 rounded-md flex items-center justify-center', metrica.cor]">
                <component :is="metrica.icone" class="w-5 h-5 text-white" />
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">{{ metrica.titulo }}</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">{{ metrica.valor }}</div>
                  <div v-if="metrica.variacao" :class="[
                    'ml-2 flex items-baseline text-sm font-semibold',
                    metrica.variacao > 0 ? 'text-green-600' : 'text-red-600'
                  ]">
                    <svg v-if="metrica.variacao > 0" class="self-center flex-shrink-0 h-4 w-4 text-green-500"
                      fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414 6.707 9.707a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="self-center flex-shrink-0 h-4 w-4 text-red-500" fill="currentColor"
                      viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                    </svg>
                    <span class="sr-only">{{ metrica.variacao > 0 ? 'Increased' : 'Decreased' }} by</span>
                    {{ Math.abs(metrica.variacao) }}%
                  </div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
          <div class="text-sm">
            <span class="font-medium text-gray-500">{{ metrica.descricao }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Grid Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Coluna Esquerda (2/3) -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Gráfico de Consultas -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Consultas por Dia</h3>
            <p class="text-sm text-gray-500">Últimos 7 dias</p>
          </div>
          <div class="p-6">
            <div class="h-64 flex items-end justify-between space-x-2">
              <div v-for="(dia, index) in dadosGrafico" :key="index" class="flex flex-col items-center flex-1">
                <div class="w-full bg-gray-200 rounded-t-md relative overflow-hidden" style="height: 200px;">
                  <div
                    :class="['absolute bottom-0 w-full rounded-t-md transition-all duration-1000 ease-out', getCorBarra(dia.valor)]"
                    :style="{ height: `${(dia.valor / maxConsultas) * 100}%` }"></div>
                  <div class="absolute top-2 left-1/2 transform -translate-x-1/2 text-xs font-medium text-gray-600">
                    {{ dia.valor }}
                  </div>
                </div>
                <div class="mt-2 text-xs text-gray-500 text-center">
                  <div class="font-medium">{{ dia.dia }}</div>
                  <div>{{ dia.data }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Atividades Recentes -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Atividades Recentes</h3>
              <button @click="carregarAtividades" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                Ver todas
              </button>
            </div>
          </div>
          <div class="divide-y divide-gray-200">
            <div v-for="atividade in atividades" :key="atividade.id" class="px-6 py-4 hover:bg-gray-50">
              <div class="flex items-center space-x-3">
                <div :class="['flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center', atividade.cor]">
                  <component :is="atividade.icone" class="w-4 h-4 text-white" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900">{{ atividade.titulo }}</p>
                  <p class="text-sm text-gray-500">{{ atividade.descricao }}</p>
                </div>
                <div class="flex-shrink-0 text-sm text-gray-500">
                  {{ atividade.tempo }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Coluna Direita (1/3) -->
      <div class="space-y-8">
        <!-- Agenda do Dia -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Agenda de Hoje</h3>
            <p class="text-sm text-gray-500">{{ agendaHoje.length }} consultas agendadas</p>
          </div>
          <div class="divide-y divide-gray-200 max-h-80 overflow-y-auto">
            <div v-for="consulta in agendaHoje" :key="consulta.id" class="px-6 py-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div :class="['w-3 h-3 rounded-full', getStatusConsulta(consulta.status)]"></div>
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ consulta.paciente }}</p>
                    <p class="text-xs text-gray-500">{{ consulta.tipo }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-sm font-medium text-gray-900">{{ consulta.horario }}</p>
                  <p class="text-xs text-gray-500">{{ consulta.profissional }}</p>
                </div>
              </div>
            </div>

            <div v-if="agendaHoje.length === 0" class="px-6 py-8 text-center">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3a2 2 0 012-2h2a2 2 0 012 2v4m-6 4v10a2 2 0 002 2h8a2 2 0 002-2V11M8 7h8m-8 0H6a2 2 0 00-2 2v8a2 2 0 002 2h2m8-12h2a2 2 0 012 2v8a2 2 0 01-2 2h-2" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma consulta hoje</h3>
              <p class="mt-1 text-sm text-gray-500">Que tal aproveitar para organizar a agenda?</p>
            </div>
          </div>
        </div>

        <!-- Estatísticas Rápidas -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Estatísticas Rápidas</h3>
          </div>
          <div class="p-6 space-y-4">
            <div v-for="stat in estatisticasRapidas" :key="stat.id" class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div :class="['w-6 h-6 rounded-md flex items-center justify-center', stat.cor]">
                  <component :is="stat.icone" class="w-3 h-3 text-white" />
                </div>
                <span class="text-sm font-medium text-gray-900">{{ stat.label }}</span>
              </div>
              <span class="text-sm font-semibold text-gray-900">{{ stat.valor }}</span>
            </div>
          </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Ações Rápidas</h3>
          </div>
          <div class="p-6 space-y-3">
            <button v-for="acao in acoesRapidas" :key="acao.id" @click="executarAcao(acao.id)"
              class="w-full flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-colors duration-200">
              <div class="flex items-center space-x-3">
                <div :class="['w-8 h-8 rounded-lg flex items-center justify-center', acao.cor]">
                  <component :is="acao.icone" class="w-4 h-4 text-white" />
                </div>
                <div class="text-left">
                  <p class="text-sm font-medium text-gray-900">{{ acao.titulo }}</p>
                  <p class="text-xs text-gray-500">{{ acao.descricao }}</p>
                </div>
              </div>
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Status do Sistema -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Status do Sistema</h3>
          </div>
          <div class="p-6 space-y-4">
            <div v-for="status in statusSistema" :key="status.id" class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div :class="['w-3 h-3 rounded-full', status.online ? 'bg-green-400' : 'bg-red-400']"></div>
                <span class="text-sm font-medium text-gray-900">{{ status.servico }}</span>
              </div>
              <span :class="[
                'text-xs font-medium px-2 py-1 rounded-full',
                status.online ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
              ]">
                {{ status.online ? 'Online' : 'Offline' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ChartBarIcon } from '@heroicons/vue/24/outline'

// ===== ÍCONES COMO COMPONENTES =====
const CalendarIcon = {
  template: `<svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
  </svg>`
}

const UserGroupIcon = {
  template: `<svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
  </svg>`
}

const CashIcon = {
  template: `<svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
  </svg>`
}

const ClipboardListIcon = {
  template: `<svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a2 2 0 002 2h2a2 2 0 002-2V3a2 2 0 012 2v6h-3a3 3 0 00-3 3v3H6a2 2 0 01-2-2V5zm8 8a1 1 0 00-1 1v3a1 1 0 002 0v-1l.293.293a1 1 0 001.414-1.414L12.414 13H14a1 1 0 100-2h-2z" clip-rule="evenodd" />
  </svg>`
}

const CheckCircleIcon = {
  template: `<svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
  </svg>`
}

const ExclamationIcon = {
  template: `<svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
  </svg>`
}

const InformationCircleIcon = {
  template: `<svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
  </svg>`
}

const PlusIcon = {
  template: `<svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
  </svg>`
}

const DocumentAddIcon = {
  template: `<svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V8z" clip-rule="evenodd" />
  </svg>`
}

const SearchIcon = {
  template: `<svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
  </svg>`
}

const CogIcon = {
  template: `<svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
  </svg>`
}

// ===== ESTADO REATIVO =====
const carregando = ref(false)
const periodoSelecionado = ref('hoje')
const toast = ref({
  show: false,
  message: '',
  type: 'success'
})

// Dados reativos
const metricas = ref([])
const dadosGrafico = ref([])
const atividades = ref([])
const agendaHoje = ref([])
const estatisticasRapidas = ref([])
const alertas = ref([])
const statusSistema = ref([])

// ===== COMPUTED PROPERTIES =====
const dataAtual = computed(() => {
  return new Date().toLocaleDateString('pt-BR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const maxConsultas = computed(() => {
  return Math.max(...dadosGrafico.value.map(d => d.valor), 1)
})

// ===== CONFIGURAÇÕES =====
const acoesRapidas = [
  {
    id: 'nova-consulta',
    titulo: 'Nova Consulta',
    descricao: 'Agendar nova consulta',
    icone: PlusIcon,
    cor: 'bg-blue-500'
  },
  {
    id: 'novo-paciente',
    titulo: 'Novo Paciente',
    descricao: 'Cadastrar paciente',
    icone: UserGroupIcon,
    cor: 'bg-green-500'
  },
  {
    id: 'buscar-paciente',
    titulo: 'Buscar Paciente',
    descricao: 'Localizar paciente',
    icone: SearchIcon,
    cor: 'bg-purple-500'
  },
  {
    id: 'relatorio',
    titulo: 'Relatórios',
    descricao: 'Gerar relatórios',
    icone: DocumentAddIcon,
    cor: 'bg-orange-500'
  }
]

// ===== MÉTODOS UTILITÁRIOS =====
const showToast = (message, type = 'success') => {
  toast.value = {
    show: true,
    message,
    type
  }

  setTimeout(() => {
    toast.value.show = false
  }, 4000)
}

const getToastIcon = (type) => {
  const icons = {
    success: CheckCircleIcon,
    warning: ExclamationIcon,
    error: ExclamationIcon,
    info: InformationCircleIcon
  }
  return icons[type] || InformationCircleIcon
}

const getAlertIcon = (tipo) => {
  const icons = {
    urgente: ExclamationIcon,
    atencao: ExclamationIcon,
    info: InformationCircleIcon
  }
  return icons[tipo] || InformationCircleIcon
}

const getCorBarra = (valor) => {
  if (valor >= 15) return 'bg-green-500'
  if (valor >= 10) return 'bg-yellow-500'
  if (valor >= 5) return 'bg-orange-500'
  return 'bg-red-500'
}

const getStatusConsulta = (status) => {
  const cores = {
    agendada: 'bg-blue-400',
    confirmada: 'bg-green-400',
    em_andamento: 'bg-yellow-400',
    concluida: 'bg-gray-400',
    cancelada: 'bg-red-400'
  }
  return cores[status] || 'bg-gray-400'
}

// ===== DADOS VAZIOS (sem mocks) =====
const metricasVazias = () => [
  {
    id: 'consultas-hoje',
    titulo: 'Consultas Hoje',
    valor: 0,
    variacao: null,
    descricao: 'vs. ontem',
    icone: CalendarIcon,
    cor: 'bg-blue-500'
  },
  {
    id: 'pacientes-mes',
    titulo: 'Pacientes Este Mês',
    valor: 0,
    variacao: null,
    descricao: 'vs. mês anterior',
    icone: UserGroupIcon,
    cor: 'bg-green-500'
  },
  {
    id: 'receita-mes',
    titulo: 'Receita do Mês',
    valor: 'R$ 0',
    variacao: null,
    descricao: 'vs. mês anterior',
    icone: CashIcon,
    cor: 'bg-purple-500'
  },
  {
    id: 'taxa-ocupacao',
    titulo: 'Taxa de Ocupação',
    valor: '0%',
    variacao: null,
    descricao: 'da agenda',
    icone: ClipboardListIcon,
    cor: 'bg-orange-500'
  }
]

const estatisticasRapidasVazias = () => [
  {
    id: 'usuarios-online',
    label: 'Usuários Online',
    valor: '0',
    icone: UserGroupIcon,
    cor: 'bg-green-500'
  },
  {
    id: 'consultas-pendentes',
    label: 'Consultas Pendentes',
    valor: '0',
    icone: ClipboardListIcon,
    cor: 'bg-yellow-500'
  },
  {
    id: 'lembretes-enviados',
    label: 'Lembretes Enviados',
    valor: '0',
    icone: InformationCircleIcon,
    cor: 'bg-blue-500'
  },
  {
    id: 'receita-dia',
    label: 'Receita do Dia',
    valor: 'R$ 0',
    icone: CashIcon,
    cor: 'bg-purple-500'
  }
]

// ===== HANDLERS DOS EVENTOS =====
const atualizarDados = async () => {
  carregando.value = true

  try {
    metricas.value = metricasVazias()
    dadosGrafico.value = []
    atividades.value = []
    agendaHoje.value = []
    estatisticasRapidas.value = estatisticasRapidasVazias()
    alertas.value = []
    statusSistema.value = []
  } catch (error) {
    console.error('Erro ao atualizar dashboard:', error)
    showToast('Erro ao atualizar dados', 'error')
  } finally {
    carregando.value = false
  }
}

const executarAcao = (acaoId) => {
  const acoes = {
    'nova-consulta': () => {
      showToast('Redirecionando para nova consulta...', 'info')
      // Aqui você redirecionaria para a página de nova consulta
    },
    'novo-paciente': () => {
      showToast('Redirecionando para cadastro de paciente...', 'info')
      // Aqui você redirecionaria para a página de cadastro
    },
    'buscar-paciente': () => {
      showToast('Abrindo busca de pacientes...', 'info')
      // Aqui você abriria um modal de busca
    },
    'relatorio': () => {
      showToast('Redirecionando para relatórios...', 'info')
      // Aqui você redirecionaria para a página de relatórios
    }
  }

  const acao = acoes[acaoId]
  if (acao) {
    acao()
  }
}

const carregarAtividades = () => {
  showToast('Carregando todas as atividades...', 'info')
  // Aqui você redirecionaria para uma página com todas as atividades
}

const dispensarAlerta = (alertaId) => {
  alertas.value = alertas.value.filter(alerta => alerta.id !== alertaId)
  showToast('Alerta dispensado')
}

// ===== LIFECYCLE HOOKS =====
onMounted(() => {
  atualizarDados()
})
</script>

<style scoped>
/* Animações para as mensagens de feedback */
.fixed {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }

  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Hover effects */
button:hover {
  transform: translateY(-1px);
}

/* Focus visible para acessibilidade */
button:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Transições suaves */
.transition-all {
  transition: all 0.2s ease-in-out;
}

.transition-colors {
  transition: color 0.2s ease-in-out, background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
}

.transition-shadow {
  transition: box-shadow 0.2s ease-in-out;
}

/* Estados de hover para cards */
.hover\:shadow-md:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Loading spinner personalizado */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

/* Scrollbar customization */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Animações para as barras do gráfico */
.transition-all {
  transition: height 1s ease-out;
}

/* Gradientes personalizados */
.bg-gradient-to-r {
  background: linear-gradient(to right, var(--tw-gradient-stops));
}

/* Estados de hover para atividades */
.hover\:bg-gray-50:hover {
  background-color: #f9fafb;
}

/* Responsividade para gráfico */
@media (max-width: 768px) {
  .h-64 {
    height: 12rem;
  }
}

/* Estilo para alertas */
.border-l-4 {
  border-left-width: 4px;
}

/* Animação de entrada para novos elementos */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeInUp {
  animation: fadeInUp 0.3s ease-out;
}

/* Estilo para status online/offline */
.bg-green-400 {
  background-color: #4ade80;
}

.bg-red-400 {
  background-color: #f87171;
}

/* Hover effects para ações rápidas */
.hover\:border-gray-300:hover {
  border-color: #d1d5db;
}

/* Estilo para métricas com variação */
.text-green-600 {
  color: #059669;
}

.text-red-600 {
  color: #dc2626;
}

/* Grid responsivo */
@media (max-width: 1024px) {
  .lg\:col-span-2 {
    grid-column: span 1;
  }
}

/* Estilo para cards de status */
.rounded-full {
  border-radius: 9999px;
}

/* Animação para barras do gráfico */
.ease-out {
  transition-timing-function: cubic-bezier(0, 0, 0.2, 1);
}
</style>
