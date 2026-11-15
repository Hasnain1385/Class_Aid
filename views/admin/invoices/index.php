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
                Invoices & Financial Audit
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Official billing records, tax receipts, and payment verification history across all institutions.
            </p>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="glass-card p-4">
        <form method="GET" action="/admin/invoices" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Search Invoice / Teacher</label>
                <input type="text" name="q" value="<?= $e($searchQuery ?? '') ?>" placeholder="INV-2026-..., name, email..." class="w-full px-3 py-2 text-xs rounded-xl glass-input">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Filter by Status</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                    <option value="">All Statuses</option>
                    <option value="paid" <?= ($statusFilter ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="pending_verification" <?= ($statusFilter ?? '') === 'pending_verification' ? 'selected' : '' ?>>Pending Verification</option>
                    <option value="unpaid" <?= ($statusFilter ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-500 transition-colors shadow-sm cursor-pointer">
                    Filter Invoices
                </button>
                <a href="/admin/invoices" class="px-3 py-2 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 bg-slate-100 dark:bg-slate-800 transition-colors border border-slate-200 dark:border-slate-700">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-semibold text-xs tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Teacher / School</th>
                        <th class="py-3 px-4">Plan Item</th>
                        <th class="py-3 px-4 font-mono">Amount</th>
                        <th class="py-3 px-4">Method & TRX</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500 dark:text-slate-400 italic">
                                No invoice records found matching the criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv): ?>
                            <?php 
                                $isPaid = ($inv['status'] === 'paid');
                                $isPending = ($inv['status'] === 'pending_verification');
                            ?>
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400 tabular-nums">
                                    <?= $e($inv['invoice_number']) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white"><?= $e($inv['teacher_name'] ?? 'User #' . $inv['user_id']) ?></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?= $e($inv['school_name'] ?? 'Class Aid School') ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-slate-900 dark:text-white"><?= $e($inv['plan_name']) ?></span>
                                    <span class="text-xs text-slate-400 uppercase font-mono block mt-0.5"><?= $e($inv['billing_cycle']) ?></span>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400 text-sm tabular-nums">
                                    $<?= number_format((float) $inv['amount'], 2) ?>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs">
                                    <span class="text-xs uppercase font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        <?= $e($inv['payment_method']) ?>
                                    </span>
                                    <div class="text-xs text-slate-400 mt-0.5 tabular-nums"><?= $e($inv['payment_reference'] ?? '-') ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if ($isPaid): ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase badge-emerald">
                                            Paid
                                        </span>
                                    <?php elseif ($isPending): ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase badge-amber animate-pulse">
                                            Verification Pending
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase badge-rose">
                                            Unpaid
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-500 dark:text-slate-400 tabular-nums">
                                    <?= date('M d, Y', strtotime($inv['issue_date'])) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="/subscription/invoice/<?= $inv['id'] ?>" target="_blank" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors inline-flex items-center gap-1" title="View Printable Invoice">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        <span>Print PDF</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
