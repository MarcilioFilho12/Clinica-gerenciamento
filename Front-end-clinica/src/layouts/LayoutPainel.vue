<template>
  <div class="flex flex-col h-screen overflow-hidden">
    <!-- HEADER NO TOPO -->
    <div class="w-full flex-shrink-0">
      <Header />
    </div>

    <!-- SHELL: Sidebar | Conteúdo (CSS Grid) -->
    <div
      class="layout-body grid flex-1 min-h-0 min-w-0 overflow-hidden"
      :data-sidebar="isCompact ? 'collapsed' : 'expanded'"
      :style="{ gridTemplateColumns: `${sidebarWidth} 1fr` }"
    >
      <Sidebar />

      <div
        :class="[
          'min-w-0 overflow-y-auto overflow-x-hidden transition-[padding] duration-[250ms] ease-in-out',
          isAgendaRoute ? 'p-2 sm:p-3' : 'p-4',
        ]"
      >
        <main
          :class="[
            'min-h-full min-w-0 w-full transition-[margin] duration-[250ms] ease-in-out',
            isAgendaRoute
              ? 'mx-0 mt-1'
              : 'mx-4 sm:mx-8 lg:mx-10 mt-4 3xl:container 3xl:mx-auto',
          ]"
        >
          <router-view />
        </main>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Sidebar from '../components/layout/Sidebar.vue'
import Header from '../components/layout/Header.vue'
import { useSidebarLayout } from '../composables/useSidebarLayout.js'

export default {
  components: {
    Sidebar,
    Header,
  },
  setup() {
    const route = useRoute()
    const { isCompact, sidebarWidth } = useSidebarLayout()

    const isAgendaRoute = computed(() => route.path === '/agenda' || route.path.startsWith('/agenda/'))

    return {
      isCompact,
      sidebarWidth,
      isAgendaRoute,
    }
  },
}
</script>

<style scoped>
.layout-body {
  transition: grid-template-columns 250ms ease-in-out;
}
</style>
