<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Gerenciamento de Permissões" description="Configure as permissões de acesso por tipo de usuário"
      :icon="ShieldCheckIcon" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Permissões' }
      ]" icon-bg-color="indigo" class="mb-8">
      <template #actions>
        <div class="flex space-x-3">
          <button @click="resetarPermissoes" :disabled="loading"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
            Resetar Tudo
          </button>

        </div>
      </template>
    </PageHeader>

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

    <!-- Seletor de Tipo de Usuário -->
    <div class="bg-white shadow rounded-lg mb-6">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Selecionar Perfil de Usuário</h2>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div v-for="tipo in tiposUsuario" :key="tipo.id" @click="selecionarTipoUsuario(tipo.id)" :class="[
            'relative rounded-lg border-2 p-4 cursor-pointer transition-all duration-200 hover:shadow-md',
            tipoUsuarioSelecionado === tipo.id
              ? 'border-indigo-500 bg-indigo-50'
              : 'border-gray-200 hover:border-gray-300'
          ]">
            <div class="flex items-center">
              <div :class="[
                'flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center',
                tipo.cor
              ]">
                <component :is="tipo.icone" class="w-6 h-6" />
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-medium text-gray-900">{{ tipo.nome }}</h3>
                <p class="text-sm text-gray-500">{{ tipo.descricao }}</p>
              </div>
            </div>

            <!-- Indicador de Seleção -->
            <div v-if="tipoUsuarioSelecionado === tipo.id"
              class="absolute top-2 right-2 w-6 h-6 bg-indigo-500 rounded-full flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabela de Permissões -->
    <div v-if="tipoUsuarioSelecionado" class="bg-white shadow rounded-lg">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-medium text-gray-900">
              Permissões para {{ getTipoUsuarioNome(tipoUsuarioSelecionado) }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
              Configure quais ações este perfil pode realizar em cada módulo
            </p>
          </div>

          <!-- Ações Rápidas -->
          <div class="flex space-x-2">
            <button @click="marcarTodas(true)"
              class="px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-md hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500">
              Marcar Todas
            </button>
            <button @click="marcarTodas(false)"
              class="px-3 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500">
              Desmarcar Todas
            </button>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Módulo
              </th>
              <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex flex-col items-center">
                  <svg class="w-4 h-4 mb-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Visualizar
                </div>
              </th>
              <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex flex-col items-center">
                  <svg class="w-4 h-4 mb-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  Criar
                </div>
              </th>
              <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex flex-col items-center">
                  <svg class="w-4 h-4 mb-1 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Editar
                </div>
              </th>
              <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex flex-col items-center">
                  <svg class="w-4 h-4 mb-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  Excluir
                </div>
              </th>
              <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                Ações
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="modulo in modulos" :key="modulo.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div :class="[
                    'flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center',
                    modulo.cor
                  ]">
                    <component :is="modulo.icone" class="w-4 h-4" />
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ modulo.nome }}</div>
                    <div class="text-sm text-gray-500">{{ modulo.descricao }}</div>
                  </div>
                </div>
              </td>

              <!-- Checkboxes de Permissões -->
              <td v-for="permissao in tiposPermissao" :key="permissao.id" class="px-4 py-4 text-center">
                <label class="inline-flex items-center">
                  <input type="checkbox" :checked="temPermissao(modulo.id, permissao.id)"
                    @change="togglePermissao(modulo.id, permissao.id, $event.target.checked)" :class="[
                      'form-checkbox h-5 w-5 rounded border-2 focus:ring-2 focus:ring-offset-2 transition-colors',
                      permissao.cor
                    ]" />
                  <span class="sr-only">{{ permissao.nome }} para {{ modulo.nome }}</span>
                </label>
              </td>

              <!-- Ações por Módulo -->
              <td class="px-6 py-4 text-center">
                <div class="flex justify-center space-x-2">
                  <button @click="marcarTodasPermissoesModulo(modulo.id, true)"
                    class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50"
                    title="Marcar todas as permissões deste módulo">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                    </svg>
                  </button>
                  <button @click="marcarTodasPermissoesModulo(modulo.id, false)"
                    class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50"
                    title="Desmarcar todas as permissões deste módulo">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Rodapé com Botões de Ação -->
      <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-500">
          {{ contarPermissoes() }} permissões ativas para este perfil
        </div>

        <div class="flex space-x-3">
          <button @click="copiarPermissoes"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Copiar de Outro Perfil
          </button>
          <button @click="salvarPermissoes" :disabled="loading" :class="[
            'px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500',
            loading
              ? 'bg-gray-400 cursor-not-allowed'
              : 'bg-indigo-600 hover:bg-indigo-700'
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
              Salvar Permissões
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Estado Inicial -->
    <div v-else class="bg-white shadow rounded-lg">
      <div class="p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Selecione um Perfil de Usuário</h3>
        <p class="mt-1 text-sm text-gray-500">
          Escolha um tipo de usuário acima para configurar suas permissões de acesso.
        </p>
      </div>
    </div>

    <!-- Modal de Cópia de Permissões -->
    <div v-if="modalCopia.show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
      aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
          class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div
                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Copiar Permissões
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 mb-4">
                    Selecione o perfil de onde deseja copiar as permissões para <strong>{{
                      getTipoUsuarioNome(tipoUsuarioSelecionado) }}</strong>:
                  </p>
                  <select v-model="modalCopia.perfilOrigem"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Selecione um perfil</option>
                    <option v-for="tipo in tiposUsuario" :key="tipo.id" :value="tipo.id"
                      :disabled="tipo.id === tipoUsuarioSelecionado">
                      {{ tipo.nome }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button @click="executarCopiaPermissoes" :disabled="!modalCopia.perfilOrigem" type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:bg-gray-400 disabled:cursor-not-allowed">
              Copiar
            </button>
            <button @click="fecharModalCopia" type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ShieldCheckIcon } from '@heroicons/vue/24/outline'

