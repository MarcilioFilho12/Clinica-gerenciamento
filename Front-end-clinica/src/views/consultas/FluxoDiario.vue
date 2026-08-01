<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Fluxo Diário" :description="`Visão executiva do dia ${dataAtual}`" :icon="ChartPieIcon"
      icon-bg-color="indigo" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Fluxo Diário' }
      ]" class="mb-8">
      <template #actions>
        <div class="flex items-center space-x-4">
          <div class="flex items-center space-x-2">
            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-sm text-gray-600">Atualizado há {{ ultimaAtualizacao }}</span>
          </div>
          <button @click="atualizarDados" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors">
            <RefreshCw class="w-4 h-4" />
          </button>
        </div>
      </template>
    </PageHeader>
    <!-- KPIs Principais -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="text-center">
          <div class="text-2xl font-bold text-indigo-600">{{ kpis.eficiencia }}%</div>
          <div class="text-xs text-gray-500 uppercase tracking-wide">Eficiência</div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="text-center">
          <div class="text-2xl font-bold text-green-600">{{ kpis.atendidos }}</div>
          <div class="text-xs text-gray-500 uppercase tracking-wide">Atendidos</div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="text-center">
          <div class="text-2xl font-bold text-orange-600">{{ kpis.aguardando }}</div>
          <div class="text-xs text-gray-500 uppercase tracking-wide">Aguardando</div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="text-center">
          <div class="text-2xl font-bold text-blue-600">{{ kpis.tempoMedio }}min</div>
          <div class="text-xs text-gray-500 uppercase tracking-wide">Tempo Médio</div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="text-center">
          <div class="text-2xl font-bold text-purple-600">{{ kpis.receita }}</div>
          <div class="text-xs text-gray-500 uppercase tracking-wide">Receita</div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="text-center">
          <div class="text-2xl font-bold" :class="kpis.statusColor">{{ kpis.status }}</div>
          <div class="text-xs text-gray-500 uppercase tracking-wide">Status</div>
        </div>
      </div>
    </div>

    <!-- Linha do Tempo do Dia -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Timeline do Dia</h2>
        <div class="flex items-center space-x-2 text-sm text-gray-500">
          <Clock class="w-4 h-4" />
          <span>{{ horaAtual }}</span>
        </div>
      </div>

      <div class="relative">
        <!-- Linha do tempo -->
        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

        <div class="space-y-4">
          <div v-for="evento in timeline" :key="evento.id" class="relative flex items-start space-x-4">
            <!-- Marcador -->
            <div
              class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center border-2 border-white shadow-sm"
              :class="getEventoColor(evento.tipo)">
              <component :is="getEventoIcon(evento.tipo)" class="w-4 h-4 text-white" />
            </div>

            <!-- Conteúdo -->
            <div class="flex-1 min-w-0 pb-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ evento.titulo }}</p>
                  <p class="text-xs text-gray-500">{{ evento.descricao }}</p>
                </div>
                <div class="text-xs text-gray-400 font-mono">{{ evento.hora }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dashboard em Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Fluxo por Profissional -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Fluxo por Profissional</h3>
        <div class="space-y-4">
          <div v-for="prof in fluxoProfissionais" :key="prof.nome" class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <UserCheck class="w-4 h-4 text-blue-600" />
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">{{ prof.nome }}</p>
                <p class="text-xs text-gray-500">{{ prof.especialidade }}</p>
              </div>
            </div>
            <div class="text-right">
              <div class="text-sm font-semibold text-gray-900">{{ prof.atendidos }}/{{ prof.agendados }}</div>
              <div class="w-16 bg-gray-200 rounded-full h-1.5 mt-1">
                <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-300"
                  :style="{ width: `${prof.progresso}%` }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Alertas e Prioridades -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Alertas</h3>
        <div class="space-y-3">
          <div v-for="alerta in alertas" :key="alerta.id" class="flex items-start space-x-3 p-3 rounded-lg"
            :class="getAlertaClass(alerta.tipo)">
            <component :is="getAlertaIcon(alerta.tipo)" class="w-4 h-4 mt-0.5" />
            <div class="flex-1">
              <p class="text-sm font-medium">{{ alerta.titulo }}</p>
              <p class="text-xs opacity-75">{{ alerta.descricao }}</p>
            </div>
            <div class="text-xs opacity-60">{{ alerta.tempo }}</div>
          </div>
        </div>
      </div>

      <!-- Próximas Ações -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Próximas Ações</h3>
        <div class="space-y-3">
          <div v-for="acao in proximasAcoes" :key="acao.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
            @click="executarAcao(acao)">
            <div class="flex items-center space-x-3">
              <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center">
                <component :is="acao.icone" class="w-3 h-3 text-indigo-600" />
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">{{ acao.titulo }}</p>
                <p class="text-xs text-gray-500">{{ acao.descricao }}</p>
              </div>
            </div>
            <ChevronRight class="w-4 h-4 text-gray-400" />
          </div>
        </div>
      </div>
    </div>

    <!-- Resumo Financeiro Rápido -->
    <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Resumo Financeiro</h3>
        <button class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
          Ver Detalhes
        </button>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center p-4 bg-green-50 rounded-lg">
          <div class="text-xl font-bold text-green-600">R$ {{ resumoFinanceiro.receitaDia }}</div>
          <div class="text-xs text-green-600 uppercase tracking-wide">Receita Hoje</div>
        </div>

        <div class="text-center p-4 bg-blue-50 rounded-lg">
          <div class="text-xl font-bold text-blue-600">R$ {{ resumoFinanceiro.ticketMedio }}</div>
          <div class="text-xs text-blue-600 uppercase tracking-wide">Ticket Médio</div>
        </div>

        <div class="text-center p-4 bg-purple-50 rounded-lg">
          <div class="text-xl font-bold text-purple-600">{{ resumoFinanceiro.consultasParticulares }}</div>
          <div class="text-xs text-purple-600 uppercase tracking-wide">Particulares</div>
        </div>

        <div class="text-center p-4 bg-orange-50 rounded-lg">
          <div class="text-xl font-bold text-orange-600">{{ resumoFinanceiro.consultasConvenio }}</div>
          <div class="text-xs text-orange-600 uppercase tracking-wide">Convênio</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { ChartPieIcon } from '@heroicons/vue/24/outline'
import {
  RefreshCw, Clock, UserCheck, ChevronRight,
  CheckCircle, AlertTriangle, Bell
} from 'lucide-vue-next'

// ===== INTEGRAÇÃO COM DADOS COMPARTILHADOS =====
// TODO: Integrar com stores existentes
// 
// import { useConsultasStore } from '@/stores/consultasStore'
// import { useFilaEsperaStore } from '@/stores/filaEsperaStore'
// import { useFluxoDiarioStore } from '@/stores/fluxoDiarioStore'
// 
// const consultasStore = useConsultasStore()
// const filaStore = useFilaEsperaStore()
// const fluxoStore = useFluxoDiarioStore()
// 
// // Dados em tempo real
// const kpis = computed(() => fluxoStore.calcularKPIs())
// const timeline = computed(() => fluxoStore.gerarTimeline())
// const alertas = computed(() => fluxoStore.gerarAlertas())

// Estado
const dataAtual = ref(new Date().toLocaleDateString('pt-BR'))
const horaAtual = ref(new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }))
const ultimaAtualizacao = ref('—')

