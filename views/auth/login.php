<form method="POST" action="/login" class="space-y-5">
    <?= $csrf() ?>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
            Teacher Email Address
        </label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i data-lucide="mail" class="w-4 h-4"></i>
            </div>
            <input type="email" 
                   name="email" 
                   required 
                   autocomplete="email"
                   class="w-full pl-9 pr-4 py-2.5 text-xs sm:text-sm rounded-xl glass-input placeholder-gray-400"
                   placeholder="e.g. teacher@school.edu">
        </div>
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
            Password
        </label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i data-lucide="lock" class="w-4 h-4"></i>
            </div>
            <input type="password" 
                   name="password" 
                   required 
                   autocomplete="current-password"
                   class="w-full pl-9 pr-4 py-2.5 text-xs sm:text-sm rounded-xl glass-input placeholder-gray-400"
                   placeholder="••••••••">
        </div>
    </div>

    <button type="submit" 
            class="w-full py-3 px-4 rounded-xl font-semibold text-sm text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 group transition-all">
        <span>Sign In to Class Aid</span>
        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
    </button>
</form>

<div class="mt-6 pt-4 border-t border-gray-200/40 dark:border-white/10 text-center">
    <div class="text-[11px] text-gray-500 dark:text-gray-400">
        Demo Login: <span class="font-mono text-indigo-500">teacher@school.edu</span> / <span class="font-mono text-indigo-500">teacher123</span>
    </div>
</div>
