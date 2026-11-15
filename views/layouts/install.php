<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title ?? 'Class Aid Setup') ?></title>
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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/assets/css/glassmorphism.css">
</head>
<body class="min-h-screen flex items-center justify-center p-4 py-10 text-slate-900 dark:text-slate-100 transition-colors">
    <!-- Top Page Loading Progress Bar -->
    <div id="page-loader-bar"></div>
    <div class="w-full max-w-2xl">
        <!-- Logo & Title -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white shadow-xl shadow-indigo-500/30 mb-3">
                <i data-lucide="sparkles" class="w-7 h-7"></i>
            </div>
            <h1 class="text-2xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-violet-500 dark:from-indigo-400 dark:to-violet-400">
                Class Aid Setup Wizard
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Configure your environment, database, and primary teacher profile</p>
        </div>

        <?php if ($flashError): ?>
            <div class="mb-4 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 text-xs flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                <span><?= $e($flashError) ?></span>
            </div>
        <?php endif; ?>

        <div class="glass-card p-6 sm:p-8">
            <?= $content ?>
        </div>

        <div class="mt-4 text-center">
            <button type="button" onclick="window.toggleTheme(event)" class="theme-toggle text-xs text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1 mx-auto cursor-pointer">
                <i data-lucide="sun" class="w-3.5 h-3.5 theme-icon-sun"></i>
                <i data-lucide="moon" class="w-3.5 h-3.5 theme-icon-moon"></i>
                <span>Switch Theme</span>
            </button>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
