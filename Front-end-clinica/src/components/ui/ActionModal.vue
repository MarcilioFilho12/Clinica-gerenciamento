<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-[100]" @close="closeModal">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 z-[100] bg-black/30 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-[100] w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
          <TransitionChild as="template" enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <DialogPanel
              :class="['relative transform overflow-visible rounded-lg bg-white border-2 text-left shadow-xl transition-all sm:my-8', borderColorClass, modalWidth || 'sm:max-w-2xl']">
              <div class="bg-white px-4 pb-2 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start mb-2">
                  <div class="mt-3 text-center sm:mx-4 sm:mt-0 sm:text-left">
                    <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">{{ titulo }}
                    </DialogTitle>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500">{{ subtitulo }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="px-4 mt-2">
                <slot></slot>
              </div>
              <div class="bg-white px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 mt-4">
                <button :disabled="actionDisabled" type="button"
                  class="inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto"
                  :class="getButtonClasses()" @click="action">
                  {{ actionLabel }}
                </button>
                <button type="button"
                  class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-md ring-1 ring-inset ring-gray-400 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                  @click="cancel">
                  {{ cancelLabel }}
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { computed } from "vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";

const emit = defineEmits(["acao", "cancel", "close"]);

const props = defineProps({
  actionDisabled: { default: false },
  open: {
    type: Boolean,
    required: true,
  },
  actionLabel: {
    type: String,
    required: true,
    default: "Enviar",
  },
  cancelLabel: {
    type: String,
    default: "Cancelar",
  },
  titulo: {
    type: String,
    required: false,
    default: "",
  },
  subtitulo: {
    type: String,
    required: false,
    default: "",
  },
  modalWidth: {
    type: String,
    default: "sm:max-w-2xl",
  },
  actionVariant: {
    type: String,
    default: "blue", // blue, red, green, etc.
  },
  borderColor: {
    type: String,
    default: "blue", // blue, danger (red), warning (yellow)
    validator: (value) => ["blue", "danger", "warning"].includes(value),
  },
});

const getButtonClasses = () => {
  const variants = {
    blue: 'hover:bg-blue-600 bg-blue-700',
    red: 'hover:bg-red-600 bg-red-700',
    green: 'hover:bg-green-600 bg-green-700',
    gray: 'hover:bg-gray-600 bg-gray-700',
  };

  const baseClasses = variants[props.actionVariant] || variants.blue;

  if (props.actionDisabled) {
    return baseClasses + ' opacity-50 cursor-not-allowed hover:bg-none';
  }

  return baseClasses;
};

const borderColorClass = computed(() => {
  const colors = {
    blue: 'border-blue-700',
    danger: 'border-red-700',
    warning: 'border-yellow-500',
  };

  return colors[props.borderColor] || colors.blue;
});

const action = () => {
  emit("acao");
};

const cancel = () => {
  emit("cancel");
};

const closeModal = () => {
  emit("cancel");
};
</script>