// ===== ÍCONES COMO COMPONENTES =====
const UserIcon = {
  template: `<svg class="w-full h-full text-white" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
  </svg>`
}

const UsersIcon = {
  template: `<svg class="w-full h-full text-white" fill="currentColor" viewBox="0 0 20 20">
    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
  </svg>`
}

const BadgeCheckIcon = {
  template: `<svg class="w-full h-full text-white" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
  </svg>`
}

const CalendarIcon = {
  template: `<svg class="w-full h-full text-white" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
  </svg>`
}

const ClipboardListIcon = {
  template: `<svg class="w-full h-full text-white" fill="currentColor" viewBox="0 0 20 20">
    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a2 2 0 002 2h2a2 2 0 002-2V3a2 2 0 012 2v6h-3a3 3 0 00-3 3v3H6a2 2 0 01-2-2V5zm8 8a1 1 0 00-1 1v3a1 1 0 002 0v-1l.293.293a1 1 0 001.414-1.414L12.414 13H14a1 1 0 100-2h-2z" clip-rule="evenodd" />
  </svg>`
}

const UserGroupIcon = {
  template: `<svg class="w-full h-full text-white" fill="currentColor" viewBox="0 0 20 20">
    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
  </svg>`
}

const CashIcon = {
  template: `<svg class="w-full h-full text-white" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
  </svg>`
}

const ChartBarIcon = {
  template: `<svg class="w-full h-full text-white" fill="currentColor" viewBox="0 0 20 20">
    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
  </svg>`
}

const CogIcon = {
  template: `<svg class="w-full h-full text-white" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
  </svg>`
}

const OfficeBuildingIcon = {
  template: `<svg class="w-full h-full text-white" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
  </svg>`
}

const DocumentTextIcon = {
  template: `<svg class="w-full h-full text-white" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
  </svg>`
}

// ===== CONFIGURAÇÃO DO SERVIÇO =====
const STORAGE_KEY = "clinica_permissoes"

// Simula delay de rede
const delay = (ms = 500) => new Promise((resolve) => setTimeout(resolve, ms))

// ===== ESTADO REATIVO =====
const tipoUsuarioSelecionado = ref('')
const permissoes = ref({})
const loading = ref(false)
const toast = ref({
  show: false,
  message: '',
  type: 'success'
})

// Modal de cópia
const modalCopia = reactive({
  show: false,
  perfilOrigem: ''
})

// ===== CONFIGURAÇÕES =====

