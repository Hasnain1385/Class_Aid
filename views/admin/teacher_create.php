<div class="max-w-2xl mx-auto space-y-6">

    <!-- Back header -->
    <div class="flex items-center gap-2">
        <a href="/admin/teachers" class="text-xs text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Teachers Directory</span>
        </a>
    </div>

    <div class="glass-card p-6 sm:p-8">
        <h1 class="text-xl font-black text-slate-900 dark:text-white mb-1">
            Provision New Teacher Workspace
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">
            Instantly set up a teacher account, assign a subscription plan, and grant classroom access.
        </p>

        <form method="POST" action="/admin/teachers" class="space-y-4">
            <?= $csrf() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Full Name *
                    </label>
                    <input type="text" name="name" required placeholder="e.g. Dr. Robert Chen" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Email Address *
                    </label>
                    <input type="email" name="email" required placeholder="teacher@school.edu" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        School / Institution Name
                    </label>
                    <input type="text" name="school_name" placeholder="e.g. St. Jude High School" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Phone / WhatsApp
                    </label>
                    <input type="text" name="phone" placeholder="+1 555-0199" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Temporary Password *
                </label>
                <input type="password" name="password" required placeholder="••••••••" minlength="6" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
            </div>

            <!-- Initial Subscription Plan Assignment -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Assign Initial Subscription Plan *
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <?php foreach ($plans as $pCode => $p): ?>
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-indigo-500/40 cursor-pointer transition-colors">
                            <input type="radio" name="plan_code" value="<?= $pCode ?>" <?= $pCode === 'annual' ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
                            <div class="flex-1">
                                <div class="font-bold text-xs text-slate-900 dark:text-white"><?= $e($p['name']) ?></div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">$<?= number_format($p['price'], 2) ?> • <?= $e($p['cycle']) ?></div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-800">
                <a href="/admin/teachers" class="px-4 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Create & Activate Teacher</span>
                </button>
            </div>

        </form>
    </div>

</div>
