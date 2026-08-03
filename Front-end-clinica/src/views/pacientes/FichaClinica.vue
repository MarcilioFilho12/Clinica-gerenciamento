<template>
  <div class="max-w-4xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Ficha Clínica" description="Preencha a ficha clínica do paciente" :icon="ClipboardDocumentIcon"
      icon-bg-color="yellow" :show-breadcrumbs="true" :breadcrumbs="breadcrumbs" class="mb-6">
      <template #actions>
        <div class="text-right">
          <p v-if="isLoading" class="text-sm text-blue-600 animate-pulse">Carregando...</p>
        </div>
      </template>
    </PageHeader>

    <form @submit.prevent="salvarFicha" class="space-y-6">
      <!-- Seleção de Paciente e Data da Consulta -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-user-check mr-2 text-[#D4AF37]">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <polyline points="16 11 18 13 22 9" />
          </svg>
          Informações da Consulta
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="md:col-span-2">
            <div class="flex gap-2">
              <div class="flex-1">
                <TypeaheadInput v-model="searchPaciente" label="Paciente" placeholder="Digite nome ou CPF do paciente..."
                  :search-function="buscarPacientes" :selected-item="pacienteSelecionado" :search-on-focus="true"
                  :get-item-label="(item) => item.nome" :get-item-subtitle="(item) => {
                    const parts = []
                    if (item.cpf) parts.push(`CPF: ${item.cpf}`)
                    if (item.contato) parts.push(`Tel: ${item.contato}`)
                    return parts.join(' • ')
                  }" :required="true" :disabled="isEditing" @select="selecionarPaciente" @clear="limparPaciente" />
              </div>
              <button v-if="!pacienteSelecionado" @click="novoPaciente" type="button"
                class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center space-x-1 text-sm font-medium mt-7"
                title="Novo Paciente">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  class="lucide lucide-user-plus">
                  <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                  <circle cx="9" cy="7" r="4" />
                  <line x1="19" x2="19" y1="8" y2="14" />
                  <line x1="22" x2="16" y1="11" y2="11" />
                </svg>
                <span class="hidden sm:inline">Novo</span>
              </button>
            </div>
          </div>

          <div>
            <InputData v-model="dataConsulta" label="Data da Consulta" :required="true" />
          </div>

          <div v-if="pacienteSelecionado" class="flex items-end">
            <div class="w-full p-3 bg-gray-50 rounded-md border border-gray-200">
              <p class="text-xs text-gray-500 mb-1">Profissional</p>
              <p class="text-sm font-medium text-gray-900">{{ profissionalNome }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Anamnese -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
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
          Anamnese
        </h2>

        <div class="space-y-4">
          <div>
            <BaseTextarea v-model="ficha.anamnese.motivoConsulta" label="Motivo da Consulta" rows="3"
              placeholder="Descreva o motivo da consulta..." />
          </div>

          <div>
            <InputData v-model="ficha.anamnese.ultimoControle" label="Último Controle Optométrico" />
          </div>

          <div>
            <BaseTextarea v-model="ficha.anamnese.antecedentesPersonais" label="Antecedentes Pessoais" rows="2"
              placeholder="Hipertensão, diabetes, cirurgias anteriores, etc." />
          </div>

          <div>
            <BaseTextarea v-model="ficha.anamnese.antecedentesFamiliares" label="Antecedentes Familiares" rows="2"
              placeholder="Histórico familiar de doenças oculares..." />
          </div>
        </div>
      </div>

      <!-- Acuidade Visual -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-eye mr-2 text-[#D4AF37]">
            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
            <circle cx="12" cy="12" r="3" />
          </svg>
          Acuidade Visual
        </h2>

        <div class="overflow-x-auto">
          <table class="w-full border-collapse border border-gray-300">
            <thead>
              <tr class="bg-gray-50">
                <th class="border border-gray-300 px-3 py-2 text-left">Olho</th>
                <th class="border border-gray-300 px-3 py-2 text-center">VL</th>
                <th class="border border-gray-300 px-3 py-2 text-center">VP</th>
                <th class="border border-gray-300 px-3 py-2 text-center">PH</th>
                <th class="border border-gray-300 px-3 py-2 text-center">Observações</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="border border-gray-300 px-3 py-2 font-medium">OD</td>
                <td class="border border-gray-300 px-2 py-1">
                  <BaseInput v-model="ficha.acuidadeVisual.od.vl" type="text" label="" placeholder="20/20" />
                </td>
                <td class="border border-gray-300 px-2 py-1">
                  <BaseInput v-model="ficha.acuidadeVisual.od.vp" type="text" label="" placeholder="J1" />
                </td>
                <td class="border border-gray-300 px-2 py-1">
                  <BaseInput v-model="ficha.acuidadeVisual.od.ph" type="text" label="" placeholder="20/20" />
                </td>
                <td class="border border-gray-300 px-2 py-1">
                  <BaseInput v-model="ficha.acuidadeVisual.od.observacoes" type="text" label="" />
                </td>
              </tr>
              <tr>
                <td class="border border-gray-300 px-3 py-2 font-medium">OE</td>
                <td class="border border-gray-300 px-2 py-1">
                  <BaseInput v-model="ficha.acuidadeVisual.oe.vl" type="text" label="" placeholder="20/20" />
                </td>
                <td class="border border-gray-300 px-2 py-1">
                  <BaseInput v-model="ficha.acuidadeVisual.oe.vp" type="text" label="" placeholder="J1" />
                </td>
                <td class="border border-gray-300 px-2 py-1">
                  <BaseInput v-model="ficha.acuidadeVisual.oe.ph" type="text" label="" placeholder="20/20" />
                </td>
                <td class="border border-gray-300 px-2 py-1">
                  <BaseInput v-model="ficha.acuidadeVisual.oe.observacoes" type="text" label="" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Refração -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-glasses mr-2 text-[#D4AF37]">
            <circle cx="6" cy="15" r="4" />
            <circle cx="18" cy="15" r="4" />
            <path d="M14 15a2 2 0 0 0-2-2 2 2 0 0 0-2 2" />
            <path d="M2.5 13 5 7c.7-1.3 1.4-2 3-2" />
            <path d="M21.5 13 19 7c-.7-1.3-1.5-2-3-2" />
          </svg>
          Refração
        </h2>

        <div class="space-y-4">
          <p class="text-xs text-gray-500">ESF −30,00 a +30,00 (0,25) · CIL 0 a −15,00 (0,25) · Eixo 0° a 180° · ADD +0,25 a +10,00</p>
          <h3 class="text-lg font-medium text-gray-800">Autorrefração</h3>
          <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300">
              <thead>
                <tr class="bg-gray-50">
                  <th class="border border-gray-300 px-3 py-2 text-left">Olho</th>
                  <th class="border border-gray-300 px-3 py-2 text-center">ESF</th>
                  <th class="border border-gray-300 px-3 py-2 text-center">CIL</th>
                  <th class="border border-gray-300 px-3 py-2 text-center">EIXO</th>
                  <th class="border border-gray-300 px-3 py-2 text-center">ADD</th>
                  <th class="border border-gray-300 px-3 py-2 text-center">AV</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="border border-gray-300 px-3 py-2 font-medium">OD</td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.autorrefacao.od.esf" label="" :options="esfOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.autorrefacao.od.cil" label="" :options="cilOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.autorrefacao.od.eixo" label="" :options="eixoOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.autorrefacao.od.add" label="" :options="addOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseInput v-model="ficha.refracao.autorrefacao.od.av" type="text" label="" placeholder="20/20" /></td>
                </tr>
                <tr>
                  <td class="border border-gray-300 px-3 py-2 font-medium">OE</td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.autorrefacao.oe.esf" label="" :options="esfOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.autorrefacao.oe.cil" label="" :options="cilOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.autorrefacao.oe.eixo" label="" :options="eixoOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.autorrefacao.oe.add" label="" :options="addOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseInput v-model="ficha.refracao.autorrefacao.oe.av" type="text" label="" placeholder="20/20" /></td>
                </tr>
              </tbody>
            </table>
          </div>

          <h3 class="text-lg font-medium text-gray-800 mt-6">Refração Subjetiva</h3>
          <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300">
              <thead>
                <tr class="bg-gray-50">
                  <th class="border border-gray-300 px-3 py-2 text-left">Olho</th>
                  <th class="border border-gray-300 px-3 py-2 text-center">ESF</th>
                  <th class="border border-gray-300 px-3 py-2 text-center">CIL</th>
                  <th class="border border-gray-300 px-3 py-2 text-center">EIXO</th>
                  <th class="border border-gray-300 px-3 py-2 text-center">ADD</th>
                  <th class="border border-gray-300 px-3 py-2 text-center">AV</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="border border-gray-300 px-3 py-2 font-medium">OD</td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.subjetiva.od.esf" label="" :options="esfOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.subjetiva.od.cil" label="" :options="cilOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.subjetiva.od.eixo" label="" :options="eixoOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.subjetiva.od.add" label="" :options="addOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseInput v-model="ficha.refracao.subjetiva.od.av" type="text" label="" placeholder="20/20" /></td>
                </tr>
                <tr>
                  <td class="border border-gray-300 px-3 py-2 font-medium">OE</td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.subjetiva.oe.esf" label="" :options="esfOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.subjetiva.oe.cil" label="" :options="cilOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.subjetiva.oe.eixo" label="" :options="eixoOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseSelect v-model="ficha.refracao.subjetiva.oe.add" label="" :options="addOptions" /></td>
                  <td class="border border-gray-300 px-2 py-1"><BaseInput v-model="ficha.refracao.subjetiva.oe.av" type="text" label="" placeholder="20/20" /></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Biomicroscopia -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-microscope mr-2 text-[#D4AF37]">
            <path d="M6 18h8" />
            <path d="M3 22h18" />
            <path d="M14 22a7 7 0 1 0 0-14h-1" />
            <path d="M9 14h2" />
            <path d="M9 12a2 2 0 0 1-2-2V6h6v4a2 2 0 0 1-2 2Z" />
            <path d="M12 6V3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3" />
          </svg>
          Biomicroscopia
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-lg font-medium text-gray-800 mb-3">Olho Direito (OD)</h3>
            <div class="space-y-3">
              <div>
                <BaseInput v-model="ficha.biomicroscopia.od.cornea" label="Córnea" type="text" />
              </div>
              <div>
                <BaseInput v-model="ficha.biomicroscopia.od.iris" label="Íris" type="text" />
              </div>
              <div>
                <BaseInput v-model="ficha.biomicroscopia.od.conjuntiva" label="Conjuntiva" type="text" />
              </div>
              <div>
                <BaseInput v-model="ficha.biomicroscopia.od.cristalino" label="Cristalino" type="text" />
              </div>
              <div>
                <BaseInput v-model="ficha.biomicroscopia.od.pupilas" label="Pupilas" type="text" />
              </div>
            </div>
          </div>

          <div>
            <h3 class="text-lg font-medium text-gray-800 mb-3">Olho Esquerdo (OE)</h3>
            <div class="space-y-3">
              <div>
                <BaseInput v-model="ficha.biomicroscopia.oe.cornea" label="Córnea" type="text" />
              </div>
              <div>
                <BaseInput v-model="ficha.biomicroscopia.oe.iris" label="Íris" type="text" />
              </div>
              <div>
                <BaseInput v-model="ficha.biomicroscopia.oe.conjuntiva" label="Conjuntiva" type="text" />
              </div>
              <div>
                <BaseInput v-model="ficha.biomicroscopia.oe.cristalino" label="Cristalino" type="text" />
              </div>
              <div>
                <BaseInput v-model="ficha.biomicroscopia.oe.pupilas" label="Pupilas" type="text" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Prescrição -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-file-text mr-2 text-[#D4AF37]">
            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
            <path d="M10 9H8" />
            <path d="M16 13H8" />
            <path d="M16 17H8" />
          </svg>
          Prescrição e Conduta
        </h2>

        <div class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <BaseSelect v-model="ficha.prescricao.material" label="Material" :options="[
                { value: '', label: 'Selecione' },
                { value: 'CR-39', label: 'CR-39' },
                { value: 'Policarbonato', label: 'Policarbonato' },
                { value: 'Trivex', label: 'Trivex' },
                { value: 'Alto Índice', label: 'Alto Índice' }
              ]" />
            </div>

            <div>
              <BaseSelect v-model="ficha.prescricao.tipoLente" label="Tipo de Lente" :options="[
                { value: '', label: 'Selecione' },
                { value: 'Monofocal', label: 'Monofocal' },
                { value: 'Bifocal', label: 'Bifocal' },
                { value: 'Multifocal', label: 'Multifocal' },
                { value: 'Progressiva', label: 'Progressiva' }
              ]" />
            </div>

            <div>
              <BaseSelect v-model="ficha.prescricao.filtro" label="Filtro" :options="[
                { value: '', label: 'Selecione' },
                { value: 'Antirreflexo', label: 'Antirreflexo' },
                { value: 'Fotossensível', label: 'Fotossensível' },
                { value: 'Blue Light', label: 'Blue Light' },
                { value: 'UV', label: 'UV' }
              ]" />
            </div>
          </div>

          <div>
            <BaseTextarea v-model="ficha.prescricao.diagnostico" label="Diagnóstico" rows="3"
              placeholder="Diagnóstico oftalmológico..." />
          </div>

          <div>
            <BaseTextarea v-model="ficha.prescricao.conduta" label="Conduta" rows="3"
              placeholder="Conduta e recomendações..." />
          </div>

          <div>
            <InputData v-model="ficha.prescricao.proximoControle" label="Próxima Consulta" />
          </div>

          <div>
            <BaseTextarea v-model="ficha.prescricao.encaminhamento" label="Encaminhamento" rows="3"
              placeholder="Encaminhamentos para impressão (especialista, exames, etc.)..." />
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-message-square mr-2 text-[#D4AF37]">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
          </svg>
          Observações Gerais
        </h2>

        <BaseTextarea v-model="ficha.observacoesGerais" label="" rows="4"
          placeholder="Observações adicionais sobre o exame..." />
      </div>

      <div class="flex flex-col sm:flex-row gap-4 justify-end">
        <BaseButton type="button" variant="outline" @click="abrirModalLimpar">
          Limpar Formulário
        </BaseButton>

        <BaseButton type="button" variant="warning" @click="imprimirFicha">
          Imprimir Ficha
        </BaseButton>

        <BaseButton type="button" variant="outline" @click="imprimirRefracaoSubjetivaA5">
          Imprimir Refração Subjetiva (A5)
        </BaseButton>

        <BaseButton type="submit" variant="primary">
          {{ isEditing ? 'Atualizar Ficha' : 'Salvar Ficha' }}
        </BaseButton>
      </div>
    </form>

    <ActionModal :open="showModalLimpar" titulo="Limpar Formulário"
      subtitulo="Tem certeza que deseja limpar todos os campos do formulário? Esta ação não pode ser desfeita."
      action-label="Limpar" action-variant="red" border-color="danger" @acao="confirmarLimparFormulario"
      @cancel="fecharModalLimpar" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ClipboardDocumentIcon } from '@heroicons/vue/24/outline'
import { toast } from 'vue3-toastify'
import { useAuthStore } from '../../stores/auth.js'
import axios from '../../services/axios.js'
import TypeaheadInput from '../../components/ui/TypeaheadInput.vue'
import { esfOptions, cilOptions, eixoOptions, addOptions } from '../../utils/refracaoOptions.js'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const isLoading = ref(false)
const isEditing = ref(false)
const pacienteSelecionado = ref(null)
const searchPaciente = ref('')
const dataConsulta = ref(new Date().toISOString().split('T')[0])
const showModalLimpar = ref(false)
const fichaClinicaId = computed(() => route.params.idFichaClinica || route.params.fichaClinicaId)
const pacienteId = computed(() => route.params.idPaciente || route.params.id)
const consultaId = ref(null) // ID da consulta vinculada (opcional)

const ficha = ref({
  anamnese: {
    motivoConsulta: '',
    ultimoControle: '',
    antecedentesPersonais: '',
    antecedentesFamiliares: ''
  },
  acuidadeVisual: {
    od: { vl: '', vp: '', ph: '', observacoes: '' },
    oe: { vl: '', vp: '', ph: '', observacoes: '' }
  },
  refracao: {
    autorrefacao: {
      od: { esf: '', cil: '', eixo: '', add: '', av: '' },
      oe: { esf: '', cil: '', eixo: '', add: '', av: '' }
    },
    subjetiva: {
      od: { esf: '', cil: '', eixo: '', add: '', av: '' },
      oe: { esf: '', cil: '', eixo: '', add: '', av: '' }
    }
  },
  biomicroscopia: {
    od: { cornea: '', iris: '', conjuntiva: '', cristalino: '', pupilas: '' },
    oe: { cornea: '', iris: '', conjuntiva: '', cristalino: '', pupilas: '' }
  },
  prescricao: {
    material: '',
    tipoLente: '',
    filtro: '',
    diagnostico: '',
    conduta: '',
    encaminhamento: '',
    proximoControle: ''
  },
  observacoesGerais: ''
})

const profissionalNome = computed(() => {
  return authStore.user?.name || 'Usuário logado'
})

const userId = computed(() => {
  return authStore.user?.id
})

const breadcrumbs = computed(() => {
  const items = [
    { label: 'Início', to: '/home' },
    { label: 'Gerenciar Pacientes', to: '/pacientes/gerenciar' }
  ]

  const currentPacienteId = pacienteId.value || pacienteSelecionado.value?.id
  if (currentPacienteId) {
    items.push({
      label: 'Detalhes do Paciente',
      to: `/pacientes/detalhes/${currentPacienteId}`
    })
  }

  items.push({ label: 'Ficha Clínica' })
  return items
})

const buscarPacientes = async (termo) => {
  try {
    const params = { limit: 20 }
    if (termo && String(termo).trim() !== '') {
      params.search = String(termo).trim()
    }
    const response = await axios.get('/listar-pacientes', { params })

    if (response.data.success) {
      return response.data.data || []
    }
    return []
  } catch (err) {
    console.error('Erro ao buscar pacientes:', err)
    return []
  }
}

const selecionarPaciente = (paciente) => {
  pacienteSelecionado.value = paciente
  searchPaciente.value = paciente.nome
}

const limparPaciente = () => {
  pacienteSelecionado.value = null
  searchPaciente.value = ''
}

const novoPaciente = () => {
  router.push('/pacientes/cadastro')
}

const mapBackendToFrontend = (data) => {
  if (data.anamnese) {
    ficha.value.anamnese = {
      motivoConsulta: data.anamnese.motivo_consulta || '',
      ultimoControle: data.anamnese.ultimo_controle || '',
      antecedentesPersonais: data.anamnese.antecedentes_pessoais || '',
      antecedentesFamiliares: data.anamnese.antecedentes_familiares || ''
    }
  }

  if (data.acuidades_visuais && data.acuidades_visuais.length > 0) {
    data.acuidades_visuais.forEach(acuidade => {
      const olho = acuidade.olho.toLowerCase()
      if (ficha.value.acuidadeVisual[olho]) {
        ficha.value.acuidadeVisual[olho] = {
          vl: acuidade.vl || '',
          vp: acuidade.vp || '',
          ph: acuidade.ph || '',
          observacoes: acuidade.observacoes || ''
        }
      }
    })
  }

  if (data.refracoes && data.refracoes.length > 0) {
    data.refracoes.forEach(refracao => {
      const tipo = refracao.tipo.toLowerCase()
      const olho = refracao.olho.toLowerCase()

      if (ficha.value.refracao[tipo] && ficha.value.refracao[tipo][olho]) {
        ficha.value.refracao[tipo][olho] = {
          esf: refracao.esf || '',
          cil: refracao.cil || '',
          eixo: refracao.eixo || '',
          add: refracao.add || '',
          av: refracao.av || ''
        }
      }
    })
  }

  if (data.biomicroscopias && data.biomicroscopias.length > 0) {
    data.biomicroscopias.forEach(bio => {
      const olho = bio.olho.toLowerCase()
      if (ficha.value.biomicroscopia[olho]) {
        ficha.value.biomicroscopia[olho] = {
          cornea: bio.cornea || '',
          iris: bio.iris || '',
          conjuntiva: bio.conjuntiva || '',
          cristalino: bio.cristalino || '',
          pupilas: bio.pupilas || ''
        }
      }
    })
  }

  if (data.prescricao) {
    ficha.value.prescricao = {
      material: data.prescricao.material || '',
      tipoLente: data.prescricao.tipo_lente || '',
      filtro: data.prescricao.filtro || '',
      diagnostico: data.prescricao.diagnostico || '',
      conduta: data.prescricao.conduta || '',
      encaminhamento: data.prescricao.encaminhamento || '',
      proximoControle: data.prescricao.proximo_controle || ''
    }
  }

  ficha.value.observacoesGerais = data.observacoes || ''
}

const mapFrontendToBackend = () => {
  const payload = {
    user_id: userId.value,
    data_consulta: dataConsulta.value,
    observacoes: ficha.value.observacoesGerais || null
  }

  if (ficha.value.anamnese.motivoConsulta || ficha.value.anamnese.ultimoControle) {
    payload.anamnese = {
      motivo_consulta: ficha.value.anamnese.motivoConsulta,
      ultimo_controle: ficha.value.anamnese.ultimoControle,
      antecedentes_pessoais: ficha.value.anamnese.antecedentesPersonais,
      antecedentes_familiares: ficha.value.anamnese.antecedentesFamiliares
    }
  }

  const acuidadesVisuais = []
  if (ficha.value.acuidadeVisual.od.vl || ficha.value.acuidadeVisual.od.vp) {
    acuidadesVisuais.push({
      olho: 'od',
      vl: ficha.value.acuidadeVisual.od.vl,
      vp: ficha.value.acuidadeVisual.od.vp,
      ph: ficha.value.acuidadeVisual.od.ph,
      observacoes: ficha.value.acuidadeVisual.od.observacoes
    })
  }
  if (ficha.value.acuidadeVisual.oe.vl || ficha.value.acuidadeVisual.oe.vp) {
    acuidadesVisuais.push({
      olho: 'oe',
      vl: ficha.value.acuidadeVisual.oe.vl,
      vp: ficha.value.acuidadeVisual.oe.vp,
      ph: ficha.value.acuidadeVisual.oe.ph,
      observacoes: ficha.value.acuidadeVisual.oe.observacoes
    })
  }
  if (acuidadesVisuais.length > 0) {
    payload.acuidades_visuais = acuidadesVisuais
  }

  const refracoes = []
  Object.keys(ficha.value.refracao).forEach(tipo => {
    ['od', 'oe'].forEach(olho => {
      const ref = ficha.value.refracao[tipo][olho]
      if (ref.esf || ref.cil || ref.av) {
        refracoes.push({
          tipo,
          olho,
          esf: ref.esf,
          cil: ref.cil,
          eixo: ref.eixo,
          add: ref.add,
          av: ref.av
        })
      }
    })
  })
  if (refracoes.length > 0) {
    payload.refracoes = refracoes
  }

  const biomicroscopias = []
  if (ficha.value.biomicroscopia.od.cornea || ficha.value.biomicroscopia.od.iris) {
    biomicroscopias.push({
      olho: 'od',
      cornea: ficha.value.biomicroscopia.od.cornea,
      iris: ficha.value.biomicroscopia.od.iris,
      conjuntiva: ficha.value.biomicroscopia.od.conjuntiva,
      cristalino: ficha.value.biomicroscopia.od.cristalino,
      pupilas: ficha.value.biomicroscopia.od.pupilas
    })
  }
  if (ficha.value.biomicroscopia.oe.cornea || ficha.value.biomicroscopia.oe.iris) {
    biomicroscopias.push({
      olho: 'oe',
      cornea: ficha.value.biomicroscopia.oe.cornea,
      iris: ficha.value.biomicroscopia.oe.iris,
      conjuntiva: ficha.value.biomicroscopia.oe.conjuntiva,
      cristalino: ficha.value.biomicroscopia.oe.cristalino,
      pupilas: ficha.value.biomicroscopia.oe.pupilas
    })
  }
  if (biomicroscopias.length > 0) {
    payload.biomicroscopias = biomicroscopias
  }

  if (ficha.value.prescricao.material || ficha.value.prescricao.diagnostico || ficha.value.prescricao.encaminhamento || ficha.value.prescricao.proximoControle) {
    payload.prescricao = {
      material: ficha.value.prescricao.material,
      tipo_lente: ficha.value.prescricao.tipoLente,
      filtro: ficha.value.prescricao.filtro,
      diagnostico: ficha.value.prescricao.diagnostico,
      conduta: ficha.value.prescricao.conduta,
      encaminhamento: ficha.value.prescricao.encaminhamento,
      proximo_controle: ficha.value.prescricao.proximoControle
    }
  }

  // Adicionar consulta_id se fornecido
  if (consultaId.value) {
    payload.consulta_id = consultaId.value
  }

  return payload
}

const loadFichaClinicaData = async (id) => {
  isLoading.value = true
  try {
    const response = await axios.get(`/fichas-clinicas/${id}`)

    if (response.data.success) {
      const data = response.data.data

      if (data.cadastro) {
        pacienteSelecionado.value = {
          id: data.cadastro.id,
          nome: data.cadastro.nome,
          cpf: data.cadastro.cpf,
          contato: data.cadastro.contato
        }
        searchPaciente.value = data.cadastro.nome
      }

      if (data.data_consulta) {
        dataConsulta.value = data.data_consulta
      }

      mapBackendToFrontend(data)

      isEditing.value = true
    }
  } catch (error) {
    console.error('Erro ao carregar ficha clínica:', error)
    toast.error('Erro ao carregar dados da ficha clínica')
    router.push('/pacientes/gerenciar')
  } finally {
    isLoading.value = false
  }
}

const loadPacienteData = async (id) => {
  isLoading.value = true
  try {
    const response = await axios.get(`/buscar-paciente/${id}`)

    if (response.data.success) {
      pacienteSelecionado.value = {
        id: response.data.data.id,
        nome: response.data.data.nome,
        cpf: response.data.data.cpf,
        contato: response.data.data.contato
      }
      searchPaciente.value = response.data.data.nome
    }
  } catch (error) {
    console.error('Erro ao carregar paciente:', error)
    toast.error('Erro ao carregar dados do paciente')
  } finally {
    isLoading.value = false
  }
}

const salvarFicha = async () => {
  const erros = []

  if (!pacienteSelecionado.value) {
    erros.push('Selecione um paciente')
  }

  if (!dataConsulta.value) {
    erros.push('Data da consulta é obrigatória')
  }

  if (!userId.value) {
    erros.push('Usuário não autenticado')
  }

  if (!ficha.value.anamnese.motivoConsulta?.trim()) {
    erros.push('Motivo da consulta é obrigatório')
  }

  const subOd = ficha.value.refracao.subjetiva.od
  const subOe = ficha.value.refracao.subjetiva.oe
  const temSubjetiva = subOd.esf || subOd.cil || subOe.esf || subOe.cil
  if (!temSubjetiva) {
    erros.push('Informe ao menos a refração subjetiva (ESF ou CIL) de um dos olhos')
  }

  if (erros.length > 0) {
    erros.forEach(erro => toast.error(erro))
    return
  }

  try {
    // Validação: Se consulta_id foi fornecido, verificar se a consulta existe e está em atendimento
    if (consultaId.value) {
      try {
        const consultaResponse = await axios.get(`/consultas/${consultaId.value}/detalhes`)
        
        if (!consultaResponse.data.success || !consultaResponse.data.data) {
          toast.warning('Consulta não encontrada. A ficha será criada sem vínculo.')
          consultaId.value = null
        } else {
          const consultaData = consultaResponse.data.data
          const statusId = consultaData.situacao?.id || consultaData.status_id
          
          // Se não estiver em atendimento, apenas criar ficha sem encerrar
          if (statusId !== 6) {
            // Ainda permite criar a ficha vinculada, mas o backend não encerrará automaticamente
            // O backend já tem essa lógica implementada
          }
        }
      } catch (consultaError) {
        console.error('Erro ao validar consulta:', consultaError)
        if (consultaError.response?.status === 404) {
          toast.warning('Consulta não encontrada. A ficha será criada sem vínculo.')
        } else {
          toast.warning('Erro ao validar consulta. A ficha será criada sem vínculo.')
        }
        consultaId.value = null
      }
    }

    const dadosParaAPI = mapFrontendToBackend()

    let response

    if (isEditing.value && fichaClinicaId.value) {
      response = await axios.put(`/fichas-clinicas/${fichaClinicaId.value}`, dadosParaAPI)

      if (response.data.success) {
        toast.success('Ficha clínica atualizada com sucesso!')
        router.push(`/pacientes/detalhes/${pacienteSelecionado.value.id}`)
      } else {
        toast.error('Erro ao atualizar ficha clínica')
      }
    } else {
      response = await axios.post(`/pacientes/${pacienteSelecionado.value.id}/fichas-clinicas`, dadosParaAPI)

      if (response.data.success) {
        // Se consulta_id foi fornecido e a consulta foi encerrada
        if (consultaId.value && response.data.consulta_encerrada) {
          toast.success('Ficha clínica criada com sucesso e consulta encerrada automaticamente!')
        } else if (consultaId.value) {
          toast.success('Ficha clínica criada com sucesso!')
        } else {
          toast.success('Ficha clínica criada com sucesso!')
        }
        
        // Se veio de uma consulta, voltar para detalhes da consulta, senão para detalhes do paciente
        if (consultaId.value) {
          router.push(`/pacientes/detalhes/${pacienteSelecionado.value.id}/consultas/${consultaId.value}`)
        } else {
          router.push(`/pacientes/detalhes/${pacienteSelecionado.value.id}`)
        }
      } else {
        toast.error('Erro ao criar ficha clínica')
      }
    }

  } catch (error) {
    console.error('Erro ao salvar ficha:', error)

    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      const errorMessages = Object.values(errors).flat()
      errorMessages.forEach(msg => toast.error(msg))
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Erro de conexão. Tente novamente.')
    }
  }
}

const abrirModalLimpar = () => {
  showModalLimpar.value = true
}

const fecharModalLimpar = () => {
  showModalLimpar.value = false
}

const confirmarLimparFormulario = () => {
  // Reset do objeto reativo
  Object.keys(ficha.value).forEach(key => {
    if (typeof ficha.value[key] === 'object' && ficha.value[key] !== null) {
      Object.keys(ficha.value[key]).forEach(subKey => {
        if (typeof ficha.value[key][subKey] === 'object') {
          Object.keys(ficha.value[key][subKey]).forEach(subSubKey => {
            ficha.value[key][subKey][subSubKey] = ''
          })
        } else {
          ficha.value[key][subKey] = ''
        }
      })
    } else {
      ficha.value[key] = ''
    }
  })

  if (!isEditing.value) {
    pacienteSelecionado.value = null
    searchPaciente.value = ''
    dataConsulta.value = new Date().toISOString().split('T')[0]
  }

  fecharModalLimpar()
  toast.success('Formulário limpo com sucesso!')
}

const imprimirFicha = () => {
  // Só permite imprimir se a ficha já foi salva (tem ID)
  if (fichaClinicaId.value) {
    const url = `/imprimir-ficha-clinica/${fichaClinicaId.value}`
    window.open(url, '_blank')
  } else {
    toast.warning('Você deve salvar a ficha clínica para poder imprimir')
  }
}

const imprimirRefracaoSubjetivaA5 = () => {
  if (fichaClinicaId.value) {
    const url = `/imprimir-refracao-subjetiva-a5/${fichaClinicaId.value}`
    window.open(url, '_blank')
  } else {
    toast.warning('Você deve salvar a ficha clínica para imprimir a refração subjetiva')
  }
}

// Carregar dados da consulta se consulta_id for fornecido
const loadConsultaData = async (id) => {
  try {
    const response = await axios.get(`/consultas/${id}/detalhes`)

    if (response.data.success) {
      const consultaData = response.data.data
      
      // Validação: Verificar se a consulta existe
      if (!consultaData || !consultaData.id) {
        toast.warning('Consulta não encontrada. A ficha será criada sem vínculo.')
        consultaId.value = null
        return
      }

      // Validação: Verificar se a consulta está em atendimento
      const statusId = consultaData.situacao?.id || consultaData.status_id
      if (statusId !== 6) {
        // Se não estiver em atendimento, apenas criar ficha sem encerrar
        toast.info('A consulta não está em atendimento. A ficha será criada sem encerrar a consulta.')
        // Ainda permite criar a ficha vinculada, mas o backend não encerrará
      }
      
      // Pré-preencher dados do paciente se não estiver selecionado
      if (!pacienteSelecionado.value && consultaData.paciente) {
        pacienteSelecionado.value = {
          id: consultaData.paciente.id,
          nome: consultaData.paciente.nome,
          cpf: consultaData.paciente.cpf,
          contato: consultaData.paciente.contato
        }
        searchPaciente.value = consultaData.paciente.nome
      }

      // Pré-preencher data da consulta se não estiver definida
      if (consultaData.data && !dataConsulta.value) {
        dataConsulta.value = consultaData.data
      }

      // Armazenar consulta_id
      consultaId.value = id
    } else {
      toast.warning('Consulta não encontrada. A ficha será criada sem vínculo.')
      consultaId.value = null
    }
  } catch (error) {
    console.error('Erro ao carregar dados da consulta:', error)
    
    if (error.response?.status === 404) {
      toast.warning('Consulta não encontrada. A ficha será criada sem vínculo.')
    } else {
      toast.error('Erro ao carregar dados da consulta. A ficha será criada sem vínculo.')
    }
    consultaId.value = null
  }
}

// Lifecycle
onMounted(async () => {
  // Verificar se tem consulta_id na query string
  const consultaIdFromQuery = route.query.consulta_id
  if (consultaIdFromQuery) {
    consultaId.value = consultaIdFromQuery
    await loadConsultaData(consultaIdFromQuery)
  }

  // Verificar se está em modo de edição (tem fichaClinicaId na rota)
  if (fichaClinicaId.value) {
    await loadFichaClinicaData(fichaClinicaId.value)
  } else if (pacienteId.value) {
    // Se tem pacienteId na rota, carregar paciente
    await loadPacienteData(pacienteId.value)
  }
})
</script>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }

  .container {
    max-width: none !important;
    padding: 0 !important;
  }

  .shadow-sm {
    box-shadow: none !important;
  }

  .bg-gray-50 {
    background-color: white !important;
  }
}

.container {
  max-width: 1200px;
}

/* Estilos para melhor experiência do usuário */
input:focus,
select:focus,
textarea:focus {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(212, 175, 55, 0.15);
}

.transition-all {
  transition: all 0.2s ease;
}

/* Responsividade para tabelas */
@media (max-width: 768px) {
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
  }

  table {
    min-width: 600px;
  }
}
</style>
