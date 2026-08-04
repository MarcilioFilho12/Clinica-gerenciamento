<template>
  <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <AgendaPanoramaLegenda class="px-4 pt-3 pb-2 border-b border-gray-100" />

    <!-- Mobile -->
        <div class="md:hidden space-y-3 p-3">
      <button
        v-for="dia in weekDays"
        :key="dia.date"
        type="button"
        class="w-full text-left border rounded-xl p-3 hover:border-gray-300 transition-colors bg-white"
        :class="[
          dia.isToday ? 'border-gray-900 ring-1 ring-gray-900/10' : 'border-gray-200',
          dia.isPast && !(cardsPorDia[dia.date] || []).length ? 'opacity-45' : 'opacity-100',
        ]"
        @click="$emit('select-day', dia.date)"
      >
        <div class="flex items-baseline justify-between mb-2">
          <div>
            <span class="text-xs font-medium text-gray-500 uppercase">{{ dia.label }}</span>
            <span class="ml-2 text-lg font-semibold text-gray-900">{{ dia.dayNumber }}</span>
          </div>
          <span class="text-xs text-gray-500">{{ (cardsPorDia[dia.date] || []).length }} consulta(s)</span>
        </div>
        <ul class="space-y-1.5">
          <li
            v-for="card in (cardsPorDia[dia.date] || []).slice(0, 5)"
            :key="card.id"
            class="text-xs rounded-lg overflow-hidden flex opacity-100"
            @click.stop="$emit('select-event', card)"
          >
            <span :class="['w-1 flex-shrink-0', card.corProf.bar]" />
            <span :class="['flex-1 px-2 py-1 truncate', card.corProf.soft, card.corProf.text]">
              <strong>{{ card.horarioInicio }}</strong> {{ card.paciente }}
              <span class="opacity-70"> · {{ card.statusLabel }}</span>
            </span>
          </li>
        </ul>
      </button>
    </div>

    <!-- Desktop timeline -->
    <div class="hidden md:block overflow-x-auto">
      <div class="min-w-[780px]">
        <div class="grid border-b border-gray-200 bg-gray-50/80" :style="gridColsStyle">
          <div class="p-2 text-[11px] font-medium text-gray-400 sticky left-0 bg-gray-50 z-10 border-r border-gray-200">
            Hora
          </div>
          <button
            v-for="dia in weekDays"
            :key="`h-${dia.date}`"
            type="button"
            class="p-2 text-center border-r border-gray-200 last:border-r-0 hover:bg-white transition-colors"
            :class="[
              dia.isToday ? 'bg-white' : '',
              dia.isPast ? 'opacity-50' : 'opacity-100',
            ]"
            @click="$emit('select-day', dia.date)"
          >
            <div class="text-[11px] font-medium text-gray-500 uppercase">{{ dia.label }}</div>
            <div
              :class="[
                'inline-flex mt-1 h-7 w-7 items-center justify-center rounded-full text-sm font-semibold',
                dia.isToday ? 'bg-gray-900 text-white' : 'text-gray-900',
              ]"
            >
              {{ dia.dayNumber }}
            </div>
          </button>
        </div>

        <div class="grid relative" :style="gridColsStyle">
          <div class="border-r border-gray-200 bg-white sticky left-0 z-10">
            <div
              v-for="slot in eixo.slots"
              :key="slot.time"
              class="border-b border-gray-100 text-[10px] px-1 flex items-start justify-end pr-2 pt-1 transition-opacity"
              :class="isSlotTimePastToday(slot.time) ? 'text-gray-300 opacity-45' : 'text-gray-400 opacity-100'"
              :style="{ height: `${slotHeightPx}px` }"
            >
              {{ slot.time }}
            </div>
          </div>

          <div
            v-for="dia in weekDays"
            :key="`col-${dia.date}`"
            class="relative border-r border-gray-100 last:border-r-0 cursor-cell"
            :class="dia.isToday ? 'bg-sky-50/40' : dia.isPast ? 'bg-gray-50/90' : 'bg-white'"
            :style="{ height: `${eixo.slots.length * slotHeightPx}px` }"
            title="Clique vazio para agendar"
            @click="onColunaClick($event, dia.date)"
          >
            <!-- Faixa opaca do passado (dia inteiro ou até a linha agora) -->
            <div
              v-if="dia.isPast"
              class="absolute inset-0 z-[5] bg-gray-400/20 pointer-events-none"
            />
            <div
              v-else-if="dia.isToday && nowLinePct != null"
              class="absolute left-0 right-0 top-0 z-[5] bg-gray-400/25 pointer-events-none"
              :style="{ height: `${nowLinePct}%` }"
            />

            <div
              v-for="slot in eixo.slots"
              :key="`${dia.date}-${slot.time}`"
              class="absolute left-0 right-0 border-b border-gray-100/80 pointer-events-none"
              :style="{ top: `${((slot.minutes - eixo.inicioMin) / spanMin) * 100}%`, height: `${slotHeightPx}px` }"
            />

            <!-- Linha agora -->
            <div
              v-if="dia.isToday && nowLinePct != null"
              class="absolute left-0 right-0 z-30 pointer-events-none flex items-center"
              :style="{ top: `${nowLinePct}%` }"
            >
              <span class="h-2 w-2 rounded-full bg-red-500 -ml-1" />
              <span class="h-px flex-1 bg-red-500" />
            </div>

            <button
              v-for="pos in blocosDoDia(dia.date)"
              :key="pos.card.id"
              type="button"
              class="absolute left-0.5 right-0.5 rounded-md overflow-hidden text-left shadow-sm hover:shadow-md hover:z-20 focus:outline-none focus:ring-2 focus:ring-gray-400 transition-shadow flex opacity-100"
              :class="[pos.card.corProf.soft, pos.card.corProf.text]"
              :style="{
                top: `${pos.topPct}%`,
                height: `${pos.heightPct}%`,
                minHeight: '22px',
                zIndex: 15,
              }"
              :title="tooltipCard(pos.card)"
              @click.stop="$emit('select-event', pos.card)"
            >
              <span :class="['w-1 flex-shrink-0', statusBarClass(pos.card)]" />
              <span class="flex-1 px-1.5 py-0.5 overflow-hidden min-w-0">
                <div class="text-[10px] font-semibold leading-tight truncate">
                  {{ pos.card.horarioInicio }}
                  <span v-if="pos.card.horarioFim" class="font-normal opacity-70">–{{ pos.card.horarioFim }}</span>
                </div>
                <div class="text-[10px] leading-tight truncate font-medium">
                  {{ pos.card.paciente }}
                </div>
                <div class="text-[9px] leading-tight truncate opacity-80">
                  {{ pos.card.statusLabel }}
                  <span v-if="mostrarProfissional"> · {{ primeiroNome(pos.card.profissional) }}</span>
                  <span v-if="pos.card.pago"> · pago</span>
                </div>
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <p class="px-4 py-2 text-[11px] text-gray-500 border-t border-gray-100 hidden md:block">
      Bloco: nome do paciente, horário e status. Cor de fundo = profissional. Barra lateral = situação do fluxo.
      Clique vazio para nova consulta.
    </p>
  </div>
