<template>
  <div class="max-w-7xl mx-auto p-6">
    <PageHeader title="Relatório Financeiro" :icon="ChartBarIcon" icon-bg-color="green"
      description="Análise detalhada dos resultados financeiros" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Relatório Financeiro' }
      ]" class="mb-8">
      <template #actions>
        <div class="flex items-center space-x-4">
          <select v-model="periodoSelecionado" @change="carregarRelatorio"
            class="text-sm border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <option value="hoje">Hoje</option>
            <option value="semana">Esta Semana</option>
            <option value="mes">Este Mês</option>
            <option value="trimestre">Trimestre</option>
            <option value="semestre">Semestre</option>
            <option value="ano">Este Ano</option>
          </select>
          <button type="button" @click="exportarRelatorio"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors flex items-center space-x-2 text-sm font-medium">
            <Download class="w-4 h-4" />
            <span>Exportar</span>
          </button>
        </div>
      </template>
    </PageHeader>

    <div v-if="loading" class="text-center py-16 text-gray-500">Gerando relatório...</div>
    <div v-else-if="error" class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 mb-6">{{ error }}</div>

    <template v-else>
      <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-lg shadow-sm p-6 mb-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
          <div>
            <h2 class="text-2xl font-bold">{{ getTituloPeriodo() }}</h2>
            <p class="text-green-100 mt-1">
              {{ formatDate(relatorio.data_inicio) }} — {{ formatDate(relatorio.data_fim) }}
            </p>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold">{{ money(relatorio.resumo?.receita_total) }}</div>
            <div class="text-green-100 flex items-center justify-end mt-1">
              <TrendingUp class="w-4 h-4 mr-1" />
              {{ relatorio.resumo?.crescimento || 0 }}% vs período anterior
            </div>
            <div class="text-sm text-green-100 mt-1">
              Despesas {{ money(relatorio.resumo?.despesa_total) }} · Saldo {{ money(relatorio.resumo?.saldo) }}
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <p class="text-xs font-medium text-gray-500 uppercase">Pacientes atendidos</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">{{ relatorio.metricas?.pacientes_atendidos || 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <p class="text-xs font-medium text-gray-500 uppercase">Ticket médio</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">{{ money(relatorio.metricas?.ticket_medio) }}</p>
          <p class="text-xs text-gray-500 mt-1">{{ relatorio.metricas?.consultas_pagas || 0 }} consultas pagas</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <p class="text-xs font-medium text-gray-500 uppercase">Valores pendentes</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">{{ money(relatorio.metricas?.a_receber) }}</p>
          <p class="text-xs text-gray-500 mt-1">{{ relatorio.metricas?.contas_a_receber || 0 }} contas</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <p class="text-xs font-medium text-gray-500 uppercase">Taxa de recebimento</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">{{ relatorio.metricas?.taxa_recebimento || 0 }}%</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Receita no período</h3>
          <div v-if="!relatorio.receita_por_periodo?.length" class="text-sm text-gray-500">Sem dados.</div>
          <div v-else class="space-y-3">
            <div v-for="(item, index) in relatorio.receita_por_periodo" :key="index"
              class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-900">{{ item.periodo }}</span>
              <div class="flex items-center space-x-3">
                <span class="text-sm font-semibold">{{ money(item.valor) }}</span>
                <div class="w-24 bg-gray-200 rounded-full h-2">
                  <div class="bg-green-500 h-2 rounded-full" :style="{ width: `${item.percentual}%` }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Receita por procedimento</h3>
          <div v-if="!relatorio.por_procedimento?.length" class="text-sm text-gray-500">Sem dados.</div>
          <div v-else class="space-y-3">
            <div v-for="tipo in relatorio.por_procedimento" :key="tipo.nome"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center space-x-3">
                <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: tipo.cor }"></div>
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ tipo.nome }}</p>
                  <p class="text-xs text-gray-500">{{ tipo.quantidade }} consultas</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm font-semibold">{{ money(tipo.valor) }}</p>
                <p class="text-xs text-gray-500">{{ tipo.percentual }}%</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Formas de pagamento</h3>
          <div v-if="!relatorio.por_forma_pagamento?.length" class="text-sm text-gray-500">Sem dados.</div>
          <div v-else class="space-y-3">
            <div v-for="forma in relatorio.por_forma_pagamento" :key="forma.chave"
              class="flex items-center justify-between">
              <span class="text-sm font-medium">{{ forma.nome }}</span>
              <div class="flex items-center space-x-3">
                <span class="text-sm font-semibold">{{ money(forma.valor) }}</span>
                <span class="text-xs text-gray-500 w-10 text-right">{{ forma.percentual }}%</span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Convênios</h3>
          <div v-if="!relatorio.por_convenio?.lista?.length" class="text-sm text-gray-500 mb-3">
            Sem receita de convênio no período.
          </div>
          <div v-else class="space-y-3 mb-4">
            <div v-for="c in relatorio.por_convenio.lista" :key="c.nome" class="border border-gray-200 rounded-lg p-3">
              <div class="flex justify-between mb-1">
                <span class="text-sm font-medium">{{ c.nome }}</span>
                <span class="text-sm font-semibold text-blue-600">{{ money(c.valor) }}</span>
              </div>
              <div class="text-xs text-gray-500">{{ c.consultas }} consultas · ticket {{ money(c.ticket_medio) }}</div>
            </div>
          </div>
          <div class="p-3 bg-green-50 rounded-lg text-sm">
            <span class="text-gray-600">Particulares:</span>
            <span class="font-semibold text-green-700 ml-1">
              {{ money(relatorio.por_convenio?.particulares?.valor) }}
              ({{ relatorio.por_convenio?.particulares?.consultas || 0 }} consultas)
            </span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Comparativo histórico</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receita</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Despesa</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Consultas</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ticket</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Variação</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="(p, index) in relatorio.historico_comparativo" :key="index" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ p.nome }}</td>
                <td class="px-4 py-3 text-sm">{{ money(p.receita) }}</td>
                <td class="px-4 py-3 text-sm">{{ money(p.despesa) }}</td>
                <td class="px-4 py-3 text-sm">{{ p.consultas }}</td>
                <td class="px-4 py-3 text-sm">{{ money(p.ticket_medio) }}</td>
                <td class="px-4 py-3 text-sm">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                    :class="p.crescimento >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                    <component :is="p.crescimento >= 0 ? TrendingUp : TrendingDown" class="w-3 h-3 mr-1" />
                    {{ Math.abs(p.crescimento) }}%
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ChartBarIcon } from '@heroicons/vue/24/outline'
import { Download, TrendingUp, TrendingDown } from 'lucide-vue-next'
import axios from '../../services/axios.js'

