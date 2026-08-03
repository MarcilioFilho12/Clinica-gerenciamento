<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Atendimentos - Financeiro" description="Gestão financeira dos atendimentos"
      :icon="CurrencyDollarIcon" icon-bg-color="green" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Atendimentos - Financeiro' }
      ]" class="mb-8">
      <template #actions>
        <div class="flex items-center space-x-4">
          <div class="text-sm text-green-600 font-medium">
            Total do dia: R$ {{ totalDia }}
          </div>
          <div class="text-sm text-gray-600">
            {{ new Date().toLocaleDateString('pt-BR') }}
          </div>
        </div>
      </template>
    </PageHeader>
    <!-- Resumo Rápido -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
            <DollarSign class="w-5 h-5 text-green-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Recebido</p>
            <p class="text-xl font-bold text-green-600">R$ {{ resumo.recebido }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
            <Clock class="w-5 h-5 text-orange-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pendente</p>
            <p class="text-xl font-bold text-orange-600">R$ {{ resumo.pendente }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
            <CreditCard class="w-5 h-5 text-blue-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Convênio</p>
            <p class="text-xl font-bold text-blue-600">R$ {{ resumo.convenio }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
            <TrendingUp class="w-5 h-5 text-purple-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ticket Médio</p>
            <p class="text-xl font-bold text-purple-600">R$ {{ resumo.ticketMedio }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
      <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-4 flex-1">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input type="text" v-model="filtros.busca" placeholder="Buscar paciente..."
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm" />
          </div>

          <select v-model="filtros.status"
            class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
            <option value="">Todos os status</option>
            <option value="pago">Pago</option>
            <option value="pendente">Pendente</option>
            <option value="parcial">Pagamento Parcial</option>
          </select>

          <select v-model="filtros.formaPagamento"
            class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
            <option value="">Todas as formas</option>
            <option value="dinheiro">Dinheiro</option>
            <option value="cartao">Cartão</option>
            <option value="pix">PIX</option>
            <option value="convenio">Convênio</option>
          </select>
        </div>

        <button @click="abrirModalPagamento()"
          class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors flex items-center space-x-2 text-sm font-medium">
          <Plus class="w-4 h-4" />
          <span>Novo Pagamento</span>
        </button>
      </div>
    </div>

    <!-- Lista de Atendimentos -->
    <div class="space-y-4">
      <div v-for="atendimento in atendimentosFiltrados" :key="atendimento.id"
        class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
        <div class="p-6">
          <div class="flex items-start justify-between">
            <!-- Informações do Atendimento -->
            <div class="flex-1 space-y-3">
              <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <User class="w-5 h-5 text-blue-600" />
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ atendimento.paciente }}</h3>
                    <p class="text-sm text-gray-500">ID: {{ atendimento.idPaciente }}</p>
                  </div>
                </div>

                <div class="flex items-center space-x-2">
                  <span class="px-3 py-1 rounded-full text-xs font-medium"
                    :class="getStatusClass(atendimento.statusPagamento)">
                    {{ getStatusLabel(atendimento.statusPagamento) }}
                  </span>
                </div>
              </div>

              <!-- Detalhes do Atendimento -->
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                <div class="flex items-center space-x-2">
                  <Calendar class="w-4 h-4 text-gray-400" />
                  <span class="text-gray-600">{{ formatarData(atendimento.data) }}</span>
                </div>

                <div class="flex items-center space-x-2">
                  <UserCheck class="w-4 h-4 text-gray-400" />
                  <span class="text-gray-600">{{ atendimento.profissional }}</span>
                </div>

                <div class="flex items-center space-x-2">
                  <FileText class="w-4 h-4 text-gray-400" />
                  <span class="text-gray-600">{{ atendimento.tipoConsulta }}</span>
                </div>

                <div class="flex items-center space-x-2">
                  <DollarSign class="w-4 h-4 text-gray-400" />
                  <span class="text-gray-600 font-medium">R$ {{ atendimento.valor }}</span>
                </div>
              </div>

              <!-- Informações de Pagamento -->
              <div class="pt-3 border-t border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Forma de Pagamento</p>
                    <div class="flex items-center space-x-2">
                      <component :is="getFormaPagamentoIcon(atendimento.formaPagamento)"
                        class="w-4 h-4 text-gray-500" />
                      <span class="text-sm font-medium text-gray-900">{{
                        getFormaPagamentoLabel(atendimento.formaPagamento)
                        }}</span>
                    </div>
                  </div>

                  <div v-if="atendimento.valorPago">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Valor Pago</p>
                    <p class="text-sm font-medium text-green-600">R$ {{ atendimento.valorPago }}</p>
                  </div>

                  <div v-if="atendimento.valorPendente">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Valor Pendente</p>
                    <p class="text-sm font-medium text-orange-600">R$ {{ atendimento.valorPendente }}</p>
                  </div>
                </div>

                <!-- Observações -->
                <div v-if="atendimento.observacoesPagamento" class="mt-3">
                  <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Observações</p>
                  <p class="text-sm text-gray-700 bg-gray-50 p-2 rounded">{{ atendimento.observacoesPagamento }}</p>
                </div>
              </div>
            </div>

            <!-- Ações -->
            <div class="flex flex-col space-y-2 ml-4">
              <button v-if="atendimento.statusPagamento !== 'pago'" @click="abrirModalPagamento(atendimento)"
                class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-sm font-medium">
                Receber
              </button>

              <button @click="gerarRecibo(atendimento)"
                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors"
                title="Gerar recibo">
                <Receipt class="w-4 h-4" />
              </button>

              <button @click="editarPagamento(atendimento)"
                class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition-colors"
                title="Editar pagamento">
                <Edit class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Estado Vazio -->
      <div v-if="atendimentosFiltrados.length === 0"
        class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <Receipt class="w-8 h-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum atendimento encontrado</h3>
        <p class="text-gray-500">Não há atendimentos que correspondam aos filtros selecionados.</p>
      </div>
    </div>
  </div>

  <!-- Modal de Pagamento -->
  <div v-if="showModalPagamento" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ atendimentoSelecionado ? 'Receber Pagamento' : 'Novo Pagamento' }}
          </h3>
          <button @click="fecharModalPagamento" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
      </div>

      <form @submit.prevent="iniciarProcessarPagamento" class="p-6 space-y-4">
        <div v-if="atendimentoSelecionado">
          <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <p class="font-medium text-gray-900">{{ atendimentoSelecionado.paciente }}</p>
            <p class="text-sm text-gray-600">{{ atendimentoSelecionado.tipoConsulta }}</p>
            <p class="text-lg font-bold text-green-600 mt-2">
              Valor: R$ {{ atendimentoSelecionado.valor }}
            </p>
          </div>
        </div>

        <div v-else>
          <TypeaheadInput
            v-model="searchPaciente"
            label="Paciente (opcional)"
            placeholder="Digite nome ou CPF do paciente..."
            :search-function="buscarPacientes"
            :selected-item="pacienteSelecionado"
            :search-on-focus="true"
            :get-item-label="(item) => item.nome"
            :get-item-subtitle="(item) => {
              const parts = []
              if (item.cpf) parts.push(`CPF: ${item.cpf}`)
              if (item.contato) parts.push(`Tel: ${item.contato}`)
              return parts.join(' • ')
            }"
            :required="false"
            @select="selecionarPaciente"
            @clear="limparPaciente"
          />
          <p class="mt-1 text-xs text-gray-500">Pode confirmar sem paciente; o sistema pedirá confirmação.</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Valor a Receber</label>
          <div class="relative">
            <DollarSign class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input type="number" step="0.01" v-model="formPagamento.valor" placeholder="0,00"
              class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
              required />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento</label>
          <select v-model="formPagamento.formaPagamento"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
            required>
            <option value="">Selecione</option>
            <option value="dinheiro">Dinheiro</option>
            <option value="cartao_credito">Cartão de Crédito</option>
            <option value="cartao_debito">Cartão de Débito</option>
            <option value="pix">PIX</option>
            <option value="convenio">Convênio</option>
          </select>
        </div>

        <div v-if="formPagamento.formaPagamento === 'convenio'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Convênio</label>
          <select v-model="formPagamento.convenio"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <option value="">Selecione o convênio</option>
            <option value="unimed">Unimed</option>
            <option value="bradesco">Bradesco Saúde</option>
            <option value="sulamerica">SulAmérica</option>
            <option value="outros">Outros</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
          <textarea v-model="formPagamento.observacoes" rows="3" placeholder="Observações sobre o pagamento..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none"></textarea>
        </div>

        <div class="flex space-x-3 pt-4">
          <button type="button" @click="fecharModalPagamento"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
            Cancelar
          </button>
          <button type="submit"
            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
            Confirmar Pagamento
          </button>
        </div>
      </form>
    </div>
  </div>

  <ActionModal
    :open="showModalSemPaciente"
    titulo="Salvar pagamento sem paciente?"
    subtitulo="Nenhum paciente cadastrado está vinculado a este pagamento."
    action-label="Confirmar assim mesmo"
    action-variant="blue"
    border-color="warning"
    cancel-label="Voltar"
    modal-width="sm:max-w-md"
    @acao="confirmarPagamentoSemPaciente"
    @cancel="showModalSemPaciente = false">
    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-900">
      Você pode associar um paciente agora ou continuar sem vínculo. Pagamentos sem paciente
      entram no caixa, mas sem histórico por paciente.
    </div>
  </ActionModal>
