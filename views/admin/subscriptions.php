<div class="space-y-6">

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
                Subscription Tracking & Audit
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Track revenue, plan cycles, and manage renewals across all educational institutions.
            </p>
        </div>
    </div>

    <!-- Pending Manual Verifications Section -->
    <?php if (!empty($pendingVerifications)): ?>
        <div class="glass-card p-6 rounded-2xl border border-amber-500/40 bg-amber-500/10 dark:bg-amber-500/5 space-y-4 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-amber-500/20">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center font-bold">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Pending Payment Verifications (<?= count($pendingVerifications) ?>)
                        </h2>
                        <p class="text-xs text-amber-800 dark:text-amber-300">Teachers who submitted EasyPaisa, JazzCash, or Bank Transfer TRX for verification.</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase badge-amber animate-pulse">
                    Action Required
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-slate-500 dark:text-slate-400 uppercase text-xs font-semibold border-b border-amber-500/20 tracking-wider">
                        <tr>
                            <th class="py-2.5 px-3">Teacher / Client</th>
                            <th class="py-2.5 px-3">Selected Plan</th>
                            <th class="py-2.5 px-3 font-mono">Amount Due</th>
                            <th class="py-2.5 px-3">Payment Method</th>
                            <th class="py-2.5 px-3 font-mono">TRX ID / Reference</th>
                            <th class="py-2.5 px-3">Proof Receipt</th>
                            <th class="py-2.5 px-3 text-right">Verification Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-500/15">
                        <?php foreach ($pendingVerifications as $pv): ?>
                            <tr>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 dark:text-white"><?= $e($pv['teacher_name']) ?></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400"><?= $e($pv['teacher_email']) ?> &bull; <?= $e($pv['school_name'] ?? 'Classroom') ?></div>
                                </td>
                                <td class="py-3 px-3">
                                    <?php 
                                        $targetCode = !empty($pv['pending_plan']) ? $pv['pending_plan'] : $pv['plan_code'];
                                        $tPlan = $plans[$targetCode] ?? null;
                                        $tName = $tPlan['name'] ?? $pv['plan_name'];
                                        $tCycle = $tPlan['cycle'] ?? $pv['billing_cycle'];
                                        $tPrice = !empty($pv['pending_price']) ? $pv['pending_price'] : ($tPlan['price'] ?? $pv['price']);
                                    ?>
                                    <span class="font-semibold text-slate-900 dark:text-white"><?= $e($tName) ?></span>
                                    <span class="text-xs text-slate-400 uppercase font-mono block"><?= $e($tCycle) ?></span>
                                    <?php if ($pv['status'] === 'trial'): ?>
                                        <span class="inline-block mt-0.5 text-xs font-bold px-2 py-0.5 rounded badge-amber">
                                            Active in Trial Mode
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 font-mono font-bold text-indigo-600 dark:text-indigo-400 text-sm tabular-nums">
                                    $<?= number_format((float) $tPrice, 2) ?>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        <?= $e($pv['payment_method']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400 tabular-nums">
                                    <?= $e($pv['payment_reference'] ?? 'N/A') ?>
                                </td>
                                <td class="py-3 px-3">
                                    <?php if (!empty($pv['payment_proof']) && file_exists(dirname(__DIR__, 2) . '/public' . $pv['payment_proof'])): ?>
                                        <a href="<?= $e($pv['payment_proof']) ?>" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                            <span>View Slip</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic text-xs">No image slip</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="/admin/subscriptions/<?= $pv['id'] ?>/approve">
                                            <?= $csrf() ?>
                                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm flex items-center gap-1 transition-all whitespace-nowrap cursor-pointer">
                                                <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                                                <span>Approve & Switch Plan</span>
                                            </button>
                                        </form>

                                        <form method="POST" action="/admin/subscriptions/<?= $pv['id'] ?>/reject" onsubmit="return confirm('Reject this payment submission?')">
                                            <?= $csrf() ?>
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer" title="Reject Payment">
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filter & Search Toolbar -->
    <div class="glass-card p-4">
        <form method="GET" action="/admin/subscriptions" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <!-- Search -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Search Teacher / School</label>
                <input type="text" name="q" value="<?= $e($searchQuery ?? '') ?>" placeholder="Name, email, school..." class="w-full px-3 py-2 text-xs rounded-xl glass-input">
            </div>

            <!-- Filter by Plan -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Filter by Plan</label>
                <select name="plan" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                    <option value="">All Plans</option>
                    <?php foreach ($plans as $pCode => $p): ?>
                        <option value="<?= $pCode ?>" <?= ($planFilter ?? '') === $pCode ? 'selected' : '' ?>>
                            <?= $e($p['name']) ?> ($<?= number_format($p['price'], 2) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter by Status -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Filter by Status</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($statusFilter ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="lifetime" <?= ($statusFilter ?? '') === 'lifetime' ? 'selected' : '' ?>>Lifetime</option>
                    <option value="expired" <?= ($statusFilter ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option>
                </select>
            </div>

            <!-- Submit & Reset -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-500 transition-colors shadow-sm cursor-pointer">
                    Filter
                </button>
                <a href="/admin/subscriptions" class="px-3 py-2 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 bg-slate-100 dark:bg-slate-800 transition-colors border border-slate-200 dark:border-slate-700">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Subscriptions Table -->
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-semibold text-xs tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Invoice / Ref</th>
                        <th class="py-3 px-4">Teacher & School</th>
                        <th class="py-3 px-4">Plan & Cycle</th>
                        <th class="py-3 px-4 font-mono">Price</th>
                        <th class="py-3 px-4">Active Dates</th>
                        <th class="py-3 px-4">Status & Days Left</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    <?php if (empty($subscriptions)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500 dark:text-slate-400 italic">
                                No subscription records match your filter criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subscriptions as $s): ?>
                            <?php 
                                $isLifetime = ($s['plan_code'] === 'lifetime' || $s['status'] === 'lifetime');
                                $pInfo = $plans[$s['plan_code']] ?? null;
                            ?>
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60 transition-colors">
                                <!-- Ref -->
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-500 dark:text-slate-400 tabular-nums">
                                    <div class="font-bold text-slate-900 dark:text-white"><?= $e($s['payment_reference'] ?? 'SUB-' . $s['id']) ?></div>
                                    <div class="text-xs text-slate-400 mt-0.5"><?= $e($s['payment_method']) ?></div>
                                </td>

                                <!-- Teacher -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white"><?= $e($s['teacher_name']) ?></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?= $e($s['school_name'] ?? 'Class Aid') ?></div>
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400 font-mono mt-0.5"><?= $e($s['teacher_email']) ?></div>
                                </td>

                                <!-- Plan -->
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase <?= $pInfo['badge'] ?? 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' ?>">
                                        <?= $e($s['plan_name']) ?>
                                    </span>
                                    <div class="text-xs text-slate-400 mt-1 uppercase font-mono">
                                        Cycle: <?= $e($s['billing_cycle']) ?>
                                    </div>
                                </td>

                                <!-- Price -->
                                <td class="py-3.5 px-4 font-mono font-bold text-sm text-indigo-600 dark:text-indigo-400 tabular-nums">
                                    $<?= number_format((float) $s['price'], 2) ?>
                                </td>

                                <!-- Dates -->
                                <td class="py-3.5 px-4 text-xs font-mono tabular-nums text-slate-600 dark:text-slate-300">
                                    <div>Started: <?= date('M d, Y', strtotime($s['starts_at'])) ?></div>
                                    <div class="mt-0.5">
                                        Expires: <?= !empty($s['expires_at']) ? date('M d, Y', strtotime($s['expires_at'])) : '<span class="text-purple-600 dark:text-purple-400 font-bold">Never (Lifetime)</span>' ?>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-4">
                                    <?php if ($isLifetime): ?>
                                        <span class="px-2.5 py-0.5 rounded text-xs font-bold badge-indigo">
                                            Lifetime VIP
                                        </span>
                                    <?php elseif (!empty($s['expires_at'])): ?>
                                        <?php 
                                            $days = ceil((strtotime($s['expires_at']) - time()) / 86400);
                                        ?>
                                        <?php if ($days <= 0): ?>
                                            <span class="px-2.5 py-0.5 rounded text-xs font-bold badge-rose tabular-nums">
                                                Expired
                                            </span>
                                        <?php elseif ($days <= 7): ?>
                                            <span class="px-2.5 py-0.5 rounded text-xs font-bold badge-amber tabular-nums">
                                                Expiring (<?= $days ?>d)
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-0.5 rounded text-xs font-bold badge-emerald tabular-nums">
                                                Active (<?= $days ?>d)
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>

                                <!-- Actions & Quick Extension -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <?php if (!$isLifetime): ?>
                                            <form method="POST" action="/admin/subscriptions/<?= $s['id'] ?>/extend">
                                                <?= $csrf() ?>
                                                <input type="hidden" name="days" value="30">
                                                <button type="submit" class="px-2 py-1 text-xs font-bold rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200 dark:border-indigo-800 transition-colors cursor-pointer" title="Add 30 days">
                                                    +30d
                                                </button>
                                            </form>
                                            <form method="POST" action="/admin/subscriptions/<?= $s['id'] ?>/extend">
                                                <?= $csrf() ?>
                                                <input type="hidden" name="days" value="90">
                                                <button type="submit" class="px-2 py-1 text-xs font-bold rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800 transition-colors cursor-pointer" title="Add 90 days">
                                                    +90d
                                                </button>
                                            </form>
                                            <form method="POST" action="/admin/subscriptions/<?= $s['id'] ?>/extend">
                                                <?= $csrf() ?>
                                                <input type="hidden" name="days" value="365">
                                                <button type="submit" class="px-2 py-1 text-xs font-bold rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/50 border border-amber-200 dark:border-amber-800 transition-colors cursor-pointer" title="Add 1 year">
                                                    +1yr
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" action="/admin/subscriptions/<?= $s['id'] ?>/delete" onsubmit="return confirm('Remove subscription record #<?= $s['id'] ?> for <?= htmlspecialchars($s['teacher_name'], ENT_QUOTES) ?>?');">
                                            <?= $csrf() ?>
                                            <button type="submit" class="p-1.5 text-xs font-bold rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-800 transition-colors cursor-pointer" title="Remove subscription">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
