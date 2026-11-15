<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="/dashboard" class="text-xs text-slate-400 hover:text-indigo-500 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                Subscription & Workspace Billing
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Manage your institutional educator tier, track renewal dates, and upgrade features.
            </p>
        </div>
    </div>

    <!-- Active Subscription Status Banner -->
    <?php if ($activeSub): ?>
        <?php 
            $isLifetime = ($activeSub['plan_code'] === 'lifetime' || $activeSub['status'] === 'lifetime');
            $daysLeft = $activeSub['days_left'] ?? 0;
            $isExpiring = !empty($activeSub['is_expiring']);
            $isExpired = !empty($activeSub['is_expired']);
            $isPending = ($activeSub['status'] === 'pending_verification');
        ?>

        <!-- Pending Manual Verification Notice -->
        <?php if ($isPending): ?>
            <div class="p-4 sm:p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-900 dark:text-amber-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center flex-shrink-0 animate-pulse">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm">Payment Under Review & Verification</h3>
                        <p class="text-xs text-amber-800 dark:text-amber-300 mt-0.5">
                            Your payment submission (TRX / Ref: <strong class="font-mono tabular-nums"><?= $e($activeSub['payment_reference'] ?? 'Submitted') ?></strong>) via <?= strtoupper($activeSub['payment_method']) ?> is waiting for Administrator sign-off.
                        </p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase badge-amber">
                        Pending Sign-off
                    </span>
                </div>
            </div>
        <!-- Expiry Alert Banner -->
        <?php elseif ($isExpired): ?>
            <div class="p-4 sm:p-5 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-800 dark:text-rose-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-500 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm">Your Subscription Has Expired!</h3>
                        <p class="text-xs text-rose-700 dark:text-rose-300 mt-0.5">Please renew or upgrade below to ensure uninterrupted access to classroom registers and parent logs.</p>
                    </div>
                </div>
            </div>
        <?php elseif ($isExpiring): ?>
            <div class="p-4 sm:p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-900 dark:text-amber-200 flex flex-col sm:flex-row items-center justify-between gap-4 animate-pulse">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm">Renewal Notice: Expiring in <?= $daysLeft ?> Day<?= $daysLeft == 1 ? '' : 's' ?>!</h3>
                        <p class="text-xs text-amber-800 dark:text-amber-300 mt-0.5">Your plan expires on <?= date('F d, Y', strtotime($activeSub['expires_at'])) ?>. Extend your plan below.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Current Plan Overview Card -->
        <div class="glass-card p-6 sm:p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-200 dark:border-slate-800">
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-xs font-mono font-bold uppercase tracking-wider text-indigo-500">Current Plan</span>
                        <?php if ($isLifetime): ?>
                            <span class="text-xs font-bold uppercase px-2.5 py-0.5 rounded-full badge-indigo">
                                Lifetime VIP
                            </span>
                        <?php elseif ($isExpired): ?>
                            <span class="text-xs font-bold uppercase px-2.5 py-0.5 rounded-full badge-rose">
                                Expired
                            </span>
                        <?php else: ?>
                            <span class="text-xs font-bold uppercase px-2.5 py-0.5 rounded-full badge-emerald">
                                Active License
                            </span>
                        <?php endif; ?>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white"><?= $e($activeSub['plan_name']) ?></h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-mono">Invoice Reference: <?= $e($activeSub['payment_reference']) ?></p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-left md:text-right">
                        <div class="text-3xl font-black text-slate-900 dark:text-white font-mono tabular-nums">
                            $<?= number_format((float) $activeSub['price'], 2) ?>
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 uppercase font-mono mt-0.5">
                            Billed <?= $e($activeSub['billing_cycle']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metadata Details Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-6 text-xs">
                <div>
                    <span class="text-slate-500 dark:text-slate-400 uppercase font-semibold text-xs block">Activation Date</span>
                    <span class="font-bold text-slate-900 dark:text-white mt-1 block"><?= date('F d, Y', strtotime($activeSub['starts_at'])) ?></span>
                </div>
                <div>
                    <span class="text-slate-500 dark:text-slate-400 uppercase font-semibold text-xs block">Renewal / Expiry</span>
                    <span class="font-bold text-slate-900 dark:text-white mt-1 block">
                        <?= !empty($activeSub['expires_at']) ? date('F d, Y', strtotime($activeSub['expires_at'])) : 'Never (Lifetime)' ?>
                    </span>
                </div>
                <div>
                    <span class="text-slate-500 dark:text-slate-400 uppercase font-semibold text-xs block">Time Remaining</span>
                    <span class="font-bold mt-1 block <?= $daysLeft <= 7 && !$isLifetime ? 'text-amber-500' : 'text-emerald-500' ?>">
                        <?= $isLifetime ? 'Unlimited Lifetime' : ($daysLeft <= 0 ? '0 Days (Expired)' : $daysLeft . ' Days Remaining') ?>
                    </span>
                </div>
                <div>
                    <span class="text-slate-500 dark:text-slate-400 uppercase font-semibold text-xs block">Payment Channel</span>
                    <span class="font-bold uppercase text-slate-900 dark:text-white mt-1 block"><?= $e($activeSub['payment_method']) ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Plan Upgrade & Renewal Options Matrix -->
    <div>
        <div class="mb-4">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                Available Educator Plans & Upgrades
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Select any tier below to upgrade your billing cycle or renew instantly.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            <?php foreach ($plans as $pCode => $p): ?>
                <?php 
                    $isCurrent = ($activeSub && $activeSub['plan_code'] === $pCode && !$activeSub['is_expired']);
                    $isPopular = !empty($p['popular']);
                ?>
                <div class="glass-card p-5 rounded-2xl flex flex-col justify-between border-2 <?= $isCurrent ? 'border-indigo-600 bg-indigo-50/20 dark:bg-indigo-950/20' : ($isPopular ? 'border-indigo-500/50 shadow-md' : 'border-slate-200 dark:border-slate-800') ?>">
                    <div>
                        <!-- Badges -->
                        <div class="h-6 mb-2">
                            <?php if ($isCurrent): ?>
                                <span class="text-xs font-bold uppercase px-2.5 py-0.5 rounded-full bg-indigo-600 text-white inline-block">
                                    Current Plan
                                </span>
                            <?php elseif ($isPopular): ?>
                                <span class="text-xs font-bold uppercase px-2.5 py-0.5 rounded-full bg-indigo-600 text-white inline-block shadow-sm">
                                    Most Popular
                                </span>
                            <?php elseif (!empty($p['discount'])): ?>
                                <span class="text-xs font-bold uppercase px-2.5 py-0.5 rounded-full badge-emerald inline-block">
                                    <?= $e($p['discount']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h3 class="font-bold text-sm text-slate-900 dark:text-white"><?= $e($p['name']) ?></h3>

                        <div class="my-3 font-mono">
                            <span class="text-2xl font-black text-slate-900 dark:text-white tabular-nums">$<?= number_format($p['price'], 2) ?></span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">/ <?= $e($p['cycle']) ?></span>
                        </div>

                        <!-- Features List -->
                        <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-300 my-4">
                            <?php foreach ($p['features'] as $feat): ?>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5"></i>
                                    <span><?= $e($feat) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800">
                        <form method="POST" action="/subscription/checkout">
                            <?= $csrf() ?>
                            <input type="hidden" name="plan_code" value="<?= $pCode ?>">
                            <?php if ($isCurrent): ?>
                                <button type="submit" class="w-full py-2.5 px-3 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200 dark:border-indigo-800 transition-colors">
                                    Renew <?= $e($p['name']) ?>
                                </button>
                            <?php else: ?>
                                <button type="submit" class="w-full py-2.5 px-3 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all">
                                    <?= $pCode === 'lifetime' ? 'Get Lifetime Access' : 'Upgrade to ' . $e($p['cycle']) ?>
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Invoices & Billing Audit History -->
    <div class="glass-card p-6">
        <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
            <i data-lucide="receipt" class="w-4 h-4 text-indigo-500"></i>
            <span>Billing History & Invoices</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-semibold text-xs tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Plan Description</th>
                        <th class="py-3 px-4">Method & TRX</th>
                        <th class="py-3 px-4 font-mono">Amount</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">PDF Invoice</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    <?php 
                        $invoiceList = !empty($invoices) ? $invoices : $history;
                    ?>
                    <?php if (empty($invoiceList)): ?>
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-500 dark:text-slate-400 italic">No previous subscription invoices found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoiceList as $inv): ?>
                            <?php 
                                $invId = $inv['id'];
                                $invNum = $inv['invoice_number'] ?? ($inv['payment_reference'] ?? 'INV-' . $invId);
                                $isPaid = in_array($inv['status'], ['paid', 'active', 'lifetime'], true);
                            ?>
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60 transition-colors">
                                <td class="py-3.5 px-4 font-mono text-indigo-600 dark:text-indigo-400 font-bold tabular-nums">
                                    <a href="/subscription/invoice/<?= $invId ?>" class="hover:underline flex items-center gap-1.5">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                        <span><?= $e($invNum) ?></span>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">
                                    <?= $e($inv['plan_name']) ?>
                                    <span class="text-xs text-slate-400 uppercase font-mono block mt-0.5"><?= $e($inv['billing_cycle'] ?? 'monthly') ?></span>
                                </td>
                                <td class="py-3.5 px-4 uppercase text-slate-500 dark:text-slate-400 font-mono text-xs">
                                    <span class="font-bold text-slate-700 dark:text-slate-300"><?= $e($inv['payment_method'] ?? 'card') ?></span>
                                    <?php if (!empty($inv['payment_reference'])): ?>
                                        <div class="text-xs text-slate-400 font-mono mt-0.5 tabular-nums"><?= $e($inv['payment_reference']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white tabular-nums">
                                    $<?= number_format((float) ($inv['amount'] ?? ($inv['price'] ?? 0)), 2) ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 tabular-nums">
                                    <?= date('M d, Y', strtotime($inv['created_at'])) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase <?= $isPaid ? 'badge-emerald' : 'badge-amber' ?>">
                                        <?= $e($inv['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="/subscription/invoice/<?= $invId ?>" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors">
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
