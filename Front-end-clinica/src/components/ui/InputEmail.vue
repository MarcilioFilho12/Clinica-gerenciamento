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

      <input :id="inputId" type="email" :value="currentValue" :placeholder="placeholder" :disabled="disabled"
        :required="required" :autocomplete="autocomplete" :class="inputClasses" @input="handleInput" @blur="handleBlur"
        @focus="handleFocus" />

      <div v-if="icon && iconPosition === 'right'" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
        <component :is="icon" class="w-5 h-5" />
      </div>
    </div>

    <p v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </p>
    <p v-else-if="validate && currentValue && !isValidEmail" class="mt-1 text-sm text-red-600">
      Por favor, insira um email válido
    </p>
  </div>
</template>

<script>
export default {
  name: 'InputEmail',
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
      default: 'exemplo@email.com'
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
    autocomplete: {
      type: String,
      default: 'email'
    }
  },
  computed: {
    inputId() {
      return `input-email-${Math.random().toString(36).substr(2, 9)}`
    },
    // Suporta tanto Vue 2 (value) quanto Vue 3 (modelValue)
    currentValue() {
      return this.modelValue !== undefined ? this.modelValue : this.value
    },
    inputClasses() {
      const baseClasses = 'block w-full border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors'

      const paddingClasses = this.icon && this.iconPosition === 'left'
        ? 'pl-10 pr-3 py-2'
        : this.icon && this.iconPosition === 'right'
          ? 'pl-3 pr-10 py-2'
          : 'px-3 py-2'

      const stateClasses = this.error || (this.validate && this.currentValue && !this.isValidEmail)
        ? 'border-red-300 focus:ring-red-500 focus:border-red-500'
        : 'border-gray-300'

      const disabledClasses = this.disabled
        ? 'bg-gray-100 cursor-not-allowed opacity-60'
        : 'bg-white'

      return `${baseClasses} ${paddingClasses} ${stateClasses} ${disabledClasses}`
    },
    isValidEmail() {
      const val = this.currentValue
      if (!val || !this.validate) return true

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return emailRegex.test(val)
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
