<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Detalhes do Paciente" description="Visualize informações do paciente e histórico de consultas"
      :icon="UserIcon" icon-bg-color="blue" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Pacientes', to: '/pacientes/gerenciar' },
        { label: 'Detalhes do Paciente' }
      ]" class="mb-6">
          <template #actions>
        <div class="flex gap-2">
          <BaseButton v-if="paciente && !isLoading" type="button" variant="ghost" size="sm" @click="recarregarDados"
            title="Recarregar dados">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-refresh-cw">
              <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8" />
              <path d="M21 3v5h-5" />
              <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16" />
              <path d="M8 16H3v5" />
            </svg>
          </BaseButton>
          <BaseButton v-if="paciente" type="button" variant="outline" @click="editarPaciente">
            Editar Paciente
          </BaseButton>
          <BaseButton v-if="paciente" type="button" variant="outline" @click="abrirModalConsultaHistorica">
            Consulta antiga
          </BaseButton>
          <BaseButton v-if="paciente" type="button" variant="primary" @click="novaFichaClinica">
            Nova Ficha Clínica
          </BaseButton>
        </div>
      </template>
    </PageHeader>

    <!-- Loading State -->
    <BaseCard v-if="isLoading" padding="lg" class="text-center">
      <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Carregando dados...</h3>
      <p class="text-gray-500">Aguarde enquanto buscamos as informações</p>
    </BaseCard>

    <!-- Error State -->
    <BaseCard v-else-if="error" padding="lg" class="border-red-200 bg-red-50">
      <div class="flex items-center space-x-3 mb-4">
        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <h3 class="text-red-800 font-medium">Erro ao carregar dados</h3>
          <p class="text-red-600 text-sm mt-1">{{ error }}</p>
        </div>
      </div>
      <BaseButton type="button" variant="danger" size="sm" @click="router.push('/pacientes/gerenciar')">
        Voltar para Lista
      </BaseButton>
    </BaseCard>

    <!-- Conteúdo Principal -->
    <div v-else-if="paciente" class="space-y-6">
      <!-- Dados Básicos do Paciente -->
      <BaseCard padding="lg">
        <div class="flex items-start justify-between mb-6">
          <div class="flex items-center space-x-4">
            <div
              class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-2xl font-bold text-white">
              {{ getInitials(paciente.nome) }}
            </div>
            <div>
              <h2 class="text-2xl font-bold text-gray-900">{{ paciente.nome }}</h2>
              <p class="text-gray-500 mt-1">{{ calcularIdade(paciente.data_nascimento) }} anos</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">CPF</label>
            <p class="text-gray-900">{{ formatCPF(paciente.cpf) || 'Não informado' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">RG</label>
            <p class="text-gray-900">{{ paciente.rg || 'Não informado' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Data de Nascimento</label>
            <p class="text-gray-900">{{ formatDate(paciente.data_nascimento) || 'Não informado' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Sexo</label>
            <p class="text-gray-900">{{ formatSexo(paciente.sexo) || 'Não informado' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Telefone</label>
            <p class="text-gray-900">{{ paciente.contato || 'Não informado' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
            <p class="text-gray-900">{{ paciente.email || 'Não informado' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Ocupação</label>
            <p class="text-gray-900">{{ paciente.ocupacao || 'Não informado' }}</p>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-500 mb-1">Endereço</label>
            <p class="text-gray-900">{{ paciente.endereco || 'Não informado' }}</p>
          </div>

          <div v-if="paciente.nome_responsavel" class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-500 mb-1">Responsável</label>
            <p class="text-gray-900">{{ paciente.nome_responsavel }}</p>
            <p v-if="paciente.cpf_responsavel" class="text-sm text-gray-500 mt-1">
              CPF: {{ formatCPF(paciente.cpf_responsavel) }}
            </p>
          </div>

          <div v-if="paciente.observacoes" class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-500 mb-1">Observações</label>
            <p class="text-gray-900 whitespace-pre-wrap">{{ paciente.observacoes }}</p>
          </div>
        </div>
      </BaseCard>

      <!-- Tabela de Consultas -->
      <BaseCard padding="lg">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-semibold text-gray-900 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-calendar mr-2 text-blue-600">
              <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
              <line x1="16" x2="16" y1="2" y2="6" />
              <line x1="8" x2="8" y1="2" y2="6" />
              <line x1="3" x2="21" y1="10" y2="10" />
            </svg>
            Histórico de Consultas
          </h3>
          <BaseButton type="button" variant="outline" size="sm" @click="abrirModalConsultaHistorica">
            + Consulta antiga
          </BaseButton>
        </div>

        <!-- Filtros -->
        <div class="mb-4 flex flex-col sm:flex-row gap-4">
          <div class="flex-1 sm:w-48">
            <BaseSelect 
              v-model="filtroStatusConsulta" 
              label="Filtrar por Status" 
              :options="opcoesStatusConsulta"
              @update:model-value="aplicarFiltrosConsultas"
            />
          </div>
          <div class="flex-1 sm:w-48">
            <InputData 
              v-model="filtroDataConsulta" 
              label="Filtrar por Data" 
              @update:model-value="aplicarFiltrosConsultas"
            />
          </div>
          <div v-if="temFiltrosConsultasAtivos" class="flex items-end">
            <BaseButton type="button" variant="ghost" size="sm" @click="limparFiltrosConsultas">
              Limpar Filtros
            </BaseButton>
          </div>
        </div>

        <!-- Loading Consultas -->
        <div v-if="isLoadingConsultas" class="text-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p class="text-gray-500">Carregando consultas...</p>
        </div>

        <!-- Tabela de Consultas Desktop -->
        <div v-else-if="consultasFiltradas.length > 0" class="overflow-x-auto hidden md:block">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Data/Hora
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Médico
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Procedimento
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Prioridade
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="consulta in consultasFiltradas" :key="consulta.id" class="hover:bg-gray-50">
                <!-- Data/Hora -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatDate(consulta.data) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ consulta.horario_inicio }} - {{ consulta.horario_fim }}
                  </div>
                </td>

                <!-- Médico -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ consulta.medico_nome }}
                  </div>
                </td>

                <!-- Procedimento -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ consulta.procedimento }}
                  </div>
                </td>

                <!-- Status -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <span :class="getStatusBadgeClass(consulta.status_id)" class="px-2 py-1 text-xs font-semibold rounded-full">
                      {{ formatStatus(consulta.status_nome) }}
                    </span>
                    <!-- Indicador visual de ficha vinculada -->
                    <span 
                      v-if="consulta.tem_ficha_clinica" 
                      class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800"
                      title="Ficha clínica vinculada"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="mr-1">
                        <path d="M9 11l3 3L22 4" />
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                      </svg>
                      Ficha
                    </span>
                    <span 
                      v-else 
                      class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-500"
                      title="Sem ficha clínica vinculada"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="mr-1">
                        <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                      </svg>
                      Sem Ficha
                    </span>
                  </div>
                </td>

                <!-- Prioridade -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getPrioridadeBadgeClass(consulta.prioridade)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ formatPrioridade(consulta.prioridade) }}
                  </span>
                </td>

                <!-- Ações -->
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end gap-2 flex-wrap">
                    <!-- Se status = "Em Atendimento" (situacao_id = 6) -->
                    <template v-if="consulta.status_id === 6">
                      <BaseButton 
                        type="button" 
                        variant="primary" 
                        size="sm" 
                        @click="criarFichaClinica(consulta.id)"
                        title="Criar ficha clínica e encerrar consulta automaticamente"
                      >
                        Criar Ficha Clínica
                      </BaseButton>
                    </template>
                    <!-- Se status = "Encerrada" ou outros -->
                    <template v-else>
                      <BaseButton 
                        type="button" 
                        variant="primary" 
                        size="sm" 
                        @click="verDetalhesConsulta(consulta.id)"
                      >
                        Ver Detalhes
                      </BaseButton>
                      <BaseButton 
                        v-if="consulta.tem_ficha_clinica" 
                        type="button" 
                        variant="outline" 
                        size="sm" 
                        @click="verFichaClinicaConsulta(consulta.ficha_clinica_id)"
                        title="Visualizar ficha clínica vinculada"
                      >
                        Ver Ficha Clínica
                      </BaseButton>
                      <BaseButton 
                        v-if="consulta.status_id !== 4 && consulta.status_id !== 5" 
                        type="button" 
                        variant="outline" 
                        size="sm" 
                        @click="cancelarConsulta(consulta.id)"
                        title="Cancelar consulta"
                      >
                        Cancelar
                      </BaseButton>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Cards Mobile -->
        <div v-else-if="consultasFiltradas.length > 0" class="md:hidden space-y-4">
          <div v-for="consulta in consultasFiltradas" :key="`mobile-consulta-${consulta.id}`"
            class="border border-gray-200 rounded-lg p-4">
            <div class="flex items-start justify-between mb-3">
              <div>
                <h4 class="text-sm font-medium text-gray-900">{{ formatDate(consulta.data) }}</h4>
                <p class="text-xs text-gray-500">{{ consulta.horario_inicio }} - {{ consulta.horario_fim }}</p>
              </div>
              <div class="flex flex-col items-end gap-1">
                <span :class="getStatusBadgeClass(consulta.status_id)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ formatStatus(consulta.status_nome) }}
                </span>
                <!-- Indicador visual de ficha vinculada -->
                <span 
                  v-if="consulta.tem_ficha_clinica" 
                  class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800"
                  title="Ficha clínica vinculada"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="mr-1">
                    <path d="M9 11l3 3L22 4" />
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                  </svg>
                  Ficha
                </span>
                <span 
                  v-else 
                  class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-500"
                  title="Sem ficha clínica vinculada"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="mr-1">
                    <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                  </svg>
                  Sem Ficha
                </span>
              </div>
            </div>

            <div class="space-y-2 text-sm text-gray-600 mb-3">
              <div><strong>Médico:</strong> {{ consulta.medico_nome }}</div>
              <div><strong>Procedimento:</strong> {{ consulta.procedimento }}</div>
              <div><strong>Prioridade:</strong> 
                <span :class="getPrioridadeBadgeClass(consulta.prioridade)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ formatPrioridade(consulta.prioridade) }}
                </span>
              </div>
            </div>

            <div class="flex flex-col gap-2">
              <!-- Se status = "Em Atendimento" (situacao_id = 6) -->
              <template v-if="consulta.status_id === 6">
                <BaseButton 
                  type="button" 
                  variant="primary" 
                  size="sm" 
                  class="w-full" 
                  @click="criarFichaClinica(consulta.id)"
                  title="Criar ficha clínica e encerrar consulta automaticamente"
                >
                  Criar Ficha Clínica
                </BaseButton>
              </template>
              <!-- Se status = "Encerrada" ou outros -->
              <template v-else>
                <BaseButton 
                  type="button" 
                  variant="primary" 
                  size="sm" 
                  class="w-full" 
                  @click="verDetalhesConsulta(consulta.id)"
                >
                  Ver Detalhes
                </BaseButton>
                <div class="flex gap-2">
                  <BaseButton 
                    v-if="consulta.tem_ficha_clinica" 
                    type="button" 
                    variant="outline" 
                    size="sm" 
                    class="flex-1"
                    @click="verFichaClinicaConsulta(consulta.ficha_clinica_id)"
                    title="Visualizar ficha clínica vinculada"
                  >
                    Ver Ficha Clínica
                  </BaseButton>
                  <BaseButton 
                    v-if="consulta.status_id !== 4 && consulta.status_id !== 5" 
                    type="button" 
                    variant="outline" 
                    size="sm" 
                    class="flex-1"
                    @click="cancelarConsulta(consulta.id)"
                    title="Cancelar consulta"
                  >
                    Cancelar
                  </BaseButton>
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- Estado Vazio -->
        <div v-else class="text-center py-12">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-calendar-x text-gray-400">
              <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
              <line x1="16" x2="16" y1="2" y2="6" />
              <line x1="8" x2="8" y1="2" y2="6" />
              <line x1="3" x2="21" y1="10" y2="10" />
              <path d="M9 16l2 2 4-4" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma consulta encontrada</h3>
          <p class="text-gray-500">
            {{ temFiltrosConsultasAtivos 
              ? 'Não há consultas que correspondam aos filtros selecionados.' 
              : 'Este paciente ainda não possui consultas cadastradas.' 
            }}
          </p>
        </div>
      </BaseCard>

      <!-- Lista de Fichas Clínicas -->
      <BaseCard padding="lg">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-semibold text-gray-900 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-clipboard-list mr-2 text-[#D4AF37]">
              <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
              <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
              <path d="M12 11h4" />
              <path d="M12 16h4" />
              <path d="M8 11h.01" />
              <path d="M8 16h.01" />
            </svg>
            Fichas Clínicas
          </h3>
          <BaseButton type="button" variant="primary" size="sm" @click="novaFichaClinica">
            Nova Ficha Clínica
          </BaseButton>
        </div>

        <!-- Loading Fichas -->
        <div v-if="isLoadingFichas" class="text-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p class="text-gray-500">Carregando fichas clínicas...</p>
        </div>

        <!-- Tabela de Fichas Clínicas -->
        <div v-else-if="fichasClinicas.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <button @click="ordenarPor('data')" class="flex items-center space-x-1 hover:text-gray-700 transition-colors">
                    <span>Data da Consulta</span>
                    <div class="flex flex-col">
                      <ArrowUp :class="['w-3 h-3', sortField === 'data' && sortDirection === 'asc' ? 'text-blue-600' : 'text-gray-300']" />
                      <ArrowDown :class="['w-3 h-3 -mt-1', sortField === 'data' && sortDirection === 'desc' ? 'text-blue-600' : 'text-gray-300']" />
                    </div>
                  </button>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <button @click="ordenarPor('profissional')" class="flex items-center space-x-1 hover:text-gray-700 transition-colors">
                    <span>Profissional</span>
                    <div class="flex flex-col">
                      <ArrowUp :class="['w-3 h-3', sortField === 'profissional' && sortDirection === 'asc' ? 'text-blue-600' : 'text-gray-300']" />
                      <ArrowDown :class="['w-3 h-3 -mt-1', sortField === 'profissional' && sortDirection === 'desc' ? 'text-blue-600' : 'text-gray-300']" />
                    </div>
                  </button>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Observações
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="ficha in fichasClinicasOrdenadas" :key="ficha.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatDate(ficha.data_consulta) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ formatDateTime(ficha.created_at) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ ficha.user?.name || 'Não informado' }}
                  </div>
                  <div v-if="ficha.user?.email" class="text-xs text-gray-500">
                    {{ ficha.user.email }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 max-w-md truncate">
                    {{ ficha.observacoes || 'Sem observações' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end gap-2">
                    <BaseButton type="button" variant="outline" size="sm" @click="verFichaClinica(ficha.id)">
                      Ver Detalhes
                    </BaseButton>
                    <BaseButton type="button" variant="primary" size="sm" @click="editarFichaClinica(ficha.id)">
                      Editar
                    </BaseButton>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Estado Vazio -->
        <div v-else class="text-center py-12">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-clipboard-x text-gray-400">
              <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
              <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
              <path d="M9 14l2 2 4-4" />
              <path d="M9 10h6" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma ficha clínica encontrada</h3>
          <p class="text-gray-500 mb-4">Este paciente ainda não possui fichas clínicas cadastradas.</p>
          <BaseButton type="button" variant="primary" @click="novaFichaClinica">
            Criar Primeira Ficha Clínica
          </BaseButton>
        </div>
      </BaseCard>
    </div>

    <!-- Modal de Cancelamento de Consulta -->
    <ActionModal 
      :open="showCancelamentoModal" 
      titulo="Cancelar Consulta"
      subtitulo="Confirme o cancelamento da consulta e informe o motivo"
      action-label="Confirmar Cancelamento"
      action-variant="red"
      border-color="danger"
      cancel-label="Cancelar"
      :action-disabled="cancelandoConsulta"
      modal-width="sm:max-w-md"
      @acao="confirmarCancelamento"
      @cancel="fecharModalCancelamento">
      
      <div class="space-y-4">
        <div v-if="consultaParaCancelar" class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
          <p class="text-sm text-gray-700">
            <span class="font-medium">Paciente:</span> {{ paciente?.nome || consultaParaCancelar.nome_paciente || consultaParaCancelar.paciente?.nome || 'N/A' }}
          </p>
          <p class="text-sm text-gray-700 mt-1">
            <span class="font-medium">Data/Hora:</span> {{ formatDate(consultaParaCancelar.data) }} - {{ consultaParaCancelar.horario_inicio }}
          </p>
          <p class="text-sm text-gray-700 mt-1">
            <span class="font-medium">Médico:</span> {{ consultaParaCancelar.medico_nome || consultaParaCancelar.user?.name || 'N/A' }}
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

    <!-- Modal de Encerramento de Consulta -->
    <ActionModal 
      :open="showEncerrarModal" 
      titulo="Encerrar Consulta"
      subtitulo="Confirme o encerramento da consulta. Você pode adicionar observações finais (opcional)."
      action-label="Encerrar Consulta"
      action-variant="blue"
      border-color="blue"
      :action-disabled="encerrandoConsulta"
      modal-width="sm:max-w-md"
      @acao="confirmarEncerrarConsulta"
      @cancel="fecharModalEncerrar">
      
      <div class="space-y-4">
        <div v-if="consultaParaEncerrar" class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
          <p class="text-sm text-gray-700">
            <span class="font-medium">Paciente:</span> {{ consultaParaEncerrar.nome_paciente || 'N/A' }}
          </p>
          <p class="text-sm text-gray-700 mt-1">
            <span class="font-medium">Data/Hora:</span> {{ formatDate(consultaParaEncerrar.data) }} - {{ consultaParaEncerrar.horario_inicio }}
          </p>
          <p class="text-sm text-gray-700 mt-1">
            <span class="font-medium">Médico:</span> {{ consultaParaEncerrar.medico_nome || 'N/A' }}
          </p>
        </div>
        
        <div>
          <BaseTextarea 
            v-model="observacoesFinais" 
            label="Observações Finais (Opcional)"
            placeholder="Adicione observações sobre o encerramento da consulta..."
            :rows="4"
          />
          <p class="text-xs text-gray-500 mt-1">
            As observações serão adicionadas às observações da consulta.
          </p>
        </div>
      </div>
    </ActionModal>

    <!-- Modal: consulta histórica -->
    <ActionModal
      :open="showModalHistorico"
      titulo="Cadastrar consulta antiga"
      subtitulo="Registre um atendimento passado no histórico deste paciente."
      action-label="Salvar no histórico"
      action-variant="blue"
      cancel-label="Cancelar"
      modal-width="sm:max-w-lg"
      :action-disabled="salvandoHistorico"
      @acao="salvarConsultaHistorica"
      @cancel="fecharModalHistorico">
      <div class="space-y-3 pb-2">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Profissional *</label>
          <select v-model="formHistorico.user_id"
            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">Selecione</option>
            <option v-for="p in profissionais" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Procedimento *</label>
          <select v-model="formHistorico.procedimento"
            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">Selecione</option>
            <option value="Consulta">Consulta</option>
            <option value="Retorno">Retorno</option>
            <option value="Exame">Exame</option>
            <option value="Cirurgia">Cirurgia</option>
          </select>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <InputData v-model="formHistorico.data" label="Data *" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Início *</label>
            <input v-model="formHistorico.horario_inicio" type="time"
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fim *</label>
            <input v-model="formHistorico.horario_fim" type="time"
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" />
          </div>
        </div>
        <div class="flex items-center gap-2">
          <input id="hist-pago" v-model="formHistorico.pago" type="checkbox"
            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
          <label for="hist-pago" class="text-sm text-gray-700">Consulta paga</label>
        </div>
        <div v-if="formHistorico.pago" class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Forma de pagamento *</label>
            <select v-model="formHistorico.forma_pagamento"
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
              <option value="">Selecione</option>
              <option value="dinheiro">Dinheiro</option>
              <option value="pix">PIX</option>
              <option value="cartao_credito">Cartão crédito</option>
              <option value="cartao_debito">Cartão débito</option>
              <option value="convenio">Convênio</option>
              <option value="transferencia">Transferência</option>
              <option value="outro">Outro</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valor</label>
            <input v-model="formHistorico.valor" type="number" min="0" step="0.01"
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
          <textarea v-model="formHistorico.observacoes" rows="2"
            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm resize-none"
            placeholder="Opcional"></textarea>
        </div>
        <p class="text-xs text-gray-500">A consulta será registrada como <strong>encerrada</strong> no histórico.</p>
      </div>
    </ActionModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { UserIcon } from '@heroicons/vue/24/outline'
import { ArrowUp, ArrowDown } from 'lucide-vue-next'
import { toast } from 'vue3-toastify'
import axios from '../../services/axios.js'

const route = useRoute()
const router = useRouter()

const pacienteId = computed(() => route.params.id)
const isLoading = ref(false)
const isLoadingFichas = ref(false)
const isLoadingConsultas = ref(false)
const error = ref(null)
const paciente = ref(null)
const fichasClinicas = ref([])
const consultas = ref([])

// Filtros de consultas
const filtroStatusConsulta = ref('')
const filtroDataConsulta = ref('')

// Modal de encerrar consulta
const showEncerrarModal = ref(false)
const consultaParaEncerrar = ref(null)
const observacoesFinais = ref('')
const encerrandoConsulta = ref(false)

// Modal de cancelar consulta
const showCancelamentoModal = ref(false)
const consultaParaCancelar = ref(null)
const motivoCancelamento = ref('')
const cancelandoConsulta = ref(false)

// Modal consulta histórica
const showModalHistorico = ref(false)
const salvandoHistorico = ref(false)
const profissionais = ref([])
const formHistorico = ref({
  user_id: '',
  procedimento: '',
  data: '',
  horario_inicio: '09:00',
  horario_fim: '09:30',
  pago: false,
  forma_pagamento: '',
  valor: '',
  observacoes: '',
})

// Ordenação
const sortField = ref(null)
const sortDirection = ref('asc')

// Opções de status para filtro
const opcoesStatusConsulta = [
  { value: '', label: 'Todos os status' },
  { value: '1', label: 'Agendada' },
  { value: '2', label: 'Confirmada' },
  { value: '4', label: 'Encerrada' },
  { value: '6', label: 'Em Atendimento' }
]

// Funções auxiliares
const getInitials = (name) => {
  if (!name) return '??'
  const names = name.trim().split(' ')
  if (names.length >= 2) {
    return (names[0][0] + names[names.length - 1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

const calcularIdade = (dataNascimento) => {
  if (!dataNascimento) return 'N/A'
  const today = new Date()
  const birth = new Date(dataNascimento)
  let age = today.getFullYear() - birth.getFullYear()
  const monthDiff = today.getMonth() - birth.getMonth()
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--
  }
  return age
}

const formatCPF = (cpf) => {
  if (!cpf) return null
  return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
}

const formatDate = (date) => {
  if (!date) return null
  
  // Se a data for uma string no formato YYYY-MM-DD (sem hora), tratar como data local
  if (typeof date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(date)) {
    const [year, month, day] = date.split('-').map(Number)
    return `${String(day).padStart(2, '0')}/${String(month).padStart(2, '0')}/${year}`
  }
  
  // Para outros formatos, usar Date normalmente
  const dateObj = new Date(date)
  if (isNaN(dateObj.getTime())) return null
  
  return dateObj.toLocaleDateString('pt-BR')
}

const formatDateTime = (date) => {
  if (!date) return null
  return new Date(date).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatSexo = (sexo) => {
  if (sexo === 'M') return 'Masculino'
  if (sexo === 'F') return 'Feminino'
  return sexo
}

// Consultas filtradas e ordenadas (mais recente primeiro)
const consultasFiltradas = computed(() => {
  let resultados = [...consultas.value]

  // Filtro por status
  if (filtroStatusConsulta.value) {
    resultados = resultados.filter(c => c.status_id === parseInt(filtroStatusConsulta.value))
  }

  // Filtro por data
  if (filtroDataConsulta.value) {
    resultados = resultados.filter(c => {
      if (!c.data) return false
      const dataConsulta = new Date(c.data).toISOString().split('T')[0]
      return dataConsulta >= filtroDataConsulta.value
    })
  }

  // Ordenar por data (mais recente primeiro)
  resultados.sort((a, b) => {
    const dataA = new Date(a.data + ' ' + a.horario_inicio)
    const dataB = new Date(b.data + ' ' + b.horario_inicio)
    return dataB - dataA
  })

  return resultados
})

// Verificar se há filtros ativos
const temFiltrosConsultasAtivos = computed(() => {
  return filtroStatusConsulta.value !== '' || filtroDataConsulta.value !== ''
})

// Aplicar filtros (placeholder - os filtros são reativos)
const aplicarFiltrosConsultas = () => {
  // Os filtros são aplicados automaticamente via computed
}

// Limpar filtros
const limparFiltrosConsultas = () => {
  filtroStatusConsulta.value = ''
  filtroDataConsulta.value = ''
}

// Formatar prioridade
const formatPrioridade = (prioridade) => {
  if (prioridade === 'alta') return 'Alta'
  if (prioridade === 'normal') return 'Normal'
  if (prioridade === 'baixa') return 'Baixa'
  return prioridade || 'Normal'
}

// Função para formatar o nome do status
const formatStatus = (statusNome) => {
  if (!statusNome) return 'N/A'
  
  const statusMap = {
    'ativo': 'Agendado',
    'em_atendimento': 'Em Atendimento',
    'agendada': 'Agendado',
    'confirmada': 'Confirmada',
    'encerrado': 'Encerrada',
    'cancelado': 'Cancelada',
    'suspenso': 'Suspenso'
  }
  
  // Converter para lowercase para fazer a comparação case-insensitive
  const statusLower = statusNome.toLowerCase().trim()
  
  // Retornar o valor mapeado ou o valor original se não houver mapeamento
  return statusMap[statusLower] || statusNome
}

// Classes para badge de status
const getStatusBadgeClass = (statusId) => {
  const classes = {
    1: 'bg-yellow-100 text-yellow-800', // Agendada
    2: 'bg-blue-100 text-blue-800', // Confirmada
    4: 'bg-gray-100 text-gray-800', // Encerrada
    6: 'bg-green-100 text-green-800' // Em Atendimento
  }
  return classes[statusId] || 'bg-gray-100 text-gray-800'
}

// Classes para badge de prioridade
const getPrioridadeBadgeClass = (prioridade) => {
  const classes = {
    alta: 'bg-orange-100 text-orange-800',
    normal: 'bg-blue-100 text-blue-800',
    baixa: 'bg-gray-100 text-gray-800'
  }
  return classes[prioridade] || 'bg-gray-100 text-gray-800'
}

// Fichas clínicas ordenadas
const fichasClinicasOrdenadas = computed(() => {
  if (!sortField.value) {
    return fichasClinicas.value
  }

  const sorted = [...fichasClinicas.value]

  sorted.sort((a, b) => {
    let valueA, valueB

    if (sortField.value === 'data') {
      valueA = new Date(a.data_consulta || a.created_at)
      valueB = new Date(b.data_consulta || b.created_at)
    } else if (sortField.value === 'profissional') {
      valueA = (a.user?.name || 'Não informado').toLowerCase()
      valueB = (b.user?.name || 'Não informado').toLowerCase()
    }

    if (sortDirection.value === 'asc') {
      if (sortField.value === 'data') {
        return valueA - valueB
      } else {
        return valueA < valueB ? -1 : valueA > valueB ? 1 : 0
      }
    } else {
      if (sortField.value === 'data') {
        return valueB - valueA
      } else {
        return valueA > valueB ? -1 : valueA < valueB ? 1 : 0
      }
    }
  })

  return sorted
})

// Função para ordenar
const ordenarPor = (campo) => {
  if (sortField.value === campo) {
    // Se já está ordenando por este campo, inverte a direção
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    // Se é um novo campo, define como ascendente
    sortField.value = campo
    sortDirection.value = 'asc'
  }
}

// Carregar dados do paciente
const loadPacienteData = async (id) => {
  isLoading.value = true
  error.value = null
  try {
    const response = await axios.get(`/buscar-paciente/${id}`)

    if (response.data.success) {
      paciente.value = response.data.data
    } else {
      error.value = response.data.message || 'Erro ao carregar dados do paciente'
      toast.error(error.value)
    }
  } catch (err) {
    console.error('Erro ao carregar paciente:', err)

    if (err.response?.status === 404) {
      error.value = 'Paciente não encontrado'
    } else if (err.response?.status === 403) {
      error.value = 'Você não tem permissão para visualizar este paciente'
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Erro ao carregar dados do paciente. Tente novamente.'
    }

    toast.error(error.value)
  } finally {
    isLoading.value = false
  }
}

// Carregar fichas clínicas do paciente
const loadFichasClinicas = async (id) => {
  isLoadingFichas.value = true
  try {
    const response = await axios.get(`/pacientes/${id}/fichas-clinicas`)

    if (response.data.success) {
      fichasClinicas.value = response.data.data || []
    } else {
      fichasClinicas.value = []
      const message = response.data.message || 'Erro ao carregar fichas clínicas'
      toast.error(message)
    }
  } catch (err) {
    console.error('Erro ao carregar fichas clínicas:', err)
    fichasClinicas.value = []

    if (err.response?.status === 404) {
      // Paciente não encontrado ou sem fichas - não é um erro crítico
      fichasClinicas.value = []
    } else if (err.response?.data?.message) {
      toast.error(err.response.data.message)
    } else {
      toast.error('Erro ao carregar fichas clínicas. Tente recarregar a página.')
    }
  } finally {
    isLoadingFichas.value = false
  }
}

// Carregar consultas do paciente
const carregarConsultasPaciente = async (id) => {
  isLoadingConsultas.value = true
  try {
    const response = await axios.get(`/pacientes/${id}/consultas`)

    if (response.data.success) {
      consultas.value = response.data.data || []
    } else {
      consultas.value = []
      const message = response.data.message || 'Erro ao carregar consultas'
      toast.error(message)
    }
  } catch (err) {
    console.error('Erro ao carregar consultas:', err)
    consultas.value = []

    if (err.response?.status === 404) {
      // Paciente não encontrado ou sem consultas - não é um erro crítico
      consultas.value = []
    } else if (err.response?.data?.message) {
      toast.error(err.response.data.message)
    } else {
      toast.error('Erro ao carregar consultas. Tente recarregar a página.')
    }
  } finally {
    isLoadingConsultas.value = false
  }
}

// Criar ficha clínica a partir de consulta
const criarFichaClinica = (consultaId) => {
  router.push(`/pacientes/ficha-clinica/${pacienteId.value}?consulta_id=${consultaId}`)
}

// Ver detalhes da consulta
const verDetalhesConsulta = (consultaId) => {
  router.push(`/pacientes/detalhes/${pacienteId.value}/consultas/${consultaId}`)
}

// Ver ficha clínica vinculada à consulta
const verFichaClinicaConsulta = (fichaClinicaId) => {
  router.push(`/pacientes/detalhes/${pacienteId.value}/ficha-clinica/${fichaClinicaId}/visualizar`)
}

// Cancelar consulta
const cancelarConsulta = (consultaId) => {
  // Buscar dados da consulta para exibir no modal
  const consulta = consultas.value.find(c => c.id === consultaId)
  if (consulta) {
    consultaParaCancelar.value = consulta
    motivoCancelamento.value = ''
    showCancelamentoModal.value = true
  } else {
    toast.error('Consulta não encontrada')
  }
}

// Confirmar cancelamento de consulta
const confirmarCancelamento = async () => {
  if (!motivoCancelamento.value || motivoCancelamento.value.trim() === '') {
    toast.error('Por favor, informe o motivo do cancelamento')
    return
  }
  
  if (!consultaParaCancelar.value) {
    toast.error('Consulta não encontrada')
    fecharModalCancelamento()
    return
  }
  
  cancelandoConsulta.value = true
  try {
    const response = await axios.post(`/consultas/${consultaParaCancelar.value.id}/cancelar`, {
      motivo_cancelamento: motivoCancelamento.value.trim()
    })
    
    if (response.data.success) {
      toast.success('Consulta cancelada com sucesso!')
      
      // Recarregar consultas para atualizar o status
      await carregarConsultasPaciente(pacienteId.value)
      
      // Fechar modal
      fecharModalCancelamento()
    } else {
      toast.error(response.data.message || 'Erro ao cancelar consulta')
    }
  } catch (err) {
    console.error('Erro ao cancelar consulta:', err)
    
    if (err.response?.data?.message) {
      toast.error(err.response.data.message)
    } else if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      Object.keys(errors).forEach(key => {
        toast.error(errors[key][0])
      })
    } else {
      toast.error('Erro ao cancelar consulta. Tente novamente.')
    }
  } finally {
    cancelandoConsulta.value = false
  }
}

// Fechar modal de cancelamento
const fecharModalCancelamento = () => {
  showCancelamentoModal.value = false
  consultaParaCancelar.value = null
  motivoCancelamento.value = ''
}

// Encerrar consulta
const encerrarConsulta = (consultaId) => {
  // Buscar dados da consulta para exibir no modal
  const consulta = consultas.value.find(c => c.id === consultaId)
  if (consulta) {
    consultaParaEncerrar.value = consulta
    observacoesFinais.value = ''
    showEncerrarModal.value = true
  } else {
    toast.error('Consulta não encontrada')
  }
}

// Confirmar encerramento de consulta
const confirmarEncerrarConsulta = async () => {
  if (!consultaParaEncerrar.value) {
    toast.error('Erro: Consulta não encontrada')
    return
  }

  encerrandoConsulta.value = true
  try {
    const payload = {}
    if (observacoesFinais.value && observacoesFinais.value.trim()) {
      payload.observacoes_finais = observacoesFinais.value.trim()
    }

    const response = await axios.put(`/consultas/${consultaParaEncerrar.value.id}/encerrar`, payload)

    if (response.data.success) {
      toast.success('Consulta encerrada com sucesso!')
      
      // Recarregar consultas para atualizar o status
      await carregarConsultasPaciente(pacienteId.value)
      
      // Fechar modal
      fecharModalEncerrar()
    } else {
      toast.error(response.data.message || 'Erro ao encerrar consulta')
    }
  } catch (err) {
    console.error('Erro ao encerrar consulta:', err)
    
    if (err.response?.data?.message) {
      toast.error(err.response.data.message)
    } else if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      Object.keys(errors).forEach(key => {
        toast.error(errors[key][0])
      })
    } else {
      toast.error('Erro ao encerrar consulta. Tente novamente.')
    }
  } finally {
    encerrandoConsulta.value = false
  }
}

// Fechar modal de encerrar consulta
const fecharModalEncerrar = () => {
  showEncerrarModal.value = false
  consultaParaEncerrar.value = null
  observacoesFinais.value = ''
}

// Recarregar dados
const recarregarDados = async () => {
  if (pacienteId.value) {
    await loadPacienteData(pacienteId.value)
    await loadFichasClinicas(pacienteId.value)
    await carregarConsultasPaciente(pacienteId.value)
  }
}

// Ações
const editarPaciente = () => {
  router.push(`/pacientes/cadastro/${pacienteId.value}`)
}

const novaFichaClinica = () => {
  router.push(`/pacientes/ficha-clinica/${pacienteId.value}`)
}

const resetFormHistorico = () => {
  formHistorico.value = {
    user_id: '',
    procedimento: '',
    data: '',
    horario_inicio: '09:00',
    horario_fim: '09:30',
    pago: false,
    forma_pagamento: '',
    valor: '',
    observacoes: '',
  }
}

const carregarProfissionais = async () => {
  try {
    const response = await axios.get('/consultas/profissionais')
    const lista = response.data?.data || []
    profissionais.value = lista.map((p) => ({
      id: p.id,
      name: p.name || `Profissional #${p.id}`,
    }))
  } catch (err) {
    console.error('Erro ao carregar profissionais:', err)
    profissionais.value = []
    toast.error('Não foi possível carregar a lista de profissionais')
  }
}

const abrirModalConsultaHistorica = async () => {
  resetFormHistorico()
  showModalHistorico.value = true
  await carregarProfissionais()
}

const fecharModalHistorico = () => {
  showModalHistorico.value = false
  salvandoHistorico.value = false
  resetFormHistorico()
}

const salvarConsultaHistorica = async () => {
  const f = formHistorico.value
  if (!f.user_id || !f.procedimento || !f.data || !f.horario_inicio || !f.horario_fim) {
    toast.error('Preencha profissional, procedimento, data e horários')
    return
  }

  const hoje = new Date().toISOString().slice(0, 10)
  if (f.data > hoje) {
    toast.error('Consulta histórica deve ter data de hoje ou anterior')
    return
  }

  if (f.horario_fim <= f.horario_inicio) {
    toast.error('Horário de fim deve ser após o início')
    return
  }

  if (f.pago && !f.forma_pagamento) {
    toast.error('Selecione a forma de pagamento')
    return
  }

  salvandoHistorico.value = true
  try {
    const payload = {
      historico: true,
      user_id: Number(f.user_id),
      paciente_id: Number(pacienteId.value),
      procedimento: f.procedimento,
      data: f.data,
      horario_inicio: f.horario_inicio,
      horario_fim: f.horario_fim,
      prioridade: 'normal',
      observacoes: f.observacoes || null,
      pago: !!f.pago,
      forma_pagamento: f.pago ? f.forma_pagamento : null,
      valor: f.pago && f.valor !== '' ? Number(f.valor) : null,
    }

    const response = await axios.post('/consultas', payload)
    if (response.data?.success) {
      toast.success(response.data.message || 'Consulta histórica cadastrada')
      fecharModalHistorico()
      await carregarConsultasPaciente(pacienteId.value)
    } else {
      toast.error(response.data?.message || 'Erro ao salvar consulta')
    }
  } catch (err) {
    const msg = err.response?.data?.message
      || err.response?.data?.errors && Object.values(err.response.data.errors)[0]?.[0]
      || 'Erro ao salvar consulta histórica'
    toast.error(msg)
  } finally {
    salvandoHistorico.value = false
  }
}

const verFichaClinica = (fichaClinicaId) => {
  router.push(`/pacientes/detalhes/${pacienteId.value}/ficha-clinica/${fichaClinicaId}/visualizar`)
}

const editarFichaClinica = (fichaClinicaId) => {
  router.push(`/pacientes/detalhes/${pacienteId.value}/ficha-clinica/${fichaClinicaId}`)
}

// Lifecycle
onMounted(async () => {
  if (pacienteId.value) {
    // Carregar dados do paciente primeiro
    await loadPacienteData(pacienteId.value)

    // Se o paciente foi carregado com sucesso, carregar fichas clínicas e consultas
    if (paciente.value) {
      await Promise.all([
        loadFichasClinicas(pacienteId.value),
        carregarConsultasPaciente(pacienteId.value)
      ])
    }
  } else {
    error.value = 'ID do paciente não fornecido'
    isLoading.value = false
  }
})
</script>

<style scoped>
/* Estilos para cabeçalhos ordenáveis */
thead th button {
  cursor: pointer;
  user-select: none;
}

thead th button:hover {
  color: #374151;
}

thead th button:focus {
  outline: none;
}
</style>
