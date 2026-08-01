<template>
  <div class="max-w-7xl mx-auto p-6">
    <PageHeader title="Visão Geral Financeira" description="Entradas, saídas e comparativo do período"
      :icon="CurrencyDollarIcon" icon-bg-color="green" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Visão Geral Financeira' }
      ]" class="mb-8">
      <template #actions>
        <div class="flex items-center space-x-4">
          <select v-model="periodoSelecionado"
            class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <option value="hoje">Hoje</option>
            <option value="semana">Esta Semana</option>
            <option value="mes">Este Mês</option>
            <option value="trimestre">Trimestre</option>
            <option value="ano">Este Ano</option>
          </select>
        </div>
      </template>
    </PageHeader>

    <div v-if="loading" class="text-center py-16 text-gray-500">Carregando dados financeiros...</div>
    <div v-else-if="error" class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 mb-6">{{ error }}</div>

    <template v-else>
      <!-- Indicadores -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Entradas (receita)</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">{{ money(resumo.receita_total) }}</p>
          <p class="text-xs mt-1" :class="resumo.crescimento_receita >= 0 ? 'text-green-600' : 'text-red-600'">
            {{ resumo.crescimento_receita >= 0 ? '+' : '' }}{{ resumo.crescimento_receita }}% vs período anterior
          </p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Saídas (despesas)</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">{{ money(resumo.despesa_total) }}</p>
          <p class="text-xs text-gray-500 mt-1">{{ resumo.despesas_recentes?.length || 0 }} lançamentos recentes</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Saldo</p>
          <p class="text-2xl font-bold mt-1" :class="resumo.saldo >= 0 ? 'text-green-700' : 'text-red-700'">
            {{ money(resumo.saldo) }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Margem {{ resumo.margem }}%</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">A receber</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">{{ money(resumo.a_receber) }}</p>
          <p class="text-xs text-gray-500 mt-1">{{ resumo.contas_a_receber }} consultas pendentes</p>
        </div>
      </div>

      <!-- Comparativo -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Comparativo com período anterior</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <div class="flex justify-between text-sm mb-1">
              <span class="text-gray-600">Receita atual</span>
              <span class="font-medium">{{ money(comparativo.receita_atual) }}</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2 mb-3">
              <div class="bg-green-500 h-2 rounded-full" :style="{ width: barWidth(comparativo.receita_atual, maxCompReceita) }"></div>
            </div>
            <div class="flex justify-between text-sm mb-1">
              <span class="text-gray-600">Receita anterior</span>
              <span class="font-medium">{{ money(comparativo.receita_anterior) }}</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
              <div class="bg-green-300 h-2 rounded-full" :style="{ width: barWidth(comparativo.receita_anterior, maxCompReceita) }"></div>
            </div>
          </div>
          <div>
            <div class="flex justify-between text-sm mb-1">
              <span class="text-gray-600">Despesa atual</span>
              <span class="font-medium">{{ money(comparativo.despesa_atual) }}</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2 mb-3">
              <div class="bg-orange-500 h-2 rounded-full" :style="{ width: barWidth(comparativo.despesa_atual, maxCompDespesa) }"></div>
            </div>
            <div class="flex justify-between text-sm mb-1">
              <span class="text-gray-600">Despesa anterior</span>
              <span class="font-medium">{{ money(comparativo.despesa_anterior) }}</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
              <div class="bg-orange-300 h-2 rounded-full" :style="{ width: barWidth(comparativo.despesa_anterior, maxCompDespesa) }"></div>
            </div>
          </div>
          <div class="flex flex-col justify-center p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-500">Ticket médio</p>
            <p class="text-xl font-bold text-gray-900">{{ money(resumo.ticket_medio) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ resumo.consultas_pagas }} consultas pagas · {{ resumo.pacientes_atendidos }} pacientes</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Formas de pagamento</h3>
          <div v-if="!resumo.por_forma_pagamento?.length" class="text-sm text-gray-500">Sem receitas no período.</div>
          <div v-else class="space-y-3">
            <div v-for="forma in resumo.por_forma_pagamento" :key="forma.chave" class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-900">{{ forma.nome }}</span>
              <div class="text-right">
                <div class="text-sm font-semibold">{{ money(forma.valor) }}</div>
                <div class="w-24 bg-gray-200 rounded-full h-1.5 mt-1 ml-auto">
                  <div class="bg-green-600 h-1.5 rounded-full" :style="{ width: `${forma.percentual}%` }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Receita por procedimento</h3>
          <div v-if="!resumo.por_procedimento?.length" class="text-sm text-gray-500">Sem receitas no período.</div>
          <div v-else class="space-y-3">
            <div v-for="tipo in resumo.por_procedimento" :key="tipo.nome" class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: tipo.cor }"></div>
                <span class="text-sm font-medium text-gray-900">{{ tipo.nome }}</span>
              </div>
              <div class="text-right">
                <div class="text-sm font-semibold">{{ money(tipo.valor) }}</div>
                <div class="text-xs text-gray-500">{{ tipo.percentual }}%</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Despesas -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Despesas do período</h3>
          <button type="button" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-md hover:bg-green-700"
            @click="abrirModalDespesa">
            Nova despesa
          </button>
        </div>

        <div v-if="!despesas.length" class="text-sm text-gray-500 py-6 text-center">Nenhuma despesa lançada neste período.</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="d in despesas" :key="d.id">
                <td class="px-4 py-2 whitespace-nowrap">{{ formatDate(d.data) }}</td>
                <td class="px-4 py-2">{{ d.descricao }}</td>
                <td class="px-4 py-2 text-gray-500">{{ d.categoria || '—' }}</td>
                <td class="px-4 py-2 text-right font-medium">{{ money(d.valor) }}</td>
                <td class="px-4 py-2 text-right">
                  <button type="button" class="text-red-600 hover:text-red-800 text-xs" @click="excluirDespesa(d.id)">
                    Excluir
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- Modal despesa -->
    <div v-if="showModalDespesa" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Nova despesa</h3>
        <form class="space-y-3" @submit.prevent="salvarDespesa">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <input v-model="formDespesa.descricao" type="text" required
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Valor</label>
              <input v-model.number="formDespesa.valor" type="number" min="0.01" step="0.01" required
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Data</label>
              <input v-model="formDespesa.data" type="date" required
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
            <input v-model="formDespesa.categoria" type="text" placeholder="Ex: Aluguel, Material..."
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Forma de pagamento</label>
            <select v-model="formDespesa.forma_pagamento" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
              <option value="">—</option>
              <option value="dinheiro">Dinheiro</option>
              <option value="pix">PIX</option>
              <option value="cartao_credito">Cartão de crédito</option>
              <option value="cartao_debito">Cartão de débito</option>
              <option value="transferencia">Transferência</option>
              <option value="outro">Outro</option>
            </select>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" class="px-3 py-2 text-sm border rounded-md" @click="showModalDespesa = false">Cancelar</button>
            <button type="submit" class="px-3 py-2 text-sm bg-green-600 text-white rounded-md" :disabled="salvando">
              {{ salvando ? 'Salvando...' : 'Salvar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { CurrencyDollarIcon } from '@heroicons/vue/24/outline'
import axios from '../../services/axios.js'
import { toast } from 'vue3-toastify'

const periodoSelecionado = ref('mes')
const loading = ref(false)
const error = ref(null)
const salvando = ref(false)
const showModalDespesa = ref(false)
const resumo = ref({
  receita_total: 0,
  despesa_total: 0,
  saldo: 0,
  ticket_medio: 0,
  consultas_pagas: 0,
  a_receber: 0,
  contas_a_receber: 0,
  pacientes_atendidos: 0,
  crescimento_receita: 0,
  margem: 0,
  por_forma_pagamento: [],
  por_procedimento: [],
  despesas_recentes: [],
  comparativo: {},
})
const despesas = ref([])
const formDespesa = ref({
  descricao: '',
  valor: null,
  data: new Date().toISOString().slice(0, 10),
  categoria: '',
  forma_pagamento: '',
})

const comparativo = computed(() => resumo.value.comparativo || {})
const maxCompReceita = computed(() => Math.max(comparativo.value.receita_atual || 0, comparativo.value.receita_anterior || 0, 1))
const maxCompDespesa = computed(() => Math.max(comparativo.value.despesa_atual || 0, comparativo.value.despesa_anterior || 0, 1))

const money = (v) => Number(v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
const barWidth = (v, max) => `${Math.min(100, Math.round(((Number(v) || 0) / max) * 100))}%`
const formatDate = (d) => {
  if (!d) return '—'
  const s = String(d).slice(0, 10)
  const [y, m, day] = s.split('-')
  return `${day}/${m}/${y}`
}

const carregarResumo = async () => {
  loading.value = true
  error.value = null
  try {
    const [resumoRes, despesasRes] = await Promise.all([
      axios.get('/financeiro/resumo', { params: { periodo: periodoSelecionado.value } }),
      axios.get('/financeiro/despesas', { params: { periodo: periodoSelecionado.value } }),
    ])
    if (!resumoRes.data.success) {
      throw new Error(resumoRes.data.message || 'Falha ao carregar resumo')
    }
    resumo.value = resumoRes.data.data
    despesas.value = despesasRes.data.data || []
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Erro ao carregar financeiro'
  } finally {
    loading.value = false
  }
}

const abrirModalDespesa = () => {
  formDespesa.value = {
    descricao: '',
    valor: null,
    data: new Date().toISOString().slice(0, 10),
    categoria: '',
    forma_pagamento: '',
  }
  showModalDespesa.value = true
}

const salvarDespesa = async () => {
  salvando.value = true
  try {
    const payload = { ...formDespesa.value }
    if (!payload.forma_pagamento) delete payload.forma_pagamento
    if (!payload.categoria) payload.categoria = null
    const res = await axios.post('/financeiro/despesas', payload)
    if (res.data.success) {
      toast.success('Despesa registrada')
      showModalDespesa.value = false
      await carregarResumo()
    }
  } catch (e) {
    toast.error(e.response?.data?.message || 'Erro ao salvar despesa')
  } finally {
    salvando.value = false
  }
}

const excluirDespesa = async (id) => {
  if (!confirm('Excluir esta despesa?')) return
  try {
    await axios.delete(`/financeiro/despesas/${id}`)
    toast.success('Despesa excluída')
    await carregarResumo()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Erro ao excluir')
  }
}

onMounted(carregarResumo)
watch(periodoSelecionado, carregarResumo)
</script>
