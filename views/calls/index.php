<div class="space-y-6">

    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="phone-call" class="w-6 h-6 text-emerald-500"></i>
                <span>Parent Communication Journal</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Centralized registry of parent phone calls, discussions, parent commitments, and follow-ups.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- CSV Export button -->
            <a href="/calls/export/csv?<?= http_build_query($filters) ?>" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-all">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Export CSV</span>
            </a>

            <!-- Print Audit Log -->
            <a href="/calls/print/audit?<?= http_build_query($filters) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-all">
                <i data-lucide="printer" class="w-4 h-4 text-emerald-500"></i>
                <span>Print Call Table</span>
            </a>

            <!-- Log Call Button -->
            <a href="/calls/create" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all">
                <i data-lucide="phone-outgoing" class="w-4 h-4"></i>
                <span>Log New Call</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Panel -->
    <div class="glass-card p-4">
        <form method="GET" action="/calls" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            
            <!-- Class Filter -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Class</label>
                <div class="glass-input-group" onclick="this.querySelector('select')?.focus()">
                    <div class="input-icon-box">
                        <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                    </div>
                    <select name="class_id">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($filters['class_id'] == $c['id']) ? 'selected' : '' ?>>
                                <?= $e($c['name']) ?> - <?= $e($c['section']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Category</label>
                <div class="glass-input-group" onclick="this.querySelector('select')?.focus()">
                    <div class="input-icon-box">
                        <i data-lucide="folder" class="w-4 h-4"></i>
                    </div>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $catKey => $catData): ?>
                            <option value="<?= $catKey ?>" <?= (($filters['category'] ?? '') === $catKey) ? 'selected' : '' ?>>
                                <?= $e(is_array($catData) ? ($catData['label'] ?? $catKey) : $catData) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">From Date</label>
                <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                    <div class="input-icon-box">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                    <input type="date" name="date_from" value="<?= $e($filters['date_from'] ?? '') ?>" class="tabular-nums">
                </div>
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">To Date</label>
                <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                    <div class="input-icon-box">
                        <i data-lucide="calendar-check" class="w-4 h-4"></i>
                    </div>
                    <input type="date" name="date_to" value="<?= $e($filters['date_to'] ?? '') ?>" class="tabular-nums">
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-end gap-2 pb-0.5">
                <button type="submit" class="flex-1 h-11 px-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold transition-all flex items-center justify-center gap-1.5 shadow-sm">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Filter</span>
                </button>
                <a href="/calls" class="h-11 px-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold transition-all border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                    Reset
                </a>
            </div>

        </form>
    </div>

    <!-- Communication Cards List -->
    <?php if (empty($calls)): ?>
        <div class="glass-card p-12 text-center text-slate-500 dark:text-slate-400">
            <i data-lucide="phone-missed" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">No communication records found</h3>
            <p class="text-xs mt-1">No call logs match the selected filter criteria.</p>
        </div>
    <?php else: ?>
        <div class="space-y-2.5">
            <?php foreach ($calls as $call): ?>
                <div class="glass-card p-3 sm:p-3.5 hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                    
                    <!-- Top header row (Compact Single Ribbon) -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 pb-2 mb-2 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2 flex-wrap">
                            <?= \App\Models\ParentCallLog::renderPill($call['call_category']) ?>
                            <a href="/students/<?= $call['student_id'] ?>" class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white hover:underline">
                                <?= $e($call['first_name'] . ' ' . $call['last_name']) ?>
                            </a>
                            <span class="font-mono text-xs text-indigo-500 font-semibold tabular-nums">[<?= $e($call['student_admin_id']) ?>]</span>
                            <span class="text-[11px] px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700/60">
                                <?= $e($call['class_name']) ?> - <?= $e($call['class_section']) ?>
                            </span>
                            <span class="text-slate-300 dark:text-slate-700 hidden sm:inline">•</span>
                            <span class="text-xs text-slate-600 dark:text-slate-300">
                                <span class="text-slate-400">Contact:</span> <strong><?= $e($call['contact_person_name']) ?></strong> (<?= $e($call['relationship']) ?>)
                                <span class="font-mono text-slate-500 tabular-nums ml-1"><?= $e($call['phone_dialed']) ?></span>
                            </span>
                        </div>

                        <div class="flex items-center gap-1.5 text-[11px] text-slate-400 tabular-nums flex-shrink-0">
                            <i data-lucide="clock" class="w-3 h-3"></i>
                            <span><?= date('M d, Y • h:i A', strtotime($call['call_timestamp'])) ?></span>
                        </div>
                    </div>

                    <!-- Discussion & Parent Response Split (Compact Layout) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                        <div class="bg-slate-50/70 dark:bg-slate-950/40 p-2.5 rounded-lg border border-slate-200/60 dark:border-slate-800">
                            <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider mb-0.5">
                                Discussion Notes:
                            </span>
                            <p class="text-slate-800 dark:text-slate-200 leading-snug break-words">
                                <?= $e($call['discussion_summary']) ?>
                            </p>
                        </div>

                        <div class="bg-indigo-50/20 dark:bg-indigo-950/20 p-2.5 rounded-lg border border-indigo-200/50 dark:border-indigo-900/40">
                            <span class="text-indigo-600 dark:text-indigo-400 block text-[10px] font-bold uppercase tracking-wider mb-0.5">
                                Parent Response &amp; Commitment:
                            </span>
                            <p class="text-slate-800 dark:text-slate-200 italic font-medium leading-snug break-words">
                                "<?= $e($call['parent_response']) ?>"
                            </p>
                        </div>
                    </div>

                    <!-- Footer actions (WhatsApp, Follow-up status, Toggle) -->
                    <div class="mt-2 pt-2 flex flex-wrap items-center justify-between gap-2 text-xs border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <button type="button" 
                                    onclick="openWhatsApp('<?= $e($call['phone_dialed']) ?>', '<?= $e($call['first_name'] . ' ' . $call['last_name']) ?>', '<?= $e($call['student_admin_id']) ?>')"
                                    class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold hover:underline cursor-pointer">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <span>WhatsApp Parent</span>
                            </button>

                            <?php if ($call['follow_up_required']): ?>
                                <span class="text-amber-500 flex items-center gap-1 font-semibold text-[11px]">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    <span>Follow-up: <?= !empty($call['follow_up_date']) ? date('M d, Y', strtotime($call['follow_up_date'])) : 'Pending' ?></span>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="POST" action="/calls/toggle-status/<?= $call['id'] ?>">
                                <?= $csrf() ?>
                                <input type="hidden" name="redirect" value="/calls?<?= http_build_query($filters) ?>">
                                <button type="submit" class="px-2 py-0.5 rounded-lg border text-[11px] font-semibold transition-colors cursor-pointer <?= $call['status'] === 'Resolved' ? 'badge-emerald' : 'badge-amber' ?>">
                                    Status: <?= $e($call['status']) ?>
                                </button>
                            </form>

                            <a href="/students/<?= $call['student_id'] ?>" class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold ml-1 text-xs">
                                Student Dossier &rarr;
                            </a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
