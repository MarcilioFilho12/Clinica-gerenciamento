<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Gerenciar Pacientes" description="Encontre rapidamente pacientes cadastrados na clínica"
      :icon="UsersIcon" icon-bg-color="blue" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Gerenciar Pacientes' }
      ]" class="mb-8">
      <template #actions>
        <div class="flex items-center gap-4">
          <div class="text-sm text-gray-600 font-medium">
            {{ filteredPatients.length }} paciente{{ filteredPatients.length !== 1 ? 's' : '' }} encontrado{{
              filteredPatients.length !== 1 ? 's' : '' }}
          </div>
          <button @click="cadastrarNovoPaciente"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center space-x-2 font-medium">
            <Plus class="w-4 h-4" />
            <span>Cadastrar Novo Paciente</span>
          </button>
        </div>
      </template>
    </PageHeader>

    <!-- Filtros e Busca -->
    <BaseCard padding="md" class="mb-6">
      <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-4 flex-1 w-full">
          <!-- Busca -->
          <div class="flex-1 min-w-[200px]">
            <BaseInput v-model="searchQuery" type="text" label="Buscar" placeholder="Buscar por nome, CPF ou e-mail"
              :icon="Search" icon-position="left" @input="handleSearch" />
          </div>

          <!-- Filtro por Gênero -->
          <div class="w-full sm:w-48">
            <BaseSelect v-model="filterGender" label="Gênero" :options="[
              { value: '', label: 'Todos os gêneros' },
              { value: 'Masculino', label: 'Masculino' },
              { value: 'Feminino', label: 'Feminino' }
            ]" />
          </div>

          <!-- Filtro por Data -->
          <div class="w-full sm:w-48">
            <InputData v-model="filterDate" label="Última consulta (após)" />
          </div>
        </div>

        <!-- Botão Limpar Filtros -->
        <BaseButton v-if="hasActiveFilters" type="button" variant="ghost" size="sm" @click="clearFilters">
          Limpar Filtros
        </BaseButton>
      </div>
    </BaseCard>

    <!-- Loading State -->
    <BaseCard v-if="loading" padding="lg" class="text-center">
      <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Carregando pacientes...</h3>
      <p class="text-gray-500">Aguarde enquanto buscamos os dados</p>
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
          <h3 class="text-red-800 font-medium">Erro ao carregar pacientes</h3>
          <p class="text-red-600 text-sm mt-1">{{ error }}</p>
        </div>
      </div>
      <BaseButton type="button" variant="danger" size="sm" @click="reloadPatients">
        Tentar Novamente
      </BaseButton>
    </BaseCard>

    <!-- Tabela de Pacientes em Atendimento -->
    <BaseCard v-if="!loading && !error && pacientesEmAtendimento.length > 0" padding="none" class="mb-6">
      <!-- Header da Tabela -->
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Pacientes em Atendimento
        </h3>
        <p class="text-sm text-gray-500 mt-1">
          {{ pacientesEmAtendimento.length }} paciente{{ pacientesEmAtendimento.length !== 1 ? 's' : '' }} em atendimento
        </p>
      </div>

      <!-- Tabela Desktop -->
      <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Médico</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horário</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridade</th>
              <th class="pr-22 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="paciente in pacientesEmAtendimento" :key="paciente.id" class="hover:bg-gray-50">
              <!-- Nome -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center space-x-3">
                  <div
                    class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center text-sm font-bold text-white">
                    {{ getInitials(paciente.nome_paciente) }}
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">
                      {{ paciente.nome_paciente }}
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ paciente.contato }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Médico -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ paciente.medico_nome }}
                </div>
              </td>

              <!-- Horário -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ paciente.horario_inicio }}
                </div>
              </td>

              <!-- Prioridade -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getPrioridadeBadgeClass(paciente.prioridade)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ formatPrioridade(paciente.prioridade) }}
                </span>
              </td>

              <!-- Ações -->
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                <div class="flex justify-end gap-2">
                  <BaseButton type="button" variant="primary" size="sm" @click="atenderPaciente(paciente)">
                    Atender
                  </BaseButton>
                  <BaseButton type="button" variant="outline" size="sm" @click="verDetalhes(paciente.paciente_id)">
                    Detalhes
                  </BaseButton>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Cards Mobile -->
      <div class="md:hidden p-4 space-y-4">
        <div v-for="paciente in pacientesEmAtendimento" :key="`mobile-atendimento-${paciente.id}`"
          class="border border-gray-200 rounded-lg p-4">
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center space-x-3">
              <div
                class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center text-sm font-bold text-white">
                {{ getInitials(paciente.nome_paciente) }}
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-900">
                  {{ paciente.nome_paciente }}
                </h4>
                <p class="text-xs text-gray-500">{{ paciente.contato }}</p>
              </div>
            </div>
          </div>

          <div class="space-y-2 text-sm text-gray-600 mb-3">
            <div><strong>Médico:</strong> {{ paciente.medico_nome }}</div>
            <div><strong>Horário:</strong> {{ paciente.horario_inicio }}</div>
            <div><strong>Prioridade:</strong> 
              <span :class="getPrioridadeBadgeClass(paciente.prioridade)" class="px-2 py-1 text-xs font-semibold rounded-full">
                {{ formatPrioridade(paciente.prioridade) }}
              </span>
            </div>
          </div>

          <div class="flex flex-col gap-2">
            <BaseButton type="button" variant="primary" size="sm" class="w-full" @click="atenderPaciente(paciente)">
              Atender
            </BaseButton>
            <BaseButton type="button" variant="outline" size="sm" class="w-full" @click="verDetalhes(paciente.paciente_id)">
              Detalhes
            </BaseButton>
          </div>
        </div>
      </div>

      <!-- Estado Vazio -->
      <div v-if="pacientesEmAtendimento.length === 0" class="p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum paciente em atendimento</h3>
        <p class="text-gray-500">Não há pacientes sendo atendidos no momento.</p>
      </div>
    </BaseCard>

    <!-- Tabela de Pacientes -->
    <BaseCard v-if="!loading && !error" padding="none">
      <!-- Header da Tabela -->
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          Lista de Pacientes
        </h3>
        <p class="text-sm text-gray-500 mt-1">
          {{ filteredPatients.length }} paciente{{ filteredPatients.length !== 1 ? 's' : '' }} encontrado{{
            filteredPatients.length !== 1 ? 's' : '' }}
        </p>
      </div>

      <!-- Tabela Desktop -->
      <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPF</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Idade</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gênero</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Consulta
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="patient in filteredPatients" :key="patient.id" class="hover:bg-gray-50">
              <!-- Nome -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center space-x-3">
                  <div
                    class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-sm font-bold text-white">
                    {{ patient.initials }}
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">
                      {{ patient.name }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- CPF -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ patient.cpf }}
                </div>
              </td>

              <!-- Email -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ patient.email }}
                </div>
              </td>

              <!-- Idade -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ patient.age }} anos
                </div>
              </td>

              <!-- Gênero -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ patient.gender }}
                </div>
              </td>

              <!-- Última Consulta -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ patient.lastAppointment }}
                </div>
              </td>

              <!-- Ações -->
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <BaseButton type="button" variant="primary" size="sm" @click="verDetalhes(patient.id)">
                  Ver Detalhes
                </BaseButton>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Cards Mobile -->
      <div class="md:hidden p-4 space-y-4">
        <div v-for="patient in filteredPatients" :key="`mobile-${patient.id}`"
          class="border border-gray-200 rounded-lg p-4">
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center space-x-3">
              <div
                class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-sm font-bold text-white">
                {{ patient.initials }}
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-900">
                  {{ patient.name }}
                </h4>
              </div>
            </div>
          </div>

          <div class="space-y-2 text-sm text-gray-600 mb-3">
            <div><strong>CPF:</strong> {{ patient.cpf }}</div>
            <div><strong>Email:</strong> {{ patient.email }}</div>
            <div><strong>Idade:</strong> {{ patient.age }} anos</div>
            <div><strong>Gênero:</strong> {{ patient.gender }}</div>
            <div><strong>Última consulta:</strong> {{ patient.lastAppointment }}</div>
          </div>

          <BaseButton type="button" variant="primary" size="sm" class="w-full" @click="verDetalhes(patient.id)">
            Ver Detalhes
          </BaseButton>
        </div>
      </div>

      <!-- Estado Vazio -->
      <div v-if="filteredPatients.length === 0 && !loading" class="p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum paciente encontrado</h3>
        <p class="text-gray-500 mb-4">
          {{ hasActiveFilters
            ? 'Não há pacientes que correspondam aos filtros selecionados.'
            : 'Não há pacientes cadastrados no sistema.'
          }}
        </p>
        <BaseButton v-if="hasActiveFilters" type="button" variant="primary" size="sm" @click="clearFilters">
          Limpar Filtros
        </BaseButton>
      </div>
    </BaseCard>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { UsersIcon } from '@heroicons/vue/24/outline'
