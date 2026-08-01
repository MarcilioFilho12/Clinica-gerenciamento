<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Inadimplentes" description="Configure alertas, prazos e ações para contas em atraso"
      :icon="AlertTriangle" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Inadimplentes' }
      ]" icon-bg-color="red" class="mb-8" />

    <!-- Loading State -->
    <div v-if="carregando" class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
      <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <Loader2 class="w-6 h-6 text-red-600 animate-spin" />
      </div>
      <p class="text-gray-600">Carregando configurações...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="erro" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
      <div class="flex items-center space-x-3">
        <AlertCircle class="w-6 h-6 text-red-600" />
        <div>
          <h3 class="text-red-800 font-medium">Erro ao carregar configurações</h3>
          <p class="text-red-600 text-sm mt-1">{{ erro }}</p>
        </div>
      </div>
      <button @click="carregarConfiguracoes"
        class="mt-4 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
        Tentar Novamente
      </button>
    </div>

    <!-- Formulário -->
    <form v-else @submit.prevent="salvarConfiguracoes" class="space-y-8">
      <!-- Prazos de Vencimento -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center space-x-2">
          <Calendar class="w-5 h-5 text-red-600" />
          <span>Prazos de Vencimento</span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Prazo Alerta -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Alerta Preventivo (dias antes do vencimento)
            </label>
            <div class="relative">
              <Bell class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input type="number" v-model.number="configuracoes.dias_alerta_preventivo" min="1" max="30"
                class="pl-10 pr-4 py-3 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.dias_alerta_preventivo }"
                placeholder="Ex: 5" />
            </div>
            <p class="text-xs text-gray-500 mt-1">Notificar antes do vencimento</p>
            <div v-if="erros.dias_alerta_preventivo" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
              <AlertCircle class="w-4 h-4" />
              <span>{{ erros.dias_alerta_preventivo }}</span>
            </div>
          </div>

          <!-- Prazo Inadimplência -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Inadimplência (dias após vencimento)
            </label>
            <div class="relative">
              <Clock class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input type="number" v-model.number="configuracoes.dias_inadimplencia" min="1" max="90"
                class="pl-10 pr-4 py-3 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.dias_inadimplencia }"
                placeholder="Ex: 15" />
            </div>
            <p class="text-xs text-gray-500 mt-1">Considerar inadimplente após</p>
            <div v-if="erros.dias_inadimplencia" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
              <AlertCircle class="w-4 h-4" />
              <span>{{ erros.dias_inadimplencia }}</span>
            </div>
          </div>

          <!-- Prazo Crítico -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Situação Crítica (dias após vencimento)
            </label>
            <div class="relative">
              <AlertTriangle class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input type="number" v-model.number="configuracoes.dias_critico" min="1" max="365"
                class="pl-10 pr-4 py-3 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.dias_critico }"
                placeholder="Ex: 60" />
            </div>
            <p class="text-xs text-gray-500 mt-1">Situação crítica após</p>
            <div v-if="erros.dias_critico" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
              <AlertCircle class="w-4 h-4" />
              <span>{{ erros.dias_critico }}</span>
            </div>
          </div>
        </div>

        <!-- Preview dos Prazos -->
        <div v-if="configuracaoCompleta" class="mt-4 p-4 bg-red-50 rounded-lg">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div class="flex items-center space-x-2 text-yellow-700">
              <Bell class="w-4 h-4" />
              <span>Alerta: {{ configuracoes.dias_alerta_preventivo }} dias antes</span>
            </div>
            <div class="flex items-center space-x-2 text-orange-700">
              <Clock class="w-4 h-4" />
              <span>Inadimplente: {{ configuracoes.dias_inadimplencia }} dias após</span>
            </div>
            <div class="flex items-center space-x-2 text-red-700">
              <AlertTriangle class="w-4 h-4" />
              <span>Crítico: {{ configuracoes.dias_critico }} dias após</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Configurações de Juros e Multa -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center space-x-2">
          <DollarSign class="w-5 h-5 text-red-600" />
          <span>Juros e Multa</span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Multa por Atraso -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Multa por Atraso (%)
            </label>
            <div class="relative">
              <Percent class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input type="number" v-model.number="configuracoes.multa_atraso" min="0" max="20" step="0.1"
                class="pl-10 pr-4 py-3 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.multa_atraso }"
                placeholder="Ex: 2.0" />
            </div>
            <p class="text-xs text-gray-500 mt-1">Percentual sobre o valor original</p>
            <div v-if="erros.multa_atraso" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
              <AlertCircle class="w-4 h-4" />
              <span>{{ erros.multa_atraso }}</span>
            </div>
          </div>

          <!-- Juros Mensais -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Juros Mensais (%)
            </label>
            <div class="relative">
              <TrendingUp class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input type="number" v-model.number="configuracoes.juros_mensal" min="0" max="10" step="0.1"
                class="pl-10 pr-4 py-3 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.juros_mensal }"
                placeholder="Ex: 1.0" />
            </div>
            <p class="text-xs text-gray-500 mt-1">Percentual mensal sobre o valor</p>
            <div v-if="erros.juros_mensal" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
              <AlertCircle class="w-4 h-4" />
              <span>{{ erros.juros_mensal }}</span>
            </div>
          </div>
        </div>

        <!-- Simulação de Cálculo -->
        <div v-if="configuracoes.multa_atraso !== '' && configuracoes.juros_mensal !== ''"
          class="mt-4 p-4 bg-orange-50 rounded-lg">
          <h4 class="text-sm font-medium text-orange-800 mb-2">Simulação para R$ 100,00 com 30 dias de atraso:</h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-orange-700">
            <div>Valor Original: R$ 100,00</div>
            <div>Multa ({{ configuracoes.multa_atraso }}%): R$ {{ calcularMulta(100) }}</div>
            <div>Total: R$ {{ calcularTotalComJuros(100, 30) }}</div>
          </div>
        </div>
      </div>

      <!-- Ações Automáticas -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center space-x-2">
          <Settings class="w-5 h-5 text-red-600" />
          <span>Ações Automáticas</span>
        </h2>

        <div class="space-y-4">
          <!-- Bloquear Agendamentos -->
          <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                <Ban class="w-5 h-5 text-red-600" />
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-900">Bloquear Novos Agendamentos</h3>
                <p class="text-sm text-gray-500">Impedir que inadimplentes agendem novas consultas</p>
              </div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="configuracoes.bloquear_agendamentos" class="sr-only peer" />
              <div
                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600">
              </div>
            </label>
          </div>

          <!-- Enviar Lembretes -->
          <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <Mail class="w-5 h-5 text-blue-600" />
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-900">Enviar Lembretes Automáticos</h3>
                <p class="text-sm text-gray-500">Notificar por email/SMS sobre vencimentos</p>
              </div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="configuracoes.enviar_lembretes" class="sr-only peer" />
              <div
                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
              </div>
            </label>
          </div>

          <!-- Aplicar Juros Automaticamente -->
          <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                <Calculator class="w-5 h-5 text-green-600" />
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-900">Aplicar Juros Automaticamente</h3>
                <p class="text-sm text-gray-500">Calcular e aplicar juros/multa automaticamente</p>
              </div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="configuracoes.aplicar_juros_automatico" class="sr-only peer" />
              <div
                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600">
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Configurações de Notificação -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center space-x-2">
          <MessageSquare class="w-5 h-5 text-red-600" />
          <span>Notificações</span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Frequência de Lembretes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Frequência dos Lembretes
            </label>
            <select v-model="configuracoes.frequencia_lembretes"
              class="w-full px-3 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
              :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.frequencia_lembretes }">
              <option value="">Selecione a frequência</option>
              <option value="diario">Diário</option>
              <option value="semanal">Semanal</option>
              <option value="quinzenal">Quinzenal</option>
              <option value="mensal">Mensal</option>
            </select>
            <div v-if="erros.frequencia_lembretes" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
              <AlertCircle class="w-4 h-4" />
              <span>{{ erros.frequencia_lembretes }}</span>
            </div>
          </div>

          <!-- Máximo de Tentativas -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Máximo de Tentativas de Contato
            </label>
            <div class="relative">
              <Hash class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input type="number" v-model.number="configuracoes.max_tentativas_contato" min="1" max="10"
                class="pl-10 pr-4 py-3 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.max_tentativas_contato }"
                placeholder="Ex: 3" />
            </div>
            <p class="text-xs text-gray-500 mt-1">Número máximo de tentativas</p>
            <div v-if="erros.max_tentativas_contato" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
              <AlertCircle class="w-4 h-4" />
              <span>{{ erros.max_tentativas_contato }}</span>
            </div>
          </div>
        </div>

        <!-- Canais de Comunicação -->
        <div class="mt-6">
          <label class="block text-sm font-medium text-gray-700 mb-3">
            Canais de Comunicação Habilitados
          </label>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <label
              class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
              <input type="checkbox" value="email" v-model="configuracoes.canais_comunicacao"
                class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
              <Mail class="w-5 h-5 text-gray-500" />
              <span class="text-sm font-medium text-gray-900">Email</span>
            </label>

            <label
              class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
              <input type="checkbox" value="sms" v-model="configuracoes.canais_comunicacao"
                class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
              <MessageSquare class="w-5 h-5 text-gray-500" />
              <span class="text-sm font-medium text-gray-900">SMS</span>
            </label>

            <label
              class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
              <input type="checkbox" value="whatsapp" v-model="configuracoes.canais_comunicacao"
                class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
              <Phone class="w-5 h-5 text-gray-500" />
              <span class="text-sm font-medium text-gray-900">WhatsApp</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Resumo das Configurações -->
      <div v-if="configuracaoCompleta"
        class="bg-gradient-to-r from-red-50 to-orange-50 rounded-lg border border-red-200 p-6">
        <h3 class="text-lg font-semibold text-red-900 mb-4 flex items-center space-x-2">
          <FileText class="w-5 h-5" />
          <span>Resumo das Configurações</span>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-red-700 font-medium">Alerta preventivo:</span>
              <span class="text-red-900">{{ configuracoes.dias_alerta_preventivo }} dias antes</span>
            </div>
            <div class="flex justify-between">
              <span class="text-red-700 font-medium">Inadimplência:</span>
              <span class="text-red-900">{{ configuracoes.dias_inadimplencia }} dias após</span>
            </div>
            <div class="flex justify-between">
              <span class="text-red-700 font-medium">Situação crítica:</span>
              <span class="text-red-900">{{ configuracoes.dias_critico }} dias após</span>
            </div>
            <div class="flex justify-between">
              <span class="text-red-700 font-medium">Multa:</span>
              <span class="text-red-900">{{ configuracoes.multa_atraso }}%</span>
            </div>
          </div>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-red-700 font-medium">Juros mensais:</span>
              <span class="text-red-900">{{ configuracoes.juros_mensal }}%</span>
            </div>
            <div class="flex justify-between">
              <span class="text-red-700 font-medium">Bloquear agendamentos:</span>
              <span class="text-red-900">{{ configuracoes.bloquear_agendamentos ? 'Sim' : 'Não' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-red-700 font-medium">Lembretes automáticos:</span>
              <span class="text-red-900">{{ configuracoes.enviar_lembretes ? 'Sim' : 'Não' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-red-700 font-medium">Canais ativos:</span>
              <span class="text-red-900">{{ configuracoes.canais_comunicacao.length }} canal{{
                configuracoes.canais_comunicacao.length !== 1 ? 'is' : '' }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Botões de Ação -->
      <div class="flex flex-col sm:flex-row gap-4 justify-end">
        <button type="button" @click="resetarConfiguracoes"
          class="px-6 py-3 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors font-medium">
          Cancelar Alterações
        </button>

        <button type="submit" :disabled="salvando || !configuracaoCompleta"
          class="px-6 py-3 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:bg-red-400 disabled:cursor-not-allowed transition-colors font-medium flex items-center space-x-2">
          <Loader2 v-if="salvando" class="w-4 h-4 animate-spin" />
          <Save v-else class="w-4 h-4" />
          <span>{{ salvando ? 'Salvando...' : 'Salvar Configurações' }}</span>
        </button>
      </div>
    </form>

    <!-- Mensagens de Feedback -->
    <div v-if="mensagemSucesso"
      class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2 z-50">
      <CheckCircle class="w-5 h-5" />
      <span>{{ mensagemSucesso }}</span>
    </div>

    <div v-if="mensagemErro"
      class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2 z-50">
      <AlertCircle class="w-5 h-5" />
      <span>{{ mensagemErro }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
  AlertTriangle, Calendar, Bell, Clock, DollarSign, Percent, TrendingUp,
  Settings, Ban, Mail, Calculator, MessageSquare, Hash, Phone, FileText,
  Save, CheckCircle, AlertCircle, Loader2
} from 'lucide-vue-next'

const storageKey = () => `marag_inadimplentes_${localStorage.getItem('clinic_slug') || 'default'}`

// ===== ESTADO REATIVO =====
const carregando = ref(false)
const salvando = ref(false)
const erro = ref('')
const mensagemSucesso = ref('')
const mensagemErro = ref('')

// Configurações de inadimplência
const configuracoes = ref({
  dias_alerta_preventivo: '',
  dias_inadimplencia: '',
  dias_critico: '',
  multa_atraso: '',
  juros_mensal: '',
  bloquear_agendamentos: false,
  enviar_lembretes: false,
  aplicar_juros_automatico: false,
  frequencia_lembretes: '',
  max_tentativas_contato: '',
  canais_comunicacao: []
})

// Backup das configurações originais
const configuracoesOriginais = ref({})

// Erros de validação
const erros = ref({})

// ===== COMPUTED PROPERTIES =====

/**
 * Verifica se todas as configurações obrigatórias estão preenchidas
 */
const configuracaoCompleta = computed(() => {
  return configuracoes.value.dias_alerta_preventivo !== '' &&
    configuracoes.value.dias_inadimplencia !== '' &&
    configuracoes.value.dias_critico !== '' &&
    configuracoes.value.multa_atraso !== '' &&
    configuracoes.value.juros_mensal !== '' &&
    configuracoes.value.frequencia_lembretes &&
    configuracoes.value.max_tentativas_contato !== ''
})

// ===== FUNÇÕES UTILITÁRIAS =====

/**
 * Calcula a multa para um valor específico
 */
const calcularMulta = (valor) => {
  if (!configuracoes.value.multa_atraso) return '0,00'
  const multa = (valor * configuracoes.value.multa_atraso) / 100
  return multa.toFixed(2).replace('.', ',')
}

/**
 * Calcula o total com juros para um período específico
 */
const calcularTotalComJuros = (valor, dias) => {
  if (!configuracoes.value.multa_atraso || !configuracoes.value.juros_mensal) return '0,00'

  const multa = (valor * configuracoes.value.multa_atraso) / 100
  const meses = Math.ceil(dias / 30)
  const juros = (valor * configuracoes.value.juros_mensal * meses) / 100
  const total = valor + multa + juros

  return total.toFixed(2).replace('.', ',')
}

/**
 * Limpa mensagens de feedback após um tempo
 */
const limparMensagens = () => {
  setTimeout(() => {
    mensagemSucesso.value = ''
    mensagemErro.value = ''
  }, 5000)
}

// ===== VALIDAÇÃO =====

/**
 * Valida todos os campos do formulário
 */
const validarFormulario = () => {
  erros.value = {}

  // Validar dias de alerta preventivo
  if (configuracoes.value.dias_alerta_preventivo === '' || configuracoes.value.dias_alerta_preventivo < 1) {
    erros.value.dias_alerta_preventivo = 'Dias de alerta preventivo é obrigatório (mínimo 1)'
  }

  // Validar dias de inadimplência
  if (configuracoes.value.dias_inadimplencia === '' || configuracoes.value.dias_inadimplencia < 1) {
    erros.value.dias_inadimplencia = 'Dias de inadimplência é obrigatório (mínimo 1)'
  }

  // Validar dias críticos
  if (configuracoes.value.dias_critico === '' || configuracoes.value.dias_critico < 1) {
    erros.value.dias_critico = 'Dias críticos é obrigatório (mínimo 1)'
  }

  // Validar ordem dos prazos
  if (configuracoes.value.dias_inadimplencia && configuracoes.value.dias_critico) {
    if (configuracoes.value.dias_critico <= configuracoes.value.dias_inadimplencia) {
      erros.value.dias_critico = 'Dias críticos deve ser maior que dias de inadimplência'
    }
  }

  // Validar multa
  if (configuracoes.value.multa_atraso === '' || configuracoes.value.multa_atraso < 0) {
    erros.value.multa_atraso = 'Multa por atraso é obrigatória (mínimo 0%)'
  }

  // Validar juros
  if (configuracoes.value.juros_mensal === '' || configuracoes.value.juros_mensal < 0) {
    erros.value.juros_mensal = 'Juros mensais é obrigatório (mínimo 0%)'
  }

  // Validar frequência de lembretes
  if (!configuracoes.value.frequencia_lembretes) {
    erros.value.frequencia_lembretes = 'Frequência de lembretes é obrigatória'
  }

  // Validar máximo de tentativas
  if (configuracoes.value.max_tentativas_contato === '' || configuracoes.value.max_tentativas_contato < 1) {
    erros.value.max_tentativas_contato = 'Máximo de tentativas é obrigatório (mínimo 1)'
  }

  return Object.keys(erros.value).length === 0
}

// ===== FUNÇÕES DA API =====

/**
 * Carrega as configurações de inadimplência da API
 */
const carregarConfiguracoes = async () => {
  carregando.value = true
  erro.value = ''

  try {
    const raw = localStorage.getItem(storageKey())
    if (raw) {
      const data = JSON.parse(raw)
      configuracoes.value = {
        dias_alerta_preventivo: data.dias_alerta_preventivo ?? '',
        dias_inadimplencia: data.dias_inadimplencia ?? '',
        dias_critico: data.dias_critico ?? '',
        multa_atraso: data.multa_atraso ?? '',
        juros_mensal: data.juros_mensal ?? '',
        bloquear_agendamentos: data.bloquear_agendamentos || false,
        enviar_lembretes: data.enviar_lembretes || false,
        aplicar_juros_automatico: data.aplicar_juros_automatico || false,
        frequencia_lembretes: data.frequencia_lembretes || '',
        max_tentativas_contato: data.max_tentativas_contato ?? '',
        canais_comunicacao: data.canais_comunicacao || []
      }
    }

    configuracoesOriginais.value = { ...configuracoes.value }

  } catch (error) {
    console.error('Erro ao carregar configurações:', error)
    erro.value = 'Erro ao carregar configurações salvas neste navegador.'
    configuracoesOriginais.value = { ...configuracoes.value }

  } finally {
    carregando.value = false
  }
}

/**
 * Salva as configurações na API
 */
const salvarConfiguracoes = async () => {
  if (!validarFormulario()) {
    mensagemErro.value = 'Por favor, corrija os erros no formulário'
    limparMensagens()
    return
  }

  salvando.value = true
  mensagemErro.value = ''

  try {
    // Prepara os dados para envio
    const dadosParaEnvio = {
      dias_alerta_preventivo: parseInt(configuracoes.value.dias_alerta_preventivo),
      dias_inadimplencia: parseInt(configuracoes.value.dias_inadimplencia),
      dias_critico: parseInt(configuracoes.value.dias_critico),
      multa_atraso: parseFloat(configuracoes.value.multa_atraso),
      juros_mensal: parseFloat(configuracoes.value.juros_mensal),
      bloquear_agendamentos: configuracoes.value.bloquear_agendamentos,
      enviar_lembretes: configuracoes.value.enviar_lembretes,
      aplicar_juros_automatico: configuracoes.value.aplicar_juros_automatico,
      frequencia_lembretes: configuracoes.value.frequencia_lembretes,
      max_tentativas_contato: parseInt(configuracoes.value.max_tentativas_contato),
      canais_comunicacao: configuracoes.value.canais_comunicacao
    }

    localStorage.setItem(storageKey(), JSON.stringify(dadosParaEnvio))
    configuracoesOriginais.value = { ...configuracoes.value }

    mensagemSucesso.value = 'Configurações de inadimplência salvas com sucesso!'
    limparMensagens()

  } catch (error) {
    console.error('Erro ao salvar configurações:', error)
    mensagemErro.value = 'Erro ao salvar configurações'
    limparMensagens()

  } finally {
    salvando.value = false
  }
}

/**
 * Reseta as configurações para os valores originais
 */
const resetarConfiguracoes = () => {
  configuracoes.value = { ...configuracoesOriginais.value }
  erros.value = {}
  mensagemErro.value = ''
  mensagemSucesso.value = ''
}

// ===== INICIALIZAÇÃO =====

/**
 * Carrega as configurações quando o componente é montado
 */
onMounted(() => {
  console.log('Componente Inadimplentes montado')
  carregarConfiguracoes()
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

/* Estilo personalizado para toggles */
.peer:checked~div {
  background-color: #dc2626;
}

.peer:focus~div {
  box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.2);
}

/* Hover effects para os cards */
label:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>