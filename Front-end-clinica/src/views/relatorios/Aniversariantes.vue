<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Relatório de Aniversariantes" description="Pacientes que fazem aniversário" :icon="GiftIcon"
      icon-bg-color="pink" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Relatório de Aniversariantes' }
      ]" class="mb-8">
      <template #actions>
        <div class="flex items-center space-x-4">
          <div class="text-sm text-pink-600 font-medium">
            {{ aniversariantes.length }} aniversariante{{ aniversariantes.length !== 1 ? 's' : '' }}
          </div>
          <button @click="exportarLista" :disabled="aniversariantes.length === 0"
            class="px-4 py-2 bg-pink-600 text-white rounded-md hover:bg-pink-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex items-center space-x-2 text-sm font-medium">
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
          <!-- Filtro por Mês -->
          <div class="flex items-center space-x-2">
            <Calendar class="w-4 h-4 text-gray-400" />
            <select v-model="mesSelecionado" @change="filtrarPorMes"
              class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-pink-500 focus:border-pink-500 text-sm">
              <option value="">Todos os meses</option>
              <option v-for="(mes, index) in meses" :key="index" :value="index + 1">
                {{ mes }}
              </option>
            </select>
          </div>

          <!-- Filtro por Dia Específico -->
          <div class="flex items-center space-x-2">
            <CalendarDays class="w-4 h-4 text-gray-400" />
            <input type="date" v-model="dataEspecifica" @change="filtrarPorData"
              class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-pink-500 focus:border-pink-500 text-sm" />
          </div>

          <!-- Botão Limpar Filtros -->
          <button @click="limparFiltros"
            class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-md transition-colors text-sm">
            Limpar Filtros
          </button>
        </div>

        <!-- Botão Recarregar -->
        <button @click="carregarAniversariantes" :disabled="carregando"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-400 transition-colors flex items-center space-x-2 text-sm font-medium">
          <RefreshCw :class="['w-4 h-4', { 'animate-spin': carregando }]" />
          <span>{{ carregando ? 'Carregando...' : 'Recarregar' }}</span>
        </button>
      </div>
    </div>

    <!-- Estado de Carregamento -->
    <div v-if="carregando" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
      <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <RefreshCw class="w-8 h-8 text-pink-600 animate-spin" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Carregando aniversariantes...</h3>
      <p class="text-gray-500">Aguarde enquanto buscamos os dados</p>
    </div>

    <!-- Estado de Erro -->
    <div v-else-if="erro" class="bg-white rounded-lg shadow-sm border border-red-200 p-12 text-center">
      <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <AlertCircle class="w-8 h-8 text-red-600" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Erro ao carregar dados</h3>
      <p class="text-gray-500 mb-4">{{ erro }}</p>
      <button @click="carregarAniversariantes"
        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
        Tentar Novamente
      </button>
    </div>

    <!-- Lista de Aniversariantes -->
    <div v-else-if="aniversariantes.length > 0" class="space-y-4">
      <!-- Aniversariantes do Dia (Destaque) -->
      <div v-if="aniversariantesDoDia.length > 0" class="mb-6">
        <div class="bg-gradient-to-r from-pink-500 to-pink-600 rounded-lg shadow-sm p-6 text-white mb-4">
          <div class="flex items-center space-x-3 mb-4">
            <Cake class="w-8 h-8" />
            <div>
              <h2 class="text-xl font-bold">🎉 Aniversariantes de Hoje!</h2>
              <p class="text-pink-100">{{ aniversariantesDoDia.length }} pessoa{{ aniversariantesDoDia.length !== 1 ?
                's' :
                '' }} fazendo aniversário hoje</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
          <div v-for="aniversariante in aniversariantesDoDia" :key="`hoje-${aniversariante.nome}`"
            class="bg-white rounded-lg shadow-sm border-2 border-pink-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center">
                  <Gift class="w-6 h-6 text-pink-600" />
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">{{ aniversariante.nome }}</h3>
                  <p class="text-sm text-pink-600 font-medium">🎂 Hoje é seu aniversário!</p>
                </div>
              </div>
              <span
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-pink-100 text-pink-800">
                {{ aniversariante.idade }} anos
              </span>
            </div>

            <div class="space-y-2 text-sm text-gray-600">
              <div class="flex items-center space-x-2">
                <CalendarDays class="w-4 h-4" />
                <span>{{ formatarDataNascimento(aniversariante.dataNascimento) }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <Phone class="w-4 h-4" />
                <span>{{ aniversariante.telefone }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Todos os Aniversariantes -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ getTituloLista() }}
          </h3>
          <p class="text-sm text-gray-500 mt-1">
            {{ aniversariantes.length }} aniversariante{{ aniversariantes.length !== 1 ? 's' : '' }} encontrado{{
              aniversariantes.length !== 1 ? 's' : '' }}
          </p>
        </div>

        <!-- Versão Desktop - Tabela -->
        <div class="hidden md:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data de
                  Nascimento</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Idade</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="aniversariante in aniversariantes" :key="aniversariante.nome" class="hover:bg-gray-50"
                :class="{ 'bg-pink-50': ehAniversarioHoje(aniversariante.dataNascimento) }">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-pink-100 rounded-full flex items-center justify-center">
                      <User class="w-5 h-5 text-pink-600" />
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ aniversariante.nome }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatarDataNascimento(aniversariante.dataNascimento) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ aniversariante.idade }} anos
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <div class="flex items-center space-x-2">
                    <Phone class="w-4 h-4 text-gray-400" />
                    <span>{{ aniversariante.telefone }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center space-x-2">
                    <component :is="ehAniversarioHoje(aniversariante.dataNascimento) ? Cake : Calendar"
                      :class="ehAniversarioHoje(aniversariante.dataNascimento) ? 'w-4 h-4 text-pink-600' : 'w-4 h-4 text-gray-400'" />
                    <span
                      :class="ehAniversarioHoje(aniversariante.dataNascimento) ? 'text-pink-600 font-medium' : 'text-gray-500'"
                      class="text-sm">
                      {{ ehAniversarioHoje(aniversariante.dataNascimento) ? 'Hoje!' : 'Próximo' }}
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Versão Mobile - Cards -->
        <div class="md:hidden p-4 space-y-4">
          <div v-for="aniversariante in aniversariantes" :key="`mobile-${aniversariante.nome}`"
            class="border border-gray-200 rounded-lg p-4"
            :class="{ 'border-pink-200 bg-pink-50': ehAniversarioHoje(aniversariante.dataNascimento) }">
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-pink-100 rounded-full flex items-center justify-center">
                  <User class="w-5 h-5 text-pink-600" />
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-900">{{ aniversariante.nome }}</h4>
                  <p class="text-xs text-gray-500">{{ formatarDataNascimento(aniversariante.dataNascimento) }}</p>
                </div>
              </div>
              <span
                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ aniversariante.idade }} anos
              </span>
            </div>

            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-2 text-sm text-gray-600">
                <Phone class="w-4 h-4" />
                <span>{{ aniversariante.telefone }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <component :is="ehAniversarioHoje(aniversariante.dataNascimento) ? Cake : Calendar"
                  :class="ehAniversarioHoje(aniversariante.dataNascimento) ? 'w-4 h-4 text-pink-600' : 'w-4 h-4 text-gray-400'" />
                <span
                  :class="ehAniversarioHoje(aniversariante.dataNascimento) ? 'text-pink-600 font-medium' : 'text-gray-500'"
                  class="text-sm">
                  {{ ehAniversarioHoje(aniversariante.dataNascimento) ? 'Hoje!' : 'Próximo' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Estado Vazio -->
    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <Cake class="w-8 h-8 text-gray-400" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum aniversariante encontrado</h3>
      <p class="text-gray-500 mb-4">
        {{ mesSelecionado || dataEspecifica
          ? 'Não há aniversariantes no período selecionado.'
          : 'Não há aniversariantes cadastrados no momento.'
        }}
      </p>
      <button @click="limparFiltros"
        class="px-4 py-2 bg-pink-600 text-white rounded-md hover:bg-pink-700 transition-colors">
        Ver Todos os Aniversariantes
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from '../../services/axios.js'
import { GiftIcon } from '@heroicons/vue/24/outline'
import {
  Cake, Download, Calendar, CalendarDays, RefreshCw, AlertCircle,
  Gift, Phone, User
} from 'lucide-vue-next'

// ===== ESTADO REATIVO =====
const aniversariantes = ref([]) // Lista completa de aniversariantes
const carregando = ref(false) // Estado de carregamento
const erro = ref('') // Mensagem de erro
const mesSelecionado = ref('') // Mês selecionado no filtro
const dataEspecifica = ref('') // Data específica selecionada
const todosAniversariantes = ref([])

// ===== DADOS AUXILIARES =====
const meses = [
  'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
]

// ===== COMPUTED PROPERTIES =====

/**
 * Filtra aniversariantes que fazem aniversário hoje
 */
const aniversariantesDoDia = computed(() => {
  const hoje = new Date()
  const diaHoje = hoje.getDate()
  const mesHoje = hoje.getMonth() + 1

  return aniversariantes.value.filter(aniversariante => {
    const dataNasc = new Date(aniversariante.dataNascimento + 'T00:00:00')
    return dataNasc.getDate() === diaHoje && (dataNasc.getMonth() + 1) === mesHoje
  })
})

// ===== FUNÇÕES UTILITÁRIAS =====

/**
 * Calcula a idade com base na data de nascimento
 */
const calcularIdade = (dataNascimento) => {
  const hoje = new Date()
  const nascimento = new Date(dataNascimento + 'T00:00:00')
  let idade = hoje.getFullYear() - nascimento.getFullYear()
  const mesAtual = hoje.getMonth()
  const mesNascimento = nascimento.getMonth()

  if (mesAtual < mesNascimento || (mesAtual === mesNascimento && hoje.getDate() < nascimento.getDate())) {
    idade--
  }

  return idade
}

/**
 * Formata a data de nascimento para exibição
 */
const formatarDataNascimento = (data) => {
  const date = new Date(data + 'T00:00:00')
  return date.toLocaleDateString('pt-BR')
}

/**
 * Verifica se é aniversário hoje
 */
const ehAniversarioHoje = (dataNascimento) => {
  const hoje = new Date()
  const nascimento = new Date(dataNascimento + 'T00:00:00')
  return hoje.getDate() === nascimento.getDate() && hoje.getMonth() === nascimento.getMonth()
}

/**
 * Gera título da lista baseado nos filtros
 */
const getTituloLista = () => {
  if (dataEspecifica.value) {
    return `Aniversariantes do dia ${formatarDataNascimento(dataEspecifica.value)}`
  }
  if (mesSelecionado.value) {
    return `Aniversariantes de ${meses[mesSelecionado.value - 1]}`
  }
  return 'Todos os Aniversariantes'
}

const toDateOnly = (value) => {
  if (!value) return ''
  if (typeof value === 'string') return value.slice(0, 10)
  return ''
}

const filtrarLista = (mes = null, dataIso = null) => {
  let lista = [...todosAniversariantes.value]

  if (dataIso) {
    const dataFiltro = new Date(dataIso + 'T00:00:00')
    const diaFiltro = dataFiltro.getDate()
    const mesFiltro = dataFiltro.getMonth() + 1
    lista = lista.filter((item) => {
      const dataNasc = new Date(item.dataNascimento + 'T00:00:00')
      return dataNasc.getDate() === diaFiltro && (dataNasc.getMonth() + 1) === mesFiltro
    })
  } else if (mes) {
    lista = lista.filter((item) => {
      const dataNasc = new Date(item.dataNascimento + 'T00:00:00')
      return (dataNasc.getMonth() + 1) === Number(mes)
    })
  }

  aniversariantes.value = lista
}

// ===== FUNÇÕES DA API =====

/**
 * Carrega aniversariantes da API
 * Por padrão carrega do mês atual
 */
const carregarAniversariantes = async (mes = null) => {
  carregando.value = true
  erro.value = ''

  try {
    const mesParam = mes || (mesSelecionado.value ? Number(mesSelecionado.value) : null)
    const response = await axios.get('/listar-pacientes')
    const pacientes = response.data?.data || response.data || []

    todosAniversariantes.value = pacientes
      .filter((p) => p.data_nascimento)
      .map((p) => {
        const dataNascimento = toDateOnly(p.data_nascimento)
        return {
          id: p.id,
          nome: p.nome,
          dataNascimento,
          telefone: p.contato || '—',
          idade: calcularIdade(dataNascimento),
        }
      })
      .sort((a, b) => {
        const da = new Date(a.dataNascimento + 'T00:00:00')
        const db = new Date(b.dataNascimento + 'T00:00:00')
        return da.getDate() - db.getDate() || da.getMonth() - db.getMonth()
      })

    filtrarLista(mesParam, dataEspecifica.value || null)

  } catch (error) {
    console.error('Erro ao carregar aniversariantes:', error)

    if (error.response) {
      erro.value = `Erro do servidor: ${error.response.status} - ${error.response.data?.message || 'Erro desconhecido'}`
    } else if (error.request) {
      erro.value = 'Erro de conexão. Verifique sua internet e tente novamente.'
    } else {
      erro.value = 'Erro inesperado. Tente novamente.'
    }

    aniversariantes.value = []
    todosAniversariantes.value = []

  } finally {
    carregando.value = false
  }
}

// ===== FUNÇÕES DE FILTRO =====

/**
 * Filtra aniversariantes por mês específico
 */
const filtrarPorMes = async () => {
  dataEspecifica.value = ''
  if (mesSelecionado.value) {
    filtrarLista(parseInt(mesSelecionado.value), null)
  } else {
    filtrarLista(null, null)
  }
}

/**
 * Filtra aniversariantes por data específica
 */
const filtrarPorData = () => {
  if (dataEspecifica.value) {
    mesSelecionado.value = ''
    filtrarLista(null, dataEspecifica.value)
  }
}

/**
 * Limpa todos os filtros e recarrega dados do mês atual
 */
const limparFiltros = () => {
  mesSelecionado.value = ''
  dataEspecifica.value = ''
  filtrarLista(null, null)
}

// ===== FUNÇÃO DE EXPORTAÇÃO =====

/**
 * Exporta a lista de aniversariantes em formato .txt
 */
const exportarLista = () => {
  if (aniversariantes.value.length === 0) {
    alert('Não há aniversariantes para exportar!')
    return
  }

  const dataAtual = new Date().toLocaleString('pt-BR')
  let conteudo = `RELATÓRIO DE ANIVERSARIANTES\n`
  conteudo += `Gerado em: ${dataAtual}\n`
  conteudo += `${getTituloLista()}\n`
  conteudo += `Total: ${aniversariantes.value.length} aniversariante${aniversariantes.value.length !== 1 ? 's' : ''}\n\n`

  // Destaque para aniversariantes do dia
  if (aniversariantesDoDia.value.length > 0) {
    conteudo += `🎉 ANIVERSARIANTES DE HOJE (${aniversariantesDoDia.value.length}):\n`
    conteudo += `${'='.repeat(50)}\n`
    aniversariantesDoDia.value.forEach(aniversariante => {
      conteudo += `• ${aniversariante.nome} - ${aniversariante.idade} anos - ${aniversariante.telefone}\n`
    })
    conteudo += `\n`
  }

  // Lista completa
  conteudo += `LISTA COMPLETA:\n`
  conteudo += `${'='.repeat(50)}\n`
  conteudo += `Nome                          | Nascimento   | Idade | Telefone\n`
  conteudo += `${'-'.repeat(70)}\n`

  aniversariantes.value.forEach(aniversariante => {
    const nome = aniversariante.nome.padEnd(30)
    const nascimento = formatarDataNascimento(aniversariante.dataNascimento).padEnd(12)
    const idade = `${aniversariante.idade} anos`.padEnd(6)
    const telefone = aniversariante.telefone

    conteudo += `${nome}| ${nascimento}| ${idade}| ${telefone}\n`
  })

  // Criar e baixar arquivo
  const elemento = document.createElement('a')
  elemento.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(conteudo)

  const nomeArquivo = `aniversariantes-${new Date().toISOString().split('T')[0]}.txt`
  elemento.download = nomeArquivo
  elemento.click()

  console.log('Lista exportada:', nomeArquivo)
}

// ===== INICIALIZAÇÃO =====

/**
 * Carrega aniversariantes do mês atual quando o componente é montado
 */
onMounted(() => {
  mesSelecionado.value = String(new Date().getMonth() + 1)
  carregarAniversariantes(new Date().getMonth() + 1)
})
</script>