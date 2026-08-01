<template>
  <div class="w-full">
    <label v-if="label" :for="textareaId" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative">
      <textarea :id="textareaId" :value="currentValue" :placeholder="placeholder" :disabled="disabled" :required="required"
        :rows="rows" :maxlength="maxLength" :class="textareaClasses" :style="resizeStyle" @input="handleInput"
        @blur="handleBlur" @focus="handleFocus"></textarea>
    </div>

    <div class="flex items-center justify-between mt-1">
      <p v-if="error" class="text-sm text-red-600">
        {{ error }}
      </p>
      <p v-else-if="showCharCount" :class="charCountClasses" class="text-sm">
        {{ charCount }} / {{ maxLength }}
      </p>
      <span v-else></span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BaseTextarea',
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
    rows: {
      type: Number,
      default: 4
    },
    maxLength: {
      type: Number,
      default: null
    },
    disabled: {
      type: Boolean,
      default: false
    },
    required: {
      type: Boolean,
      default: false
    },
    resize: {
      type: String,
      default: 'vertical',
      validator: (value) => ['none', 'both', 'horizontal', 'vertical'].includes(value)
    }
  },
  computed: {
    textareaId() {
      return `base-textarea-${Math.random().toString(36).substr(2, 9)}`
    },
    // Suporta tanto Vue 2 (value) quanto Vue 3 (modelValue)
    currentValue() {
      return this.modelValue !== undefined ? this.modelValue : this.value
    },
    textareaClasses() {
      const baseClasses = 'block w-full border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors'

      const paddingClasses = 'px-3 py-2'

      const stateClasses = this.error
        ? 'border-red-300 focus:ring-red-500 focus:border-red-500'
        : 'border-gray-300'

      const disabledClasses = this.disabled
        ? 'bg-gray-100 cursor-not-allowed opacity-60'
        : 'bg-white'

      return `${baseClasses} ${paddingClasses} ${stateClasses} ${disabledClasses}`
    },
    resizeStyle() {
      return {
        resize: this.resize
      }
    },
    charCount() {
      return this.currentValue ? this.currentValue.length : 0
    },
    showCharCount() {
      return this.maxLength !== null && this.maxLength > 0
    },
    charCountClasses() {
      if (!this.maxLength) return 'text-gray-500'

      const percentage = (this.charCount / this.maxLength) * 100

      if (percentage >= 100) {
        return 'text-red-600 font-semibold'
      } else if (percentage >= 90) {
        return 'text-orange-600'
      } else {
        return 'text-gray-500'
      }
    }
  },
  methods: {
    handleInput(event) {
      let newValue = event.target.value

      if (this.maxLength && newValue.length > this.maxLength) {
        newValue = newValue.substring(0, this.maxLength)
        event.target.value = newValue
      }

      // Emite para Vue 3
      this.$emit('update:modelValue', newValue)
      // Emite para Vue 2 (compatibilidade)
      this.$emit('input', newValue)
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
