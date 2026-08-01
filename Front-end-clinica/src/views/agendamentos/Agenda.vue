<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Agenda de Consultas" description="Gerenciamento de agendamentos e horários" :icon="Calendar"
      class="mb-8" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Agenda de Consultas' }
      ]">
      <template #actions>
        <div class="flex items-center gap-3">
          <div class="inline-flex rounded-md border border-gray-200 overflow-hidden text-sm">
            <button type="button" @click="setViewMode('dia')"
              :class="['px-3 py-1.5', viewMode === 'dia' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50']">
              Dia
            </button>
            <button type="button" @click="setViewMode('semana')"
              :class="['px-3 py-1.5 border-l border-gray-200', viewMode === 'semana' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50']">
              Semana
            </button>
            <button type="button" @click="setViewMode('mes')"
              :class="['px-3 py-1.5 border-l border-gray-200', viewMode === 'mes' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50']">
              Mês
            </button>
          </div>
          <div class="text-sm text-gray-600 font-medium">
            {{ headerDate }}
          </div>
        </div>
      </template>
    </PageHeader>
    <!-- Controles -->
    <BaseCard padding="md" class="mb-6">
      <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-4 flex-1">
          <!-- Seleção de Data -->
          <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-2">Data</label>
            <div class="relative">
              <Calendar class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400 z-10" />
              <input type="date" v-model="selectedDate"
                class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors bg-white" />
            </div>
          </div>

          <!-- Seleção de Médico -->
          <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-2">Médico</label>
            <div class="relative">
              <select v-model="selectedDoctor"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors appearance-none bg-white min-w-[200px]">
                <option value="">Todos os médicos</option>
                <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                  {{ doctor.name }} - {{ doctor.specialty }}
                </option>
              </select>
            </div>
          </div>

        </div>

        <!-- Botão Nova Consulta -->
        <button @click="openModal()"
          class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center space-x-2 font-medium mt-7">
          <Plus class="w-4 h-4" />
          <span>Nova Consulta</span>
        </button>
      </div>
    </BaseCard>

    <!-- Mensagem quando clínica não funciona no dia -->
    <div v-if="viewMode === 'dia' && configuracao && !configuracao.dia_funcionamento && !loading && !error"
      class="bg-orange-50 border border-orange-200 rounded-lg p-6 mb-6">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
          <Calendar class="w-5 h-5 text-orange-600" />
        </div>
        <div>
          <h3 class="text-lg font-semibold text-orange-800">Clínica não funciona neste dia</h3>
          <p class="text-orange-600 mt-1">
            A clínica não está aberta em {{ formatDateLong(selectedDate) }}.
            <br>
            Horário de funcionamento: {{ configuracao.horario_inicio }} às {{ configuracao.horario_fim }}
          </p>
        </div>
      </div>
    </div>

    <!-- Mensagem de erro -->
    <div v-if="error && !loading" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
          <X class="w-5 h-5 text-red-600" />
        </div>
        <div>
          <h3 class="text-lg font-semibold text-red-800">Erro ao carregar agenda</h3>
          <p class="text-red-600 mt-1">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Mensagem quando não há configuração -->
    <div v-if="!configuracao && !error && !loading" class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
          <Calendar class="w-5 h-5 text-blue-600" />
        </div>
        <div class="flex-1">
          <h3 class="text-lg font-semibold text-blue-800">Você ainda não possui nenhuma configuração cadastrada</h3>
          <p class="text-blue-600 mt-1 mb-4">Para começar a usar a agenda, é necessário criar uma configuração de agendamento.</p>
          <button 
            @click="$router.push('/configuracoes/agendamentos/novo')"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center space-x-2 font-medium">
            <Plus class="w-4 h-4" />
            <span>Criar Configuração</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Visão Semana -->
    <BaseCard v-if="viewMode === 'semana' && !loading && !error" padding="md" class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <button type="button" class="px-3 py-1.5 text-sm border rounded-md hover:bg-gray-50" @click="shiftPeriod(-1)">← Semana anterior</button>
        <h3 class="font-semibold text-gray-900">{{ periodoLabel }}</h3>
        <button type="button" class="px-3 py-1.5 text-sm border rounded-md hover:bg-gray-50" @click="shiftPeriod(1)">Próxima semana →</button>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
        <button
          v-for="dia in weekDays"
          :key="dia.date"
          type="button"
          @click="abrirDia(dia.date)"
          class="text-left border rounded-lg p-3 hover:border-blue-400 hover:bg-blue-50 transition-colors min-h-[120px]"
        >
          <div class="text-xs font-medium text-gray-500 uppercase">{{ dia.label }}</div>
          <div class="text-lg font-semibold text-gray-900">{{ dia.dayNumber }}</div>
          <div class="mt-2 text-sm text-blue-700 font-medium">{{ dia.total }} consulta(s)</div>
          <ul class="mt-2 space-y-1">
            <li v-for="c in dia.preview" :key="c.id" class="text-xs text-gray-600 truncate">
              {{ c.horario }} · {{ c.paciente }}
              <span v-if="c.pago" class="text-green-600">· pago</span>
            </li>
          </ul>
        </button>
      </div>
    </BaseCard>

    <!-- Visão Mês -->
    <BaseCard v-if="viewMode === 'mes' && !loading && !error" padding="md" class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <button type="button" class="px-3 py-1.5 text-sm border rounded-md hover:bg-gray-50" @click="shiftPeriod(-1)">← Mês anterior</button>
        <h3 class="font-semibold text-gray-900">{{ periodoLabel }}</h3>
        <button type="button" class="px-3 py-1.5 text-sm border rounded-md hover:bg-gray-50" @click="shiftPeriod(1)">Próximo mês →</button>
      </div>
      <div class="grid grid-cols-7 gap-2 mb-2">
        <div v-for="nome in ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb']" :key="nome" class="text-center text-xs font-medium text-gray-500 py-1">{{ nome }}</div>
      </div>
      <div class="grid grid-cols-7 gap-2">
        <button
          v-for="cel in monthCells"
          :key="cel.key"
          type="button"
          :disabled="!cel.inMonth"
          @click="cel.inMonth && abrirDia(cel.date)"
          :class="[
            'min-h-[84px] rounded-lg border p-2 text-left transition-colors',
            cel.inMonth ? 'hover:border-blue-400 hover:bg-blue-50 bg-white' : 'bg-gray-50 text-gray-300 cursor-default',
            cel.date === selectedDate ? 'ring-2 ring-blue-500' : ''
          ]"
        >
          <div class="text-sm font-semibold">{{ cel.dayNumber }}</div>
          <div v-if="cel.inMonth" class="text-xs mt-1" :class="cel.total ? 'text-blue-700 font-medium' : 'text-gray-400'">
            {{ cel.total }} consulta(s)
          </div>
        </button>
      </div>
    </BaseCard>

    <!-- Grid de Médicos -->
    <div v-if="viewMode === 'dia' && !selectedDoctor && (configuracao?.dia_funcionamento || loading) && !error"
      class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
      <BaseCard v-for="doctor in doctors" :key="doctor.id" padding="sm">
        <!-- Header do Médico -->
        <template #header>
          <div class="flex items-center space-x-3">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <UserCheck class="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">{{ doctor.name }}</h3>
              <p class="text-sm text-gray-600">{{ doctor.specialty }}</p>
              <p class="text-xs text-gray-500">{{ doctor.crm }}</p>
            </div>
          </div>
        </template>

        <!-- Horários do Médico -->
        <div class="space-y-2">
          <!-- Loading state -->
          <div v-if="loading" class="text-center py-4 text-gray-500">
            Carregando horários...
          </div>

          <!-- Error state -->
          <div v-else-if="error" class="text-center py-4 text-red-500">
            {{ error }}
          </div>

          <!-- No funciona neste dia -->
          <div v-else-if="configuracao && !configuracao.dia_funcionamento" class="text-center py-4 text-gray-500">
            Clínica não funciona neste dia
          </div>

          <!-- Todos os horários (disponíveis e ocupados) -->
          <div v-else v-for="horario in getHorariosDisponiveis(doctor.id)"
            :key="`${doctor.id}-${horario.horario_inicio}`"             :class="[
              'flex items-center p-3 border rounded-md transition-colors gap-2',
              horario.ocupado
                ? (isConsultaEmAtendimento(horario)
                    ? 'border-green-300 bg-green-50 hover:bg-green-100'
                    : isConsultaEncerrada(horario)
                    ? 'border-blue-300 bg-blue-50 hover:bg-blue-100'
                    : isConsultaEmergencial(horario)
                    ? 'border-orange-300 bg-orange-50 hover:bg-orange-100'
                    : 'border-red-200 bg-red-50 hover:bg-red-100')
                : 'border-gray-200 hover:bg-gray-50'
            ]">
            <div class="flex flex-col items-start space-y-1 flex-shrink-0">
              <div class="flex items-center space-x-2">
                <Clock :class="horario.ocupado 
                  ? (isConsultaEmAtendimento(horario) ? 'w-4 h-4 text-green-500'
                    : isConsultaEncerrada(horario) ? 'w-4 h-4 text-blue-500'
                    : isConsultaEmergencial(horario) ? 'w-4 h-4 text-orange-500' 
                    : 'w-4 h-4 text-red-400')
                  : 'w-4 h-4 text-gray-400'" />
                <span class="font-mono text-sm font-medium whitespace-nowrap">
                  {{ isConsultaEmergencial(horario) && horario.consulta?.horario_inicio 
                    ? horario.consulta.horario_inicio 
                    : horario.horario_inicio }}
                </span>
                <span class="text-xs text-gray-400 whitespace-nowrap">- 
                  {{ isConsultaEmergencial(horario) && horario.consulta?.horario_fim 
                    ? horario.consulta.horario_fim 
                    : horario.horario_fim }}
                </span>
              </div>
              <span v-if="isConsultaEmAtendimento(horario)" 
                class="ml-5 px-2 py-0.5 text-xs font-semibold rounded-full bg-green-200 text-green-800 whitespace-nowrap">
                Em Atendimento
              </span>
              <span v-else-if="isConsultaEncerrada(horario)" 
                class="ml-5 px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-200 text-blue-800 whitespace-nowrap">
                Já Realizada
              </span>
              <span v-else-if="isConsultaEmergencial(horario)" 
                class="ml-5 px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-200 text-orange-800 whitespace-nowrap">
                Urgente
              </span>
            </div>

            <!-- Horário ocupado -->
            <div v-if="horario.ocupado && horario.consulta" 
              @click="abrirFluxoMedico(horario.consulta)"
              :class="[
                'flex items-center gap-2 flex-1 min-w-0 ml-2 cursor-pointer rounded p-1 transition-colors hover:bg-blue-50',
              ]"
              title="Abrir pré-cadastro e ficha clínica">
              <div class="flex-1 min-w-0 overflow-hidden">
                <p class="text-sm font-medium text-gray-900 truncate">
                  {{ horario.consulta.paciente?.nome || 'Sem paciente' }}
                </p>
                <p class="text-xs text-gray-500 truncate">
                  {{ horario.consulta.paciente?.contato || '' }}
                </p>
              </div>
              <div class="flex-shrink-0 flex items-center gap-0.5" @click.stop>
                <button
                  v-if="!horario.consulta.chegada_em && !isConsultaEmAtendimento(horario) && !isConsultaEncerrada(horario)"
                  @click="confirmarChegadaModal(horario.consulta, doctor.name)"
                  class="p-1 text-gray-400 hover:text-green-600 transition-colors"
                  title="Confirmar chegada">
                  <UserCheck class="w-3.5 h-3.5" />
                </button>
                <button 
                  @click="editAppointmentFromHorario(horario.consulta)"
                  :disabled="isConsultaEmAtendimento(horario) || isConsultaEncerrada(horario)"
                  :class="[
                    'p-1 transition-colors',
                    (isConsultaEmAtendimento(horario) || isConsultaEncerrada(horario))
                      ? 'text-gray-300 cursor-not-allowed'
                      : 'text-gray-400 hover:text-blue-600'
                  ]" 
                  :title="isConsultaEmAtendimento(horario) ? 'Consultas em atendimento não podem ser editadas' : isConsultaEncerrada(horario) ? 'Consultas já realizadas não podem ser editadas' : 'Editar consulta'">
                  <Edit class="w-3 h-3" />
                </button>
              </div>
            </div>

            <!-- Horário disponível -->
            <div v-else>
              <button v-if="!isHorarioNoPassado(selectedDate, horario.horario_inicio)" @click="openModal(horario.horario_inicio, doctor.id)"
                class="text-xs text-blue-600 hover:text-blue-700 font-medium px-2 py-1 rounded border border-blue-200 hover:bg-blue-50 transition-colors">
                Agendar
              </button>
              <span v-else class="text-xs text-gray-400 px-2 py-1">
                Data passada
              </span>
            </div>
          </div>

          <!-- Sem horários disponíveis -->
          <div
            v-if="!loading && !error && configuracao?.dia_funcionamento && getHorariosDisponiveis(doctor.id).length === 0"
            class="text-center py-4 text-gray-500">
            Nenhum horário disponível
          </div>
        </div>

        <!-- Resumo do Médico -->
        <template #footer>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Consultas:</span>
            <span class="font-medium">{{ getDoctorAppointments(doctor.id).length }}/{{
              getHorariosDisponiveis(doctor.id).length }}</span>
          </div>
        </template>
      </BaseCard>
    </div>

    <!-- Agenda Individual do Médico -->
    <BaseCard v-else-if="viewMode === 'dia' && selectedDoctor && selectedDoctorData" padding="md">
      <!-- Header do Médico Selecionado -->
      <template #header>
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
              <UserCheck class="w-8 h-8 text-blue-600" />
            </div>
            <div>
              <h2 class="text-xl font-semibold text-gray-900">{{ selectedDoctorData?.name || 'Médico não encontrado' }}
              </h2>
              <p class="text-gray-600">{{ selectedDoctorData?.specialty || 'Especialidade não informada' }}</p>
              <p class="text-sm text-gray-500">{{ selectedDoctorData?.crm || 'CRM não informado' }}</p>
            </div>
          </div>
          <button @click="selectedDoctor = ''" class="text-gray-400 hover:text-gray-600 transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
      </template>

      <!-- Horários Detalhados -->
      <!-- Mensagem quando clínica não funciona -->
      <div v-if="configuracao && !configuracao.dia_funcionamento && !loading && !error" class="text-center py-12">
        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <Calendar class="w-8 h-8 text-orange-600" />
        </div>
        <h3 class="text-lg font-semibold text-orange-800 mb-2">Clínica não funciona neste dia</h3>
        <p class="text-orange-600">
          A clínica não está aberta em {{ formatDateLong(selectedDate) }}.
          <br>
          Horário de funcionamento: {{ configuracao.horario_inicio }} às {{ configuracao.horario_fim }}
        </p>
      </div>

      <!-- Grid de horários -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Slots padrão + Consultas de prioridade alta que não correspondem aos slots -->
        <div v-for="slot in getAllTimeSlotsForDoctor(selectedDoctor)" :key="slot.time || slot"
          :class="[
            'border rounded-lg p-4 hover:shadow-md transition-all',
            slot.appointment && slot.appointment.situacao_id === 6
              ? 'border-green-300 bg-green-50'
              : slot.appointment && slot.appointment.situacao_id === 4
              ? 'border-blue-300 bg-blue-50'
              : slot.appointment && slot.appointment.prioridade === 'alta'
              ? 'border-orange-300 bg-orange-50'
              : 'border-gray-200'
          ]">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-2">
              <Clock :class="slot.appointment && slot.appointment.situacao_id === 6
                ? 'w-4 h-4 text-green-500'
                : slot.appointment && slot.appointment.situacao_id === 4
                ? 'w-4 h-4 text-blue-500'
                : slot.appointment && slot.appointment.prioridade === 'alta' 
                ? 'w-4 h-4 text-orange-500' 
                : 'w-4 h-4 text-gray-500'" />
              <span class="font-mono font-semibold text-gray-900">
                {{ slot.appointment && slot.appointment.prioridade === 'alta' && slot.appointment.horario_inicio
                  ? slot.appointment.horario_inicio
                  : (slot.time || slot) }}
              </span>
              <span v-if="slot.appointment && slot.appointment.horario_fim" class="text-xs text-gray-400">
                - {{ slot.appointment.horario_fim }}
              </span>
              <span v-else-if="!slot.appointment" class="text-xs text-gray-400">
                - {{ getEndTime(slot.time || slot) }}
              </span>
              <span v-if="slot.appointment && slot.appointment.situacao_id === 6" 
                class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-200 text-green-800">
                Em Atendimento
              </span>
              <span v-else-if="slot.appointment && slot.appointment.situacao_id === 4" 
                class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-200 text-blue-800">
                Já Realizada
              </span>
              <span v-else-if="slot.appointment && slot.appointment.prioridade === 'alta'" 
                class="px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-200 text-orange-800">
                Urgente
              </span>
            </div>
            <span :class="[
              'px-2 py-1 text-xs font-medium rounded-full',
              slot.appointment 
                ? (slot.appointment.situacao_id === 6
                    ? 'bg-green-100 text-green-700'
                    : slot.appointment.situacao_id === 4
                    ? 'bg-blue-100 text-blue-700'
                    : slot.appointment.prioridade === 'alta' 
                    ? 'bg-orange-100 text-orange-700' 
                    : 'bg-red-100 text-red-700')
                : 'bg-green-100 text-green-700'
            ]">
              {{ slot.appointment 
                ? (slot.appointment.situacao_id === 6 ? 'Em Atendimento' 
                  : slot.appointment.situacao_id === 4 ? 'Já Realizada'
                  : 'Ocupado')
                : 'Disponível' }}
            </span>
          </div>

          <div v-if="slot.appointment" 
            @click="abrirFluxoMedico(slot.appointment.consulta || slot.appointment)"
            class="space-y-3 rounded p-2 transition-colors cursor-pointer hover:bg-blue-50"
            title="Abrir pré-cadastro e ficha clínica">
            <div class="space-y-2">
              <p class="font-medium text-gray-900">{{ slot.appointment.patient }}</p>
              <p class="text-sm text-gray-600 flex items-center">
                <Phone class="w-3 h-3 mr-1" />
                {{ slot.appointment.phone }}
              </p>
              <p v-if="slot.appointment.notes" class="text-xs text-gray-600 bg-gray-50 rounded p-2">
                {{ slot.appointment.notes }}
              </p>
            </div>

            <div class="flex items-center justify-between">
              <span :class="getStatusClass(slot.appointment.status)">
                {{ slot.appointment.status }}
              </span>
              <div class="flex space-x-1" @click.stop>
                <button
                  v-if="!slot.appointment.chegada_em && slot.appointment.situacao_id !== 6 && slot.appointment.situacao_id !== 4"
                  @click="confirmarChegadaModal(slot.appointment.consulta || slot.appointment, slot.appointment.doctorName)"
                  class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded transition-colors"
                  title="Confirmar chegada">
                  <UserCheck class="w-4 h-4" />
                </button>
                <button 
                  @click="editAppointment(slot.appointment)"
                  :disabled="slot.appointment.situacao_id === 6 || slot.appointment.situacao_id === 4"
                  :class="[
                    'p-1.5 rounded transition-colors',
                    (slot.appointment.situacao_id === 6 || slot.appointment.situacao_id === 4)
                      ? 'text-gray-300 cursor-not-allowed'
                      : 'text-gray-400 hover:text-blue-600 hover:bg-blue-50'
                  ]"
                  :title="slot.appointment.situacao_id === 6 ? 'Consultas em atendimento não podem ser editadas' : slot.appointment.situacao_id === 4 ? 'Consultas já realizadas não podem ser editadas' : 'Editar consulta'">
                  <Edit class="w-4 h-4" />
                </button>
                <button @click="completeAppointment(slot.appointment.id)"
                  v-if="slot.appointment.status === 'agendada'"
                  class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded transition-colors">
                  <Check class="w-4 h-4" />
                </button>
                <button @click="cancelAppointment(slot.appointment.id)"
                  class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                  <X class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-6">
            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <Plus class="w-4 h-4 text-gray-400" />
            </div>
            <button v-if="!isHorarioNoPassado(selectedDate, slot.time || slot)" @click="openModal(slot.time || slot, selectedDoctor)"
              class="text-blue-600 hover:text-blue-700 font-medium text-sm">
              Agendar consulta
            </button>
            <span v-else class="text-gray-400 font-medium text-sm">
              Horário passado
            </span>
          </div>
        </div>
      </div>
    </BaseCard>

    <!-- Estatísticas -->
    <div v-if="viewMode === 'dia'" class="mt-6 grid grid-cols-1 sm:grid-cols-4 gap-4">
      <BaseCard padding="sm">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
            <Calendar class="w-5 h-5 text-blue-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Agendadas</p>
            <p class="text-xl font-bold text-gray-900">{{ totalAgendadas }}</p>
          </div>
        </div>
      </BaseCard>

      <BaseCard padding="sm">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
            <Check class="w-5 h-5 text-green-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Atendidas</p>
            <p class="text-xl font-bold text-gray-900">{{ totalAtendidas }}</p>
          </div>
        </div>
      </BaseCard>

      <BaseCard padding="sm">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
            <Clock class="w-5 h-5 text-yellow-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pendentes</p>
            <p class="text-xl font-bold text-gray-900">{{ totalPendentes }}</p>
          </div>
        </div>
      </BaseCard>

      <BaseCard padding="sm">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
            <UserCheck class="w-5 h-5 text-gray-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Médicos Ativos</p>
            <p class="text-xl font-bold text-gray-900">{{ doctors.length }}</p>
          </div>
        </div>
      </BaseCard>
    </div>

    <ActionModal :open="showModal" :titulo="editingAppointment ? 'Editar Consulta' : 'Nova Consulta'"
      :subtitulo="editingAppointment ? 'Atualize os dados da consulta' : 'Selecione o paciente e preencha os dados para agendar'"
      :action-label="savingAppointment ? (editingAppointment ? 'Atualizando...' : 'Agendando...') : (editingAppointment ? 'Atualizar' : 'Agendar')"
      :action-disabled="savingAppointment" modal-width="sm:max-w-lg" @acao="saveAppointment" @cancel="closeModal">
      <div class="space-y-4">
        <div>
          <div class="flex gap-2">
            <div class="flex-1">
              <TypeaheadInput v-model="searchPacientesModal" label="Paciente *"
                placeholder="Digite o nome do paciente..."
                :search-function="buscarPacientes" :selected-item="pacienteSelecionado"
                :get-item-label="(item) => item.nome" :get-item-subtitle="(item) => {
                  const parts = []
                  if (item.cpf) parts.push(`CPF: ${item.cpf}`)
                  if (item.contato) parts.push(`Tel: ${item.contato}`)
                  return parts.join(' • ')
                }" :required="!editingAppointment" @select="selecionarPaciente" @clear="limparPaciente" />
              <p class="mt-1 text-xs text-gray-500">Busque e selecione o paciente cadastrado antes de agendar.</p>
            </div>

            <button @click="irCadastrarPacienteAntes" type="button"
              class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center space-x-1 text-sm font-medium mt-7"
              title="Cadastrar novo paciente">
              <UserPlus class="w-4 h-4" />
              <span class="hidden sm:inline">Novo</span>
            </button>
          </div>
        </div>

        <div>
          <InputTelefone v-model="form.phone" label="Telefone" :required="false" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-2">Médico</label>
            <div class="relative">
              <select v-model="form.doctorId"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors appearance-none bg-white"
                required>
                <option value="">Selecione o médico</option>
                <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                  {{ doctor.name }}
                </option>
              </select>
            </div>
          </div>

          <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-2">Horário</label>
            <div class="relative">
              <select v-model="form.time" :disabled="loadingHorariosModal || !form.doctorId || !form.date"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors appearance-none disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed disabled:opacity-60"
                required>
                <option value="">
                  {{ loadingHorariosModal ? 'Carregando horários...' : !form.doctorId || !form.date ? 'Selecione médico e data primeiro' : 'Selecione o horário' }}
                </option>
                <option v-for="horario in horariosDisponiveisModal" :key="horario.horario_inicio"
                  :value="horario.horario_inicio">
                  {{ horario.horario_inicio }} - {{ horario.horario_fim }}
                </option>
              </select>
              <div v-if="loadingHorariosModal"
                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-2">Procedimento</label>
            <div class="relative">
              <select v-model="form.procedimento"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors appearance-none bg-white"
                required>
                <option value="">Selecione o procedimento</option>
                <option v-for="procedimento in procedimentos" :key="procedimento" :value="procedimento">
                  {{ procedimento }}
                </option>
              </select>
            </div>
          </div>

          <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-2">Prioridade</label>
            <div class="relative">
              <select 
                v-model="form.prioridade"
                :disabled="editingAppointment && editingAppointment.prioridade === 'alta'"
                :class="[
                  'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors appearance-none',
                  editingAppointment && editingAppointment.prioridade === 'alta'
                    ? 'bg-gray-100 text-gray-500 cursor-not-allowed'
                    : 'bg-white'
                ]">
                <option v-for="prioridade in prioridadesFiltradas" :key="prioridade.value" :value="prioridade.value">
                  {{ prioridade.label }}
                </option>
              </select>
              <p v-if="editingAppointment && editingAppointment.prioridade === 'alta'" 
                class="mt-1 text-xs text-gray-500">
                Consultas urgentes não podem ter a prioridade alterada
              </p>
            </div>
          </div>
        </div>

        <div>
          <InputData v-model="form.date" label="Data" :required="true" />
        </div>

        <div class="w-full">
          <label class="block text-sm font-medium text-gray-700 mb-2">Convênio/Parceiro</label>
          <div class="relative">
            <select v-model="form.parceiro_id"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors appearance-none bg-white">
              <option value="">Particular</option>
              <option v-for="parceiro in parceiros" :key="parceiro.id" :value="parceiro.id">
                {{ parceiro.nome }}
              </option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="w-full">
            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
              <input type="checkbox" v-model="form.pago" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
              Consulta paga
            </label>
            <select v-model="form.forma_pagamento" :disabled="!form.pago"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors appearance-none bg-white disabled:bg-gray-100 disabled:text-gray-500">
              <option value="">Forma de pagamento</option>
              <option v-for="forma in formasPagamento" :key="forma.value" :value="forma.value">
                {{ forma.label }}
              </option>
            </select>
          </div>
          <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-2">Valor (R$)</label>
            <input type="number" step="0.01" min="0" v-model="form.valor" :disabled="!form.pago"
              placeholder="0,00"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500" />
          </div>
        </div>

        <div>
          <BaseTextarea v-model="form.notes" label="Observações" placeholder="Observações sobre a consulta..."
            :rows="3" />
        </div>
      </div>
    </ActionModal>

    <!-- Pré-pergunta: paciente cadastrado? -->
    <ActionModal
      :open="showModalPacienteCadastrado"
      titulo="Paciente já cadastrado?"
      subtitulo="Antes de agendar, confirme se o paciente já existe no sistema."
      action-label="Sim, já está cadastrado"
      action-variant="blue"
      cancel-label="Fechar"
      modal-width="sm:max-w-md"
      @acao="pacienteJaCadastrado"
      @cancel="fecharPerguntaPaciente">
      <div class="space-y-3 pb-1">
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-900">
          <p class="font-medium mb-1">Como deseja continuar?</p>
          <ul class="list-disc pl-5 space-y-1 text-blue-800">
            <li><strong>Sim</strong> — buscar e selecionar o paciente, depois agendar.</li>
            <li><strong>Não</strong> — cadastrar o paciente primeiro e voltar para agendar.</li>
          </ul>
        </div>
        <button type="button"
          class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-800 hover:bg-gray-50"
          @click="pacienteNaoCadastrado">
          Não, cadastrar paciente agora
        </button>
      </div>
    </ActionModal>

    <!-- Modal de Confirmação de Chegada -->
    <ActionModal 
      :open="showModalChegada" 
      titulo="Confirmar Chegada do Paciente"
      subtitulo="Deseja confirmar a chegada deste paciente ao consultório?"
      action-label="Confirmar Chegada"
      action-variant="green"
      cancel-label="Cancelar"
      modal-width="sm:max-w-md"
      @acao="confirmarChegada"
      @cancel="fecharModalChegada">
      
      <div v-if="consultaParaConfirmarChegada" class="space-y-4">
        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
          <p class="text-sm text-gray-700">
            <span class="font-medium">Paciente:</span> {{ consultaParaConfirmarChegada.paciente?.nome || consultaParaConfirmarChegada.patient }}
          </p>
          <p class="text-sm text-gray-700 mt-1">
            <span class="font-medium">Horário:</span> {{ consultaParaConfirmarChegada.horario_inicio || consultaParaConfirmarChegada.time }}
          </p>
          <p class="text-sm text-gray-700 mt-1">
            <span class="font-medium">Profissional:</span> {{ consultaParaConfirmarChegada.user?.name || 'N/A' }}
          </p>
        </div>
        
        <div class="flex items-center space-x-2 text-sm text-blue-600">
          <Info class="w-4 h-4 text-blue-500" />
          <span>Após confirmar, o paciente aparecerá na fila de espera.</span>
        </div>
      </div>
    </ActionModal>

    <!-- Confirmação: salvar sem paciente -->
    <ActionModal
      :open="showModalSemPaciente"
      titulo="Salvar sem paciente?"
      subtitulo="Nenhum paciente cadastrado está vinculado a esta consulta/pagamento."
      action-label="Salvar assim mesmo"
      action-variant="blue"
      border-color="warning"
      cancel-label="Voltar"
      modal-width="sm:max-w-md"
      @acao="confirmarSalvarSemPaciente"
      @cancel="fecharModalSemPaciente">
      <div class="space-y-3">
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-900">
          Você pode associar um paciente agora ou continuar sem vínculo. Consultas sem paciente
          entram no financeiro, mas sem histórico clínico individual.
        </div>
      </div>
    </ActionModal>
  </div>
