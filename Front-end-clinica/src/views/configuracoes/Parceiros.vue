<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Configurações de Parceiros"
      description="Gerencie convênios, laboratórios, fornecedores e indicações médicas" :icon="Users"
      :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Parceiros' }
      ]" icon-bg-color="blue" class="mb-8">
      <template #actions>
        <button @click="abrirModalCadastro"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center space-x-2 font-medium">
          <Plus class="w-4 h-4" />
          <span>Novo Parceiro</span>
        </button>
      </template>
    </PageHeader>

    <!-- Dashboard de Indicadores -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-3">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <CheckCircle class="w-6 h-6 text-green-600" />
          </div>
          <div>
            <p class="text-sm text-gray-600">Parceiros Ativos</p>
            <p class="text-2xl font-bold text-gray-900">{{ indicadores.ativos }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-3">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <Building class="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <p class="text-sm text-gray-600">Total de Parceiros</p>
            <p class="text-2xl font-bold text-gray-900">{{ indicadores.total }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-3">
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
            <Heart class="w-6 h-6 text-purple-600" />
          </div>
          <div>
            <p class="text-sm text-gray-600">Convênios</p>
            <p class="text-2xl font-bold text-gray-900">{{ indicadores.convenios }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-3">
          <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
            <Calendar class="w-6 h-6 text-orange-600" />
          </div>
          <div>
            <p class="text-sm text-gray-600">Último Cadastro</p>
            <p class="text-sm font-medium text-gray-900">{{ indicadores.ultimoCadastro }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros e Busca -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-4 flex-1">
          <!-- Busca por Nome -->
          <div class="flex items-center space-x-2">
            <Search class="w-4 h-4 text-gray-400" />
            <input type="text" v-model="filtros.nome" @input="filtrarParceiros" placeholder="Buscar por nome..."
              class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm min-w-[200px]" />
          </div>

          <!-- Filtro por Tipo -->
          <div class="flex items-center space-x-2">
            <Filter class="w-4 h-4 text-gray-400" />
            <select v-model="filtros.tipo" @change="filtrarParceiros"
              class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
              <option value="">Todos os tipos</option>
              <option value="convenio">Convênio</option>
              <option value="laboratorio">Laboratório</option>
              <option value="fornecedor">Fornecedor</option>
              <option value="indicacao_medica">Indicação Médica</option>
              <option value="empresa_conveniada">Empresa Conveniada</option>
            </select>
          </div>

          <!-- Filtro por Situação -->
          <div class="flex items-center space-x-2">
            <ToggleLeft class="w-4 h-4 text-gray-400" />
            <select v-model="filtros.situacao" @change="filtrarParceiros"
              class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
              <option value="">Todas as situações</option>
              <option value="ativo">Ativo</option>
              <option value="inativo">Inativo</option>
            </select>
          </div>

          <!-- Ordenação -->
          <div class="flex items-center space-x-2">
            <ArrowUpDown class="w-4 h-4 text-gray-400" />
            <select v-model="ordenacao" @change="ordenarParceiros"
              class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
              <option value="nome_asc">Nome (A-Z)</option>
              <option value="nome_desc">Nome (Z-A)</option>
              <option value="data_asc">Data (Mais Antigo)</option>
              <option value="data_desc">Data (Mais Recente)</option>
            </select>
          </div>
        </div>

        <!-- Botão Limpar Filtros -->
        <button @click="limparFiltros"
          class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-md transition-colors text-sm">
          Limpar Filtros
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="carregando" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
      <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <Loader2 class="w-8 h-8 text-blue-600 animate-spin" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Carregando parceiros...</h3>
      <p class="text-gray-500">Aguarde enquanto buscamos os dados</p>
    </div>

    <!-- Error State -->
    <div v-else-if="erro" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
      <div class="flex items-center space-x-3">
        <AlertCircle class="w-6 h-6 text-red-600" />
        <div>
          <h3 class="text-red-800 font-medium">Erro ao carregar parceiros</h3>
          <p class="text-red-600 text-sm mt-1">{{ erro }}</p>
        </div>
      </div>
      <button @click="carregarParceiros"
        class="mt-4 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
        Tentar Novamente
      </button>
    </div>

    <!-- Tabela de Parceiros -->
    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200">
      <!-- Header da Tabela -->
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Lista de Parceiros
        </h3>
        <p class="text-sm text-gray-500 mt-1">
          {{ parceirosFiltrados.length }} parceiro{{ parceirosFiltrados.length !== 1 ? 's' : '' }} encontrado{{
            parceirosFiltrados.length !== 1 ? 's' : '' }}
        </p>
      </div>

      <!-- Tabela Desktop -->
      <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parceiro</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsável
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="parceiro in parceirosFiltrados" :key="parceiro.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                    :class="getTipoIconClass(parceiro.tipo)">
                    <component :is="getTipoIcon(parceiro.tipo)" class="w-5 h-5" />
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ parceiro.nome }}</div>
                    <div class="text-sm text-gray-500">{{ parceiro.cnpj }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getTipoClass(parceiro.tipo)">
                  {{ getTipoLabel(parceiro.tipo) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getStatusClass(parceiro.situacao || parceiro.status)">
                  {{ ((parceiro.situacao || parceiro.status || 'ativo')).charAt(0).toUpperCase() + ((parceiro.situacao
                    ||
                  parceiro.status || 'ativo')).slice(1) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div class="space-y-1">
                  <div class="flex items-center space-x-2">
                    <Phone class="w-4 h-4 text-gray-400" />
                    <span>{{ parceiro.telefone }}</span>
                  </div>
                  <div class="flex items-center space-x-2">
                    <Mail class="w-4 h-4 text-gray-400" />
                    <span class="truncate max-w-[150px]">{{ parceiro.email }}</span>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ parceiro.responsavel }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center justify-center space-x-3">
                  <button @click="editarParceiro(parceiro)"
                    class="text-blue-600 hover:text-blue-900 p-2 rounded hover:bg-blue-50 flex items-center justify-center w-8 h-8"
                    title="Editar">
                    <Edit class="w-4 h-4" />
                  </button>
                  <button @click="verDetalhes(parceiro)"
                    class="text-gray-600 hover:text-gray-900 p-2 rounded hover:bg-gray-50 flex items-center justify-center w-8 h-8"
                    title="Ver Detalhes">
                    <Eye class="w-4 h-4" />
                  </button>
                  <button @click="excluirParceiro(parceiro)"
                    class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-50 flex items-center justify-center w-8 h-8"
                    title="Excluir">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Cards Mobile -->
      <div class="md:hidden p-4 space-y-4">
        <div v-for="parceiro in parceirosFiltrados" :key="`mobile-${parceiro.id}`"
          class="border border-gray-200 rounded-lg p-4">
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                :class="getTipoIconClass(parceiro.tipo)">
                <component :is="getTipoIcon(parceiro.tipo)" class="w-5 h-5" />
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-900">{{ parceiro.nome }}</h4>
                <p class="text-xs text-gray-500">{{ parceiro.cnpj }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                :class="getStatusClass(parceiro.situacao || parceiro.status)">
                {{ ((parceiro.situacao || parceiro.status || 'ativo')).charAt(0).toUpperCase() + ((parceiro.situacao ||
                  parceiro.status || 'ativo')).slice(1) }}
              </span>
            </div>
          </div>

          <div class="space-y-2 text-sm text-gray-600 mb-3">
            <div class="flex items-center space-x-2">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                :class="getTipoClass(parceiro.tipo)">
                {{ getTipoLabel(parceiro.tipo) }}
              </span>
            </div>
            <div class="flex items-center space-x-2">
              <Phone class="w-4 h-4" />
              <span>{{ parceiro.telefone }}</span>
            </div>
            <div class="flex items-center space-x-2">
              <User class="w-4 h-4" />
              <span>{{ parceiro.responsavel }}</span>
            </div>
          </div>

          <div class="flex items-center justify-end space-x-3">
            <button @click="editarParceiro(parceiro)"
              class="text-blue-600 hover:text-blue-900 p-2 rounded hover:bg-blue-50 flex items-center justify-center w-10 h-10"
              title="Editar">
              <Edit class="w-4 h-4" />
            </button>
            <button @click="verDetalhes(parceiro)"
              class="text-gray-600 hover:text-gray-900 p-2 rounded hover:bg-gray-50 flex items-center justify-center w-10 h-10"
              title="Ver Detalhes">
              <Eye class="w-4 h-4" />
            </button>
            <button @click="excluirParceiro(parceiro)"
              class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-50 flex items-center justify-center w-10 h-10"
              title="Excluir">
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Estado Vazio -->
      <div v-if="parceirosFiltrados.length === 0 && !carregando" class="p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <Users class="w-8 h-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum parceiro encontrado</h3>
        <p class="text-gray-500 mb-4">
          {{ temFiltrosAtivos()
            ? 'Não há parceiros que correspondam aos filtros selecionados.'
            : 'Comece cadastrando seu primeiro parceiro.'
          }}
        </p>
        <button @click="temFiltrosAtivos() ? limparFiltros() : abrirModalCadastro()"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
          {{ temFiltrosAtivos() ? 'Limpar Filtros' : 'Cadastrar Parceiro' }}
        </button>
      </div>
    </div>

    <ActionModal :open="modalDetalhesAberto" titulo="Detalhes do Parceiro" subtitulo="" action-label="Editar"
      modal-width="sm:max-w-3xl" @acao="editarParceiro(parceiroSelecionado)" @cancel="fecharModalDetalhes">
      <div v-if="parceiroSelecionado" class="space-y-6">
        <!-- Informações Básicas -->
        <div class="flex items-start space-x-4">
          <div class="w-16 h-16 rounded-lg flex items-center justify-center"
            :class="getTipoIconClass(parceiroSelecionado.tipo)">
            <component :is="getTipoIcon(parceiroSelecionado.tipo)" class="w-8 h-8" />
          </div>
          <div class="flex-1">
            <h3 class="text-xl font-semibold text-gray-900">{{ parceiroSelecionado.nome }}</h3>
            <div class="flex items-center space-x-4 mt-2">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                :class="getTipoClass(parceiroSelecionado.tipo)">
                {{ getTipoLabel(parceiroSelecionado.tipo) }}
              </span>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                :class="getStatusClass(parceiroSelecionado.situacao || parceiroSelecionado.status)">
                {{ (parceiroSelecionado.situacao || 'ativo').charAt(0).toUpperCase() + (parceiroSelecionado.situacao ||
                'ativo').slice(1) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Informações de Contato -->
        <div class="border-t border-gray-200 pt-6">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Informações de Contato</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="flex items-center space-x-3">
              <Phone class="w-4 h-4 text-gray-400" />
              <span>{{ parceiroSelecionado.telefone }}</span>
            </div>
            <div class="flex items-center space-x-3">
              <Mail class="w-4 h-4 text-gray-400" />
              <span>{{ parceiroSelecionado.email }}</span>
            </div>
            <div v-if="parceiroSelecionado.site" class="flex items-center space-x-3">
              <Globe class="w-4 h-4 text-gray-400" />
              <a :href="parceiroSelecionado.site" target="_blank" class="text-blue-600 hover:text-blue-800">
                {{ parceiroSelecionado.site }}
              </a>
            </div>
            <div class="flex items-center space-x-3">
              <User class="w-4 h-4 text-gray-400" />
              <span>{{ parceiroSelecionado.responsavel }}</span>
            </div>
            <div v-if="parceiroSelecionado.cnpj" class="flex items-center space-x-3">
              <FileText class="w-4 h-4 text-gray-400" />
              <span>{{ parceiroSelecionado.cnpj }}</span>
            </div>
          </div>
        </div>

        <!-- Endereço -->
        <div v-if="parceiroSelecionado.endereco_completo" class="border-t border-gray-200 pt-6">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Endereço</h4>
          <div class="flex items-start space-x-3 text-sm text-gray-600">
            <MapPin class="w-4 h-4 text-gray-400 mt-0.5" />
            <span>{{ parceiroSelecionado.endereco_completo }}</span>
          </div>
        </div>

        <!-- Observações -->
        <div v-if="parceiroSelecionado.observacoes" class="border-t border-gray-200 pt-6">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Observações</h4>
          <p class="text-sm text-gray-600 bg-gray-50 p-4 rounded-lg">
            {{ parceiroSelecionado.observacoes }}
          </p>
        </div>

        <!-- Informações de Sistema -->
        <div class="border-t border-gray-200 pt-6">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Informações do Sistema</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
            <div class="flex items-center space-x-3">
              <Calendar class="w-4 h-4 text-gray-400" />
              <span>Cadastrado em: {{ formatarData(parceiroSelecionado.created_at) }}</span>
            </div>
            <div class="flex items-center space-x-3">
              <Clock class="w-4 h-4 text-gray-400" />
              <span>Última atualização: {{ formatarData(parceiroSelecionado.updated_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </ActionModal>

    <!-- Modal de Confirmação de Exclusão -->
    <ActionModal :open="modalExclusaoAberto" titulo="Confirmar Exclusão" subtitulo=""
      :action-label="carregando ? 'Excluindo...' : 'Excluir'" :action-disabled="carregando" modal-width="sm:max-w-md"
      @acao="confirmarExclusao" @cancel="cancelarExclusao">
      <div v-if="parceiroParaExcluir" class="space-y-4">
        <div class="flex items-start space-x-3">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
              <Trash2 class="w-5 h-5 text-red-600" />
            </div>
          </div>
          <div class="flex-1">
            <p class="text-sm text-gray-700">
              Tem certeza que deseja excluir o parceiro <strong>{{ parceiroParaExcluir.nome }}</strong>?
            </p>
            <p class="text-sm text-red-600 mt-2">
              Esta ação não pode ser desfeita e todos os dados do parceiro serão permanentemente removidos.
            </p>
          </div>
        </div>
      </div>
    </ActionModal>

    <!-- Mensagens de Feedback -->
    <div v-if="mensagemSucesso"
      class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2 z-50">
      <CheckCircle class="w-5 h-5" />
      <span>{{ mensagemSucesso }}</span>
    </div>

    <div v-if="mensagemErro"
      class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2 z-50">
      <AlertCircle class="w-5 h-5" />
      <span>{{ mensagemErro }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from '../../services/axios.js'
import {
  Users, Plus, CheckCircle, Building, Heart, Calendar, Search, Filter,
  ToggleLeft, ArrowUpDown, Loader2, AlertCircle, Phone, Mail,
  Edit, Eye, Trash2, User, Globe, FileText, MapPin, Clock
} from 'lucide-vue-next'

// ===== ROUTER =====
const router = useRouter()

// ===== ESTADO REATIVO =====
const carregando = ref(false)
const erro = ref('')
const mensagemSucesso = ref('')
const mensagemErro = ref('')

// Dados dos parceiros
const parceiros = ref([])
const parceirosFiltrados = ref([])

// Modais
const modalDetalhesAberto = ref(false)
const parceiroSelecionado = ref(null)
const modalExclusaoAberto = ref(false)
const parceiroParaExcluir = ref(null)

// Filtros e ordenação
const filtros = ref({
  nome: '',
  tipo: '',
  status: '',
  situacao: ''
})
const ordenacao = ref('nome_asc')


// Indicadores
const indicadores = ref({
  total: 0,
  ativos: 0,
  convenios: 0,
  ultimoCadastro: 'Nunca'
})

// ===== COMPUTED PROPERTIES =====

/**
 * Verifica se há filtros ativos
 */
const temFiltrosAtivos = () => {
  return filtros.value.nome || filtros.value.tipo || filtros.value.status || filtros.value.situacao
}

// ===== FUNÇÕES UTILITÁRIAS =====


/**
 * Formata data para exibição
 */
const formatarData = (data) => {
  if (!data) return 'N/A'
  return new Date(data).toLocaleDateString('pt-BR')
}

/**
 * Retorna ícone baseado no tipo de parceiro
 */
const getTipoIcon = (tipo) => {
  const icons = {
    'convenio': Heart,
    'laboratorio': Building,
    'fornecedor': Building,
    'indicacao_medica': User,
    'empresa_conveniada': Building
  }
  return icons[tipo] || Building
}

/**
 * Retorna classe do ícone baseado no tipo
 */
const getTipoIconClass = (tipo) => {
  const classes = {
    'convenio': 'bg-purple-100 text-purple-600',
    'laboratorio': 'bg-blue-100 text-blue-600',
    'fornecedor': 'bg-green-100 text-green-600',
    'indicacao_medica': 'bg-orange-100 text-orange-600',
    'empresa_conveniada': 'bg-indigo-100 text-indigo-600'
  }
  return classes[tipo] || 'bg-gray-100 text-gray-600'
}

/**
 * Retorna classe CSS baseada no tipo
 */
const getTipoClass = (tipo) => {
  const classes = {
    'convenio': 'bg-purple-100 text-purple-800',
    'laboratorio': 'bg-blue-100 text-blue-800',
    'fornecedor': 'bg-green-100 text-green-800',
    'indicacao_medica': 'bg-orange-100 text-orange-800',
    'empresa_conveniada': 'bg-indigo-100 text-indigo-800'
  }
  return classes[tipo] || 'bg-gray-100 text-gray-800'
}

/**
 * Retorna label do tipo
 */
const getTipoLabel = (tipo) => {
  const labels = {
    'convenio': 'Convênio',
    'laboratorio': 'Laboratório',
    'fornecedor': 'Fornecedor',
    'indicacao_medica': 'Indicação Médica',
    'empresa_conveniada': 'Empresa Conveniada'
  }
  return labels[tipo] || tipo
}

/**
 * Retorna classe CSS baseada no status
 */
const getStatusClass = (status) => {
  const statusValue = (status || 'ativo').toLowerCase()
  return statusValue === 'ativo'
    ? 'bg-green-100 text-green-800'
    : 'bg-red-100 text-red-800'
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

// ===== FUNÇÕES DE FILTRO E ORDENAÇÃO =====

/**
 * Mapeia o valor do select para o nome real na tabela
 */
const mapearTipoFiltro = (valorSelect) => {
  const mapeamento = {
    'convenio': 'Convênio',
    'laboratorio': 'Laboratório',
    'fornecedor': 'Fornecedor',
    'indicacao_medica': 'Indicação Médica',
    'empresa_conveniada': 'Empresa Conveniada'
  }
  return mapeamento[valorSelect] || valorSelect
}

/**
 * Filtra parceiros baseado nos filtros ativos
 */
const filtrarParceiros = () => {
  let resultado = [...parceiros.value]

  // Filtro por nome
  if (filtros.value.nome) {
    resultado = resultado.filter(parceiro =>
      parceiro.nome.toLowerCase().includes(filtros.value.nome.toLowerCase())
    )
  }

  // Filtro por tipo
  if (filtros.value.tipo) {
    // Mapeia o valor do select para o nome real na tabela
    const tipoFiltro = mapearTipoFiltro(filtros.value.tipo)
    resultado = resultado.filter(parceiro => {
      // Compara o nome do tipo do parceiro com o valor mapeado
      return parceiro.tipo === tipoFiltro
    })
  }

  // Filtro por situação
  if (filtros.value.situacao) {
    resultado = resultado.filter(parceiro => parceiro.situacao === filtros.value.situacao)
  }

  parceirosFiltrados.value = resultado
  ordenarParceiros()
}

/**
 * Ordena parceiros baseado na ordenação selecionada
 */
const ordenarParceiros = () => {
  const [campo, direcao] = ordenacao.value.split('_')

  parceirosFiltrados.value.sort((a, b) => {
    let valorA, valorB

    if (campo === 'nome') {
      valorA = a.nome.toLowerCase()
      valorB = b.nome.toLowerCase()
    } else if (campo === 'data') {
      valorA = new Date(a.created_at)
      valorB = new Date(b.created_at)
    }

    if (direcao === 'asc') {
      return valorA < valorB ? -1 : valorA > valorB ? 1 : 0
    } else {
      return valorA > valorB ? -1 : valorA < valorB ? 1 : 0
    }
  })
}

/**
 * Limpa todos os filtros
 */
const limparFiltros = () => {
  filtros.value = {
    nome: '',
    tipo: '',
    status: '',
    situacao: ''
  }
  ordenacao.value = 'nome_asc'
  filtrarParceiros()
}

// ===== FUNÇÕES DE MODAL =====

/**
 * Navega para página de novo parceiro
 */
const abrirModalCadastro = () => {
  router.push('/configuracoes/parceiros/novo')
}

/**
 * Edita parceiro (por enquanto navega para página de novo)
 */
const editarParceiro = (parceiro) => {
  router.push(`/configuracoes/parceiros/${parceiro.id}`)
}

/**
 * Ver detalhes do parceiro
 */
const verDetalhes = (parceiro) => {
  parceiroSelecionado.value = {
    ...parceiro,
    endereco_completo: montarEnderecoCompleto(parceiro)
  }
  modalDetalhesAberto.value = true
}

/**
 * Fecha modal de detalhes
 */
const fecharModalDetalhes = () => {
  modalDetalhesAberto.value = false
  parceiroSelecionado.value = null
}

/**
 * Monta endereço completo
 */
const montarEnderecoCompleto = (parceiro) => {
  const partes = []

  if (parceiro.rua) partes.push(parceiro.rua)
  if (parceiro.numero) partes.push(parceiro.numero)
  if (parceiro.complemento) partes.push(parceiro.complemento)
  if (parceiro.bairro) partes.push(parceiro.bairro)
  if (parceiro.cidade) partes.push(parceiro.cidade)
  if (parceiro.estado) partes.push(parceiro.estado)
  if (parceiro.cep) partes.push(`CEP: ${parceiro.cep}`)

  return partes.length > 0 ? partes.join(', ') : null
}


// ===== FUNÇÕES DA API =====

/**
 * Carrega parceiros da API
 */
const carregarParceiros = async () => {
  carregando.value = true
  erro.value = ''

  try {
    const response = await axios.get('/parceiros')

    if (response.data.success) {
      // A API retorna: { success: true, message: '...', data: [...] }
      const dadosAPI = response.data.data || []

      // Mapeia os dados da API para o formato esperado pelo frontend
      parceiros.value = dadosAPI.map(parceiro => ({
        id: parceiro.id,
        nome: parceiro.nome,
        tipo: parceiro.tipo_parceiro?.nome || parceiro.tipo_parceiro_id || 'outro',
        tipo_parceiro_id: parceiro.tipo_parceiro_id,
        cnpj: parceiro.cnpj,
        telefone: parceiro.telefone,
        email: parceiro.email,
        site: parceiro.site,
        responsavel: parceiro.responsavel,
        cep: parceiro.cep,
        logradouro: parceiro.logradouro,
        numero: parceiro.numero,
        complemento: parceiro.complemento,
        bairro: parceiro.bairro,
        cidade: parceiro.cidade,
        estado: parceiro.estado,
        observacoes: parceiro.observacoes,
        status: parceiro.situacao?.nome?.toLowerCase() || (parceiro.situacao_id == 1 ? 'ativo' : parceiro.situacao_id == 2 ? 'inativo' : 'ativo'), // Usa o campo situacao
        situacao: parceiro.situacao?.nome?.toLowerCase() || (parceiro.situacao_id == 1 ? 'ativo' : parceiro.situacao_id == 2 ? 'inativo' : null),
        situacao_id: parceiro.situacao_id,
        created_at: parceiro.created_at,
        updated_at: parceiro.updated_at,
        deleted_at: parceiro.deleted_at
      }))


      // Atualiza indicadores
      indicadores.value = {
        total: parceiros.value.length,
        ativos: parceiros.value.filter(p => (p.situacao || p.status || 'ativo').toLowerCase() === 'ativo').length,
        convenios: parceiros.value.filter(p => p.tipo === 'Convênio').length,
        ultimoCadastro: parceiros.value.length > 0
          ? formatarData(Math.max(...parceiros.value.map(p => new Date(p.created_at))))
          : 'Nunca'
      }

      filtrarParceiros()

    } else {
      throw new Error(response.data.message || 'Erro ao carregar parceiros')
    }

  } catch (error) {
    console.error('Erro ao carregar parceiros:', error)

    if (error.response) {
      // Erro da API do backend
      if (error.response.status === 404) {
        erro.value = 'Serviço de parceiros não encontrado. Verifique se o backend está rodando.'
      } else if (error.response.status === 500) {
        erro.value = 'Erro interno do servidor. Tente novamente mais tarde.'
      } else {
        erro.value = error.response.data?.message || `Erro do servidor: ${error.response.status}`
      }
    } else if (error.request) {
      // Erro de conexão
      erro.value = 'Erro de conexão. Verifique sua internet e se o backend está rodando em http://localhost:8000'
    } else {
      erro.value = 'Erro inesperado. Tente novamente.'
    }

    parceiros.value = []
    parceirosFiltrados.value = []
    indicadores.value = {
      total: 0,
      ativos: 0,
      convenios: 0,
      ultimoCadastro: 'Nunca'
    }

  } finally {
    carregando.value = false
  }
}


/**
 * Alterna status do parceiro
 */
const toggleStatus = async (parceiro) => {
  const novoStatus = parceiro.status === 'ativo' ? 'inativo' : 'ativo'
  const acao = novoStatus === 'ativo' ? 'ativar' : 'desativar'

  if (!confirm(`Tem certeza que deseja ${acao} o parceiro "${parceiro.nome}"?`)) {
    return
  }

  try {
    // TODO: Implementar rota PATCH no backend para alterar status
    // await axios.patch(`/parceiros/${parceiro.id}/status`, {
    //   status: novoStatus
    // })

    // Atualiza status localmente
    parceiro.status = novoStatus

    // Atualiza indicadores
    indicadores.value.ativos = parceiros.value.filter(p => (p.situacao || p.status || 'ativo').toLowerCase() === 'ativo').length

    mensagemSucesso.value = `Parceiro ${novoStatus === 'ativo' ? 'ativado' : 'desativado'} com sucesso!`
    limparMensagens()

  } catch (error) {
    console.error('Erro ao alterar status:', error)
    mensagemErro.value = 'Erro ao alterar status do parceiro'
    limparMensagens()
  }
}

/**
 * Abre modal de confirmação para excluir parceiro
 */
const excluirParceiro = (parceiro) => {
  parceiroParaExcluir.value = parceiro
  modalExclusaoAberto.value = true
}

/**
 * Confirma exclusão do parceiro
 */
const confirmarExclusao = async () => {
  if (!parceiroParaExcluir.value) return

  try {
    carregando.value = true
    // Chama a API para excluir o parceiro
    const response = await axios.delete(`/parceiros/${parceiroParaExcluir.value.id}`)

    if (response.data.success) {
      // Remove da lista local apenas se a API confirmou sucesso
      const index = parceiros.value.findIndex(p => p.id === parceiroParaExcluir.value.id)
      if (index > -1) {
        parceiros.value.splice(index, 1)
      }

      // Atualiza indicadores
      indicadores.value.total = parceiros.value.length
      indicadores.value.ativos = parceiros.value.filter(p => (p.situacao || p.status || 'ativo').toLowerCase() === 'ativo').length
      indicadores.value.convenios = parceiros.value.filter(p => p.tipo === 'Convênio').length

      // Refiltra lista
      filtrarParceiros()

      mensagemSucesso.value = response.data.message || 'Parceiro excluído com sucesso!'
      limparMensagens()

      // Fecha modal
      modalExclusaoAberto.value = false
      parceiroParaExcluir.value = null
    } else {
      mensagemErro.value = response.data.message || 'Erro ao excluir parceiro'
      limparMensagens()
    }

  } catch (error) {
    console.error('Erro ao excluir parceiro:', error)

    if (error.response) {
      // Erro da API do backend
      if (error.response.status === 404) {
        mensagemErro.value = 'Parceiro não encontrado'
      } else if (error.response.status === 400) {
        mensagemErro.value = error.response.data.message || 'Parceiro já foi excluído'
      } else {
        mensagemErro.value = error.response.data.message || 'Erro do servidor'
      }
    } else if (error.request) {
      // Erro de conexão
      mensagemErro.value = 'Erro de conexão. Verifique sua internet e se o backend está rodando.'
    } else {
      mensagemErro.value = 'Erro inesperado. Tente novamente.'
    }

    limparMensagens()
  } finally {
    carregando.value = false
  }
}

/**
 * Cancela exclusão do parceiro
 */
const cancelarExclusao = () => {
  modalExclusaoAberto.value = false
  parceiroParaExcluir.value = null
}

// ===== INICIALIZAÇÃO =====

/**
 * Carrega parceiros quando o componente é montado
 */
onMounted(() => {
  carregarParceiros()
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
  background-color: #2563eb;
}

.peer:focus~div {
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
}

/* Hover effects */
button:hover {
  transform: translateY(-1px);
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
</style>