<template>
  <BaseCard padding="sm" class="mb-6 pb-1">
    <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
      <!-- Input de Busca -->
      <div class="flex-1 w-full lg:w-auto">
        <BaseInput :value="searchValue" type="text" :label="searchLabel" :placeholder="searchPlaceholder"
          :icon="PhMagnifyingGlass" icon-position="left" :class="searchInputClass" @input="handleSearchInput" />
      </div>

      <!-- Botões no lado direito -->
      <div class="flex items-center gap-3">
        <!-- Botão de Filtros (sempre presente) -->
        <BaseButton :variant="filtersActive ? 'primary' : 'outline'" :icon="PhFunnel" icon-position="left"
          @click="handleFiltersClick" class="h-10 mt-7">
          Filtros
        </BaseButton>

        <!-- Slot para botão customizado -->
        <slot name="action-button">
          <!-- Se não houver slot, não renderiza nada -->
        </slot>
      </div>
    </div>
  </BaseCard>
</template>

<script>
import { PhMagnifyingGlass, PhFunnel } from '@phosphor-icons/vue'

export default {
  name: 'SearchBar',
  components: {
    PhMagnifyingGlass,
    PhFunnel
  },
  props: {
    // Valor do input de busca
    searchValue: {
      type: String,
      default: ''
    },
    // Label do input de busca
    searchLabel: {
      type: String,
      default: 'Buscar'
    },
    // Placeholder do input de busca
    searchPlaceholder: {
      type: String,
      default: 'Digite para buscar...'
    },
    // Classe customizada para o input de busca
    searchInputClass: {
      type: String,
      default: 'w-full sm:w-64'
    },
    // Indica se há filtros ativos (para destacar o botão)
    filtersActive: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      PhMagnifyingGlass,
      PhFunnel
    }
  },
  methods: {
    handleSearchInput(value) {
      this.$emit('input', value)
      this.$emit('search', value)
    },
    handleFiltersClick() {
      this.$emit('filters-click')
    }
  }
}
</script>
