<div class="space-y-8">

    <!-- Hero Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">SaaS Cloud Active</span>
            </div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white mt-1">
                SaaS Command Center
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Multi-tenant teacher workspace governance, subscription billing & broadcast alerts.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="/admin/plans" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm flex items-center gap-1.5 transition-all">
                <i data-lucide="package" class="w-4 h-4"></i>
                <span>Manage Plans</span>
            </a>
            <a href="/admin/teachers/create" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm flex items-center gap-1.5 transition-all">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Provision Teacher</span>
            </a>
            <a href="/admin/announcements" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm flex items-center gap-1.5 transition-all">
                <i data-lucide="megaphone" class="w-4 h-4 text-purple-500"></i>
                <span>New Broadcast</span>
            </a>
            <a href="/admin/reminders" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm flex items-center gap-1.5 transition-all">
                <i data-lucide="calendar-clock" class="w-4 h-4 text-amber-500"></i>
                <span>Send Reminder</span>
            </a>
        </div>
    </div>

    <!-- Pending Payment Verifications Banner -->
    <?php if (!empty($pendingVerificationsCount)): ?>
        <div class="p-4 sm:p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center flex-shrink-0 animate-pulse">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                        <span><?= $pendingVerificationsCount ?> Pending Payment Verification<?= $pendingVerificationsCount > 1 ? 's' : '' ?></span>
                        <span class="text-xs uppercase px-2 py-0.5 rounded-full badge-amber font-bold">Action Required</span>
                    </h3>
                    <p class="text-xs text-amber-800 dark:text-amber-300 mt-0.5">
                        Teachers are currently active in 14-day trial mode awaiting your EasyPaisa / Bank payment verification.
                    </p>
                </div>
            </div>
            <a href="/admin/subscriptions" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 shadow-sm whitespace-nowrap transition-colors flex items-center gap-1.5 flex-shrink-0">
                <span>Review & Verify Payments</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    <?php endif; ?>

    <!-- SaaS Metric KPIs Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Estimated MRR -->
        <div class="glass-card p-5">
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-2">
                <span class="font-bold uppercase tracking-wider text-xs">Estimated MRR</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tabular-nums">
                $<?= number_format($stats['estimated_mrr'], 2) ?>
            </div>
            <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mt-1 tabular-nums">
                ARR: $<?= number_format($stats['estimated_arr'], 2) ?>
            </div>
        </div>

        <!-- Active Teachers -->
        <div class="glass-card p-5">
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-2">
                <span class="font-bold uppercase tracking-wider text-xs">Active Teachers</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tabular-nums">
                <?= $stats['total_teachers'] ?>
            </div>
            <div class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-1">
                <?= $stats['active_subscriptions'] ?> Paid Subscriptions
            </div>
        </div>

        <!-- Lifetime Subscribers -->
        <div class="glass-card p-5">
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-2">
                <span class="font-bold uppercase tracking-wider text-xs">Lifetime VIPs</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    <i data-lucide="crown" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tabular-nums">
                <?= $stats['lifetime_subscriptions'] ?>
            </div>
            <div class="text-xs text-purple-600 dark:text-purple-400 font-semibold mt-1">
                Perpetual Licenses
            </div>
        </div>

        <!-- Expiring in 7 Days -->
        <div class="glass-card p-5">
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-2">
                <span class="font-bold uppercase tracking-wider text-xs">Expiring &lt; 7 Days</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono tabular-nums">
                <?= $stats['expiring_in_7_days'] ?>
            </div>
            <div class="text-xs text-amber-600 dark:text-amber-400 font-semibold mt-1">
                <?= $stats['expired_subscriptions'] ?> Expired Accounts
            </div>
        </div>

    </div>

    <!-- Subscription Plans Matrix & Breakdown -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Subscription Plan Tiers & Active Roster
            </h2>
            <div class="flex items-center gap-3">
                <a href="/admin/plans" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    Manage Plans &rarr;
                </a>
                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                <a href="/admin/subscriptions" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    View Subscriptions &rarr;
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <?php foreach ($plans as $pCode => $p): ?>
                <?php 
                    $count = $stats['plan_breakdown'][$pCode] ?? 0;
                ?>
                <div class="glass-card p-4 rounded-xl flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-mono uppercase font-bold text-slate-400"><?= $e($p['cycle']) ?></span>
                            <span class="px-2 py-0.5 rounded text-xs font-bold <?= $p['badge'] ?>">
                                <?= $count ?> active
                            </span>
                        </div>
                        <div class="font-bold text-sm text-slate-900 dark:text-white"><?= $e($p['name']) ?></div>
                        <div class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 mt-1 tabular-nums">
                            $<?= number_format($p['price'], 2) ?>
                        </div>
                    </div>
                    <?php if (!empty($p['discount'])): ?>
                        <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium mt-2">
                            <?= $e($p['discount']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Two-Column Layout: Expiring Soon & Recent Teachers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Column 1: Action Required (Subscriptions Expiring Soon) -->
        <div class="glass-card p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="clock" class="w-5 h-5 text-amber-500"></i>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider">
                        Expiring Subscriptions (Next 7 Days)
                    </h3>
                </div>
                <span class="text-xs font-mono px-2.5 py-0.5 rounded-full badge-amber font-bold">
                    <?= count($expiring) ?> alerts
                </span>
            </div>

            <?php if (empty($expiring)): ?>
                <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-xs">
                    <i data-lucide="check-circle" class="w-8 h-8 text-emerald-500 mx-auto mb-2 opacity-60"></i>
                    No subscriptions expiring within the next 7 days.
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($expiring as $sub): ?>
                        <?php 
                            $days = ceil((strtotime($sub['expires_at']) - time()) / 86400);
                        ?>
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800 flex items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-xs text-slate-900 dark:text-white"><?= $e($sub['teacher_name']) ?></div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?= $e($sub['school_name']) ?> • <span class="font-mono text-indigo-600 dark:text-indigo-400"><?= $e($sub['plan_name']) ?></span></div>
                                <div class="text-xs text-amber-600 dark:text-amber-400 font-bold mt-1 tabular-nums">
                                    Expires in <?= $days ?> day<?= $days == 1 ? '' : 's' ?> (<?= date('M d, Y', strtotime($sub['expires_at'])) ?>)
                                </div>
                            </div>

                            <form method="POST" action="/admin/subscriptions/<?= $sub['id'] ?>/extend" class="flex items-center gap-1">
                                <?= $csrf() ?>
                                <input type="hidden" name="days" value="30">
                                <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded-lg badge-amber hover:opacity-90 transition-opacity whitespace-nowrap cursor-pointer">
                                    +30 Days Grace
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Column 2: Teacher Onboarding Activity -->
        <div class="glass-card p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="user-check" class="w-5 h-5 text-indigo-500"></i>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider">
                        Recent Teacher Workspaces
                    </h3>
                </div>
                <a href="/admin/teachers" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">
                    Manage Roster &rarr;
                </a>
            </div>

            <div class="space-y-3">
                <?php foreach ($recentTeachers as $t): ?>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                <?= strtoupper(substr($t['name'], 0, 2)) ?>
                            </div>
                            <div>
                                <div class="font-bold text-xs text-slate-900 dark:text-white flex items-center gap-2">
                                    <span><?= $e($t['name']) ?></span>
                                    <?php if ($t['status'] === 'suspended'): ?>
                                        <span class="text-xs px-2 py-0.5 rounded badge-rose font-bold uppercase">Suspended</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?= $e($t['email']) ?> • <?= $e($t['school_name']) ?></div>
                            </div>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase <?= $plans[$t['plan_code']]['badge'] ?? 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' ?>">
                                <?= $e($t['plan_code'] ?? 'No Plan') ?>
                            </span>
                            <div class="text-xs text-slate-400 mt-1 tabular-nums">
                                <?= $t['students_count'] ?? 0 ?> students
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- Active Broadcast Announcements Overview -->
    <div class="glass-card p-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
            <div class="flex items-center gap-2">
                <i data-lucide="megaphone" class="w-5 h-5 text-purple-500"></i>
                <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider">
                    Published System Announcements
                </h3>
            </div>
            <a href="/admin/announcements" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">
                Manage Broadcasts &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($announcements as $ann): ?>
                <?php 
                    $tInfo = \App\Models\Announcement::TYPES[$ann['type']] ?? \App\Models\Announcement::TYPES['info'];
                ?>
                <div class="p-4 rounded-xl border <?= $tInfo['banner'] ?>">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold uppercase px-2 py-0.5 rounded-full <?= $tInfo['badge'] ?>">
                            <?= $e($tInfo['label']) ?>
                        </span>
                        <span class="text-xs opacity-75 font-mono tabular-nums">
                            <?= date('M d, Y', strtotime($ann['created_at'])) ?>
                        </span>
                    </div>
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white"><?= $e($ann['title']) ?></h4>
                    <p class="text-xs opacity-90 mt-1 line-clamp-2"><?= $e($ann['content']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>