// Tipos de usuário
const tiposUsuario = [
  {
    id: 'administrador',
    nome: 'Administrador',
    descricao: 'Acesso total ao sistema',
    icone: BadgeCheckIcon,
    cor: 'bg-red-500 text-white'
  },
  {
    id: 'recepcao',
    nome: 'Recepção',
    descricao: 'Atendimento e agendamentos',
    icone: UsersIcon,
    cor: 'bg-blue-500 text-white'
  },
  {
    id: 'profissional',
    nome: 'Profissional',
    descricao: 'Médicos e especialistas',
    icone: UserIcon,
    cor: 'bg-green-500 text-white'
  }
]

// Módulos do sistema
const modulos = [
  {
    id: 'agenda',
    nome: 'Agenda',
    descricao: 'Agendamentos e consultas',
    icone: CalendarIcon,
    cor: 'bg-blue-100 text-blue-600'
  },
  {
    id: 'consultas',
    nome: 'Consultas',
    descricao: 'Registro de consultas',
    icone: ClipboardListIcon,
    cor: 'bg-green-100 text-green-600'
  },
  {
    id: 'pacientes',
    nome: 'Pacientes',
    descricao: 'Cadastro de pacientes',
    icone: UserGroupIcon,
    cor: 'bg-purple-100 text-purple-600'
  },
  {
    id: 'financeiro',
    nome: 'Financeiro',
    descricao: 'Controle financeiro',
    icone: CashIcon,
    cor: 'bg-yellow-100 text-yellow-600'
  },
  {
    id: 'relatorios',
    nome: 'Relatórios',
    descricao: 'Relatórios e estatísticas',
    icone: ChartBarIcon,
    cor: 'bg-indigo-100 text-indigo-600'
  },
  {
    id: 'configuracoes',
    nome: 'Configurações',
    descricao: 'Configurações do sistema',
    icone: CogIcon,
    cor: 'bg-gray-100 text-gray-600'
  },
  {
    id: 'usuarios',
    nome: 'Usuários',
    descricao: 'Gerenciamento de usuários',
    icone: UsersIcon,
    cor: 'bg-red-100 text-red-600'
  },
  {
    id: 'parceiros',
    nome: 'Parceiros',
    descricao: 'Gestão de parceiros',
    icone: OfficeBuildingIcon,
    cor: 'bg-teal-100 text-teal-600'
  },
  {
    id: 'templates',
    nome: 'Modelos/Templates',
    descricao: 'Templates de documentos',
    icone: DocumentTextIcon,
    cor: 'bg-orange-100 text-orange-600'
  }
]

// Tipos de permissão
const tiposPermissao = [
  {
    id: 'visualizar',
    nome: 'Visualizar',
    cor: 'text-blue-600 focus:ring-blue-500'
  },
  {
    id: 'criar',
    nome: 'Criar',
    cor: 'text-green-600 focus:ring-green-500'
  },
  {
    id: 'editar',
    nome: 'Editar',
    cor: 'text-yellow-600 focus:ring-yellow-500'
  },
  {
    id: 'excluir',
    nome: 'Excluir',
    cor: 'text-red-600 focus:ring-red-500'
  }
]

// ===== COMPUTED PROPERTIES =====
const contarPermissoes = () => {
  if (!tipoUsuarioSelecionado.value || !permissoes.value[tipoUsuarioSelecionado.value]) {
    return 0
  }

  let total = 0
  const perfilPermissoes = permissoes.value[tipoUsuarioSelecionado.value]

  Object.values(perfilPermissoes).forEach(moduloPermissoes => {
    total += moduloPermissoes.length
  })

  return total
}

// ===== MÉTODOS UTILITÁRIOS =====
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

const getTipoUsuarioNome = (tipoId) => {
  const tipo = tiposUsuario.find(t => t.id === tipoId)
  return tipo ? tipo.nome : tipoId
}

// ===== FUNÇÕES DE PERMISSÃO =====
const temPermissao = (moduloId, permissaoId) => {
  if (!tipoUsuarioSelecionado.value || !permissoes.value[tipoUsuarioSelecionado.value]) {
    return false
  }

  const moduloPermissoes = permissoes.value[tipoUsuarioSelecionado.value][moduloId] || []
  return moduloPermissoes.includes(permissaoId)
}

