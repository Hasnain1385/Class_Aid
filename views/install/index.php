<div class="space-y-6">
    <!-- Server Requirements Checklist -->
    <div>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 flex items-center gap-2">
            <i data-lucide="check-square" class="w-4 h-4"></i>
            <span>System Requirements</span>
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
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                    Select Database Engine
                </label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all border-indigo-600 bg-indigo-50/20 dark:bg-indigo-950/20" id="card-sqlite">
                        <input type="radio" name="db_type" value="sqlite" checked class="sr-only" onchange="toggleDbFields('sqlite')">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                <i data-lucide="database" class="w-4 h-4 text-indigo-500"></i> SQLite 3
                            </span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 font-semibold">Recommended</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Zero-config, fast, saved locally to file.</p>
                    </label>

                    <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all border-gray-200 dark:border-white/10" id="card-mysql">
                        <input type="radio" name="db_type" value="mysql" class="sr-only" onchange="toggleDbFields('mysql')">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                <i data-lucide="server" class="w-4 h-4 text-emerald-500"></i> MySQL / MariaDB
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Multi-user or cPanel/live hosting deployment.</p>
                    </label>
                </div>
            </div>

            <!-- MySQL Credentials Panel (Collapsible) -->
            <div id="mysql-credentials-panel" class="hidden space-y-4 p-4 rounded-xl border border-indigo-500/30 bg-indigo-50/10 dark:bg-indigo-950/20">
                <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400">MySQL Connection Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Host</label>
                        <input type="text" name="mysql_host" value="127.0.0.1" class="w-full px-3 py-2 text-xs rounded-lg glass-input">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Port</label>
                        <input type="text" name="mysql_port" value="3306" class="w-full px-3 py-2 text-xs rounded-lg glass-input">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Database Name</label>
                        <input type="text" name="mysql_name" value="class_aid" class="w-full px-3 py-2 text-xs rounded-lg glass-input">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Username (non-root supported)</label>
                        <input type="text" name="mysql_user" value="root" class="w-full px-3 py-2 text-xs rounded-lg glass-input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <input type="password" name="mysql_password" placeholder="Database password (leave blank if none)" class="w-full px-3 py-2 text-xs rounded-lg glass-input">
                    </div>
                </div>
            </div>

            <!-- Step 2: Teacher & School Profile -->
            <div class="space-y-4 pt-2 border-t border-gray-200/50 dark:border-white/10">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                    <span>Teacher Admin Account</span>
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Your Full Name *</label>
                        <input type="text" name="teacher_name" value="Mr. Nain (Class Teacher)" required class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">School / Institution Name</label>
                        <input type="text" name="school_name" value="Springdale Public Academy" class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address *</label>
                        <input type="email" name="email" value="teacher@school.edu" required class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Password *</label>
                        <input type="password" name="password" value="teacher123" required class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                    </div>
                </div>
            </div>

            <!-- Demo Data Checkbox -->
            <div class="p-4 rounded-xl border border-indigo-500/20 bg-indigo-500/5 flex items-start gap-3">
                <input type="checkbox" name="seed_demo" value="1" id="seed_demo" checked class="mt-1 rounded text-indigo-600 focus:ring-indigo-500">
                <label for="seed_demo" class="cursor-pointer">
                    <span class="block text-xs font-bold text-gray-900 dark:text-white">Seed Initial Classes & Realistic Demo Dataset</span>
                    <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                        Creates your primary Home Class (Grade 10-A with ~30 students), 4 other subject classes (Grade 9A, 9B, 11A, 12A), sample attendance records, parent call records, and exams.
                    </span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3.5 px-4 rounded-xl font-semibold text-sm text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-xl shadow-indigo-600/30 flex items-center justify-center gap-2 group transition-all">
                <span>Complete Installation & Launch</span>
                <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
            </button>
        </form>

        <script>
            function toggleDbFields(type) {
                const mysqlPanel = document.getElementById('mysql-credentials-panel');
                const cardSqlite = document.getElementById('card-sqlite');
                const cardMysql = document.getElementById('card-mysql');

                if (type === 'mysql') {
                    mysqlPanel.classList.remove('hidden');
                    cardMysql.classList.add('border-indigo-600', 'bg-indigo-50/20', 'dark:bg-indigo-950/20', 'border-2');
                    cardSqlite.classList.remove('border-indigo-600', 'bg-indigo-50/20', 'dark:bg-indigo-950/20', 'border-2');
                    cardSqlite.classList.add('border-gray-200', 'dark:border-white/10');
                } else {
                    mysqlPanel.classList.add('hidden');
                    cardSqlite.classList.add('border-indigo-600', 'bg-indigo-50/20', 'dark:bg-indigo-950/20', 'border-2');
                    cardMysql.classList.remove('border-indigo-600', 'bg-indigo-50/20', 'dark:bg-indigo-950/20', 'border-2');
                    cardMysql.classList.add('border-gray-200', 'dark:border-white/10');
                }
            }
        </script>
    <?php endif; ?>
</div>
