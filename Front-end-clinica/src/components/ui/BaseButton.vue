<template>
  <button :type="type" :disabled="disabled || loading" :class="buttonClasses" @click="handleClick">
    <span v-if="loading" class="inline-flex items-center">
      <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
        </path>
      </svg>
      <span v-if="!hideLoadingText">Carregando...</span>
    </span>
    <span v-else class="inline-flex items-center">
      <component v-if="icon && iconPosition === 'left'" :is="icon" :class="iconSizeClasses" class="mr-2" />
      <slot></slot>
      <component v-if="icon && iconPosition === 'right'" :is="icon" :class="iconSizeClasses" class="ml-2" />
    </span>
  </button>
</template>

<script>
export default {
  name: 'BaseButton',
  props: {
    variant: {
      type: String,
      default: 'primary',
      validator: (value) => ['primary', 'secondary', 'danger', 'outline', 'ghost'].includes(value)
    },
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    type: {
      type: String,
      default: 'button',
      validator: (value) => ['button', 'submit', 'reset'].includes(value)
    },
    disabled: {
      type: Boolean,
      default: false
    },
    loading: {
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
    hideLoadingText: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    buttonClasses() {
      const baseClasses = 'inline-flex items-center justify-center font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2'

      // Variantes
      const variantClasses = {
        primary: 'bg-black text-white hover:bg-gray-800 focus:ring-black',
        secondary: 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
        danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        outline: 'border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-gray-500',
        warning: 'bg-[#D4AF37] text-white hover:bg-[#BF9F30] focus:ring-[#D4AF37]',
        ghost: 'text-gray-700 bg-transparent hover:bg-gray-100 focus:ring-gray-500'
      }

      // Tamanhos
      const sizeClasses = {
        sm: 'px-3 py-1.5 text-sm',
        md: 'px-6 py-3 text-base',
        lg: 'px-8 py-4 text-lg'
      }

      // Estados
      const disabledClasses = (this.disabled || this.loading)
        ? 'opacity-50 cursor-not-allowed'
        : 'cursor-pointer'

      return `${baseClasses} ${variantClasses[this.variant]} ${sizeClasses[this.size]} ${disabledClasses}`
    },
    iconSizeClasses() {
      const sizeMap = {
        sm: 'w-4 h-4',
        md: 'w-5 h-5',
        lg: 'w-6 h-6'
      }
      return sizeMap[this.size]
    }
  },
  methods: {
    handleClick(event) {
      if (!this.disabled && !this.loading) {
        this.$emit('click', event)
      }
    }
  }
}
</script>