const togglePermissao = (moduloId, permissaoId, checked) => {
  if (!tipoUsuarioSelecionado.value) return

  if (!permissoes.value[tipoUsuarioSelecionado.value]) {
    permissoes.value[tipoUsuarioSelecionado.value] = {}
  }

  if (!permissoes.value[tipoUsuarioSelecionado.value][moduloId]) {
    permissoes.value[tipoUsuarioSelecionado.value][moduloId] = []
  }

  const moduloPermissoes = permissoes.value[tipoUsuarioSelecionado.value][moduloId]

  if (checked) {
    if (!moduloPermissoes.includes(permissaoId)) {
      moduloPermissoes.push(permissaoId)
    }
  } else {
    const index = moduloPermissoes.indexOf(permissaoId)
    if (index > -1) {
      moduloPermissoes.splice(index, 1)
    }
  }
}

const marcarTodas = (marcar) => {
  if (!tipoUsuarioSelecionado.value) return

  if (!permissoes.value[tipoUsuarioSelecionado.value]) {
    permissoes.value[tipoUsuarioSelecionado.value] = {}
  }

  modulos.forEach(modulo => {
    if (marcar) {
      permissoes.value[tipoUsuarioSelecionado.value][modulo.id] = [...tiposPermissao.map(p => p.id)]
    } else {
      permissoes.value[tipoUsuarioSelecionado.value][modulo.id] = []
    }
  })
}

const marcarTodasPermissoesModulo = (moduloId, marcar) => {
  if (!tipoUsuarioSelecionado.value) return

  if (!permissoes.value[tipoUsuarioSelecionado.value]) {
    permissoes.value[tipoUsuarioSelecionado.value] = {}
  }

  if (marcar) {
    permissoes.value[tipoUsuarioSelecionado.value][moduloId] = [...tiposPermissao.map(p => p.id)]
  } else {
    permissoes.value[tipoUsuarioSelecionado.value][moduloId] = []
  }
}

// ===== HANDLERS DOS EVENTOS =====
const selecionarTipoUsuario = (tipoId) => {
  tipoUsuarioSelecionado.value = tipoId

  // Inicializa permissões se não existir
  if (!permissoes.value[tipoId]) {
    permissoes.value[tipoId] = {}
    modulos.forEach(modulo => {
      permissoes.value[tipoId][modulo.id] = []
    })
  }
}

const salvarPermissoes = async () => {
  if (!tipoUsuarioSelecionado.value) return

  try {
    loading.value = true
    await delay(800) // Simula delay de rede

    // Salva no localStorage
    localStorage.setItem(STORAGE_KEY, JSON.stringify(permissoes.value))

    showToast(`Permissões do perfil ${getTipoUsuarioNome(tipoUsuarioSelecionado.value)} salvas com sucesso!`)
  } catch (error) {
    console.error('Erro ao salvar permissões:', error)
    showToast('Erro ao salvar permissões. Tente novamente.', 'error')
  } finally {
    loading.value = false
  }
}

const resetarPermissoes = async () => {
  if (!confirm('Tem certeza que deseja resetar todas as permissões? Esta ação não pode ser desfeita.')) {
    return
  }

  try {
    loading.value = true
    await delay(500)

    // Reseta todas as permissões
    permissoes.value = {}
    tipoUsuarioSelecionado.value = ''

    // Remove do localStorage
    localStorage.removeItem(STORAGE_KEY)

    // Reinicializa com dados padrão
    inicializarPermissoesPadrao()

    showToast('Todas as permissões foram resetadas para os valores padrão!')
  } catch (error) {
    console.error('Erro ao resetar permissões:', error)
    showToast('Erro ao resetar permissões. Tente novamente.', 'error')
  } finally {
    loading.value = false
  }
}

const copiarPermissoes = () => {
  modalCopia.show = true
  modalCopia.perfilOrigem = ''
}

const fecharModalCopia = () => {
  modalCopia.show = false
  modalCopia.perfilOrigem = ''
}

const executarCopiaPermissoes = () => {
  if (!modalCopia.perfilOrigem || !tipoUsuarioSelecionado.value) return

  // Copia as permissões do perfil origem para o perfil selecionado
  if (permissoes.value[modalCopia.perfilOrigem]) {
    permissoes.value[tipoUsuarioSelecionado.value] = JSON.parse(
      JSON.stringify(permissoes.value[modalCopia.perfilOrigem])
    )

    showToast(`Permissões copiadas de ${getTipoUsuarioNome(modalCopia.perfilOrigem)} para ${getTipoUsuarioNome(tipoUsuarioSelecionado.value)}!`)
  }

  fecharModalCopia()
}

