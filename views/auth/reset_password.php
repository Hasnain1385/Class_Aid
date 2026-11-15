<div class="glass-card p-6 sm:p-8 space-y-6">
    <div class="text-center space-y-1">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 mb-2">
            <i data-lucide="key-round" class="w-6 h-6"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Create New Password</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400">
            Enter a secure password for <strong class="text-indigo-600 dark:text-indigo-400"><?= $e($user['email']) ?></strong>.
        </p>
    </div>

    <form method="POST" action="/reset-password" class="space-y-4">
        <?= $csrf() ?>
        <input type="hidden" name="token" value="<?= $e($token) ?>">

        <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                New Password (Min 6 Characters) <span class="text-rose-500">*</span>
            </label>
            <div class="glass-input-group">
                <div class="input-addon-box">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <input type="password" id="password" name="password" minlength="6" required autofocus placeholder="••••••••">
            </div>
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                Confirm New Password <span class="text-rose-500">*</span>
            </label>
            <div class="glass-input-group">
                <div class="input-addon-box">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <input type="password" id="password_confirmation" name="password_confirmation" minlength="6" required placeholder="••••••••">
            </div>
        </div>

        <button type="submit" class="w-full py-3 px-4 rounded-xl font-bold text-xs sm:text-sm text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span>Set New Password & Sign In</span>
        </button>
    </form>

    <div class="text-center pt-2">
        <a href="/login" class="text-xs text-gray-500 hover:text-indigo-400 transition-colors">
            &larr; Return to Sign In
        </a>
    </div>
</div>
