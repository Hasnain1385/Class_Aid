<form method="POST" action="/forgot-password" class="space-y-5">
    <?= $csrf() ?>

    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 mb-1.5">
            Registered Email Address
        </label>
        <div class="glass-input-group">
            <div class="input-addon-box">
                <i data-lucide="mail" class="w-4 h-4"></i>
            </div>
            <input type="email" 
                   name="email" 
                   required 
                   autofocus
                   autocomplete="email"
                   placeholder="e.g. teacher@school.edu">
        </div>
    </div>

    <button type="submit" 
            class="w-full py-3 px-4 rounded-xl font-bold text-sm text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/25 flex items-center justify-center gap-2 group transition-all cursor-pointer">
        <span>Send Password Reset Link</span>
        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
    </button>
</form>

<div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-800 text-center">
    <a href="/login" class="text-xs text-indigo-600 dark:text-indigo-400 font-bold hover:underline inline-flex items-center gap-1.5">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        <span>Return to Sign In</span>
    </a>
</div>
