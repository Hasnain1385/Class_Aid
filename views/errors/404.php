<div class="text-center space-y-4">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-500/10 text-indigo-500 mb-2">
        <i data-lucide="compass" class="w-8 h-8"></i>
    </div>
    <h2 class="text-2xl font-black text-gray-900 dark:text-white">404 - Page Not Found</h2>
    <p class="text-xs text-gray-500 dark:text-gray-400">
        The requested URL <code class="font-mono bg-gray-100 dark:bg-white/10 px-1.5 py-0.5 rounded"><?= $e($path ?? '') ?></code> was not found on this server.
    </p>
    <div class="pt-4">
        <a href="/dashboard" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md">
            <i data-lucide="home" class="w-4 h-4"></i>
            <span>Return to Dashboard</span>
        </a>
    </div>
</div>
