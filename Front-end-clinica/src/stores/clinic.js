import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import axios from '../services/axios.js'

const STORAGE_SLUG = 'clinic_slug'
const STORAGE_BRANDING = 'clinic_branding'

export const useClinicStore = defineStore('clinic', () => {
  const slug = ref(localStorage.getItem(STORAGE_SLUG) || import.meta.env.VITE_CLINIC_SLUG || '')
  const branding = ref(readBranding())

  const nome = computed(() => branding.value?.nome || 'Sua Clínica')
  const logoUrl = computed(() => branding.value?.logo_url || null)
  const corPrimaria = computed(() => branding.value?.cor_primaria || '#0676a6')
  const corSecundaria = computed(() => branding.value?.cor_secundaria || '#D4AF37')

  function readBranding() {
    try {
      const raw = localStorage.getItem(STORAGE_BRANDING)
      return raw ? JSON.parse(raw) : null
    } catch {
      return null
    }
  }

  function setSlug(value) {
    const next = String(value || '').trim().toLowerCase()
    slug.value = next
    if (next) localStorage.setItem(STORAGE_SLUG, next)
    else localStorage.removeItem(STORAGE_SLUG)
  }

  function setBranding(data) {
    branding.value = data
    if (data) localStorage.setItem(STORAGE_BRANDING, JSON.stringify(data))
    else localStorage.removeItem(STORAGE_BRANDING)
    applyCssVars()
  }

  function applyCssVars() {
    const root = document.documentElement
    root.style.setProperty('--clinic-primary', corPrimaria.value)
    root.style.setProperty('--clinic-secondary', corSecundaria.value)
    if (branding.value?.nome) {
      document.title = `${branding.value.nome} | Marag`
    }
  }

  async function loadBranding(forcedSlug) {
    const s = (forcedSlug || slug.value || '').trim().toLowerCase()
    if (!s) return null
    const { data } = await axios.get('/clinic/branding', { params: { slug: s } })
    if (data?.success && data.data) {
      setSlug(s)
      setBranding(data.data)
      return data.data
    }
    return null
  }

  function clear() {
    // mantém slug local para próximo login; limpa só branding se quiser
  }

  return {
    slug,
    branding,
    nome,
    logoUrl,
    corPrimaria,
    corSecundaria,
    setSlug,
    setBranding,
    applyCssVars,
    loadBranding,
    clear,
  }
})
