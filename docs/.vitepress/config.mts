import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'MIXUAUTH',
  description: 'Dokumentasi Ekstensif Sistem Autentikasi AI-Powered dan Risk Engine',
  lang: 'id-ID',
  ignoreDeadLinks: true,
  outDir: './.vitepress/dist',

  head: [
    ['link', { rel: 'icon', href: '/favicon.ico' }],
    ['meta', { name: 'theme-color', content: '#0f172a' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:title', content: 'MIXUAUTH Docs' }],
    ['meta', { property: 'og:description', content: 'Dokumentasi teknis, arsitektur, dan operasional MIXUAUTH' }],
  ],

  themeConfig: {
    logo: '/logo.png',

    nav: [
      { text: 'Beranda', link: '/' },
      { text: 'Docs', link: '/guide/' },
      { text: 'Dev', link: '/developer/' },
      { text: 'API', link: '/api/' },
      { text: 'Changelog', link: 'https://github.com/mixudev/mixuauth/releases' }
    ],

    sidebar: {
      // Sidebar terpadu untuk semua halaman dokumentasi
      '/guide/': sharedSidebar(),
      '/architecture/': sharedSidebar(),
      '/security/': sharedSidebar(),
      '/auth/': sharedSidebar(),
      '/sso/': sharedSidebar(),
      '/developer/': sharedSidebar(),
      
      // Sidebar khusus untuk API
      '/api/': [
        {
          text: 'Referensi API',
          collapsed: false,
          items: [
            { text: 'Ikhtisar API', link: '/api/' },
            { text: 'Authentication API', link: '/api/auth' },
            { text: 'AI Risk API', link: '/api/ai-risk' },
            { text: 'Katalog Error Codes', link: '/api/errors' },
          ],
        },
      ],
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/mixudev/mixuauth' }
    ],

    search: {
      provider: 'local',
      options: {
        locales: {
          root: {
            translations: {
              button: {
                buttonText: 'Cari Dokumen...',
                buttonAriaLabel: 'Cari Dokumen'
              },
              modal: {
                noResultsText: 'Tidak ada hasil untuk',
                resetButtonTitle: 'Hapus pencarian',
                footer: {
                  selectText: 'pilih',
                  navigateText: 'navigasi'
                }
              }
            }
          }
        }
      }
    },

    footer: {
      message: 'Dirilis di bawah lisensi MIT.',
      copyright: 'Copyright © 2026-present AI Auth Security Team'
    }
  },
})

// Fungsi untuk menyediakan sidebar yang sama di berbagai folder
function sharedSidebar() {
  return [
    {
      text: 'Persiapan & Instalasi',
      collapsed: false,
      items: [
        { text: 'Ringkasan Panduan', link: '/guide/' },
        { text: 'Instalasi Sistem', link: '/guide/installation' },
        { text: 'Arsitektur Docker', link: '/guide/docker' },
        { text: 'Struktur Folder Proyek', link: '/guide/folder-structure' },
        { text: 'Konfigurasi Environment', link: '/guide/environment' },
        { text: 'Konfigurasi Lengkap', link: '/guide/configuration-reference' },
      ],
    },
    {
      text: 'Arsitektur Backend',
      collapsed: true,
      items: [
        { text: 'Modul Modular Laravel', link: '/architecture/modules' },
        { text: 'Skema Database & Model', link: '/architecture/database' },
        { text: 'Template Email', link: '/architecture/emails' },
      ],
    },
    {
      text: 'Engine Keamanan',
      collapsed: true,
      items: [
        { text: 'AI Risk Engine', link: '/architecture/ai-engine' },
        { text: 'Flow Autentikasi', link: '/architecture/auth-flow' },
      ],
    },
    {
      text: 'Layer Keamanan Deep-Dive',
      collapsed: true,
      items: [
        { text: 'End-to-End Login Layers', link: '/security/login-layers' },
        { text: 'Device Fingerprint & Sesi', link: '/security/device-fingerprint' },
        { text: 'AI Risk Scoring', link: '/security/ai-risk-scoring' },
        { text: 'MFA & Backup Codes', link: '/security/mfa-backup' },
      ],
    },
    {
      text: 'Otentikasi Lanjutan',
      collapsed: true,
      items: [
        { text: 'Social Auth (Google/Github)', link: '/auth/social-auth' },
      ]
    },
    {
      text: 'Single Sign-On (SSO)',
      collapsed: false,
      items: [
        { text: 'Ikhtisar SSO Server', link: '/sso/overview' },
        { text: 'Arsitektur & Direktori', link: '/sso/architecture' },
        { text: 'Access Area & Security', link: '/sso/security' },
        { text: 'Panduan Integrasi Klien', link: '/sso/integration' },
      ],
    },
    {
      text: 'Operasional',
      collapsed: true,
      items: [
        { text: 'Operasional Harian', link: '/guide/operations' },
        { text: 'Troubleshooting & Runbook', link: '/guide/troubleshooting' },
      ],
    },
    {
      text: 'Developer Guide',
      collapsed: false,
      items: [
        { text: 'Ikhtisar Developer', link: '/developer/' },
        { text: 'Integrasi Klien', link: '/developer/client-integration' },
        { text: 'Timezone Management', link: '/developer/timezone' },
        { text: 'Audit Logging', link: '/developer/audit-log' },
        { text: 'Security Client', link: '/developer/security-client' },
      ],
    }
  ]
}
