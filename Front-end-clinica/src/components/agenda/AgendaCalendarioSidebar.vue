<template>
  <aside class="w-full lg:w-56 xl:w-64 flex-shrink-0 flex flex-col gap-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-gray-900">Profissionais</h3>
        <span class="text-[10px] text-gray-500">até {{ maxSelecionados }}</span>
      </div>
      <p class="text-[11px] text-gray-500 mb-3 leading-snug">
        Marque quem aparece na grade. Cores distintas por profissional; status do paciente no bloco.
      </p>
      <ul class="space-y-2 max-h-64 overflow-y-auto">
        <li v-for="doc in profissionais" :key="doc.id">
          <label
            class="flex items-center gap-2 cursor-pointer rounded-lg px-2 py-1.5 hover:bg-gray-50"
            :class="isChecked(doc.id) ? 'bg-gray-50 ring-1 ring-gray-200' : ''"
          >
            <input
              type="checkbox"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              :checked="isChecked(doc.id)"
              :disabled="!isChecked(doc.id) && selecionados.length >= maxSelecionados"
              @change="onToggle(doc.id, $event.target.checked)"
            />
            <span :class="['h-2.5 w-2.5 rounded-sm flex-shrink-0', corDe(doc.id).dot]" />
            <span class="text-sm text-gray-800 truncate" :title="doc.name">{{ doc.name }}</span>
          </label>
        </li>
        <li v-if="!profissionais.length" class="text-xs text-gray-500 py-2">
          Nenhum profissional carregado.
        </li>
      </ul>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between mb-3">
        <button type="button" class="p-1 rounded hover:bg-gray-100" @click="$emit('shift-month', -1)">
          <ChevronLeft class="w-4 h-4 text-gray-600" />
        </button>
        <span class="text-sm font-semibold text-gray-900 capitalize">{{ mesLabel }}</span>
        <button type="button" class="p-1 rounded hover:bg-gray-100" @click="$emit('shift-month', 1)">
          <ChevronRight class="w-4 h-4 text-gray-600" />
        </button>
      </div>
      <div class="grid grid-cols-7 gap-0.5 mb-1">
        <div
          v-for="(n, idx) in weekdayLetters"
          :key="'wd-' + idx"
          class="text-center text-[10px] text-gray-400 font-medium"
        >
          {{ n }}
        </div>
      </div>
      <div class="grid grid-cols-7 gap-0.5">
        <button
          v-for="cel in miniCells"
          :key="cel.key"
          type="button"
          :disabled="!cel.inMonth"
          class="aspect-square text-[11px] rounded-full flex items-center justify-center transition-colors"
          :class="miniCelClass(cel)"
          @click="cel.inMonth && $emit('select-date', cel.date)"
        >
          {{ cel.dayNumber }}
        </button>
      </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
      <p class="text-[10px] font-medium text-gray-500 mb-2 uppercase tracking-wide">Status do fluxo</p>
      <AgendaPanoramaLegenda class="!gap-2 flex-col items-start" />
    </div>
  </aside>
</template>

<script>
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import AgendaPanoramaLegenda from './AgendaPanoramaLegenda.vue'
import {
  MAX_PROFISSIONAIS_PANORAMA,
  corProfissionalPorId,
  buildMonthCells,
} from '../../utils/agendaPanorama.js'
import { parseLocalDate, formatDateISO } from '../../utils/agendaDatas.js'

export default {
  name: 'AgendaCalendarioSidebar',
  components: { ChevronLeft, ChevronRight, AgendaPanoramaLegenda },
  props: {
    profissionais: { type: Array, default: () => [] },
    selecionados: { type: Array, default: () => [] },
    selectedDate: { type: String, default: '' },
    maxSelecionados: { type: Number, default: MAX_PROFISSIONAIS_PANORAMA },
  },
  emits: ['update:selecionados', 'select-date', 'shift-month'],
  computed: {
    weekdayLetters() {
      return ['D', 'S', 'T', 'Q', 'Q', 'S', 'S']
    },
    mesLabel() {
      const d = parseLocalDate(this.selectedDate || formatDateISO(new Date()))
      return d.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
    },
    miniCells() {
      const base = this.selectedDate || formatDateISO(new Date())
      const d = parseLocalDate(base)
      const inicioMes = formatDateISO(new Date(d.getFullYear(), d.getMonth(), 1))
      return buildMonthCells(inicioMes, {})
    },
  },
  methods: {
    corDe(id) {
      return corProfissionalPorId(id)
    },
    isChecked(id) {
      return this.selecionados.map(String).includes(String(id))
    },
    onToggle(id, checked) {
      const idStr = String(id)
      let next = this.selecionados.map(String)
      if (checked) {
        if (next.length >= this.maxSelecionados) return
        if (!next.includes(idStr)) next = [...next, idStr]
      } else {
        next = next.filter((x) => x !== idStr)
      }
      this.$emit('update:selecionados', next)
    },
    miniCelClass(cel) {
      if (!cel.inMonth) return 'text-gray-300 cursor-default'
      if (cel.date === this.selectedDate) return 'bg-gray-900 text-white font-semibold'
      if (cel.isToday) return 'bg-blue-100 text-blue-900 font-semibold'
      return 'text-gray-700 hover:bg-gray-100'
    },
  },
}
</script>
