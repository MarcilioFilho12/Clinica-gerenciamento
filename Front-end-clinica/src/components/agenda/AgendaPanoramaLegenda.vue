<template>
  <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600">
    <span class="font-medium text-gray-700">Legenda:</span>
    <span
      v-for="item in legenda"
      :key="item.key"
      class="inline-flex items-center gap-1.5"
    >
      <span :class="['inline-block h-2.5 w-2.5 rounded-sm', item.class]" />
      {{ item.label }}
    </span>
  </div>
</template>

<script>
import { LEGENDA_PANORAMA } from '../../utils/agendaPanorama.js'

export default {
  name: 'AgendaPanoramaLegenda',
  data() {
    return { legenda: LEGENDA_PANORAMA }
  },
}
</script>
