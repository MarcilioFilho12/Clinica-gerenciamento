<template>
  <div class="max-w-4xl mx-auto p-6">
    <!-- Header -->
    <PageHeader
      :title="isFluxoAtendimento ? 'Pré-cadastro do Paciente' : (isRetornoAgenda ? 'Cadastrar paciente para agendar' : 'Cadastro de Paciente')"
      :description="isFluxoAtendimento
        ? 'Revise os dados preenchidos pela recepção e continue para a ficha clínica'
        : (isRetornoAgenda
          ? 'Cadastre o paciente e, em seguida, volte automaticamente para agendar a consulta'
          : 'Cadastre um novo paciente no sistema')"
      :icon="UserIcon"
      icon-bg-color="blue"
      :show-breadcrumbs="true"
      :breadcrumbs="breadcrumbs"
      class="mb-6">
      <template #actions>
        <div class="text-right">
          <p v-if="isLoadingPatient" class="text-sm text-blue-600 animate-pulse">Carregando...</p>
        </div>
      </template>
    </PageHeader>

    <div v-if="isFluxoAtendimento" class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
      Fluxo de atendimento: confirme o pré-cadastro e avance para preencher a ficha clínica.
    </div>

    <div v-else-if="isRetornoAgenda" class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
      Após salvar o cadastro, você voltará para a Agenda com este paciente já selecionado para concluir o agendamento.
    </div>

    <form :key="formKey" @submit.prevent="salvarPaciente" class="space-y-6">
      <!-- Dados Pessoais -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-user mr-2 text-[#D4AF37]">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
          Dados Pessoais
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="lg:col-span-2">
            <BaseInput v-model="paciente.nome" label="Nome Completo" type="text" placeholder="Nome completo do paciente"
              required />
          </div>

          <div>
            <InputData v-model="paciente.dataNascimento" label="Data de Nascimento" required />
          </div>

          <div>
            <InputNumber v-model="paciente.rg" label="RG" placeholder="Digite o RG" />
          </div>

          <div>
            <InputCPF v-model="paciente.cpf" label="CPF" placeholder="000.000.000-00" />
          </div>

          <div>
            <BaseSelect v-model="paciente.sexo" label="Sexo"
              :options="[{ value: 'M', label: 'Masculino' }, { value: 'F', label: 'Feminino' }]"
              placeholder="Selecione" />
          </div>

          <div class="lg:col-span-3">
            <BaseInput v-model="paciente.endereco" label="Endereço" type="text" placeholder="Endereço completo" />
          </div>

          <div>
            <BaseInput v-model="paciente.ocupacao" label="Ocupação" type="text" placeholder="Profissão" />
          </div>

          <div>
            <InputEmail v-model="paciente.email" label="Email" />
          </div>

          <div>
            <InputTelefone v-model="paciente.telefone" label="Telefone" required />
          </div>

          <div class="lg:col-span-2">
            <BaseInput v-model="paciente.nome_responsavel" label="Nome do Responsável" type="text"
              placeholder="Nome completo do responsável (se menor de idade)" />
          </div>

          <div>
            <InputCPF v-model="paciente.cpf_responsavel" label="CPF do Responsável" placeholder="000.000.000-00" />
          </div>

          <div class="lg:col-span-3">
            <BaseTextarea v-model="paciente.observacoes" label="Observações" rows="3"
              placeholder="Observações gerais sobre o paciente..." />
          </div>
        </div>
      </div>

      <!-- Botões de Ação -->
      <div class="flex flex-col sm:flex-row gap-4 justify-end">
        <BaseButton v-if="!isFluxoAtendimento" type="button" variant="outline" @click="abrirModalLimpar">
          Limpar Formulário
        </BaseButton>

        <BaseButton v-if="isFluxoAtendimento" type="button" variant="outline" @click="voltarAgenda">
          Voltar
        </BaseButton>

        <BaseButton type="submit" variant="primary" :disabled="isSaving">
          <template v-if="isFluxoAtendimento">
            {{ isSaving ? 'Salvando...' : 'Salvar e continuar para ficha' }}
          </template>
          <template v-else>
            {{ isEditing ? 'Atualizar Paciente' : 'Cadastrar Paciente' }}
          </template>
        </BaseButton>
      </div>
    </form>

    <!-- Modal de Confirmação para Limpar Formulário -->
    <ActionModal :open="showModalLimpar" titulo="Limpar Formulário"
      subtitulo="Tem certeza que deseja limpar todos os campos? Esta ação não pode ser desfeita."
      action-label="Sim, Limpar" action-variant="red" border-color="danger" @acao="confirmarLimparFormulario"
      @cancel="fecharModalLimpar">
      <div class="py-4">
        <p class="text-sm text-gray-600">
          Todos os dados preenchidos serão perdidos. Deseja continuar?
        </p>
      </div>
    </ActionModal>

  </div>
</template>

<script>
import { UserIcon } from '@heroicons/vue/24/outline'
import { toast } from 'vue3-toastify'
import axios from '../../services/axios.js'
import {
  isPreCadastroCompleto,
  prepararConsultaParaAtendimento,
  urlFichaClinica,
} from '../../utils/fluxoAtendimento.js'

export default {
  name: 'CadastroPaciente',
  data() {
    return {
      UserIcon,
      isEditing: false,
      isLoadingPatient: false,
      isSaving: false,
      paciente: {
        nome: '',
        dataNascimento: '',
        rg: '',
        cpf: '',
        sexo: '',
        endereco: '',
        telefone: '',
        ocupacao: '',
        email: '',
        nome_responsavel: '',
        cpf_responsavel: '',
        observacoes: ''
      },
      showModalLimpar: false,
      formKey: 0
    }
  },
  computed: {
    patientId() {
      return this.$route.params.id
    },
    consultaId() {
      return this.$route.query.consulta_id || null
    },
    isFluxoAtendimento() {
      return this.$route.query.fluxo === 'atendimento' && Boolean(this.consultaId && this.patientId)
    },
    isRetornoAgenda() {
      return this.$route.query.retorno === 'agenda'
    },
    breadcrumbs() {
      if (this.isFluxoAtendimento) {
        return [
          { label: 'Início', to: '/home' },
          { label: 'Agenda', to: '/agenda' },
          { label: 'Pré-cadastro' },
        ]
      }
      if (this.isRetornoAgenda) {
        return [
          { label: 'Início', to: '/home' },
          { label: 'Agenda', to: '/agenda' },
          { label: 'Cadastrar paciente' },
        ]
      }
      return [
        { label: 'Início', to: '/home' },
        { label: 'Gerenciar Pacientes', to: '/pacientes/gerenciar' },
        { label: 'Cadastro de Paciente' },
      ]
    },
  },
  methods: {
    mapBackendToFrontend(data) {
      this.paciente = {
        nome: data.nome || '',
        dataNascimento: data.data_nascimento || '',
        rg: data.rg || '',
        cpf: data.cpf || '',
        sexo: data.sexo || '',
        endereco: data.endereco || '',
        telefone: data.contato || '',
        ocupacao: data.ocupacao || '',
        email: data.email || '',
        nome_responsavel: data.nome_responsavel || '',
        cpf_responsavel: data.cpf_responsavel || '',
        observacoes: data.observacoes || ''
      }
    },

    mapFrontendToBackend() {
      return {
        nome: this.paciente.nome,
        data_nascimento: this.paciente.dataNascimento,
        contato: this.paciente.telefone,
        cpf: this.paciente.cpf ? this.paciente.cpf.replace(/\D/g, '') : null,
        email: this.paciente.email || null,
        sexo: this.paciente.sexo || null,
        ocupacao: this.paciente.ocupacao || null,
        rg: this.paciente.rg || null,
        nome_responsavel: this.paciente.nome_responsavel || null,
        cpf_responsavel: this.paciente.cpf_responsavel ? this.paciente.cpf_responsavel.replace(/\D/g, '') : null,
        observacoes: this.paciente.observacoes || null,
        endereco: this.paciente.endereco || null
      }
    },

    async loadPacienteData(id) {
      this.isLoadingPatient = true
      try {
        const response = await axios.get(`/buscar-paciente/${id}`)

        if (response.data.success) {
          this.mapBackendToFrontend(response.data.data)
          this.isEditing = true
        }
      } catch (error) {
        console.error('Erro ao carregar paciente:', error)
        toast.error('Erro ao carregar dados do paciente')
        this.$router.push(this.isFluxoAtendimento ? '/agenda' : '/pacientes/gerenciar')
      } finally {
        this.isLoadingPatient = false
      }
    },

    validarFormulario() {
      const erros = []

      if (!this.paciente.nome || this.paciente.nome.trim() === '') {
        erros.push('Nome é obrigatório')
      }

      if (!this.paciente.dataNascimento) {
        erros.push('Data de nascimento é obrigatória')
      }

      if (!this.paciente.telefone || this.paciente.telefone.trim() === '') {
        erros.push('Telefone é obrigatório')
      }

      if (this.paciente.email && this.paciente.email.trim() !== '') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(this.paciente.email)) {
          erros.push('Email inválido')
        }
      }

      if (this.paciente.cpf && this.paciente.cpf.trim() !== '') {
        const cpfLimpo = this.paciente.cpf.replace(/\D/g, '')
        if (cpfLimpo.length !== 11) {
          erros.push('CPF deve ter 11 dígitos')
        }
      }

      if (this.paciente.cpf_responsavel && this.paciente.cpf_responsavel.trim() !== '') {
        const cpfResponsavelLimpo = this.paciente.cpf_responsavel.replace(/\D/g, '')
        if (cpfResponsavelLimpo.length !== 11) {
          erros.push('CPF do responsável deve ter 11 dígitos')
        }
      }

      if (this.paciente.telefone && this.paciente.telefone.trim() !== '') {
        const telefoneLimpo = this.paciente.telefone.replace(/\D/g, '')
        if (telefoneLimpo.length < 10 || telefoneLimpo.length > 11) {
          erros.push('Telefone deve ter entre 10 e 11 dígitos')
        }
      }

      return erros
    },

    async irParaFichaClinica() {
      try {
        let jaChegou = false
        let jaEmAtendimento = false

        try {
          const consultaResponse = await axios.get(`/consultas/${this.consultaId}/detalhes`)
          const data = consultaResponse.data?.data
          jaChegou = Boolean(data?.chegada_em)
          const situacaoId = data?.situacao?.id || data?.situacao_id
          jaEmAtendimento = situacaoId === 6
        } catch (_) {
          // segue com preparar
        }

        await prepararConsultaParaAtendimento(this.consultaId, { jaChegou, jaEmAtendimento })
        this.$router.push(urlFichaClinica(this.patientId, this.consultaId))
      } catch (error) {
        console.error('Erro ao preparar atendimento:', error)
        toast.error(error.response?.data?.message || 'Não foi possível iniciar o atendimento')
      }
    },

    voltarAgenda() {
      this.$router.push('/agenda')
    },

    async salvarPaciente() {
      const erros = this.validarFormulario()
      if (erros.length > 0) {
        erros.forEach(erro => toast.error(erro))
        return
      }

      if (this.isFluxoAtendimento && !isPreCadastroCompleto(this.paciente)) {
        toast.error('Complete nome, data de nascimento e telefone antes de continuar')
        return
      }

      try {
        this.isSaving = true
        const dadosParaAPI = this.mapFrontendToBackend()
        let response

        if (this.isEditing && this.patientId) {
          response = await axios.put(`/atualizar-paciente/${this.patientId}`, dadosParaAPI)

          if (response.data.success) {
            if (this.isFluxoAtendimento) {
              toast.success('Pré-cadastro confirmado')
              await this.irParaFichaClinica()
            } else {
              toast.success('Paciente atualizado com sucesso!')
              setTimeout(() => {
                this.$router.push('/pacientes/gerenciar')
              }, 1500)
            }
          } else {
            toast.error('Erro ao atualizar paciente')
          }
        } else {
          response = await axios.post('/cadastrar-paciente', dadosParaAPI)

          if (response.data.success) {
            toast.success('Paciente cadastrado com sucesso!')
            if (this.isRetornoAgenda) {
              const pacienteId = response.data.data?.id
              const q = this.$route.query || {}
              const params = new URLSearchParams({
                agendar: '1',
                paciente_id: String(pacienteId || ''),
              })
              if (q.date) params.set('date', q.date)
              if (q.time) params.set('time', q.time)
              if (q.doctorId) params.set('doctorId', q.doctorId)
              this.$router.push(`/agenda?${params.toString()}`)
            } else {
              this.$router.push('/pacientes/gerenciar')
            }
          } else {
            toast.error('Erro ao cadastrar paciente')
          }
        }
      } catch (error) {
        console.error('Erro ao salvar paciente:', error)

        if (error.response && error.response.data && error.response.data.errors) {
          const errors = error.response.data.errors
          const errorMessages = Object.values(errors).flat()
          errorMessages.forEach(msg => toast.error(msg))
        } else if (error.response && error.response.data && error.response.data.message) {
          toast.error(error.response.data.message)
        } else {
          toast.error('Erro de conexão. Tente novamente.')
        }
      } finally {
        this.isSaving = false
      }
    },

    abrirModalLimpar() {
      this.showModalLimpar = true
    },

    fecharModalLimpar() {
      this.showModalLimpar = false
    },

    async confirmarLimparFormulario() {
      this.paciente.nome = ''
      this.paciente.dataNascimento = ''
      this.paciente.rg = ''
      this.paciente.cpf = ''
      this.paciente.sexo = ''
      this.paciente.endereco = ''
      this.paciente.telefone = ''
      this.paciente.ocupacao = ''
      this.paciente.email = ''
      this.paciente.nome_responsavel = ''
      this.paciente.cpf_responsavel = ''
      this.paciente.observacoes = ''

      this.formKey++
      await this.$nextTick()

      this.showModalLimpar = false
      toast.success('Formulário limpo com sucesso!')
    }
  },
  async mounted() {
    if (this.patientId) {
      await this.loadPacienteData(this.patientId)
    }
  }
}
</script>

<style scoped>
input:focus,
select:focus,
textarea:focus {
  outline: none;
}
</style>
