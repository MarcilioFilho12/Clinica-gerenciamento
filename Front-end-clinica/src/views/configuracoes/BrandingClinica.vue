<template>
  <div class="max-w-2xl mx-auto p-6">
    <PageHeader title="Marca da Clínica" description="White-label: nome, logo e cores (login continua Marag)"
      :icon="PaintBrushIcon" icon-bg-color="blue" :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Marca da Clínica' }
      ]" class="mb-6" />

    <form class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 space-y-4" @submit.prevent="salvar">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nome da clínica</label>
        <input v-model="form.nome" type="text" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">URL do logo</label>
        <input v-model="form.logo_url" type="url" placeholder="https://..."
          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" />
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Cor primária</label>
          <input v-model="form.cor_primaria" type="color" class="w-full h-10 border border-gray-300 rounded-md" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Cor secundária</label>
          <input v-model="form.cor_secundaria" type="color" class="w-full h-10 border border-gray-300 rounded-md" />
        </div>
      </div>
      <p class="text-xs text-gray-500">Slug: <strong>{{ clinic.slug }}</strong> (definido no provisionamento)</p>
      <div class="flex justify-end">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm" :disabled="saving">
          {{ saving ? 'Salvando...' : 'Salvar' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { PaintBrushIcon } from '@heroicons/vue/24/outline'
import { toast } from 'vue3-toastify'
import axios from '../../services/axios.js'
import { useClinicStore } from '../../stores/clinic.js'

const clinic = useClinicStore()
const saving = ref(false)
const form = reactive({
  nome: '',
  logo_url: '',
  cor_primaria: '#0676a6',
  cor_secundaria: '#D4AF37',
})

onMounted(async () => {
  try {
    const data = await clinic.loadBranding()
    if (data) {
      form.nome = data.nome || ''
      form.logo_url = data.logo_url || ''
      form.cor_primaria = data.cor_primaria || '#0676a6'
      form.cor_secundaria = data.cor_secundaria || '#D4AF37'
    }
  } catch (e) {
    toast.error('Não foi possível carregar branding')
  }
})

const salvar = async () => {
  saving.value = true
  try {
    const res = await axios.put('/clinic/branding', {
      nome: form.nome,
      logo_url: form.logo_url || null,
      cor_primaria: form.cor_primaria,
      cor_secundaria: form.cor_secundaria,
    })
    if (res.data.success) {
      clinic.setBranding(res.data.data)
      toast.success('Marca atualizada')
    }
  } catch (e) {
    toast.error(e.response?.data?.message || 'Erro ao salvar')
  } finally {
    saving.value = false
  }
}
</script>
