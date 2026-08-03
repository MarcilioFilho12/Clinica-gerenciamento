import { useAuthStore } from '../stores/auth.js'
import { createRouter, createWebHistory } from "vue-router";

/** Lazy load: reduz bundle inicial do piloto */
const LayoutPainel = () => import("../layouts/LayoutPainel.vue");
const Login = () => import("../views/Login/Login.vue");
const Dashboard = () => import("../views/dashboard/Dashboard.vue");
const Agendamento = () => import("../views/agendamentos/Agenda.vue");
const GerenciarPacientes = () => import("../views/pacientes/GerenciarPacientes.vue");
const CadastroPaciente = () => import("../views/pacientes/CadastroPaciente.vue");
const FichaClinica = () => import("../views/pacientes/FichaClinica.vue");
const VisualizarFichaClinica = () => import("../views/pacientes/VisualizarFichaClinica.vue");
const ImprimirFichaClinica = () => import("../views/pacientes/ImprimirFichaClinica.vue");
const ImprimirRefracaoSubjetivaA5 = () => import("../views/pacientes/ImprimirRefracaoSubjetivaA5.vue");
const DetalhesPaciente = () => import("../views/pacientes/DetalhesPaciente.vue");
const DetalhesConsulta = () => import("../views/pacientes/DetalhesConsulta.vue");
const FilaEspera = () => import("../views/consultas/FilaEspera.vue");
const VisaoGeral = () => import("../views/financeiro/VisaoGeral.vue");
const Aniversariantes = () => import("../views/relatorios/Aniversariantes.vue");
const ConsultasVencidas = () => import("../views/relatorios/ConsultasVencidas.vue");
const RelatorioFinanceiro = () => import("../views/relatorios/RelatorioFinanceiro.vue");
const Agendamentos = () => import("../views/configuracoes/Agendamentos.vue");
const ListConfigAgendamentos = () => import("../views/configuracoes/ListaConfigAgendamentos.vue");
const Ajustes = () => import("../views/configuracoes/Ajustes.vue");
const BrandingClinica = () => import("../views/configuracoes/BrandingClinica.vue");
const Parceiros = () => import("../views/configuracoes/Parceiros.vue");
const NovoParceiro = () => import("../views/configuracoes/NovoParceiro.vue");
const Usuarios = () => import("../views/configuracoes/Usuarios.vue");

/** Perfis: 1 Admin, 2 Recepção, 3 Profissional */
const P = { ADMIN: 1, RECEPCAO: 2, PROFISSIONAL: 3 };
const ALL = [P.ADMIN, P.RECEPCAO, P.PROFISSIONAL];
const STAFF = [P.ADMIN, P.RECEPCAO];
const ADMIN = [P.ADMIN];

/**
 * Piloto soft launch: rotas fora do escopo / incompletas → /home
 * (Modelos, Inadimplentes, Permissões, Fluxo Diário, Relatório Consultas,
 *  Consultas Atendidas, Atendimentos financeiro, Telão — G0.2 off)
 */
const pilotoRedirect = { path: "/home" };