const kpis = ref({
  eficiencia: 0,
  atendidos: 0,
  aguardando: 0,
  tempoMedio: 0,
  receita: 'R$ 0',
  status: '—',
  statusColor: 'text-gray-500'
})

const timeline = ref([])
const fluxoProfissionais = ref([])
const alertas = ref([])
const proximasAcoes = ref([])

const resumoFinanceiro = ref({
  receitaDia: '0',
  ticketMedio: '0',
  consultasParticulares: 0,
  consultasConvenio: 0
})

// Funções utilitárias
const getEventoColor = (tipo) => {
  const colors = {
    'inicio': 'bg-blue-600',
    'atendimento': 'bg-green-600',
    'alerta': 'bg-orange-600',
    'fim': 'bg-gray-600'
  }
  return colors[tipo] || 'bg-gray-600'
}

const getEventoIcon = (tipo) => {
  const icons = {
    'inicio': Clock,
    'atendimento': CheckCircle,
    'alerta': AlertTriangle,
    'fim': Clock
  }
  return icons[tipo] || Clock
}

const getAlertaClass = (tipo) => {
  const classes = {
    'warning': 'bg-orange-50 text-orange-700',
    'info': 'bg-blue-50 text-blue-700',
    'success': 'bg-green-50 text-green-700',
    'error': 'bg-red-50 text-red-700'
  }
  return classes[tipo] || 'bg-gray-50 text-gray-700'
}

const getAlertaIcon = (tipo) => {
  const icons = {
    'warning': AlertTriangle,
    'info': Bell,
    'success': CheckCircle,
    'error': AlertTriangle
  }
  return icons[tipo] || Bell
}

// Ações
const atualizarDados = () => {
  // TODO: Atualizar dados dos stores
  ultimaAtualizacao.value = 'agora'
  console.log('Dados atualizados')
}

const executarAcao = (acao) => {
  // TODO: Implementar ações específicas
  console.log('Executando ação:', acao.titulo)
}

// Timer para atualizar hora
let timer = null

onMounted(() => {
  timer = setInterval(() => {
    horaAtual.value = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
  }, 60000)
})

onUnmounted(() => {
  if (timer) {
    clearInterval(timer)
  }
})
</script>