<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Gerenciamento de Usuários" description="Gerencie os usuários do sistema da clínica"
      :icon="UsersIcon" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Usuários' }
      ]" icon-bg-color="blue" class="mb-8" />

    <!-- Toast de Notificação -->
    <div v-if="toast.show" :class="[
      'fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300',
      toast.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    ]">
      <div class="flex items-center">
        <svg v-if="toast.type === 'success'" class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            clip-rule="evenodd" />
        </svg>
        <svg v-else class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd"
            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
            clip-rule="evenodd" />
        </svg>
        {{ toast.message }}
      </div>
    </div>

    <!-- Formulário de Cadastro/Edição -->
    <div class="mb-8">
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-medium text-gray-900">
            {{ usuarioEditando ? 'Editar Usuário' : 'Novo Usuário' }}
          </h2>
        </div>
        <div class="p-6">
          <!-- Formulário -->
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Nome Completo -->
              <div>
                <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                  Nome Completo *
                </label>
                <input id="nome" ref="nomeInput" v-model="form.nome" type="text" required autofocus autocomplete="name"
                  aria-label="Nome completo do usuário" :class="[
                    'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm',
                    errors.nome ? 'border-red-300' : 'border-gray-300'
                  ]" placeholder="Digite o nome completo" />
                <p v-if="errors.nome" class="mt-1 text-sm text-red-600">
                  {{ errors.nome }}
                </p>
              </div>

              <!-- E-mail -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                  E-mail *
                </label>
                <input id="email" v-model="form.email" type="email" required autocomplete="email"
                  aria-label="E-mail do usuário" :class="[
                    'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm',
                    errors.email ? 'border-red-300' : 'border-gray-300'
                  ]" placeholder="exemplo@email.com" />
                <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                  {{ errors.email }}
                </p>
              </div>

              <!-- Senha (apenas para novo usuário) -->
              <div v-if="!usuarioEditando">
                <label for="senha" class="block text-sm font-medium text-gray-700 mb-2">
                  Senha *
                </label>
                <input id="senha" v-model="form.senha" type="password" required autocomplete="new-password"
                  aria-label="Senha do usuário" :class="[
                    'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm',
                    errors.senha ? 'border-red-300' : 'border-gray-300'
                  ]" placeholder="Digite a senha" />
                <p v-if="errors.senha" class="mt-1 text-sm text-red-600">
                  {{ errors.senha }}
                </p>
              </div>

              <!-- Confirmar Senha (apenas para novo usuário) -->
              <div v-if="!usuarioEditando">
                <label for="confirmarSenha" class="block text-sm font-medium text-gray-700 mb-2">
                  Confirmar Senha *
                </label>
                <input id="confirmarSenha" v-model="form.confirmarSenha" type="password" required
                  autocomplete="new-password" aria-label="Confirmação da senha" :class="[
                    'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm',
                    errors.confirmarSenha ? 'border-red-300' : 'border-gray-300'
                  ]" placeholder="Confirme a senha" />
                <p v-if="errors.confirmarSenha" class="mt-1 text-sm text-red-600">
                  {{ errors.confirmarSenha }}
                </p>
              </div>

              <!-- Tipo de Usuário -->
              <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                  Tipo de Usuário *
                </label>
                <select id="tipo" v-model="form.tipo" required aria-label="Tipo de usuário" :class="[
                  'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm',
                  errors.tipo ? 'border-red-300' : 'border-gray-300'
                ]">
                  <option value="">Selecione o tipo</option>
                  <option value="Administrador">Administrador</option>
                  <option value="Recepção">Recepção</option>
                  <option value="Profissional">Profissional</option>
                </select>
                <p v-if="errors.tipo" class="mt-1 text-sm text-red-600">
                  {{ errors.tipo }}
                </p>
              </div>

              <!-- Status -->
              <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                  Status
                </label>
                <select id="status" v-model="form.status" aria-label="Status do usuário"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                  <option value="ativo">Ativo</option>
                  <option value="inativo">Inativo</option>
                </select>
              </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-end space-x-3">
              <button v-if="usuarioEditando" type="button" @click="cancelarEdicao"
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
              </button>
              <button type="submit" :disabled="loading" :class="[
                'px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                loading
                  ? 'bg-gray-400 cursor-not-allowed'
                  : 'bg-blue-600 hover:bg-blue-700'
              ]">
                <span v-if="loading" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  Salvando...
                </span>
                <span v-else>
                  {{ usuarioEditando ? 'Atualizar' : 'Salvar' }}
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Lista de Usuários -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <h2 class="text-lg font-medium text-gray-900 mb-4 sm:mb-0">Lista de Usuários</h2>

          <!-- Campo de Filtro -->
          <div class="w-full sm:w-80">
            <label for="filtro" class="sr-only">Filtrar usuários</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input id="filtro" v-model="filtro" type="text"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="Buscar por nome ou e-mail..." aria-label="Filtrar usuários por nome ou e-mail" />
            </div>
          </div>
        </div>
      </div>

      <div class="p-6">
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-8">
          <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
          </svg>
        </div>

        <!-- Tabela de Usuários -->
        <div v-else-if="usuariosFiltrados.length > 0"
          class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
          <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Nome
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  E-mail
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tipo
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Criado em
                </th>
                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">Ações</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="usuario in usuariosFiltrados" :key="usuario.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ usuario.nome }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ usuario.email }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getTipoClass(usuario.tipo)"
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getTipoLabel(usuario.tipo) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(usuario.status)"
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getStatusLabel(usuario.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatarData(usuario.criadoEm) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end space-x-2">
                    <button @click="editarUsuario(usuario)"
                      class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      title="Editar usuário" aria-label="Editar usuário">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button @click="confirmarExclusao(usuario)"
                      class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500"
                      title="Excluir usuário" aria-label="Excluir usuário">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Estado Vazio -->
        <div v-else class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum usuário encontrado</h3>
          <p class="mt-1 text-sm text-gray-500">
            {{ filtro ? 'Tente ajustar o filtro de busca.' : 'Comece cadastrando o primeiro usuário do sistema.' }}
          </p>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <ActionModal :open="modalExclusao.show" titulo="Confirmar Exclusão" subtitulo=""
      :action-label="loading ? 'Excluindo...' : 'Excluir'" :action-disabled="loading" modal-width="sm:max-w-md"
      @acao="excluirUsuario" @cancel="cancelarExclusao">
      <div v-if="modalExclusao.usuario" class="space-y-4">
        <div class="flex items-start space-x-3">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
          </div>
          <div class="flex-1">
            <p class="text-sm text-gray-700">
              Tem certeza que deseja excluir o usuário <strong>{{ modalExclusao.usuario.nome }}</strong>?
            </p>
            <p class="text-sm text-red-600 mt-2">
              Esta ação não pode ser desfeita e todos os dados do usuário serão permanentemente removidos.
            </p>
          </div>
        </div>
      </div>
    </ActionModal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue'
import axios from '../../services/axios.js'
import { UsersIcon } from '@heroicons/vue/24/outline'

// ===== CONFIGURAÇÃO DO SERVIÇO =====
const profiles = ref([])
const situacoes = ref([])

// Funções auxiliares para mapeamento
const getProfileIdByNome = (nome) => {
  const profile = profiles.value.find(p => p.nome === nome)
  return profile ? profile.id : null
}

const getSituacaoIdByNome = (nome) => {
  const situacao = situacoes.value.find(s => s.nome === nome)
  return situacao ? situacao.id : null
}

const getProfileNomeById = (id) => {
  const profile = profiles.value.find(p => p.id === id)
  return profile ? profile.nome : ''
}

const getSituacaoNomeById = (id) => {
  const situacao = situacoes.value.find(s => s.id === id)
  return situacao ? situacao.nome : ''
}

// Função para traduzir mensagens de erro do Laravel
const traduzirErro = (mensagem) => {
  const traducoes = {
    'The email has already been taken.': 'Este e-mail já está sendo utilizado por outro usuário.',
    'The email field is required.': 'O campo e-mail é obrigatório.',
    'The name field is required.': 'O campo nome é obrigatório.',
    'The password field is required.': 'O campo senha é obrigatório.',
    'The password must be at least 6 characters.': 'A senha deve ter pelo menos 8 caracteres.',
    'The password must be at least 8 characters.': 'A senha deve ter pelo menos 8 caracteres.',
    'The profile id must exist.': 'O perfil selecionado é inválido.',
    'The situacao id must exist.': 'A situação selecionada é inválida.'
  }

  return traducoes[mensagem] || mensagem
}

// Valida dados do usuário
const validarUsuario = (dados, isEdicao = false) => {
  const erros = []

  if (!dados.nome?.trim()) {
    erros.push("Nome é obrigatório")
  }

  if (!dados.email?.trim()) {
    erros.push("E-mail é obrigatório")
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(dados.email)) {
    erros.push("E-mail inválido")
  }

  if (!isEdicao) {
    if (!dados.senha?.trim()) {
      erros.push("Senha é obrigatória")
    } else if (dados.senha.length < 8) {
      erros.push("Senha deve ter pelo menos 8 caracteres")
    }

    if (!dados.confirmarSenha?.trim()) {
      erros.push("Confirmação de senha é obrigatória")
    } else if (dados.senha !== dados.confirmarSenha) {
      erros.push("Senhas não coincidem")
    }
  }

  if (!dados.tipo) {
    erros.push("Tipo de usuário é obrigatório")
  }

  if (erros.length > 0) {
    throw new Error(erros.join(", "))
  }
}

// Serviço de usuários usando API
const usuariosService = {
  async listarUsuarios() {
    try {
      const response = await axios.get('/usuarios')
      if (response.data.success) {
        return response.data.data.map(usuario => ({
          id: usuario.id,
          nome: usuario.name,
          email: usuario.email,
          tipo: usuario.profile?.nome || '',
          status: usuario.situacao?.nome || 'ativo',
          criadoEm: usuario.created_at
        }))
      }
      throw new Error(response.data.message || 'Erro ao carregar usuários')
    } catch (error) {
      console.error("Erro ao listar usuários:", error)
      if (error.response?.data?.message) {
        throw new Error(error.response.data.message)
      }
      throw new Error("Erro ao carregar usuários")
    }
  },

  async salvarUsuario(dadosUsuario) {
    try {
      // Mapear dados do frontend para o formato da API
      const dadosParaAPI = {
        name: dadosUsuario.nome,
        email: dadosUsuario.email,
        password: dadosUsuario.senha,
        profile_id: dadosUsuario.profile_id,
        situacao_id: dadosUsuario.situacao_id
      }

      const response = await axios.post('/usuarios', dadosParaAPI)
      if (response.data.success) {
        const usuario = response.data.data
        return {
          id: usuario.id,
          nome: usuario.name,
          email: usuario.email,
          tipo: usuario.profile?.nome || '',
          status: usuario.situacao?.nome || 'ativo',
          criadoEm: usuario.created_at
        }
      }
      throw new Error(traduzirErro(response.data.message || 'Erro ao salvar usuário'))
    } catch (error) {
      console.error("Erro ao salvar usuário:", error)
      if (error.response?.data?.errors) {
        const errors = Object.values(error.response.data.errors).flat()
        const errorsTraduzidos = errors.map(erro => traduzirErro(erro))
        throw new Error(errorsTraduzidos.join(', '))
      }
      if (error.response?.data?.message) {
        throw new Error(traduzirErro(error.response.data.message))
      }
      throw error
    }
  },

  async editarUsuario(id, dadosUsuario) {
    try {
      // Mapear dados do frontend para o formato da API
      const dadosParaAPI = {
        name: dadosUsuario.nome,
        email: dadosUsuario.email,
        profile_id: dadosUsuario.profile_id,
        situacao_id: dadosUsuario.situacao_id
      }

      // Adicionar senha apenas se fornecida
      if (dadosUsuario.senha && dadosUsuario.senha.trim()) {
        dadosParaAPI.password = dadosUsuario.senha
      }

      const response = await axios.put(`/usuarios/${id}`, dadosParaAPI)
      if (response.data.success) {
        const usuario = response.data.data
        return {
          id: usuario.id,
          nome: usuario.name,
          email: usuario.email,
          tipo: usuario.profile?.nome || '',
          status: usuario.situacao?.nome || 'ativo',
          criadoEm: usuario.created_at
        }
      }
      throw new Error(traduzirErro(response.data.message || 'Erro ao atualizar usuário'))
    } catch (error) {
      console.error("Erro ao editar usuário:", error)
      if (error.response?.data?.errors) {
        const errors = Object.values(error.response.data.errors).flat()
        const errorsTraduzidos = errors.map(erro => traduzirErro(erro))
        throw new Error(errorsTraduzidos.join(', '))
      }
      if (error.response?.data?.message) {
        throw new Error(traduzirErro(error.response.data.message))
      }
      throw error
    }
  },

  async excluirUsuario(id) {
    try {
      const response = await axios.delete(`/usuarios/${id}`)
      if (response.data.success) {
        return true
      }
      throw new Error(response.data.message || 'Erro ao excluir usuário')
    } catch (error) {
      console.error("Erro ao excluir usuário:", error)
      if (error.response?.data?.message) {
        throw new Error(traduzirErro(error.response.data.message))
      }
      throw error
    }
  }
}

// ===== ESTADO REATIVO =====
const usuarios = ref([])
const usuarioEditando = ref(null)
const loading = ref(false)
const filtro = ref('')
const toast = ref({
  show: false,
  message: '',
  type: 'success'
})

// Refs
const nomeInput = ref(null)

// Estado reativo do formulário
const form = reactive({
  nome: '',
  email: '',
  senha: '',
  confirmarSenha: '',
  tipo: '',
  status: 'ativo',
  profile_id: '',
  situacao_id: ''
})

// Estado de erros
const errors = reactive({
  nome: '',
  email: '',
  senha: '',
  confirmarSenha: '',
  tipo: ''
})

// Estado do modal de exclusão
const modalExclusao = reactive({
  show: false,
  usuario: null
})

// ===== COMPUTED PROPERTIES =====
const usuariosFiltrados = computed(() => {
  if (!filtro.value) return usuarios.value

  const termo = filtro.value.toLowerCase()
  return usuarios.value.filter(usuario =>
    usuario.nome.toLowerCase().includes(termo) ||
    usuario.email.toLowerCase().includes(termo)
  )
})

// ===== MÉTODOS UTILITÁRIOS =====
const getTipoClass = (tipo) => {
  const classes = {
    'Administrador': 'bg-red-100 text-red-800',
    'Recepção': 'bg-blue-100 text-blue-800',
    'Profissional': 'bg-green-100 text-green-800'
  }
  return classes[tipo] || 'bg-gray-100 text-gray-800'
}

const getTipoLabel = (tipo) => {
  const labels = {
    'Administrador': 'Administrador',
    'Recepção': 'Recepção',
    'Profissional': 'Profissional'
  }
  return labels[tipo] || tipo
}

const getStatusClass = (status) => {
  return status === 'ativo'
    ? 'bg-green-100 text-green-800'
    : 'bg-red-100 text-red-800'
}

const getStatusLabel = (status) => {
  return status === 'ativo' ? 'Ativo' : 'Inativo'
}

const formatarData = (data) => {
  if (!data) return 'N/A'
  return new Date(data).toLocaleDateString('pt-BR')
}

// Métodos para gerenciar toast
const showToast = (message, type = 'success') => {
  toast.value = {
    show: true,
    message,
    type
  }

  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}

// ===== VALIDAÇÃO =====
const validarFormulario = () => {
  // Limpa erros anteriores
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })

  let isValid = true

  if (!form.nome.trim()) {
    errors.nome = 'Nome é obrigatório'
    isValid = false
  }

  if (!form.email.trim()) {
    errors.email = 'E-mail é obrigatório'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'E-mail inválido'
    isValid = false
  }

  if (!usuarioEditando.value) {
    if (!form.senha.trim()) {
      errors.senha = 'Senha é obrigatória'
      isValid = false
    } else if (form.senha.length < 8) {
      errors.senha = 'Senha deve ter pelo menos 8 caracteres'
      isValid = false
    }

    if (!form.confirmarSenha.trim()) {
      errors.confirmarSenha = 'Confirmação de senha é obrigatória'
      isValid = false
    } else if (form.senha !== form.confirmarSenha) {
      errors.confirmarSenha = 'Senhas não coincidem'
      isValid = false
    }
  }

  if (!form.tipo) {
    errors.tipo = 'Tipo de usuário é obrigatório'
    isValid = false
  }

  return isValid
}