</template>

<script setup>
import { ref, computed } from 'vue'
import { CurrencyDollarIcon } from '@heroicons/vue/24/outline'
import {
  Receipt, DollarSign, Clock, CreditCard, TrendingUp, Search, Plus,
  User, Calendar, UserCheck, FileText, Edit, X, Banknote, Smartphone
} from 'lucide-vue-next'
import axios from '../../services/axios.js'
import { toast } from 'vue3-toastify'

const filtros = ref({
  busca: '',
  status: '',
  formaPagamento: ''
})

const showModalPagamento = ref(false)
const showModalSemPaciente = ref(false)
const atendimentoSelecionado = ref(null)
const searchPaciente = ref('')
const pacienteSelecionado = ref(null)

const atendimentos = ref([])

const formPagamento = ref({
  valor: '',
  formaPagamento: '',
  convenio: '',
  observacoes: ''
})

const atendimentosFiltrados = computed(() => {
  let filtered = atendimentos.value

  if (filtros.value.busca) {
    const busca = filtros.value.busca.toLowerCase()
    filtered = filtered.filter(atendimento =>
      String(atendimento.paciente || '').toLowerCase().includes(busca) ||
      String(atendimento.idPaciente || '').toLowerCase().includes(busca)
    )
  }

  if (filtros.value.status) {
    filtered = filtered.filter(atendimento => atendimento.statusPagamento === filtros.value.status)
  }

  if (filtros.value.formaPagamento) {
    filtered = filtered.filter(atendimento => atendimento.formaPagamento === filtros.value.formaPagamento)
  }

  return filtered
})

