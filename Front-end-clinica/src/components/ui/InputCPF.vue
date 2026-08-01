<template>
  <div class="w-full">
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative">
      <input :id="inputId" type="text" :value="currentValue" :placeholder="placeholder" :disabled="disabled"
        :required="required" :class="inputClasses" v-maska="'###.###.###-##'" @input="handleInput" @blur="handleBlur"
        @focus="handleFocus" />
    </div>

    <p v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </p>
    <p v-else-if="validate && currentValue && !isValidCPF" class="mt-1 text-sm text-red-600">
      CPF inválido
    </p>
  </div>
</template>

<script>
export default {
  name: 'InputCPF',
  props: {
    modelValue: {
      type: String,
      default: ''
    },
    // Compatibilidade com Vue 2
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
      default: '000.000.000-00'
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
    validate: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    inputId() {
      return `input-cpf-${Math.random().toString(36).substr(2, 9)}`
    },
    // Suporta tanto Vue 2 (value) quanto Vue 3 (modelValue)
    currentValue() {
      return this.modelValue !== undefined ? this.modelValue : this.value
    },
    inputClasses() {
      const baseClasses = 'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors'

      const stateClasses = this.error || (this.validate && this.currentValue && !this.isValidCPF)
        ? 'border-red-300 focus:ring-red-500 focus:border-red-500'
        : 'border-gray-300'

      const disabledClasses = this.disabled
        ? 'bg-gray-100 cursor-not-allowed opacity-60'
        : 'bg-white'

      return `${baseClasses} ${stateClasses} ${disabledClasses}`
    },
    isValidCPF() {
      const val = this.currentValue
      if (!val || !this.validate) return true

      const cpf = val.replace(/\D/g, '')
      if (cpf.length !== 11) return false
      if (/^(\d)\1{10}$/.test(cpf)) return false
      let sum = 0
      let remainder

      for (let i = 1; i <= 9; i++) {
        sum += parseInt(cpf.substring(i - 1, i)) * (11 - i)
      }
      remainder = (sum * 10) % 11
      if (remainder === 10 || remainder === 11) remainder = 0
      if (remainder !== parseInt(cpf.substring(9, 10))) return false

      sum = 0
      for (let i = 1; i <= 10; i++) {
        sum += parseInt(cpf.substring(i - 1, i)) * (12 - i)
      }
      remainder = (sum * 10) % 11
      if (remainder === 10 || remainder === 11) remainder = 0
      if (remainder !== parseInt(cpf.substring(10, 11))) return false

      return true
    }
  },
  methods: {
    handleInput(event) {
      // A máscara já é aplicada pelo v-maska
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
