<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title ?? 'Super Admin') ?> - Class Aid SaaS</title>
    <!-- Prevent flash of wrong theme -->
    <script>
        (function() {
            const saved = localStorage.getItem('class_aid_theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
        window.toggleTheme = function(e) {
            if (e) {
                if (typeof e.preventDefault === 'function') e.preventDefault();
                if (typeof e.stopPropagation === 'function') e.stopPropagation();
                if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
            }
            const now = Date.now();
            if (window._lastThemeToggle && (now - window._lastThemeToggle) < 250) {
                return document.documentElement.classList.contains('dark');
            }
            window._lastThemeToggle = now;

            const isDark = document.documentElement.classList.toggle('dark');
            try {
                localStorage.setItem('class_aid_theme', isDark ? 'dark' : 'light');
            } catch (err) {}
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
            return isDark;
        };
    </script>
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
    <link rel="stylesheet" href="/assets/css/glassmorphism.css?v=1.1.0">
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-indigo-500 selection:text-white text-slate-900 dark:text-slate-100 transition-colors">

    <!-- Top Page Loading Progress Bar -->
    <div id="page-loader-bar"></div>

    <!-- Super Admin Header (Liquid Glass) -->
    <header class="sticky top-0 z-40 w-full liquid-header shadow-sm transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            
            <!-- Brand & Admin Badge -->
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="/admin" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-rose-600 flex items-center justify-center text-white shadow-md shadow-rose-600/20 group-hover:scale-105 transition-transform flex-shrink-0">
                        <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-base tracking-tight text-slate-900 dark:text-white">
                                Class Aid
                            </span>
                            <span class="text-xs font-bold uppercase px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-600 dark:text-rose-300 border border-rose-500/30">
                                Super Admin
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 hidden sm:block font-medium">
                            Multi-Tenant SaaS Management
                        </div>
                    </div>
                </a>
            </div>

            <!-- Right Controls: View as Teacher / Theme / Profile -->
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="/dashboard" class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/30 text-indigo-600 dark:text-indigo-300 flex items-center gap-1.5 transition-all" title="View Teacher Portal">
                    <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Teacher View</span>
                </a>

                <!-- Theme Toggle Button -->
                <button type="button" onclick="window.toggleTheme(event)" class="theme-toggle p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer" title="Toggle Theme">
                    <i data-lucide="sun" class="w-5 h-5 theme-icon-sun"></i>
                    <i data-lucide="moon" class="w-5 h-5 theme-icon-moon"></i>
                </button>

                <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-800">
                    <a href="/profile" class="flex items-center gap-2 group p-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Manage Administrator Profile">
                        <?php if (!empty($user['avatar']) && file_exists(dirname(__DIR__, 2) . '/public' . $user['avatar'])): ?>
                            <img src="<?= $e($user['avatar']) ?>" alt="Avatar" class="w-8 h-8 rounded-xl object-cover ring-2 ring-rose-500/30">
                        <?php else: ?>
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-rose-600 to-amber-500 text-white font-bold text-xs flex items-center justify-center shadow-md shadow-rose-500/20 group-hover:scale-105 transition-transform flex-shrink-0">
                                <?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="hidden lg:block text-left">
                            <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors"><?= $e($user['name'] ?? 'Super Admin') ?></div>
                            <div class="text-xs text-rose-600 dark:text-rose-400 font-mono">Master Administrator</div>
                        </div>
                    </a>
                    <a href="/logout" class="p-2 text-rose-500 hover:bg-rose-500/10 rounded-xl transition-colors" title="Logout">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Horizontal Scrollable Admin Navigation Ribbon (Liquid Glass) -->
        <nav class="border-t border-slate-200/80 dark:border-white/10 bg-slate-50/70 dark:bg-slate-950/70 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto flex items-center gap-1 overflow-x-auto no-scrollbar py-1.5">
                <a href="/admin" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                    <i data-lucide="gauge" class="w-4 h-4 text-indigo-500 dark:text-indigo-400"></i>
                    <span>Overview</span>
                </a>
                <a href="/admin/teachers" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                    <i data-lucide="users" class="w-4 h-4 text-emerald-500 dark:text-emerald-400"></i>
                    <span>Teachers</span>
                </a>
                <a href="/admin/subscriptions" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                    <i data-lucide="credit-card" class="w-4 h-4 text-amber-500 dark:text-amber-400"></i>
                    <span>Subscriptions</span>
                </a>
                <a href="/admin/plans" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                    <i data-lucide="package" class="w-4 h-4 text-violet-500 dark:text-violet-400"></i>
                    <span>SaaS Plans</span>
                </a>
                <a href="/admin/invoices" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                    <i data-lucide="file-text" class="w-4 h-4 text-rose-500 dark:text-rose-400"></i>
                    <span>Invoices</span>
                </a>
                <a href="/admin/payment-methods" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                    <i data-lucide="qr-code" class="w-4 h-4 text-teal-500 dark:text-teal-400"></i>
                    <span>Gateways & QR</span>
                </a>
                <a href="/admin/settings/mail" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                    <i data-lucide="mail" class="w-4 h-4 text-sky-500 dark:text-sky-400"></i>
                    <span>SMTP</span>
                </a>
                <a href="/admin/announcements" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                    <i data-lucide="megaphone" class="w-4 h-4 text-purple-500 dark:text-purple-400"></i>
                    <span>Broadcasts</span>
                </a>
                <a href="/admin/reminders" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                    <i data-lucide="calendar-clock" class="w-4 h-4 text-cyan-500 dark:text-cyan-400"></i>
                    <span>Reminders</span>
                </a>
                <a href="/admin/updates" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                    <i data-lucide="refresh-cw" class="w-4 h-4 text-emerald-500 dark:text-emerald-400"></i>
                    <span>Updates</span>
                </a>
            </div>
        </nav>
    </header>

    <!-- Main Viewport Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <!-- Flash Messages -->
        <?php if ($flashSuccess): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-950/50 border border-emerald-800/80 text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5 text-sm font-medium">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-400 flex-shrink-0"></i>
                    <span><?= $e($flashSuccess) ?></span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:opacity-75 p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-rose-950/50 border border-rose-800/80 text-rose-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5 text-sm font-medium">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-400 flex-shrink-0"></i>
                    <span><?= $e($flashError) ?></span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:opacity-75 p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <!-- Admin Mobile Bottom Bar -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-slate-900 border-t border-slate-800 px-2 py-1.5 shadow-lg">
        <div class="grid grid-cols-5 text-center text-xs">
            <a href="/admin" class="flex flex-col items-center py-1 text-slate-400 hover:text-white">
                <i data-lucide="gauge" class="w-5 h-5"></i>
                <span class="text-xs mt-1 font-bold">Admin</span>
            </a>
            <a href="/admin/teachers" class="flex flex-col items-center py-1 text-slate-400 hover:text-white">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="text-xs mt-1 font-bold">Teachers</span>
            </a>
            <a href="/admin/subscriptions" class="flex flex-col items-center py-1 text-slate-400 hover:text-white">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
                <span class="text-xs mt-1 font-bold">Plans</span>
            </a>
            <a href="/admin/announcements" class="flex flex-col items-center py-1 text-slate-400 hover:text-white">
                <i data-lucide="megaphone" class="w-5 h-5"></i>
                <span class="text-xs mt-1 font-bold">Alerts</span>
            </a>
            <a href="/dashboard" class="flex flex-col items-center py-1 text-indigo-400">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="text-xs mt-1 font-bold">App</span>
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
