import { toast } from 'vue3-toastify'

/**
 * Opções padrão para todos os toasts
 * Baseadas na configuração do main.js
 */
const defaultOptions = {
  autoClose: 2000,
  position: 'top-right',
  transition: 'slide'
}

/**
 * Mescla as opções padrão com opções customizadas
 * @param {Object} customOptions - Opções customizadas
 * @returns {Object} Opções mescladas
 */
const mergeOptions = (customOptions = {}) => {
  return {
    ...defaultOptions,
    ...customOptions
  }
}

/**
 * Composable para padronizar o uso de toasts/notificações no projeto
 * 
 * @returns {Object} Objeto com funções para exibir diferentes tipos de toast
 * 
 * @example
 * import { useToast } from '@/composables/useToast'
 * 
 * const { toastSuccess, toastError } = useToast()
 * 
 * // Uso simples
 * toastSuccess('Operação realizada com sucesso!')
 * 
 * // Uso com opções customizadas
 * toastError('Erro ao salvar', { autoClose: 5000 })
 */
export function useToast() {
  return {
    toastSuccess,
    toastError,
    toastWarning,
    toastInfo,
    toastDefault
  }
}

/**
 * Exibe uma notificação de sucesso
 * Pode ser importada diretamente sem usar o composable
 * @param {String} message - Mensagem a ser exibida
 * @param {Object} options - Opções customizadas (opcional)
 * 
 * @example
 * import { toastSuccess } from '@/composables/useToast'
 * toastSuccess('Operação realizada com sucesso!')
 */
export function toastSuccess(message, options = {}) {
  toast.success(message, mergeOptions(options))
}

/**
 * Exibe uma notificação de erro
 * Pode ser importada diretamente sem usar o composable
 * @param {String} message - Mensagem a ser exibida
 * @param {Object} options - Opções customizadas (opcional)
 * 
 * @example
 * import { toastError } from '@/composables/useToast'
 * toastError('Erro ao salvar', { autoClose: 5000 })
 */
export function toastError(message, options = {}) {
  toast.error(message, mergeOptions(options))
}

/**
 * Exibe uma notificação de aviso
 * Pode ser importada diretamente sem usar o composable
 * @param {String} message - Mensagem a ser exibida
 * @param {Object} options - Opções customizadas (opcional)
 * 
 * @example
 * import { toastWarning } from '@/composables/useToast'
 * toastWarning('Atenção!')
 */
export function toastWarning(message, options = {}) {
  toast.warning(message, mergeOptions(options))
}

/**
 * Exibe uma notificação informativa
 * Pode ser importada diretamente sem usar o composable
 * @param {String} message - Mensagem a ser exibida
 * @param {Object} options - Opções customizadas (opcional)
 * 
 * @example
 * import { toastInfo } from '@/composables/useToast'
 * toastInfo('Informação importante')
 */
export function toastInfo(message, options = {}) {
  toast.info(message, mergeOptions(options))
}

/**
 * Exibe uma notificação padrão (sem tipo específico)
 * Pode ser importada diretamente sem usar o composable
 * @param {String} message - Mensagem a ser exibida
 * @param {Object} options - Opções customizadas (opcional)
 * 
 * @example
 * import { toastDefault } from '@/composables/useToast'
 * toastDefault('Notificação padrão')
 */
export function toastDefault(message, options = {}) {
  toast(message, mergeOptions(options))
}

