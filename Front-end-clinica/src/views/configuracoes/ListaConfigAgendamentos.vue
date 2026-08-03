<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Configurações de Agendamento"
      description="Gerencie as configurações de horários e disponibilidade" :icon="Settings" :show-breadcrumbs="true"
      :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Configurações de Agendamento' }
      ]" icon-bg-color="blue" class="mb-8">
      <template #actions>
        <button @click="abrirModalNovaConfig"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center space-x-2 font-medium">
          <Plus class="w-6 h-6" />
          <span>Nova Configuração</span>
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
            <p class="text-sm text-gray-600">Configurações Ativas</p>
            <p class="text-2xl font-bold text-gray-900">{{ indicadores.ativas }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-3">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <Settings class="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <p class="text-sm text-gray-600">Total de Configurações</p>
            <p class="text-2xl font-bold text-gray-900">{{ indicadores.total }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-3">
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
            <User class="w-6 h-6 text-purple-600" />
          </div>
          <div>
            <p class="text-sm text-gray-600">Personalizadas</p>
            <p class="text-2xl font-bold text-gray-900">{{ indicadores.personalizadas }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-3">
          <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
            <Calendar class="w-6 h-6 text-orange-600" />
          </div>
          <div>
            <p class="text-sm text-gray-600">Última Atualização</p>
            <p class="text-sm font-medium text-gray-900">{{ indicadores.ultimaAtualizacao }}</p>
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
            <input type="text" v-model="filtros.nome" @input="filtrarConfiguracoes"
              placeholder="Buscar por profissional..."
              class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm min-w-[200px]" />
          </div>

          <!-- Filtro por Tipo -->
          <div class="flex items-center space-x-2">
            <Filter class="w-4 h-4 text-gray-400" />
            <select v-model="filtros.tipo" @change="filtrarConfiguracoes"
              class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
              <option value="">Todos os tipos</option>
              <option value="padrao">Configuração Padrão</option>
              <option value="personalizada">Configuração Personalizada</option>
            </select>
          </div>

          <!-- Filtro por Status -->
          <div class="flex items-center space-x-2">
            <ToggleLeft class="w-4 h-4 text-gray-400" />
            <select v-model="filtros.status" @change="filtrarConfiguracoes"
              class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
              <option value="">Todos os status</option>
              <option value="ativa">Ativa</option>
              <option value="inativa">Inativa</option>
            </select>
          </div>

          <!-- Ordenação -->
          <div class="flex items-center space-x-2">
            <ArrowUpDown class="w-4 h-4 text-gray-400" />
            <select v-model="ordenacao" @change="ordenarConfiguracoes"
              class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
              <option value="data_desc">Data (Mais Recente)</option>
              <option value="data_asc">Data (Mais Antigo)</option>
              <option value="nome_asc">Profissional (A-Z)</option>
              <option value="nome_desc">Profissional (Z-A)</option>
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
      <h3 class="text-lg font-medium text-gray-900 mb-2">Carregando configurações...</h3>
      <p class="text-gray-500">Aguarde enquanto buscamos os dados</p>
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

    <!-- Tabela de Configurações -->
    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200">
      <!-- Header da Tabela -->
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Lista de Configurações
        </h3>
        <p class="text-sm text-gray-500 mt-1">
          {{ configuracoesFiltradas.length }} configuração{{ configuracoesFiltradas.length !== 1 ? 'ões' : '' }}
          encontrada{{ configuracoesFiltradas.length !== 1 ? 's' : '' }}
        </p>
      </div>

      <!-- Tabela Desktop -->
      <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profissional
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período de
                Vigência
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horário de
                Funcionamento</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="config in configuracoesFiltradas" :key="config.id" class="hover:bg-gray-50">
              <!-- Tipo -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-3 h-3 rounded-full mr-3" :class="config.padrao ? 'bg-blue-500' : 'bg-green-500'"></div>
                  <span class="text-sm font-medium text-gray-900">
                    {{ config.padrao ? 'Padrão' : 'Personalizada' }}
                  </span>
                </div>
              </td>

              <!-- Profissional -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                    :class="config.padrao ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600'">
                    <component :is="config.padrao ? Settings : User" class="w-5 h-5" />
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">
                      {{ config.padrao ? 'Sistema' : (config.user?.name || 'Usuário não encontrado') }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ config.padrao ? 'Aplicada a todos' : config.user?.email }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Período de Vigência -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ formatarData(config.data_inicio_vigencia) }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ config.data_fim_vigencia ? `até ${formatarData(config.data_fim_vigencia)}` : 'Vigente' }}
                </div>
              </td>

              <!-- Horário de Funcionamento -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ formatarHorario(config.horario_inicio) }} - {{ formatarHorario(config.horario_fim) }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ config.duracao_consulta }}min consulta + {{ config.intervalo_consulta }}min intervalo
                </div>
              </td>

              <!-- Status -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getStatusClass(config)">
                  {{ isConfigAtiva(config) ? 'Ativa' : 'Inativa' }}
                </span>
              </td>

              <!-- Ações -->
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center justify-center space-x-3">
                  <button @click="verDetalhes(config)"
                    class="text-gray-600 hover:text-gray-900 p-2 rounded hover:bg-gray-50 flex items-center justify-center w-8 h-8"
                    title="Ver Detalhes">
                    <Eye class="w-4 h-4" />
                  </button>
                  <button @click="editarConfig(config)"
                    :disabled="!podeEditarConfig(config)"
                    class="text-blue-600 hover:text-blue-900 disabled:text-gray-400 disabled:cursor-not-allowed p-2 rounded hover:bg-blue-50 flex items-center justify-center w-8 h-8"
                    :title="podeEditarConfig(config) ? 'Editar' : 'Configurações encerradas não podem ser editadas'">
                    <Edit class="w-4 h-4" />
                  </button>
                  <button @click="excluirConfig(config)" :disabled="!podeExcluirConfig(config)"
                    class="text-red-600 hover:text-red-900 disabled:text-gray-400 disabled:cursor-not-allowed p-2 rounded hover:bg-red-50 flex items-center justify-center w-8 h-8"
                    :title="podeExcluirConfig(config) ? 'Excluir' : 'A configuração padrão vigente não pode ser excluída'">
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
        <div v-for="config in configuracoesFiltradas" :key="`mobile-${config.id}`"
          class="border border-gray-200 rounded-lg p-4">
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                :class="config.padrao ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600'">
                <component :is="config.padrao ? Settings : User" class="w-5 h-5" />
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-900">
                  {{ config.padrao ? 'Configuração Padrão' : (config.user?.name || 'Usuário não encontrado') }}
                </h4>
                <p class="text-xs text-gray-500">
                  {{ config.padrao ? 'Sistema' : config.user?.email }}
                </p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                :class="getStatusClass(config)">
                {{ isConfigAtiva(config) ? 'Ativa' : 'Inativa' }}
              </span>
            </div>
          </div>

          <div class="space-y-2 text-sm text-gray-600 mb-3">
            <div class="flex items-center space-x-2">
              <Calendar class="w-4 h-4" />
              <span>{{ formatarData(config.data_inicio_vigencia) }} - {{ config.data_fim_vigencia ?
                formatarData(config.data_fim_vigencia) : 'Vigente' }}</span>
            </div>
            <div class="flex items-center space-x-2">
              <Clock class="w-4 h-4" />
              <span>{{ formatarHorario(config.horario_inicio) }} - {{ formatarHorario(config.horario_fim) }}</span>
            </div>
          </div>

          <div class="flex items-center justify-end space-x-3">
            <button @click="verDetalhes(config)"
              class="text-gray-600 hover:text-gray-900 p-2 rounded hover:bg-gray-50 flex items-center justify-center w-10 h-10"
              title="Ver Detalhes">
              <Eye class="w-4 h-4" />
            </button>
            <button @click="editarConfig(config)"
              :disabled="!podeEditarConfig(config)"
              class="text-blue-600 hover:text-blue-900 disabled:text-gray-400 disabled:cursor-not-allowed p-2 rounded hover:bg-blue-50 flex items-center justify-center w-10 h-10"
              :title="podeEditarConfig(config) ? 'Editar' : 'Configurações encerradas não podem ser editadas'">
              <Edit class="w-4 h-4" />
            </button>
            <button @click="excluirConfig(config)" :disabled="!podeExcluirConfig(config)"
              class="text-red-600 hover:text-red-900 disabled:text-gray-400 disabled:cursor-not-allowed p-2 rounded hover:bg-red-50 flex items-center justify-center w-10 h-10"
              :title="podeExcluirConfig(config) ? 'Excluir' : 'A configuração padrão vigente não pode ser excluída'">
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Estado Vazio -->
      <div v-if="configuracoesFiltradas.length === 0 && !carregando" class="p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <Settings class="w-8 h-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma configuração encontrada</h3>
        <p class="text-gray-500 mb-4">
          {{ temFiltrosAtivos()
            ? 'Não há configurações que correspondam aos filtros selecionados.'
            : 'Comece criando sua primeira configuração de agendamento.'
          }}
        </p>
        <button @click="temFiltrosAtivos() ? limparFiltros() : abrirModalNovaConfig()"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
          {{ temFiltrosAtivos() ? 'Limpar Filtros' : 'Criar Configuração' }}
        </button>
      </div>
    </div>

    <!-- Modal de Detalhes -->
    <ActionModal :open="modalDetalhesAberto" titulo="Detalhes da Configuração" subtitulo="" action-label="Editar"
      modal-width="sm:max-w-3xl" @acao="editarConfig(configuracaoSelecionada)" @cancel="fecharModalDetalhes">
      <div v-if="configuracaoSelecionada" class="space-y-3">
        <!-- Informações Básicas -->
        <div class="flex items-start space-x-4">
          <div class="w-16 h-16 rounded-lg flex items-center justify-center"
            :class="configuracaoSelecionada.padrao ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600'">
            <component :is="configuracaoSelecionada.padrao ? Settings : User" class="w-8 h-8" />
          </div>
          <div class="flex-1">
            <h3 class="text-xl font-semibold text-gray-900">
              {{ configuracaoSelecionada.padrao ? 'Configuração Padrão' : 'Configuração Personalizada' }}
            </h3>
            <div class="flex items-center space-x-4 mt-2">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                :class="configuracaoSelecionada.padrao ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'">
                {{ configuracaoSelecionada.padrao ? 'Padrão' : 'Personalizada' }}
              </span>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                :class="getStatusClass(configuracaoSelecionada)">
                {{ isConfigAtiva(configuracaoSelecionada) ? 'Ativa' : 'Inativa' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Informações do Profissional -->
        <div class="border-t border-gray-200 pt-4">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Informações do Profissional</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="flex items-center space-x-3">
              <User class="w-4 h-4 text-gray-400" />
              <span>{{ configuracaoSelecionada.padrao ? 'Sistema' : (configuracaoSelecionada.user?.name || 'Usuário não encontrado') }}</span>
            </div>
            <div class="flex items-center space-x-3">
              <Mail class="w-4 h-4 text-gray-400" />
              <span>{{ configuracaoSelecionada.padrao ? 'Aplicada a todos' : configuracaoSelecionada.user?.email
                }}</span>
            </div>
          </div>
        </div>

        <!-- Período de Vigência -->
        <div class="border-t border-gray-200 pt-3">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Período de Vigência</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="flex items-center space-x-3">
              <Calendar class="w-4 h-4 text-gray-400" />
              <span>Início: {{ formatarData(configuracaoSelecionada.data_inicio_vigencia) }}</span>
            </div>
            <div class="flex items-center space-x-3">
              <Calendar class="w-4 h-4 text-gray-400" />
              <span>Fim: {{ configuracaoSelecionada.data_fim_vigencia ?
                formatarData(configuracaoSelecionada.data_fim_vigencia) : 'Vigente' }}</span>
            </div>
          </div>
        </div>

        <!-- Horários -->
        <div class="border-t border-gray-200 pt-3">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Horários de Funcionamento</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="flex items-center space-x-3">
              <Clock class="w-4 h-4 text-gray-400" />
              <span>{{ formatarHorario(configuracaoSelecionada.horario_inicio) }} - {{
                formatarHorario(configuracaoSelecionada.horario_fim) }}</span>
            </div>
            <div class="flex items-center space-x-3">
              <Clock class="w-4 h-4 text-gray-400" />
              <span>{{ configuracaoSelecionada.duracao_consulta }}min consulta + {{
                configuracaoSelecionada.intervalo_consulta }}min intervalo</span>
            </div>
          </div>
        </div>

        <!-- Dias da Semana -->
        <div class="border-t border-gray-200 pt-3">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Dias de Funcionamento</h4>
          <div class="grid grid-cols-7 gap-2">
            <div v-for="(dia, index) in diasSemana" :key="index" class="text-center p-2 rounded-lg"
              :class="getDiaAtivoClass(configuracaoSelecionada, index)">
              <div class="text-xs font-medium">{{ dia.abrev }}</div>
              <div class="text-xs text-gray-500">{{ dia.nome }}</div>
            </div>
          </div>
        </div>

        <!-- Pausas -->
        <div v-if="configuracaoSelecionada.pausas && configuracaoSelecionada.pausas.length > 0"
          class="border-t border-gray-200 pt-3">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Pausas Configuradas</h4>
          <div class="space-y-2">
            <div v-for="(pausa, index) in configuracaoSelecionada.pausas" :key="index"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div>
                <span class="text-sm font-medium text-gray-900">{{ pausa.nome }}</span>
                <span class="text-sm text-gray-500 ml-2">{{ pausa.inicio }} - {{ pausa.fim }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Informações de Sistema -->
        <div class="border-t border-gray-200 pt-3">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Informações do Sistema</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
            <div class="flex items-center space-x-3">
              <Calendar class="w-4 h-4 text-gray-400" />
              <span>Criada em: {{ formatarData(configuracaoSelecionada.created_at) }}</span>
            </div>
            <div class="flex items-center space-x-3">
              <Clock class="w-4 h-4 text-gray-400" />
              <span>Última atualização: {{ formatarData(configuracaoSelecionada.updated_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </ActionModal>

    <!-- Modal de Confirmação de Exclusão -->
    <ActionModal :open="modalExclusaoAberto" titulo="Confirmar Exclusão" subtitulo=""
      :action-label="carregando ? 'Excluindo...' : 'Excluir'" :action-disabled="carregando" modal-width="sm:max-w-md"
      @acao="confirmarExclusao" @cancel="cancelarExclusao">
      <div v-if="configuracaoParaExcluir" class="space-y-4">
        <div class="flex items-start space-x-3">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
              <Trash2 class="w-5 h-5 text-red-600" />
            </div>
          </div>
          <div class="flex-1">
            <p class="text-sm text-gray-700">
              Tem certeza que deseja excluir esta configuração?
            </p>
            <p class="text-sm text-red-600 mt-2">
              Esta ação não pode ser desfeita e todos os dados da configuração serão permanentemente removidos.
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
  Settings, Plus, CheckCircle, User, Calendar, Search, Filter,
  ToggleLeft, ArrowUpDown, Loader2, AlertCircle, Eye,
  Edit, Trash2, Mail, Clock
} from 'lucide-vue-next'

// ===== ROUTER =====
const router = useRouter()

// ===== ESTADO REATIVO =====
const carregando = ref(false)
const erro = ref('')
const mensagemSucesso = ref('')
const mensagemErro = ref('')

// Dados das configurações
const configuracoes = ref([])
const configuracoesFiltradas = ref([])

// Modais
const modalDetalhesAberto = ref(false)
const configuracaoSelecionada = ref(null)
const modalExclusaoAberto = ref(false)
const configuracaoParaExcluir = ref(null)

// Filtros e ordenação
const filtros = ref({
  nome: '',
  tipo: '',
  status: ''
})
const ordenacao = ref('data_desc')

// Indicadores
const indicadores = ref({
  total: 0,
  ativas: 0,
  personalizadas: 0,
  ultimaAtualizacao: 'Nunca'
})

// Dias da semana
const diasSemana = [
  { nome: 'Domingo', abrev: 'Dom' },
  { nome: 'Segunda', abrev: 'Seg' },
  { nome: 'Terça', abrev: 'Ter' },
  { nome: 'Quarta', abrev: 'Qua' },
  { nome: 'Quinta', abrev: 'Qui' },
  { nome: 'Sexta', abrev: 'Sex' },
  { nome: 'Sábado', abrev: 'Sáb' }
]

// ===== COMPUTED PROPERTIES =====

/**
 * Verifica se há filtros ativos
 */
const temFiltrosAtivos = () => {
  return filtros.value.nome || filtros.value.tipo || filtros.value.status
}

// ===== FUNÇÕES UTILITÁRIAS =====

/**
 * Formata data para exibição (DD/MM/AAAA)
 */
const formatarData = (data) => {
  if (!data) return 'N/A'

  try {
    // Se for uma string de data ISO (2024-01-01T00:00:00.000000Z)
    if (typeof data === 'string' && data.includes('T')) {
      const dataObj = new Date(data)
      const resultado = dataObj.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      })
      console.log(`DEBUG formatarData: ${data} → ${resultado}`)
      return resultado
    }

    // Se for uma string de data simples (2024-01-01)
    if (typeof data === 'string' && data.match(/^\d{4}-\d{2}-\d{2}$/)) {
      const [ano, mes, dia] = data.split('-')
      return `${dia}/${mes}/${ano}`
    }

    // Se já for um objeto Date
    if (data instanceof Date) {
      return data.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      })
    }

    // Fallback para outros formatos
    const dataObj = new Date(data)
    return dataObj.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    })
  } catch (error) {
    console.error('Erro ao formatar data:', error, data)
    return 'Data inválida'
  }
}

