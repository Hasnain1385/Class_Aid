<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $e($title ?? 'Class Aid') ?> - EduAid Suite</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        glass: {
                            50: 'rgba(255, 255, 255, 0.05)',
                            100: 'rgba(255, 255, 255, 0.1)',
                            200: 'rgba(255, 255, 255, 0.2)',
                            300: 'rgba(255, 255, 255, 0.3)',
                            800: 'rgba(15, 23, 42, 0.75)',
                            900: 'rgba(11, 15, 25, 0.85)',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Custom Glassmorphism CSS -->
    <link rel="stylesheet" href="/assets/css/glassmorphism.css">
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-indigo-500 selection:text-white pb-20 md:pb-0">

    <!-- Top Glass Navigation Bar -->
    <header class="sticky top-0 z-40 w-full backdrop-blur-xl bg-white/70 dark:bg-slate-900/70 border-b border-gray-200/50 dark:border-white/10 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            
            <!-- Brand Logo & School Name -->
            <div class="flex items-center gap-3">
                <a href="/dashboard" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform">
                        <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="font-bold text-base tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-violet-600 dark:from-indigo-400 dark:to-violet-400">
                            Class Aid
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 hidden sm:block truncate max-w-[140px]">
                            <?= $e($user['school_name'] ?? 'Classroom Suite') ?>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Global Live Student Search Bar -->
            <div class="flex-1 max-w-md relative search-wrapper">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <input type="text" 
                           class="global-student-search w-full pl-9 pr-4 py-2 text-xs sm:text-sm rounded-xl glass-input placeholder-gray-400 dark:placeholder-gray-500" 
                           placeholder="Search student by name, ID (e.g. ADM-1001)...">
                </div>
                <!-- Instant Live Results Dropdown -->
                <div class="search-results-dropdown hidden absolute left-0 right-0 mt-2 glass-card p-1 shadow-2xl max-h-80 overflow-y-auto z-50"></div>
            </div>

            <!-- Right Controls: Theme Toggle & User Menu -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Theme Toggle Button -->
                <button type="button" class="theme-toggle p-2 rounded-xl text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100/50 dark:hover:bg-white/10 transition-colors" title="Toggle Theme">
                    <i data-lucide="sun" class="w-5 h-5 theme-icon-sun"></i>
                    <i data-lucide="moon" class="w-5 h-5 theme-icon-moon"></i>
                </button>

                <!-- Teacher Menu -->
                <div class="flex items-center gap-2 pl-2 border-l border-gray-200/50 dark:border-white/10">
                    <div class="hidden md:block text-right">
                        <div class="text-xs font-semibold text-gray-800 dark:text-gray-200"><?= $e($user['name'] ?? 'Teacher') ?></div>
                        <div class="text-[10px] text-indigo-500 dark:text-indigo-400 font-medium">Class Teacher</div>
                    </div>
                    <a href="/logout" class="p-2 text-rose-500 hover:bg-rose-500/10 rounded-xl transition-colors" title="Logout">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                    </a>
                </div>

            </div>
        </div>
    </header>

    <!-- Desktop Navigation Ribbon -->
    <nav class="hidden md:block bg-white/40 dark:bg-slate-900/40 backdrop-blur-md border-b border-gray-200/40 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-1 py-2">
            <a href="/dashboard" class="flex items-center gap-2 px-3.5 py-2 text-xs font-medium rounded-lg hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span>Dashboard</span>
            </a>
            <a href="/calls" class="flex items-center gap-2 px-3.5 py-2 text-xs font-medium rounded-lg hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="phone-call" class="w-4 h-4"></i>
                <span>Parent Call Journal</span>
            </a>
            <a href="/attendance" class="flex items-center gap-2 px-3.5 py-2 text-xs font-medium rounded-lg hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="calendar-check" class="w-4 h-4"></i>
                <span>Attendance Register</span>
            </a>
            <a href="/students" class="flex items-center gap-2 px-3.5 py-2 text-xs font-medium rounded-lg hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Students Roster</span>
            </a>
            <a href="/results" class="flex items-center gap-2 px-3.5 py-2 text-xs font-medium rounded-lg hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="award" class="w-4 h-4"></i>
                <span>Exams & Marks</span>
            </a>
            <a href="/classes" class="flex items-center gap-2 px-3.5 py-2 text-xs font-medium rounded-lg hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                <span>Classes & Subjects</span>
            </a>
        </div>
    </nav>

    <!-- Main Viewport Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <!-- Flash Messages -->
        <?php if ($flashSuccess): ?>
            <div class="mb-5 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-between backdrop-blur-md">
                <div class="flex items-center gap-3 text-sm">
                    <i data-lucide="check-circle-2" class="w-5 h-5 flex-shrink-0"></i>
                    <span><?= $e($flashSuccess) ?></span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:opacity-75">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="mb-5 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 flex items-center justify-between backdrop-blur-md">
                <div class="flex items-center gap-3 text-sm">
                    <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                    <span><?= $e($flashError) ?></span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:opacity-75">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <!-- Mobile-First Sticky Bottom Navigation Bar (High Usability) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 backdrop-blur-2xl bg-white/80 dark:bg-slate-900/85 border-t border-gray-200/60 dark:border-white/10 px-2 py-2">
        <div class="grid grid-cols-5 text-center">
            
            <a href="/dashboard" class="flex flex-col items-center justify-center py-1 text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1 font-medium">Home</span>
            </a>

            <a href="/calls" class="flex flex-col items-center justify-center py-1 text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="phone-call" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1 font-medium">Calls</span>
            </a>

            <a href="/attendance" class="flex flex-col items-center justify-center py-1 text-indigo-600 dark:text-indigo-400">
                <div class="w-10 h-10 -mt-5 rounded-full bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-600/40">
                    <i data-lucide="calendar-check" class="w-5 h-5"></i>
                </div>
                <span class="text-[10px] mt-0.5 font-bold">Roll Call</span>
            </a>

            <a href="/students" class="flex flex-col items-center justify-center py-1 text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1 font-medium">Students</span>
            </a>

            <a href="/results" class="flex flex-col items-center justify-center py-1 text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="award" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1 font-medium">Marks</span>
            </a>

        </div>
    </nav>

    <!-- App Scripts -->
    <script src="/assets/js/app.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
