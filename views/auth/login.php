<form method="POST" action="/login" class="space-y-5">
    <?= $csrf() ?>

    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 mb-1.5">
            Teacher Email Address
        </label>
        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
            <div class="input-icon-box">
                <i data-lucide="mail" class="w-4 h-4"></i>
            </div>
            <input type="email"
                   name="email"
                   required
                   autocomplete="email"
                   placeholder="e.g. teacher@school.edu">
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                Password
            </label>
            <a href="/forgot-password" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">
                Forgot Password?
            </a>
        </div>
        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
            <div class="input-icon-box">
                <i data-lucide="lock" class="w-4 h-4"></i>
            </div>
            <input type="password"
                   id="login-password"
                   name="password"
                   required
                   autocomplete="current-password"
                   placeholder="••••••••">
            <button type="button"
                    onclick="event.stopPropagation(); togglePasswordVisibility('login-password', this)"
                    class="input-action-btn"
                    title="Toggle password visibility">
                <i data-lucide="eye" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <button type="submit"
            class="w-full py-3 px-4 rounded-xl font-bold text-sm text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/25 flex items-center justify-center gap-2 group transition-all">
        <span>Sign In to Class Aid</span>
        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
    </button>
</form>

<div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-800 text-center space-y-3">
    <div class="text-xs text-slate-600 dark:text-slate-300">
        New educator or school? <a href="/register" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Choose a Plan & Register</a>
    </div>

    <?php if (\App\Core\Config::get('APP_DEBUG', false)): ?>
        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300 space-y-1">
            <div class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-1">Developer Debug Accounts</div>
            <div><strong>Teacher:</strong> <span class="font-mono text-indigo-600 dark:text-indigo-400">teacher@school.edu</span></div>
            <div><strong>Super Admin:</strong> <span class="font-mono text-rose-600 dark:text-rose-400">hasbaig786@gmail.com</span></div>
        </div>
    <?php endif; ?>
</div>