const routes = [
  { path: "/", component: Login },
  {
    path: "/",
    component: LayoutPainel,
    children: [
      { path: "/home", component: Dashboard, meta: { auth: true, profiles: ALL } },
      { path: "/agenda", component: Agendamento, meta: { auth: true, profiles: ALL } },
      { path: "/pacientes/gerenciar", component: GerenciarPacientes, meta: { auth: true, profiles: ALL } },
      { path: "/pacientes/cadastro", component: CadastroPaciente, meta: { auth: true, profiles: ALL } },
      { path: "/pacientes/cadastro/:id", component: CadastroPaciente, meta: { auth: true, profiles: ALL } },
      { path: "/pacientes/detalhes/:id", component: DetalhesPaciente, meta: { auth: true, profiles: ALL } },
      { path: "/pacientes/detalhes/:idPaciente/consultas/:consultaId", component: DetalhesConsulta, meta: { auth: true, profiles: ALL } },
      { path: "/pacientes/ficha-clinica", component: FichaClinica, meta: { auth: true, profiles: ALL } },
      { path: "/pacientes/ficha-clinica/:id", component: FichaClinica, meta: { auth: true, profiles: ALL } },
      { path: "/pacientes/detalhes/:idPaciente/ficha-clinica/:idFichaClinica/visualizar", component: VisualizarFichaClinica, meta: { auth: true, profiles: ALL } },
      { path: "/pacientes/detalhes/:idPaciente/ficha-clinica/:idFichaClinica", component: FichaClinica, meta: { auth: true, profiles: ALL } },
      { path: "/consultas/atendidas", redirect: pilotoRedirect },
      { path: "/consultas/fila-espera", component: FilaEspera, meta: { auth: true, profiles: ALL } },
      { path: "/consultas/fluxo-diario", redirect: pilotoRedirect },
      { path: "/financeiro/atendimentos", redirect: pilotoRedirect },
      { path: "/financeiro/visao-geral", component: VisaoGeral, meta: { auth: true, profiles: STAFF } },
      { path: "/relatorios/aniversariantes", component: Aniversariantes, meta: { auth: true, profiles: STAFF } },
      { path: "/relatorios/consultas-vencidas", component: ConsultasVencidas, meta: { auth: true, profiles: STAFF } },
      { path: "/relatorios/consultas", redirect: pilotoRedirect },
      { path: "/relatorios/financeiro", component: RelatorioFinanceiro, meta: { auth: true, profiles: STAFF } },
      { path: "/configuracoes/agendamentos", component: ListConfigAgendamentos, meta: { auth: true, profiles: STAFF } },
      { path: "/configuracoes/agendamentos/:id", component: Agendamentos, meta: { auth: true, profiles: STAFF } },
      { path: "/configuracoes/agendamentos/novo", component: Agendamentos, meta: { auth: true, profiles: STAFF } },
      { path: "/configuracoes/ajustes-gerais", component: Ajustes, meta: { auth: true, profiles: ADMIN } },
      { path: "/configuracoes/marca", component: BrandingClinica, meta: { auth: true, profiles: ADMIN } },
      { path: "/configuracoes/inadimplentes", redirect: pilotoRedirect },
      { path: "/configuracoes/modelos", redirect: pilotoRedirect },
      { path: "/configuracoes/parceiros", component: Parceiros, meta: { auth: true, profiles: STAFF } },
      { path: "/configuracoes/parceiros/novo", component: NovoParceiro, meta: { auth: true, profiles: STAFF } },
      { path: "/configuracoes/parceiros/:id", component: NovoParceiro, meta: { auth: true, profiles: STAFF } },
      { path: "/configuracoes/usuarios", component: Usuarios, meta: { auth: true, profiles: ADMIN } },
      { path: "/configuracoes/permissoes", redirect: pilotoRedirect },
    ]
  },
  { path: "/imprimir-ficha-clinica/:id", component: ImprimirFichaClinica, meta: { auth: true, profiles: ALL } },
  { path: "/imprimir-refracao-subjetiva-a5/:id", component: ImprimirRefracaoSubjetivaA5, meta: { auth: true, profiles: ALL } },
  { path: "/consultas/telao-chamada", redirect: pilotoRedirect },
];

const router = createRouter({
  history: createWebHistory(),
  routes: routes,
});

router.beforeEach(async (to, from, next) => {
  if (!to.meta?.auth) {
    next();
    return;
  }

  const auth = useAuthStore();

  if (!auth.token || !auth.user) {
    next({ path: '/' });
    return;
  }

  const isAuthenticated = await auth.checkToken();
  if (!isAuthenticated) {
    next({ path: '/' });
    return;
  }

  const allowed = to.meta.profiles;
  if (Array.isArray(allowed) && allowed.length > 0 && !auth.hasProfile(...allowed)) {
    next({ path: '/home' });
    return;
  }

  next();
});

export default router;
