<template>
  <div class="min-h-screen bg-gray-50 text-gray-900 p-8 flex flex-col">
  <div class="flex-1 flex flex-col justify-center items-center">
    <div class="max-w-6xl w-full mx-auto space-y-12 text-center">
      <!-- Header -->
      <header class="space-y-2">
        <h1 class="text-4xl font-semibold tracking-tight text-gray-900">Seja Bem-vindo!</h1>
        <p class="text-sm text-gray-500">{{ dataAtual }}</p>
      </header>

      <!-- Última chamada -->
      <section v-if="ultimaChamada" class="bg-white rounded-3xl p-10 shadow-md border border-gray-200 space-y-6 ring-4 ring-blue-400 animate-pulse">
        <span class="inline-flex items-center justify-center text-sm font-medium px-5 py-2 rounded-full bg-green-600 text-white">Chamada atual</span>
        <h2 class="text-5xl font-bold text-gray-900 truncate">{{ ultimaChamada.paciente.nome }}</h2>
        <p class="text-3xl font-semibold text-blue-600">{{ ultimaChamada.codigo_chegada }}</p>
        <p class="text-lg text-gray-600">{{ ultimaChamada.profissional.nome }}</p>
        <p class="text-sm text-gray-500">{{ ultimaChamada.horario_chamada }}</p>
      </section>

      <!-- Histórico -->
      <section v-if="chamadasAnteriores.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <article
          v-for="chamada in chamadasAnteriores"
          :key="chamada.id"
          class="bg-white rounded-2xl p-6 border border-gray-200 hover:border-gray-300 hover:shadow-md transition space-y-2"
        >
          <h3 class="text-xl font-medium text-gray-900 truncate">{{ chamada.paciente.nome }}</h3>
          <p class="text-lg font-medium text-blue-600">{{ chamada.codigo_chegada }}</p>
          <p class="text-sm text-gray-600">{{ chamada.profissional.nome }}</p>
          <p class="text-xs text-gray-500">{{ chamada.horario_chamada }}</p>
        </article>
      </section>

      <!-- Vazio -->
      <section v-if="chamadas.length === 0" class="bg-white rounded-3xl p-16 border border-gray-200 space-y-4 shadow-sm text-center">
        <h2 class="text-3xl font-semibold text-gray-900">Bom dia!</h2>
        <p class="text-gray-500">Cuidar da saúde é um investimento diário.</p>
      </section>

      <!-- Status -->
      <footer class="text-center">
        <span
          :class="[
            'inline-flex items-center gap-2 px-4 py-2 text-sm rounded-full font-medium',
            conectado ? 'bg-green-600 text-white' : 'bg-red-600 text-white'
          ]"
        >
          <span
            :class="[
              'w-2 h-2 rounded-full',
              conectado ? 'bg-white' : 'bg-white animate-pulse'
            ]"
          />
          {{ conectado ? 'Conectado' : 'Desconectado' }}
        </span>
      </footer>
    </div>
  </div>

  <!-- Logo fixa rodapé -->
  <div class="w-full py-6 flex justify-center border-t border-gray-200">
    <img src="../../img/logos/marag-logotype.png" alt="Logo" class="h-14 opacity-80" />
  </div>
</div>
</template>

<script>
import Echo from '@/services/echo.js';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import somChamada from '@/sons/painel-chamada.mp3';