const resumo = computed(() => {
  const recebido = atendimentos.value
    .filter(a => a.statusPagamento === 'pago')
    .reduce((sum, a) => sum + parseFloat(a.valorPago || 0), 0)
    .toFixed(2)

  const pendente = atendimentos.value
    .reduce((sum, a) => sum + parseFloat(a.valorPendente || 0), 0)
    .toFixed(2)

  const convenio = atendimentos.value
    .filter(a => a.formaPagamento === 'convenio')
    .reduce((sum, a) => sum + parseFloat(a.valorPago || 0), 0)
    .toFixed(2)

  const totalAtendimentos = atendimentos.value.length
  const ticketMedio = totalAtendimentos > 0
    ? (parseFloat(recebido) / totalAtendimentos).toFixed(2)
    : '0.00'

  return {
    recebido,
    pendente,
    convenio,
    ticketMedio
  }
})

const totalDia = computed(() => {
  return atendimentos.value
    .reduce((sum, a) => sum + parseFloat(a.valor || 0), 0)
    .toFixed(2)
})

const formatarData = (data) => {
  return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR')
}

const getStatusClass = (status) => {
  const classes = {
    'pago': 'bg-green-100 text-green-700',
    'pendente': 'bg-orange-100 text-orange-700',
    'parcial': 'bg-yellow-100 text-yellow-700'
  }
  return classes[status] || 'bg-gray-100 text-gray-700'
}

const getStatusLabel = (status) => {
  const labels = {
    'pago': 'Pago',
    'pendente': 'Pendente',
    'parcial': 'Parcial'
  }
  return labels[status] || 'Indefinido'
}

