<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full bg-[#F8F7FC]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') - ميقا الجديدة للاتصالات والتقنية</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts: Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Vite & CDN Fallback for instant viewing) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#F3F0FF',
                            100: '#E9D5FF',
                            200: '#DDD6FE',
                            300: '#C4B5FD',
                            400: '#A78BFA',
                            500: '#8B5CF6',
                            600: '#6F42C1',
                            700: '#5B2BB8',
                            800: '#4C1D95',
                            900: '#2C1D54',
                            950: '#1E1538',
                        }
                    },
                    fontFamily: {
                        sans: ['Cairo', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #F8F7FC;
        }
        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }
        [x-cloak]{display:none !important}
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .motion-reduce\:animate-none {
            animation: none !important;
        }
    </style>
</head>
<body class="h-full text-gray-800 antialiased selection:bg-purple-500 selection:text-white">

    <div class="min-h-screen flex">
        <!-- Sidebar Component -->
        <x-sidebar />

        <!-- Main Workspace (offset by sidebar width on desktop) -->
        <div class="flex-1 lg:mr-64 flex flex-col min-w-0 transition-all duration-300">
            <!-- Navbar Component -->
            <x-navbar />

            <!-- Main Page Content Container -->
            <main class="flex-1 p-4 md:p-6 lg:p-8 space-y-6 max-w-7xl w-full mx-auto">
                @yield('content')
            </main>

            <!-- Compact Footer -->
            <footer class="mt-auto py-4 px-6 border-t border-gray-200/60 bg-white/50 text-center text-xs text-gray-400">
                <p>جميع الحقوق محفوظة &copy; {{ date('Y') }} شركة ميقا الجديدة للاتصالات والتقنية MEGA ALJADIDA</p>
            </footer>
        </div>
    </div>

    <!-- Mobile Drawer Overlay JS -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('sidebarToggle');
            const closeBtn = document.getElementById('sidebarClose');
            const sidebarLinks = sidebar?.querySelectorAll('a');

            // Check if screen is desktop (lg and above)
            const isDesktop = window.matchMedia('(min-width: 1024px)');

            // Open sidebar function
            function openSidebar() {
                if (!sidebar || !overlay) return;
                sidebar.classList.remove('translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.remove('hidden');
                // Small delay to allow display:block to apply before opacity transition
                requestAnimationFrame(() => {
                    overlay.classList.remove('opacity-0');
                    overlay.classList.add('opacity-100');
                });
                // Lock body scroll on mobile
                if (!isDesktop.matches) {
                    document.body.style.overflow = 'hidden';
                }
            }

            // Close sidebar function
            function closeSidebar() {
                if (!sidebar || !overlay) return;
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('translate-x-full');
                overlay.classList.remove('opacity-100');
                overlay.classList.add('opacity-0');
                // Wait for transition to finish before hiding
                setTimeout(() => {
                    overlay.classList.add('hidden');
                }, 300);
                // Unlock body scroll
                document.body.style.overflow = '';
            }

            // Toggle button click
            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    if (sidebar.classList.contains('translate-x-full')) {
                        openSidebar();
                    } else {
                        closeSidebar();
                    }
                });
            }

            // Close button click
            if (closeBtn) {
                closeBtn.addEventListener('click', closeSidebar);
            }

            // Overlay click
            if (overlay) {
                overlay.addEventListener('click', closeSidebar);
            }

            // Escape key press
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !sidebar.classList.contains('translate-x-full')) {
                    closeSidebar();
                }
            });

            // Close on link click (mobile only)
            if (sidebarLinks) {
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        if (!isDesktop.matches) {
                            closeSidebar();
                        }
                    });
                });
            }

            // Sync state with screen size changes
            isDesktop.addEventListener('change', (e) => {
                if (e.matches) {
                    // Desktop: ensure sidebar is visible
                    sidebar.classList.remove('translate-x-full');
                    sidebar.classList.add('translate-x-0');
                    overlay.classList.add('hidden');
                    document.body.style.overflow = '';
                } else {
                    // Mobile: hide sidebar by default
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });

            // Initial state check
            if (!isDesktop.matches) {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('translate-x-full');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
