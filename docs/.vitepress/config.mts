import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'AI Auth System',
  description: 'Dokumentasi AI-Powered Authentication dan Risk Engine',
  lang: 'id-ID',
  ignoreDeadLinks: true,
  outDir: './.vitepress/dist',

  head: [
    ['link', { rel: 'icon', href: '/favicon.ico' }],
    ['meta', { name: 'theme-color', content: '#0f172a' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:title', content: 'AI Auth System Docs' }],
    ['meta', { property: 'og:description', content: 'Dokumentasi teknis dan operasional AI Auth System' }],
  ],

  themeConfig: {
    logo: '/logo.svg',

    nav: [
      { text: 'Beranda', link: '/' },
      { text: 'Panduan', link: '/guide/' },
      { text: 'Arsitektur', link: '/architecture/modules' },
      { text: 'API', link: '/api/' },
    ],

    sidebar: {
      '/guide/': [
        {
          text: 'Mulai Di Sini',
          items: [
            { text: 'Ringkasan Panduan', link: '/guide/' },
            { text: 'Instalasi', link: '/guide/installation' },
            { text: 'Arsitektur Docker', link: '/guide/docker' },
            { text: 'Konfigurasi Environment', link: '/guide/environment' },
            { text: 'Operasional Harian', link: '/guide/operations' },
            { text: 'Troubleshooting', link: '/guide/troubleshooting' },
          ],
        },
      ],
      '/architecture/': [
        {
          text: 'Arsitektur Aplikasi',
          items: [
            { text: 'Modul Laravel', link: '/architecture/modules' },
            { text: 'AI Risk Engine', link: '/architecture/ai-engine' },
            { text: 'Flow Autentikasi', link: '/architecture/auth-flow' },
          ],
        },
      ],
      '/api/': [
        {
          text: 'Referensi API',
          items: [
            { text: 'Ikhtisar API', link: '/api/' },
            { text: 'Authentication API', link: '/api/auth' },
            { text: 'AI Risk API', link: '/api/ai-risk' },
            { text: 'Error Codes', link: '/api/errors' },
          ],
        },
      ],
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/mixudev/mixuauth' },
    ],

    footer: {
      message: 'Internal Technical Documentation',
      copyright: 'Copyright 2026 AI Auth Team',
    },

    search: {
      provider: 'local',
    },

    editLink: {
      pattern: 'https://github.com/mixudev/mixuauth/edit/main/docs/:path',
      text: 'Edit halaman ini',
    },
  },
})
