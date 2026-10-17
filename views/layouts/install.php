<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title ?? 'Class Aid Setup') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/assets/css/glassmorphism.css">
</head>
<body class="min-h-screen flex items-center justify-center p-4 py-10">
    <div class="w-full max-w-2xl">
        <!-- Logo & Title -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white shadow-xl shadow-indigo-500/30 mb-3">
                <i data-lucide="sparkles" class="w-7 h-7"></i>
            </div>
            <h1 class="text-2xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-violet-500 dark:from-indigo-400 dark:to-violet-400">
                Class Aid Setup Wizard
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Configure your environment, database, and primary teacher profile</p>
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
            <button type="button" class="theme-toggle text-xs text-gray-500 dark:text-gray-400 hover:text-indigo-500 flex items-center gap-1 mx-auto">
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
