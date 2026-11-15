<?php
    $isDebug = (bool) \App\Core\Config::get('APP_DEBUG', false);
?>
<div class="text-center space-y-5 max-w-md mx-auto p-4 sm:p-6">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 mb-1">
        <i data-lucide="compass" class="w-8 h-8"></i>
    </div>

    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">404 - Page Not Found</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
            The page or resource you are looking for does not exist, has been removed, or is temporarily unavailable.
        </p>
    </div>

    <?php if ($isDebug): ?>
        <!-- Debug Mode Diagnostics (Visible ONLY when APP_DEBUG=true) -->
        <div class="p-3.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-left text-xs text-slate-600 dark:text-slate-300 space-y-1 font-mono">
            <div class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Developer Diagnostic Mode</div>
            <div><strong>Method:</strong> <?= htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'GET', ENT_QUOTES, 'UTF-8') ?></div>
            <div><strong>Requested Path:</strong> <?= htmlspecialchars($path ?? ($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?></div>
            <div><strong>Timestamp:</strong> <?= date('Y-m-d H:i:s') ?></div>
        </div>
    <?php endif; ?>

    <div class="pt-2 flex items-center justify-center gap-3">
        <a href="javascript:history.back()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Previous Page</span>
        </a>
        <a href="/dashboard" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/25 transition-all">
            <i data-lucide="home" class="w-4 h-4"></i>
            <span>Return to Dashboard</span>
        </a>
    </div>
</div>