// ===== CARREGAMENTO E INICIALIZAÇÃO =====
const carregarPermissoes = () => {
  try {
    const dados = localStorage.getItem(STORAGE_KEY)
    if (dados) {
      permissoes.value = JSON.parse(dados)
    } else {
      inicializarPermissoesPadrao()
    }
  } catch (error) {
    console.error('Erro ao carregar permissões:', error)
    inicializarPermissoesPadrao()
  }
}

const inicializarPermissoesPadrao = () => {
  // Permissões padrão para cada tipo de usuário
  const permissoesPadrao = {
    administrador: {
      agenda: ['visualizar', 'criar', 'editar', 'excluir'],
      consultas: ['visualizar', 'criar', 'editar', 'excluir'],
      pacientes: ['visualizar', 'criar', 'editar', 'excluir'],
      financeiro: ['visualizar', 'criar', 'editar', 'excluir'],
      relatorios: ['visualizar', 'criar', 'editar', 'excluir'],
      configuracoes: ['visualizar', 'criar', 'editar', 'excluir'],
      usuarios: ['visualizar', 'criar', 'editar', 'excluir'],
      parceiros: ['visualizar', 'criar', 'editar', 'excluir'],
      templates: ['visualizar', 'criar', 'editar', 'excluir']
    },
    recepcao: {
      agenda: ['visualizar', 'criar', 'editar'],
      consultas: ['visualizar', 'criar'],
      pacientes: ['visualizar', 'criar', 'editar'],
      financeiro: ['visualizar'],
      relatorios: ['visualizar'],
      configuracoes: [],
      usuarios: [],
      parceiros: ['visualizar'],
      templates: ['visualizar']
    },
    profissional: {
      agenda: ['visualizar', 'editar'],
      consultas: ['visualizar', 'criar', 'editar'],
      pacientes: ['visualizar', 'editar'],
      financeiro: ['visualizar'],
      relatorios: ['visualizar', 'criar'],
      configuracoes: [],
      usuarios: [],
      parceiros: ['visualizar'],
      templates: ['visualizar', 'criar', 'editar']
    }
  }

  permissoes.value = permissoesPadrao

  // Salva no localStorage
  localStorage.setItem(STORAGE_KEY, JSON.stringify(permissoes.value))
}

// ===== LIFECYCLE HOOKS =====
onMounted(() => {
  carregarPermissoes()
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
  outline: 2px solid #4f46e5;
  outline-offset: 2px;
}

/* Modal backdrop */
.fixed.inset-0 {
  backdrop-filter: blur(4px);
}

/* Checkbox customizado */
.form-checkbox {
  appearance: none;
  background-color: #fff;
  margin: 0;
  font: inherit;
  color: currentColor;
  width: 1.25em;
  height: 1.25em;
  border: 0.15em solid currentColor;
  border-radius: 0.25em;
  transform: translateY(-0.075em);
  display: grid;
  place-content: center;
}

.form-checkbox::before {
  content: "";
  width: 0.65em;
  height: 0.65em;
  clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
  transform: scale(0);
  transform-origin: bottom left;
  transition: 120ms transform ease-in-out;
  box-shadow: inset 1em 1em currentColor;
}

.form-checkbox:checked::before {
  transform: scale(1);
}

.form-checkbox:focus {
  outline: max(2px, 0.15em) solid currentColor;
  outline-offset: max(2px, 0.15em);
}

/* Transições suaves */
.transition-all {
  transition: all 0.2s ease-in-out;
}

/* Estados de hover para cards */
.cursor-pointer:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Estados de hover para tabela */
tbody tr:hover {
  background-color: #f9fafb;
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

/* Scrollbar customization */
.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Estilo para seleção de perfil */
.border-indigo-500 {
  border-color: #6366f1;
  box-shadow: 0 0 0 1px #6366f1;
}

/* Responsividade para tabela */
@media (max-width: 768px) {
  .overflow-x-auto {
    overflow-x: scroll;
  }

  table {
    min-width: 800px;
  }
}
</style>
