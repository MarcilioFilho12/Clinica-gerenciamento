<template>
  <div class="w-full">
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative">
      <input :id="inputId" type="tel" :value="currentValue" :placeholder="currentPlaceholder" :disabled="disabled"
        :required="required" :class="inputClasses" v-maska="currentMask" @input="handleInput" @blur="handleBlur"
        @focus="handleFocus" />
    </div>

    <p v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script>
export default {
  name: 'InputTelefone',
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
    type: {
      type: String,
      default: 'auto',
      validator: (value) => ['celular', 'fixo', 'auto'].includes(value)
    }
  },
  computed: {
    inputId() {
      return `input-telefone-${Math.random().toString(36).substr(2, 9)}`
    },
    // Suporta tanto Vue 2 (value) quanto Vue 3 (modelValue)
    currentValue() {
      return this.modelValue !== undefined ? this.modelValue : this.value
    },
    inputClasses() {
      const baseClasses = 'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors'

      const stateClasses = this.error
        ? 'border-red-300 focus:ring-red-500 focus:border-red-500'
        : 'border-gray-300'

      const disabledClasses = this.disabled
        ? 'bg-gray-100 cursor-not-allowed opacity-60'
        : 'bg-white'

      return `${baseClasses} ${stateClasses} ${disabledClasses}`
    },
    telefoneType() {
      if (this.type !== 'auto') {
        return this.type
      }

      const digits = this.currentValue ? this.currentValue.replace(/\D/g, '') : ''

      if (digits.length === 0) {
        return 'celular'
      }

      if (digits.length <= 2) {
        return 'celular'
      }

      if (digits.length > 10) {
        return 'celular'
      }

      if (digits.length === 10) {
        return digits[2] === '9' ? 'celular' : 'fixo'
      }

      return 'celular'
    },
    currentMask() {
      return this.telefoneType === 'celular'
        ? '(##) #####-####'
        : '(##) ####-####'
    },
    currentPlaceholder() {
      if (this.placeholder) {
        return this.placeholder
      }
      return this.telefoneType === 'celular'
        ? '(00) 00000-0000'
        : '(00) 0000-0000'
    }
  },
  methods: {
    handleInput(event) {
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
