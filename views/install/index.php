<div class="space-y-6">
    <!-- Server Requirements Checklist -->
    <div>
        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 flex items-center gap-2">
            <i data-lucide="check-square" class="w-4 h-4"></i>
            <span>System Requirements Check</span>
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
            <?php foreach ($requirements as $req => $met): ?>
                <div class="p-3 rounded-xl border flex items-center justify-between text-xs font-medium <?= $met ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 border-rose-500/30 text-rose-600 dark:text-rose-400' ?>">
                    <span><?= $e($req) ?></span>
                    <i data-lucide="<?= $met ? 'check' : 'x' ?>" class="w-4 h-4"></i>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!$canProceed): ?>
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 text-xs">
            <strong>Action Required:</strong> Your server environment does not meet the minimum requirements (PHP 8.2+ and PDO are mandatory). Please enable missing extensions before continuing.
        </div>
    <?php else: ?>

        <!-- Setup Form -->
        <form method="POST" action="/install/process" class="space-y-6" id="install-form">
            <?= $csrf() ?>

            <!-- Step 1: Database Selection -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">
                    Step 1: Database Engine
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all border-indigo-600 bg-indigo-50/20 dark:bg-indigo-950/20" id="card-sqlite">
                        <input type="radio" name="db_type" value="sqlite" checked class="sr-only" onchange="toggleDbFields('sqlite')">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                <i data-lucide="database" class="w-4 h-4 text-indigo-500"></i> SQLite 3
                            </span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 font-semibold">Recommended</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Zero-configuration, ultra-fast embedded database.</p>
                    </label>

                    <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all border-gray-200 dark:border-white/10" id="card-mysql">
                        <input type="radio" name="db_type" value="mysql" class="sr-only" onchange="toggleDbFields('mysql')">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                <i data-lucide="server" class="w-4 h-4 text-emerald-500"></i> MySQL / MariaDB
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Live hosting, cPanel or dedicated MySQL server.</p>
                    </label>
                </div>
            </div>

            <!-- MySQL Credentials Panel (Collapsible) -->
            <div id="mysql-credentials-panel" class="hidden space-y-4 p-4 rounded-xl border border-indigo-500/30 bg-indigo-50/10 dark:bg-indigo-950/20">
                <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400">MySQL Connection Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Host</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="server" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="mysql_host" value="127.0.0.1" placeholder="127.0.0.1">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Port</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="hash" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="mysql_port" value="3306" placeholder="3306">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Database Name</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="database" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="mysql_name" value="class_aid" placeholder="class_aid">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Username (non-root supported)</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="mysql_user" value="root" placeholder="root">
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="key" class="w-4 h-4"></i>
                            </div>
                            <input type="password" name="mysql_password" placeholder="Database password (leave blank if none)">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Master Super Administrator Account -->
            <div class="space-y-4 pt-2 border-t border-gray-200/50 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-gradient-to-tr from-rose-500 to-amber-500 text-white font-bold text-xs flex items-center justify-center shadow-md">
                        2
                    </span>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                        Master Super Administrator Setup
                    </h2>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    This account will hold master privileges to configure EasyPaisa gateways, manage teacher workspaces, review manual payment receipts, and dispatch broadcasts.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Super Admin Name *</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="user-check" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="admin_name" placeholder="e.g. Mirza Hasnain Baig" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">School / Organization Name</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="building" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="school_name" placeholder="e.g. Oakridge Academy & SaaS Hub">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Email Address *</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <input type="email" name="email" placeholder="admin@domain.com" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Master Password *</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input type="password" id="install-password" name="password" minlength="6" placeholder="Choose a secure password" required>
                            <button type="button" onclick="event.stopPropagation(); togglePasswordVisibility('install-password', this)" class="input-action-btn" title="Toggle password visibility">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">WhatsApp / EasyPaisa Mobile Number</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="phone" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="phone" placeholder="e.g. 0300-1234567">
                        </div>
                        <span class="text-[10px] text-gray-400 mt-1 block">Used as the default EasyPaisa receiving account for student subscriptions.</span>
                    </div>
                </div>
            </div>

            <!-- Step 3: Outgoing Email (SMTP) Setup (Optional) -->
            <div class="space-y-4 pt-2 border-t border-gray-200/50 dark:border-white/10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white font-bold text-xs flex items-center justify-center shadow-md">
                            3
                        </span>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                            SMTP Outgoing Mail Settings (Optional)
                        </h2>
                    </div>
                    <span class="text-[11px] text-gray-400">Can be configured later in Admin Portal</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">SMTP Host</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="cloud" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="mail_host" placeholder="smtp.gmail.com">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">SMTP Port</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="hash" class="w-4 h-4"></i>
                            </div>
                            <input type="number" name="mail_port" value="587" placeholder="587">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">SMTP Username</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="mail_username" placeholder="your_email@gmail.com">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">SMTP Password / App Password</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="key" class="w-4 h-4"></i>
                            </div>
                            <input type="password" id="install-mail-password" name="mail_password" placeholder="••••••••••••">
                            <button type="button" onclick="event.stopPropagation(); togglePasswordVisibility('install-mail-password', this)" class="input-action-btn" title="Toggle password visibility">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Demo Data Checkbox -->
            <div class="p-4 rounded-xl border border-indigo-500/20 bg-indigo-50/5 flex items-start gap-3">
                <input type="checkbox" name="seed_demo" value="1" id="seed_demo" checked class="mt-1 rounded text-indigo-600 focus:ring-indigo-500">
                <label for="seed_demo" class="cursor-pointer">
                    <span class="block text-xs font-bold text-gray-900 dark:text-white">Seed Starter Classrooms & Default EasyPaisa Gateway</span>
                    <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                        Creates your primary Home Class roster, subjects, and provisions the EasyPaisa & JazzCash QR gateways with your details.
                    </span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3.5 px-4 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-xl shadow-indigo-600/30 flex items-center justify-center gap-2 group transition-all">
                <span>Complete Setup & Launch Super Admin Portal</span>
                <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
            </button>
        </form>

        <script>
            function toggleDbFields(type) {
                const panel = document.getElementById('mysql-credentials-panel');
                const cardSqlite = document.getElementById('card-sqlite');
                const cardMysql = document.getElementById('card-mysql');

                if (type === 'mysql') {
                    panel.classList.remove('hidden');
                    cardMysql.classList.add('border-indigo-600', 'bg-indigo-50/20', 'dark:bg-indigo-950/20', 'border-2');
                    cardMysql.classList.remove('border-gray-200', 'dark:border-white/10', 'border');
                    cardSqlite.classList.remove('border-indigo-600', 'bg-indigo-50/20', 'dark:bg-indigo-950/20', 'border-2');
                    cardSqlite.classList.add('border-gray-200', 'dark:border-white/10', 'border');
                } else {
                    panel.classList.add('hidden');
                    cardSqlite.classList.add('border-indigo-600', 'bg-indigo-50/20', 'dark:bg-indigo-950/20', 'border-2');
                    cardSqlite.classList.remove('border-gray-200', 'dark:border-white/10', 'border');
                    cardMysql.classList.remove('border-indigo-600', 'bg-indigo-50/20', 'dark:bg-indigo-950/20', 'border-2');
                    cardMysql.classList.add('border-gray-200', 'dark:border-white/10', 'border');
                }
            }
        </script>
    <?php endif; ?>
</div>
