<template>
  <!-- Sidebar -->
  <aside @mouseenter="isHovered = true" @mouseleave="isHovered = false" :class="[
    'bg-white shadow-xl border-r border-gray-100 flex flex-col h-full relative overflow-hidden transition-all duration-300 ease-in-out',
    (isCollapsed && !isHovered) ? 'w-20' : 'w-72'
  ]">
    <!-- Menu de navegação -->
    <nav :class="[
      'overflow-y-auto flex-1 min-h-0 overflow-x-hidden [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden transition-all duration-300',
      (isCollapsed && !isHovered) ? 'p-2' : 'p-4'
    ]">
      <ul class="space-y-2 pb-4">
        <!-- Botão de toggle quando recolhido (primeiro item) -->
        <li v-if="isCollapsed && !isHovered">
          <button @click="toggleSidebar"
            class="w-full p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200 text-gray-600 hover:text-[#0676a6] flex items-center justify-center"
            title="Expandir menu">
            <Menu class="h-5 w-5" />
          </button>
        </li>

        <!-- Item simples (sem dropdown) -->
        <li v-for="item in menuItems" :key="item.id">
          <!-- Separador -->
          <hr v-if="item.separator && (!isCollapsed || isHovered)" class="my-3 border-gray-200" />

          <!-- Link simples -->
          <div v-else-if="!item.children" :class="[
            'relative w-full flex items-center rounded-lg transition-all duration-200',
            (isCollapsed && !isHovered)
              ? 'justify-center'
              : ''
          ]">
            <router-link :to="item.to" :class="[
              'flex items-center rounded-lg transition-all duration-200 hover:[&>svg]:scale-110',
              (isCollapsed && !isHovered)
                ? 'justify-center px-2 py-3 w-full'
                : 'px-4 py-3 text-left hover:translate-x-0.5 flex-1',
              isActive(item.to)
                ? 'bg-[#0676a6] text-white shadow-md'
                : 'text-gray-700 hover:text-[#0676a6]'
            ]" :title="(isCollapsed && !isHovered) ? item.label : ''">
              <component :is="item.icon" :class="[
                'h-5 w-5 transition-transform duration-200',
                (isCollapsed && !isHovered) ? '' : 'mr-3'
              ]" />
              <span v-if="!isCollapsed || isHovered" class="font-medium">{{ item.label }}</span>
            </router-link>

            <!-- Botão de toggle apenas no item Início quando expandido -->
            <button v-if="item.id === 'inicio' && (!isCollapsed || isHovered)" @click.stop="toggleSidebar"
              class="ml-2 p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200 text-gray-600 hover:text-[#0676a6]"
              title="Recolher/Expandir menu">
              <Menu class="h-5 w-5" />
            </button>
          </div>

          <!-- Item com dropdown -->
          <template v-else>
            <button v-if="!isCollapsed || isHovered" @click="toggleMenu(item.id)" :class="[
              'relative w-full flex items-center justify-between px-4 py-3 text-left rounded-lg transition-all duration-200 hover:translate-x-0.5 hover:[&>svg]:scale-110',
              isMenuOpen(item.id) || isActiveInChildren(item)
                ? 'bg-[#c1dde9] text-[#0676a6] font-semibold'
                : 'text-gray-700 hover:text-[#0676a6]'
            ]">
              <div class="flex items-center">
                <component :is="item.icon" class="h-5 w-5 mr-3" />
                <span class="font-medium">{{ item.label }}</span>
              </div>
              <ChevronDown :class="[
                'h-4 w-4 transition-transform duration-300',
                isMenuOpen(item.id) ? 'rotate-180' : ''
              ]" />
            </button>

            <!-- Versão recolhida: apenas ícone com tooltip e menu flutuante -->
            <div v-else class="relative group">
              <button @click="toggleMenu(item.id)" :class="[
                'relative w-full flex items-center justify-center px-2 py-3 rounded-lg transition-all duration-200 hover:[&>svg]:scale-110',
                isActiveInChildren(item)
                  ? 'bg-[#c1dde9] text-[#0676a6]'
                  : 'text-gray-700 hover:text-[#0676a6]'
              ]" :title="item.label">
                <component :is="item.icon" class="h-5 w-5" />
              </button>

              <!-- Menu flutuante para versão recolhida -->
              <div v-if="isMenuOpen(item.id)"
                class="absolute left-full ml-2 top-0 z-50 bg-white rounded-lg shadow-xl border border-gray-200 py-2 min-w-[200px]">
                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase border-b border-gray-100">
                  {{ item.label }}
                </div>
                <ul class="mt-1">
                  <li v-for="child in item.children" :key="child.id">
                    <router-link :to="child.to" @click="toggleMenu(item.id)" :class="[
                      'flex items-center px-4 py-2 text-sm transition-colors duration-200',
                      isActive(child.to)
                        ? 'bg-[#0676a6] text-white'
                        : 'text-gray-700 hover:bg-gray-100 hover:text-[#0676a6]'
                    ]">
                      <component :is="child.icon" class="h-4 w-4 mr-3" />
                      <span>{{ child.label }}</span>
                    </router-link>
                  </li>
                </ul>
              </div>

              <!-- Tooltip simples quando menu não está aberto -->
              <div v-else
                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 z-50 opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200">
                <div class="bg-gray-900 text-white text-sm px-3 py-2 rounded-lg shadow-lg whitespace-nowrap">
                  {{ item.label }}
                </div>
              </div>
            </div>

            <!-- Submenu (apenas quando expandido ou com hover) -->
            <div v-if="!isCollapsed || isHovered" :class="[
              'transition-all duration-300 ease-in-out',
              isMenuOpen(item.id) ? 'mt-2' : 'max-h-0 overflow-hidden'
            ]">
              <ul class="ml-4 space-y-1">
                <li v-for="child in item.children" :key="child.id">
                  <router-link :to="child.to" :class="getSubmenuClass(child.to)">
                    <component :is="child.icon" class="h-4 w-4 mr-3" />
                    <span>{{ child.label }}</span>
                  </router-link>
                </li>
              </ul>
            </div>
          </template>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth.js';