/**
 * Formata horário para exibição (apenas hora:minuto)
 */
const formatarHorario = (horario) => {
  if (!horario) return 'N/A'
  // Se for uma string de data completa, extrai apenas o horário
  if (typeof horario === 'string' && horario.includes('T')) {
    return horario.split('T')[1].substring(0, 5) // Pega apenas HH:MM
  }
  // Se já for apenas horário (HH:MM), retorna como está
  return horario
}

/**
 * Verifica se a configuração está ativa baseado na data_fim_vigencia
 */
const isConfigAtiva = (config) => {
  if (!config.data_fim_vigencia) {
    return true // Se data_fim_vigencia é null, está ativa
  }

  const hoje = new Date()
  hoje.setHours(0, 0, 0, 0)
  const dataFim = new Date(config.data_fim_vigencia)

  return dataFim >= hoje
}

/** Só a vigente (sem data fim) pode ser editada — gera nova versão no backend */
const podeEditarConfig = (config) => {
  return !!config && !config.data_fim_vigencia
}

/** Padrão vigente não apaga; inativas (histórico) e personalizadas podem */
const podeExcluirConfig = (config) => {
  if (!config) return false
  if (config.padrao && !config.data_fim_vigencia) return false
  return true
}

/**
 * Retorna classe CSS baseada no status
 */
