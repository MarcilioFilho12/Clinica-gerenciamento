<template>
  <div class="a5-page bg-white text-gray-900">
    <div v-if="loading" class="p-8 text-center text-gray-500">Carregando...</div>
    <div v-else-if="error" class="p-8 text-center text-red-600">{{ error }}</div>
    <div v-else class="p-6 print:p-4">
      <header class="border-b border-gray-300 pb-3 mb-4">
        <h1 class="text-lg font-bold tracking-tight">Refração Subjetiva</h1>
        <p class="text-sm text-gray-600 mt-1">{{ pacienteNome }}</p>
        <p class="text-xs text-gray-500">
          Data: {{ formatDate(fichaClinica?.data_consulta) }}
          <span v-if="profissionalNome"> · {{ profissionalNome }}</span>
        </p>
      </header>

      <table class="w-full border-collapse border border-gray-400 text-sm mb-4">
        <thead>
          <tr class="bg-gray-100">
            <th class="border border-gray-400 px-2 py-1.5 text-left">Olho</th>
            <th class="border border-gray-400 px-2 py-1.5 text-center">ESF</th>
            <th class="border border-gray-400 px-2 py-1.5 text-center">CIL</th>
            <th class="border border-gray-400 px-2 py-1.5 text-center">EIXO</th>
            <th class="border border-gray-400 px-2 py-1.5 text-center">ADD</th>
            <th class="border border-gray-400 px-2 py-1.5 text-center">AV</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="olho in ['od', 'oe']" :key="olho">
            <td class="border border-gray-400 px-2 py-1.5 font-semibold uppercase">{{ olho }}</td>
            <td class="border border-gray-400 px-2 py-1.5 text-center">{{ valor(olho, 'esf') }}</td>
            <td class="border border-gray-400 px-2 py-1.5 text-center">{{ valor(olho, 'cil') }}</td>
            <td class="border border-gray-400 px-2 py-1.5 text-center">{{ valor(olho, 'eixo') }}</td>
            <td class="border border-gray-400 px-2 py-1.5 text-center">{{ valor(olho, 'add') }}</td>
            <td class="border border-gray-400 px-2 py-1.5 text-center">{{ valor(olho, 'av') }}</td>
          </tr>
        </tbody>
      </table>

      <section v-if="fichaClinica?.prescricao?.encaminhamento" class="mb-4">
        <h2 class="text-sm font-semibold mb-1">Encaminhamento</h2>
        <p class="text-sm whitespace-pre-wrap border border-gray-300 rounded p-2 bg-gray-50">
          {{ fichaClinica.prescricao.encaminhamento }}
        </p>
      </section>

      <section v-if="fichaClinica?.prescricao?.proximo_controle" class="mb-4 text-sm">
        <span class="font-semibold">Próxima consulta:</span>
        {{ formatDate(fichaClinica.prescricao.proximo_controle) }}
      </section>

      <div class="mt-6 flex gap-2 print:hidden">
        <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm" @click="printPage">
          Imprimir A5
        </button>
        <button type="button" class="px-4 py-2 border rounded-md text-sm" @click="fechar">
          Fechar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import axios from '../../services/axios.js'

const route = useRoute()
const loading = ref(true)
const error = ref('')
const fichaClinica = ref(null)

const pacienteNome = computed(() => fichaClinica.value?.cadastro?.nome || 'Paciente')
const profissionalNome = computed(() => fichaClinica.value?.user?.name || '')

const subjetivas = computed(() => {
  const list = fichaClinica.value?.refracoes || []
  return list.filter((r) => {
    const t = String(r.tipo || '').toLowerCase()
    return t.includes('subjet')
  })
})

const valor = (olho, campo) => {
  const row = subjetivas.value.find((r) => String(r.olho).toLowerCase() === olho)
  return row?.[campo] || '—'
}

const formatDate = (value) => {
  if (!value) return '—'
  const raw = String(value).substring(0, 10)
  const [y, m, d] = raw.split('-')
  if (!y || !m || !d) return value
  return `${d}/${m}/${y}`
}

const printPage = () => window.print()
const fechar = () => window.close()

onMounted(async () => {
  try {
    const id = route.params.id
    const { data } = await axios.get(`/fichas-clinicas/${id}`)
    if (!data.success) {
      throw new Error(data.message || 'Falha ao carregar ficha')
    }
    fichaClinica.value = data.data
    setTimeout(() => window.print(), 400)
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Erro ao carregar'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.a5-page {
  min-height: 100vh;
}

@media print {
  @page {
    size: A5 portrait;
    margin: 10mm;
  }

  body {
    background: white !important;
  }

  .a5-page {
    width: 148mm;
    min-height: 210mm;
  }
}
</style>