</template>

<script>
import { Calendar, Clock, User, Phone, Plus, Search, Edit, Check, X, UserCheck, UserPlus, Info } from 'lucide-vue-next'
import axios from '../../services/axios.js'
import { toastSuccess, toastError, toastWarning } from '../../composables/useToast.js'
import { urlPreCadastro } from '../../utils/fluxoAtendimento.js'

export default {
  name: 'Agenda',
  components: {
    Calendar,
    Clock,
    User,
    Phone,
    Plus,
    Search,
    Edit,
    Check,
    X,
    UserCheck,
    UserPlus,
    Info,
  },
  data() {
    return {
      Calendar,
      selectedDate: '',
      selectedDoctor: '',
      viewMode: 'dia',
      periodoConsultas: [],
      showModal: false,
      editingAppointment: null,
      showModalChegada: false,
      showModalSemPaciente: false,
      showModalPacienteCadastrado: false,
      pendingOpen: null,
      consultaParaConfirmarChegada: null,
      loading: false,
      error: null,
      loadingHorariosModal: false,
      horariosDisponiveisModal: [],
      parceiros: [],
      profissionaisModal: [],
      savingAppointment: false,
      searchPacientesModal: '',
      pacienteSelecionado: null,
      profissionais: [],
      agendaData: null,
      configuracao: null,
      form: {
        doctorId: '',
        patient: '',
        phone: '',
        date: '',
        time: '',
        procedimento: 'Consulta',
        prioridade: 'normal',
        parceiro_id: '',
        pago: false,
        forma_pagamento: '',
        valor: '',
        notes: ''
      },
      procedimentos: ['Consulta', 'Retorno', 'Exame', 'Cirurgia'],
      formasPagamento: [
        { value: 'dinheiro', label: 'Dinheiro' },
        { value: 'pix', label: 'PIX' },
        { value: 'cartao_credito', label: 'Cartão de crédito' },
        { value: 'cartao_debito', label: 'Cartão de débito' },
        { value: 'convenio', label: 'Convênio' },
        { value: 'transferencia', label: 'Transferência' },
        { value: 'outro', label: 'Outro' },
      ],
      prioridades: [
        { value: 'normal', label: 'Normal' },
        { value: 'baixa', label: 'Baixa' },
        { value: 'alta', label: 'Alta' }
      ],

    }
  },
  computed: {
    doctors() {
      const profsToUse = this.showModal ? this.profissionaisModal : this.profissionais

      if (!profsToUse || !Array.isArray(profsToUse)) return [];

      return profsToUse.map(prof => ({
        id: prof.id,
        name: prof.name,
        specialty: prof.especialidade || prof.profile?.name || 'Sem especialidade',
        crm: prof.crm || 'CRM não informado',
        email: prof.email
      }))
    },
    timeSlots() {
      if (!this.configuracao || !this.configuracao.dia_funcionamento) {
        return []
      }

      const horarios = []
      const inicio = new Date('2000-01-01T' + this.configuracao.horario_inicio + ':00')
      const fim = new Date('2000-01-01T' + this.configuracao.horario_fim + ':00')
      const duracao = this.configuracao.duracao_consulta
      const intervalo = this.configuracao.intervalo_consulta

      let atual = new Date(inicio)
      while (atual < fim) {
        const horario = atual.toTimeString().slice(0, 5)
        horarios.push(horario)
        atual.setMinutes(atual.getMinutes() + duracao + intervalo)
      }

      return horarios
    },
    appointments() {
      if (!this.agendaData?.profissionais) return [];

      const consultas = []
      this.agendaData.profissionais.forEach(prof => {
        if (prof.consultas_agendadas && prof.consultas_agendadas.length > 0) {
          prof.consultas_agendadas.forEach(consulta => {
            let horarioFormatado = ''
            if (typeof consulta.horario_inicio === 'string') {
              if (consulta.horario_inicio.includes('T')) {
                const dateObj = new Date(consulta.horario_inicio)
                horarioFormatado = dateObj.toTimeString().substring(0, 5)
              } else {
                horarioFormatado = consulta.horario_inicio.substring(0, 5)
              }
            } else {
              horarioFormatado = this.normalizeTime(consulta.horario_inicio)
            }

            consultas.push({
              id: consulta.id,
              doctorId: prof.id,
              doctorName: prof.name, // Nome do profissional
              patient: consulta.paciente?.nome || 'Sem paciente',
              phone: consulta.paciente?.contato || '',
              date: consulta.data,
              time: horarioFormatado,
              status: 'agendada',
              notes: consulta.observacoes || '',
              procedimento: consulta.procedimento || '',
              prioridade: consulta.prioridade || 'normal',
              parceiro_id: consulta.parceiro_id || '',
              pago: !!consulta.pago,
              forma_pagamento: consulta.forma_pagamento || '',
              valor: consulta.valor ?? '',
              horario_inicio: horarioFormatado,
              horario_fim: consulta.horario_fim ? (typeof consulta.horario_fim === 'string' 
                ? consulta.horario_fim.substring(0, 5) 
                : consulta.horario_fim.format('H:i')) : null,
              situacao_id: consulta.situacao_id || null,
              chegada_em: consulta.chegada_em || null,
              codigo_chegada: consulta.codigo_chegada || null,
              consulta: consulta // Manter referência completa da consulta para uso no modal
            })
          })
        }
      })

      return consultas
    },
    filteredAppointments() {
      let filtered = this.appointments.filter(apt => apt.date === this.selectedDate)

      if (this.selectedDoctor) {
        filtered = filtered.filter(apt => apt.doctorId == this.selectedDoctor)
      }

      if (this.searchTerm) {
        filtered = filtered.filter(apt =>
          apt.patient.toLowerCase().includes(this.searchTerm.toLowerCase())
        )
      }

      return filtered
    },
    totalAgendadas() {
      return this.filteredAppointments.filter(apt => apt.status === 'agendada').length
    },
    totalAtendidas() {
      return this.filteredAppointments.filter(apt => apt.status === 'atendida').length
    },
    totalPendentes() {
      return this.filteredAppointments.filter(apt => apt.status === 'agendada').length
    },
    headerDate() {
      if (this.viewMode !== 'dia') {
        return this.periodoLabel
      }
      const dateToUse = this.selectedDate || this.obterDataAtual()
      return this.formatDateLong(dateToUse)
    },
    periodoRange() {
      const base = this.parseLocalDate(this.selectedDate || this.obterDataAtual())
      if (this.viewMode === 'semana') {
        const day = base.getDay()
        const start = new Date(base)
        start.setDate(base.getDate() - day)
        const end = new Date(start)
        end.setDate(start.getDate() + 6)
        return { inicio: this.formatDateISO(start), fim: this.formatDateISO(end) }
      }
      if (this.viewMode === 'mes') {
        const start = new Date(base.getFullYear(), base.getMonth(), 1)
        const end = new Date(base.getFullYear(), base.getMonth() + 1, 0)
        return { inicio: this.formatDateISO(start), fim: this.formatDateISO(end) }
      }
      return { inicio: this.selectedDate, fim: this.selectedDate }
    },
    periodoLabel() {
      const { inicio, fim } = this.periodoRange
      if (this.viewMode === 'mes') {
        const d = this.parseLocalDate(inicio)
        return d.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
      }
      return `${this.formatDateShort(inicio)} — ${this.formatDateShort(fim)}`
    },
    consultasPorData() {
      const map = {}
      ;(this.periodoConsultas || []).forEach((c) => {
        const data = typeof c.data === 'string'
          ? c.data.substring(0, 10)
          : (c.data?.substring?.(0, 10) || '')
        if (!map[data]) map[data] = []
        map[data].push(c)
      })
      return map
    },
    weekDays() {
      const { inicio } = this.periodoRange
      const start = this.parseLocalDate(inicio)
      const nomes = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']
      return Array.from({ length: 7 }, (_, i) => {
        const d = new Date(start)
        d.setDate(start.getDate() + i)
        const iso = this.formatDateISO(d)
        const list = this.consultasPorData[iso] || []
        return {
          date: iso,
          label: nomes[i],
          dayNumber: d.getDate(),
          total: list.length,
          preview: list.slice(0, 3).map((c) => ({
            id: c.id,
            horario: this.normalizeTime(c.horario_inicio),
            paciente: c.paciente?.nome || 'Paciente',
            pago: !!c.pago,
          })),
        }
      })
    },
    monthCells() {
      const { inicio } = this.periodoRange
      const first = this.parseLocalDate(inicio)
      const startPad = first.getDay()
      const daysInMonth = new Date(first.getFullYear(), first.getMonth() + 1, 0).getDate()
      const cells = []
      for (let i = 0; i < startPad; i++) {
        cells.push({ key: `pad-${i}`, inMonth: false, dayNumber: '', date: '', total: 0 })
      }
      for (let day = 1; day <= daysInMonth; day++) {
        const d = new Date(first.getFullYear(), first.getMonth(), day)
        const iso = this.formatDateISO(d)
        const list = this.consultasPorData[iso] || []
        cells.push({
          key: iso,
          inMonth: true,
          dayNumber: day,
          date: iso,
          total: list.length,
        })
      }
      while (cells.length % 7 !== 0) {
        cells.push({ key: `end-${cells.length}`, inMonth: false, dayNumber: '', date: '', total: 0 })
      }
      return cells
    },
    selectedDoctorData() {
      return this.doctors.find(doc => doc.id == this.selectedDoctor) || null
    },
    procedimentosSelectOptions() {
      return this.procedimentos.map(proc => ({
        value: proc,
        label: proc
      }))
    },
    prioridadesFiltradas() {
      // Se estiver editando uma consulta urgente (prioridade alta), mostrar apenas "Alta" (desabilitada)
      if (this.editingAppointment && this.editingAppointment.prioridade === 'alta') {
        return [{ value: 'alta', label: 'Alta' }]
      }
      // Para novos agendamentos ou edição de consultas normais, mostrar apenas Normal e Baixa
      return this.prioridades.filter(p => p.value !== 'alta')
    }
  },
  methods: {
    parseLocalDate(iso) {
      const [y, m, d] = iso.split('-').map(Number)
      return new Date(y, m - 1, d)
    },
    formatDateISO(dateObj) {
      const y = dateObj.getFullYear()
      const m = String(dateObj.getMonth() + 1).padStart(2, '0')
      const d = String(dateObj.getDate()).padStart(2, '0')
      return `${y}-${m}-${d}`
    },
    formatDateShort(iso) {
      return this.parseLocalDate(iso).toLocaleDateString('pt-BR')
    },
    setViewMode(mode) {
      this.viewMode = mode
      this.carregarAgenda()
    },
    shiftPeriod(direction) {
      const base = this.parseLocalDate(this.selectedDate || this.obterDataAtual())
      if (this.viewMode === 'semana') {
        base.setDate(base.getDate() + (direction * 7))
      } else if (this.viewMode === 'mes') {
        base.setMonth(base.getMonth() + direction)
      } else {
        base.setDate(base.getDate() + direction)
      }
      this.selectedDate = this.formatDateISO(base)
    },
    abrirDia(dateIso) {
      this.selectedDate = dateIso
      this.viewMode = 'dia'
      this.carregarAgenda()
    },
    obterDataAtual() {
      const hoje = new Date()
      const year = hoje.getFullYear()
      const month = String(hoje.getMonth() + 1).padStart(2, '0')
      const day = String(hoje.getDate()).padStart(2, '0')
      return `${year}-${month}-${day}`
    },
    formatDateLong(date) {
      if (!date) return ''

      try {
        if (typeof date === 'string' && date.match(/^\d{4}-\d{2}-\d{2}$/)) {
          const [ano, mes, dia] = date.split('-')
          const year = parseInt(ano, 10)
          const month = parseInt(mes, 10) - 1
          const day = parseInt(dia, 10)
          const dateObj = new Date(year, month, day)
          if (isNaN(dateObj.getTime())) return ''

          const weekdays = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado']
          const months = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro']

          const weekday = weekdays[dateObj.getDay()]
          const monthName = months[month]

          return `${weekday}, ${day} de ${monthName} de ${year}`
        }

        const dateObj = new Date(date + 'T00:00:00')
        if (isNaN(dateObj.getTime())) return '';

        const weekdays = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado']
        const months = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro']

        const day = dateObj.getDate()
        const month = months[dateObj.getMonth()]
        const year = dateObj.getFullYear()
        const weekday = weekdays[dateObj.getDay()]

        return `${weekday}, ${day} de ${month} de ${year}`
      } catch (error) {
        console.error('Erro ao formatar data:', error, date)
        return ''
      }
    },
    getAppointment(doctorId, time) {
      if (!doctorId || !time) return null
      return this.filteredAppointments.find(apt =>
        apt.doctorId == doctorId && apt.time === time
      )
    },
    getDoctorAppointments(doctorId) {
      if (!this.agendaData?.profissionais) return []

      const profissional = this.agendaData.profissionais.find(p => p.id == doctorId)
      if (!profissional) return []

      const horariosOcupados = profissional.horarios_disponiveis?.filter(h => h.ocupado) || []
      return horariosOcupados
    },
    getSelectedDoctor() {
      return this.doctors.find(doc => doc.id == this.selectedDoctor)
    },
    getHorariosDisponiveis(doctorId) {
      if (!this.agendaData?.profissionais) return []

      const profissional = this.agendaData.profissionais.find(p => p.id == doctorId)
      return profissional?.horarios_disponiveis || []
    },
    isHorarioOcupado(doctorId, horarioInicio) {
      const consultas = this.appointments.filter(apt =>
        apt.doctorId == doctorId && apt.time === horarioInicio
      )
      return consultas.length > 0 ? consultas[0] : null
    },
    getStatusClass(status) {
      const classes = {
        'agendada': 'px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700',
        'atendida': 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700',
        'cancelada': 'px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700'
      }
      return classes[status] || ''
    },
    isDataNoPassado(data) {
      if (!data) return false

      const hoje = new Date()
      const dataSelecionada = new Date(data)

      // Comparar apenas a data (sem horário)
      hoje.setHours(0, 0, 0, 0)
      dataSelecionada.setHours(0, 0, 0, 0)

      // Se a data selecionada for anterior a hoje, está no passado
      return dataSelecionada < hoje
    },
    isHorarioNoPassado(data, horario) {
      if (!data || !horario) return false

      const agora = new Date()
      
      // Obter data atual em formato local (sem timezone)
      const hoje = new Date()
      const hojeLocal = new Date(hoje.getFullYear(), hoje.getMonth(), hoje.getDate())
      
      // Converter data selecionada para formato local
      const [ano, mes, dia] = data.split('-').map(Number)
      const dataSelecionada = new Date(ano, mes - 1, dia)
      
      // Se a data for anterior a hoje, está no passado
      if (dataSelecionada < hojeLocal) {
        return true
      }
      
      // Se a data for futura (depois de hoje), não está no passado
      if (dataSelecionada > hojeLocal) {
        return false
      }
      
      // Se for hoje, verificar se o horário já passou
      const [hora, minuto] = horario.split(':').map(Number)
      const horarioAgendamento = new Date(agora.getFullYear(), agora.getMonth(), agora.getDate(), hora, minuto, 0)
      
      // Verificar se o horário já passou
      return horarioAgendamento < agora
    },
    normalizeTime(time) {
      if (typeof time === 'string') {
        return time.substring(0, 5)
      }
      return time
    },
    isConsultaEmergencial(horario) {
      // Verifica se é uma consulta de prioridade alta (emergencial)
      return horario.consulta && (
        horario.consulta.prioridade === 'alta' || 
        horario.is_emergencial === true
      )
    },
    isConsultaEmAtendimento(horario) {
      // Verifica se é uma consulta em atendimento (situacao_id = 6)
      return horario.consulta && horario.consulta.situacao_id === 6
    },
    isConsultaEncerrada(horario) {
      // Verifica se é uma consulta encerrada (situacao_id = 4)
      return horario.consulta && horario.consulta.situacao_id === 4
    },
    getAllTimeSlotsForDoctor(doctorId) {
      if (!doctorId || !this.agendaData?.profissionais) {
        return this.timeSlots.map(slot => ({ time: slot, appointment: null }))
      }

      const profissional = this.agendaData.profissionais.find(p => p.id == doctorId)
      if (!profissional) {
        return this.timeSlots.map(slot => ({ time: slot, appointment: null }))
      }

      // Obter todos os horários disponíveis (incluindo emergenciais)
      const horariosDisponiveis = profissional.horarios_disponiveis || []
      
      // Criar um mapa de slots padrão
      const slotsMap = new Map()
      this.timeSlots.forEach(slot => {
        slotsMap.set(slot, { time: slot, appointment: null })
      })

      // Adicionar consultas aos slots correspondentes
      horariosDisponiveis.forEach(horario => {
        if (horario.ocupado && horario.consulta) {
          const slotTime = horario.horario_inicio
          // Buscar o appointment correspondente
          const appointment = this.appointments.find(apt => 
            apt.doctorId == doctorId && apt.id === horario.consulta.id
          )
          
          if (appointment) {
            // Se o horário corresponde a um slot padrão, adicionar ao slot
            if (slotsMap.has(slotTime)) {
              slotsMap.get(slotTime).appointment = appointment
            } else {
              // Se não corresponde, adicionar como novo slot (emergencial)
              // Usar o horário da consulta se disponível
              const consultaTime = horario.consulta.horario_inicio || slotTime
              slotsMap.set(consultaTime, { 
                time: consultaTime, 
                appointment: appointment,
                isEmergencial: true
              })
            }
          } else if (horario.consulta) {
            // Se não encontrou no appointments, criar um objeto básico a partir da consulta
            const consultaTime = horario.consulta.horario_inicio || slotTime
            if (!slotsMap.has(consultaTime)) {
              slotsMap.set(consultaTime, {
                time: consultaTime,
                appointment: {
                  id: horario.consulta.id,
                  doctorId: doctorId,
                  doctorName: profissional?.name || null, // Adicionar nome do profissional
                  patient: horario.consulta.paciente?.nome || 'Sem paciente',
                  phone: horario.consulta.paciente?.contato || '',
                  status: 'agendada',
                  notes: horario.consulta.observacoes || '',
                  prioridade: horario.consulta.prioridade || 'normal',
                  horario_inicio: horario.consulta.horario_inicio,
                  horario_fim: horario.consulta.horario_fim,
                  situacao_id: horario.consulta.situacao_id,
                  chegada_em: horario.consulta.chegada_em || null,
                  codigo_chegada: horario.consulta.codigo_chegada || null,
                  consulta: horario.consulta // Manter referência completa
                },
                isEmergencial: horario.is_emergencial || horario.consulta.prioridade === 'alta'
              })
            }
          }
        }
      })

      // Converter para array e ordenar por horário
      const slots = Array.from(slotsMap.values())
      slots.sort((a, b) => {
        const timeA = a.time || '00:00'
        const timeB = b.time || '00:00'
        return timeA.localeCompare(timeB)
      })

      return slots
    },
    getEndTime(startTime) {
      if (!startTime || !this.configuracao) return ''
      
      const [hours, minutes] = startTime.split(':').map(Number)
      const start = new Date(2000, 0, 1, hours, minutes)
      const end = new Date(start.getTime() + (this.configuracao.duracao_consulta || 30) * 60000)
      
      return `${String(end.getHours()).padStart(2, '0')}:${String(end.getMinutes()).padStart(2, '0')}`
    },
    async openModal(time = '', doctorId = '') {
      const selectedDoctorId = doctorId || this.selectedDoctor || ''
      const horarioSelecionado = time

      this.pendingOpen = {
        doctorId: selectedDoctorId,
        time: horarioSelecionado,
        date: this.selectedDate,
      }

      // Nova consulta: perguntar se o paciente já está cadastrado
      this.showModalPacienteCadastrado = true
    },

    async pacienteJaCadastrado() {
      this.showModalPacienteCadastrado = false
      await this.abrirModalConsulta(this.pendingOpen || {})
    },

    fecharPerguntaPaciente() {
      this.showModalPacienteCadastrado = false
      this.pendingOpen = null
    },

    pacienteNaoCadastrado() {
      this.showModalPacienteCadastrado = false
      this.irCadastrarPacienteAntes()
    },

    irCadastrarPacienteAntes() {
      const ctx = this.pendingOpen || {
        doctorId: this.form.doctorId || this.selectedDoctor || '',
        time: this.form.time || '',
        date: this.form.date || this.selectedDate || '',
      }

      const params = new URLSearchParams({
        retorno: 'agenda',
      })
      if (ctx.date) params.set('date', ctx.date)
      if (ctx.time) params.set('time', ctx.time)
      if (ctx.doctorId) params.set('doctorId', String(ctx.doctorId))

      this.showModal = false
      this.showModalPacienteCadastrado = false
      this.$router.push(`/pacientes/cadastro?${params.toString()}`)
    },

    async abrirModalConsulta({ doctorId = '', time = '', date = '', paciente = null } = {}) {
      const selectedDoctorId = doctorId || this.selectedDoctor || ''
      const dataConsulta = date || this.selectedDate
      const horarioSelecionado = time

      this.form = {
        doctorId: selectedDoctorId,
        patient: paciente?.nome || '',
        phone: paciente?.contato || '',
        date: dataConsulta,
        time: horarioSelecionado,
        procedimento: 'Consulta',
        prioridade: 'normal',
        parceiro_id: '',
        pago: false,
        forma_pagamento: '',
        valor: '',
        notes: ''
      }
      this.editingAppointment = null
      this.showModal = true

      this.searchPacientesModal = paciente?.nome || ''
      this.pacienteSelecionado = paciente || null

      if (dataConsulta) {
        if (dataConsulta !== this.selectedDate) {
          this.selectedDate = dataConsulta
        }
        const dadosAgenda = await this.carregarAgendaParaData(dataConsulta)
        this.profissionaisModal = dadosAgenda.profissionais

        if (selectedDoctorId && dadosAgenda.configuracao?.dia_funcionamento) {
          await this.buscarHorariosDisponiveisModal(selectedDoctorId, dataConsulta)
          if (horarioSelecionado) {
            this.form.time = horarioSelecionado
          }
        }
      }
    },

    formatarDataParaInput(data) {
      if (!data) return ''

      if (typeof data === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(data)) {
        return data
      }
      if (typeof data === 'string' && data.includes('T')) {
        return data.split('T')[0]
      }

      if (data instanceof Date || (typeof data === 'string')) {
        const dateObj = new Date(data)
        if (!isNaN(dateObj.getTime())) {
          const year = dateObj.getFullYear()
          const month = String(dateObj.getMonth() + 1).padStart(2, '0')
          const day = String(dateObj.getDate()).padStart(2, '0')
          return `${year}-${month}-${day}`
        }
      }

      return ''
    },
    async editAppointment(appointment) {
      // Não permitir editar consultas em atendimento ou encerradas
      if (appointment.situacao_id === 6) {
        toastWarning('Consultas em atendimento não podem ser editadas', {
          autoClose: 3000,
        })
        return
      }
      if (appointment.situacao_id === 4) {
        toastWarning('Consultas já realizadas não podem ser editadas', {
          autoClose: 3000,
        })
        return
      }

      if (appointment.id) {
        try {
          const response = await axios.get(`/consultas/${appointment.id}`)
          if (response.data.success && response.data.data) {
            const consulta = response.data.data
            const dataFormatada = this.formatarDataParaInput(consulta.data)

            this.form = {
              doctorId: consulta.user_id?.toString() || '',
              patient: consulta.paciente?.nome || '',
              phone: consulta.paciente?.contato || '',
              date: dataFormatada,
              time: typeof consulta.horario_inicio === 'string'
                ? consulta.horario_inicio.substring(0, 5)
                : consulta.horario_inicio,
              procedimento: consulta.procedimento || '',
              prioridade: consulta.prioridade || 'normal',
              parceiro_id: consulta.parceiro_id || '',
              pago: !!consulta.pago,
              forma_pagamento: consulta.forma_pagamento || '',
              valor: consulta.valor ?? '',
              notes: consulta.observacoes || ''
            }

            if (consulta.paciente) {
              this.pacienteSelecionado = consulta.paciente
              this.searchPacientesModal = consulta.paciente.nome
            }

            this.editingAppointment = {
              id: consulta.id,
              doctorId: consulta.user_id,
              patient: consulta.paciente?.nome || '',
              phone: consulta.paciente?.contato || '',
              date: dataFormatada,
              time: typeof consulta.horario_inicio === 'string'
                ? consulta.horario_inicio.substring(0, 5)
                : consulta.horario_inicio,
              status: 'agendada',
              notes: consulta.observacoes || '',
              procedimento: consulta.procedimento || '',
              prioridade: consulta.prioridade || 'normal',
              parceiro_id: consulta.parceiro_id || '',
              situacao_id: consulta.situacao_id || null
            }

            this.showModal = true

            if (this.form.doctorId && this.form.date) {
              await this.buscarHorariosDisponiveisModal(this.form.doctorId, this.form.date)
              this.form.time = this.editingAppointment.time
            }
            return
          }
        } catch (err) {
          console.error('Erro ao carregar consulta:', err)
        }
      }
      const dataFormatada = this.formatarDataParaInput(appointment.date)
      this.form = {
        ...appointment,
        date: dataFormatada,
        doctorId: appointment.doctorId?.toString() || ''
      }
      this.editingAppointment = { ...appointment, date: dataFormatada }
      this.showModal = true

      if (this.form.doctorId && this.form.date) {
        await this.buscarHorariosDisponiveisModal(this.form.doctorId, this.form.date)
        this.form.time = appointment.time || ''
      }
    },
    async editAppointmentFromHorario(consultaData) {
      // Não permitir editar consultas em atendimento ou encerradas
      if (consultaData.situacao_id === 6) {
        toastWarning('Consultas em atendimento não podem ser editadas', {
          autoClose: 3000,
        })
        return
      }
      if (consultaData.situacao_id === 4) {
        toastWarning('Consultas já realizadas não podem ser editadas', {
          autoClose: 3000,
        })
        return
      }

      try {
        const response = await axios.get(`/consultas/${consultaData.id}`)
        if (response.data.success && response.data.data) {
          const consulta = response.data.data
          const dataFormatada = this.formatarDataParaInput(consulta.data)

          this.form = {
            doctorId: consulta.user_id?.toString() || '',
            patient: consulta.paciente?.nome || '',
            phone: consulta.paciente?.contato || '',
            date: dataFormatada,
            time: typeof consulta.horario_inicio === 'string'
              ? consulta.horario_inicio.substring(0, 5)
              : consulta.horario_inicio,
            procedimento: consulta.procedimento || '',
            prioridade: consulta.prioridade || 'normal',
            parceiro_id: consulta.parceiro_id || '',
            pago: !!consulta.pago,
            forma_pagamento: consulta.forma_pagamento || '',
            valor: consulta.valor ?? '',
            notes: consulta.observacoes || ''
          }

          if (consulta.paciente) {
            this.pacienteSelecionado = consulta.paciente
            this.ignoreNextWatch = true
            this.searchPacientesModal = consulta.paciente.nome
          }

          this.editingAppointment = {
            id: consulta.id,
            doctorId: consulta.user_id,
            patient: consulta.paciente?.nome || '',
            phone: consulta.paciente?.contato || '',
            date: dataFormatada,
            time: typeof consulta.horario_inicio === 'string'
              ? consulta.horario_inicio.substring(0, 5)
              : consulta.horario_inicio,
            status: 'agendada',
            notes: consulta.observacoes || '',
            procedimento: consulta.procedimento || '',
            prioridade: consulta.prioridade || 'normal',
            parceiro_id: consulta.parceiro_id || '',
            situacao_id: consulta.situacao_id || null
          }

          this.showModal = true

          if (this.form.doctorId && this.form.date) {
            await this.buscarHorariosDisponiveisModal(this.form.doctorId, this.form.date)
            this.form.time = this.editingAppointment.time
          }
        }
      } catch (err) {
        console.error('Erro ao carregar consulta:', err)
        toastError('Erro ao carregar consulta', {
          autoClose: 3000,
        })
      }
    },
    async buscarHorariosDisponiveisModal(doctorId, data) {
      if (!doctorId || !data) {
        this.horariosDisponiveisModal = []
        return
      }

      try {
        this.loadingHorariosModal = true
        const response = await axios.get('/consultas/horarios-disponiveis', {
          params: {
            user_id: doctorId,
            data: data
          }
        })

        if (response.data.success) {
          this.horariosDisponiveisModal = response.data.data.horarios_disponiveis || []
        } else {
          this.horariosDisponiveisModal = []
        }
      } catch (err) {
        console.error('Erro ao buscar horários disponíveis:', err)
        this.horariosDisponiveisModal = []
      } finally {
        this.loadingHorariosModal = false
      }
    },
    closeModal() {
      this.showModal = false
      this.editingAppointment = null
      this.form = { doctorId: '', patient: '', phone: '', date: '', time: '', procedimento: '', prioridade: 'normal', parceiro_id: '', pago: false, forma_pagamento: '', valor: '', notes: '' }
      this.horariosDisponiveisModal = []
      this.profissionaisModal = []

      this.searchPacientesModal = ''
      this.pacienteSelecionado = null
    },
    confirmarChegadaModal(consulta, doctorName = null) {
      // Garantir que temos a consulta correta (pode vir de slot.appointment.consulta ou diretamente)
      const consultaCompleta = consulta.consulta || consulta;
      
      // Adicionar nome do profissional se não estiver na consulta
      if (!consultaCompleta.user && doctorName) {
        consultaCompleta.user = { name: doctorName };
      } else if (!consultaCompleta.user && consulta.doctorName) {
        // Se não passou doctorName mas tem no consulta
        consultaCompleta.user = { name: consulta.doctorName };
      } else if (!consultaCompleta.user && consultaCompleta.doctorId) {
        // Buscar nome do profissional na lista de profissionais pelo doctorId
        const profissional = this.agendaData?.profissionais?.find(p => p.id == consultaCompleta.doctorId);
        if (profissional) {
          consultaCompleta.user = { name: profissional.name };
        }
      } else if (!consultaCompleta.user && consultaCompleta.user_id) {
        // Buscar nome do profissional na lista de profissionais pelo user_id
        const profissional = this.agendaData?.profissionais?.find(p => p.id == consultaCompleta.user_id);
        if (profissional) {
          consultaCompleta.user = { name: profissional.name };
        }
      } else if (!consultaCompleta.user && this.agendaData?.profissionais) {
        // Último recurso: tentar buscar na lista de doctors
        const doctor = this.doctors?.find(d => d.id == consultaCompleta.doctorId || d.id == consultaCompleta.user_id);
        if (doctor) {
          consultaCompleta.user = { name: doctor.name };
        }
      }
      
      this.consultaParaConfirmarChegada = consultaCompleta;
      this.showModalChegada = true;
    },
    async confirmarChegada() {
      if (!this.consultaParaConfirmarChegada) return;
      
      try {
        // Garantir que temos o ID da consulta
        const consultaId = this.consultaParaConfirmarChegada.id || this.consultaParaConfirmarChegada.consulta?.id;
        if (!consultaId) {
          toastError('ID da consulta não encontrado');
          return;
        }

        const response = await axios.post(
          `/consultas/${consultaId}/confirmar-chegada`
        );
        
        if (response.data.success) {
          toastSuccess('Chegada confirmada com sucesso!');
          await this.carregarAgenda(); // Recarregar agenda
          this.showModalChegada = false;
          this.consultaParaConfirmarChegada = null;
        }
      } catch (error) {
        console.error('Erro ao confirmar chegada:', error);
        toastError(error.response?.data?.message || 'Erro ao confirmar chegada');
      }
    },
    fecharModalChegada() {
      this.showModalChegada = false;
      this.consultaParaConfirmarChegada = null;
    },
    async saveAppointment() {
      try {
        this.savingAppointment = true

        if (!this.form.doctorId) {
          toastWarning('Selecione um médico', {
            autoClose: 3000,
          })
          this.savingAppointment = false
          return
        }

        if (!this.form.date) {
          toastWarning('Selecione uma data', {
            autoClose: 3000,
          })
          this.savingAppointment = false
          return
        }

        if (!this.form.time) {
          toastWarning('Selecione um horário', {
            autoClose: 3000,
          })
          this.savingAppointment = false
          return
        }

        if (!this.form.procedimento) {
          toastWarning('Selecione um procedimento', {
            autoClose: 3000,
          })
          this.savingAppointment = false
          return
        }

        if (this.form.pago && !this.form.forma_pagamento) {
          toastWarning('Selecione a forma de pagamento', {
            autoClose: 3000,
          })
          this.savingAppointment = false
          return
        }

        if (!this.pacienteSelecionado) {
          if (!this.editingAppointment) {
            toastWarning('Selecione o paciente cadastrado antes de agendar', {
              autoClose: 3500,
            })
            this.savingAppointment = false
            return
          }
          this.savingAppointment = false
          this.showModalSemPaciente = true
          return
        }

        await this.persistirConsulta()

      } catch (err) {
        console.error('Erro ao salvar consulta:', err)
        this.savingAppointment = false
      }
    },
    fecharModalSemPaciente() {
      this.showModalSemPaciente = false
    },
    async confirmarSalvarSemPaciente() {
      this.showModalSemPaciente = false
      this.savingAppointment = true
      await this.persistirConsulta()
    },
    async persistirConsulta() {
      try {
        const [hora, minuto] = this.form.time.split(':')
        const inicio = new Date()
        inicio.setHours(parseInt(hora), parseInt(minuto), 0, 0)

        const duracao = this.configuracao?.duracao_consulta || 30
        const fim = new Date(inicio.getTime() + duracao * 60000)
        const horarioFim = `${fim.getHours().toString().padStart(2, '0')}:${fim.getMinutes().toString().padStart(2, '0')}`

        const dadosConsulta = {
          user_id: parseInt(this.form.doctorId),
          paciente_id: this.pacienteSelecionado?.id || null,
          procedimento: this.form.procedimento,
          data: this.form.date,
          horario_inicio: this.form.time,
          horario_fim: horarioFim,
          prioridade: this.form.prioridade,
          parceiro_id: this.form.parceiro_id || null,
          observacoes: this.form.notes || null,
          pago: !!this.form.pago,
          forma_pagamento: this.form.pago ? (this.form.forma_pagamento || null) : null,
          valor: this.form.pago && this.form.valor !== '' ? Number(this.form.valor) : null,
        }

        let response

        if (this.editingAppointment) {
          response = await axios.put(`/consultas/${this.editingAppointment.id}`, dadosConsulta)
        } else {
          response = await axios.post('/consultas', dadosConsulta)
        }

        if (response.data.success) {
          await this.carregarAgenda()
          this.closeModal()

          toastSuccess(response.data.message || 'Consulta agendada com sucesso!', {
            autoClose: 3000,
          })
        } else {
          toastError(response.data.message || 'Erro ao salvar consulta', {
            autoClose: 4000,
          })
        }

      } catch (err) {
        console.error('Erro ao salvar consulta:', err)

        let errorMessage = 'Erro ao salvar consulta'

        if (err.response?.data?.message) {
          errorMessage = err.response.data.message
        } else if (err.response?.data?.errors) {
          const errors = err.response.data.errors
          const firstError = Object.keys(errors)[0]
          errorMessage = errors[firstError][0]
        }

        toastError(errorMessage, {
          autoClose: 4000,
        })
      } finally {
        this.savingAppointment = false
      }
    },
    completeAppointment(id) {
      const appointment = this.appointments.find(apt => apt.id === id)
      if (appointment) {
        appointment.status = 'atendida'
      }
    },
    cancelAppointment(id) {
      if (confirm('Tem certeza que deseja cancelar esta consulta?')) {
        const appointment = this.appointments.find(apt => apt.id === id)
        if (appointment) {
          appointment.status = 'cancelada'
        }
      }
    },
    novoPaciente() {
      this.irCadastrarPacienteAntes()
    },
    abrirFluxoMedico(consultaRaw) {
      const consulta = consultaRaw?.consulta || consultaRaw
      if (!consulta) return

      const consultaId = consulta.id
      const pacienteId = consulta.paciente_id || consulta.paciente?.id

      if (!consultaId || !pacienteId) {
        toastError('Não foi possível identificar o paciente desta consulta')
        return
      }

      if (consulta.situacao_id === 4) {
        this.$router.push(`/pacientes/detalhes/${pacienteId}/consultas/${consultaId}`)
        return
      }

      this.$router.push(urlPreCadastro(pacienteId, consultaId))
    },
    async carregarAgenda() {
      if (!this.selectedDate) return

      try {
        this.loading = true
        this.error = null

        if (this.viewMode === 'semana' || this.viewMode === 'mes') {
          const { inicio, fim } = this.periodoRange
          const params = new URLSearchParams({
            data_inicio: inicio,
            data_fim: fim,
          })
          if (this.selectedDoctor) {
            params.set('user_id', this.selectedDoctor)
          }
          const response = await axios.get(`/consultas/agenda-periodo?${params.toString()}`)
          if (response.data.success) {
            this.periodoConsultas = response.data.data.consultas || []
            this.error = null
          } else {
            throw new Error(response.data.message || 'Erro ao carregar agenda do período')
          }
          return
        }

        const response = await axios.get(`/consultas?data=${this.selectedDate}`)

        if (response.data.success) {
          this.agendaData = response.data.data
          this.profissionais = response.data.data.profissionais || []
          this.configuracao = response.data.data.configuracao
          this.periodoConsultas = []
        } else {
          throw new Error(response.data.message || 'Erro ao carregar agenda')
        }
      } catch (err) {
        console.error('Erro ao carregar agenda:', err)
        this.error = err.response?.data?.message || err.message || 'Erro ao carregar dados'
        this.profissionais = []
        this.agendaData = null
        this.configuracao = null
        this.periodoConsultas = []
      } finally {
        this.loading = false
      }
    },
    async carregarAgendaParaData(data) {
      if (!data) return

      try {
        const response = await axios.get(`/consultas?data=${data}`)

        if (response.data.success) {
          return {
            profissionais: response.data.data.profissionais || [],
            configuracao: response.data.data.configuracao
          }
        } else {
          throw new Error(response.data.message || 'Erro ao carregar agenda')
        }
      } catch (err) {
        console.error('Erro ao carregar agenda para data:', err)
        return {
          profissionais: [],
          configuracao: null
        }
      }
    },
    async carregarParceiros() {
      try {
        const response = await axios.get('/parceiros')
        if (response.data.success) {
          this.parceiros = response.data.data || []
        } else {
          this.parceiros = []
        }
      } catch (err) {
        console.error('Erro ao carregar parceiros:', err)
        this.parceiros = []
      }
    },
    async buscarPacientes(termo) {
      if (!termo || termo.length < 2) {
        return []
      }

      try {
        const response = await axios.get('/listar-pacientes', {
          params: {
            search: termo
          }
        })

        if (response.data.success) {
          return response.data.data || []
        } else {
          return []
        }
      } catch (err) {
        console.error('Erro ao buscar pacientes:', err)
        return []
      }
    },
    selecionarPaciente(paciente) {
      this.pacienteSelecionado = paciente
      this.form.patient = paciente.nome
      this.form.phone = paciente.contato || ''
      this.searchPacientesModal = paciente.nome
    },
    limparPaciente() {
      this.pacienteSelecionado = null
      this.form.patient = ''
      this.form.phone = ''
      this.searchPacientesModal = ''
    },
    async retomarAgendamentoAposCadastro() {
      const q = this.$route.query || {}
      if (q.agendar !== '1' || !q.paciente_id) return

      try {
        const response = await axios.get(`/buscar-paciente/${q.paciente_id}`)
        const paciente = response.data?.data
        if (!paciente) {
          toastWarning('Paciente cadastrado, mas não foi possível carregar os dados para agendar')
          return
        }

        if (q.date) {
          this.selectedDate = q.date
          await this.carregarAgenda()
        }

        this.pendingOpen = {
          doctorId: q.doctorId || this.selectedDoctor || '',
          time: q.time || '',
          date: q.date || this.selectedDate,
        }

        await this.abrirModalConsulta({
          doctorId: this.pendingOpen.doctorId,
          time: this.pendingOpen.time,
          date: this.pendingOpen.date,
          paciente,
        })

        toastSuccess('Paciente cadastrado. Complete o agendamento da consulta.')
      } catch (err) {
        console.error('Erro ao retomar agendamento:', err)
        toastError('Não foi possível abrir o agendamento com o paciente cadastrado')
      } finally {
        this.$router.replace({ path: '/agenda', query: {} })
      }
    },
  },
  watch: {
    selectedDate() {
      if (this.selectedDate) {
        this.carregarAgenda()
      }
    },
    selectedDoctor() {
      if (this.viewMode !== 'dia') {
        this.carregarAgenda()
      }
    },
    'form.pago'(pago) {
      if (!pago) {
        this.form.forma_pagamento = ''
        this.form.valor = ''
      }
    },
    'form.doctorId'(newDoctorId, oldDoctorId) {
      if (this.showModal && newDoctorId && this.form.date) {
        this.buscarHorariosDisponiveisModal(newDoctorId, this.form.date)
        if (oldDoctorId && oldDoctorId !== newDoctorId) {
          this.form.time = ''
        }
      }
    },
    'form.date': {
      async handler(newDate, oldDate) {
        if (this.showModal && newDate) {
          const dadosAgenda = await this.carregarAgendaParaData(newDate)
          this.profissionaisModal = dadosAgenda.profissionais

          if (this.form.doctorId && dadosAgenda.configuracao?.dia_funcionamento) {
            await this.buscarHorariosDisponiveisModal(this.form.doctorId, newDate)
          } else {
            if (oldDate && oldDate !== newDate) {
              this.form.time = ''
            }
            this.horariosDisponiveisModal = []
          }

          if (!dadosAgenda.configuracao?.dia_funcionamento) {
            this.form.doctorId = ''
          }
        }
      }
    },
  },
  async mounted() {
    this.selectedDate = this.obterDataAtual()
    await this.carregarAgenda()
    await this.carregarParceiros()
    await this.retomarAgendamentoAposCadastro()
  }
}
</script>

<style scoped>
select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}

select:disabled {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
}
</style>