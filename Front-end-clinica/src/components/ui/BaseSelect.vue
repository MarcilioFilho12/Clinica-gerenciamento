<template>
  <div class="w-full">
    <label v-if="label" :for="selectId" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative">
      <select :id="selectId" :value="currentValue" :disabled="disabled" :required="required" :class="selectClasses"
        @change="handleChange" @blur="handleBlur" @focus="handleFocus">
        <option v-for="(option, index) in options" :key="getOptionKey(option, index)" :value="option.value"
          :disabled="option.disabled || false">
          {{ option.label }}
        </option>
      </select>
    </div>

    <p v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script>
export default {
  name: 'BaseSelect',
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
    options: {
      type: Array,
      required: true,
      validator: (value) => {
        return value.every(option =>
          typeof option === 'object' &&
          option !== null &&
          'value' in option &&
          'label' in option
        )
      }
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
    }
  },
  computed: {
    selectId() {
      return `base-select-${Math.random().toString(36).substr(2, 9)}`
    },
    // Suporta tanto Vue 2 (value) quanto Vue 3 (modelValue)
    currentValue() {
      return this.modelValue !== undefined ? this.modelValue : this.value
    },
    selectClasses() {
      const baseClasses = 'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors appearance-none bg-white'

      const stateClasses = this.error
        ? 'border-red-300 focus:ring-red-500 focus:border-red-500'
        : 'border-gray-300'

      const disabledClasses = this.disabled
        ? 'bg-gray-100 cursor-not-allowed opacity-60'
        : ''

      return `${baseClasses} ${stateClasses} ${disabledClasses}`
    }
  },
  methods: {
    getOptionKey(option, index) {
      // Gerar uma chave única para cada opção
      // Se o value for vazio, usar o index e label como parte da chave
      if (option.value === '' || option.value === null || option.value === undefined) {
        return `option-empty-${index}-${option.label}`
      }
      return `option-${option.value}`
    },
    handleChange(event) {
      let value = event.target.value

      // Preservar o tipo original do valor (número ou string)
      if (value !== '') {
        // Encontrar a opção correspondente para preservar o tipo
        const option = this.options.find(opt => String(opt.value) === String(value))
        if (option) {
          // Usar o valor original da opção para preservar o tipo
          value = option.value
        } else if (!isNaN(value) && value !== null && value !== '') {
          // Se não encontrou a opção mas é um número válido, tentar converter
          const numericOption = this.options.find(opt => String(opt.value) === String(value) && typeof opt.value === 'number')
          if (numericOption) {
            value = Number(value)
          }
        }
      } else {
        // Garantir que valor vazio seja sempre string vazia
        value = ''
      }

      // Emite para Vue 3
      this.$emit('update:modelValue', value)
      // Emite para Vue 2 (compatibilidade)
      this.$emit('input', value)
      this.$emit('change', value)
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

<style scoped>
select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}

select:disabled {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
}
</style>