const getFormaPagamentoIcon = (forma) => {
  const icons = {
    'dinheiro': Banknote,
    'cartao_credito': CreditCard,
    'cartao_debito': CreditCard,
    'pix': Smartphone,
    'convenio': FileText
  }
  return icons[forma] || DollarSign
}

const getFormaPagamentoLabel = (forma) => {
  const labels = {
    'dinheiro': 'Dinheiro',
    'cartao_credito': 'Cartão de Crédito',
    'cartao_debito': 'Cartão de Débito',
    'pix': 'PIX',
    'convenio': 'Convênio'
  }
  return labels[forma] || 'Não informado'
}

const buscarPacientes = async (termo) => {
  try {
    const params = { limit: 20 }
    if (termo && String(termo).trim() !== '') {
      params.search = String(termo).trim()
    }
    const response = await axios.get('/listar-pacientes', { params })
    return response.data?.data || []
  } catch (err) {
    console.error('Erro ao buscar pacientes:', err)
    return []
  }
}

const selecionarPaciente = (paciente) => {
  pacienteSelecionado.value = paciente
  searchPaciente.value = paciente?.nome || ''
}

const limparPaciente = () => {
  pacienteSelecionado.value = null
  searchPaciente.value = ''
}

const abrirModalPagamento = (atendimento = null) => {
  atendimentoSelecionado.value = atendimento
  limparPaciente()
  if (atendimento) {
    formPagamento.value = {
      valor: atendimento.valorPendente || atendimento.valor,
      formaPagamento: atendimento.formaPagamento || '',
      convenio: '',
      observacoes: atendimento.observacoesPagamento || ''
    }
  } else {
    formPagamento.value = {
      valor: '',
      formaPagamento: '',
      convenio: '',
      observacoes: ''
    }
  }
  showModalPagamento.value = true
}

const fecharModalPagamento = () => {
  showModalPagamento.value = false
  showModalSemPaciente.value = false
  atendimentoSelecionado.value = null
  limparPaciente()
  formPagamento.value = {
    valor: '',
    formaPagamento: '',
    convenio: '',
    observacoes: ''
  }
}

const iniciarProcessarPagamento = () => {
  if (!formPagamento.value.valor || !formPagamento.value.formaPagamento) {
    toast.error('Informe valor e forma de pagamento')
    return
  }

  const temPaciente = atendimentoSelecionado.value || pacienteSelecionado.value
  if (!temPaciente) {
    showModalSemPaciente.value = true
    return
  }

  processarPagamento()
}

const confirmarPagamentoSemPaciente = () => {
  showModalSemPaciente.value = false
  processarPagamento()
}

const processarPagamento = () => {
  const valorPago = parseFloat(formPagamento.value.valor)

  if (atendimentoSelecionado.value) {
    const index = atendimentos.value.findIndex(a => a.id === atendimentoSelecionado.value.id)
    if (index !== -1) {
      const valorTotal = parseFloat(atendimentoSelecionado.value.valor)

      atendimentos.value[index] = {
        ...atendimentos.value[index],
        formaPagamento: formPagamento.value.formaPagamento,
        valorPago: valorPago.toFixed(2),
        valorPendente: valorPago >= valorTotal ? null : (valorTotal - valorPago).toFixed(2),
        statusPagamento: valorPago >= valorTotal ? 'pago' : 'parcial',
        observacoesPagamento: formPagamento.value.observacoes
      }
    }
  } else {
    const paciente = pacienteSelecionado.value
    atendimentos.value.unshift({
      id: `pag-${Date.now()}`,
      idPaciente: paciente?.id || '—',
      paciente: paciente?.nome || 'Sem paciente',
      profissional: '—',
      tipoConsulta: 'Pagamento avulso',
      data: new Date().toISOString().slice(0, 10),
      valor: valorPago.toFixed(2),
      valorPago: valorPago.toFixed(2),
      valorPendente: null,
      formaPagamento: formPagamento.value.formaPagamento,
      statusPagamento: 'pago',
      observacoesPagamento: formPagamento.value.observacoes || null,
    })
  }

  toast.success('Pagamento confirmado')
  fecharModalPagamento()
}

const gerarRecibo = (atendimento) => {
  console.log('Gerando recibo para:', atendimento.paciente)
}

const editarPagamento = (atendimento) => {
  abrirModalPagamento(atendimento)
}
</script>