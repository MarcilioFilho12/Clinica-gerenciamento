<template>
  <div class="min-h-screen bg-white p-8 font-[Arial,sans-serif] print:p-[1cm_0.75cm] print:m-0 print:max-w-full print:bg-white max-w-[1200px] mx-auto">
    <div v-if="isLoading" class="flex flex-col items-center justify-center min-h-[50vh] text-center no-print">
      <div class="w-10 h-10 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin mb-4"></div>
      <h3>Carregando ficha clínica...</h3>
      <p>Aguarde enquanto buscamos as informações</p>
    </div>

    <div v-else-if="error" class="p-8 text-center text-red-600 no-print">
      <h3>Erro ao carregar ficha clínica</h3>
      <p>{{ error }}</p>
    </div>

    <div v-else-if="fichaClinica">
      <div class="mb-4 pb-2 border-b-2 border-gray-700 print:break-after-avoid print:mb-3 print:pb-2">
        <div class="text-center mb-3">
          <span class="text-[2.0rem] font-bold text-gray-900 mb-1 print:text-[1.50rem] print:mb-1">FICHA CLÍNICA</span>
        </div>
        <div class="grid grid-cols-2 gap-2 text-sm print:text-xs print:gap-2">
          <div class="flex flex-row gap-2">
            <span class="font-bold text-gray-900">Paciente:</span>
            <span class="text-gray-700 print:text-gray-900">{{ fichaClinica.cadastro?.nome || 'Não informado' }}</span>
          </div>
          <div class="flex flex-row gap-2">
            <span class="font-bold text-gray-900">Data da Consulta:</span>
            <span class="text-gray-700 print:text-gray-900">{{ formatDate(fichaClinica.data_consulta) || 'Não informado' }}</span>
          </div>
          <div v-if="fichaClinica.cadastro?.cpf" class="flex flex-row gap-2">
            <span class="font-bold text-gray-900">CPF:</span>
            <span class="text-gray-700 print:text-gray-900">{{ formatCPF(fichaClinica.cadastro.cpf) }}</span>
          </div>
          <div class="flex flex-row gap-2">
            <span class="font-bold text-gray-900">Profissional:</span>
            <span class="text-gray-700 print:text-gray-900">{{ fichaClinica.user?.name || 'Não informado' }}</span>
          </div>
        </div>
      </div>

      <div class="mb-3 print:break-inside-avoid print:mb-2 print:p-2 print:border print:border-gray-300 print:bg-white">
        <h2 class="text-base font-bold text-gray-900 mb-2 pb-1 border-b border-gray-300 print:break-after-avoid print:mt-0 print:mb-2 print:text-[0.9375rem] print:pb-1">Informações da Consulta</h2>
        <div class="py-1 print:py-[0.125rem]">
          <div class="grid grid-cols-2 gap-x-2 gap-y-1 print:gap-x-1.5 print:gap-y-0.5">
            <div class="flex items-baseline gap-1.5 mb-0.5 print:mb-0.5">
              <span class="text-[0.6875rem] font-semibold text-gray-500 whitespace-nowrap print:text-[0.625rem]">Paciente:</span>
              <span class="text-xs text-gray-900 font-medium print:text-[0.6875rem]">{{ fichaClinica.cadastro?.nome || 'Não informado' }}</span>
              <span v-if="fichaClinica.cadastro?.cpf" class="text-[0.6875rem] text-gray-500 font-normal print:text-[0.625rem]">
                (CPF: {{ formatCPF(fichaClinica.cadastro.cpf) }})
              </span>
            </div>
            <div class="flex items-baseline gap-1.5 mb-0.5 print:mb-0.5">
              <span class="text-[0.6875rem] font-semibold text-gray-500 whitespace-nowrap print:text-[0.625rem]">Data da Consulta:</span>
              <span class="text-xs text-gray-900 font-medium print:text-[0.6875rem]">{{ formatDate(fichaClinica.data_consulta) || 'Não informado' }}</span>
            </div>
            <div class="flex items-baseline gap-1.5 mb-0.5 print:mb-0.5">
              <span class="text-[0.6875rem] font-semibold text-gray-500 whitespace-nowrap print:text-[0.625rem]">Profissional:</span>
              <span class="text-xs text-gray-900 font-medium print:text-[0.6875rem]">{{ fichaClinica.user?.name || 'Não informado' }}</span>
              <span v-if="fichaClinica.user?.email" class="text-[0.6875rem] text-gray-500 font-normal print:text-[0.625rem]">
                ({{ fichaClinica.user.email }})
              </span>
            </div>
            <div class="flex items-baseline gap-1.5 mb-0.5 print:mb-0.5">
              <span class="text-[0.6875rem] font-semibold text-gray-500 whitespace-nowrap print:text-[0.625rem]">Data de Criação:</span>
              <span class="text-xs text-gray-900 font-medium print:text-[0.6875rem]">{{ formatDateTime(fichaClinica.created_at) || 'Não informado' }}</span>
            </div>
            <div v-if="fichaClinica.observacoes" class="flex flex-col gap-1 col-span-full mb-0.5 print:mb-0.5">
              <span class="text-[0.6875rem] font-semibold text-gray-500 whitespace-nowrap print:text-[0.625rem]">Observações Gerais:</span>
              <span class="text-xs text-gray-700 whitespace-pre-wrap break-words leading-[1.3] p-1 bg-gray-50 border border-gray-200 rounded-sm print:text-[0.6875rem] print:p-1 print:leading-[1.25]">{{ fichaClinica.observacoes }}</span>
            </div>
          </div>
        </div>
      </div>

      <div v-if="fichaClinica.anamnese" class="mb-3 print:break-inside-avoid print:mb-2 print:p-2 print:border print:border-gray-300 print:bg-white">
        <h2 class="text-base font-bold text-gray-900 mb-2 pb-1 border-b border-gray-300 print:break-after-avoid print:mt-0 print:mb-2 print:text-[0.9375rem] print:pb-1">Anamnese</h2>
        <div class="py-1 print:py-[0.125rem]">
          <div class="grid grid-cols-2 gap-x-2 gap-y-1 print:gap-x-1.5 print:gap-y-0.5">
            <div v-if="fichaClinica.anamnese.motivo_consulta" class="flex flex-row items-start gap-1.5 col-span-full mb-0.5 print:mb-0.5">
              <span class="text-[0.6875rem] font-semibold text-gray-500 whitespace-nowrap print:text-[0.625rem]">Motivo da Consulta:</span>
              <span class="text-xs text-gray-700 whitespace-pre-wrap break-words leading-[1.3] p-1 bg-gray-50 border border-gray-200 rounded-sm flex-1 print:text-[0.6875rem] print:p-1 print:leading-[1.25]">{{ fichaClinica.anamnese.motivo_consulta }}</span>
            </div>

            <div v-if="fichaClinica.anamnese.ultimo_controle" class="flex items-baseline gap-1.5 mb-0.5 print:mb-0.5">
              <span class="text-[0.6875rem] font-semibold text-gray-500 whitespace-nowrap print:text-[0.625rem]">Último Controle Optométrico:</span>
              <span class="text-xs text-gray-900 font-medium print:text-[0.6875rem]">{{ formatDate(fichaClinica.anamnese.ultimo_controle) }}</span>
            </div>

            <div v-if="fichaClinica.anamnese.antecedentes_pessoais" class="flex flex-row items-start gap-1.5 col-span-full mb-0.5 print:mb-0.5">
              <span class="text-[0.6875rem] font-semibold text-gray-500 whitespace-nowrap print:text-[0.625rem]">Antecedentes Pessoais:</span>
              <span class="text-xs text-gray-700 whitespace-pre-wrap break-words leading-[1.3] p-1 bg-gray-50 border border-gray-200 rounded-sm flex-1 print:text-[0.6875rem] print:p-1 print:leading-[1.25]">{{ fichaClinica.anamnese.antecedentes_pessoais }}</span>
            </div>

            <div v-if="fichaClinica.anamnese.antecedentes_familiares" class="flex flex-row items-start gap-1.5 col-span-full mb-0.5 print:mb-0.5">
              <span class="text-[0.6875rem] font-semibold text-gray-500 whitespace-nowrap print:text-[0.625rem]">Antecedentes Familiares:</span>
              <span class="text-xs text-gray-700 whitespace-pre-wrap break-words leading-[1.3] p-1 bg-gray-50 border border-gray-200 rounded-sm flex-1 print:text-[0.6875rem] print:p-1 print:leading-[1.25]">{{ fichaClinica.anamnese.antecedentes_familiares }}</span>
            </div>
          </div>
        </div>
      </div>

      <div v-if="fichaClinica.acuidades_visuais && fichaClinica.acuidades_visuais.length > 0" class="mb-3 print:break-inside-avoid print:mb-2 print:p-2 print:border print:border-gray-300 print:bg-white">
        <h2 class="text-base font-bold text-gray-900 mb-2 pb-1 border-b border-gray-300 print:break-after-avoid print:mt-0 print:mb-2 print:text-[0.9375rem] print:pb-1">Acuidade Visual</h2>
        <div class="py-1 print:py-[0.125rem]">
          <table class="w-full border-collapse my-2 print:break-inside-avoid text-[0.8125rem] print:my-[0.375rem] print:text-[0.75rem]">
            <thead class="bg-gray-100 print:bg-gray-100">
              <tr>
                <th class="px-2 py-1.5 text-left font-semibold text-gray-900 border border-gray-300 text-xs print:px-1.5 print:py-1 print:text-[0.6875rem] print:font-bold print:border-gray-300 print:bg-gray-100 print:text-gray-900">Olho</th>
                <th class="px-2 py-1.5 text-left font-semibold text-gray-900 border border-gray-300 text-xs print:px-1.5 print:py-1 print:text-[0.6875rem] print:font-bold print:border-gray-300 print:bg-gray-100 print:text-gray-900">VL</th>
                <th class="px-2 py-1.5 text-left font-semibold text-gray-900 border border-gray-300 text-xs print:px-1.5 print:py-1 print:text-[0.6875rem] print:font-bold print:border-gray-300 print:bg-gray-100 print:text-gray-900">VP</th>
                <th class="px-2 py-1.5 text-left font-semibold text-gray-900 border border-gray-300 text-xs print:px-1.5 print:py-1 print:text-[0.6875rem] print:font-bold print:border-gray-300 print:bg-gray-100 print:text-gray-900">PH</th>
                <th class="px-2 py-1.5 text-left font-semibold text-gray-900 border border-gray-300 text-xs print:px-1.5 print:py-1 print:text-[0.6875rem] print:font-bold print:border-gray-300 print:bg-gray-100 print:text-gray-900">Observações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="acuidade in fichaClinica.acuidades_visuais" :key="acuidade.id" class="print:break-inside-avoid">
                <td class="px-2 py-1.5 border border-gray-300 text-gray-700 font-semibold print:px-1.5 print:py-1 print:text-[0.75rem] print:border-gray-300">{{ acuidade.olho.toUpperCase() }}</td>
                <td class="px-2 py-1.5 border border-gray-300 text-gray-700 text-center print:px-1.5 print:py-1 print:text-[0.75rem] print:border-gray-300">{{ acuidade.vl || '-' }}</td>
                <td class="px-2 py-1.5 border border-gray-300 text-gray-700 text-center print:px-1.5 print:py-1 print:text-[0.75rem] print:border-gray-300">{{ acuidade.vp || '-' }}</td>
                <td class="px-2 py-1.5 border border-gray-300 text-gray-700 text-center print:px-1.5 print:py-1 print:text-[0.75rem] print:border-gray-300">{{ acuidade.ph || '-' }}</td>
                <td class="px-2 py-1.5 border border-gray-300 text-gray-700 print:px-1.5 print:py-1 print:text-[0.75rem] print:border-gray-300">{{ acuidade.observacoes || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="fichaClinica.refracoes && fichaClinica.refracoes.length > 0" class="mb-3 print:break-inside-avoid print:mb-2 print:p-2 print:border print:border-gray-300 print:bg-white">
        <h2 class="text-base font-bold text-gray-900 mb-2 pb-1 border-b border-gray-300 print:break-after-avoid print:mt-0 print:mb-2 print:text-[0.9375rem] print:pb-1">Refração</h2>
        <div class="py-1 print:py-[0.125rem]">
          <div v-for="tipo in tiposRefracao" :key="tipo" class="mb-3 print:break-inside-avoid print:mb-2">
            <h3 class="text-sm font-semibold text-gray-700 mb-2 print:break-after-avoid print:mb-[0.375rem] print:text-[0.8125rem]">{{ tipo }}</h3>
            <table v-if="getRefracoesPorTipo(tipo).length > 0" class="w-full border-collapse my-2 print:break-inside-avoid text-[0.8125rem] print:my-[0.375rem] print:text-[0.75rem]">
              <thead class="bg-gray-100 print:bg-gray-100 print:table-header-group">
                <tr>
                  <th class="px-2 py-1.5 text-left font-semibold text-gray-900 border border-gray-300 text-xs print:px-1.5 print:py-1 print:text-[0.6875rem] print:font-bold print:border-gray-300 print:bg-gray-100 print:text-gray-900">Olho</th>
                  <th class="px-2 py-1.5 text-left font-semibold text-gray-900 border border-gray-300 text-xs print:px-1.5 print:py-1 print:text-[0.6875rem] print:font-bold print:border-gray-300 print:bg-gray-100 print:text-gray-900">ESF</th>
                  <th class="px-2 py-1.5 text-left font-semibold text-gray-900 border border-gray-300 text-xs print:px-1.5 print:py-1 print:text-[0.6875rem] print:font-bold print:border-gray-300 print:bg-gray-100 print:text-gray-900">CIL</th>
                  <th class="px-2 py-1.5 text-left font-semibold text-gray-900 border border-gray-300 text-xs print:px-1.5 print:py-1 print:text-[0.6875rem] print:font-bold print:border-gray-300 print:bg-gray-100 print:text-gray-900">EIXO</th>
                  <th class="px-2 py-1.5 text-left font-semibold text-gray-900 border border-gray-300 text-xs print:px-1.5 print:py-1 print:text-[0.6875rem] print:font-bold print:border-gray-300 print:bg-gray-100 print:text-gray-900">ADD</th>
                  <th class="px-2 py-1.5 text-left font-semibold text-gray-900 border border-gray-300 text-xs print:px-1.5 print:py-1 print:text-[0.6875rem] print:font-bold print:border-gray-300 print:bg-gray-100 print:text-gray-900">AV</th>
                </tr>
              </thead>
              <tbody class="print:table-row-group">
                <tr v-for="refracao in getRefracoesPorTipo(tipo)" :key="refracao.id" class="print:break-inside-avoid">
                  <td class="px-2 py-1.5 border border-gray-300 text-gray-700 font-semibold print:px-1.5 print:py-1 print:text-[0.75rem] print:border-gray-300">{{ refracao.olho.toUpperCase() }}</td>
                  <td class="px-2 py-1.5 border border-gray-300 text-gray-700 text-center print:px-1.5 print:py-1 print:text-[0.75rem] print:border-gray-300">{{ refracao.esf || '-' }}</td>
                  <td class="px-2 py-1.5 border border-gray-300 text-gray-700 text-center print:px-1.5 print:py-1 print:text-[0.75rem] print:border-gray-300">{{ refracao.cil || '-' }}</td>
                  <td class="px-2 py-1.5 border border-gray-300 text-gray-700 text-center print:px-1.5 print:py-1 print:text-[0.75rem] print:border-gray-300">{{ refracao.eixo || '-' }}</td>
                  <td class="px-2 py-1.5 border border-gray-300 text-gray-700 text-center print:px-1.5 print:py-1 print:text-[0.75rem] print:border-gray-300">{{ refracao.add || '-' }}</td>
                  <td class="px-2 py-1.5 border border-gray-300 text-gray-700 text-center print:px-1.5 print:py-1 print:text-[0.75rem] print:border-gray-300">{{ refracao.av || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div v-if="fichaClinica.biomicroscopias && fichaClinica.biomicroscopias.length > 0">
        <div class="hidden print:block print:h-[1cm] print:break-before-page print:break-after-avoid print:m-0 print:p-0 print:invisible"></div>
        <div class="mb-3 print:break-inside-avoid print:mb-2 print:p-2 print:border print:border-gray-300 print:bg-white print:mt-0 print:pt-2 print:break-before-avoid">
          <h2 class="text-base font-bold text-gray-900 mb-2 pb-1 border-b border-gray-300 print:break-after-avoid print:mt-0 print:mb-2 print:text-[0.9375rem] print:pb-1">Biomicroscopia</h2>
        <div class="py-1 print:py-[0.125rem]">
          <div class="grid grid-cols-2 gap-3 print:gap-2">
            <div v-for="bio in fichaClinica.biomicroscopias" :key="bio.id" class="mb-2 print:break-inside-avoid">
              <h3 class="text-sm font-semibold text-gray-700 mb-2 print:break-after-avoid print:mb-[0.375rem] print:text-[0.8125rem]">Olho {{ bio.olho.toUpperCase() }}</h3>
              <div class="flex flex-col gap-1.5 print:gap-1">
                <div v-if="bio.cornea" class="mb-1.5 print:mb-1.5">
                  <label class="block text-xs font-medium text-gray-500 mb-0.5 print:text-[0.6875rem] print:mb-0.5">Córnea</label>
                  <p class="text-[0.8125rem] text-gray-700 p-1.5 bg-gray-50 border border-gray-200 rounded leading-[1.4] print:text-[0.75rem] print:p-1 print:bg-gray-50 print:border-gray-200 print:rounded-none">{{ bio.cornea }}</p>
                </div>
                <div v-if="bio.iris" class="mb-1.5 print:mb-1.5">
                  <label class="block text-xs font-medium text-gray-500 mb-0.5 print:text-[0.6875rem] print:mb-0.5">Íris</label>
                  <p class="text-[0.8125rem] text-gray-700 p-1.5 bg-gray-50 border border-gray-200 rounded leading-[1.4] print:text-[0.75rem] print:p-1 print:bg-gray-50 print:border-gray-200 print:rounded-none">{{ bio.iris }}</p>
                </div>
                <div v-if="bio.conjuntiva" class="mb-1.5 print:mb-1.5">
                  <label class="block text-xs font-medium text-gray-500 mb-0.5 print:text-[0.6875rem] print:mb-0.5">Conjuntiva</label>
                  <p class="text-[0.8125rem] text-gray-700 p-1.5 bg-gray-50 border border-gray-200 rounded leading-[1.4] print:text-[0.75rem] print:p-1 print:bg-gray-50 print:border-gray-200 print:rounded-none">{{ bio.conjuntiva }}</p>
                </div>
                <div v-if="bio.cristalino" class="mb-1.5 print:mb-1.5">
                  <label class="block text-xs font-medium text-gray-500 mb-0.5 print:text-[0.6875rem] print:mb-0.5">Cristalino</label>
                  <p class="text-[0.8125rem] text-gray-700 p-1.5 bg-gray-50 border border-gray-200 rounded leading-[1.4] print:text-[0.75rem] print:p-1 print:bg-gray-50 print:border-gray-200 print:rounded-none">{{ bio.cristalino }}</p>
                </div>
                <div v-if="bio.pupilas" class="mb-1.5 print:mb-1.5">
                  <label class="block text-xs font-medium text-gray-500 mb-0.5 print:text-[0.6875rem] print:mb-0.5">Pupilas</label>
                  <p class="text-[0.8125rem] text-gray-700 p-1.5 bg-gray-50 border border-gray-200 rounded leading-[1.4] print:text-[0.75rem] print:p-1 print:bg-gray-50 print:border-gray-200 print:rounded-none">{{ bio.pupilas }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        </div>
      </div>

      <div v-if="fichaClinica.prescricao" class="mb-3 print:break-inside-avoid print:mb-2 print:p-2 print:border print:border-gray-300 print:bg-white print:mt-6 print:pt-2">
        <h2 class="text-base font-bold text-gray-900 mb-2 pb-1 border-b border-gray-300 print:break-after-avoid print:mt-0 print:mb-2 print:text-[0.9375rem] print:pb-1">Prescrição e Conduta</h2>
        <div class="py-1 print:py-[0.125rem]">
          <div class="grid grid-cols-3 gap-2 mb-2 print:gap-2 print:grid">
            <div v-if="fichaClinica.prescricao.material" class="mb-2 print:mb-1.5">
              <label class="block text-xs font-medium text-gray-500 mb-0.5 print:text-[0.6875rem] print:mb-0.5">Material</label>
              <p class="text-[0.8125rem] text-gray-700 p-1.5 bg-gray-50 border border-gray-200 rounded leading-[1.4] print:text-[0.75rem] print:p-1 print:bg-gray-50 print:border-gray-200 print:rounded-none">{{ fichaClinica.prescricao.material }}</p>
            </div>

            <div v-if="fichaClinica.prescricao.tipo_lente" class="mb-2 print:mb-1.5">
              <label class="block text-xs font-medium text-gray-500 mb-0.5 print:text-[0.6875rem] print:mb-0.5">Tipo de Lente</label>
              <p class="text-[0.8125rem] text-gray-700 p-1.5 bg-gray-50 border border-gray-200 rounded leading-[1.4] print:text-[0.75rem] print:p-1 print:bg-gray-50 print:border-gray-200 print:rounded-none">{{ fichaClinica.prescricao.tipo_lente }}</p>
            </div>

            <div v-if="fichaClinica.prescricao.filtro" class="mb-2 print:mb-1.5">
              <label class="block text-xs font-medium text-gray-500 mb-0.5 print:text-[0.6875rem] print:mb-0.5">Filtro</label>
              <p class="text-[0.8125rem] text-gray-700 p-1.5 bg-gray-50 border border-gray-200 rounded leading-[1.4] print:text-[0.75rem] print:p-1 print:bg-gray-50 print:border-gray-200 print:rounded-none">{{ fichaClinica.prescricao.filtro }}</p>
            </div>
          </div>

          <div v-if="fichaClinica.prescricao.diagnostico" class="mb-2 print:mb-1.5 col-span-full">
            <label class="block text-xs font-medium text-gray-500 mb-0.5 print:text-[0.6875rem] print:mb-0.5">Diagnóstico</label>
            <p class="text-[0.8125rem] text-gray-700 whitespace-pre-wrap break-words p-2 bg-gray-50 border border-gray-200 rounded leading-[1.4] print:text-[0.75rem] print:p-1.5 print:bg-gray-50 print:border-gray-200 print:rounded-none print:leading-[1.3]">{{ fichaClinica.prescricao.diagnostico }}</p>
          </div>

          <div v-if="fichaClinica.prescricao.conduta" class="mb-2 print:mb-1.5 col-span-full">
            <label class="block text-xs font-medium text-gray-500 mb-0.5 print:text-[0.6875rem] print:mb-0.5">Conduta</label>
            <p class="text-[0.8125rem] text-gray-700 whitespace-pre-wrap break-words p-2 bg-gray-50 border border-gray-200 rounded leading-[1.4] print:text-[0.75rem] print:p-1.5 print:bg-gray-50 print:border-gray-200 print:rounded-none print:leading-[1.3]">{{ fichaClinica.prescricao.conduta }}</p>
          </div>

          <div v-if="fichaClinica.prescricao.encaminhamento" class="mb-2 print:mb-1.5 col-span-full">
            <label class="block text-xs font-medium text-gray-500 mb-0.5 print:text-[0.6875rem] print:mb-0.5">Encaminhamento</label>
            <p class="text-[0.8125rem] text-gray-700 whitespace-pre-wrap break-words p-2 bg-gray-50 border border-gray-200 rounded leading-[1.4] print:text-[0.75rem] print:p-1.5 print:bg-gray-50 print:border-gray-200 print:rounded-none print:leading-[1.3]">{{ fichaClinica.prescricao.encaminhamento }}</p>
          </div>

          <div v-if="fichaClinica.prescricao.proximo_controle" class="mb-2 print:mb-1.5">
            <label class="block text-xs font-medium text-gray-500 mb-0.5 print:text-[0.6875rem] print:mb-0.5">Próxima Consulta</label>
            <p class="text-[0.8125rem] text-gray-700 p-1.5 bg-gray-50 border border-gray-200 rounded leading-[1.4] print:text-[0.75rem] print:p-1 print:bg-gray-50 print:border-gray-200 print:rounded-none">{{ formatDate(fichaClinica.prescricao.proximo_controle) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from '../../services/axios.js';

const route = useRoute();

const fichaClinicaId = computed(() => {
  return route.params.id || route.query.id;
});

const isLoading = ref(false);
const error = ref(null);
const fichaClinica = ref(null);

const formatCPF = (cpf) => {
  if (!cpf) return null;
  return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
};

const formatDate = (date) => {
  if (!date) return null;
  return new Date(date).toLocaleDateString('pt-BR');
};

const formatDateTime = (date) => {
  if (!date) return null; 
  return new Date(date).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

const tiposRefracao = computed(() => {
  if (!fichaClinica.value?.refracoes) return [];
  const tipos = [...new Set(fichaClinica.value.refracoes.map(r => r.tipo))];
  return tipos.map(tipo => {
    if (tipo === 'autorrefacao') return 'Autorrefração';
    if (tipo === 'subjetiva') return 'Refração Subjetiva';
    return tipo;
  });
});

const getRefracoesPorTipo = (tipoLabel) => {
  if (!fichaClinica.value?.refracoes) return [];

  let tipoBanco = null;
  
  if (tipoLabel === 'Autorrefração') {
    tipoBanco = 'autorrefacao';
  } else if (tipoLabel === 'Refração Subjetiva') {
    tipoBanco = 'subjetiva';
  } else {
    const tipoLower = tipoLabel.toLowerCase();
    if (tipoLower === 'autorrefação' || tipoLower === 'autorrefacao') {
      tipoBanco = 'autorrefacao';
    } else if (tipoLower === 'refração subjetiva' || tipoLower === 'subjetiva') {
      tipoBanco = 'subjetiva';
    }
  }

  if (!tipoBanco) return [];

  return fichaClinica.value.refracoes.filter(r => r.tipo === tipoBanco);
}

const loadFichaClinicaData = async (id) => {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await axios.get(`/fichas-clinicas/${id}`);

    if (response.data.success) {
      fichaClinica.value = response.data.data;
      setTimeout(() => {
        window.print();
      }, 500);
    } else {
      error.value = response.data.message || 'Erro ao carregar ficha clínica';
    }
  } catch (err) {
    console.error('Erro ao carregar ficha clínica:', err);

    if (err.response?.status === 404) {
      error.value = 'Ficha clínica não encontrada';
    } else if (err.response?.status === 403) {
      error.value = 'Você não tem permissão para visualizar esta ficha clínica';
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message;
    } else {
      error.value = 'Erro ao carregar ficha clínica. Tente novamente.';
    }
  } finally {
    isLoading.value = false;
  }
};

onMounted(async () => {
  if (fichaClinicaId.value) {
    await loadFichaClinicaData(fichaClinicaId.value);
  } else {
    error.value = 'ID da ficha clínica não fornecido';
    isLoading.value = false;
  }
});
</script>

<style scoped>
@media print {
  @page {
    size: A4;
    margin: 0;
  }

  .no-print {
    display: none !important;
  }

  html, body {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
    background: white !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  h1,
  h2,
  h3 {
    page-break-after: avoid;
    color: #111827 !important;
  }

  * {
    box-shadow: none !important;
    text-shadow: none !important;
  }
}
</style>