const getStatusClass = (config) => {
  return isConfigAtiva(config)
    ? 'bg-green-100 text-green-800'
    : 'bg-red-100 text-red-800'
}

/**
 * Retorna classe para dia da semana
 */
const getDiaAtivoClass = (config, index) => {
  const diasMap = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sab']
  const diaAtivo = config[diasMap[index]]
  return diaAtivo
    ? 'bg-green-100 text-green-800 border border-green-200'
    : 'bg-gray-100 text-gray-500 border border-gray-200'
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
 * Filtra configurações baseado nos filtros ativos
 */
const filtrarConfiguracoes = () => {
  let resultado = [...configuracoes.value]

  // Filtro por nome (profissional)
  if (filtros.value.nome) {
    resultado = resultado.filter(config => {
      const nomeProfissional = config.padrao ? 'Sistema' : (config.user?.name || '')
      return nomeProfissional.toLowerCase().includes(filtros.value.nome.toLowerCase())
    })
  }

  // Filtro por tipo
  if (filtros.value.tipo) {
    if (filtros.value.tipo === 'padrao') {
      resultado = resultado.filter(config => config.padrao)
    } else if (filtros.value.tipo === 'personalizada') {
      resultado = resultado.filter(config => !config.padrao)
    }
  }

  // Filtro por status
  if (filtros.value.status) {
    const statusAtivo = filtros.value.status === 'ativa'
    resultado = resultado.filter(config => isConfigAtiva(config) === statusAtivo)
  }

  configuracoesFiltradas.value = resultado
  ordenarConfiguracoes()
}

/**
 * Ordena configurações baseado na ordenação selecionada
 */
const ordenarConfiguracoes = () => {
  const [campo, direcao] = ordenacao.value.split('_')

  configuracoesFiltradas.value.sort((a, b) => {
    let valorA, valorB

    if (campo === 'nome') {
      valorA = (a.padrao ? 'Sistema' : (a.user?.name || '')).toLowerCase()
      valorB = (b.padrao ? 'Sistema' : (b.user?.name || '')).toLowerCase()
    } else if (campo === 'data') {
      valorA = new Date(a.data_inicio_vigencia)
      valorB = new Date(b.data_inicio_vigencia)
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
    status: ''
  }
  ordenacao.value = 'data_desc'
  filtrarConfiguracoes()
}

// ===== FUNÇÕES DE MODAL =====

/**
  * Navega para página de nova configuração
 */
const abrirModalNovaConfig = () => {
  router.push('/configuracoes/agendamentos/novo')
}

/**
 * Navega para página de edição da configuração
 */
const editarConfig = (config) => {
  if (!podeEditarConfig(config)) {
    mensagemErro.value = 'Só é possível editar a configuração vigente.'
    limparMensagens()
    return
  }
  router.push(`/configuracoes/agendamentos/${config.id}`)
}

/**
 * Ver detalhes da configuração
 */
const verDetalhes = (config) => {
  configuracaoSelecionada.value = config
  modalDetalhesAberto.value = true
}

/**
 * Fecha modal de detalhes
 */
const fecharModalDetalhes = () => {
  modalDetalhesAberto.value = false
  configuracaoSelecionada.value = null
}

/**
 * Abre modal de confirmação para excluir configuração
 */
const excluirConfig = (config) => {
  if (!podeExcluirConfig(config)) {
    mensagemErro.value = 'A configuração padrão vigente não pode ser excluída. Crie outra para substituí-la.'
    limparMensagens()
    return
  }
  configuracaoParaExcluir.value = config
  modalExclusaoAberto.value = true
}

/**
 * Confirma exclusão da configuração
 */
const confirmarExclusao = async () => {
  if (!configuracaoParaExcluir.value) return

  try {
    carregando.value = true
    // Chama a API para excluir a configuração
    const response = await axios.delete(`/configuracoes-agendamento/${configuracaoParaExcluir.value.id}`)

    if (response.data?.tipo_acao === 'desativada') {
      const idx = configuracoes.value.findIndex(c => c.id === configuracaoParaExcluir.value.id)
      if (idx > -1) {
        configuracoes.value[idx].data_fim_vigencia = response.data.data_fim_vigencia
      }
    } else {
      const index = configuracoes.value.findIndex(c => c.id === configuracaoParaExcluir.value.id)
      if (index > -1) {
        configuracoes.value.splice(index, 1)
      }
    }

    // Atualiza indicadores
    atualizarIndicadores()

    // Refiltra lista
    filtrarConfiguracoes()

    mensagemSucesso.value = response.data?.mensagem || 'Configuração excluída com sucesso!'
    limparMensagens()

    // Fecha modal
    modalExclusaoAberto.value = false
    configuracaoParaExcluir.value = null

  } catch (error) {
    console.error('Erro ao excluir configuração:', error)

    if (error.response) {
      // Erro da API do backend
      if (error.response.status === 404) {
        mensagemErro.value = 'Configuração não encontrada'
      } else if (error.response.status === 400) {
        mensagemErro.value = error.response.data.message || 'Configuração já foi excluída'
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
 * Cancela exclusão da configuração
 */
const cancelarExclusao = () => {
  modalExclusaoAberto.value = false
  configuracaoParaExcluir.value = null
}

// ===== FUNÇÕES DA API =====

/**
 * Atualiza indicadores
 */
const atualizarIndicadores = () => {
  indicadores.value = {
    total: configuracoes.value.length,
    ativas: configuracoes.value.filter(c => isConfigAtiva(c)).length,
    personalizadas: configuracoes.value.filter(c => !c.padrao).length,
    ultimaAtualizacao: configuracoes.value.length > 0
      ? formatarData(configuracoes.value.reduce((maisRecente, config) => {
        const dataConfig = new Date(config.updated_at)
        const dataMaisRecente = new Date(maisRecente.updated_at)
        return dataConfig > dataMaisRecente ? config : maisRecente
      }).updated_at)
      : 'Nunca'
  }
}

/**
 * Carrega configurações da API
 */
const carregarConfiguracoes = async () => {
  carregando.value = true
  erro.value = ''

  try {
    const response = await axios.get('/configuracoes-agendamento')

    // A API retorna dados paginados do Laravel: { data: [...], current_page: 1, ... }
    const dadosAPI = response.data.data || []

    // Mapeia os dados da API para o formato esperado pelo frontend
    configuracoes.value = dadosAPI.map(config => ({
      id: config.id,
      user_id: config.user_id,
      user: config.user,
      seg: config.seg,
      ter: config.ter,
      qua: config.qua,
      qui: config.qui,
      sex: config.sex,
      sab: config.sab,
      dom: config.dom,
      horario_inicio: config.horario_inicio,
      horario_fim: config.horario_fim,
      duracao_consulta: config.duracao_consulta,
      intervalo_consulta: config.intervalo_consulta,
      pausas: config.pausas || [],
      data_inicio_vigencia: config.data_inicio_vigencia,
      data_fim_vigencia: config.data_fim_vigencia,
      padrao: config.padrao,
      consultas_count: config.consultas_count || 0,
      created_at: config.created_at,
      updated_at: config.updated_at
    }))

    // Atualiza indicadores
    atualizarIndicadores()

    filtrarConfiguracoes()

  } catch (error) {
    console.error('Erro ao carregar configurações:', error)

    if (error.response) {
      // Erro da API do backend
      if (error.response.status === 404) {
        erro.value = 'Serviço de configurações não encontrado. Verifique se o backend está rodando.'
      } else if (error.response.status === 500) {
        erro.value = 'Erro interno do servidor. Tente novamente mais tarde.'
      } else {
        erro.value = error.response.data?.message || `Erro do servidor: ${error.response.status}`
      }
    } else if (error.request) {
      // Erro de conexão
      erro.value = 'Erro de conexão. Verifique se a API está no ar (VITE_API_URL / backend).'
    } else {
      erro.value = 'Erro inesperado. Tente novamente.'
    }

    configuracoes.value = []
    atualizarIndicadores()
    filtrarConfiguracoes()

  } finally {
    carregando.value = false
  }
}

// ===== INICIALIZAÇÃO =====

/**
 * Carrega configurações quando o componente é montado
 */
onMounted(() => {
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