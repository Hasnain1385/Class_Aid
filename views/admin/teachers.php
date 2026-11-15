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
                Teacher Directory & Workspaces
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                <?= count($teachers) ?> active educational workspaces registered on Class Aid SaaS.
            </p>
        </div>

        <a href="/admin/teachers/create" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm flex items-center gap-2 transition-all self-start sm:self-auto">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Provision New Teacher</span>
        </a>
    </div>

    <!-- Generated Password Reset Link Banner (if any was just generated) -->
    <?php 
        $lastResetLink = \App\Core\Session::flash('last_reset_link');
        $lastResetTeacher = \App\Core\Session::flash('last_reset_teacher');
    ?>
    <?php if ($lastResetLink): ?>
        <div class="p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/30 border-2 border-indigo-500/40 text-slate-900 dark:text-white space-y-2 shadow-sm animate-in fade-in duration-300">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-300 font-bold text-xs uppercase tracking-wider">
                    <i data-lucide="key-round" class="w-4 h-4 text-indigo-500"></i>
                    <span>Generated Password Reset Link for <?= $e($lastResetTeacher ?? 'Teacher') ?></span>
                </div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">Valid for 24 Hours</span>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="<?= $e($lastResetLink) ?>" id="reset-link-input" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono text-indigo-600 dark:text-indigo-400 select-all">
                <button type="button" onclick="navigator.clipboard.writeText('<?= $e($lastResetLink) ?>'); alert('Password reset link copied to clipboard!');" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm whitespace-nowrap transition-colors flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    <span>Copy Link</span>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Teachers Table Card -->
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-semibold text-xs tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Teacher & School</th>
                        <th class="py-3 px-4">Contact</th>
                        <th class="py-3 px-4">Current Plan</th>
                        <th class="py-3 px-4">Subscription Status</th>
                        <th class="py-3 px-4 text-center">Classes</th>
                        <th class="py-3 px-4 text-center">Students</th>
                        <th class="py-3 px-4">Account Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    <?php if (empty($teachers)): ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500 dark:text-slate-400 italic">
                                No teachers registered yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($teachers as $t): ?>
                            <?php 
                                $pCode = $t['plan_code'] ?? 'none';
                                $pInfo = $plans[$pCode] ?? null;
                                $isLifetime = ($pCode === 'lifetime' || $t['subscription_status'] === 'lifetime');
                                $isSuspended = ($t['status'] === 'suspended');
                            ?>
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60 transition-colors">
                                <!-- Name & School -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-sm text-slate-900 dark:text-white"><?= $e($t['name']) ?></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400"><?= $e($t['school_name'] ?? 'Class Aid School') ?></div>
                                    <div class="text-xs text-slate-400 font-mono mt-0.5 tabular-nums">Joined <?= date('M d, Y', strtotime($t['created_at'])) ?></div>
                                </td>

                                <!-- Contact -->
                                <td class="py-3.5 px-4 font-mono">
                                    <div class="text-indigo-600 dark:text-indigo-400 font-semibold"><?= $e($t['email']) ?></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 tabular-nums"><?= $e($t['phone'] ?? 'No phone') ?></div>
                                </td>

                                <!-- Plan -->
                                <td class="py-3.5 px-4">
                                    <?php if ($pInfo): ?>
                                        <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase <?= $pInfo['badge'] ?>">
                                            <?= $e($pInfo['name']) ?>
                                        </span>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-1 tabular-nums">
                                            $<?= number_format($pInfo['price'], 2) ?> / <?= $e($pInfo['cycle']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic text-xs">Unsubscribed</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Subscription Expiry -->
                                <td class="py-3.5 px-4 text-xs font-mono">
                                    <?php if ($isLifetime): ?>
                                        <span class="text-purple-600 dark:text-purple-400 font-bold flex items-center gap-1">
                                            <i data-lucide="infinity" class="w-3.5 h-3.5"></i>
                                            <span>Lifetime Access</span>
                                        </span>
                                    <?php elseif (!empty($t['subscription_expires_at'])): ?>
                                        <?php 
                                            $days = ceil((strtotime($t['subscription_expires_at']) - time()) / 86400);
                                        ?>
                                        <?php if ($days <= 0): ?>
                                            <span class="text-rose-600 dark:text-rose-400 font-bold tabular-nums">Expired (<?= date('M d, Y', strtotime($t['subscription_expires_at'])) ?>)</span>
                                        <?php elseif ($days <= 7): ?>
                                            <span class="text-amber-600 dark:text-amber-400 font-bold tabular-nums"><?= $days ?> days left (<?= date('M d, Y', strtotime($t['subscription_expires_at'])) ?>)</span>
                                        <?php else: ?>
                                            <span class="text-emerald-600 dark:text-emerald-400 font-medium tabular-nums"><?= date('M d, Y', strtotime($t['subscription_expires_at'])) ?> (<?= $days ?>d)</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Classes Count -->
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800 dark:text-slate-200 tabular-nums">
                                    <?= $t['classes_count'] ?? 0 ?>
                                </td>

                                <!-- Students Count -->
                                <td class="py-3.5 px-4 text-center font-bold text-indigo-600 dark:text-indigo-400 tabular-nums">
                                    <?= $t['students_count'] ?? 0 ?>
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4">
                                    <?php if ($isSuspended): ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold badge-rose">
                                            Suspended
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold badge-emerald">
                                            Active
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="/admin/teachers/<?= $t['id'] ?>/edit" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors" title="Edit Teacher & Plan">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>

                                        <!-- Send Password Reset Link -->
                                        <form method="POST" action="/admin/teachers/<?= $t['id'] ?>/send-reset" onsubmit="return confirm('Generate and email a password reset link to <?= $e($t['email']) ?>?');">
                                            <?= $csrf() ?>
                                            <button type="submit" class="p-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 transition-colors cursor-pointer" title="Generate & Send Password Reset Link">
                                                <i data-lucide="key-round" class="w-4 h-4"></i>
                                            </button>
                                        </form>

                                        <form method="POST" action="/admin/teachers/<?= $t['id'] ?>/toggle" onsubmit="return confirm('Change status for this teacher?');">
                                            <?= $csrf() ?>
                                            <?php if ($isSuspended): ?>
                                                <button type="submit" class="p-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 transition-colors cursor-pointer" title="Reactivate Account">
                                                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 transition-colors cursor-pointer" title="Suspend Account">
                                                    <i data-lucide="ban" class="w-4 h-4"></i>
                                                </button>
                                            <?php endif; ?>
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
