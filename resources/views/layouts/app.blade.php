<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full bg-[#F8F7FC]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') - ميقا الجديدة للاتصالات والتقنية</title>

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
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('aside');
            
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('-translate-x-full');
                    sidebar.classList.toggle('translate-x-0');
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