import { Search, Plus } from 'lucide-vue-next'
import axios from '../../services/axios'
import { urlPreCadastro } from '../../utils/fluxoAtendimento.js'

const router = useRouter()

const searchQuery = ref('')
const filterId = ref('')
const filterDate = ref('')
const filterGender = ref('')
const loading = ref(false)
const error = ref(null)

const patients = ref([])
const pacientesEmAtendimento = ref([])

// Função para calcular idade a partir da data de nascimento
const calculateAge = (birthDate) => {
  const today = new Date()
  const birth = new Date(birthDate)
  let age = today.getFullYear() - birth.getFullYear()
  const monthDiff = today.getMonth() - birth.getMonth()
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--
  }
  return age
}

// Função para obter iniciais do nome
const getInitials = (name) => {
  const names = name.trim().split(' ')
  if (names.length >= 2) {
    return (names[0][0] + names[names.length - 1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

// Função para formatar gênero
const formatGender = (sexo) => {
  if (sexo === 'M') return 'Masculino'
  if (sexo === 'F') return 'Feminino'
  return sexo || 'Não informado'
}

// Função para formatar CPF
const formatCPF = (cpf) => {
  if (!cpf) return 'Não informado'
  return cpf
}

// Função para formatar prioridade
const formatPrioridade = (prioridade) => {
  switch (prioridade) {
    case 'alta': return 'Alta'
    case 'normal': return 'Normal'
    case 'baixa': return 'Baixa'
    default: return 'Normal'
  }
}

// Função para obter classes CSS do badge de prioridade
const getPrioridadeBadgeClass = (prioridade) => {
  const classes = {
    alta: 'bg-orange-100 text-orange-800',
    normal: 'bg-blue-100 text-blue-800',
    baixa: 'bg-gray-100 text-gray-800'
  }
  return classes[prioridade] || 'bg-gray-100 text-gray-800'
}

// Carregar pacientes da API
const loadPatients = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await axios.get('/listar-pacientes')

    if (response.data.success) {
      // Transformar dados do backend para o formato esperado pelo frontend
      patients.value = response.data.data.map(paciente => ({
        id: paciente.id,
        name: paciente.nome,
        cpf: formatCPF(paciente.cpf),
        email: paciente.email || 'Não informado',
        age: calculateAge(paciente.data_nascimento),
        gender: formatGender(paciente.sexo),
        lastAppointment: paciente.updated_at ? new Date(paciente.updated_at).toLocaleDateString('pt-BR') : 'Sem registro',
        initials: getInitials(paciente.nome)
      }))
    }
  } catch (err) {
    console.error('Erro ao carregar pacientes:', err)
    error.value = 'Erro ao carregar pacientes. Tente novamente.'
  } finally {
    loading.value = false
  }
}

// Função para recarregar pacientes
const reloadPatients = () => {
  loadPatients()
}

// Carregar pacientes em atendimento
const carregarPacientesEmAtendimento = async () => {
  try {
    const response = await axios.get('/consultas/pacientes-em-atendimento')

    if (response.data.success) {
      pacientesEmAtendimento.value = response.data.data || []
    }
  } catch (err) {
    console.error('Erro ao carregar pacientes em atendimento:', err)
    // Não mostrar erro para o usuário, apenas logar
    pacientesEmAtendimento.value = []
  }
}

// Variável para armazenar o ID do intervalo
let intervalId = null

// Carregar pacientes ao montar o componente
onMounted(() => {
  loadPatients()
  carregarPacientesEmAtendimento()
  
  // Atualizar pacientes em atendimento a cada 30 segundos (opcional)
  intervalId = setInterval(() => {
    carregarPacientesEmAtendimento()
  }, 30000) // 30 segundos
})

// Limpar intervalo quando o componente for desmontado
onBeforeUnmount(() => {
  if (intervalId) {
    clearInterval(intervalId)
  }
})

const filteredPatients = computed(() => {
  let results = patients.value

  // Busca geral (nome, CPF, e-mail)
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    results = results.filter(p =>
      p.name.toLowerCase().includes(query) ||
      p.cpf.replace(/\D/g, '').includes(query.replace(/\D/g, '')) ||
      p.email.toLowerCase().includes(query)
    )
  }

  // Filtro por gênero
  if (filterGender.value) {
    results = results.filter(p => p.gender === filterGender.value)
  }

  // Filtro por data da última consulta
  if (filterDate.value) {
    results = results.filter(p => {
      if (p.lastAppointment === 'Sem registro') return false
      return new Date(p.lastAppointment.split('/').reverse().join('-')) >= new Date(filterDate.value)
    })
  }

  return results
})

const hasActiveFilters = computed(() => {
  return (
    searchQuery.value ||
    filterId.value ||
    filterDate.value ||
    filterGender.value
  )
})

const clearFilters = () => {
  searchQuery.value = ''
  filterId.value = ''
  filterDate.value = ''
  filterGender.value = ''
}

const handleSearch = () => { }

const verDetalhes = (patientId) => {
  router.push(`/pacientes/detalhes/${patientId}`)
}

const atenderPaciente = (paciente) => {
  if (!paciente?.paciente_id || !paciente?.id) return
  router.push(urlPreCadastro(paciente.paciente_id, paciente.id))
}

const cadastrarNovoPaciente = () => {
  router.push('/pacientes/cadastro')
}
</script>
