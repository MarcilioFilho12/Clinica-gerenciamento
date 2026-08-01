import ActionModal from './ui/ActionModal.vue';
import Sidebar from './layout/Sidebar.vue';
import Header from './layout/Header.vue';
import BaseInput from './ui/BaseInput.vue';
import BaseSelect from './ui/BaseSelect.vue';
import BaseTextarea from './ui/BaseTextarea.vue';
import InputCPF from './ui/InputCPF.vue';
import InputTelefone from './ui/InputTelefone.vue';
import InputEmail from './ui/InputEmail.vue';
import InputData from './ui/InputData.vue';
import InputNumber from './ui/InputNumber.vue';
import BaseButton from './ui/BaseButton.vue';
import BaseCard from './ui/BaseCard.vue';
import TypeaheadInput from './ui/TypeaheadInput.vue';
import SearchBar from './ui/SearchBar.vue';
import PageHeader from './layout/PageHeader.vue';
import Breadcrumbs from './layout/Breadcrumbs.vue';


export default function registerGlobalComponents(app) {
  // Componentes UI existentes
  app.component('ActionModal', ActionModal);
  app.component('BaseInput', BaseInput);
  app.component('BaseSelect', BaseSelect);
  app.component('BaseTextarea', BaseTextarea);
  app.component('InputCPF', InputCPF);
  app.component('InputTelefone', InputTelefone);
  app.component('InputEmail', InputEmail);
  app.component('InputData', InputData);
  app.component('InputNumber', InputNumber);
  app.component('BaseButton', BaseButton);
  app.component('BaseCard', BaseCard);
  app.component('TypeaheadInput', TypeaheadInput);
  app.component('SearchBar', SearchBar);
  
  // Componentes Layout existentes
  app.component('Sidebar', Sidebar);
  app.component('Header', Header);
  app.component('PageHeader', PageHeader);
  app.component('Breadcrumbs', Breadcrumbs);
  
}