</template>

<script>
import AgendaPanoramaLegenda from './AgendaPanoramaLegenda.vue'
import { minutesToTime, obterDataAtualISO, isHorarioNoPassado } from '../../utils/agendaDatas.js'
import {
  agruparConsultasPorData,
  buildWeekDays,
  gerarEixoHorarios,
  posicaoBlocoNoEixo,
} from '../../utils/agendaPanorama.js'

const SLOT_HEIGHT_PX = 48

export default {
  name: 'AgendaSemanaPanorama',
  components: { AgendaPanoramaLegenda },
  props: {
    rangeInicio: { type: String, required: true },
    consultas: { type: Array, default: () => [] },
    configuracao: { type: Object, default: null },
    mostrarProfissional: { type: Boolean, default: true },
  },
  emits: ['select-day', 'select-event', 'select-slot'],
  data() {
    return { slotHeightPx: SLOT_HEIGHT_PX, nowTick: Date.now() }
  },
  computed: {
    weekDays() {
      return buildWeekDays(this.rangeInicio)
    },
    cardsPorDia() {
      return agruparConsultasPorData(this.consultas)
    },
    eixo() {
      return gerarEixoHorarios(this.configuracao)
    },
    spanMin() {
      return Math.max(this.eixo.fimMin - this.eixo.inicioMin, 1)
    },
    gridColsStyle() {
      return { gridTemplateColumns: `48px repeat(7, minmax(0, 1fr))` }
    },
    nowLinePct() {
      void this.nowTick
      const now = new Date()
      const mins = now.getHours() * 60 + now.getMinutes()
      if (mins < this.eixo.inicioMin || mins > this.eixo.fimMin) return null
      return ((mins - this.eixo.inicioMin) / this.spanMin) * 100
    },
  },
  mounted() {
    this._nowTimer = setInterval(() => {
      this.nowTick = Date.now()
    }, 60000)
  },
  beforeUnmount() {
    if (this._nowTimer) clearInterval(this._nowTimer)
  },
  methods: {
    primeiroNome(nome) {
      if (!nome) return ''
      return String(nome).trim().split(/\s+/)[0]
    },
    isSlotTimePastToday(time) {
      void this.nowTick
      return isHorarioNoPassado(obterDataAtualISO(), time)
    },
    statusBarClass(card) {
      if (card.statusKey === 'urgencia') return 'bg-red-500'
      if (card.statusKey === 'atendimento') return 'bg-violet-600'
      if (card.statusKey === 'chegou') return 'bg-amber-500'
      if (card.statusKey === 'realizada') return 'bg-slate-400'
      return card.corProf?.bar || 'bg-indigo-500'
    },
    blocosDoDia(dateIso) {
      const cards = this.cardsPorDia[dateIso] || []
      return cards.map((card) => ({
        card,
        ...posicaoBlocoNoEixo(card, this.eixo.inicioMin, this.eixo.fimMin),
      }))
    },
    tooltipCard(card) {
      return [
        card.horarioInicio,
        card.horarioFim ? `– ${card.horarioFim}` : '',
        card.paciente,
        card.profissional,
        card.statusLabel,
        card.procedimento,
        card.pago ? 'pago' : '',
      ]
        .filter(Boolean)
        .join(' · ')
    },
    onColunaClick(event, dateIso) {
      const el = event.currentTarget
      if (!el) return
      const rect = el.getBoundingClientRect()
      const y = Math.min(Math.max(event.clientY - rect.top, 0), rect.height - 1)
      const ratio = rect.height > 0 ? y / rect.height : 0
      const minutesAbs = this.eixo.inicioMin + ratio * this.spanMin
      const passo = this.eixo.passoMin || 30
      const snapped =
        this.eixo.inicioMin +
        Math.floor((minutesAbs - this.eixo.inicioMin) / passo) * passo
      const capped = Math.min(snapped, this.eixo.fimMin - Math.min(passo, 15))
      const time = minutesToTime(Math.max(capped, this.eixo.inicioMin))
      this.$emit('select-slot', { date: dateIso, time })
    },
  },
}
</script>
