import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

const API_PROXY_TARGET = process.env.VITE_API_PROXY_TARGET || 'http://host.docker.internal:8000'

export default defineConfig(async () => {
  const plugins = [react()]

  if (process.env.NODE_ENV !== 'production' && process.env.VITE_COVERAGE === 'true') {
    const { default: istanbul } = await import('vite-plugin-istanbul')
    plugins.push(istanbul({ include: 'src/**/*' }))
  }

  return {
    plugins,
    server: {
      host: '0.0.0.0',
      port: 5173,
      proxy: {
        '/api': { target: API_PROXY_TARGET, changeOrigin: true },
        '/login': { target: API_PROXY_TARGET, changeOrigin: true },
        '/logout': { target: API_PROXY_TARGET, changeOrigin: true },
        '/sanctum': { target: API_PROXY_TARGET, changeOrigin: true },
      },
    },
  }
})
