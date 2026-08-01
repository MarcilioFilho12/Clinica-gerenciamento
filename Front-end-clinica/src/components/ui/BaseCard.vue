<template>
  <div :class="cardClasses">
    <!-- Header -->
    <div v-if="title || $slots.header" :class="headerClasses">
      <slot name="header">
        <h3 v-if="title" class="text-lg font-semibold text-gray-900">
          {{ title }}
        </h3>
      </slot>
    </div>

    <!-- Conteúdo Principal -->
    <div :class="contentClasses">
      <slot></slot>
    </div>

    <!-- Footer -->
    <div v-if="$slots.footer" :class="footerClasses">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BaseCard',
  props: {
    title: {
      type: String,
      default: ''
    },
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'outlined', 'elevated'].includes(value)
    },
    padding: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    }
  },
  computed: {
    cardClasses() {
      const baseClasses = 'bg-white rounded-lg transition-all'

      const variantClasses = {
        default: 'shadow-sm border border-gray-200',
        outlined: 'border-2 border-gray-300',
        elevated: 'shadow-md border border-gray-200'
      }

      return `${baseClasses} ${variantClasses[this.variant]}`
    },
    headerClasses() {
      const paddingClasses = {
        sm: 'px-3 py-2',
        md: 'px-6 py-4',
        lg: 'px-8 py-6'
      }

      return `${paddingClasses[this.padding]} border-b border-gray-200`
    },
    contentClasses() {
      const paddingClasses = {
        sm: 'p-3',
        md: 'p-6',
        lg: 'p-8'
      }

      return paddingClasses[this.padding]
    },
    footerClasses() {
      const paddingClasses = {
        sm: 'px-3 py-2',
        md: 'px-6 py-4',
        lg: 'px-8 py-6'
      }

      return `${paddingClasses[this.padding]} border-t border-gray-200`
    }
  }
}
</script>