// ===== HANDLERS DOS EVENTOS =====
const handleSubmit = async () => {
  if (!validarFormulario()) {
    return
  }

  try {
    loading.value = true

    // Mapear dados do formulário para a API
    const dadosLimpos = {
      nome: form.nome.trim(),
      email: form.email.trim(),
      senha: form.senha,
      confirmarSenha: form.confirmarSenha,
      tipo: form.tipo,
      status: form.status,
      profile_id: getProfileIdByNome(form.tipo),
      situacao_id: getSituacaoIdByNome(form.status)
    }

    if (usuarioEditando.value) {
      // Modo edição
      const usuarioAtualizado = await usuariosService.editarUsuario(
        usuarioEditando.value.id,
        dadosLimpos
      )

      // Atualiza o usuário na lista
      const index = usuarios.value.findIndex(u => u.id === usuarioEditando.value.id)
      if (index !== -1) {
        usuarios.value[index] = usuarioAtualizado
      }

      showToast('Usuário atualizado com sucesso!')
      usuarioEditando.value = null
    } else {
      // Modo criação
      const novoUsuario = await usuariosService.salvarUsuario(dadosLimpos)
      usuarios.value.push(novoUsuario)
      showToast('Usuário cadastrado com sucesso!')
    }

    limparFormulario()
  } catch (error) {
    console.error('Erro ao salvar usuário:', error)
    showToast(error.message || 'Erro ao salvar usuário. Tente novamente.', 'error')
  } finally {
    loading.value = false
  }
}

