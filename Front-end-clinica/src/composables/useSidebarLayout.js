import { computed, ref, readonly } from 'vue'

/** Larguras travadas do shell (CSS Grid). */
export const SIDEBAR_WIDTH_COLLAPSED = 72
export const SIDEBAR_WIDTH_EXPANDED = 280

const isCollapsed = ref(false)
const isHovered = ref(false)

/**
 * Estado compartilhado da sidebar de navegação.
 * Layout e Sidebar leem a mesma fonte — só CSS anima a largura (sem JS de layout).
 */
export function useSidebarLayout() {
  const isCompact = computed(() => isCollapsed.value && !isHovered.value)

  const sidebarWidthPx = computed(() =>
    isCompact.value ? SIDEBAR_WIDTH_COLLAPSED : SIDEBAR_WIDTH_EXPANDED
  )

  const sidebarWidth = computed(() => `${sidebarWidthPx.value}px`)

  function toggleSidebar() {
    isCollapsed.value = !isCollapsed.value
  }

  function setCollapsed(value) {
    isCollapsed.value = Boolean(value)
  }

  function setHovered(value) {
    isHovered.value = Boolean(value)
  }

  return {
    isCollapsed: readonly(isCollapsed),
    isHovered: readonly(isHovered),
    isCompact,
    sidebarWidthPx,
    sidebarWidth,
    toggleSidebar,
    setCollapsed,
    setHovered,
  }
}
