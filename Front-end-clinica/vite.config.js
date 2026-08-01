import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { fileURLToPath, URL } from 'node:url'

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  // Soft launch: impede bundle de prod/staging sem API URL (evita localhost no cliente)
  if (mode === 'production' && !process.env.VITE_API_URL) {
    throw new Error(
      'Defina VITE_API_URL antes do build de staging/prod (ex.: https://sua-api.up.railway.app/api)'
    )
  }

  return {
    plugins: [vue(), tailwindcss()],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
      },
    },
  }
})
