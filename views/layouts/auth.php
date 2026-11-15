<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title ?? 'Sign In') ?> - Class Aid</title>
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
    <link rel="stylesheet" href="/assets/css/glassmorphism.css?v=1.1.0">
</head>
<body class="min-h-screen flex items-center justify-center p-3 sm:p-6 py-10 text-slate-900 dark:text-slate-100 transition-colors">
    <!-- Top Page Loading Progress Bar -->
    <div id="page-loader-bar"></div>
    <div class="w-full <?= $e($maxWidth ?? 'max-w-md') ?> mx-auto transition-all">
        <!-- Brand Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/25 mb-3">
                <i data-lucide="graduation-cap" class="w-7 h-7"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                Class Aid
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium"><?= $e($subtitle ?? 'Multi-Class Teacher Digital Assistant') ?></p>
        </div>

        <!-- Flash Messages -->
        <?php if ($flashError): ?>
            <div class="mb-4 p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/80 text-rose-900 dark:text-rose-100 text-xs flex items-center gap-2 shadow-sm">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 dark:text-rose-400 flex-shrink-0"></i>
                <span class="font-medium"><?= $e($flashError) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($flashSuccess): ?>
            <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80 text-emerald-900 dark:text-emerald-100 text-xs flex items-center gap-2 shadow-sm">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0"></i>
                <span class="font-medium"><?= $e($flashSuccess) ?></span>
            </div>
        <?php endif; ?>

        <div class="glass-card p-6 sm:p-8">
            <?= $content ?>
        </div>
        
        <div class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400 flex items-center justify-center gap-4">
            <button type="button" onclick="window.toggleTheme(event)" class="theme-toggle hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-1 font-medium cursor-pointer">
                <i data-lucide="sun" class="w-3.5 h-3.5 theme-icon-sun"></i>
                <i data-lucide="moon" class="w-3.5 h-3.5 theme-icon-moon"></i>
                <span>Toggle Theme</span>
            </button>
            <span>•</span>
            <span class="font-medium">Educational Record Suite</span>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