const cancelarEdicao = () => {
  usuarioEditando.value = null
  limparFormulario()
}

const editarUsuario = (usuario) => {
  usuarioEditando.value = { ...usuario }
  preencherFormulario(usuario)
}

const confirmarExclusao = (usuario) => {
  modalExclusao.show = true
  modalExclusao.usuario = usuario
}

const excluirUsuario = async () => {
  if (!modalExclusao.usuario) return

  try {
    loading.value = true
    await usuariosService.excluirUsuario(modalExclusao.usuario.id)

    // Remove o usuário da lista
    usuarios.value = usuarios.value.filter(u => u.id !== modalExclusao.usuario.id)

    showToast('Usuário excluído com sucesso!')
    cancelarExclusao()
  } catch (error) {
    console.error('Erro ao excluir usuário:', error)
    showToast('Erro ao excluir usuário. Tente novamente.', 'error')
  } finally {
    loading.value = false
  }
}

const cancelarExclusao = () => {
  modalExclusao.show = false
  modalExclusao.usuario = null
}

// ===== MÉTODOS DO FORMULÁRIO =====
const limparFormulario = () => {
  Object.keys(form).forEach(key => {
    if (key === 'status' || key === 'situacao_id') {
      form[key] = key === 'status' ? 'ativo' : getSituacaoIdByNome('ativo')
    } else {
      form[key] = ''
    }
  })

  // Limpa erros
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })

  // Foca no primeiro campo
  nextTick(() => {
    if (nomeInput.value) {
      nomeInput.value.focus()
    }
  })
}

