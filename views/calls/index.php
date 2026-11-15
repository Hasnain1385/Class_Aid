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

            <!-- Print Audit for Selected Class -->
            <?php if (!empty($filters['class_id'])): ?>
                <a href="/calls/print/class/<?= $filters['class_id'] ?>?<?= http_build_query($filters) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-all">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Print Class Audit</span>
                </a>
            <?php endif; ?>

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
                <select name="class_id" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($filters['class_id'] == $c['id']) ? 'selected' : '' ?>>
                            <?= $e($c['name']) ?> - <?= $e($c['section']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Category</label>
                <select name="category" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $catKey => $catData): ?>
                        <option value="<?= $catKey ?>" <?= (($filters['category'] ?? '') === $catKey) ? 'selected' : '' ?>>
                            <?= $e(is_array($catData) ? ($catData['label'] ?? $catKey) : $catData) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">From Date</label>
                <input type="date" name="date_from" value="<?= $e($filters['date_from'] ?? '') ?>" class="w-full px-3 py-2 text-xs rounded-xl glass-input tabular-nums">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">To Date</label>
                <input type="date" name="date_to" value="<?= $e($filters['date_to'] ?? '') ?>" class="w-full px-3 py-2 text-xs rounded-xl glass-input tabular-nums">
            </div>

            <!-- Action buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold transition-all">
                    Filter Logs
                </button>
                <a href="/calls" class="py-2 px-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold transition-all border border-slate-200 dark:border-slate-700">
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
        <div class="space-y-3.5">
            <?php foreach ($calls as $call): ?>
                <div class="glass-card p-4 sm:p-5 hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                    
                    <!-- Top header row -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2 flex-wrap">
                            <?= \App\Models\ParentCallLog::renderPill($call['call_category']) ?>
                            <a href="/students/<?= $call['student_id'] ?>" class="text-sm font-bold text-slate-900 dark:text-white hover:underline">
                                <?= $e($call['first_name'] . ' ' . $call['last_name']) ?>
                            </a>
                            <span class="font-mono text-xs text-indigo-500 font-semibold tabular-nums">[<?= $e($call['student_admin_id']) ?>]</span>
                            <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700/60">
                                <?= $e($call['class_name']) ?> - <?= $e($call['class_section']) ?>
                            </span>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-slate-400 tabular-nums">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            <span><?= date('M d, Y • h:i A', strtotime($call['call_timestamp'])) ?></span>
                        </div>
                    </div>

                    <!-- Contact Person & Phone -->
                    <div class="mb-3 text-xs flex flex-wrap items-center gap-x-4 gap-y-1 text-slate-600 dark:text-slate-300">
                        <div>
                            <span class="text-slate-400">Contacted:</span>
                            <strong class="text-slate-900 dark:text-white"><?= $e($call['contact_person_name']) ?></strong> (<?= $e($call['relationship']) ?>)
                        </div>
                        <div>
                            <span class="text-slate-400">Dialed Number:</span>
                            <span class="font-mono tabular-nums font-semibold"><?= $e($call['phone_dialed']) ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400">Interaction:</span>
                            <span class="font-medium"><?= $e($call['call_type']) ?></span>
                        </div>
                    </div>

                    <!-- Discussion & Parent Response Split -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 text-xs">
                        <div class="bg-slate-50 dark:bg-slate-950/60 p-3.5 rounded-xl border border-slate-200/60 dark:border-slate-800">
                            <span class="text-slate-400 block text-xs font-bold uppercase tracking-wider mb-1">
                                Discussion Notes & Reason:
                            </span>
                            <p class="text-slate-800 dark:text-slate-200 leading-relaxed whitespace-pre-line">
                                <?= $e($call['discussion_summary']) ?>
                            </p>
                        </div>

                        <div class="bg-indigo-50/30 dark:bg-indigo-950/20 p-3.5 rounded-xl border border-indigo-200/60 dark:border-indigo-900/50">
                            <span class="text-indigo-600 dark:text-indigo-400 block text-xs font-bold uppercase tracking-wider mb-1">
                                Parent Response & Commitment:
                            </span>
                            <p class="text-slate-800 dark:text-slate-200 italic font-medium leading-relaxed whitespace-pre-line">
                                "<?= $e($call['parent_response']) ?>"
                            </p>
                        </div>
                    </div>

                    <!-- Footer actions: WhatsApp, Follow-up status -->
                    <div class="mt-3.5 pt-3 flex flex-wrap items-center justify-between gap-3 text-xs border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <button type="button" 
                                    onclick="openWhatsApp('<?= $e($call['phone_dialed']) ?>', '<?= $e($call['first_name'] . ' ' . $call['last_name']) ?>', '<?= $e($call['student_admin_id']) ?>')"
                                    class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                <span>WhatsApp Parent</span>
                            </button>

                            <?php if ($call['follow_up_required']): ?>
                                <span class="text-amber-500 flex items-center gap-1 font-semibold">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    <span>Follow-up: <?= !empty($call['follow_up_date']) ? date('M d, Y', strtotime($call['follow_up_date'])) : 'Pending' ?></span>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="POST" action="/calls/toggle-status/<?= $call['id'] ?>">
                                <?= $csrf() ?>
                                <input type="hidden" name="redirect" value="/calls?<?= http_build_query($filters) ?>">
                                <button type="submit" class="px-2.5 py-1 rounded-lg border text-xs font-semibold transition-colors cursor-pointer <?= $call['status'] === 'Resolved' ? 'badge-emerald' : 'badge-amber' ?>">
                                    Status: <?= $e($call['status']) ?> (Click to switch)
                                </button>
                            </form>

                            <a href="/students/<?= $call['student_id'] ?>" class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold ml-2">
                                Student Dossier &rarr;
                            </a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
