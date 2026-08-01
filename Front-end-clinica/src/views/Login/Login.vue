<template>
  <div
    class="min-h-screen relative bg-gradient-to-br from-blue-50 via-white to-gray-50 flex items-center justify-center p-4">
    <!-- Background fixo com imagens -->
    <div class="fixed inset-0 pointer-events-none">
      <!-- Imagem no canto superior esquerdo -->
      <div class="absolute top-0 left-0 opacity-5">
        <img src="../../img/logos/marag-background.png" alt="Background decorativo"
          class="w-[600px] h-[600px] object-contain" />
      </div>

      <!-- Imagem no canto inferior direito -->
      <div class="absolute bottom-0 right-0 opacity-5 transform rotate-180">
        <img src="../../img/logos/marag-background.png" alt="Background decorativo"
          class="w-[600px] h-[600px] object-contain" />
      </div>
    </div>

    <!-- Container principal -->
    <div class="w-full max-w-md relative z-10">
      <!-- Card de login -->
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-100 p-8">
        <!-- Logo e branding -->
        <div class="text-center mb-8">
          <div class="flex justify-center mb-4">
            <img src="../../img/logos/marag-logotype.png" alt="Logo Marag" class="w-80 h-auto object-contain" />
          </div>
          <p class="text-gray-500 text-sm">Acesso ao painel administrativo</p>
        </div>

        <!-- Formulário de login -->
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Slug da clínica (D11/D14) -->
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700">Clínica</label>
            <div class="relative">
              <input v-model="form.clinic" type="text" placeholder="slug da clínica (ex: demo)"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50/50 placeholder-gray-400"
                required />
            </div>
          </div>

          <!-- Campo de email -->
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700">E-mail</label>
            <div class="relative">
              <input v-model="form.email" type="email" placeholder="seu@email.com"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50/50 placeholder-gray-400"
                required />
            </div>
          </div>

          <!-- Campo de senha -->
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700">Senha</label>
            <div class="relative">
              <input v-model="form.senha" type="password" placeholder="Sua senha"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50/50 placeholder-gray-400"
                required />
            </div>
          </div>

          <!-- Botão de login -->
          <button type="submit" :disabled="loading"
            class="w-full py-3 px-4 rounded-xl font-semibold text-white transition-all duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            :class="loading ? 'bg-gray-800 hover:bg-gray-900 cursor-not-allowed shadow-lg hover:shadow-xl' : 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl'">
            <span v-if="loading" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              Entrando...
            </span>
            <span v-else class="flex items-center justify-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                </path>
              </svg>
              Entrar
            </span>
          </button>
        </form>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-100 text-center">
          <p class="text-xs text-gray-400">
            © 2025 Marag Tecnologia. Todos os direitos reservados.
          </p>
        </div>
      </div>
    </div>

    <!-- Toast de Notificação -->
    <div v-if="toast.show" :class="[
      'fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300',
      toast.type === 'success' ? 'bg-green-500 text-white' :
        toast.type === 'warning' ? 'bg-yellow-500 text-white' :
          toast.type === 'error' ? 'bg-red-500 text-white' : 'bg-blue-500 text-white'
    ]">
      <div class="flex items-center">
        <component :is="getToastIcon(toast.type)" class="w-5 h-5 mr-2" />
        {{ toast.message }}
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from '../../services/axios.js';
import { onMounted, reactive, ref } from 'vue';
import { useAuthStore } from '../../stores/auth.js'
import { useClinicStore } from '../../stores/clinic.js'
import { useRouter } from 'vue-router';

const auth = useAuthStore();
const clinic = useClinicStore();
const router = useRouter();
const loading = ref(false);

const form = reactive({
  clinic: clinic.slug || '',
  email: '',
  senha: ''
});

onMounted(async () => {
  if (form.clinic) {
    try {
      await clinic.loadBranding(form.clinic)
    } catch (_) {
      // branding opcional no login
    }
  }
})

// Toast de notificação
const toast = ref({
  show: false,
  message: '',
  type: 'success'
});

// Ícones para o toast
const CheckCircleIcon = {
  template: `<svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
  </svg>`
}

const ExclamationIcon = {
  template: `<svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
  </svg>`
}

const InformationCircleIcon = {
  template: `<svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
  </svg>`
}

// Função para mostrar toast
const showToast = (message, type = 'success') => {
  toast.value = {
    show: true,
    message,
    type
  }

  setTimeout(() => {
    toast.value.show = false
  }, 4000)
}

const getToastIcon = (type) => {
  const icons = {
    success: CheckCircleIcon,
    warning: ExclamationIcon,
    error: ExclamationIcon,
    info: InformationCircleIcon
  }
  return icons[type] || InformationCircleIcon
}

async function handleLogin() {
  loading.value = true;

  try {
    clinic.setSlug(form.clinic)
    try {
      await clinic.loadBranding(form.clinic)
    } catch (_) {}

    const { data } = await axios.post('/auth', {
      email: form.email,
      senha: form.senha,
    });

    auth.setToken(data.token);
    auth.setUser(data.user);
    if (data.clinic) {
      clinic.setBranding(data.clinic)
    }

    if (data.token && data.user) {
      showToast('Login realizado com sucesso!', 'success');
      setTimeout(() => {
        router.push('/home');
      }, 1000);
    }
  } catch (error) {
    console.log(error);

    if (error.response?.status === 400 || error.response?.status === 404) {
      showToast(error.response?.data?.message || 'Clínica inválida', 'error');
    } else if (error.response?.status === 401) {
      showToast('E-mail ou senha incorretos', 'error');
    } else if (error.response?.status === 422) {
      showToast('Por favor, verifique os dados informados', 'error');
    } else if (error.response?.status >= 500) {
      showToast('Erro interno do servidor. Tente novamente mais tarde.', 'error');
    } else if (!navigator.onLine) {
      showToast('Sem conexão com a internet', 'error');
    } else {
      showToast('Erro ao fazer login. Tente novamente.', 'error');
    }
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
/* Animações personalizadas */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }

  to {
    opacity: 1;
    transform: translateX(0);
  }
}


/* Aplicar animações */
.min-h-screen>div {
  animation: fadeInUp 0.8s ease-out;
}

.min-h-screen>div>div {
  animation: slideIn 0.6s ease-out 0.2s both;
}

/* Hover effects personalizados */
input:focus {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px -8px rgba(59, 130, 246, 0.3);
}

button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px -10px rgba(59, 130, 246, 0.4);
}

/* Responsividade aprimorada */
@media (max-width: 640px) {
  .min-h-screen>div {
    padding: 1rem;
  }

  .bg-white\/80 {
    padding: 1.5rem;
  }
}

/* Gradiente de fundo mais suave */
.bg-gradient-to-br {
  background: linear-gradient(135deg, #eff6ff 0%, #ffffff 50%, #f8fafc 100%);
}

/* Responsividade para as imagens de fundo */
@media (max-width: 1024px) {
  .fixed .absolute img {
    width: 500px;
    height: 500px;
  }
}

@media (max-width: 768px) {
  .fixed .absolute img {
    width: 400px;
    height: 400px;
  }
}

@media (max-width: 480px) {
  .fixed .absolute img {
    width: 300px;
    height: 300px;
  }
}
</style>
