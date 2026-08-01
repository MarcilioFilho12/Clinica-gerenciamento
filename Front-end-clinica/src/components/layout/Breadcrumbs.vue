<template>
  <nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2">
      <li v-for="(item, index) in items" :key="index" class="flex items-center">
        <!-- Separador (exceto no primeiro item) -->
        <span v-if="index > 0" class="mx-2 text-gray-400" aria-hidden="true">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
              d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
              clip-rule="evenodd" />
          </svg>
        </span>

        <!-- Item do breadcrumb -->
        <div class="flex items-center">
          <!-- Link (se tiver 'to') -->
          <router-link v-if="item.to" :to="item.to"
            class="text-sm font-medium text-gray-500 hover:text-blue-700 transition-colors">
            {{ item.label }}
          </router-link>

          <!-- Item atual (sem link) -->
          <span v-else class="text-sm font-medium text-gray-900" aria-current="page">
            {{ item.label }}
          </span>
        </div>
      </li>
    </ol>
  </nav>
</template>

<script>
export default {
  name: 'Breadcrumbs',
  props: {
    items: {
      type: Array,
      required: true,
      validator: (value) => {
        return value.every(item =>
          typeof item === 'object' &&
          item !== null &&
          'label' in item &&
          typeof item.label === 'string' &&
          (!('to' in item) || typeof item.to === 'string')
        )
      }
    }
  }
}
</script>
