<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex justify-end"
      role="dialog"
      aria-modal="true"
      aria-labelledby="panorama-drawer-title"
    >
      <div class="absolute inset-0 bg-black/40" @click="$emit('close')" />

      <aside class="relative z-10 flex h-full w-full max-w-md flex-col bg-white shadow-xl">
        <header class="flex items-start justify-between gap-3 border-b border-gray-200 px-5 py-4">
          <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
              {{ card?.isPausa ? 'Pausa / bloqueio' : 'Consulta' }}
            </p>
            <h2 id="panorama-drawer-title" class="mt-1 truncate text-lg font-semibold text-gray-900">
              {{ card?.paciente || '—' }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
              {{ card?.horarioInicio }}
              <span v-if="card?.horarioFim"> – {{ card.horarioFim }}</span>
              <span v-if="card?.data"> · {{ formatDateShort(card.data) }}</span>
            </p>
          </div>
          <button
            type="button"
            class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700"
            aria-label="Fechar"
            @click="$emit('close')"
          >
            <X class="h-5 w-5" />
          </button>
        </header>

        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
          <div class="flex flex-wrap items-center gap-2">
            <span
              :class="[
                'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium border',
                card?.classes?.soft || 'bg-gray-100 text-gray-700',
              ]"
            >
              {{ statusLabel }}
            </span>
            <span
              v-if="card?.pago && !card?.isPausa"
              class="inline-flex items-center rounded-md bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-800 border border-emerald-200"
            >
              Pago
            </span>
          </div>

          <dl class="space-y-3 text-sm">
            <div v-if="card?.profissional">
              <dt class="text-gray-500">Profissional</dt>
              <dd class="font-medium text-gray-900">{{ card.profissional }}</dd>
            </div>
            <div v-if="card?.procedimento">
              <dt class="text-gray-500">{{ card?.isPausa ? 'Tipo' : 'Procedimento' }}</dt>
              <dd class="font-medium text-gray-900">{{ card.procedimento }}</dd>
            </div>
          </dl>
        </div>

        <footer class="border-t border-gray-200 px-5 py-4 space-y-2">
          <button
            v-if="podeConfirmarChegada"
            type="button"
            class="w-full rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700"
            @click="$emit('chegada')"
          >
            Confirmar chegada
          </button>
          <button
            v-if="podeEditar"
            type="button"
            class="w-full rounded-md bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
            @click="$emit('transferir')"
          >
            Transferir / Remarcar
          </button>
          <button
            v-if="podeEditar && !card?.isPausa"
            type="button"
            class="w-full rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 hover:bg-gray-50"
            @click="$emit('editar')"
          >
            Editar dados
          </button>
          <button
            v-if="podeCancelar"
            type="button"
            class="w-full rounded-md bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700"
            @click="$emit('cancelar')"
          >
            {{ card?.isPausa ? 'Remover pausa' : 'Cancelar consulta' }}
          </button>
          <button
            type="button"
            class="w-full rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 hover:bg-gray-50"
            @click="$emit('ir-dia')"
          >
            Ir ao dia
          </button>
          <button
            type="button"
            class="w-full rounded-md px-4 py-2 text-sm text-gray-500 hover:text-gray-800"
            @click="$emit('close')"
          >
            Fechar
          </button>
        </footer>
      </aside>
    </div>
  </Teleport>
</template>

<script>
import { X } from 'lucide-vue-next'
import { formatDateShort } from '../../utils/agendaDatas.js'
import { LEGENDA_PANORAMA, SITUACAO } from '../../utils/agendaPanorama.js'

export default {
  name: 'AgendaPanoramaDrawer',
  components: { X },
  props: {
    open: { type: Boolean, default: false },
    card: { type: Object, default: null },
  },
  emits: ['close', 'editar', 'transferir', 'chegada', 'cancelar', 'ir-dia'],
  computed: {
    statusLabel() {
      if (this.card?.isPausa) return 'Pausa'
      const key = this.card?.statusKey
      return LEGENDA_PANORAMA.find((i) => i.key === key)?.label || 'Agendada'
    },
    situacaoId() {
      return Number(this.card?.situacaoId)
    },
    podeEditar() {
      return this.situacaoId !== SITUACAO.EM_ATENDIMENTO && this.situacaoId !== SITUACAO.REALIZADA
    },
    podeCancelar() {
      return this.situacaoId !== SITUACAO.EM_ATENDIMENTO
        && this.situacaoId !== SITUACAO.REALIZADA
        && this.situacaoId !== SITUACAO.CANCELADA
    },
    podeConfirmarChegada() {
      if (!this.card || this.card.isPausa) return false
      if (this.card.chegadaEm) return false
      if (this.situacaoId === SITUACAO.EM_ATENDIMENTO || this.situacaoId === SITUACAO.REALIZADA) {
        return false
      }
      return true
    },
  },
  methods: {
    formatDateShort,
  },
}
</script>
