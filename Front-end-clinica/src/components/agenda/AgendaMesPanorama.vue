<template>
  <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
    <AgendaPanoramaLegenda class="mb-4" />

    <div class="grid grid-cols-7 gap-px mb-1 bg-transparent">
      <div
        v-for="nome in diasSemana"
        :key="nome"
        class="text-center text-[10px] sm:text-xs font-medium text-gray-400 py-1"
      >
        {{ nome }}
      </div>
    </div>

    <div class="grid grid-cols-7 gap-1 sm:gap-1.5">
      <button
        v-for="cel in cells"
        :key="cel.key"
        type="button"
        :disabled="!cel.inMonth"
        :class="[
          'min-h-[96px] sm:min-h-[110px] rounded-lg border p-1.5 text-left transition-colors',
          cel.inMonth ? 'hover:border-gray-300 bg-white border-gray-200' : 'bg-gray-50/80 text-gray-300 cursor-default border-transparent',
          cel.date === selectedDate ? 'ring-2 ring-gray-900' : '',
        ]"
        @click="cel.inMonth && $emit('select-day', cel.date)"
      >
        <div class="flex justify-end mb-1">
          <span
            :class="[
              'inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold',
              cel.isToday ? 'bg-gray-900 text-white' : 'text-gray-800',
            ]"
          >
            {{ cel.dayNumber }}
          </span>
        </div>

        <div v-if="cel.inMonth" class="space-y-0.5">
          <div
            v-for="card in cel.cards.slice(0, maxChips)"
            :key="card.id"
            class="rounded-md overflow-hidden flex text-[10px] sm:text-[11px] leading-tight"
            :title="tooltipCard(card)"
            @click.stop="$emit('select-event', card)"
          >
            <span :class="['w-0.5 flex-shrink-0', statusBarClass(card)]" />
            <span :class="['flex-1 px-1 py-0.5 truncate', card.corProf.soft, card.corProf.text]">
              <span class="font-semibold">{{ card.horarioInicio }}</span>
              {{ primeiroNome(card.paciente) }}
            </span>
          </div>
          <div
            v-if="cel.cards.length > maxChips"
            class="text-[10px] text-gray-500 font-medium pl-0.5"
          >
            +{{ cel.cards.length - maxChips }} mais
          </div>
        </div>
      </button>
    </div>
  </div>
</template>

<script>
import AgendaPanoramaLegenda from './AgendaPanoramaLegenda.vue'
import { agruparConsultasPorData, buildMonthCells } from '../../utils/agendaPanorama.js'

export default {
  name: 'AgendaMesPanorama',
  components: { AgendaPanoramaLegenda },
  props: {
    rangeInicio: { type: String, required: true },
    selectedDate: { type: String, default: '' },
    consultas: { type: Array, default: () => [] },
    maxChips: { type: Number, default: 3 },
  },
  emits: ['select-day', 'select-event'],
  computed: {
    diasSemana() {
      return ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']
    },
    cells() {
      return buildMonthCells(this.rangeInicio, agruparConsultasPorData(this.consultas))
    },
  },
  methods: {
    primeiroNome(nome) {
      if (!nome) return ''
      return String(nome).trim().split(/\s+/)[0]
    },
    statusBarClass(card) {
      if (card.statusKey === 'urgencia') return 'bg-red-500'
      if (card.statusKey === 'atendimento') return 'bg-violet-600'
      if (card.statusKey === 'chegou') return 'bg-amber-500'
      if (card.statusKey === 'realizada') return 'bg-slate-400'
      return card.corProf?.bar || 'bg-indigo-500'
    },
    tooltipCard(card) {
      return [
        card.horarioInicio,
        card.horarioFim ? `– ${card.horarioFim}` : '',
        card.paciente,
        card.profissional,
        card.statusLabel,
        card.pago ? 'pago' : '',
      ]
        .filter(Boolean)
        .join(' · ')
    },
  },
}
</script>
