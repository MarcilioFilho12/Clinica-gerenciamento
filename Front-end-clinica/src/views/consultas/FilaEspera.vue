<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Fila de Espera" description="Gerenciamento de pacientes aguardando" :icon="ClockIcon"
      icon-bg-color="red" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Fila de Espera' }
      ]" class="mb-8">
      <template #actions>
        <div class="flex items-center space-x-4">
          <button 
            @click="abrirTelao"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors flex items-center space-x-2 text-sm font-medium">
            <Monitor class="w-4 h-4" />
            <span>Painel de Chamada</span>
          </button>
          <div class="text-sm text-gray-600">
            <span class="font-medium">{{ pacientesNaFila }}</span> pacientes aguardando
          </div>
          <div class="text-sm text-gray-600 font-medium">
            {{ new Date().toLocaleDateString('pt-BR', { weekday: 'short', day: 'numeric', month: 'short' }) }}
          </div>
        </div>
      </template>
    </PageHeader>
    <!-- Estatísticas Compartilhadas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <BaseCard>
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
            <Users class="w-6 h-6 text-orange-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Na Fila</p>
            <p class="text-2xl font-bold text-gray-900">{{ pacientesNaFila }}</p>
          </div>
        </div>
      </BaseCard>

      <BaseCard>
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <UserCheck class="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Atendidos Hoje</p>
            <p class="text-2xl font-bold text-gray-900">{{ atendidosHoje }}</p>
          </div>
        </div>
      </BaseCard>

      <BaseCard>
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
            <Timer class="w-6 h-6 text-indigo-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tempo Médio</p>
            <p class="text-2xl font-bold text-gray-900">{{ tempoMedioEspera }}</p>
          </div>
        </div>
      </BaseCard>

      <BaseCard>
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
            <AlertCircle class="w-6 h-6 text-red-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Prioridade</p>
            <p class="text-2xl font-bold text-gray-900">{{ pacientesPrioridade }}</p>
          </div>
        </div>
      </BaseCard>
    </div>

    <!-- Controles -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-4 flex-1">
          <!-- Busca -->
          <BaseInput 
            v-model="searchTerm" 
            type="text"
            placeholder="Buscar paciente na fila..."
            :icon="Search"
            icon-position="left"
          />

          <!-- Filtro por Profissional -->
          <BaseSelect 
            v-model="filtroProfissional"
            :options="opcoesProfissionaisFiltro"
            placeholder="Todos os profissionais"
            class="min-w-[200px]"
          />

          <!-- Filtro por Prioridade -->
          <BaseSelect 
            v-model="filtroPrioridade"
            :options="opcoesPrioridadeFiltro"
            placeholder="Todas as prioridades"
          />
        </div>

        <!-- Ações -->
        <div class="flex space-x-3">
          <button @click="adicionarPaciente"
            class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition-colors flex items-center space-x-2 text-sm font-medium">
            <Plus class="w-4 h-4" />
            <span>Adicionar à Fila</span>
          </button>

          <button @click="atualizarFila"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors flex items-center space-x-2 text-sm font-medium">
            <RefreshCw class="w-4 h-4" />
            <span>Atualizar</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Fila de Espera -->
    <div class="space-y-4">
      <div v-for="(paciente, index) in filaFiltrada" :key="paciente.id"
        class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200"
        :class="getPriorityBorderClass(paciente.prioridade)">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <!-- Posição na Fila -->
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white"
                  :class="getPriorityBgClass(paciente.prioridade)">
                  {{ index + 1 }}
                </div>
              </div>

              <!-- Informações do Paciente -->
              <div class="flex-1 space-y-2">
                <div class="flex items-center space-x-3">
                  <h3 class="text-lg font-semibold text-gray-900">{{ paciente.nomePaciente }}</h3>
                  
                  <span class="px-2 py-1 rounded-full text-xs font-medium"
                    :class="getPriorityClass(paciente.prioridade)">
                    {{ getPriorityLabel(paciente.prioridade) }}
                  </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                  <div class="flex items-center space-x-2">
                    <Timer class="w-4 h-4 text-gray-400" />
                    <span class="text-sm text-gray-600">{{ paciente.horaChegada }}</span>
                  </div>

                  <div class="flex items-center space-x-2">
                    <UserCheck class="w-4 h-4 text-gray-400" />
                    <span class="text-sm text-gray-600">{{ paciente.profissional }}</span>
                  </div>

                  <div class="flex items-center space-x-2">
                    <Phone class="w-4 h-4 text-gray-400" />
                    <span class="text-sm text-gray-600">{{ paciente.telefone }}</span>
                  </div>

                  <div class="flex items-center space-x-2">
                    <Timer class="w-4 h-4 text-gray-400" />
                    <span class="text-sm text-gray-600">{{ calcularTempoEspera(paciente.horaChegada) }}</span>
                  </div>
                </div>

                <!-- Tipo de Consulta e Observações -->
                <div class="pt-2 border-t border-gray-100">
                  <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                      <FileText class="w-4 h-4 text-gray-400" />
                      <span class="text-sm text-gray-600">{{ paciente.tipoConsulta }}</span>
                    </div>

                    <div v-if="paciente.observacoes" class="flex items-center space-x-2">
                      <MessageSquare class="w-4 h-4 text-gray-400" />
                      <span class="text-sm text-gray-600 truncate max-w-xs">{{ paciente.observacoes }}</span>
                    </div>
                  </div>
                </div>

                <!-- Histórico do Paciente -->
                <div v-if="getHistoricoPaciente(paciente.idPaciente)" class="pt-2">
                  <div class="flex items-center space-x-2 text-xs text-blue-600">
                    <History class="w-3 h-3" />
                    <span>Última consulta: {{ getHistoricoPaciente(paciente.idPaciente).ultimaConsulta }}</span>
                    <span class="text-gray-400">•</span>
                    <span>Total de consultas: {{ getHistoricoPaciente(paciente.idPaciente).totalConsultas }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Ações -->
            <div class="flex flex-col space-y-2 ml-4">
              <button @click="chamarPaciente(paciente)"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors flex items-center space-x-2 text-sm font-medium">
                <UserCheck class="w-4 h-4" />
                <span>Chamar</span>
              </button>

              <button @click="editarConsulta(paciente)"
                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors"
                title="Editar Consulta">
                <Edit class="w-4 h-4" />
              </button>

              <button @click="removerDaFila(paciente.id)"
                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors"
                title="Remover da fila">
                <X class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Estado Vazio -->
      <div v-if="filaFiltrada.length === 0"
        class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <Users class="w-8 h-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum paciente na fila</h3>
        <p class="text-gray-500 mb-4">Não há pacientes aguardando atendimento no momento.</p>
        <button @click="adicionarPaciente"
          class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition-colors">
          Adicionar Paciente
        </button>
      </div>
    </div>

    <!-- Modal de Confirmação - Aviso sobre Urgências -->
    <ActionModal 
      :open="showConfirmacaoModal" 
      titulo="Atenção: Atendimento de Urgência"
      subtitulo="Você está prestes a adicionar um atendimento de urgência à fila de espera"
      action-label="Continuar"
      action-variant="orange"
      cancel-label="Cancelar"
      modal-width="sm:max-w-md"
      border-color="warning"
      @acao="confirmarAdicionarUrgencia"
      @cancel="showConfirmacaoModal = false">
      
      <div class="space-y-4">
        <div class="flex items-start space-x-3 p-4 bg-orange-50 border border-orange-200 rounded-lg">
          <AlertCircle class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" />
          <div class="text-sm text-orange-800">
            <p class="font-medium mb-2">Importante:</p>
            <ul class="list-disc list-inside space-y-1 text-orange-700">
              <li>Consultas com prioridade <strong>normal</strong> ou <strong>baixa</strong> devem ser agendadas no módulo <strong>Agenda.</strong></li>
              <li>Na Fila de Espera você só pode adicionar <strong>atendimentos de urgência</strong> (alta prioridade)</li>
              <li>Este atendimento será adicionado com prioridade alta e aparecerá no topo da fila</li>
            </ul>
          </div>
        </div>
        
        <div class="flex items-center space-x-2 text-sm text-gray-600">
          <Info class="w-4 h-4 text-blue-500" />
          <span>Deseja continuar e adicionar este atendimento de urgência?</span>
        </div>
      </div>
    </ActionModal>

    <!-- Modal Adicionar/Editar Consulta -->
    <ActionModal 
      :open="showModal" 
      :titulo="editandoConsulta ? 'Editar Consulta' : 'Adicionar à Fila'"
      :subtitulo="editandoConsulta ? 'Edite os dados da consulta' : 'Preencha os dados para adicionar à fila de espera'"
      :action-label="editandoConsulta ? 'Atualizar Consulta' : 'Adicionar'" 
      :action-variant="editandoConsulta ? 'blue' : 'green'"
      modal-width="sm:max-w-lg" 
      @acao="salvarConsulta" 
      @cancel="fecharModal">
      <form @submit.prevent="salvarConsulta" class="space-y-4">
        <!-- Campos do Paciente - Apenas no modo de adicionar -->
        <div v-if="!editandoConsulta" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Busca de Paciente usando TypeaheadInput -->
          <div class="col-span-2">
            <TypeaheadInput 
              v-model="searchPacientesModal" 
              label="Paciente" 
              placeholder="Digite o nome do paciente..."
              :search-function="buscarPacientes" 
              :selected-item="pacienteSelecionado"
              :get-item-label="(item) => item.nome" 
              :get-item-subtitle="(item) => {
                const parts = []
                if (item.cpf) parts.push(`CPF: ${item.cpf}`)
                if (item.contato) parts.push(`Tel: ${item.contato}`)
                return parts.join(' • ')
              }"
              :required="true" 
              @select="selecionarPaciente" 
              @clear="limparPaciente" 
            />
          </div>
        </div>
        
        <!-- Campos do Paciente - Modo de edição (somente leitura) -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Paciente</label>
            <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-600">
              {{ form.nomePaciente }}
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Telefone - Apenas no modo de adicionar (preenchido automaticamente ao selecionar paciente) -->
          <InputTelefone 
            v-if="!editandoConsulta"
            v-model="form.contato" 
            label="Telefone"
            :disabled="!!pacienteSelecionado"
            required
          />
          <!-- Telefone - Modo de edição (somente leitura) -->
          <div v-else>
            <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
            <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-600">
              {{ form.contato }}
            </div>
          </div>

          <!-- Profissional - Sempre editável -->
          <BaseSelect 
            v-model="form.profissional"
            label="Profissional"
            :options="opcoesProfissionaisModal"
            required
          />
        </div>

        <!-- Resto do formulário (Tipo de Consulta, Prioridade, Observações) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <BaseSelect 
            v-model="form.tipoConsulta"
            label="Tipo de Consulta"
            :options="opcoesTipoConsulta"
            required
          />
          <!-- Prioridade: Desabilitada no modo adicionar (sempre alta) e quando editando consulta de prioridade alta -->
          <BaseSelect 
            v-model="form.prioridade"
            label="Prioridade"
            :options="opcoesPrioridadeModal"
            :disabled="!editandoConsulta || (editandoConsulta && editandoConsulta.prioridade === 'alta')"
            required
          />
          <!-- Tooltip ou texto explicativo quando desabilitado -->
          <p v-if="!editandoConsulta" class="text-xs text-gray-500 col-span-2 -mt-2">
            Consultas adicionadas diretamente na fila têm prioridade alta automaticamente
          </p>
          <p v-else-if="editandoConsulta && editandoConsulta.prioridade === 'alta'" class="text-xs text-gray-500 col-span-2 -mt-2">
            Consultas de urgência (prioridade alta) não podem ter a prioridade alterada
          </p>
        </div>

        <BaseTextarea 
          v-model="form.observacoes" 
          label="Observações"
          placeholder="Observações adicionais..."
          :rows="3"
        />
      </form>
    </ActionModal>

    <!-- Modal de Cancelamento de Consulta -->
    <ActionModal 
      :open="showCancelamentoModal" 
      titulo="Cancelar Consulta"
      subtitulo="Confirme o cancelamento da consulta e informe o motivo"
      action-label="Confirmar Cancelamento"
      action-variant="red"
      border-color="danger"
      cancel-label="Cancelar"
      modal-width="sm:max-w-md"
      @acao="confirmarCancelamento"
      @cancel="fecharModalCancelamento">
      
      <div class="space-y-4">
        <div v-if="consultaParaCancelar" class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
          <p class="text-sm text-gray-700">
            <span class="font-medium">Paciente:</span> {{ consultaParaCancelar.nomePaciente }}
          </p>
          <p class="text-sm text-gray-700 mt-1">
            <span class="font-medium">Profissional:</span> {{ consultaParaCancelar.profissional }}
          </p>
        </div>
        
        <div>
          <BaseTextarea 
            v-model="motivoCancelamento" 
            label="Motivo do Cancelamento"
            placeholder="Informe o motivo do cancelamento..."
            :rows="4"
            required
          />
        </div>
      </div>
    </ActionModal>

    <!-- Modal de Confirmação de Chamada -->
    <ActionModal 
      :open="showChamadaModal" 
      titulo="Chamar Paciente"
      subtitulo="Confirme que deseja chamar o paciente para atendimento"
      action-label="Confirmar Chamada"
      action-variant="green"
      border-color="success"
      cancel-label="Cancelar"
      modal-width="sm:max-w-md"
      @acao="confirmarChamadaPaciente"
      @cancel="fecharModalChamada">
      
      <div class="space-y-4">
        <div v-if="pacienteParaChamar" class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
          <p class="text-sm text-gray-700">
            <span class="font-medium">Paciente:</span> {{ pacienteParaChamar.nomePaciente }}
          </p>
          <p class="text-sm text-gray-700 mt-1">
            <span class="font-medium">Profissional:</span> {{ pacienteParaChamar.profissional }}
          </p>
          <p class="text-sm text-gray-700 mt-1">
            <span class="font-medium">Código:</span> {{ pacienteParaChamar.codigoChegada || 'N/A' }}
          </p>
        </div>
        
        <div class="flex items-center space-x-2 text-sm text-gray-600">
          <Info class="w-4 h-4 text-blue-500" />
          <span>O paciente será marcado como "em atendimento" e aparecerá no telão de chamadas.</span>
        </div>
      </div>
    </ActionModal>
  </div>
