<template>
  <header class="bg-white/95 backdrop-blur-sm border-b border-gray-200 shadow-sm sticky top-0 z-50 py-4">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Logo e Título (Esquerda) -->
        <div class="flex items-center space-x-3 sm:space-x-4 flex-shrink-0">
          <img
            :src="clinic.logoUrl || defaultLogo"
            :alt="clinic.nome"
            class="h-12 w-auto opacity-90"
          />
          <div class="hidden sm:block">
            <h1 class="text-base sm:text-lg font-semibold text-gray-900 tracking-tight">
              {{ clinic.nome }}
            </h1>
            <p class="text-xs text-gray-500 font-normal">
              Sistema de Gestão · Marag
            </p>
          </div>
        </div>

        <!-- Informações do Sistema (Direita) -->
        <div class="flex items-center space-x-4 sm:space-x-6">
          <!-- Data e Hora -->
          <div class="hidden md:flex flex-col items-end text-right">
            <p class="text-sm font-medium text-gray-700 leading-tight">
              {{ dataAtual }}
            </p>
            <p class="text-xs text-gray-500 leading-tight">
              {{ horaAtual }}
            </p>
          </div>

          <!-- User Badge -->
          <div
            class="bg-gradient-to-r from-blue-50 to-green-50 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full border border-blue-100 shadow-sm">
            <span class="text-xs sm:text-sm font-medium text-blue-700">
              {{ auth.profileName }}
            </span>
          </div>

          <!-- Botão Logout -->
          <button @click="logout"
            class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-md transition-colors duration-200 border border-gray-200">
            Sair
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useClinicStore } from '../../stores/clinic'
import { useRouter } from 'vue-router'
import defaultLogo from '../../img/utils/sua-logo.png'

const dataAtual = ref('')
const horaAtual = ref('')
const router = useRouter()
let intervalId = null

const auth = useAuthStore()
const clinic = useClinicStore()

const logout = () => {
  auth.clear()
  router.push('/')
}

const atualizarDataHora = () => {
  const agora = new Date()

  dataAtual.value = agora.toLocaleDateString('pt-BR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })

  horaAtual.value = agora.toLocaleTimeString('pt-BR', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(async () => {
  atualizarDataHora()
  intervalId = setInterval(atualizarDataHora, 60000)
  clinic.applyCssVars()
  if (clinic.slug && !clinic.branding) {
    try {
      await clinic.loadBranding()
    } catch (_) {}
  }
})

onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId)
  }
})
</script>

<style scoped>
/* Estilos específicos do header */
header {
  transition: all 0.2s ease-in-out;
}

/* Hover suave no botão de logout */
button:hover {
  transform: translateY(-1px);
}
</style>