<template>
  <div class="max-w-4xl mx-auto p-6">
    <!-- Header da Página -->
    <PageHeader :title="isEditando ? 'Editar Parceiro' : 'Novo Parceiro'"
      :description="isEditando ? 'Edite as informações do parceiro' : 'Cadastre um novo parceiro no sistema'"
      :icon="Users" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Parceiros', to: '/configuracoes/parceiros' },
        { label: isEditando ? 'Editar Parceiro' : 'Novo Parceiro' }
      ]" icon-bg-color="blue" class="mb-8">
      <template #actions>
        <button @click="voltarParaLista"
          class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors flex items-center space-x-2">
          <ArrowLeft class="w-4 h-4" />
          <span>Voltar</span>
        </button>
      </template>
    </PageHeader>

    <!-- Formulário -->
    <div class="bg-white rounded-lg shadow-xl">
      <!-- Estado de carregamento -->
      <div v-if="carregandoParceiro" class="p-6 flex items-center justify-center">
        <div class="flex items-center space-x-3">
          <Loader2 class="w-6 h-6 animate-spin text-blue-600" />
          <span class="text-gray-600">Carregando dados do parceiro...</span>
        </div>
      </div>

      <form v-else @submit.prevent="salvarParceiro" class="p-6 space-y-6">
        <!-- Informações Básicas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Nome da Empresa -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Nome da Empresa/Parceiro *
            </label>
            <input type="text" v-model="formulario.nome"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.nome }"
              placeholder="Ex: Laboratório Central" />
            <div v-if="erros.nome" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
              <AlertCircle class="w-4 h-4" />
              <span>{{ erros.nome }}</span>
            </div>
          </div>

          <!-- Tipo de Parceiro, Situação e CNPJ na mesma linha -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:col-span-2">
            <!-- Tipo de Parceiro -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tipo de Parceiro *
              </label>
              <select v-model="formulario.tipo_parceiro_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.tipo_parceiro_id }">
                <option value="">Selecione o tipo</option>
                <option v-for="tipo in tiposParceiros" :key="tipo.id" :value="tipo.id">
                  {{ tipo.nome }}
                </option>
              </select>
              <div v-if="erros.tipo_parceiro_id" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                <AlertCircle class="w-4 h-4" />
                <span>{{ erros.tipo_parceiro_id }}</span>
              </div>
            </div>

            <!-- Situação -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Situação
              </label>
              <select v-model="formulario.situacao_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.situacao_id }">
                <option value="">Selecione a situação</option>
                <option value="1">Ativo</option>
                <option value="2">Inativo</option>
              </select>
              <div v-if="erros.situacao_id" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                <AlertCircle class="w-4 h-4" />
                <span>{{ erros.situacao_id }}</span>
              </div>
            </div>

            <!-- CNPJ -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                CNPJ
              </label>
              <input type="text" v-model="formulario.cnpj" @input="formatarCNPJ"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.cnpj }"
                placeholder="00.000.000/0000-00" maxlength="18" />
              <div v-if="erros.cnpj" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                <AlertCircle class="w-4 h-4" />
                <span>{{ erros.cnpj }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Informações de Contato -->
        <div class="border-t border-gray-200 pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Informações de Contato</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Telefone -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Telefone *
              </label>
              <input type="text" v-model="formulario.telefone" @input="formatarTelefone"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.telefone }"
                placeholder="(48) 99999-9999" maxlength="15" />
              <div v-if="erros.telefone" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                <AlertCircle class="w-4 h-4" />
                <span>{{ erros.telefone }}</span>
              </div>
            </div>

            <!-- E-mail -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                E-mail
              </label>
              <input type="email" v-model="formulario.email"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.email }"
                placeholder="contato@empresa.com" />
              <div v-if="erros.email" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                <AlertCircle class="w-4 h-4" />
                <span>{{ erros.email }}</span>
              </div>
            </div>

            <!-- Site -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Site
              </label>
              <input type="url" v-model="formulario.site"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.site }"
                placeholder="https://www.empresa.com" />
              <div v-if="erros.site" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                <AlertCircle class="w-4 h-4" />
                <span>{{ erros.site }}</span>
              </div>
            </div>

            <!-- Responsável -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Responsável pelo Contato
              </label>
              <input type="text" v-model="formulario.responsavel"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': erros.responsavel }"
                placeholder="Nome do responsável" />
              <div v-if="erros.responsavel" class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                <AlertCircle class="w-4 h-4" />
                <span>{{ erros.responsavel }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Endereço -->
        <div class="border-t border-gray-200 pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Endereço</h3>
          <!-- CEP, Cidade e Estado na mesma linha -->
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
            <!-- CEP -->
            <div class="lg:col-span-3">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                CEP
              </label>
              <input type="text" v-model="formulario.cep" @input="formatarCEP" @blur="buscarCEP"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="00000-000" maxlength="9" />
            </div>

            <!-- Cidade -->
            <div class="lg:col-span-5">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Cidade
              </label>
              <input type="text" v-model="formulario.cidade"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Nome da cidade" />
            </div>

            <!-- Estado -->
            <div class="lg:col-span-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Estado
              </label>
              <select v-model="formulario.estado"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione o estado</option>
                <option value="AC">Acre</option>
                <option value="AL">Alagoas</option>
                <option value="AP">Amapá</option>
                <option value="AM">Amazonas</option>
                <option value="BA">Bahia</option>
                <option value="CE">Ceará</option>
                <option value="DF">Distrito Federal</option>
                <option value="ES">Espírito Santo</option>
                <option value="GO">Goiás</option>
                <option value="MA">Maranhão</option>
                <option value="MT">Mato Grosso</option>
                <option value="MS">Mato Grosso do Sul</option>
                <option value="MG">Minas Gerais</option>
                <option value="PA">Pará</option>
                <option value="PB">Paraíba</option>
                <option value="PR">Paraná</option>
                <option value="PE">Pernambuco</option>
                <option value="PI">Piauí</option>
                <option value="RJ">Rio de Janeiro</option>
                <option value="RN">Rio Grande do Norte</option>
                <option value="RS">Rio Grande do Sul</option>
                <option value="RO">Rondônia</option>
                <option value="RR">Roraima</option>
                <option value="SC">Santa Catarina</option>
                <option value="SP">São Paulo</option>
                <option value="SE">Sergipe</option>
                <option value="TO">Tocantins</option>
              </select>
            </div>
          </div>

          <!-- Outros campos de endereço -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Rua/Logradouro -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Rua/Logradouro
              </label>
              <input type="text" v-model="formulario.logradouro"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Nome da rua" />
            </div>

            <!-- Bairro -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Bairro
              </label>
              <input type="text" v-model="formulario.bairro"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Nome do bairro" />
            </div>

            <!-- Número -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Número
              </label>
              <input type="text" v-model="formulario.numero"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="123" />
            </div>

            <!-- Complemento -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Complemento
              </label>
              <input type="text" v-model="formulario.complemento"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Sala, andar..." />
            </div>
          </div>
        </div>

        <!-- Observações -->
        <div class="border-t border-gray-200 pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Observações</h3>
          <div>
            <textarea v-model="formulario.observacoes" rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Informações adicionais sobre o parceiro..." />
          </div>
        </div>

        <!-- Botões -->
        <div class="border-t border-gray-200 pt-6 flex justify-end space-x-3">
          <button type="button" @click="voltarParaLista"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors font-medium">
            Cancelar
          </button>
          <button type="submit" :disabled="salvandoParceiro"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed transition-colors font-medium flex items-center space-x-2">
            <Loader2 v-if="salvandoParceiro" class="w-4 h-4 animate-spin" />
            <Save v-else class="w-4 h-4" />
            <span>{{ salvandoParceiro ? 'Salvando...' : (isEditando ? 'Salvar Alterações' : 'Cadastrar Parceiro')
              }}</span>
          </button>
        </div>
      </form>
    </div>

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
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from '../../services/axios.js'
import {
  Users,
  ArrowLeft,
  AlertCircle,
  Loader2,
  Save,
  CheckCircle
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()

// Estados reativos
const formulario = ref({
  nome: '',
  tipo_parceiro_id: '',
  situacao_id: '',
  cnpj: '',
  telefone: '',
  email: '',
  site: '',
  responsavel: '',
  cep: '',
  logradouro: '',
  numero: '',
  complemento: '',
  bairro: '',
  cidade: '',
  estado: '',
  observacoes: ''
})

const erros = ref({})
const salvandoParceiro = ref(false)
const tiposParceiros = ref([])
const mensagemSucesso = ref('')
const mensagemErro = ref('')
const parceiroId = ref(null)
const isEditando = ref(false)
const carregandoParceiro = ref(false)

// ===== VALIDAÇÃO =====

const validarFormulario = () => {
  erros.value = {}

  // Nome obrigatório
  if (!formulario.value.nome.trim()) {
    erros.value.nome = 'Nome da empresa é obrigatório'
  }

  // Tipo obrigatório
  if (!formulario.value.tipo_parceiro_id) {
    erros.value.tipo_parceiro_id = 'Tipo de parceiro é obrigatório'
  }

  // Telefone obrigatório
  if (!formulario.value.telefone.trim()) {
    erros.value.telefone = 'Telefone é obrigatório'
  }

  // Email válido se preenchido
  if (formulario.value.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formulario.value.email)) {
    erros.value.email = 'E-mail inválido'
  }

  // Validar CNPJ se preenchido
  if (formulario.value.cnpj && formulario.value.cnpj.replace(/\D/g, '').length !== 14) {
    erros.value.cnpj = 'CNPJ deve ter 14 dígitos'
  }

  return Object.keys(erros.value).length === 0
}

// ===== FORMATAÇÃO =====

const formatarCNPJ = (event) => {
  let valor = event.target.value.replace(/\D/g, '')
  valor = valor.replace(/^(\d{2})(\d)/, '$1.$2')
  valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
  valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2')
  valor = valor.replace(/(\d{4})(\d)/, '$1-$2')
  formulario.value.cnpj = valor
}

const formatarTelefone = (event) => {
  let valor = event.target.value.replace(/\D/g, '')
  valor = valor.replace(/^(\d{2})(\d)/, '($1) $2')
  valor = valor.replace(/(\d{4})(\d)/, '$1-$2')
  valor = valor.replace(/(\d{5})(\d)/, '$1-$2')
  formulario.value.telefone = valor
}

const formatarCEP = (event) => {
  let valor = event.target.value.replace(/\D/g, '')
  valor = valor.replace(/^(\d{5})(\d)/, '$1-$2')
  formulario.value.cep = valor
}

const buscarCEP = async () => {
  const cep = formulario.value.cep.replace(/\D/g, '')
  if (cep.length === 8) {
    try {
      const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
      const data = await response.json()

      if (!data.erro) {
        formulario.value.logradouro = data.logradouro
        formulario.value.bairro = data.bairro
        formulario.value.cidade = data.localidade
        formulario.value.estado = data.uf
      }
    } catch (error) {
      console.error('Erro ao buscar CEP:', error)
    }
  }
}

// ===== FUNÇÕES DA API =====

const carregarTiposParceiros = async () => {
  try {
    const response = await axios.get('/parceiros-tipos')

    if (response.data.success) {
      tiposParceiros.value = response.data.data
    } else {
      tiposParceiros.value = []
      mensagemErro.value = response.data.message || 'Erro ao carregar tipos de parceiros'
    }
  } catch (error) {
    console.error('Erro ao carregar tipos de parceiros:', error)
    tiposParceiros.value = []
    if (error.response) {
      mensagemErro.value = error.response.data?.message || 'Erro ao carregar tipos de parceiros'
    } else if (error.request) {
      mensagemErro.value = 'Erro de conexão. Verifique sua internet e tente novamente.'
    } else {
      mensagemErro.value = 'Erro inesperado ao carregar tipos de parceiros.'
    }
  }
}

const carregarParceiro = async (id) => {
  carregandoParceiro.value = true
  mensagemErro.value = ''

  try {
    const response = await axios.get(`/parceiros/${id}`)

    if (response.data.success && response.data.data) {
      const parceiro = response.data.data

      // Preenche o formulário com os dados do parceiro
      formulario.value = {
        nome: parceiro.nome || '',
        tipo_parceiro_id: parceiro.tipo_parceiro_id || '',
        situacao_id: parceiro.situacao_id || '',
        cnpj: parceiro.cnpj || '',
        telefone: parceiro.telefone || '',
        email: parceiro.email || '',
        site: parceiro.site || '',
        responsavel: parceiro.responsavel || '',
        cep: parceiro.cep || '',
        logradouro: parceiro.logradouro || '',
        numero: parceiro.numero || '',
        complemento: parceiro.complemento || '',
        bairro: parceiro.bairro || '',
        cidade: parceiro.cidade || '',
        estado: parceiro.estado || '',
        observacoes: parceiro.observacoes || ''
      }
    } else {
      mensagemErro.value = 'Parceiro não encontrado'
    }
  } catch (error) {
    console.error('Erro ao carregar parceiro:', error)
    if (error.response) {
      mensagemErro.value = error.response.data.message || 'Erro ao carregar dados do parceiro'
    } else {
      mensagemErro.value = 'Erro de conexão. Verifique sua internet e tente novamente.'
    }
  } finally {
    carregandoParceiro.value = false
  }
}

const salvarParceiro = async () => {
  if (!validarFormulario()) {
    return
  }

  salvandoParceiro.value = true
  mensagemErro.value = ''
  mensagemSucesso.value = ''

  try {
    // Prepara dados para envio
    const dadosParaEnvio = {
      nome: formulario.value.nome.trim(),
      tipo_parceiro_id: formulario.value.tipo_parceiro_id,
      situacao_id: formulario.value.situacao_id || null,
      cnpj: formulario.value.cnpj.replace(/\D/g, '') || null,
      telefone: formulario.value.telefone.trim(),
      email: formulario.value.email.trim() || null,
      site: formulario.value.site.trim() || null,
      responsavel: formulario.value.responsavel.trim() || null,
      cep: formulario.value.cep.replace(/\D/g, '') || null,
      logradouro: formulario.value.logradouro.trim() || null,
      numero: formulario.value.numero.trim() || null,
      complemento: formulario.value.complemento.trim() || null,
      bairro: formulario.value.bairro.trim() || null,
      cidade: formulario.value.cidade.trim() || null,
      estado: formulario.value.estado || null,
      observacoes: formulario.value.observacoes.trim() || null
    }

    // Escolhe o método e URL baseado no modo (criação ou edição)
    let response
    if (isEditando.value && parceiroId.value) {
      // Modo edição - usa PUT
      response = await axios.put(`/parceiros/${parceiroId.value}`, dadosParaEnvio)
    } else {
      // Modo criação - usa POST
      response = await axios.post('/parceiros', dadosParaEnvio)
    }

    if (response.data.success) {
      mensagemSucesso.value = response.data.message || (isEditando.value ? 'Parceiro atualizado com sucesso!' : 'Parceiro cadastrado com sucesso!')

      if (!isEditando.value) {
        // Só limpa o formulário se estiver criando um novo parceiro
        formulario.value = {
          nome: '',
          tipo_parceiro_id: '',
          situacao_id: '',
          cnpj: '',
          telefone: '',
          email: '',
          site: '',
          responsavel: '',
          cep: '',
          logradouro: '',
          numero: '',
          complemento: '',
          bairro: '',
          cidade: '',
          estado: '',
          observacoes: ''
        }
        erros.value = {}
      }

      // Redireciona para a lista após um breve delay
      setTimeout(() => {
        router.push('/parceiros')
      }, 1500)
    } else {
      mensagemErro.value = response.data.message || (isEditando.value ? 'Erro ao atualizar parceiro' : 'Erro ao cadastrar parceiro')
    }

  } catch (error) {
    console.error('Erro ao salvar parceiro:', error)

    if (error.response) {
      // Erros de validação do backend
      if (error.response.status === 422 && error.response.data.errors) {
        const backendErrors = error.response.data.errors
        erros.value = {}

        // Mapeia erros do backend para o frontend
        Object.keys(backendErrors).forEach(campo => {
          erros.value[campo] = Array.isArray(backendErrors[campo])
            ? backendErrors[campo][0]
            : backendErrors[campo]
        })

        mensagemErro.value = 'Corrija os erros abaixo'
      } else {
        mensagemErro.value = error.response.data.message || 'Erro do servidor'
      }
    } else if (error.request) {
      mensagemErro.value = 'Erro de conexão. Verifique sua internet e tente novamente.'
    } else {
      mensagemErro.value = 'Erro inesperado. Tente novamente.'
    }
  } finally {
    salvandoParceiro.value = false
  }
}

const voltarParaLista = () => {
  router.push('/parceiros')
}

// ===== LIFECYCLE =====

onMounted(() => {
  carregarTiposParceiros()

  // Verifica se há um ID na rota para carregar dados para edição
  const id = route.params.id
  if (id) {
    parceiroId.value = id
    isEditando.value = true
    carregarParceiro(id)
  }
})
</script>

<style scoped>
/* Estilos específicos da página se necessário */
</style>
