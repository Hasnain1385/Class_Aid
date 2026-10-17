<div class="space-y-6">

    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Student Directory & Rosters
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Manage universal Student Admin IDs, student profiles, attendance metrics, and guardians.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/students/create<?= $selectedClassId ? '?class_id=' . $selectedClassId : '' ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/30 transition-all">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Register New Student</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="glass-card p-4">
        <form method="GET" action="/students" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Filter by Class</label>
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
                <label class="block text-[11px] font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Search by Name, Admin ID or Phone</label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input type="text" 
                               name="q" 
                               value="<?= $e($searchQuery) ?>" 
                               placeholder="e.g. ADM-2026-010, Sarah, or 555-0101" 
                               class="w-full pl-9 pr-3 py-2 text-xs rounded-xl glass-input">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold transition-all">
                        Search
                    </button>
                    <?php if (!empty($searchQuery) || $selectedClassId): ?>
                        <a href="/students" class="px-3 py-2 bg-gray-200 dark:bg-white/10 hover:bg-gray-300 dark:hover:bg-white/20 text-gray-700 dark:text-gray-300 rounded-xl text-xs flex items-center justify-center transition-all">
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Student Cards (Mobile) / Table (Desktop) -->
    <?php if (empty($students)): ?>
        <div class="glass-card p-12 text-center text-gray-500 dark:text-gray-400">
            <i data-lucide="users" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">No students found</h3>
            <p class="text-xs mt-1">Try adjusting your search criteria or register a new student.</p>
        </div>
    <?php else: ?>
        
        <!-- Desktop Data Table View -->
        <div class="glass-card overflow-hidden hidden md:block">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-200/50 dark:border-white/10 text-gray-500 dark:text-gray-400 uppercase font-semibold">
                    <tr>
                        <th class="py-3 px-4">Admin ID</th>
                        <th class="py-3 px-4">Student Name</th>
                        <th class="py-3 px-4">Class</th>
                        <th class="py-3 px-4">Guardian & Contact</th>
                        <th class="py-3 px-4">Attendance Rate</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php foreach ($students as $stu): ?>
                        <tr class="hover:bg-indigo-500/5 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                <?= $e($stu['student_admin_id']) ?>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-gray-900 dark:text-white">
                                <a href="/students/<?= $stu['id'] ?>" class="hover:underline flex items-center gap-1.5">
                                    <span><?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?></span>
                                    <span class="text-[10px] text-gray-400 capitalize">(<?= $e($stu['gender'] ?? 'N/A') ?>)</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">
                                <span class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-white/10 font-medium">
                                    <?= $e($stu['class_name'] . ' ' . $stu['class_section']) ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-gray-900 dark:text-gray-200 font-medium">
                                    <?= $e($stu['primary_guardian_name']) ?> <span class="text-[10px] text-gray-400">(<?= $e($stu['primary_guardian_relation']) ?>)</span>
                                </div>
                                <div class="text-[11px] text-gray-500 flex items-center gap-2 mt-0.5">
                                    <span><?= $e($stu['primary_phone']) ?></span>
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
                                    $rateColor = $rate >= 85 ? 'text-emerald-500' : ($rate >= 75 ? 'text-amber-500' : 'text-rose-500 font-bold');
                                ?>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold <?= $rateColor ?>"><?= $rate ?>%</span>
                                    <span class="text-[10px] text-gray-400">(<?= $stu['stats']['present'] ?? 0 ?>/<?= $stu['stats']['total'] ?? 0 ?>)</span>
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
                                    <a href="/students/<?= $stu['id'] ?>/edit" class="p-1.5 rounded-lg bg-gray-500/10 hover:bg-gray-500/20 text-gray-600 dark:text-gray-400" title="Edit Student">
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
                                <span class="font-mono text-xs font-bold text-indigo-500"><?= $e($stu['student_admin_id']) ?></span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300">
                                    <?= $e($stu['class_name'] . ' ' . $stu['class_section']) ?>
                                </span>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">
                                <?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>
                            </h3>
                        </div>

                        <?php 
                            $rate = $stu['stats']['percentage'] ?? 0;
                            $rateColor = $rate >= 85 ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/30' : ($rate >= 75 ? 'bg-amber-500/10 text-amber-600 border-amber-500/30' : 'bg-rose-500/10 text-rose-600 border-rose-500/30');
                        ?>
                        <span class="text-xs px-2.5 py-1 rounded-full border font-bold <?= $rateColor ?>">
                            <?= $rate ?>% Att.
                        </span>
                    </div>

                    <div class="text-xs bg-gray-50/50 dark:bg-white/5 p-2.5 rounded-xl space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Guardian:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200"><?= $e($stu['primary_guardian_name']) ?> (<?= $e($stu['primary_guardian_relation']) ?>)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Contact:</span>
                            <span class="font-mono text-gray-800 dark:text-gray-200"><?= $e($stu['primary_phone']) ?></span>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="grid grid-cols-3 gap-2 pt-1 border-t border-gray-100 dark:border-white/5">
                        <button type="button" 
                                onclick="openWhatsApp('<?= $e($stu['primary_phone']) ?>', '<?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>', '<?= $e($stu['student_admin_id']) ?>')"
                                class="py-2 px-2 rounded-xl text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center gap-1">
                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                            <span>Chat</span>
                        </button>
                        <a href="/calls/create?student_id=<?= $stu['id'] ?>" class="py-2 px-2 rounded-xl text-xs font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center gap-1">
                            <i data-lucide="phone-call" class="w-3.5 h-3.5"></i>
                            <span>Call Log</span>
                        </a>
                        <a href="/students/<?= $stu['id'] ?>" class="py-2 px-2 rounded-xl text-xs font-semibold bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-200 flex items-center justify-center gap-1">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            <span>Profile</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>
