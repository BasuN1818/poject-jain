<script>
    // Initialize theme
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    function toggleTheme() {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    }
</script>
<style>
    /* Global Dark Mode Overrides */
    html.dark body,
    html.dark .main-content {
        background: #000000 !important;
        color: #f5f5f7 !important;
    }

    html.dark .card,
    html.dark .stat-card,
    html.dark .modal-box {
        background: #1c1c1e !important;
        border-color: #2c2c2e !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5) !important;
    }

    html.dark .idp-card {
        background: #2c2c2e !important;
        border-left-color: #34C759 !important;
    }

    html.dark .sidebar,
    html.dark .emp-sidebar {
        background: rgba(28, 28, 30, 0.85) !important;
        border-color: #2c2c2e !important;
    }

    html.dark .text-apple-dark,
    html.dark .text-gray-900,
    html.dark .text-gray-800,
    html.dark .text-gray-700 {
        color: #ffffff !important;
    }

    html.dark .text-apple-gray,
    html.dark .text-gray-600,
    html.dark .text-gray-500 {
        color: #a1a1a6 !important;
    }

    html.dark .bg-white {
        background-color: #1c1c1e !important;
    }

    html.dark .bg-gray-50,
    html.dark .bg-gray-50\/50 {
        background-color: #2c2c2e !important;
        border-color: #3a3a3c !important;
    }

    html.dark .border-gray-50,
    html.dark .border-gray-100 {
        border-color: #3a3a3c !important;
    }

    html.dark .border-t,
    html.dark .border-b {
        border-color: #3a3a3c !important;
    }

    html.dark .input-field,
    html.dark .time-input,
    html.dark .select-status {
        background: #2c2c2e !important;
        border-color: #3a3a3c !important;
        color: #fff !important;
    }

    html.dark .nav-item,
    html.dark .emp-nav-item {
        color: #f5f5f7 !important;
    }

    html.dark .nav-item:hover,
    html.dark .emp-nav-item:hover {
        background: #2c2c2e !important;
    }

    /* Specific overrides for active states and highlights */
    html.dark .nav-item.active {
        background: rgba(10, 132, 255, 0.2) !important;
        color: #0a84ff !important;
    }

    html.dark .emp-nav-item.active {
        background: rgba(48, 209, 88, 0.2) !important;
        color: #30d158 !important;
    }

    html.dark th {
        background-color: #1c1c1e !important;
        color: #a1a1a6 !important;
    }

    html.dark td {
        color: #f5f5f7 !important;
    }

    html.dark tr:hover td {
        background-color: #2c2c2e !important;
    }

    /* Status Badge Colors Dark */
    html.dark .bg-blue-50 {
        background-color: rgba(10, 132, 255, 0.15) !important;
    }

    html.dark .text-apple-blue {
        color: #0a84ff !important;
    }

    html.dark .bg-green-50 {
        background-color: rgba(48, 209, 88, 0.15) !important;
        border-color: rgba(48, 209, 88, 0.3) !important;
    }

    html.dark .text-green-700 {
        color: #30d158 !important;
    }

    html.dark .bg-red-50 {
        background-color: rgba(255, 69, 58, 0.15) !important;
        border-color: rgba(255, 69, 58, 0.3) !important;
    }

    html.dark .text-red-700 {
        color: #ff453a !important;
    }

    html.dark .bg-orange-50 {
        background-color: rgba(255, 159, 10, 0.15) !important;
        border-color: rgba(255, 159, 10, 0.3) !important;
    }

    html.dark .text-orange-700 {
        color: #ff9f0a !important;
    }

    html.dark .bg-purple-50 {
        background-color: rgba(191, 90, 242, 0.15) !important;
        border-color: rgba(191, 90, 242, 0.3) !important;
    }

    /* Specific select colors dark */
    html.dark .select-present {
        background-color: rgba(48, 209, 88, 0.1) !important;
        color: #30d158 !important;
    }

    html.dark .select-absent {
        background-color: rgba(255, 69, 58, 0.1) !important;
        color: #ff453a !important;
    }

    html.dark .select-late {
        background-color: rgba(255, 159, 10, 0.1) !important;
        color: #ff9f0a !important;
    }

    html.dark .select-half {
        background-color: rgba(191, 90, 242, 0.1) !important;
        color: #bf5af2 !important;
    }
</style>