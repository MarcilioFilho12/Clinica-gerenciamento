<template>
  <div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <PageHeader title="Configurações Gerais" description="Gerencie todas as configurações do sistema" :icon="Settings"
      :show-breadcrumbs="true" :breadcrumbs="[
        { label: 'Início', to: '/home' },
        { label: 'Configurações Gerais' }
      ]" class="mb-8" iconBgColor="gray" />

    <!-- Loading State -->
    <div v-if="carregandoGeral" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <Loader2 class="w-8 h-8 text-gray-600 animate-spin" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Carregando configurações...</h3>
      <p class="text-gray-500">Aguarde enquanto buscamos todas as configurações</p>
    </div>

    <!-- Error State -->
    <div v-else-if="erroGeral" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
      <div class="flex items-center space-x-3">
        <AlertCircle class="w-6 h-6 text-red-600" />
        <div>
          <h3 class="text-red-800 font-medium">Erro ao carregar configurações</h3>
          <p class="text-red-600 text-sm mt-1">{{ erroGeral }}</p>
        </div>
      </div>
      <button @click="carregarTodasConfiguracoes"
        class="mt-4 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
        Tentar Novamente
      </button>
    </div>

    <!-- Navegação por Abas -->
    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
      <div class="border-b border-gray-200">
        <nav class="flex space-x-8 px-6" aria-label="Tabs">
          <button v-for="aba in abas" :key="aba.id" @click="abaAtiva = aba.id"
            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors" :class="abaAtiva === aba.id
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
            <div class="flex items-center space-x-2">
              <component :is="aba.icone" class="w-4 h-4" />
              <span>{{ aba.nome }}</span>
            </div>
          </button>
        </nav>
      </div>
    </div>

    <!-- Conteúdo das Abas -->
    <div v-if="!carregandoGeral && !erroGeral">
      <!-- 2. Informações da Clínica -->
      <div v-show="abaAtiva === 'clinica'" class="space-y-6">
        <form @submit.prevent="salvarInformacoesClinica"
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center space-x-2">
            <Building class="w-5 h-5 text-blue-600" />
            <span>Informações da Clínica</span>
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nome Fantasia -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome Fantasia *</label>
              <input type="text" v-model="clinica.nomeFantasia"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': erros.nomeFantasia }" placeholder="Nome da clínica" />
              <div v-if="erros.nomeFantasia" class="mt-1 text-sm text-red-600">{{ erros.nomeFantasia }}</div>
            </div>

            <!-- Razão Social -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Razão Social *</label>
              <input type="text" v-model="clinica.razaoSocial"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': erros.razaoSocial }" placeholder="Razão social da clínica" />
              <div v-if="erros.razaoSocial" class="mt-1 text-sm text-red-600">{{ erros.razaoSocial }}</div>
            </div>

            <!-- CNPJ -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">CNPJ *</label>
              <input type="text" v-model="clinica.cnpj" @input="formatarCNPJ"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': erros.cnpj }" placeholder="00.000.000/0000-00" maxlength="18" />
              <div v-if="erros.cnpj" class="mt-1 text-sm text-red-600">{{ erros.cnpj }}</div>
            </div>

            <!-- Telefone Principal -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Telefone Principal *</label>
              <input type="text" v-model="clinica.telefone" @input="formatarTelefone"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': erros.telefone }" placeholder="(48) 3333-4444" maxlength="15" />
              <div v-if="erros.telefone" class="mt-1 text-sm text-red-600">{{ erros.telefone }}</div>
            </div>

            <!-- WhatsApp -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp</label>
              <input type="text" v-model="clinica.whatsapp" @input="formatarTelefone"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="(48) 99999-9999" maxlength="15" />
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">E-mail *</label>
              <input type="email" v-model="clinica.email"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': erros.email }" placeholder="contato@clinica.com.br" />
              <div v-if="erros.email" class="mt-1 text-sm text-red-600">{{ erros.email }}</div>
            </div>
          </div>

          <!-- Endereço -->
          <div class="mt-8 border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Endereço</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">CEP</label>
                <input type="text" v-model="clinica.cep" @input="formatarCEP" @blur="buscarCEP"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="00000-000" maxlength="9" />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Rua/Logradouro</label>
                <input type="text" v-model="clinica.rua"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Nome da rua" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Número</label>
                <input type="text" v-model="clinica.numero"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="123" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Complemento</label>
                <input type="text" v-model="clinica.complemento"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Sala, andar..." />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bairro</label>
                <input type="text" v-model="clinica.bairro"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Nome do bairro" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
                <input type="text" v-model="clinica.cidade"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Nome da cidade" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select v-model="clinica.estado"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="">Selecione</option>
                  <option value="SC">Santa Catarina</option>
                  <option value="RS">Rio Grande do Sul</option>
                  <option value="PR">Paraná</option>
                  <option value="SP">São Paulo</option>
                  <option value="RJ">Rio de Janeiro</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Horário de Funcionamento -->
          <div class="mt-8 border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Horário de Funcionamento</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Segunda a Sexta</label>
                <div class="flex space-x-2">
                  <input type="time" v-model="clinica.horarioSemana.inicio"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                  <span class="flex items-center text-gray-500">às</span>
                  <input type="time" v-model="clinica.horarioSemana.fim"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sábado</label>
                <div class="flex space-x-2">
                  <input type="time" v-model="clinica.horarioSabado.inicio"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                  <span class="flex items-center text-gray-500">às</span>
                  <input type="time" v-model="clinica.horarioSabado.fim"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
              </div>
            </div>
          </div>

          <!-- Upload do Logo -->
          <div class="mt-8 border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Logo da Clínica</h3>
            <div class="flex items-start space-x-6">
              <!-- Preview do Logo -->
              <div class="flex-shrink-0">
                <div
                  class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50">
                  <img v-if="clinica.logoUrl" :src="clinica.logoUrl" alt="Logo da clínica"
                    class="w-full h-full object-contain rounded-lg" />
                  <div v-else class="text-center">
                    <ImageIcon class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                    <p class="text-sm text-gray-500">Sem logo</p>
                  </div>
                </div>
              </div>

              <!-- Upload -->
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Fazer upload do logo
                </label>
                <div class="flex items-center space-x-4">
                  <input type="file" ref="logoInput" @change="handleLogoUpload" accept="image/*" class="hidden" />
                  <button type="button" @click="$refs.logoInput.click()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors flex items-center space-x-2">
                    <Upload class="w-4 h-4" />
                    <span>Escolher Arquivo</span>
                  </button>
                  <button v-if="clinica.logoUrl" type="button" @click="removerLogo"
                    class="px-4 py-2 border border-red-300 text-red-700 rounded-md hover:bg-red-50 transition-colors flex items-center space-x-2">
                    <Trash2 class="w-4 h-4" />
                    <span>Remover</span>
                  </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                  Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB
                </p>
              </div>
            </div>
          </div>

          <!-- Botão Salvar -->
          <div class="mt-8 border-t border-gray-200 pt-6 flex justify-end">
            <button type="submit" :disabled="salvandoClinica"
              class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed transition-colors font-medium flex items-center space-x-2">
              <Loader2 v-if="salvandoClinica" class="w-4 h-4 animate-spin" />
              <Save v-else class="w-4 h-4" />
              <span>{{ salvandoClinica ? 'Salvando...' : 'Salvar Informações' }}</span>
            </button>
          </div>
        </form>
      </div>
      <!-- 4. Notificações e Comunicação -->
      <div v-show="abaAtiva === 'notificacoes'" class="space-y-6">
        <form @submit.prevent="salvarNotificacoes" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center space-x-2">
            <Mail class="w-5 h-5 text-blue-600" />
            <span>Notificações e Comunicação</span>
          </h2>

          <!-- Configurações de E-mail -->
          <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Configurações de E-mail</h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div>
                  <h4 class="text-sm font-medium text-gray-900">Envio Automático de E-mails</h4>
                  <p class="text-sm text-gray-500">Ativar envio automático de confirmações e lembretes</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="notificacoes.emailAutomatico" class="sr-only peer" />
                  <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                  </div>
                </label>
              </div>

              <!-- Mensagem de Confirmação -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mensagem de Confirmação de Consulta</label>
                <textarea v-model="notificacoes.mensagemConfirmacao" rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Olá {nome}, sua consulta foi confirmada para {data} às {hora}..."></textarea>
                <p class="text-xs text-gray-500 mt-1">
                  Use: {nome}, {data}, {hora}, {clinica} para personalizar
                </p>
              </div>

              <!-- Mensagem de Lembrete -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mensagem de Lembrete</label>
                <textarea v-model="notificacoes.mensagemLembrete" rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Lembrete: você tem consulta amanhã às {hora}..."></textarea>
              </div>

              <!-- Assinatura de E-mail -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Assinatura Personalizada</label>
                <textarea v-model="notificacoes.assinaturaEmail" rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Atenciosamente,&#10;Nome da Clínica&#10;(00) 0000-0000"></textarea>
              </div>
            </div>
          </div>

          <!-- Configurações de WhatsApp/SMS -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">WhatsApp e SMS</h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div>
                  <h4 class="text-sm font-medium text-gray-900">Lembretes por WhatsApp</h4>
                  <p class="text-sm text-gray-500">Enviar lembretes automáticos via WhatsApp</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="notificacoes.whatsappAtivo" class="sr-only peer" />
                  <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600">
                  </div>
                </label>
              </div>

              <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div>
                  <h4 class="text-sm font-medium text-gray-900">Lembretes por SMS</h4>
                  <p class="text-sm text-gray-500">Enviar lembretes automáticos via SMS</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="notificacoes.smsAtivo" class="sr-only peer" />
                  <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600">
                  </div>
                </label>
              </div>

              <!-- Mensagem WhatsApp/SMS -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mensagem para WhatsApp/SMS</label>
                <textarea v-model="notificacoes.mensagemWhatsapp" rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Olá {nome}! Lembrete da sua consulta amanhã às {hora}. Nome da Clínica."></textarea>
              </div>

              <!-- Horário de Envio -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Enviar Lembrete com Antecedência</label>
                  <select v-model="notificacoes.antecedenciaLembrete"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="1">1 hora antes</option>
                    <option value="2">2 horas antes</option>
                    <option value="4">4 horas antes</option>
                    <option value="24">1 dia antes</option>
                    <option value="48">2 dias antes</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Horário Preferencial de Envio</label>
                  <input type="time" v-model="notificacoes.horarioEnvio"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
              </div>
            </div>
          </div>

          <!-- Botão Salvar -->
          <div class="mt-8 border-t border-gray-200 pt-6 flex justify-end">
            <button type="submit" :disabled="salvandoNotificacoes"
              class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed transition-colors font-medium flex items-center space-x-2">
              <Loader2 v-if="salvandoNotificacoes" class="w-4 h-4 animate-spin" />
              <Save v-else class="w-4 h-4" />
              <span>{{ salvandoNotificacoes ? 'Salvando...' : 'Salvar Notificações' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- 5. Configurações de Pagamento -->
      <div v-show="abaAtiva === 'pagamento'" class="space-y-6">
        <form @submit.prevent="salvarPagamento" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center space-x-2">
            <CreditCard class="w-5 h-5 text-blue-600" />
            <span>Configurações de Pagamento</span>
          </h2>

          <!-- Métodos de Pagamento -->
          <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Métodos de Pagamento Aceitos</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <label v-for="metodo in metodosPagamento" :key="metodo.id"
                class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="checkbox" :value="metodo.id" v-model="pagamento.metodosAceitos"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                <component :is="metodo.icone" class="w-5 h-5 text-gray-500" />
                <span class="text-sm font-medium text-gray-900">{{ metodo.nome }}</span>
              </label>
            </div>
          </div>

          <!-- Configurações de Parcelamento -->
          <div class="mb-8 border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Parcelamento</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Máximo de Parcelas</label>
                <select v-model="pagamento.maxParcelas"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="1">Sem parcelamento</option>
                  <option value="2">2x</option>
                  <option value="3">3x</option>
                  <option value="6">6x</option>
                  <option value="12">12x</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Valor Mínimo da Parcela</label>
                <input type="number" v-model.number="pagamento.valorMinimoParcela" min="0" step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="50.00" />
              </div>
            </div>
          </div>

          <!-- Configurações de Desconto -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Descontos</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Desconto à Vista (%)</label>
                <input type="number" v-model.number="pagamento.descontoVista" min="0" max="100" step="0.1"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="5.0" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Desconto PIX (%)</label>
                <input type="number" v-model.number="pagamento.descontoPix" min="0" max="100" step="0.1"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="3.0" />
              </div>
            </div>
          </div>

          <!-- Botão Salvar -->
          <div class="mt-8 border-t border-gray-200 pt-6 flex justify-end">
            <button type="submit" :disabled="salvandoPagamento"
              class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed transition-colors font-medium flex items-center space-x-2">
              <Loader2 v-if="salvandoPagamento" class="w-4 h-4 animate-spin" />
              <Save v-else class="w-4 h-4" />
              <span>{{ salvandoPagamento ? 'Salvando...' : 'Salvar Configurações' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- 7. Backup e Exportações -->
      <div v-show="abaAtiva === 'backup'" class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center space-x-2">
            <Database class="w-5 h-5 text-blue-600" />
            <span>Backup e Exportações</span>
          </h2>

          <!-- Backup Manual -->
          <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Backup Manual</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="p-4 border border-gray-200 rounded-lg">
                <h4 class="font-medium text-gray-900 mb-2">Backup Completo</h4>
                <p class="text-sm text-gray-500 mb-4">Inclui todos os dados: pacientes, agendamentos, financeiro</p>
                <button @click="gerarBackupCompleto" :disabled="gerandoBackup"
                  class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-400 transition-colors flex items-center justify-center space-x-2">
                  <Loader2 v-if="gerandoBackup" class="w-4 h-4 animate-spin" />
                  <Download v-else class="w-4 h-4" />
                  <span>{{ gerandoBackup ? 'Gerando...' : 'Gerar Backup' }}</span>
                </button>
              </div>

              <div class="p-4 border border-gray-200 rounded-lg">
                <h4 class="font-medium text-gray-900 mb-2">Exportar Dados Específicos</h4>
                <p class="text-sm text-gray-500 mb-4">Escolha quais dados exportar</p>
                <button @click="abrirModalExportacao"
                  class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors flex items-center justify-center space-x-2">
                  <FileText class="w-4 h-4" />
                  <span>Exportar Dados</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Backup Automático -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Backup Automático</h3>
            <form @submit.prevent="salvarConfigBackup" class="space-y-4">
              <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div>
                  <h4 class="text-sm font-medium text-gray-900">Ativar Backup Automático</h4>
                  <p class="text-sm text-gray-500">Gerar backups automaticamente em intervalos regulares</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="backup.automatico" class="sr-only peer" />
                  <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                  </div>
                </label>
              </div>

              <div v-if="backup.automatico" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Frequência do Backup</label>
                  <select v-model="backup.frequencia"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="diario">Diário</option>
                    <option value="semanal">Semanal</option>
                    <option value="mensal">Mensal</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Horário do Backup</label>
                  <input type="time" v-model="backup.horario"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
              </div>

              <div class="flex justify-end">
                <button type="submit" :disabled="salvandoBackup"
                  class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed transition-colors font-medium flex items-center space-x-2">
                  <Loader2 v-if="salvandoBackup" class="w-4 h-4 animate-spin" />
                  <Save v-else class="w-4 h-4" />
                  <span>{{ salvandoBackup ? 'Salvando...' : 'Salvar Configurações' }}</span>
                </button>
              </div>
            </form>
          </div>

          <!-- Histórico de Backups -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Histórico de Backups</h3>
            <div class="space-y-3">
              <div v-for="backup in historicoBackups" :key="backup.id"
                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <Database class="w-5 h-5 text-blue-600" />
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ backup.nome }}</div>
                    <div class="text-sm text-gray-500">{{ formatarData(backup.data) }} - {{ backup.tamanho }}</div>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <button @click="baixarBackup(backup)"
                    class="text-blue-600 hover:text-blue-900 p-2 rounded hover:bg-blue-50" title="Baixar">
                    <Download class="w-4 h-4" />
                  </button>
                  <button @click="excluirBackup(backup)"
                    class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-50" title="Excluir">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>

              <div v-if="historicoBackups.length === 0" class="text-center py-8 text-gray-500">
                <Database class="w-8 h-8 mx-auto mb-2 text-gray-400" />
                <p>Nenhum backup encontrado</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Exportação -->
    <div v-if="modalExportacaoAberto"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Exportar Dados</h3>
          <button @click="fecharModalExportacao" class="text-gray-400 hover:text-gray-600 p-1">
            <X class="w-6 h-6" />
          </button>
        </div>

        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">Selecione os dados para exportar:</label>
            <div class="space-y-2">
              <label class="flex items-center space-x-3">
                <input type="checkbox" v-model="exportacao.pacientes"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                <span class="text-sm text-gray-900">Pacientes</span>
              </label>
              <label class="flex items-center space-x-3">
                <input type="checkbox" v-model="exportacao.agendamentos"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                <span class="text-sm text-gray-900">Agendamentos</span>
              </label>
              <label class="flex items-center space-x-3">
                <input type="checkbox" v-model="exportacao.financeiro"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                <span class="text-sm text-gray-900">Dados Financeiros</span>
              </label>
              <label class="flex items-center space-x-3">
                <input type="checkbox" v-model="exportacao.parceiros"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                <span class="text-sm text-gray-900">Parceiros</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Formato do arquivo:</label>
            <select v-model="exportacao.formato"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="csv">CSV (Excel)</option>
              <option value="json">JSON</option>
              <option value="pdf">PDF</option>
            </select>
          </div>

          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button @click="fecharModalExportacao"
              class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
              Cancelar
            </button>
            <button @click="exportarDados" :disabled="exportandoDados"
              class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-green-400 transition-colors flex items-center space-x-2">
              <Loader2 v-if="exportandoDados" class="w-4 h-4 animate-spin" />
              <Download v-else class="w-4 h-4" />
              <span>{{ exportandoDados ? 'Exportando...' : 'Exportar' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mensagens de Feedback -->
    <div v-if="mensagemSucesso"
      class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2 z-50">
      <CheckCircle class="w-5 h-5" />
      <span>{{ mensagemSucesso }}</span>
    </div>

    <div v-if="mensagemErro"
      class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2 z-50">
      <AlertCircle class="w-5 h-5" />
      <span>{{ mensagemErro }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '../../services/axios.js'
import { useClinicStore } from '../../stores/clinic.js'
import {
  Settings, Building, Mail, CreditCard, Database,
  Loader2, AlertCircle, CheckCircle, Save, Edit,
  Key, X, Upload, Trash2, ImageIcon as Image, Heart, Download, FileText
} from 'lucide-vue-next'

const clinicStore = useClinicStore()

const storageKey = (name) => `marag_ajustes_${name}_${clinicStore.slug || 'default'}`

const loadLocal = (name, fallback) => {
  try {
    const raw = localStorage.getItem(storageKey(name))
    return raw ? { ...fallback, ...JSON.parse(raw) } : { ...fallback }
  } catch {
    return { ...fallback }
  }
}

const saveLocal = (name, data) => {
  localStorage.setItem(storageKey(name), JSON.stringify(data))
}

// ===== ESTADO REATIVO =====
const carregandoGeral = ref(false)
const erroGeral = ref('')
const mensagemSucesso = ref('')
const mensagemErro = ref('')

// Navegação por abas
const abaAtiva = ref('clinica')
const abas = [
  { id: 'clinica', nome: 'Clínica', icone: Building },
  { id: 'notificacoes', nome: 'Notificações', icone: Mail },
  { id: 'pagamento', nome: 'Pagamento', icone: CreditCard },
  { id: 'backup', nome: 'Backup', icone: Database }
]

// Estados de salvamento
const salvandoSenha = ref(false)
const salvandoClinica = ref(false)
const salvandoNotificacoes = ref(false)
const salvandoPagamento = ref(false)
const salvandoBackup = ref(false)

// Modais
const modalUsuarioAberto = ref(false)
const modalExportacaoAberto = ref(false)
const modoEdicaoUsuario = ref(false)

// Estados de operações
const salvandoUsuario = ref(false)
const gerandoBackup = ref(false)
const exportandoDados = ref(false)

// ===== DADOS DOS FORMULÁRIOS =====

// Senha do administrador
const senhaAdmin = ref({
  atual: '',
  nova: '',
  confirmar: ''
})


// Informações da clínica
const clinica = ref({
  nomeFantasia: '',
  razaoSocial: '',
  cnpj: '',
  telefone: '',
  whatsapp: '',
  email: '',
  cep: '',
  rua: '',
  numero: '',
  complemento: '',
  bairro: '',
  cidade: '',
  estado: '',
  horarioSemana: { inicio: '', fim: '' },
  horarioSabado: { inicio: '', fim: '' },
  logoUrl: ''
})

// Notificações
const notificacoes = ref({
  emailAutomatico: true,
  mensagemConfirmacao: '',
  mensagemLembrete: '',
  assinaturaEmail: '',
  whatsappAtivo: true,
  smsAtivo: false,
  mensagemWhatsapp: '',
  antecedenciaLembrete: 24,
  horarioEnvio: '09:00'
})

// Pagamento
const pagamento = ref({
  metodosAceitos: [],
  maxParcelas: 1,
  valorMinimoParcela: 50,
  descontoVista: 0,
  descontoPix: 0
})

const metodosPagamento = [
  { id: 'dinheiro', nome: 'Dinheiro', icone: 'DollarSign' },
  { id: 'pix', nome: 'PIX', icone: 'Smartphone' },
  { id: 'cartao_credito', nome: 'Cartão de Crédito', icone: 'CreditCard' },
  { id: 'cartao_debito', nome: 'Cartão de Débito', icone: 'CreditCard' },
  { id: 'boleto', nome: 'Boleto', icone: 'FileText' },
  { id: 'convenio', nome: 'Convênio', icone: 'Heart' }
]

// Backup
const backup = ref({
  automatico: false,
  frequencia: 'semanal',
  horario: '02:00'
})

const historicoBackups = ref([])

// Exportação
const exportacao = ref({
  pacientes: true,
  agendamentos: true,
  financeiro: false,
  parceiros: false,
  formato: 'csv'
})

// Erros de validação
const erros = ref({})

// ===== FUNÇÕES UTILITÁRIAS =====

/**
 * Formata CNPJ
 */
const formatarCNPJ = (event) => {
  let valor = event.target.value.replace(/\D/g, '')
  valor = valor.replace(/^(\d{2})(\d)/, '$1.$2')
  valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
  valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2')
  valor = valor.replace(/(\d{4})(\d)/, '$1-$2')
  clinica.value.cnpj = valor
}

/**
 * Formata telefone
 */
const formatarTelefone = (event) => {
  let valor = event.target.value.replace(/\D/g, '')
  valor = valor.replace(/^(\d{2})(\d)/, '($1) $2')
  valor = valor.replace(/(\d{5})(\d)/, '$1-$2')

  // Determina qual campo está sendo editado
  if (event.target === document.querySelector('input[v-model="clinica.telefone"]')) {
    clinica.value.telefone = valor
  } else if (event.target === document.querySelector('input[v-model="clinica.whatsapp"]')) {
    clinica.value.whatsapp = valor
  }
}

/**
 * Formata CEP
 */
const formatarCEP = (event) => {
  let valor = event.target.value.replace(/\D/g, '')
  valor = valor.replace(/^(\d{5})(\d)/, '$1-$2')
  clinica.value.cep = valor
}

/**
 * Busca CEP
 */
const buscarCEP = async () => {
  const cep = clinica.value.cep.replace(/\D/g, '')
  if (cep.length === 8) {
    try {
      const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
      const data = await response.json()

      if (!data.erro) {
        clinica.value.rua = data.logradouro
        clinica.value.bairro = data.bairro
        clinica.value.cidade = data.localidade
        clinica.value.estado = data.uf
      }
    } catch (error) {
      console.error('Erro ao buscar CEP:', error)
    }
  }
}

/**
 * Formata data
 */
const formatarData = (data) => {
  if (!data) return 'N/A'
  return new Date(data).toLocaleDateString('pt-BR')
}

/**
 * Limpa mensagens
 */
const limparMensagens = () => {
  setTimeout(() => {
    mensagemSucesso.value = ''
    mensagemErro.value = ''
  }, 5000)
}

// ===== FUNÇÕES DE SENHA =====

/**
 * Altera senha do administrador
 */
const alterarSenhaAdmin = async () => {
  // Validação
  erros.value = {}

  if (!senhaAdmin.value.atual) {
    erros.value.senhaAtual = 'Senha atual é obrigatória'
  }

  if (!senhaAdmin.value.nova) {
    erros.value.senhaNova = 'Nova senha é obrigatória'
  } else if (senhaAdmin.value.nova.length < 6) {
    erros.value.senhaNova = 'Nova senha deve ter pelo menos 6 caracteres'
  }

  if (!senhaAdmin.value.confirmar) {
    erros.value.senhaConfirmar = 'Confirmação de senha é obrigatória'
  } else if (senhaAdmin.value.nova !== senhaAdmin.value.confirmar) {
    erros.value.senhaConfirmar = 'Senhas não coincidem'
  }

  if (Object.keys(erros.value).length > 0) {
    return
  }

  salvandoSenha.value = true

  try {
    await axios.put('/auth/senha', {
      senha_atual: senhaAdmin.value.atual,
      senha_nova: senhaAdmin.value.nova
    })

    senhaAdmin.value = { atual: '', nova: '', confirmar: '' }
    mensagemSucesso.value = 'Senha alterada com sucesso!'
    limparMensagens()

  } catch (error) {
    console.error('Erro ao alterar senha:', error)
    if (error.response?.status === 401) {
      erros.value.senhaAtual = 'Senha atual incorreta'
    } else {
      mensagemErro.value = error.response?.data?.message || 'Erro ao alterar senha'
      limparMensagens()
    }
  } finally {
    salvandoSenha.value = false
  }
}

// ===== FUNÇÕES DE UPLOAD =====

/**
 * Handle upload do logo
 */
const handleLogoUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validações
  if (!file.type.startsWith('image/')) {
    mensagemErro.value = 'Apenas arquivos de imagem são aceitos'
    limparMensagens()
    return
  }

  if (file.size > 2 * 1024 * 1024) { // 2MB
    mensagemErro.value = 'Arquivo muito grande. Máximo 2MB'
    limparMensagens()
    return
  }

  try {
    const formData = new FormData()
    formData.append('logo', file)

    const response = await axios.post('/clinic/logo', formData)
    const url = response.data.url || response.data.data?.logo_url || ''
    clinica.value.logoUrl = url
    if (response.data.data) {
      clinicStore.setBranding(response.data.data)
    }
    mensagemSucesso.value = 'Logo enviado com sucesso!'
    limparMensagens()

  } catch (error) {
    console.error('Erro ao enviar logo:', error)
    mensagemErro.value = error.response?.data?.message || 'Erro ao enviar logo'
    limparMensagens()
  } finally {
    event.target.value = ''
  }
}

/**
 * Remove logo
 */
const removerLogo = async () => {
  if (!confirm('Tem certeza que deseja remover o logo?')) {
    return
  }

  try {
    const response = await axios.delete('/clinic/logo')
    clinica.value.logoUrl = ''
    if (response.data.data) {
      clinicStore.setBranding(response.data.data)
    }
    mensagemSucesso.value = 'Logo removido com sucesso!'
    limparMensagens()

  } catch (error) {
    console.error('Erro ao remover logo:', error)
    mensagemErro.value = error.response?.data?.message || 'Erro ao remover logo'
    limparMensagens()
  }
}
// ===== FUNÇÕES DE BACKUP =====

/**
 * Gera backup completo
 */
const gerarBackupCompleto = async () => {
  gerandoBackup.value = true
  try {
    mensagemErro.value = 'Backup completo ainda não está disponível nesta versão.'
    limparMensagens()
  } finally {
    gerandoBackup.value = false
  }
}

/**
 * Abre modal de exportação
 */
const abrirModalExportacao = () => {
  modalExportacaoAberto.value = true
}

/**
 * Fecha modal de exportação
 */
const fecharModalExportacao = () => {
  modalExportacaoAberto.value = false
}

/**
 * Exporta dados
 */
const exportarDados = async () => {
  const dadosSelecionados = Object.keys(exportacao.value)
    .filter(key => key !== 'formato' && exportacao.value[key])

  if (dadosSelecionados.length === 0) {
    mensagemErro.value = 'Selecione pelo menos um tipo de dado para exportar'
    limparMensagens()
    return
  }

  exportandoDados.value = true
  try {
    mensagemErro.value = 'Exportação ainda não está disponível nesta versão.'
    limparMensagens()
  } finally {
    exportandoDados.value = false
  }
}

/**
 * Baixa backup
 */
const baixarBackup = async () => {
  mensagemErro.value = 'Download de backup ainda não está disponível nesta versão.'
  limparMensagens()
}

/**
 * Exclui backup
 */
const excluirBackup = async (backup) => {
  if (!confirm(`Tem certeza que deseja excluir o backup "${backup.nome}"?`)) {
    return
  }
  const index = historicoBackups.value.findIndex(b => b.id === backup.id)
  if (index > -1) {
    historicoBackups.value.splice(index, 1)
    saveLocal('backups', historicoBackups.value)
  }
  mensagemSucesso.value = 'Registro removido localmente.'
  limparMensagens()
}

// ===== FUNÇÕES DE SALVAMENTO =====

/**
 * Salva informações da clínica
 */
const salvarInformacoesClinica = async () => {
  // Validação
  erros.value = {}

  if (!clinica.value.nomeFantasia.trim()) {
    erros.value.nomeFantasia = 'Nome fantasia é obrigatório'
  }

  if (!clinica.value.razaoSocial.trim()) {
    erros.value.razaoSocial = 'Razão social é obrigatória'
  }

  if (!clinica.value.cnpj.trim()) {
    erros.value.cnpj = 'CNPJ é obrigatório'
  }

  if (!clinica.value.telefone.trim()) {
    erros.value.telefone = 'Telefone é obrigatório'
  }

  if (!clinica.value.email.trim()) {
    erros.value.email = 'E-mail é obrigatório'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(clinica.value.email)) {
    erros.value.email = 'E-mail inválido'
  }

  if (Object.keys(erros.value).length > 0) {
    return
  }

  salvandoClinica.value = true

  try {
    const res = await axios.put('/clinic/branding', {
      nome: clinica.value.nomeFantasia.trim(),
      logo_url: clinica.value.logoUrl || null,
    })
    if (res.data?.data) {
      clinicStore.setBranding(res.data.data)
    }
    saveLocal('clinica', clinica.value)
    mensagemSucesso.value = 'Informações da clínica salvas com sucesso!'
    limparMensagens()

  } catch (error) {
    console.error('Erro ao salvar informações da clínica:', error)
    mensagemErro.value = error.response?.data?.message || 'Erro ao salvar informações da clínica'
    limparMensagens()
  } finally {
    salvandoClinica.value = false
  }
}

/**
 * Salva notificações
 */
const salvarNotificacoes = async () => {
  salvandoNotificacoes.value = true

  try {
    saveLocal('notificacoes', notificacoes.value)
    mensagemSucesso.value = 'Configurações de notificação salvas com sucesso!'
    limparMensagens()

  } catch (error) {
    console.error('Erro ao salvar notificações:', error)
    mensagemErro.value = 'Erro ao salvar notificações'
    limparMensagens()
  } finally {
    salvandoNotificacoes.value = false
  }
}

/**
 * Salva pagamento
 */
const salvarPagamento = async () => {
  salvandoPagamento.value = true

  try {
    saveLocal('pagamento', pagamento.value)
    mensagemSucesso.value = 'Configurações de pagamento salvas com sucesso!'
    limparMensagens()

  } catch (error) {
    console.error('Erro ao salvar pagamento:', error)
    mensagemErro.value = 'Erro ao salvar pagamento'
    limparMensagens()
  } finally {
    salvandoPagamento.value = false
  }
}

/**
 * Salva configurações de backup
 */
const salvarConfigBackup = async () => {
  salvandoBackup.value = true

  try {
    saveLocal('backup', backup.value)
    mensagemSucesso.value = 'Configurações de backup salvas com sucesso!'
    limparMensagens()

  } catch (error) {
    console.error('Erro ao salvar configurações de backup:', error)
    mensagemErro.value = 'Erro ao salvar configurações de backup'
    limparMensagens()
  } finally {
    salvandoBackup.value = false
  }
}

// ===== FUNÇÕES DE CARREGAMENTO =====

/**
 * Carrega histórico de backups
 */
const carregarHistoricoBackups = async () => {
  try {
    const raw = localStorage.getItem(storageKey('backups'))
    historicoBackups.value = raw ? JSON.parse(raw) : []
  } catch (error) {
    console.error('Erro ao carregar histórico de backups:', error)
    historicoBackups.value = []
  }
}

/**
 * Carrega todas as configurações
 */
const carregarTodasConfiguracoes = async () => {
  carregandoGeral.value = true
  erroGeral.value = ''

  try {
    const branding = await clinicStore.loadBranding()
    const clinicaLocal = loadLocal('clinica', clinica.value)

    clinica.value = {
      ...clinica.value,
      ...clinicaLocal,
      nomeFantasia: branding?.nome || clinicaLocal.nomeFantasia || '',
      logoUrl: branding?.logo_url || clinicaLocal.logoUrl || '',
    }

    notificacoes.value = loadLocal('notificacoes', notificacoes.value)
    pagamento.value = loadLocal('pagamento', pagamento.value)
    backup.value = loadLocal('backup', backup.value)
    await carregarHistoricoBackups()

  } catch (error) {
    console.error('Erro ao carregar configurações:', error)
    erroGeral.value = 'Erro ao carregar configurações. Tente novamente.'
    historicoBackups.value = []

  } finally {
    carregandoGeral.value = false
  }
}

// ===== INICIALIZAÇÃO =====

onMounted(() => {
  console.log('Componente Ajustes montado')
  carregarTodasConfiguracoes()
})
</script>

<style scoped>
/* Animações para as mensagens de feedback */
.fixed {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }

  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Estilo personalizado para toggles */
.peer:checked~div {
  background-color: #2563eb;
}

.peer:focus~div {
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
}

/* Hover effects */
button:hover {
  transform: translateY(-1px);
}

/* Modal backdrop */
.fixed.inset-0 {
  backdrop-filter: blur(4px);
}

/* Scrollbar customization */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Tabs styling */
nav button {
  position: relative;
  transition: all 0.2s ease;
}

nav button:hover {
  background-color: rgba(59, 130, 246, 0.05);
}

/* Color picker styling */
input[type="color"] {
  border: none;
  cursor: pointer;
}

input[type="color"]::-webkit-color-swatch-wrapper {
  padding: 0;
}

input[type="color"]::-webkit-color-swatch {
  border: none;
  border-radius: 4px;
}
</style>