<div class="space-y-6 max-w-4xl">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="/admin" class="text-xs text-slate-400 hover:text-indigo-500 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Command Center</span>
                </a>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                SMTP & Email Dispatch Settings
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Configure SMTP email credentials for dispatching invoices, payment receipts, and announcements to your registered clients.
            </p>
        </div>
    </div>

    <!-- SMTP Configuration Card -->
    <div class="glass-card p-6 sm:p-8 space-y-6">
        <form method="POST" action="/admin/settings/mail" class="space-y-6">
            <?= $csrf() ?>

            <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i data-lucide="mail-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Outgoing Mail Server (SMTP)</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Used for client billing receipts, subscription invoices, and welcome emails.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">SMTP Host Server *</label>
                    <input type="text" name="mail_host" value="<?= $e($mailSettings['MAIL_HOST'] ?? '') ?>" placeholder="e.g. smtp.gmail.com or mail.yourdomain.com" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">SMTP Port *</label>
                    <input type="number" name="mail_port" value="<?= $e($mailSettings['MAIL_PORT'] ?? 587) ?>" placeholder="587 or 465" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input font-mono tabular-nums">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">SMTP Username / Email *</label>
                    <input type="text" name="mail_username" value="<?= $e($mailSettings['MAIL_USERNAME'] ?? '') ?>" placeholder="you@domain.com" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">SMTP Password / App Password *</label>
                    <input type="password" name="mail_password" value="<?= $e($mailSettings['MAIL_PASSWORD'] ?? '') ?>" placeholder="••••••••••••" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Encryption Protocol</label>
                    <select name="mail_encryption" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                        <option value="tls" <?= ($mailSettings['MAIL_ENCRYPTION'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Recommended - Port 587)</option>
                        <option value="ssl" <?= ($mailSettings['MAIL_ENCRYPTION'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                        <option value="none" <?= ($mailSettings['MAIL_ENCRYPTION'] ?? '') === 'none' ? 'selected' : '' ?>>None (Port 25)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Sender Email Address *</label>
                    <input type="email" name="mail_from_address" value="<?= $e($mailSettings['MAIL_FROM_ADDRESS'] ?? 'billing@classaid.com') ?>" placeholder="billing@classaid.com" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input font-mono">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Sender Display Name *</label>
                    <input type="text" name="mail_from_name" value="<?= $e($mailSettings['MAIL_FROM_NAME'] ?? 'Class Aid Suite') ?>" placeholder="Class Aid Suite" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="text-xs text-slate-500 dark:text-slate-400">Settings are securely stored directly in your environment configuration (`.env`).</span>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save SMTP Settings</span>
                </button>
            </div>
        </form>

        <!-- Test Email Dispatch Trigger -->
        <div class="pt-6 border-t border-slate-100 dark:border-slate-800">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Test Live SMTP Dispatch</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Send an instant test diagnostic message to verify socket connectivity, TLS handshake, and authentication.</p>
            
            <form method="POST" action="/admin/settings/mail/test" class="flex flex-col sm:flex-row items-center gap-3">
                <?= $csrf() ?>
                <input type="email" name="test_email" required value="<?= $e($user['email'] ?? 'hasbaig786@gmail.com') ?>" placeholder="recipient@example.com" class="flex-1 w-full px-3.5 py-2 text-xs rounded-xl glass-input font-mono">
                <button type="submit" class="w-full sm:w-auto px-5 py-2 text-xs font-bold rounded-xl badge-emerald hover:opacity-90 transition-opacity flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Dispatch Test Email</span>
                </button>
            </form>
        </div>

    </div>

</div>
