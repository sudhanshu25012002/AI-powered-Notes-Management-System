<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Notes App')</title>
    <meta name="description" content="@yield('meta_description', 'AI-Powered Notes Management System with semantic search and AI summaries.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📝</text></svg>">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-black dark:text-neutral-100 min-h-screen transition-colors duration-200">

    {{-- Navigation --}}
    <nav class="bg-white border-b border-gray-200 dark:bg-neutral-900 dark:border-neutral-800 shadow-sm sticky top-0 z-10 transition-colors duration-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <a href="/notes" class="flex items-center gap-2 text-xl font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 transition">
                <span>📝</span>
                <span>Notes App</span>
            </a>
            <div class="flex items-center gap-4">
                <button onclick="toggleTheme()" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-800 transition" aria-label="Toggle Theme">
                    <svg id="theme-toggle-sun-icon" class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.46 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z"/></svg>
                    <svg id="theme-toggle-moon-icon" class="w-5 h-5 block dark:hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                </button>
                <script>
                    function toggleTheme() {
                        if (document.documentElement.classList.contains('dark')) {
                            document.documentElement.classList.remove('dark');
                            localStorage.setItem('theme', 'light');
                        } else {
                            document.documentElement.classList.add('dark');
                            localStorage.setItem('theme', 'dark');
                        }
                    }
                </script>
                <a href="/notes/create"
                   class="bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center gap-1">
                    <span>+</span> New Note
                </a>
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="max-w-5xl mx-auto px-4 sm:px-6 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-700 dark:bg-green-950/20 dark:border-green-800/50 dark:text-green-400 text-sm px-4 py-3 rounded-lg flex items-center gap-2">
                <span>✅</span> {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-5xl mx-auto px-4 sm:px-6 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-700 dark:bg-red-950/20 dark:border-red-800/50 dark:text-red-400 text-sm px-4 py-3 rounded-lg flex items-center gap-2">
                <span>❌</span> {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Main content --}}
    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 dark:border-neutral-800 mt-12 py-6 text-center text-xs text-gray-400 dark:text-neutral-500">
        AI-Powered Notes · Laravel 12 + Gemini API
    </footer>

</body>
</html>
