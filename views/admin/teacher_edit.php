<div class="max-w-4xl mx-auto space-y-6">

    <!-- Back header -->
    <div class="flex items-center gap-2">
        <a href="/admin/teachers" class="text-xs text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Teachers Directory</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Edit Form -->
        <div class="lg:col-span-2 glass-card p-6 sm:p-8">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800 mb-6">
                <div>
                    <h1 class="text-lg font-black text-slate-900 dark:text-white">
                        Edit Teacher: <?= $e($teacher['name']) ?>
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Account ID: #<?= $teacher['id'] ?> • Registered <?= date('M d, Y', strtotime($teacher['created_at'])) ?></p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase <?= $teacher['status'] === 'suspended' ? 'badge-rose' : 'badge-emerald' ?>">
                    <?= $e($teacher['status']) ?>
                </span>
            </div>

            <form method="POST" action="/admin/teachers/<?= $teacher['id'] ?>" class="space-y-4">
                <?= $csrf() ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Full Name
                        </label>
                        <input type="text" name="name" value="<?= $e($teacher['name']) ?>" required class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Email Address
                        </label>
                        <input type="email" name="email" value="<?= $e($teacher['email']) ?>" required class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            School / Institution Name
                        </label>
                        <input type="text" name="school_name" value="<?= $e($teacher['school_name'] ?? '') ?>" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Phone / WhatsApp
                        </label>
                        <input type="text" name="phone" value="<?= $e($teacher['phone'] ?? '') ?>" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Account Status
                        </label>
                        <select name="status" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                            <option value="active" <?= $teacher['status'] === 'active' ? 'selected' : '' ?>>Active (Can Sign In)</option>
                            <option value="suspended" <?= $teacher['status'] === 'suspended' ? 'selected' : '' ?>>Suspended (Sign In Blocked)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Reset Password (Leave blank to keep)
                        </label>
                        <input type="password" name="password" placeholder="••••••••" minlength="6" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                    </div>
                </div>

                <!-- Plan Override / Upgrade Section -->
                <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-1.5 flex items-center gap-1.5">
                        <i data-lucide="crown" class="w-4 h-4"></i>
                        <span>Change / Upgrade Subscription Plan</span>
                    </label>
                    <select name="change_plan" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                        <option value="">-- Keep Current Plan (No Change) --</option>
                        <?php foreach ($plans as $pCode => $p): ?>
                            <option value="<?= $pCode ?>">
                                <?= $e($p['name']) ?> ($<?= number_format($p['price'], 2) ?> • <?= $e($p['cycle']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Save Teacher Changes</span>
                    </button>
                </div>

            </form>
        </div>

        <!-- Right Column: Current Plan & History -->
        <div class="space-y-6">
            <!-- Current Subscription Box -->
            <div class="glass-card p-6">
                <h3 class="font-bold text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-2">
                    <i data-lucide="credit-card" class="w-4 h-4 text-indigo-500"></i>
                    <span>Active Subscription</span>
                </h3>

                <?php if ($currentSub): ?>
                    <?php 
                        $pInfo = $plans[$currentSub['plan_code']] ?? null;
                    ?>
                    <div class="space-y-3">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                            <div class="font-bold text-sm text-slate-900 dark:text-white"><?= $e($currentSub['plan_name']) ?></div>
                            <div class="text-xs font-mono text-indigo-600 dark:text-indigo-400 font-bold mt-0.5">
                                $<?= number_format((float) $currentSub['price'], 2) ?> • <?= $e($currentSub['billing_cycle']) ?>
                            </div>
                        </div>

                        <div class="text-xs space-y-1.5 text-slate-500 dark:text-slate-400">
                            <div class="flex justify-between">
                                <span>Status:</span>
                                <span class="font-bold uppercase text-slate-900 dark:text-white"><?= $e($currentSub['status']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Started:</span>
                                <span class="text-slate-900 dark:text-white"><?= date('M d, Y', strtotime($currentSub['starts_at'])) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Expires:</span>
                                <span class="text-slate-900 dark:text-white font-mono"><?= !empty($currentSub['expires_at']) ? date('M d, Y', strtotime($currentSub['expires_at'])) : 'Lifetime (No Expiry)' ?></span>
                            </div>
                        </div>

                        <?php if (!$currentSub['is_lifetime']): ?>
                            <form method="POST" action="/admin/subscriptions/<?= $currentSub['id'] ?>/extend" class="pt-2">
                                <?= $csrf() ?>
                                <input type="hidden" name="days" value="30">
                                <button type="submit" class="w-full py-2 text-xs font-bold rounded-lg bg-indigo-500/15 hover:bg-indigo-500/25 text-indigo-600 dark:text-indigo-300 border border-indigo-500/30 transition-colors cursor-pointer">
                                    +30 Days Extension
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">No active subscription found.</p>
                <?php endif; ?>
            </div>

            <!-- Subscription Invoices History -->
            <div class="glass-card p-6">
                <h3 class="font-bold text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
                    Billing Audit History
                </h3>
                <div class="space-y-2 text-xs">
                    <?php if (empty($subHistory)): ?>
                        <p class="text-slate-400 text-[11px] italic">No prior invoices.</p>
                    <?php else: ?>
                        <?php foreach ($subHistory as $hist): ?>
                            <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-[11px]">
                                <div>
                                    <div class="font-semibold text-slate-900 dark:text-white"><?= $e($hist['plan_code']) ?></div>
                                    <div class="text-slate-400 text-[10px]"><?= date('Y-m-d', strtotime($hist['created_at'])) ?></div>
                                </div>
                                <div class="text-right font-mono">
                                    <div class="text-indigo-600 dark:text-indigo-400 font-bold">$<?= number_format((float) $hist['price'], 2) ?></div>
                                    <div class="text-[9px] uppercase text-slate-400"><?= $e($hist['status']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>
