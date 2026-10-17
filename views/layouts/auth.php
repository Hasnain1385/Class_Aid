<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title ?? 'Sign In') ?> - Class Aid</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/assets/css/glassmorphism.css">
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white shadow-xl shadow-indigo-500/30 mb-3">
                <i data-lucide="graduation-cap" class="w-7 h-7"></i>
            </div>
            <h1 class="text-2xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-violet-500 dark:from-indigo-400 dark:to-violet-400">
                Class Aid
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Multi-Class Teacher Digital Assistant</p>
        </div>

        <!-- Flash Messages -->
        <?php if ($flashError): ?>
            <div class="mb-4 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 text-xs flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                <span><?= $e($flashError) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($flashSuccess): ?>
            <div class="mb-4 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-xs flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 flex-shrink-0"></i>
                <span><?= $e($flashSuccess) ?></span>
            </div>
        <?php endif; ?>

        <div class="glass-card p-6 sm:p-8">
            <?= $content ?>
        </div>
        
        <div class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400 flex items-center justify-center gap-4">
            <button type="button" class="theme-toggle hover:text-indigo-500 transition-colors flex items-center gap-1">
                <i data-lucide="sun" class="w-3.5 h-3.5 theme-icon-sun"></i>
                <i data-lucide="moon" class="w-3.5 h-3.5 theme-icon-moon"></i>
                <span>Theme</span>
            </button>
            <span>•</span>
            <span>Educational Record Suite</span>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