const preencherFormulario = (usuario) => {
  if (usuario) {
    form.nome = usuario.nome || ''
    form.email = usuario.email || ''
    form.senha = ''
    form.confirmarSenha = ''
    form.tipo = usuario.tipo || ''
    form.status = usuario.status || 'ativo'
    // Mapear os IDs baseados no nome (temporário até termos as APIs)
    form.profile_id = getProfileIdByNome(usuario.tipo) || ''
    form.situacao_id = getSituacaoIdByNome(usuario.status) || ''
  } else {
    limparFormulario()
  }
}

// ===== CARREGAMENTO DE DADOS =====
const carregarUsuarios = async () => {
  try {
    loading.value = true
    usuarios.value = await usuariosService.listarUsuarios()
  } catch (error) {
    console.error('Erro ao carregar usuários:', error)
    showToast('Erro ao carregar usuários.', 'error')
  } finally {
    loading.value = false
  }
}

const carregarProfilesESituacoes = async () => {
  try {
    // Dados fixos temporários até criar as APIs específicas
    profiles.value = [
      { id: 1, nome: 'Administrador' },
      { id: 2, nome: 'Recepção' },
      { id: 3, nome: 'Profissional' }
    ]

    situacoes.value = [
      { id: 1, nome: 'ativo' },
      { id: 2, nome: 'inativo' },
      { id: 3, nome: 'suspenso' },
      { id: 4, nome: 'encerrado' }
    ]
  } catch (error) {
    console.error('Erro ao carregar dados relacionados:', error)
  }
}

// ===== LIFECYCLE HOOKS =====
onMounted(() => {
  carregarProfilesESituacoes()
  carregarUsuarios()
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

/* Modal backdrop */
.fixed.inset-0 {
  backdrop-filter: blur(4px);
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

/* Transições suaves */
.transition-all {
  transition: all 0.2s ease-in-out;
}

/* Estados de hover para tabela */
tbody tr:hover {
  background-color: #f9fafb;
}

/* Estilo para campos com erro */
.border-red-300 {
  border-color: #fca5a5;
  box-shadow: 0 0 0 1px #fca5a5;
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
</style>