import {
  Calendar, Users, ChevronDown, User, Search, FileText,
  Stethoscope, CheckCircle, Clock, DollarSign, Eye, CreditCard,
  FileBarChart, TrendingUp, Gift, AlertTriangle, Settings,
  CalendarDays, Handshake, Wrench, Layout, Menu, Home
} from 'lucide-vue-next';

const route = useRoute();
const auth = useAuthStore();

const P = { ADMIN: 1, RECEPCAO: 2, PROFISSIONAL: 3 };
const ALL = [P.ADMIN, P.RECEPCAO, P.PROFISSIONAL];
const STAFF = [P.ADMIN, P.RECEPCAO];
const ADMIN = [P.ADMIN];

const menusAbertos = ref({});
const isCollapsed = ref(false);
const isHovered = ref(false);

const allMenuItems = [
  {
    id: 'inicio',
    label: 'Início',
    icon: Home,
    to: '/home',
    separator: false,
    profiles: ALL,
  },
  {
    id: 'separator-1',
    separator: true,
  },
  {
    id: 'agenda',
    label: 'Agenda',
    icon: Calendar,
    to: '/agenda',
    separator: false,
    profiles: ALL,
  },
  {
    id: 'pacientes',
    label: 'Pacientes',
    icon: Users,
    profiles: ALL,
    children: [
      {
        id: 'pacientes-pesquisar',
        label: 'Gerenciar Pacientes',
        icon: Search,
        to: '/pacientes/gerenciar',
        pageKey: 'pacientes-pesquisar',
        profiles: ALL,
      },
      {
        id: 'pacientes-ficha',
        label: 'Cadastrar Paciente',
        icon: FileText,
        to: '/pacientes/cadastro',
        pageKey: 'pacientes-ficha',
        profiles: ALL,
      }
    ]
  },
  {
    id: 'consultas',
    label: 'Consultas',
    icon: Stethoscope,
    profiles: ALL,
    children: [
      {
        id: 'consultas-atendidas',
        label: 'Consultas Atendidas',
        icon: CheckCircle,
        to: '/consultas/atendidas',
        pageKey: 'consultas-atendidas',
        profiles: ALL,
      },
      {
        id: 'consultas-fila',
        label: 'Fila de Espera',
        icon: Clock,
        to: '/consultas/fila-espera',
        pageKey: 'consultas-fila-espera',
        profiles: ALL,
      },
    ]
  },
  {
    id: 'financeiro',
    label: 'Financeiro',
    icon: DollarSign,
    profiles: STAFF,
    children: [
      {
        id: 'financeiro-visao-geral',
        label: 'Visão Geral',
        icon: Eye,
        to: '/financeiro/visao-geral',
        pageKey: 'financeiro-visao-geral',
        profiles: STAFF,
      },
      {
        id: 'financeiro-atendimentos',
        label: 'Atendimentos',
        icon: CreditCard,
        to: '/financeiro/atendimentos',
        pageKey: 'financeiro-atendimentos',
        profiles: STAFF,
      }
    ]
  },
  {
    id: 'relatorios',
    label: 'Relatórios',
    icon: FileBarChart,
    profiles: STAFF,
    children: [
      {
        id: 'relatorios-financeiro',
        label: 'Relatório Financeiro',
        icon: TrendingUp,
        to: '/relatorios/financeiro',
        pageKey: 'relatorios-financeiro',
        profiles: STAFF,
      },
      {
        id: 'relatorios-aniversariantes',
        label: 'Aniversariantes',
        icon: Gift,
        to: '/relatorios/aniversariantes',
        pageKey: 'relatorios-aniversariantes',
        profiles: STAFF,
      },
      {
        id: 'relatorios-vencidas',
        label: 'Consultas Vencidas',
        icon: AlertTriangle,
        to: '/relatorios/consultas-vencidas',
        pageKey: 'relatorios-vencidas',
        profiles: STAFF,
      }
    ]
  },
  {
    id: 'configuracoes',
    label: 'Configurações',
    icon: Settings,
    profiles: STAFF,
    children: [
      {
        id: 'configuracoes-agendamentos',
        label: 'Agendamentos',
        icon: CalendarDays,
        to: '/configuracoes/agendamentos',
        pageKey: 'configuracoes-agendamentos',
        profiles: STAFF,
      },
      {
        id: 'configuracoes-parceiros',
        label: 'Parceiros',
        icon: Handshake,
        to: '/configuracoes/parceiros',
        pageKey: 'configuracoes-parceiros',
        profiles: STAFF,
      },
      {
        id: 'configuracoes-ajustes-gerais',
        label: 'Ajustes Gerais',
        icon: Wrench,
        to: '/configuracoes/ajustes-gerais',
        pageKey: 'configuracoes-ajustes-gerais',
        profiles: ADMIN,
      },
      {
        id: 'configuracoes-marca',
        label: 'Marca da Clínica',
        icon: Layout,
        to: '/configuracoes/marca',
        pageKey: 'configuracoes-marca',
        profiles: ADMIN,
      },
      {
        id: 'configuracoes-usuarios',
        label: 'Usuários',
        icon: User,
        to: '/configuracoes/usuarios',
        pageKey: 'configuracoes-usuarios',
        profiles: ADMIN,
      },
    ]
  }
];

