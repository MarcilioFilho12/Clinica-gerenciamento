<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Consultas Vencidas" description="Retornos e consultas em atraso" :icon="ExclamationTriangleIcon"
      icon-bg-color="red" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Consultas Vencidas' }
      ]" class="mb-8">
      <template #actions>
        <div class="flex items-center space-x-4">
          <div class="text-sm text-red-600 font-medium">
            {{ consultasVencidas.length }} consulta{{ consultasVencidas.length !== 1 ? 's' : '' }} vencida{{
              consultasVencidas.length !== 1 ? 's' : '' }}
          </div>
          <button @click="exportarLista" :disabled="consultasVencidas.length === 0"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex items-center space-x-2 text-sm font-medium">
            <Download class="w-4 h-4" />
            <span>Exportar Lista</span>
          </button>
        </div>
      </template>
    </PageHeader>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-4 flex-1">
          <!-- Filtro por Período de Vencimento -->
          <div class="flex items-center space-x-2">
            <Clock class="w-4 h-4 text-gray-400" />
            <select v-model="periodoVencimento" @change="filtrarPorPeriodo"
              class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
              <option value="">Todos os períodos</option>
              <option value="hoje">Vencidas hoje</option>
              <option value="semana">Últimos 7 dias</option>
              <option value="mes">Último mês</option>
              <option value="trimestre">Último trimestre</option>
            </select>
          </div>

          <!-- Filtro por Tipo de Consulta -->
          <div class="flex items-center space-x-2">
            <FileText class="w-4 h-4 text-gray-400" />
            <select v-model="tipoConsulta" @change="filtrarPorTipo"
              class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
              <option value="">Todos os tipos</option>
              <option value="retorno">Retornos</option>
              <option value="consulta">Consultas</option>
              <option value="exame">Exames</option>
              <option value="cirurgia">Cirurgias</option>
            </select>
          </div>

          <!-- Filtro por Profissional -->
          <div class="flex items-center space-x-2">
            <UserCheck class="w-4 h-4 text-gray-400" />
            <select v-model="profissionalSelecionado" @change="filtrarPorProfissional"
              class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
              <option value="">Todos os profissionais</option>
              <option v-for="prof in profissionais" :key="prof.id" :value="prof.id">
                {{ prof.nome }}
              </option>
            </select>
          </div>

          <!-- Botão Limpar Filtros -->
          <button @click="limparFiltros"
            class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-md transition-colors text-sm">
            Limpar Filtros
          </button>
        </div>

        <!-- Botão Recarregar -->
        <button @click="carregarConsultasVencidas" :disabled="carregando"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-400 transition-colors flex items-center space-x-2 text-sm font-medium">
          <RefreshCw :class="['w-4 h-4', { 'animate-spin': carregando }]" />
          <span>{{ carregando ? 'Carregando...' : 'Recarregar' }}</span>
        </button>
      </div>
    </div>

    <!-- Estado de Carregamento -->
    <div v-if="carregando" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
      <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <RefreshCw class="w-8 h-8 text-red-600 animate-spin" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Carregando consultas vencidas...</h3>
      <p class="text-gray-500">Aguarde enquanto buscamos os dados</p>
    </div>

    <!-- Estado de Erro -->
    <div v-else-if="erro" class="bg-white rounded-lg shadow-sm border border-red-200 p-12 text-center">
      <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <AlertCircle class="w-8 h-8 text-red-600" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Erro ao carregar dados</h3>
      <p class="text-gray-500 mb-4">{{ erro }}</p>
      <button @click="carregarConsultasVencidas"
        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
        Tentar Novamente
      </button>
    </div>

    <!-- Lista de Consultas Vencidas -->
    <div v-else-if="consultasVencidas.length > 0" class="space-y-4">
      <!-- Consultas Críticas (Vencidas há mais de 30 dias) -->
      <div v-if="consultasCriticas.length > 0" class="mb-6">
        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-sm p-6 text-white mb-4">
          <div class="flex items-center space-x-3 mb-4">
            <AlertTriangle class="w-8 h-8" />
            <div>
              <h2 class="text-xl font-bold">⚠️ Consultas Críticas!</h2>
              <p class="text-red-100">{{ consultasCriticas.length }} consulta{{ consultasCriticas.length !== 1 ? 's' :
                '' }}
                vencida{{ consultasCriticas.length !== 1 ? 's' : '' }} há mais de 30 dias</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
          <div v-for="consulta in consultasCriticas" :key="`critica-${consulta.id}`"
            class="bg-white rounded-lg shadow-sm border-2 border-red-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                  <AlertTriangle class="w-6 h-6 text-red-600" />
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">{{ consulta.paciente }}</h3>
                  <p class="text-sm text-red-600 font-medium">🚨 Crítica - {{ consulta.diasVencida }} dias</p>
                </div>
              </div>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                :class="getStatusClass(consulta.prioridade)">
                {{ consulta.prioridade }}
              </span>
            </div>

            <div class="space-y-2 text-sm text-gray-600">
              <div class="flex items-center space-x-2">
                <Calendar class="w-4 h-4" />
                <span>Venceu em: {{ formatarData(consulta.dataVencimento) }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <FileText class="w-4 h-4" />
                <span>{{ consulta.tipoConsulta }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <Phone class="w-4 h-4" />
                <span>{{ consulta.telefone }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Todas as Consultas Vencidas -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ getTituloLista() }}
          </h3>
          <p class="text-sm text-gray-500 mt-1">
            {{ consultasVencidas.length }} consulta{{ consultasVencidas.length !== 1 ? 's' : '' }} vencida{{
              consultasVencidas.length !== 1 ? 's' : '' }} encontrada{{ consultasVencidas.length !== 1 ? 's' : '' }}
          </p>
        </div>

        <!-- Versão Desktop - Tabela -->
        <div class="hidden md:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data
                  Vencimento
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dias Vencida
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profissional
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridade
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="consulta in consultasVencidas" :key="consulta.id" class="hover:bg-gray-50"
                :class="{ 'bg-red-50': consulta.diasVencida > 30 }">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                      <User class="w-5 h-5 text-red-600" />
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ consulta.paciente }}</div>
                      <div class="text-sm text-gray-500">ID: {{ consulta.pacienteId }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center space-x-2">
                    <component :is="getTipoIcon(consulta.tipoConsulta)" class="w-4 h-4 text-gray-400" />
                    <span class="text-sm text-gray-900">{{ consulta.tipoConsulta }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatarData(consulta.dataVencimento) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getDiasVencidaClass(consulta.diasVencida)">
                    {{ consulta.diasVencida }} dia{{ consulta.diasVencida !== 1 ? 's' : '' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ consulta.profissional }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <div class="flex items-center space-x-2">
                    <Phone class="w-4 h-4 text-gray-400" />
                    <span>{{ consulta.telefone }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getStatusClass(consulta.prioridade)">
                    {{ consulta.prioridade }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Versão Mobile - Cards -->
        <div class="md:hidden p-4 space-y-4">
          <div v-for="consulta in consultasVencidas" :key="`mobile-${consulta.id}`"
            class="border border-gray-200 rounded-lg p-4"
            :class="{ 'border-red-200 bg-red-50': consulta.diasVencida > 30 }">
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                  <User class="w-5 h-5 text-red-600" />
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-900">{{ consulta.paciente }}</h4>
                  <p class="text-xs text-gray-500">{{ consulta.tipoConsulta }} - {{ consulta.profissional }}</p>
                </div>
              </div>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                :class="getStatusClass(consulta.prioridade)">
                {{ consulta.prioridade }}
              </span>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 mb-3">
              <div class="flex items-center space-x-2">
                <Calendar class="w-4 h-4" />
                <span>{{ formatarData(consulta.dataVencimento) }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <Clock class="w-4 h-4" />
                <span>{{ consulta.diasVencida }} dias</span>
              </div>
            </div>

            <div class="flex items-center space-x-2 text-sm text-gray-600">
              <Phone class="w-4 h-4" />
              <span>{{ consulta.telefone }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Estado Vazio -->
    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <CheckCircle class="w-8 h-8 text-green-400" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma consulta vencida encontrada</h3>
      <p class="text-gray-500 mb-4">
        {{ temFiltrosAtivos()
          ? 'Não há consultas vencidas no período selecionado.'
          : 'Parabéns! Todas as consultas estão em dia.'
        }}
      </p>
      <button @click="limparFiltros"
        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
        {{ temFiltrosAtivos() ? 'Ver Todas as Consultas' : 'Recarregar' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from '../../services/axios.js'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import {
  AlertTriangle, Download, Clock, FileText, UserCheck, RefreshCw,
  AlertCircle, Calendar, Phone, User, CheckCircle, Eye, Stethoscope,
  Scissors, RotateCcw
} from 'lucide-vue-next'

// ===== ESTADO REATIVO =====
const consultasVencidas = ref([]) // Lista completa de consultas vencidas
const carregando = ref(false) // Estado de carregamento
const erro = ref('') // Mensagem de erro
const periodoVencimento = ref('') // Período de vencimento selecionado
const tipoConsulta = ref('') // Tipo de consulta selecionado
const profissionalSelecionado = ref('') // Profissional selecionado

// ===== DADOS AUXILIARES =====
const profissionais = ref([])

// ===== COMPUTED PROPERTIES =====

/**
 * Filtra consultas críticas (vencidas há mais de 30 dias)
 */
const consultasCriticas = computed(() => {
  return consultasVencidas.value.filter(consulta => consulta.diasVencida > 30)
})

// ===== FUNÇÕES UTILITÁRIAS =====

/**
 * Calcula quantos dias a consulta está vencida
 */
const calcularDiasVencida = (dataVencimento) => {
  const hoje = new Date()
  const vencimento = new Date(dataVencimento + 'T00:00:00')
  const diffTime = hoje - vencimento
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return Math.max(0, diffDays)
}

/**
 * Determina a prioridade baseada nos dias vencidos
 */
const determinarPrioridade = (diasVencida) => {
  if (diasVencida > 60) return 'Crítica'
  if (diasVencida > 30) return 'Alta'
  if (diasVencida > 15) return 'Média'
  return 'Baixa'
}

/**
 * Formata a data para exibição
 */
const formatarData = (data) => {
  const date = new Date(data + 'T00:00:00')
  return date.toLocaleDateString('pt-BR')
}

/**
 * Gera título da lista baseado nos filtros
 */
const getTituloLista = () => {
  let titulo = 'Consultas Vencidas'

  if (periodoVencimento.value) {
    const periodos = {
      'hoje': 'Vencidas Hoje',
      'semana': 'Vencidas nos Últimos 7 Dias',
      'mes': 'Vencidas no Último Mês',
      'trimestre': 'Vencidas no Último Trimestre'
    }
    titulo = periodos[periodoVencimento.value] || titulo
  }

  if (tipoConsulta.value) {
    titulo += ` - ${tipoConsulta.value.charAt(0).toUpperCase() + tipoConsulta.value.slice(1)}s`
  }

  if (profissionalSelecionado.value) {
    const prof = profissionais.value.find(p => p.id == profissionalSelecionado.value)
    if (prof) titulo += ` - ${prof.nome}`
  }

  return titulo
}

/**
 * Verifica se há filtros ativos
 */
const temFiltrosAtivos = () => {
  return periodoVencimento.value || tipoConsulta.value || profissionalSelecionado.value
}

/**
 * Retorna classe CSS baseada nos dias vencidos
 */
const getDiasVencidaClass = (dias) => {
  if (dias > 60) return 'bg-red-100 text-red-800'
  if (dias > 30) return 'bg-orange-100 text-orange-800'
  if (dias > 15) return 'bg-yellow-100 text-yellow-800'
  return 'bg-blue-100 text-blue-800'
}

/**
 * Retorna classe CSS baseada na prioridade
 */
const getStatusClass = (prioridade) => {
  const classes = {
    'Crítica': 'bg-red-100 text-red-800',
    'Alta': 'bg-orange-100 text-orange-800',
    'Média': 'bg-yellow-100 text-yellow-800',
    'Baixa': 'bg-blue-100 text-blue-800'
  }
  return classes[prioridade] || 'bg-gray-100 text-gray-800'
}

/**
 * Retorna ícone baseado no tipo de consulta
 */
const getTipoIcon = (tipo) => {
  const icons = {
    'retorno': RotateCcw,
    'consulta': Stethoscope,
    'exame': Eye,
    'cirurgia': Scissors
  }
  return icons[String(tipo || '').toLowerCase()] || FileText
}

const toDateOnly = (value) => {
  if (!value) return ''
  if (typeof value === 'string') return value.slice(0, 10)
  return ''
}

const dentroDoPeriodo = (diasVencida) => {
  switch (periodoVencimento.value) {
    case 'hoje':
      return diasVencida === 0
    case 'semana':
      return diasVencida <= 7
    case 'mes':
      return diasVencida <= 30
    case 'trimestre':
      return diasVencida <= 90
    default:
      return true
  }
}

// ===== FUNÇÕES DA API =====

/**
 * Carrega consultas vencidas da API
 */
const carregarConsultasVencidas = async () => {
  carregando.value = true
  erro.value = ''

  try {
    const hoje = new Date()
    const fim = new Date(hoje)
    const inicio = new Date(hoje)
    inicio.setDate(inicio.getDate() - 180)

    const fmt = (d) => d.toISOString().slice(0, 10)
    const params = {
      data_inicio: fmt(inicio),
      data_fim: fmt(fim),
    }
    if (profissionalSelecionado.value) {
      params.user_id = profissionalSelecionado.value
    }

    const [agendaRes, usersRes] = await Promise.all([
      axios.get('/consultas/agenda-periodo', { params }),
      axios.get('/usuarios').catch(() => ({ data: { data: [] } })),
    ])

    const consultas = agendaRes.data?.data?.consultas || []
    const users = usersRes.data?.data || usersRes.data || []
    profissionais.value = users
      .filter((u) => Number(u.profile_id) === 3)
      .map((u) => ({ id: u.id, nome: u.name }))

    if (profissionais.value.length === 0) {
      const mapa = new Map()
      consultas.forEach((c) => {
        if (c.user_id && c.user?.name) {
          mapa.set(c.user_id, { id: c.user_id, nome: c.user.name })
        }
      })
      profissionais.value = [...mapa.values()]
    }

    // Não encerradas (4) nem canceladas (5)
    let lista = consultas
      .filter((c) => ![4, 5].includes(Number(c.situacao_id)))
      .map((c) => {
        const dataVencimento = toDateOnly(c.data)
        const diasVencida = calcularDiasVencida(dataVencimento)
        const tipo = c.procedimento || 'consulta'
        return {
          id: c.id,
          paciente: c.paciente?.nome || 'Paciente',
          pacienteId: c.paciente_id,
          tipoConsulta: tipo,
          dataVencimento,
          diasVencida,
          prioridade: determinarPrioridade(diasVencida),
          profissional: c.user?.name || '—',
          profissionalId: c.user_id,
          telefone: c.paciente?.contato || '—',
        }
      })
      .filter((c) => dentroDoPeriodo(c.diasVencida))

    if (tipoConsulta.value) {
      const tipo = tipoConsulta.value.toLowerCase()
      lista = lista.filter((c) => String(c.tipoConsulta).toLowerCase().includes(tipo))
    }

    consultasVencidas.value = lista.sort((a, b) => b.diasVencida - a.diasVencida)

  } catch (error) {
    console.error('Erro ao carregar consultas vencidas:', error)

    if (error.response) {
      erro.value = `Erro do servidor: ${error.response.status} - ${error.response.data?.message || 'Erro desconhecido'}`
    } else if (error.request) {
      erro.value = 'Erro de conexão. Verifique sua internet e tente novamente.'
    } else {
      erro.value = 'Erro inesperado. Tente novamente.'
    }

    consultasVencidas.value = []

  } finally {
    carregando.value = false
  }
}

// ===== FUNÇÕES DE FILTRO =====

/**
 * Filtra consultas por período de vencimento
 */
const filtrarPorPeriodo = async () => {
  await carregarConsultasVencidas()
}

/**
 * Filtra consultas por tipo
 */
const filtrarPorTipo = async () => {
  await carregarConsultasVencidas()
}

/**
 * Filtra consultas por profissional
 */
const filtrarPorProfissional = async () => {
  await carregarConsultasVencidas()
}

/**
 * Limpa todos os filtros e recarrega dados
 */
const limparFiltros = () => {
  periodoVencimento.value = ''
  tipoConsulta.value = ''
  profissionalSelecionado.value = ''
  carregarConsultasVencidas()
}

// ===== FUNÇÃO DE EXPORTAÇÃO =====

/**
 * Exporta a lista de consultas vencidas em formato .txt
 */
const exportarLista = () => {
  if (consultasVencidas.value.length === 0) {
    alert('Não há consultas vencidas para exportar!')
    return
  }

  const dataAtual = new Date().toLocaleString('pt-BR')
  let conteudo = `RELATÓRIO DE CONSULTAS VENCIDAS\n`
  conteudo += `Gerado em: ${dataAtual}\n`
  conteudo += `${getTituloLista()}\n`
  conteudo += `Total: ${consultasVencidas.value.length} consulta${consultasVencidas.value.length !== 1 ? 's' : ''} vencida${consultasVencidas.value.length !== 1 ? 's' : ''}\n\n`

  // Destaque para consultas críticas
  if (consultasCriticas.value.length > 0) {
    conteudo += `⚠️ CONSULTAS CRÍTICAS (${consultasCriticas.value.length}) - Mais de 30 dias:\n`
    conteudo += `${'='.repeat(70)}\n`
    consultasCriticas.value.forEach(consulta => {
      conteudo += `• ${consulta.paciente} - ${consulta.tipoConsulta} - ${consulta.diasVencida} dias - ${consulta.telefone}\n`
    })
    conteudo += `\n`
  }

  // Lista completa por prioridade
  const prioridades = ['Crítica', 'Alta', 'Média', 'Baixa']
  prioridades.forEach(prioridade => {
    const consultasPrioridade = consultasVencidas.value.filter(c => c.prioridade === prioridade)
    if (consultasPrioridade.length > 0) {
      conteudo += `PRIORIDADE ${prioridade.toUpperCase()} (${consultasPrioridade.length}):\n`
      conteudo += `${'-'.repeat(50)}\n`
      conteudo += `Paciente                     | Tipo      | Vencimento | Dias | Profissional      | Telefone\n`
      conteudo += `${'-'.repeat(100)}\n`

      consultasPrioridade.forEach(consulta => {
        const paciente = consulta.paciente.padEnd(25)
        const tipo = consulta.tipoConsulta.padEnd(10)
        const vencimento = formatarData(consulta.dataVencimento).padEnd(11)
        const dias = `${consulta.diasVencida}`.padEnd(5)
        const profissional = consulta.profissional.padEnd(18)
        const telefone = consulta.telefone

        conteudo += `${paciente}| ${tipo}| ${vencimento}| ${dias}| ${profissional}| ${telefone}\n`
      })
      conteudo += `\n`
    }
  })

  // Resumo estatístico
  conteudo += `RESUMO ESTATÍSTICO:\n`
  conteudo += `${'='.repeat(30)}\n`
  conteudo += `Total de consultas vencidas: ${consultasVencidas.value.length}\n`
  conteudo += `Consultas críticas (>30 dias): ${consultasCriticas.value.length}\n`
  conteudo += `Média de dias vencidos: ${Math.round(consultasVencidas.value.reduce((acc, c) => acc + c.diasVencida, 0) / consultasVencidas.value.length)} dias\n`

  // Criar e baixar arquivo
  const elemento = document.createElement('a')
  elemento.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(conteudo)

  const nomeArquivo = `consultas-vencidas-${new Date().toISOString().split('T')[0]}.txt`
  elemento.download = nomeArquivo
  elemento.click()

  console.log('Lista exportada:', nomeArquivo)
}

// ===== INICIALIZAÇÃO =====

/**
 * Carrega consultas vencidas quando o componente é montado
 */
onMounted(() => {
  console.log('Componente ConsultasVencidas montado')
  carregarConsultasVencidas()
})
</script>