export default {
  name: 'TelaoChamada',
  setup() {
    const chamadas = ref([]);
    const conectado = ref(false);
    const maxChamadasHistoricas = 3; // Máximo de chamadas no histórico (além da última em destaque)

    const dataAtual = computed(() => {
      return new Date().toLocaleDateString('pt-BR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      });
    });

    const ultimaChamada = computed(() => {
      return chamadas.value.length > 0 ? chamadas.value[0] : null;
    });

    const chamadasAnteriores = computed(() => {
      // Retornar apenas as 3 últimas chamadas anteriores à atual
      return chamadas.value.slice(1, maxChamadasHistoricas + 1);
    });

    // Função para tocar o som de chamada 3 vezes com 3 segundos de intervalo
    const tocarSomChamada = () => {
      // Função auxiliar para tocar uma vez
      const tocarUmaVez = () => {
        return new Promise((resolve, reject) => {
          // Criar uma nova instância de Audio para cada tocada
          const som = new Audio(somChamada);
          som.volume = 0.7; // Volume em 70% (ajustável de 0.0 a 1.0)
          
          // Quando terminar de tocar, resolver a promise
          som.onended = () => {
            resolve();
          };
          
          // Tratar erros
          som.onerror = (error) => {
            console.warn('Erro ao tocar som:', error);
            resolve(); // Continuar mesmo se houver erro
          };
          
          // Tocar o áudio
          som.play().catch(error => {
            console.warn('Erro ao iniciar reprodução do som:', error);
            resolve(); // Continuar mesmo se houver erro
          });
        });
      };
      
      // Tocar 3 vezes em sequência com 3 segundos de intervalo
      tocarUmaVez()
        .then(() => {
          // Aguardar 3 segundos antes da segunda tocada
          return new Promise(resolve => setTimeout(resolve, 3000));
        })
        .then(() => tocarUmaVez())
        .then(() => {
          // Aguardar 3 segundos antes da terceira tocada
          return new Promise(resolve => setTimeout(resolve, 3000));
        })
        .then(() => tocarUmaVez())
        .catch(error => {
          console.error('Erro ao tocar sequência de sons:', error);
        });
    };

    onMounted(() => {
      console.log('[DEBUG] TelaoChamada montado - iniciando conexão...');

      const params = new URLSearchParams(window.location.search)
      const clinicParam = params.get('clinic')
      if (clinicParam) {
        localStorage.setItem('clinic_slug', clinicParam.toLowerCase())
      }
      
      // Conectar ao canal de chamadas da clínica
      const slug = localStorage.getItem('clinic_slug') || import.meta.env.VITE_CLINIC_SLUG || 'default'
      const canalNome = `chamadas.pacientes.${slug}`
      const canal = Echo.channel(canalNome);
      console.log('[DEBUG] Canal criado:', canalNome);
      
      // Log quando o canal é subscrito
      canal.subscribed(() => {
        console.log('[DEBUG] ✅ Canal subscrito com sucesso:', canalNome);
      });
      
      // Log de erros na subscrição
      canal.error((error) => {
        console.error('[DEBUG] ❌ Erro ao subscrever canal:', error);
      });
      
      // Ouvir eventos (tentar ambos os formatos possíveis)
      canal.listen('.paciente.chamado', (data) => {
        console.log('[DEBUG] 🎯 Nova chamada recebida (.paciente.chamado)!', data);
        
        // Tocar som de chamada (3 vezes com 3 segundos de intervalo)
        tocarSomChamada();
          
          // Adicionar nova chamada no início do array
          chamadas.value.unshift({
            id: data.id || Date.now(),
            paciente: data.paciente,
            profissional: data.profissional,
            codigo_chegada: data.codigo_chegada || 'N/A',
            horario_chamada: data.horario_chamada,
            data_chamada: data.data_chamada,
          });

          // Manter apenas a última chamada + 3 no histórico (total de 4)
          // Se passar de 4, remover a mais antiga (a última do array)
          if (chamadas.value.length > maxChamadasHistoricas + 1) {
            chamadas.value = chamadas.value.slice(0, maxChamadasHistoricas + 1);
          }

          // Indicar que está conectado
          conectado.value = true;
        });
      
      // Listener alternativo (sem ponto) - caso o Laravel não esteja prefixando
      canal.listen('paciente.chamado', (data) => {
        console.log('[DEBUG] 🎯 Nova chamada recebida (paciente.chamado sem ponto)!', data);
        
        // Tocar som de chamada (3 vezes com 3 segundos de intervalo)
        tocarSomChamada();
        
        // Adicionar nova chamada no início do array
        chamadas.value.unshift({
          id: data.id || Date.now(),
          paciente: data.paciente,
          profissional: data.profissional,
          codigo_chegada: data.codigo_chegada || 'N/A',
          horario_chamada: data.horario_chamada,
          data_chamada: data.data_chamada,
        });

        // Manter apenas a última chamada + 3 no histórico (total de 4)
        // Se passar de 4, remover a mais antiga (a última do array)
        if (chamadas.value.length > maxChamadasHistoricas + 1) {
          chamadas.value = chamadas.value.slice(0, maxChamadasHistoricas + 1);
        }

        conectado.value = true;
      });
      
      // Listener com nome completo do evento (App\Events\PacienteChamado)
      canal.listen('App\\Events\\PacienteChamado', (data) => {
        console.log('[DEBUG] 🎯 Nova chamada recebida (nome completo do evento)!', data);
        
        // Tocar som de chamada (3 vezes com 3 segundos de intervalo)
        tocarSomChamada();
        
        // Adicionar nova chamada no início do array
        chamadas.value.unshift({
          id: data.id || Date.now(),
          paciente: data.paciente,
          profissional: data.profissional,
          codigo_chegada: data.codigo_chegada || 'N/A',
          horario_chamada: data.horario_chamada,
          data_chamada: data.data_chamada,
        });

        // Manter apenas a última chamada + 3 no histórico (total de 4)
        // Se passar de 4, remover a mais antiga (a última do array)
        if (chamadas.value.length > maxChamadasHistoricas + 1) {
          chamadas.value = chamadas.value.slice(0, maxChamadasHistoricas + 1);
        }

        conectado.value = true;
      });

      // Monitorar conexão
      Echo.connector.pusher.connection.bind('connected', () => {
        console.log('[DEBUG] ✅ Conectado ao WebSocket');
        conectado.value = true;
      });
      
      // Monitorar subscription bem-sucedida (debug)
      canal.subscription.bind('pusher:subscription_succeeded', () => {
        console.log('[DEBUG] ✅ Subscription ao canal bem-sucedida - pronto para receber eventos');
      });

      Echo.connector.pusher.connection.bind('disconnected', () => {
        console.log('Desconectado do WebSocket');
        conectado.value = false;
      });

      Echo.connector.pusher.connection.bind('error', (error) => {
        console.error('Erro na conexão WebSocket:', error);
        conectado.value = false;
      });
    });

    onUnmounted(() => {
      const slug = localStorage.getItem('clinic_slug') || import.meta.env.VITE_CLINIC_SLUG || 'default'
      Echo.leave(`chamadas.pacientes.${slug}`);
    });

    return {
      chamadas,
      conectado,
      dataAtual,
      ultimaChamada,
      chamadasAnteriores,
    };
  }
};
</script>

<style scoped>
@keyframes pulse-border {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
  }
  50% {
    box-shadow: 0 0 0 20px rgba(34, 197, 94, 0);
  }
}

.animate-pulse-border {
  animation: pulse-border 2s infinite;
}
</style>