const canSee = (profiles) => {
  if (!profiles || profiles.length === 0) return true;
  return auth.hasProfile(...profiles);
};

const menuItems = computed(() => {
  return allMenuItems
    .map((item) => {
      if (item.separator) return item;
      if (item.children) {
        const children = item.children.filter((child) => canSee(child.profiles));
        if (children.length === 0 || !canSee(item.profiles)) return null;
        return { ...item, children };
      }
      if (!canSee(item.profiles)) return null;
      return item;
    })
    .filter(Boolean);
});

// Funções
const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
  // Fechar todos os menus quando recolher
  if (isCollapsed.value) {
    menusAbertos.value = {};
  }
};

const toggleMenu = (menuId) => {
  menusAbertos.value[menuId] = !menusAbertos.value[menuId];
};

const isMenuOpen = (menuId) => {
  return menusAbertos.value[menuId] || false;
};

const isActive = (to) => {
  return route.path === to;
};

const isActiveInChildren = (item) => {
  if (!item.children) return false;
  return item.children.some(child => route.path === child.to);
};

const getSubmenuClass = (to) => {
  const isActiveRoute = route.path === to;
  return [
    'relative w-full flex items-center px-4 py-2 text-left rounded-lg transition-all duration-200 text-sm hover:translate-x-1 hover:[&>svg]:scale-110',
    isActiveRoute
      ? 'bg-[#0676a6] text-white shadow-md font-medium'
      : 'text-gray-600 hover:text-[#0676a6]'
  ];
};

watch(() => route.path, () => {
  if (isCollapsed.value) {
    menusAbertos.value = {};
  }
});
</script>
