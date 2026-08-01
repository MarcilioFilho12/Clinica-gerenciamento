<template>
  <div class="bg-white border-b border-gray-200 py-4 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Título e Descrição -->
        <div class="flex-1">
          <!-- Breadcrumbs (se tiver items ou slot) -->
          <div v-if="(showBreadcrumbs && breadcrumbs && breadcrumbs.length > 0) || $slots.breadcrumbs" class="mb-2">
            <Breadcrumbs v-if="showBreadcrumbs && breadcrumbs && breadcrumbs.length > 0" :items="breadcrumbs" />
            <slot v-else name="breadcrumbs"></slot>
          </div>

          <!-- Título -->
          <div class="flex items-center space-x-4">
            <div v-if="icon" class="flex-shrink-0">
              <component :is="icon" :class="['w-10 h-10 text-white rounded-lg p-2', iconBgColorClass]" />
            </div>
            <div class="flex-1">
              <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
                {{ title }}
              </h1>
              <p v-if="description" class="mt-1 text-sm text-gray-500">
                {{ description }}
              </p>
            </div>
          </div>

          <!-- Slot para conteúdo adicional -->
          <div v-if="$slots.default" class="mt-4">
            <slot></slot>
          </div>
        </div>

        <!-- Ações (botões no lado direito) -->
        <div v-if="$slots.actions" class="flex-shrink-0">
          <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
            <slot name="actions"></slot>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Breadcrumbs from './Breadcrumbs.vue'

/**
 * Componente PageHeader
 * 
 * Aceita ícones do Hero Icons (@heroicons/vue)
 * 
 * @example
 * // Usando Hero Icons
 * import { ClockIcon } from '@heroicons/vue/24/outline'
 * 
 * <PageHeader 
 *   title="Fila de Espera" 
 *   description="Gerenciamento de pacientes"
 *   :icon="ClockIcon"
 * />
 */
export default {
  name: 'PageHeader',
  components: {
    Breadcrumbs
  },
  props: {
    title: {
      type: String,
      required: true
    },
    description: {
      type: String,
      default: ''
    },
    showBreadcrumbs: {
      type: Boolean,
      default: false
    },
    breadcrumbs: {
      type: Array,
      default: null,
      validator: (value) => {
        if (value === null) return true
        return value.every(item =>
          typeof item === 'object' &&
          item !== null &&
          'label' in item &&
          typeof item.label === 'string' &&
          (!('to' in item) || typeof item.to === 'string')
        )
      }
    },
    /**
     * Ícone do Hero Icons (@heroicons/vue)
     * Exemplo: import { ClockIcon } from '@heroicons/vue/24/outline'
     * e passar :icon="ClockIcon"
     */
    icon: {
      type: [Object, Function],
      default: null
    },
    /**
     * Cor de fundo do ícone
     * Valores aceitos: 'blue', 'gray', 'green', 'red', 'yellow', 'purple', 'indigo', 'pink'
     */
    iconBgColor: {
      type: String,
      default: 'blue',
      validator: (value) => ['blue', 'gray', 'green', 'red', 'yellow', 'purple', 'indigo', 'pink'].includes(value)
    }
  },
  computed: {
    iconBgColorClass() {
      const colors = {
        blue: 'bg-blue-600',
        gray: 'bg-gray-600',
        green: 'bg-green-600',
        red: 'bg-red-600',
        yellow: 'bg-yellow-600',
        purple: 'bg-purple-600',
        indigo: 'bg-indigo-600',
        pink: 'bg-pink-600'
      }
      return colors[this.iconBgColor] || colors.blue
    }
  }
}
</script>
