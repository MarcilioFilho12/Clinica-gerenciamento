<template>
  <div class="w-full">
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative">
      <div v-if="showIcon && iconPosition !== 'right'"
        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
        <component :is="icon" class="w-5 h-5" />
      </div>

      <input :id="inputId" type="date" :value="currentFormattedValue" :min="min" :max="max" :disabled="disabled"
        :required="required" :class="inputClasses" @input="handleInput" @blur="handleBlur" @focus="handleFocus" />

      <div v-if="showIcon && iconPosition === 'right'"
        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
        <component :is="icon" class="w-5 h-5" />
      </div>
    </div>

    <p v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script>
export default {
  name: 'InputData',
  props: {
    modelValue: {
      type: [String, Date],
      default: ''
    },
    // Compatibilidade com Vue 2
    value: {
      type: [String, Date],
      default: ''
    },
    label: {
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
    min: {
      type: String,
      default: null
    },
    max: {
      type: String,
      default: null
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
    showIcon: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    inputId() {
      return `input-data-${Math.random().toString(36).substr(2, 9)}`
    },
    // Suporta tanto Vue 2 (value) quanto Vue 3 (modelValue)
    currentValue() {
      return this.modelValue !== undefined ? this.modelValue : this.value
    },
    inputClasses() {
      const baseClasses = 'block w-full border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors'

      const paddingClasses = this.showIcon && this.icon && this.iconPosition === 'left'
        ? 'pl-10 pr-3 py-2'
        : this.showIcon && this.icon && this.iconPosition === 'right'
          ? 'pl-3 pr-10 py-2'
          : 'px-3 py-2'

      const stateClasses = this.error
        ? 'border-red-300 focus:ring-red-500 focus:border-red-500'
        : 'border-gray-300'

      const disabledClasses = this.disabled
        ? 'bg-gray-100 cursor-not-allowed opacity-60'
        : 'bg-white'

      return `${baseClasses} ${paddingClasses} ${stateClasses} ${disabledClasses}`
    },
    // Formata o valor para o formato YYYY-MM-DD (formato esperado pelo input type="date")
    currentFormattedValue() {
      const val = this.currentValue
      if (!val) return ''

      // Se já está no formato YYYY-MM-DD
      if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(val)) {
        return val
      }

      // Se é uma data ISO completa (com hora)
      if (typeof val === 'string' && val.includes('T')) {
        return val.split('T')[0]
      }

      // Se é um objeto Date
      if (val instanceof Date) {
        const year = val.getFullYear()
        const month = String(val.getMonth() + 1).padStart(2, '0')
        const day = String(val.getDate()).padStart(2, '0')
        return `${year}-${month}-${day}`
      }

      // Tenta converter string para Date
      if (typeof val === 'string') {
        const dateObj = new Date(val)
        if (!isNaN(dateObj.getTime())) {
          const year = dateObj.getFullYear()
          const month = String(dateObj.getMonth() + 1).padStart(2, '0')
          const day = String(dateObj.getDate()).padStart(2, '0')
          return `${year}-${month}-${day}`
        }
      }

      return ''
    }
  },
  methods: {
    handleInput(event) {
      // O valor já vem no formato YYYY-MM-DD do input type="date"
      const value = event.target.value
      // Emite para Vue 3
      this.$emit('update:modelValue', value)
      // Emite para Vue 2 (compatibilidade)
      this.$emit('input', value)
    },
    handleBlur(event) {
      this.$emit('blur', event)
    },
    handleFocus(event) {
      this.$emit('focus', event)
    }
  }
}
</script>
