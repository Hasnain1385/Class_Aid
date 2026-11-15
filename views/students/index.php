<div class="space-y-6">

    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                Student Directory & Rosters
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Manage universal Student Admin IDs, student profiles, attendance metrics, and guardians.
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="/students/print/roster<?= $selectedClassId ? '?class_id=' . $selectedClassId : '' ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/80 shadow-sm transition-all" title="Print Official Student Roster & Directory">
                <i data-lucide="printer" class="w-4 h-4 text-violet-500"></i>
                <span>Print Roster<?= $selectedClassId ? ' (Selected Class)' : '' ?></span>
            </a>
            <a href="/students/import<?= $selectedClassId ? '?class_id=' . $selectedClassId : '' ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/80 shadow-sm transition-all">
                <i data-lucide="scan-line" class="w-4 h-4 text-indigo-500"></i>
                <span>Bulk Import (CSV / OCR)</span>
            </a>
            <a href="/students/create<?= $selectedClassId ? '?class_id=' . $selectedClassId : '' ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Register New Student</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="glass-card p-4">
        <form method="GET" action="/students" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Filter by Class</label>
                <select name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                    <option value="">All Managed Classes</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($selectedClassId == $c['id']) ? 'selected' : '' ?>>
                            <?= $e($c['name']) ?> - <?= $e($c['section']) ?> <?= $c['is_home_class'] ? '(Home Class)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Search by Name, Admin ID or Phone</label>
                <div class="flex gap-2">
                    <div class="glass-input-group flex-1" onclick="this.querySelector('input')?.focus()">
                        <div class="input-icon-box">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input type="text" 
                               name="q" 
                               value="<?= $e($searchQuery) ?>" 
                               placeholder="e.g. ADM-2026-010, Sarah, or 555-0101" 
                               class="text-xs">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold transition-all">
                        Search
                    </button>
                    <?php if (!empty($searchQuery) || $selectedClassId): ?>
                        <a href="/students" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold flex items-center justify-center transition-all border border-slate-200 dark:border-slate-700">
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Student Cards (Mobile) / Table (Desktop) -->
    <?php if (empty($students)): ?>
        <div class="glass-card p-12 text-center text-slate-500 dark:text-slate-400">
            <i data-lucide="users" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">No students found</h3>
            <p class="text-xs mt-1">Try adjusting your search criteria or register a new student.</p>
        </div>
    <?php else: ?>
        
        <!-- Desktop Data Table View -->
        <div class="glass-card overflow-hidden hidden md:block">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-semibold text-xs tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Admin ID</th>
                        <th class="py-3 px-4">Student Name</th>
                        <th class="py-3 px-4">Class</th>
                        <th class="py-3 px-4">Guardian & Contact</th>
                        <th class="py-3 px-4">Attendance Rate</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    <?php foreach ($students as $stu): ?>
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400 tabular-nums">
                                <?= $e($stu['student_admin_id']) ?>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">
                                <a href="/students/<?= $stu['id'] ?>" class="hover:underline flex items-center gap-1.5">
                                    <span><?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?></span>
                                    <span class="text-xs text-slate-400 capitalize">(<?= $e($stu['gender'] ?? 'N/A') ?>)</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300">
                                <span class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 font-medium border border-slate-200/50 dark:border-slate-700/60 text-xs">
                                    <?= $e($stu['class_name'] . ' ' . $stu['class_section']) ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-900 dark:text-slate-200 font-medium">
                                    <?= $e($stu['primary_guardian_name']) ?> <span class="text-xs text-slate-400">(<?= $e($stu['primary_guardian_relation']) ?>)</span>
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                                    <span class="font-mono tabular-nums"><?= $e($stu['primary_phone']) ?></span>
                                    <button type="button" 
                                            onclick="openWhatsApp('<?= $e($stu['primary_phone']) ?>', '<?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>', '<?= $e($stu['student_admin_id']) ?>')"
                                            class="text-emerald-500 hover:text-emerald-600" title="WhatsApp Chat">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <?php 
                                    $rate = $stu['stats']['percentage'] ?? 0;
                                    $rateBadge = $rate >= 85 ? 'badge-emerald' : ($rate >= 75 ? 'badge-amber' : 'badge-rose');
                                ?>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-full font-bold text-xs <?= $rateBadge ?> tabular-nums"><?= $rate ?>%</span>
                                    <span class="text-xs text-slate-400 tabular-nums">(<?= $stu['stats']['present'] ?? 0 ?>/<?= $stu['stats']['total'] ?? 0 ?>)</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="/calls/create?student_id=<?= $stu['id'] ?>" class="p-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400" title="Log Parent Call">
                                        <i data-lucide="phone-call" class="w-4 h-4"></i>
                                    </a>
                                    <a href="/students/<?= $stu['id'] ?>" class="p-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400" title="View 360° Dossier">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="/students/<?= $stu['id'] ?>/edit" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300" title="Edit Student">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Touch Cards View -->
        <div class="grid grid-cols-1 gap-3 md:hidden">
            <?php foreach ($students as $stu): ?>
                <div class="glass-card p-4 space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono text-xs font-bold text-indigo-500 tabular-nums"><?= $e($stu['student_admin_id']) ?></span>
                                <span class="text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700/60">
                                    <?= $e($stu['class_name'] . ' ' . $stu['class_section']) ?>
                                </span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-1">
                                <?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>
                            </h3>
                        </div>

                        <?php 
                            $rate = $stu['stats']['percentage'] ?? 0;
                            $rateBadge = $rate >= 85 ? 'badge-emerald' : ($rate >= 75 ? 'badge-amber' : 'badge-rose');
                        ?>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-bold tabular-nums <?= $rateBadge ?>">
                            <?= $rate ?>% Att.
                        </span>
                    </div>

                    <div class="text-xs bg-slate-50 dark:bg-slate-950/60 p-3 rounded-xl space-y-1.5 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Guardian:</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200"><?= $e($stu['primary_guardian_name']) ?> (<?= $e($stu['primary_guardian_relation']) ?>)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Contact:</span>
                            <span class="font-mono text-slate-800 dark:text-slate-200 tabular-nums"><?= $e($stu['primary_phone']) ?></span>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="grid grid-cols-3 gap-2 pt-1 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" 
                                onclick="openWhatsApp('<?= $e($stu['primary_phone']) ?>', '<?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>', '<?= $e($stu['student_admin_id']) ?>')"
                                class="py-2 px-2 rounded-xl text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 flex items-center justify-center gap-1 transition-colors">
                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                            <span>Chat</span>
                        </button>
                        <a href="/calls/create?student_id=<?= $stu['id'] ?>" class="py-2 px-2 rounded-xl text-xs font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-500/20 flex items-center justify-center gap-1 transition-colors">
                            <i data-lucide="phone-call" class="w-3.5 h-3.5"></i>
                            <span>Call Log</span>
                        </a>
                        <a href="/students/<?= $stu['id'] ?>" class="py-2 px-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center gap-1 transition-colors">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            <span>Profile</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>
