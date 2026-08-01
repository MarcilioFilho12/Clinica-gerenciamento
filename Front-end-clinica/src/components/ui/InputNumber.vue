<template>
  <div class="w-full">
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative">
      <div v-if="icon && iconPosition !== 'right'" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
        <component :is="icon" class="w-5 h-5" />
      </div>

      <input :id="inputId" type="text" :value="currentDisplayValue" :placeholder="placeholder" :disabled="disabled"
        :required="required" :class="inputClasses" @input="handleInput" @blur="handleBlur" @focus="handleFocus"
        @keydown="handleKeydown" />

      <div v-if="icon && iconPosition === 'right'" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
        <component :is="icon" class="w-5 h-5" />
      </div>
    </div>

    <p v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </p>
    <p v-else-if="validationError" class="mt-1 text-sm text-red-600">
      {{ validationError }}
    </p>
  </div>
</template>

<script>
export default {
  name: 'InputNumber',
  props: {
    modelValue: {
      type: [String, Number],
      default: ''
    },
    // Compatibilidade com Vue 2
    value: {
      type: [String, Number],
      default: ''
    },
    label: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: ''
    },
    error: {
      type: String,
      default: ''
    },
    disabled: {
      type: Boolean,
      default: false
    },
    required: {
      type: Boolean,
      default: false
    },
    icon: {
      type: [Object, Function],
      default: null
    },
    iconPosition: {
      type: String,
      default: 'left',
      validator: (value) => ['left', 'right'].includes(value)
    },
    min: {
      type: Number,
      default: null
    },
    max: {
      type: Number,
      default: null
    },
    step: {
      type: Number,
      default: 1
    },
    allowDecimal: {
      type: Boolean,
      default: false
    },
    allowNegative: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    inputId() {
      return `input-number-${Math.random().toString(36).substr(2, 9)}`
    },
    // Suporta tanto Vue 2 (value) quanto Vue 3 (modelValue)
    currentValue() {
      return this.modelValue !== undefined ? this.modelValue : this.value
    },
    currentDisplayValue() {
      const val = this.currentValue
      if (val === '' || val === null || val === undefined) {
        return ''
      }
      return String(val)
    },
    inputClasses() {
      const baseClasses = 'block w-full border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors'

      const paddingClasses = this.icon && this.iconPosition === 'left'
        ? 'pl-10 pr-3 py-2'
        : this.icon && this.iconPosition === 'right'
          ? 'pl-3 pr-10 py-2'
          : 'px-3 py-2'

      const stateClasses = this.error || this.validationError
        ? 'border-red-300 focus:ring-red-500 focus:border-red-500'
        : 'border-gray-300'

      const disabledClasses = this.disabled
        ? 'bg-gray-100 cursor-not-allowed opacity-60'
        : 'bg-white'

      return `${baseClasses} ${paddingClasses} ${stateClasses} ${disabledClasses}`
    },
    validationError() {
      const val = this.currentValue
      if (!val && val !== 0) {
        return ''
      }

      const numValue = this.parseValue(val)

      if (isNaN(numValue)) {
        return 'Valor inválido'
      }

      if (this.min !== null && numValue < this.min) {
        return `O valor mínimo é ${this.min}`
      }

      if (this.max !== null && numValue > this.max) {
        return `O valor máximo é ${this.max}`
      }

      return ''
    }
  },
  methods: {
    parseValue(value) {
      if (value === '' || value === null || value === undefined) {
        return NaN
      }

      // Remove tudo exceto números, ponto decimal e sinal negativo (se permitido)
      let cleaned = String(value).replace(/[^\d.-]/g, '')

      // Se não permite negativo, remove o sinal
      if (!this.allowNegative) {
        cleaned = cleaned.replace(/-/g, '')
      }

      // Se não permite decimal, remove o ponto
      if (!this.allowDecimal) {
        cleaned = cleaned.replace(/\./g, '')
      }

      // Garante apenas um ponto decimal
      const parts = cleaned.split('.')
      if (parts.length > 2) {
        cleaned = parts[0] + '.' + parts.slice(1).join('')
      }

      return cleaned === '' || cleaned === '-' ? NaN : parseFloat(cleaned)
    },
    formatValue(value) {
      const numValue = this.parseValue(value)

      if (isNaN(numValue)) {
        return ''
      }

      // Se não permite decimal, retorna como inteiro
      if (!this.allowDecimal) {
        return String(Math.floor(numValue))
      }

      return String(numValue)
    },
    handleInput(event) {
      let inputValue = event.target.value

      // Remove caracteres não numéricos (exceto ponto decimal e sinal negativo se permitidos)
      let cleaned = inputValue.replace(/[^\d.-]/g, '')

      // Se não permite negativo, remove o sinal
      if (!this.allowNegative) {
        cleaned = cleaned.replace(/-/g, '')
      }

      // Se não permite decimal, remove o ponto
      if (!this.allowDecimal) {
        cleaned = cleaned.replace(/\./g, '')
      }

      // Garante apenas um ponto decimal
      const parts = cleaned.split('.')
      if (parts.length > 2) {
        cleaned = parts[0] + '.' + parts.slice(1).join('')
      }

      // Garante que o sinal negativo está apenas no início
      if (this.allowNegative && cleaned.includes('-')) {
        cleaned = '-' + cleaned.replace(/-/g, '')
      }

      // Se o valor está vazio, emite string vazia
      if (cleaned === '' || cleaned === '-') {
        // Emite para Vue 3
        this.$emit('update:modelValue', '')
        // Emite para Vue 2 (compatibilidade)
        this.$emit('input', '')
        return
      }

      // Emite o valor formatado
      // Emite para Vue 3
      this.$emit('update:modelValue', cleaned)
      // Emite para Vue 2 (compatibilidade)
      this.$emit('input', cleaned)
    },
    handleKeydown(event) {
      // Permite: Backspace, Delete, Tab, Escape, Enter, Home, End, setas
      const allowedKeys = [
        'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
        'Home', 'End', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'
      ]

      if (allowedKeys.includes(event.key)) {
        return
      }

      // Permite Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
      if (event.ctrlKey || event.metaKey) {
        if (['a', 'c', 'v', 'x'].includes(event.key.toLowerCase())) {
          return
        }
      }

      // Permite números
      if (event.key >= '0' && event.key <= '9') {
        return
      }

      // Permite ponto decimal se permitido
      if (this.allowDecimal && event.key === '.') {
        const currentValue = event.target.value
        // Não permite múltiplos pontos
        if (!currentValue.includes('.')) {
          return
        }
      }

      // Permite sinal negativo se permitido e no início
      if (this.allowNegative && event.key === '-') {
        const currentValue = event.target.value
        const cursorPosition = event.target.selectionStart
        // Só permite no início
        if (cursorPosition === 0 && !currentValue.includes('-')) {
          return
        }
      }

      // Bloqueia todos os outros caracteres
      event.preventDefault()
    },
    handleBlur(event) {
      // Ao perder o foco, formata o valor final
      const formatted = this.formatValue(event.target.value)
      if (formatted !== event.target.value) {
        // Emite para Vue 3
        this.$emit('update:modelValue', formatted)
        // Emite para Vue 2 (compatibilidade)
        this.$emit('input', formatted)
      }
      this.$emit('blur', event)
    },
    handleFocus(event) {
      this.$emit('focus', event)
    }
  }
}
</script>