const periodoSelecionado = ref('mes')
const loading = ref(false)
const error = ref(null)
const relatorio = ref({})

const money = (v) => Number(v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
const formatDate = (d) => {
  if (!d) return '—'
  const s = String(d).slice(0, 10)
  const [y, m, day] = s.split('-')
  return `${day}/${m}/${y}`
}

const getTituloPeriodo = () => {
  const titulos = {
    hoje: 'Relatório do Dia',
    semana: 'Relatório Semanal',
    mes: 'Relatório Mensal',
    trimestre: 'Relatório Trimestral',
    semestre: 'Relatório Semestral',
    ano: 'Relatório Anual',
  }
  return titulos[periodoSelecionado.value] || 'Relatório'
}

const carregarRelatorio = async () => {
  loading.value = true
  error.value = null
  try {
    const res = await axios.get('/financeiro/relatorio', { params: { periodo: periodoSelecionado.value } })
    if (!res.data.success) throw new Error(res.data.message || 'Falha ao carregar')
    relatorio.value = res.data.data
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Erro ao carregar relatório'
  } finally {
    loading.value = false
  }
}

const exportarRelatorio = () => {
  const r = relatorio.value
  const texto = [
    `Relatório Financeiro - ${getTituloPeriodo()}`,
    `Período: ${formatDate(r.data_inicio)} a ${formatDate(r.data_fim)}`,
    `Gerado em: ${new Date().toLocaleString('pt-BR')}`,
    '',
    `Receita: ${money(r.resumo?.receita_total)}`,
    `Despesas: ${money(r.resumo?.despesa_total)}`,
    `Saldo: ${money(r.resumo?.saldo)}`,
    `Crescimento: ${r.resumo?.crescimento || 0}%`,
    `Ticket médio: ${money(r.metricas?.ticket_medio)}`,
    `Consultas pagas: ${r.metricas?.consultas_pagas || 0}`,
    `A receber: ${money(r.metricas?.a_receber)}`,
  ].join('\n')

  const elemento = document.createElement('a')
  elemento.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(texto)
  elemento.download = `relatorio-financeiro-${periodoSelecionado.value}-${new Date().toISOString().slice(0, 10)}.txt`
  elemento.click()
}

onMounted(carregarRelatorio)
</script>
