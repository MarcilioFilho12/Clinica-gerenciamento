<template>
  <div class="w-full relative">
    <BaseInput v-model="searchValue" type="text" :label="label" :placeholder="placeholder" :required="required"
      :disabled="disabled" :error="error" @focus="handleFocus" @blur="handleBlur" />

    <!-- Loading indicator -->
    <div v-if="loading" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none mt-7">
      <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
    </div>

    <!-- Dropdown com sugestões -->
    <div v-if="showDropdown && items.length > 0"
      class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
      <div v-for="(item, index) in items" :key="getItemKeyValue(item, index)" @click="selectItem(item)"
        class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0">
        <slot name="item" :item="item">
          <div class="font-medium text-sm text-gray-900">{{ getItemLabelValue(item) }}</div>
          <div v-if="getItemSubtitleValue(item)" class="text-xs text-gray-500">
            {{ getItemSubtitleValue(item) }}
          </div>
        </slot>
      </div>
    </div>

    <!-- Botão limpar -->
    <button v-if="selectedItem" @click="clearSelection" type="button"
      class="absolute inset-y-0 right-8 flex items-center pr-2 text-gray-400 hover:text-gray-600 z-10 mt-7">
      <X class="w-4 h-4" />
    </button>
  </div>
</template>

<script>
import BaseInput from './BaseInput.vue'
import { X } from 'lucide-vue-next'

export default {
  name: 'TypeaheadInput',
  components: {
    BaseInput,
    X
  },
  props: {
    value: {
      type: String,
      default: ''
    },
    label: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: 'Digite para buscar...'
    },
    required: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    error: {
      type: String,
      default: ''
    },
    // Função para buscar itens (deve retornar Promise<Array>)
    searchFunction: {
      type: Function,
      required: true
    },
    // Função para obter a chave única do item
    getItemKey: {
      type: Function,
      default: (item, index) => item.id || index
    },
    // Função para obter o label do item
    getItemLabel: {
      type: Function,
      default: (item) => item.nome || item.name || item.label || String(item)
    },
    // Função para obter o subtítulo do item (opcional)
    getItemSubtitle: {
      type: Function,
      default: null
    },
    // Valor do item selecionado
    selectedItem: {
      type: Object,
      default: null
    },
    // Mínimo de caracteres para iniciar busca
    minChars: {
      type: Number,
      default: 2
    },
    // Tempo de debounce em ms
    debounceMs: {
      type: Number,
      default: 300
    },
    // Delay para fechar dropdown no blur (ms)
    blurDelay: {
      type: Number,
      default: 200
    }
  },
  data() {
    return {
      searchValue: this.value || '',
      items: [],
      loading: false,
      showDropdown: false,
      debounceTimer: null,
      ignoreNextWatch: false
    }
  },
  watch: {
    value: {
      handler(newValue) {
        if (newValue !== this.searchValue) {
          this.searchValue = newValue
        }
      },
      immediate: true
    },
    selectedItem: {
      handler(newItem) {
        if (newItem) {
          this.searchValue = this.getItemLabelValue(newItem)
          this.showDropdown = false
          this.items = []
        }
      },
      immediate: true
    },
    searchValue: {
      handler(newValue, oldValue) {
        clearTimeout(this.debounceTimer)

        // Ignorar se foi definido programaticamente
        if (this.ignoreNextWatch) {
          this.ignoreNextWatch = false
          return
        }

        // Não buscar se o valor não mudou ou é muito curto
        if (newValue === oldValue || !newValue || newValue.length < this.minChars) {
          this.items = []
          this.showDropdown = false
          this.$emit('input', newValue)
          return
        }

        // Não buscar se o valor corresponde exatamente ao item já selecionado
        if (this.selectedItem && newValue === this.getItemLabelValue(this.selectedItem)) {
          this.items = []
          this.showDropdown = false
          this.$emit('input', newValue)
          return
        }

        // Se o usuário está digitando algo diferente do item selecionado, limpar seleção
        if (this.selectedItem && newValue !== this.getItemLabelValue(this.selectedItem)) {
          this.$emit('clear')
        }

        // Emitir evento input para o componente pai
        this.$emit('input', newValue)

        // Debounce da busca
        this.debounceTimer = setTimeout(() => {
          this.performSearch(newValue)
        }, this.debounceMs)
      }
    }
  },
  methods: {
    handleInput(value) {
      // Garantir que recebemos o valor, não o evento
      let inputValue = ''
      
      if (typeof value === 'string' || typeof value === 'number') {
        inputValue = String(value)
      } else if (value && typeof value === 'object') {
        // Se for um evento, extrair o valor
        if (value.target && value.target.value !== undefined) {
          inputValue = String(value.target.value)
        } else if (value.value !== undefined) {
          inputValue = String(value.value)
        } else {
          inputValue = ''
        }
      } else {
        inputValue = String(value || '')
      }

      this.searchValue = inputValue
      this.$emit('input', this.searchValue)
    },
    handleFocus() {
      // Não mostrar dropdown se já há um item selecionado e o texto corresponde ao label dele
      if (this.selectedItem && this.searchValue === this.getItemLabelValue(this.selectedItem)) {
        this.showDropdown = false
        this.items = []
        return
      }

      if (this.searchValue && this.searchValue.length >= this.minChars) {
        this.showDropdown = this.items.length > 0
        if (this.items.length === 0 && (!this.selectedItem || this.searchValue !== this.getItemLabelValue(this.selectedItem))) {
          this.performSearch(this.searchValue)
        }
      }
    },
    handleBlur() {
      // Delay para permitir clique no item do dropdown
      setTimeout(() => {
        this.showDropdown = false
      }, this.blurDelay)
    },
    async performSearch(term) {
      // Garantir que term é uma string
      let searchTerm = ''
      if (typeof term === 'string') {
        searchTerm = term
      } else if (typeof term === 'number') {
        searchTerm = String(term)
      } else if (term && typeof term === 'object') {
        // Se for um evento, extrair o valor
        if (term.target && term.target.value !== undefined) {
          searchTerm = String(term.target.value)
        } else if (term.value !== undefined) {
          searchTerm = String(term.value)
        } else {
          console.warn('performSearch recebeu um objeto inesperado:', term)
          return
        }
      } else {
        searchTerm = String(term || '')
      }

      if (!searchTerm || searchTerm.length < this.minChars) {
        this.items = []
        this.showDropdown = false
        return
      }

      try {
        this.loading = true
        const results = await this.searchFunction(searchTerm)
        this.items = Array.isArray(results) ? results : []
        this.showDropdown = this.items.length > 0
      } catch (err) {
        console.error('Erro ao buscar itens:', err)
        this.items = []
        this.showDropdown = false
      } finally {
        this.loading = false
      }
    },
    getItemKeyValue(item, index) {
      return this.getItemKey(item, index)
    },
    getItemLabelValue(item) {
      return this.getItemLabel(item)
    },
    getItemSubtitleValue(item) {
      return this.getItemSubtitle ? this.getItemSubtitle(item) : null
    },
    selectItem(item) {
      this.ignoreNextWatch = true
      this.searchValue = this.getItemLabelValue(item)
      this.showDropdown = false
      this.items = []
      this.$emit('select', item)
      this.$emit('input', this.searchValue)
    },
    clearSelection() {
      this.ignoreNextWatch = true
      this.searchValue = ''
      this.showDropdown = false
      this.items = []
      this.$emit('clear')
      this.$emit('input', '')
    }
  }
}
</script>
