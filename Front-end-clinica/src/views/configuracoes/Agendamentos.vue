<template>
  <div class="max-w-4xl mx-auto p-6">
    <!-- Header -->
    <PageHeader :title="isEdicao ? 'Editar Configuração' : 'Nova Configuração'"
      :description="isEdicao ? 'Edite os horários e disponibilidade' : 'Configure os horários e disponibilidade para consultas'"
      :icon="Calendar" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Configurações de Agendamentos', to: '/configuracoes/agendamentos' },
        { label: isEdicao ? 'Editar Configuração' : 'Nova Configuração' }
      ]" class="mb-8" />

    <!-- Loading State -->
    <BaseCard v-if="carregando" padding="lg" class="text-center">
      <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <Loader2 class="w-6 h-6 text-blue-600 animate-spin" />
      </div>
      <p class="text-gray-600">Carregando configurações...</p>
    </BaseCard>

    <!-- Error State -->
    <BaseCard v-else-if="erro" padding="md" class="mb-6 bg-red-50 border-red-200">
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
    </BaseCard>

    <!-- Formulário -->
    <form v-else @submit.prevent="salvarConfiguracoes" class="space-y-8">
      <!-- Tipo de Configuração -->
      <BaseCard padding="md">
        <template #header>
          <h2 class="text-lg font-semibold text-gray-900 flex items-center space-x-2">
            <Settings class="w-5 h-5 text-blue-600" />
            <span>Tipo de Configuração</span>
          </h2>
        </template>

        <div class="space-y-4">
          <!-- Configuração Padrão -->
          <label
            class="flex items-center space-x-3 p-4 border-2 rounded-lg cursor-pointer transition-all hover:bg-gray-50"
            :class="configuracoes.user_id === null ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
            <input type="radio" :value="null" @change="selecionarConfiguracaoPadrao"
              :checked="configuracoes.user_id === null" class="sr-only" />
            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
              :class="configuracoes.user_id === null ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
              <div v-if="configuracoes.user_id === null" class="w-2 h-2 bg-white rounded-full"></div>
            </div>
            <div>
              <div class="font-medium text-gray-900">Configuração Padrão</div>
              <div class="text-sm text-gray-500">Aplicada a todos os profissionais que não possuem uma configuração
              </div>
            </div>
          </label>

          <!-- Configuração Personalizada -->
          <label
            class="flex items-center space-x-3 p-4 border-2 rounded-lg cursor-pointer transition-all hover:bg-gray-50"
            :class="configuracoes.user_id !== null ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
            <input type="radio" :value="'personalizada'" @change="selecionarConfiguracaoPersonalizada"
              :checked="configuracoes.user_id !== null" class="sr-only" />
            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
              :class="configuracoes.user_id !== null ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
              <div v-if="configuracoes.user_id !== null" class="w-2 h-2 bg-white rounded-full"></div>
            </div>
            <div>
              <div class="font-medium text-gray-900">Configuração Personalizada</div>
              <div class="text-sm text-gray-500">Aplicada a um profissional específico</div>
            </div>
          </label>

          <!-- Seleção de Usuário (se personalizada) -->
          <div v-if="configuracoes.user_id !== null && configuracoes.user_id !== 'temp'">
            <BaseSelect v-model="configuracoes.user_id" label="Selecione o Profissional" :options="opcoesUsuarios"
              :disabled="carregandoUsuarios" />
            <div v-if="carregandoUsuarios" class="mt-2 text-sm text-gray-500">
              Carregando profissionais...
            </div>
          </div>

          <!-- Mensagem de carregamento quando seleciona personalizada mas não há usuários -->
          <div v-if="configuracoes.user_id === 'temp'" class="ml-7">
            <div class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500 text-sm">
              Carregando profissionais...
            </div>
          </div>
        </div>
      </BaseCard>

      <!-- Dias Disponíveis -->
      <BaseCard padding="md">
        <template #header>
          <h2 class="text-lg font-semibold text-gray-900 flex items-center space-x-2">
            <CalendarDays class="w-5 h-5 text-blue-600" />
            <span>Dias da Semana Disponíveis</span>
          </h2>
        </template>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
          <label v-for="(dia, index) in diasSemana" :key="index"
            class="flex flex-col items-center p-3 border-2 rounded-lg cursor-pointer transition-all hover:bg-gray-50"
            :class="getDiaAtivo(index)
              ? 'border-blue-500 bg-blue-50 text-blue-700'
              : 'border-gray-200 text-gray-600'">
            <input type="checkbox" :checked="getDiaAtivo(index)" @change="toggleDia(index)" class="sr-only" />
            <span class="text-xs font-medium uppercase tracking-wide">{{ dia.abrev }}</span>
            <span class="text-sm mt-1">{{ dia.nome }}</span>
          </label>
        </div>

        <div v-if="erros.dias_disponiveis" class="mt-2 text-sm text-red-600 flex items-center space-x-1">
          <AlertCircle class="w-4 h-4" />
          <span>{{ erros.dias_disponiveis }}</span>
        </div>
      </BaseCard>

      <!-- Horários de Atendimento -->
      <BaseCard padding="md">
        <template #header>
          <h2 class="text-lg font-semibold text-gray-900 flex items-center space-x-2">
            <Clock class="w-5 h-5 text-blue-600" />
            <span>Horários de Atendimento</span>
          </h2>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Horário de Início -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Horário de Início
            </label>
            <div class="relative">
              <Clock class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input type="time" v-model="configuracoes.horario_inicio"
                class="block w-full pl-10 pr-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors bg-white"
                :class="erros.horario_inicio ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300'" />
            </div>
            <div v-if="erros.horario_inicio" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
              <AlertCircle class="w-4 h-4" />
              <span>{{ erros.horario_inicio }}</span>
            </div>
          </div>

          <!-- Horário de Fim -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Horário de Fim
            </label>
            <div class="relative">
              <Clock class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input type="time" v-model="configuracoes.horario_fim"
                class="block w-full pl-10 pr-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors bg-white"
                :class="erros.horario_fim ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300'" />
            </div>
            <div v-if="erros.horario_fim" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
              <AlertCircle class="w-4 h-4" />
              <span>{{ erros.horario_fim }}</span>
            </div>
          </div>
        </div>

        <!-- Preview dos Horários -->
        <div v-if="configuracoes.horario_inicio && configuracoes.horario_fim" class="mt-4 p-4 bg-blue-50 rounded-lg">
          <div class="flex items-center space-x-2 text-blue-700">
            <Info class="w-4 h-4" />
            <span class="text-sm font-medium">
              Atendimento das {{ configuracoes.horario_inicio }} às {{ configuracoes.horario_fim }}
              ({{ calcularHorasAtendimento() }} horas de atendimento)
            </span>
          </div>
        </div>
      </BaseCard>

      <!-- Configurações de Consulta -->
      <BaseCard padding="md">
        <template #header>
          <h2 class="text-lg font-semibold text-gray-900 flex items-center space-x-2">
            <Timer class="w-5 h-5 text-blue-600" />
            <span>Configurações de Consulta</span>
          </h2>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Duração da Consulta -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Duração Padrão da Consulta (minutos)
            </label>
            <div class="relative">
              <Timer class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <select v-model="configuracoes.duracao_consulta"
                class="block w-full pl-10 pr-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors appearance-none bg-white base-select-style"
                :class="erros.duracao_consulta ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300'">
                <option value="">Selecione a duração</option>
                <option value="15">15 minutos</option>
                <option value="20">20 minutos</option>
                <option value="30">30 minutos</option>
                <option value="45">45 minutos</option>
                <option value="60">1 hora</option>
                <option value="90">1 hora e 30 minutos</option>
                <option value="120">2 horas</option>
              </select>
            </div>
            <div v-if="erros.duracao_consulta" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
              <AlertCircle class="w-4 h-4" />
              <span>{{ erros.duracao_consulta }}</span>
            </div>
          </div>

          <!-- Intervalo entre Consultas -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Intervalo entre Consultas (minutos)
            </label>
            <div class="relative">
              <Pause class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <select v-model="configuracoes.intervalo_consulta"
                class="block w-full pl-10 pr-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors appearance-none bg-white base-select-style"
                :class="erros.intervalo_consulta ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300'">
                <option value="">Selecione o intervalo</option>
                <option value="0">Sem intervalo</option>
                <option value="5">5 minutos</option>
                <option value="10">10 minutos</option>
                <option value="15">15 minutos</option>
                <option value="20">20 minutos</option>
                <option value="30">30 minutos</option>
              </select>
            </div>
            <div v-if="erros.intervalo_consulta" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
              <AlertCircle class="w-4 h-4" />
              <span>{{ erros.intervalo_consulta }}</span>
            </div>
          </div>
        </div>

        <!-- Preview das Configurações -->
        <div v-if="configuracoes.duracao_consulta && configuracoes.intervalo_consulta !== ''"
          class="mt-4 p-4 bg-green-50 rounded-lg">
          <div class="flex items-center space-x-2 text-green-700">
            <CheckCircle class="w-4 h-4" />
            <span class="text-sm font-medium">
              Consultas de {{ configuracoes.duracao_consulta }} minutos
              {{ configuracoes.intervalo_consulta > 0 ? `com intervalo de ${configuracoes.intervalo_consulta} minutos` :
                'sem intervalo' }}
              ({{ calcularConsultasPorHora() }} consultas por hora)
            </span>
          </div>
        </div>
      </BaseCard>

      <!-- Pausas no Horário -->
      <BaseCard padding="md">
        <template #header>
          <h2 class="text-lg font-semibold text-gray-900 flex items-center space-x-2">
            <Coffee class="w-5 h-5 text-blue-600" />
            <span>Pausas no Horário de Funcionamento</span>
          </h2>
        </template>

        <div class="space-y-4">
          <!-- Lista de pausas existentes -->
          <div v-if="configuracoes.pausas.length > 0" class="space-y-3">
            <div v-for="(pausa, index) in configuracoes.pausas" :key="index" class="p-3 rounded-lg border"
              :class="validarPausaIndividual(pausa, index) ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200'">
              <div class="flex items-start space-x-3">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                  <div>
                    <BaseInput v-model="pausa.nome" type="text" label="Nome da pausa" placeholder="Ex: Almoço"
                      :error="validarPausaIndividual(pausa, index)" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Início</label>
                    <input v-model="pausa.inicio" type="time"
                      class="block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors bg-white"
                      :class="validarPausaIndividual(pausa, index) ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300'" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fim</label>
                    <input v-model="pausa.fim" type="time"
                      class="block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors bg-white"
                      :class="validarPausaIndividual(pausa, index) ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300'" />
                  </div>
                </div>
                <button @click="removerPausa(index)" type="button"
                  class="p-2 text-red-600 hover:bg-red-50 rounded-md transition-colors mt-6" title="Remover pausa">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>

              <!-- Mensagem de erro individual -->
              <div v-if="validarPausaIndividual(pausa, index)" class="mt-3">
                <div class="text-xs text-red-600 flex items-center space-x-1">
                  <AlertCircle class="w-3 h-3" />
                  <span>{{ validarPausaIndividual(pausa, index) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Mensagem quando não há pausas -->
          <div v-else class="text-center py-8 text-gray-500">
            <Coffee class="w-12 h-12 mx-auto mb-3 text-gray-300" />
            <p class="text-sm">Nenhuma pausa configurada</p>
            <p class="text-xs text-gray-400 mt-1">Clique em "Adicionar Pausa" para criar</p>
          </div>

          <!-- Botão para adicionar nova pausa -->
          <button @click="adicionarPausa" type="button"
            class="w-full flex items-center justify-center space-x-2 px-4 py-3 border-2 border-dashed border-gray-300 text-gray-600 rounded-lg hover:border-blue-400 hover:text-blue-600 transition-colors">
            <Plus class="w-4 h-4" />
            <span>Adicionar Pausa</span>
          </button>

          <!-- Avisos de validação -->
          <div v-if="erros.pausas" class="text-sm text-red-600 flex items-center space-x-1">
            <AlertCircle class="w-4 h-4" />
            <span>{{ erros.pausas }}</span>
          </div>
        </div>
      </BaseCard>

      <!-- Resumo das Configurações -->
      <BaseCard v-if="configuracaoCompleta" padding="md"
        class="bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200">
        <template #header>
          <h3 class="text-lg font-semibold text-blue-900 flex items-center space-x-2">
            <Settings class="w-5 h-5" />
            <span>Resumo das Configurações</span>
          </h3>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-blue-700 font-medium">Dias disponíveis:</span>
              <span class="text-blue-900">{{ diasSelecionados }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-blue-700 font-medium">Horário:</span>
              <span class="text-blue-900">{{ configuracoes.horario_inicio }} - {{ configuracoes.horario_fim }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-blue-700 font-medium">Pausas:</span>
              <span class="text-blue-900">{{ configuracoes.pausas.length }} configurada(s)</span>
            </div>
          </div>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-blue-700 font-medium">Duração consulta:</span>
              <span class="text-blue-900">{{ configuracoes.duracao_consulta }} min</span>
            </div>
            <div class="flex justify-between">
              <span class="text-blue-700 font-medium">Intervalo:</span>
              <span class="text-blue-900">{{ configuracoes.intervalo_consulta }} min</span>
            </div>
          </div>
        </div>

        <!-- Lista de pausas no resumo -->
        <div v-if="configuracoes.pausas.length > 0" class="mt-4 pt-4 border-t border-blue-200">
          <h4 class="text-sm font-medium text-blue-700 mb-2">Pausas configuradas:</h4>
          <div class="space-y-1">
            <div v-for="pausa in configuracoes.pausas.filter(p => p.nome && p.inicio && p.fim)"
              :key="pausa.nome + pausa.inicio" class="flex justify-between text-xs text-blue-600">
              <span>{{ pausa.nome }}</span>
              <span>{{ pausa.inicio }} - {{ pausa.fim }}</span>
            </div>
          </div>
        </div>
      </BaseCard>

      <!-- Botões de Ação -->
      <div class="flex items-center justify-between pt-6 border-t border-gray-200">
        <BaseButton type="button" variant="ghost" size="md" :icon="ArrowLeft" icon-position="left"
          @click="voltarParaLista" :class="temMudancasNaoSalvas() ? 'text-orange-600 hover:text-orange-800' : ''">
          {{ temMudancasNaoSalvas() ? 'Voltar (alterações não salvas)' : 'Voltar para Lista' }}
        </BaseButton>

        <div class="flex items-center space-x-3">
          <BaseButton type="button" variant="ghost" size="md" @click="resetarFormulario">
            Resetar
          </BaseButton>
          <BaseButton type="submit" variant="primary" size="md" :loading="salvando" :disabled="!configuracaoCompleta"
            :icon="Save" icon-position="left" :hide-loading-text="true">
            {{ salvando ? 'Salvando...' : (isEdicao ? 'Atualizar' : 'Criar') }}
          </BaseButton>
        </div>
      </div>
    </form>

    <!-- Modal de Confirmação para Conflitos Temporais usando ActionModal -->
    <ActionModal :open="showModalConfirmacao" titulo="Confirmação Necessária"
      subtitulo="Nova configuração com período de transição" action-label="Confirmar Nova Configuração"
      :action-disabled="salvando" modal-width="sm:max-w-md" @acao="confirmarNovaConfiguracao"
      @cancel="cancelarConfirmacao">
      <div class="space-y-4">
        <div class="flex items-center space-x-3 mb-4">
          <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
            <AlertCircle class="w-6 h-6 text-yellow-600" />
          </div>
          <div class="flex-1">
            <p class="text-gray-700">{{ dadosConfirmacao.mensagem }}</p>
          </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <div class="flex items-center space-x-2 mb-2">
            <Info class="w-4 h-4 text-yellow-600" />
            <span class="text-sm font-medium text-yellow-800">Detalhes da Transição</span>
          </div>
          <div class="text-sm text-yellow-700 space-y-1">
            <p><strong>Data de início sugerida:</strong> {{ formatarData(dadosConfirmacao.dataInicioSugerida) }}</p>
            <p><strong>Consultas afetadas:</strong> {{ dadosConfirmacao.consultasAfetadas }}</p>
          </div>
        </div>
      </div>
    </ActionModal>

    <!-- Modal de Confirmação para Sair sem Salvar -->
    <ActionModal :open="showModalConfirmarSair" titulo="Alterações não salvas"
      subtitulo="Você tem alterações não salvas. Deseja realmente sair sem salvar?" action-label="Sair sem Salvar"
      action-variant="danger" border-color="danger" modal-width="sm:max-w-md" @acao="confirmarSairSemSalvar"
      @cancel="cancelarSairSemSalvar">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
          <AlertCircle class="w-6 h-6 text-red-600" />
        </div>
        <div class="flex-1">
          <p class="text-gray-700">Todas as alterações não salvas serão perdidas.</p>
        </div>
      </div>
    </ActionModal>

    <!-- Modal de Confirmação para Resetar Formulário -->
    <ActionModal :open="showModalConfirmarResetar" titulo="Descartar alterações"
      subtitulo="Deseja realmente descartar todas as alterações?" action-label="Descartar Alterações"
      action-variant="danger" border-color="danger" modal-width="sm:max-w-md" @acao="confirmarResetarFormulario"
      @cancel="cancelarResetarFormulario">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
          <AlertCircle class="w-6 h-6 text-red-600" />
        </div>
        <div class="flex-1">
          <p class="text-gray-700">Todas as alterações serão descartadas e o formulário voltará ao estado original.</p>
        </div>
      </div>
    </ActionModal>
  </div>
</template>

<script>
import axios from '../../services/axios.js'
import {
  Calendar, CalendarDays, Clock, Timer, Pause, Settings, Save,
  CheckCircle, AlertCircle, Info, Loader2, Plus, Trash2, Coffee, ArrowLeft
} from 'lucide-vue-next'
import { toastSuccess, toastError } from '../../composables/useToast.js'

export default {
  name: 'Agendamentos',
  components: {
    Calendar,
    CalendarDays,
    Clock,
    Timer,
    Pause,
    Settings,
    Save,
    CheckCircle,
    AlertCircle,
    Info,
    Loader2,
    Plus,
    Trash2,
    Coffee,
    ArrowLeft
  },
  data() {
    return {
      // Componentes para props (Vue 2)
      Calendar,
      Save,
      ArrowLeft,

      // ===== ESTADO REATIVO =====
      carregando: false,
      salvando: false,
      erro: '',

      // Configurações do agendamento
      configuracoes: {
        user_id: null, // null = configuração padrão, número = configuração personalizada
        seg: false,
        ter: false,
        qua: false,
        qui: false,
        sex: false,
        sab: false,
        dom: false,
        horario_inicio: '',
        horario_fim: '',
        duracao_consulta: '',
        intervalo_consulta: '',
        pausas: []
      },

      // Backup das configurações originais
      configuracoesOriginais: {},

      // Erros de validação
      erros: {},

      // Usuários disponíveis para configurações personalizadas
      usuarios: [],
      carregandoUsuarios: false,

      // Modal de confirmação para conflitos temporais
      showModalConfirmacao: false,
      dadosConfirmacao: {
        mensagem: '',
        dataInicioSugerida: '',
        consultasAfetadas: 0
      },

      // Modais de confirmação para ações
      showModalConfirmarSair: false,
      showModalConfirmarResetar: false,

      // ===== DADOS AUXILIARES =====
      diasSemana: [
        { nome: 'Domingo', abrev: 'Dom' },
        { nome: 'Segunda', abrev: 'Seg' },
        { nome: 'Terça', abrev: 'Ter' },
        { nome: 'Quarta', abrev: 'Qua' },
        { nome: 'Quinta', abrev: 'Qui' },
        { nome: 'Sexta', abrev: 'Sex' },
        { nome: 'Sábado', abrev: 'Sáb' }
      ]
    }
  },
  computed: {
    /**
     * Detectar modo (criação ou edição)
     */
    configuracaoId() {
      return this.$route.params.id
    },
    isEdicao() {
      return !!this.configuracaoId
    },
    isNovaConfiguracao() {
      return this.$route.path.includes('/novo')
    },
    /**
     * Verifica se todas as configurações obrigatórias estão preenchidas
     */
    configuracaoCompleta() {
      return this.temDiasSelecionados() &&
        this.configuracoes.horario_inicio &&
        this.configuracoes.horario_fim &&
        this.configuracoes.duracao_consulta &&
        this.configuracoes.intervalo_consulta !== ''
    },
    /**
     * Retorna os dias selecionados em formato legível
     */
    diasSelecionados() {
      if (!this.temDiasSelecionados()) return 'Nenhum'

      const nomes = []
      this.diasSemana.forEach((dia, index) => {
        if (this.getDiaAtivo(index)) {
          nomes.push(dia.abrev)
        }
      })

      return nomes.join(', ')
    },
    /**
     * Formata os usuários como opções para o BaseSelect
     */
    opcoesUsuarios() {
      const opcoes = [
        { value: '', label: 'Selecione um profissional' }
      ]

      if (this.usuarios && Array.isArray(this.usuarios)) {
        this.usuarios.forEach(usuario => {
          opcoes.push({
            value: usuario.id,
            label: `${usuario.name} (${usuario.email})`
          })
        })
      }

      return opcoes
    }
  },

  methods: {
    // ===== FUNÇÕES UTILITÁRIAS =====

    /**
     * Mapeia índice do dia para campo booleano
     */
    getCampoDia(index) {
      const campos = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sab']
      return campos[index]
    },

    /**
     * Verifica se um dia está ativo
     */
    getDiaAtivo(index) {
      const campo = this.getCampoDia(index)
      return this.configuracoes[campo]
    },

    /**
     * Alterna o estado de um dia
     */
    toggleDia(index) {
      const campo = this.getCampoDia(index)
      this.configuracoes[campo] = !this.configuracoes[campo]
    },

    /**
     * Verifica se há pelo menos um dia selecionado
     */
    temDiasSelecionados() {
      return this.configuracoes.seg || this.configuracoes.ter || this.configuracoes.qua ||
        this.configuracoes.qui || this.configuracoes.sex || this.configuracoes.sab ||
        this.configuracoes.dom
    },

    /**
     * Calcula o total de horas de atendimento
     */
    calcularHorasAtendimento() {
      if (!this.configuracoes.horario_inicio || !this.configuracoes.horario_fim) return '0'

      const inicio = new Date(`2000-01-01T${this.configuracoes.horario_inicio}:00`)
      const fim = new Date(`2000-01-01T${this.configuracoes.horario_fim}:00`)

      const diffMs = fim - inicio
      const diffHoras = diffMs / (1000 * 60 * 60)

      return diffHoras.toFixed(1)
    },

    /**
     * Calcula quantas consultas cabem por hora
     */
    calcularConsultasPorHora() {
      if (!this.configuracoes.duracao_consulta || this.configuracoes.intervalo_consulta === '') return '0'

      const duracaoTotal = parseInt(this.configuracoes.duracao_consulta) + parseInt(this.configuracoes.intervalo_consulta)
      const consultasPorHora = Math.floor(60 / duracaoTotal)

      return consultasPorHora
    },


    // ===== FUNÇÕES DE PAUSAS =====

    /**
     * Adiciona uma nova pausa ao array
     */
    adicionarPausa() {
      this.configuracoes.pausas.push({
        nome: '',
        inicio: '',
        fim: ''
      })
    },

    /**
     * Remove uma pausa específica do array
     */
    removerPausa(index) {
      this.configuracoes.pausas.splice(index, 1)
    },

    /**
     * Valida uma pausa individual
     */
    validarPausaIndividual(pausa, index) {
      const horarioInicio = this.configuracoes.horario_inicio
      const horarioFim = this.configuracoes.horario_fim

      // Se a pausa não tem todos os campos, retorna válida (será ignorada)
      if (!pausa.nome || !pausa.inicio || !pausa.fim || !horarioInicio || !horarioFim) {
        return null
      }

      // Validar se início é menor que fim
      if (pausa.inicio >= pausa.fim) {
        return `Horário de fim deve ser maior que o início`
      }

      // Validar se está dentro do horário de funcionamento
      if (pausa.inicio < horarioInicio) {
        return `Não pode começar antes das ${horarioInicio}`
      }

      if (pausa.fim > horarioFim) {
        return `Não pode terminar depois das ${horarioFim}`
      }

      // Validar sobreposição com outras pausas
      const outrasPausas = this.configuracoes.pausas.filter((p, i) => i !== index && p.nome && p.inicio && p.fim)

      for (const outraPausa of outrasPausas) {
        const seSobrepoem = (
          (pausa.inicio < outraPausa.fim && pausa.fim > outraPausa.inicio)
        )

        if (seSobrepoem) {
          return `Sobreposição com "${outraPausa.nome}"`
        }
      }

      return null
    },

    /**
     * Valida se há sobreposição entre pausas e se estão dentro do horário de funcionamento
     */
    validarPausas() {
      const pausas = this.configuracoes.pausas
      const horarioInicio = this.configuracoes.horario_inicio
      const horarioFim = this.configuracoes.horario_fim

      // Se não há horário de funcionamento definido, não validar pausas
      if (!horarioInicio || !horarioFim) {
        return null
      }

      for (let i = 0; i < pausas.length; i++) {
        const pausa = pausas[i]

        // Se a pausa não tem todos os campos preenchidos, pular validação (será filtrada na hora de salvar)
        if (!pausa.nome || !pausa.inicio || !pausa.fim) {
          continue
        }

        // Validar se início é menor que fim
        if (pausa.inicio >= pausa.fim) {
          return `A pausa "${pausa.nome}" deve ter horário de fim maior que o início`
        }

        // Validar se a pausa está dentro do horário de funcionamento
        if (pausa.inicio < horarioInicio) {
          return `A pausa "${pausa.nome}" não pode começar antes do horário de funcionamento (${horarioInicio})`
        }

        if (pausa.fim > horarioFim) {
          return `A pausa "${pausa.nome}" não pode terminar após o horário de funcionamento (${horarioFim})`
        }

        // Validar sobreposição com outras pausas
        for (let j = i + 1; j < pausas.length; j++) {
          const outraPausa = pausas[j]

          // Pular se a outra pausa não está completa
          if (!outraPausa.nome || !outraPausa.inicio || !outraPausa.fim) {
            continue
          }

          // Verificar sobreposição: duas pausas se sobrepõem se uma começa antes da outra terminar
          // e termina depois da outra começar
          const seSobrepoem = (
            (pausa.inicio < outraPausa.fim && pausa.fim > outraPausa.inicio) ||
            (outraPausa.inicio < pausa.fim && outraPausa.fim > pausa.inicio)
          )

          if (seSobrepoem) {
            return `As pausas "${pausa.nome}" (${pausa.inicio}-${pausa.fim}) e "${outraPausa.nome}" (${outraPausa.inicio}-${outraPausa.fim}) se sobrepõem`
          }
        }
      }

      return null
    },

    // ===== VALIDAÇÃO =====

    /**
     * Valida todos os campos do formulário
     */
    validarFormulario() {
      this.erros = {}

      // Validar dias disponíveis
      if (!this.temDiasSelecionados()) {
        this.erros.dias_disponiveis = 'Selecione pelo menos um dia da semana'
      }

      // Validar horário de início
      if (!this.configuracoes.horario_inicio) {
        this.erros.horario_inicio = 'Horário de início é obrigatório'
      }

      // Validar horário de fim
      if (!this.configuracoes.horario_fim) {
        this.erros.horario_fim = 'Horário de fim é obrigatório'
      }

      // Validar se horário de fim é maior que início
      if (this.configuracoes.horario_inicio && this.configuracoes.horario_fim) {
        if (this.configuracoes.horario_fim <= this.configuracoes.horario_inicio) {
          this.erros.horario_fim = 'Horário de fim deve ser maior que o de início'
        }
      }

      // Validar duração da consulta
      if (!this.configuracoes.duracao_consulta) {
        this.erros.duracao_consulta = 'Duração da consulta é obrigatória'
      }

      // Validar intervalo entre consultas
      if (this.configuracoes.intervalo_consulta === '') {
        this.erros.intervalo_consulta = 'Intervalo entre consultas é obrigatório'
      }

      // Validar pausas
      const erroPausas = this.validarPausas()
      if (erroPausas) {
        this.erros.pausas = erroPausas
      }

      return Object.keys(this.erros).length === 0
    },

    // ===== FUNÇÕES DA API =====

    /**
     * Normaliza horário da API (ISO / H:i:s) para input type="time" (HH:MM)
     */
    extrairHora(valor) {
      if (!valor) return ''
      if (typeof valor === 'string') {
        if (valor.includes('T')) {
          return valor.split('T')[1].substring(0, 5)
        }
        if (/^\d{2}:\d{2}/.test(valor)) {
          return valor.substring(0, 5)
        }
      }
      return String(valor)
    },

    /**
     * Carrega as configurações de agendamento da API
     */
    async carregarConfiguracoes() {
      this.carregando = true
      this.erro = ''

      try {
        if (this.isEdicao) {
          // Modo edição: carregar configuração específica
          const response = await axios.get(`/configuracoes-agendamento/${this.configuracaoId}`)
          const config = response.data

          this.configuracoes = {
            user_id: config.user_id,
            seg: !!config.seg,
            ter: !!config.ter,
            qua: !!config.qua,
            qui: !!config.qui,
            sex: !!config.sex,
            sab: !!config.sab,
            dom: !!config.dom,
            horario_inicio: this.extrairHora(config.horario_inicio),
            horario_fim: this.extrairHora(config.horario_fim),
            duracao_consulta: (config.duracao_consulta && config.duracao_consulta.toString()) || '',
            intervalo_consulta: (config.intervalo_consulta !== null && config.intervalo_consulta !== undefined)
              ? config.intervalo_consulta.toString()
              : '',
            pausas: config.pausas || []
          }
        } else {
          // Modo criação: usar configurações padrão
          this.configuracoes = {
            user_id: null, // Padrão: configuração padrão
            seg: true,
            ter: true,
            qua: true,
            qui: true,
            sex: true,
            sab: false,
            dom: false,
            horario_inicio: '08:00',
            horario_fim: '18:00',
            duracao_consulta: '30',
            intervalo_consulta: '10',
            pausas: []
          }
        }

        // Salva backup das configurações originais
        this.configuracoesOriginais = { ...this.configuracoes }

        console.log('Configurações carregadas:', this.configuracoes)

      } catch (error) {
        console.error('Erro ao carregar configurações:', error)

        if (error.response) {
          const errorData = error.response.data
          this.erro = `Erro do servidor: ${error.response.status} - ${(errorData && errorData.message) || (errorData && errorData.error) || 'Erro desconhecido'}`
        } else if (error.request) {
          this.erro = 'Erro de conexão. Verifique sua internet e tente novamente.'
        } else {
          this.erro = 'Erro inesperado. Tente novamente.'
        }

        // Dados padrão para desenvolvimento
        console.warn('Usando configurações padrão para desenvolvimento')
        this.configuracoes = {
          user_id: null,
          seg: true,
          ter: true,
          qua: true,
          qui: true,
          sex: true,
          sab: false,
          dom: false,
          horario_inicio: '08:00',
          horario_fim: '18:00',
          duracao_consulta: '30',
          intervalo_consulta: '10',
          pausas: []
        }
        this.configuracoesOriginais = { ...this.configuracoes }

      } finally {
        this.carregando = false
      }
    },

    /**
     * Salva as configurações na API
     */
    async salvarConfiguracoes() {
      if (!this.validarFormulario()) {
        toastError('Por favor, corrija os erros no formulário')
        return
      }

      this.salvando = true

      try {
        // Prepara os dados para envio
        const dadosParaEnvio = {
          user_id: this.configuracoes.user_id,
          seg: this.configuracoes.seg ? 1 : 0,
          ter: this.configuracoes.ter ? 1 : 0,
          qua: this.configuracoes.qua ? 1 : 0,
          qui: this.configuracoes.qui ? 1 : 0,
          sex: this.configuracoes.sex ? 1 : 0,
          sab: this.configuracoes.sab ? 1 : 0,
          dom: this.configuracoes.dom ? 1 : 0,
          horario_inicio: this.configuracoes.horario_inicio,
          horario_fim: this.configuracoes.horario_fim,
          duracao_consulta: parseInt(this.configuracoes.duracao_consulta),
          intervalo_consulta: parseInt(this.configuracoes.intervalo_consulta),
          pausas: this.configuracoes.pausas.filter(p => p.nome && p.inicio && p.fim) // Filtra apenas pausas válidas
        }

        const response = await axios.post('/configuracoes-agendamento', dadosParaEnvio)

        // Verificar se precisa de confirmação para conflitos temporais
        if (response.data.precisa_confirmacao) {
          this.dadosConfirmacao = {
            mensagem: response.data.mensagem,
            dataInicioSugerida: response.data.data_inicio_sugerida,
            consultasAfetadas: response.data.consultas_afetadas
          }
          this.showModalConfirmacao = true
          return
        }

        // Atualiza o backup com as novas configurações
        this.configuracoesOriginais = { ...this.configuracoes }

        toastSuccess('Configurações salvas com sucesso!')

        // Redirecionar para a lista após sucesso
        setTimeout(() => {
          this.$router.push('/configuracoes/agendamentos')
        }, 1500)

        console.log('Configurações salvas:', response.data)

      } catch (error) {
        console.error('Erro ao salvar configurações:', error)

        if (error.response) {
          // Erros de validação do backend
          if (error.response.status === 422 && error.response.data.errors) {
            const backendErrors = error.response.data.errors
            this.erros = {}

            // Mapeia erros do backend para o frontend
            Object.keys(backendErrors).forEach(campo => {
              this.erros[campo] = backendErrors[campo][0]
            })

            toastError('Corrija os erros no formulário')
          } else {
            const errorData = error.response.data
            toastError(`Erro do servidor: ${(errorData && errorData.message) || (errorData && errorData.error) || 'Erro desconhecido'}`)
          }
        } else if (error.request) {
          toastError('Erro de conexão. Verifique sua internet e tente novamente.')
        } else {
          toastError('Erro inesperado. Tente novamente.')
        }

      } finally {
        this.salvando = false
      }
    },


    /**
     * Carrega lista de usuários para configurações personalizadas
     */
    async carregarUsuarios() {
      this.carregandoUsuarios = true

      try {
        // Carregar apenas usuários com profile_id = 3 (profissionais)
        const response = await axios.get('/usuarios', {
          params: {
            profile_id: 3
          }
        })
        this.usuarios = response.data.data || response.data

        // Se estava esperando usuários (valor temp), selecionar o primeiro
        if (this.configuracoes.user_id === 'temp' && this.usuarios.length > 0) {
          this.configuracoes.user_id = this.usuarios[0].id
        }
      } catch (error) {
        console.error('Erro ao carregar usuários:', error)
        this.usuarios = []

        // Se estava esperando usuários e deu erro, voltar para padrão
        if (this.configuracoes.user_id === 'temp') {
          this.configuracoes.user_id = null
        }
      } finally {
        this.carregandoUsuarios = false
      }
    },

    /**
     * Confirma criação de nova configuração com período de transição
     */
    async confirmarNovaConfiguracao() {
      try {
        const dadosParaEnvio = {
          user_id: this.configuracoes.user_id,
          data_inicio_vigencia: this.dadosConfirmacao.dataInicioSugerida,
          seg: this.configuracoes.seg ? 1 : 0,
          ter: this.configuracoes.ter ? 1 : 0,
          qua: this.configuracoes.qua ? 1 : 0,
          qui: this.configuracoes.qui ? 1 : 0,
          sex: this.configuracoes.sex ? 1 : 0,
          sab: this.configuracoes.sab ? 1 : 0,
          dom: this.configuracoes.dom ? 1 : 0,
          horario_inicio: this.configuracoes.horario_inicio,
          horario_fim: this.configuracoes.horario_fim,
          duracao_consulta: parseInt(this.configuracoes.duracao_consulta),
          intervalo_consulta: parseInt(this.configuracoes.intervalo_consulta),
          pausas: this.configuracoes.pausas.filter(p => p.nome && p.inicio && p.fim)
        }

        await axios.post('/configuracoes-agendamento/confirmar', dadosParaEnvio)

        this.showModalConfirmacao = false
        toastSuccess('Configuração criada com sucesso!')

        // Redirecionar para a lista após sucesso
        setTimeout(() => {
          this.$router.push('/configuracoes/agendamentos')
        }, 1500)

      } catch (error) {
        console.error('Erro ao confirmar configuração:', error)
        toastError('Erro ao confirmar configuração. Tente novamente.')
      }
    },

    /**
     * Cancela confirmação e fecha modal
     */
    cancelarConfirmacao() {
      this.showModalConfirmacao = false
      this.dadosConfirmacao = {
        mensagem: '',
        dataInicioSugerida: '',
        consultasAfetadas: 0
      }
    },

    /**
     * Navega de volta para a lista de configurações
     */
    voltarParaLista() {
      // Verificar se há mudanças não salvas
      if (this.temMudancasNaoSalvas()) {
        this.showModalConfirmarSair = true
      } else {
        this.$router.push('/configuracoes/agendamentos')
      }
    },

    /**
     * Confirma sair sem salvar
     */
    confirmarSairSemSalvar() {
      this.showModalConfirmarSair = false
      this.$router.push('/configuracoes/agendamentos')
    },

    /**
     * Cancela sair sem salvar
     */
    cancelarSairSemSalvar() {
      this.showModalConfirmarSair = false
    },

    /**
     * Verifica se há mudanças não salvas
     */
    temMudancasNaoSalvas() {
      const original = this.configuracoesOriginais
      const atual = this.configuracoes

      return JSON.stringify(original) !== JSON.stringify(atual)
    },

    /**
     * Seleciona configuração personalizada
     */
    selecionarConfiguracaoPersonalizada() {
      // Se não há usuários carregados, tentar carregar novamente
      if (this.usuarios.length === 0) {
        this.carregarUsuarios()
        return
      }

      // Selecionar o primeiro usuário disponível
      if (this.usuarios.length > 0) {
        this.configuracoes.user_id = this.usuarios[0].id
      } else {
        console.warn('Nenhum usuário disponível para configuração personalizada')
        // Mesmo assim, definir um valor temporário para mostrar o select
        this.configuracoes.user_id = 'temp'
      }
    },

    /**
     * Seleciona configuração padrão
     */
    selecionarConfiguracaoPadrao() {
      this.configuracoes.user_id = null
    },

    /**
     * Formata data para exibição
     */
    formatarData(data) {
      if (!data) return 'N/A'

      try {
        // Se for uma string de data ISO (2024-01-01T00:00:00.000000Z)
        if (typeof data === 'string' && data.includes('T')) {
          const dataObj = new Date(data)
          return dataObj.toLocaleDateString('pt-BR', {
            timeZone: 'UTC',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
          })
        }

        // Se for uma string de data simples (2024-01-01)
        if (typeof data === 'string' && data.match(/^\d{4}-\d{2}-\d{2}$/)) {
          const [ano, mes, dia] = data.split('-')
          return `${dia}/${mes}/${ano}`
        }

        // Se já for um objeto Date
        if (data instanceof Date) {
          return data.toLocaleDateString('pt-BR', {
            timeZone: 'UTC',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
          })
        }

        // Fallback para outros formatos
        const dataObj = new Date(data)
        return dataObj.toLocaleDateString('pt-BR', {
          timeZone: 'UTC',
          day: '2-digit',
          month: '2-digit',
          year: 'numeric'
        })
      } catch (error) {
        console.error('Erro ao formatar data:', error, data)
        return 'Data inválida'
      }
    },

    /**
     * Reseta o formulário para os valores originais
     */
    resetarFormulario() {
      if (this.temMudancasNaoSalvas()) {
        this.showModalConfirmarResetar = true
      } else {
        // Se não há mudanças, apenas limpa os erros
        this.erros = {}
      }
    },

    /**
     * Confirma resetar formulário
     */
    confirmarResetarFormulario() {
      this.showModalConfirmarResetar = false
      this.configuracoes = { ...this.configuracoesOriginais }
      this.erros = {}
    },

    /**
     * Cancela resetar formulário
     */
    cancelarResetarFormulario() {
      this.showModalConfirmarResetar = false
    }
  },
  mounted() {
    console.log('Componente Agendamentos montado')
    this.carregarConfiguracoes()
    this.carregarUsuarios()
  }
}
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

/* Estilo personalizado para checkboxes de dias da semana */
input[type="checkbox"]:checked+span {
  font-weight: 600;
}

/* Hover effects para os cards de dias */
label:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Estilo para selects com seta customizada (mesmo estilo do BaseSelect) */
.base-select-style {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}

.base-select-style:disabled {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
}
</style>