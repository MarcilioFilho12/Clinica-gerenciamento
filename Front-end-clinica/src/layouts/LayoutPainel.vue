<template>
  <div class="flex flex-col h-screen">
    <!-- HEADER NO TOPO -->
    <div class="w-full">
      <Header />
    </div>

    <!-- CONTEÚDO ABAIXO DO HEADER -->
    <div class="flex flex-1 overflow-hidden">
      <!-- SIDEBAR NA ESQUERDA -->
      <Sidebar />

      <!-- CONTEÚDO PRINCIPAL -->
      <div class="flex-1 overflow-y-auto p-4">
        <main class="mx-10 mt-4 3xl:container 3xl:mx-auto min-h-full">
          <router-view />
        </main>
      </div>
    </div>
  </div>
</template>

<script>
import Sidebar from "../components/layout/Sidebar.vue";
import Header from "../components/layout/Header.vue";

export default {
  components: {
    Sidebar,
    Header,
  },
};
</script>