</template>

<script>
import {
  ClockIcon
} from '@heroicons/vue/24/outline'
import {
  Users, UserCheck, CheckCircle, AlertCircle, Search, Plus,
  RefreshCw, Phone, Timer, FileText, MessageSquare, History, Edit, X, Info, Monitor
} from 'lucide-vue-next'
import { toastSuccess, toastError, toastWarning, toastInfo } from '../../composables/useToast.js';
import axios from '../../services/axios.js';
import { urlPreCadastro } from '../../utils/fluxoAtendimento.js';

export default {
  name: 'FiladeEspera',
  components: {
    ClockIcon,
    Users,
    UserCheck,
    CheckCircle,
    AlertCircle,
    Search,
    Plus,
    RefreshCw,
    Phone,
    Timer,
    FileText,
    MessageSquare,
    History,
    Edit,
    X,
    Info,
    Monitor
  },
  data() {
    return {
      // Componentes de ícones (para usar como props)
      ClockIcon,

      // Estado
      searchTerm: '',
      filtroProfissional: '',
      filtroPrioridade: '',
      showModal: false,
      showConfirmacaoModal: false, // Modal de confirmação
      showCancelamentoModal: false, // Modal de cancelamento
      showChamadaModal: false, // Modal de confirmação de chamada
      consultaParaCancelar: null, // Consulta que será cancelada
      pacienteParaChamar: null, // Paciente que será chamado
      motivoCancelamento: '', // Motivo do cancelamento
      editandoConsulta: null, // Renomeado de editandoPaciente
      pacienteSelecionado: null, // Paciente selecionado no TypeaheadInput
      searchPacientesModal: '', // Termo de busca para pacientes

      // Dados da fila (será populado pela API)
      filaEspera: [],

      // Dados compartilhados (será populado pela API)
      consultasAtendidas: [],

      // Profissionais (será populado pela API)
      profissionais: [],
      profissionaisCompletos: [], // Dados completos dos profissionais (com IDs)

      // Estatísticas (será populado pela API)
      estatisticasData: {},

      // Formulário
      form: {
        paciente_id: null,
        nomePaciente: '',
        idPaciente: '',
        contato: '', // Renomeado de telefone para contato
        profissional: '',
        tipoConsulta: '',
        prioridade: 'alta', // Sempre alta para novas consultas na fila
        observacoes: ''
      }
    }
  },
  computed: {
    filaFiltrada() {
      let filtered = [...this.filaEspera]

      // Ordenar por prioridade primeiro
      filtered.sort((a, b) => {
        const prioridadeOrder = { 'alta': 0, 'normal': 1, 'baixa': 2 }
        if (prioridadeOrder[a.prioridade] !== prioridadeOrder[b.prioridade]) {
          return prioridadeOrder[a.prioridade] - prioridadeOrder[b.prioridade]
        }
        // Se mesma prioridade, ordenar por hora de chegada
        return a.horaChegada.localeCompare(b.horaChegada)
      })

      if (this.searchTerm) {
        const search = this.searchTerm.toLowerCase()
        filtered = filtered.filter(paciente =>
          paciente.nomePaciente?.toLowerCase().includes(search) ||
          (paciente.idPaciente && paciente.idPaciente.toString().toLowerCase().includes(search))
        )
      }

      if (this.filtroProfissional) {
        filtered = filtered.filter(paciente => paciente.profissional === this.filtroProfissional)
      }

      if (this.filtroPrioridade) {
        filtered = filtered.filter(paciente => paciente.prioridade === this.filtroPrioridade)
      }

      return filtered
    },
    pacientesNaFila() {
      return this.estatisticasData.pacientesNaFila || this.filaEspera.length
    },
    pacientesPrioridade() {
      return this.estatisticasData.pacientesPrioridade || this.filaEspera.filter(p => p.prioridade === 'alta').length
    },
    atendidosHoje() {
      return this.estatisticasData.atendidosHoje || 0
    },
    tempoMedioEspera() {
      // Calcular a média de todos os tempos de espera das consultas na fila
      if (!this.filaEspera || this.filaEspera.length === 0) {
        return '0min'
      }
      
      const tempos = this.filaEspera.map(paciente => {
        if (!paciente.horaChegada) return 0
        const tempo = this.calcularTempoEsperaMinutos(paciente.horaChegada)
        // Garantir que valores negativos sejam tratados como 0
        return Math.max(0, tempo)
      })
      
      const soma = tempos.reduce((acc, tempo) => acc + tempo, 0)
      const media = Math.round(soma / tempos.length)
      
      // Formatar: se for >= 60 minutos, mostrar em horas
      if (media >= 60) {
        const horas = Math.floor(media / 60)
        const minutos = media % 60
        if (minutos === 0) {
          return `${horas}h`
        }
        return `${horas}h ${minutos}min`
      }
      
      return `${media}min`
    },
    opcoesProfissionaisModal() {
      return [
        { value: '', label: 'Selecione' },
        ...this.profissionais.map(prof => ({ value: prof, label: prof }))
      ];
    },
    opcoesTipoConsulta() {
      return [
        { value: '', label: 'Selecione' },
        { value: 'Consulta', label: 'Consulta' },
        { value: 'Retorno', label: 'Retorno' },
        { value: 'Exame', label: 'Exame' },
        { value: 'Cirurgia', label: 'Cirurgia' }
      ];
    },
    opcoesPrioridadeModal() {
      return [
        { value: 'alta', label: 'Alta' },
        { value: 'normal', label: 'Normal' },
        { value: 'baixa', label: 'Baixa' }
      ];
    },
    opcoesProfissionaisFiltro() {
      return [
        { value: '', label: 'Todos os profissionais' },
        ...this.profissionais.map(prof => ({ value: prof, label: prof }))
      ];
    },
    opcoesPrioridadeFiltro() {
      return [
        { value: '', label: 'Todas as prioridades' },
        { value: 'alta', label: 'Alta Prioridade' },
        { value: 'normal', label: 'Atendimento Normal' },
        { value: 'baixa', label: 'Baixa Prioridade' }
      ];
    }
  },
  async mounted() {
    await this.carregarFilaEspera();
    await this.carregarEstatisticas();
    await this.carregarProfissionais();
  },
  methods: {
    async carregarFilaEspera() {
      try {
        // Obter data local (Brasil) em vez de UTC
        const hoje = new Date();
        const ano = hoje.getFullYear();
        const mes = String(hoje.getMonth() + 1).padStart(2, '0');
        const dia = String(hoje.getDate()).padStart(2, '0');
        const dataLocal = `${ano}-${mes}-${dia}`;
        
        const response = await axios.get('/consultas/fila-espera', {
          params: {
            data: dataLocal
          }
        });
        
        if (response.data.success) {
          this.filaEspera = response.data.data;
        } else {
          toastError('Erro ao carregar fila de espera');
        }
      } catch (error) {
        console.error('Erro ao carregar fila:', error);
        toastError('Erro ao carregar fila de espera');
      }
    },
    
    async carregarEstatisticas() {
      try {
        // Obter data local (Brasil) em vez de UTC
        const hoje = new Date();
        const ano = hoje.getFullYear();
        const mes = String(hoje.getMonth() + 1).padStart(2, '0');
        const dia = String(hoje.getDate()).padStart(2, '0');
        const dataLocal = `${ano}-${mes}-${dia}`;
        
        const response = await axios.get('/consultas/fila-espera/estatisticas', {
          params: {
            data: dataLocal
          }
        });
        
        if (response.data.success) {
          // Atualizar data property que alimenta as computed properties
          this.estatisticasData = response.data.data;
        }
      } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
      }
    },
    
    async carregarProfissionais() {
      try {
        // Usar endpoint existente ou criar um específico
        const response = await axios.get('/usuarios');
        if (response.data.success) {
          const profs = response.data.data.filter(u => u.profile_id === 3); // Apenas profissionais
          this.profissionais = profs.map(u => u.name);
          // Guardar dados completos para obter IDs quando necessário
          this.profissionaisCompletos = profs;
        }
      } catch (error) {
        console.error('Erro ao carregar profissionais:', error);
      }
    },
    
    // Método helper para obter ID do profissional pelo nome
    obterIdProfissional(nomeProfissional) {
      const profissional = this.profissionaisCompletos?.find(p => p.name === nomeProfissional);
      return profissional ? profissional.id : null;
    },
    
    calcularTempoEspera(horaChegada) {
      const agora = new Date()
      const chegada = new Date()
      const [hora, minuto] = horaChegada.split(':')
      chegada.setHours(parseInt(hora), parseInt(minuto), 0, 0)

      const diffMs = agora - chegada
      const diffMinutos = Math.floor(diffMs / (1000 * 60))

      if (diffMinutos < 60) {
        return `${diffMinutos}min`
      } else {
        const horas = Math.floor(diffMinutos / 60)
        const mins = diffMinutos % 60
        return `${horas}h ${mins}min`
      }
    },
    calcularTempoEsperaMinutos(horaChegada) {
      const agora = new Date()
      const chegada = new Date()
      const [hora, minuto] = horaChegada.split(':')
      chegada.setHours(parseInt(hora), parseInt(minuto), 0, 0)

      const diffMs = agora - chegada
      return Math.floor(diffMs / (1000 * 60))
    },
    getPriorityClass(prioridade) {
      const classes = {
        'alta': 'bg-red-100 text-red-700',
        'normal': 'bg-blue-100 text-blue-700',
        'baixa': 'bg-gray-100 text-gray-700'
      }
      return classes[prioridade] || classes.normal
    },
    getPriorityBgClass(prioridade) {
      const classes = {
        'alta': 'bg-red-600',
        'normal': 'bg-blue-600',
        'baixa': 'bg-gray-600'
      }
      return classes[prioridade] || classes.normal
    },
    getPriorityBorderClass(prioridade) {
      const classes = {
        'alta': 'border-l-4 border-l-red-500',
        'normal': '',
        'baixa': ''
      }
      return classes[prioridade] || ''
    },
    getPriorityLabel(prioridade) {
      const labels = {
        'alta': 'Alta Prioridade',
        'normal': 'Atendimento Normal',
        'baixa': 'Baixa Prioridade'
      }
      return labels[prioridade] || 'Normal'
    },
    getHistoricoPaciente(idPaciente) {
      const consultas = this.consultasAtendidas.filter(c => c.idPaciente === idPaciente)
      if (consultas.length === 0) return null

      const ultimaConsulta = consultas.sort((a, b) => new Date(b.data) - new Date(a.data))[0]
      return {
        ultimaConsulta: new Date(ultimaConsulta.data + 'T00:00:00').toLocaleDateString('pt-BR'),
        totalConsultas: consultas.length
      }
    },
    chamarPaciente(paciente) {
      // Armazenar paciente e abrir modal de confirmação
      this.pacienteParaChamar = paciente;
      this.showChamadaModal = true;
    },
    
    async confirmarChamadaPaciente() {
      if (!this.pacienteParaChamar) {
        toastError('Paciente não encontrado');
        this.fecharModalChamada();
        return;
      }
      
      try {
        const response = await axios.post(`/consultas/${this.pacienteParaChamar.id}/chamar`);
        
        if (response.data.success) {
          const chamado = this.pacienteParaChamar
          // Remover da fila local
          const index = this.filaEspera.findIndex(p => p.id === chamado.id);
          if (index !== -1) {
            this.filaEspera.splice(index, 1);
          }
          
          // Atualizar estatísticas
          await this.carregarEstatisticas();
          
          toastSuccess(`${chamado.nomePaciente} foi chamado para atendimento`);
          this.fecharModalChamada();

          if (chamado.idPaciente && chamado.id) {
            this.$router.push(urlPreCadastro(chamado.idPaciente, chamado.id))
          }
        } else {
          toastError(response.data.message || 'Erro ao chamar paciente');
        }
      } catch (error) {
        console.error('Erro ao chamar paciente:', error);
        toastError('Erro ao chamar paciente');
      }
    },
    
    fecharModalChamada() {
      this.showChamadaModal = false;
      this.pacienteParaChamar = null;
    },
    adicionarPaciente() {
      // Abrir primeiro o modal de confirmação
      this.showConfirmacaoModal = true
    },
    
    confirmarAdicionarUrgencia() {
      // Fechar modal de confirmação
      this.showConfirmacaoModal = false
      
      // Preparar formulário
      this.form = {
        paciente_id: null,
        nomePaciente: '',
        idPaciente: '',
        contato: '',
        profissional: '',
        tipoConsulta: '',
        prioridade: 'alta', // ⚠️ SEMPRE alta quando adiciona novo
        observacoes: ''
      }
      this.pacienteSelecionado = null
      this.searchPacientesModal = ''
      this.editandoConsulta = null
      
      // Abrir modal principal
      this.showModal = true
    },
    
    // Métodos para buscar e selecionar paciente
    async buscarPacientes(termo) {
      if (!termo || termo.length < 2) return [];
      try {
        const response = await axios.get('/listar-pacientes', {
          params: { search: termo }
        });
        return response.data.success ? response.data.data : [];
      } catch (error) {
        console.error('Erro ao buscar pacientes:', error);
        return [];
      }
    },
    
    selecionarPaciente(paciente) {
      this.pacienteSelecionado = paciente;
      this.form.paciente_id = paciente.id; // ID numérico
      this.form.nomePaciente = paciente.nome;
      this.form.contato = paciente.contato || '';
      this.searchPacientesModal = paciente.nome;
    },
    
    limparPaciente() {
      this.pacienteSelecionado = null;
      this.form.paciente_id = null;
      this.form.nomePaciente = '';
      this.form.contato = '';
      this.searchPacientesModal = '';
    },
    
    editarConsulta(consulta) { // Renomeado de editarPaciente
      this.form = { 
        paciente_id: consulta.idPaciente, // ID numérico
        nomePaciente: consulta.nomePaciente,
        idPaciente: consulta.idPaciente,
        contato: consulta.telefone, // Campo vem como 'telefone' da API mas é 'contato' no modelo
        profissional: consulta.profissional,
        tipoConsulta: consulta.tipoConsulta,
        prioridade: consulta.prioridade, // Pode ser alta, normal ou baixa
        observacoes: consulta.observacoes || ''
      }
      this.editandoConsulta = consulta
      this.showModal = true
    },
    async salvarConsulta() { // Renomeado de salvarPaciente
      try {
        if (this.editandoConsulta) {
          // Editar CONSULTA existente
          // ⚠️ NÃO enviar dados do paciente (nome, idPaciente, telefone)
          // Apenas dados da consulta
          const response = await axios.put(`/consultas/${this.editandoConsulta.id}`, {
            prioridade: this.form.prioridade,
            procedimento: this.form.tipoConsulta,
            observacoes: this.form.observacoes,
            user_id: this.obterIdProfissional(this.form.profissional),
          });
          
          if (response.data.success) {
            await this.carregarFilaEspera();
            await this.carregarEstatisticas();
            toastSuccess('Consulta atualizada com sucesso!');
          }
        } else {
          // Adicionar novo paciente à fila (criar consulta)
          // ⚠️ Prioridade sempre "alta" para consultas criadas diretamente na fila
          if (!this.form.paciente_id) {
            toastError('Selecione um paciente');
            return;
          }
          
          const user_id = this.obterIdProfissional(this.form.profissional);
          if (!user_id) {
            toastError('Selecione um profissional válido');
            return;
          }
          
          const response = await axios.post('/consultas/fila-espera/adicionar', {
            paciente_id: this.form.paciente_id, // ID numérico
            user_id: user_id,
            prioridade: 'alta', // Sempre alta para novas consultas na fila
            procedimento: this.form.tipoConsulta,
            observacoes: this.form.observacoes,
          });
          
          if (response.data.success) {
            await this.carregarFilaEspera();
            await this.carregarEstatisticas();
            toastSuccess('Paciente adicionado à fila com sucesso!');
          }
        }
        
        this.fecharModal();
      } catch (error) {
        console.error('Erro ao salvar consulta:', error);
        toastError(error.response?.data?.message || 'Erro ao salvar consulta');
      }
    },
    removerDaFila(id) {
      // Encontrar a consulta na fila
      const consulta = this.filaEspera.find(p => p.id === id)
      if (!consulta) {
        toastError('Consulta não encontrada na fila')
        return
      }
      
      // Abrir modal de cancelamento
      this.consultaParaCancelar = consulta
      this.motivoCancelamento = ''
      this.showCancelamentoModal = true
    },
    
    async confirmarCancelamento() {
      if (!this.motivoCancelamento || this.motivoCancelamento.trim() === '') {
        toastError('Por favor, informe o motivo do cancelamento')
        return
      }
      
      if (!this.consultaParaCancelar) {
        toastError('Consulta não encontrada')
        this.fecharModalCancelamento()
        return
      }
      
      try {
        const response = await axios.post(`/consultas/${this.consultaParaCancelar.id}/cancelar`, {
          motivo_cancelamento: this.motivoCancelamento.trim()
        })
        
        if (response.data.success) {
          // Remover da fila local
          const index = this.filaEspera.findIndex(p => p.id === this.consultaParaCancelar.id)
          if (index !== -1) {
            this.filaEspera.splice(index, 1)
          }
          
          // Atualizar estatísticas
          await this.carregarEstatisticas()
          
          toastSuccess(`Consulta de ${this.consultaParaCancelar.nomePaciente} foi cancelada com sucesso!`)
          this.fecharModalCancelamento()
        } else {
          toastError(response.data.message || 'Erro ao cancelar consulta')
        }
      } catch (error) {
        console.error('Erro ao cancelar consulta:', error)
        toastError(error.response?.data?.message || 'Erro ao cancelar consulta')
      }
    },
    
    fecharModalCancelamento() {
      this.showCancelamentoModal = false
      this.consultaParaCancelar = null
      this.motivoCancelamento = ''
    },
    fecharModal() {
      this.showModal = false
      this.editandoConsulta = null // Renomeado de editandoPaciente
      this.pacienteSelecionado = null
      this.searchPacientesModal = ''
      this.form = {
        paciente_id: null,
        nomePaciente: '',
        idPaciente: '',
        contato: '', // Renomeado de telefone
        profissional: '',
        tipoConsulta: '',
        prioridade: 'alta', // Sempre alta para novas consultas
        observacoes: ''
      }
    },
    async atualizarFila() {
      await this.carregarFilaEspera();
      await this.carregarEstatisticas();
      toastInfo('Fila atualizada');
    },
    abrirTelao() {
      const url = window.location.origin + '/consultas/telao-chamada';
      window.open(url, '_blank', 'width=1920,height=1080');
    }
  }
}
</script>