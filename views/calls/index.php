<div class="space-y-6">

    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="phone-call" class="w-6 h-6 text-emerald-500"></i>
                <span>Parent Communication Journal</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Centralized registry of parent phone calls, discussions, parent commitments, and follow-ups.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- CSV Export button -->
            <a href="/calls/export/csv?<?= http_build_query($filters) ?>" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold glass-card hover:border-emerald-500/40 text-emerald-600 dark:text-emerald-400 transition-all">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Export CSV</span>
            </a>

            <!-- Print Audit for Selected Class -->
            <?php if (!empty($filters['class_id'])): ?>
                <a href="/calls/print/class/<?= $filters['class_id'] ?>?<?= http_build_query($filters) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold glass-card hover:border-indigo-500/40 text-gray-700 dark:text-gray-300 transition-all">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Print Class Audit</span>
                </a>
            <?php endif; ?>

            <!-- Log Call Button -->
            <a href="/calls/create" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/30 transition-all">
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
                <label class="block text-[11px] font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Class</label>
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
                <label class="block text-[11px] font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Category</label>
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
                <label class="block text-[11px] font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">From Date</label>
                <input type="date" name="date_from" value="<?= $e($filters['date_from'] ?? '') ?>" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-[11px] font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">To Date</label>
                <input type="date" name="date_to" value="<?= $e($filters['date_to'] ?? '') ?>" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
            </div>

            <!-- Action buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold transition-all">
                    Filter Logs
                </button>
                <a href="/calls" class="py-2 px-3 bg-gray-200 dark:bg-white/10 hover:bg-gray-300 dark:hover:bg-white/20 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-medium transition-all">
                    Reset
                </a>
            </div>

        </form>
    </div>

    <!-- Communication Cards List -->
    <?php if (empty($calls)): ?>
        <div class="glass-card p-12 text-center text-gray-500 dark:text-gray-400">
            <i data-lucide="phone-missed" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">No communication records found</h3>
            <p class="text-xs mt-1">No call logs match the selected filter criteria.</p>
        </div>
    <?php else: ?>
        <div class="space-y-3.5">
            <?php foreach ($calls as $call): ?>
                <div class="glass-card p-4 sm:p-5 hover:border-indigo-500/40 transition-all">
                    
                    <!-- Top header row -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 mb-3 border-b border-gray-100 dark:border-white/5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-extrabold px-2.5 py-0.5 rounded-full border <?= \App\Models\ParentCallLog::getCategoryBadgeClass($call['call_category']) ?>">
                                <?= $e($call['call_category']) ?>
                            </span>
                            <a href="/students/<?= $call['student_id'] ?>" class="text-sm font-bold text-gray-900 dark:text-white hover:underline">
                                <?= $e($call['first_name'] . ' ' . $call['last_name']) ?>
                            </a>
                            <span class="font-mono text-xs text-indigo-500 font-semibold">[<?= $e($call['student_admin_id']) ?>]</span>
                            <span class="text-xs px-2 py-0.5 rounded-md bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300">
                                <?= $e($call['class_name']) ?> - <?= $e($call['class_section']) ?>
                            </span>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            <span><?= date('M d, Y • h:i A', strtotime($call['call_timestamp'])) ?></span>
                        </div>
                    </div>

                    <!-- Contact Person & Phone -->
                    <div class="mb-3 text-xs flex flex-wrap items-center gap-x-4 gap-y-1 text-gray-600 dark:text-gray-300">
                        <div>
                            <span class="text-gray-400">Contacted:</span>
                            <strong class="text-gray-900 dark:text-white"><?= $e($call['contact_person_name']) ?></strong> (<?= $e($call['relationship']) ?>)
                        </div>
                        <div>
                            <span class="text-gray-400">Dialed Number:</span>
                            <span class="font-mono"><?= $e($call['phone_dialed']) ?></span>
                        </div>
                        <div>
                            <span class="text-gray-400">Interaction:</span>
                            <span><?= $e($call['call_type']) ?></span>
                        </div>
                    </div>

                    <!-- Discussion & Parent Response Split -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 text-xs">
                        <div class="bg-gray-50/70 dark:bg-white/5 p-3.5 rounded-xl border border-gray-100 dark:border-white/5">
                            <span class="text-gray-400 block text-[10px] font-bold uppercase tracking-wider mb-1">
                                Discussion Notes & Reason:
                            </span>
                            <p class="text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-line">
                                <?= $e($call['discussion_summary']) ?>
                            </p>
                        </div>

                        <div class="bg-indigo-50/20 dark:bg-indigo-950/20 p-3.5 rounded-xl border border-indigo-500/15">
                            <span class="text-indigo-500 dark:text-indigo-400 block text-[10px] font-bold uppercase tracking-wider mb-1">
                                Parent Response & Commitment:
                            </span>
                            <p class="text-gray-800 dark:text-gray-200 italic font-medium leading-relaxed whitespace-pre-line">
                                "<?= $e($call['parent_response']) ?>"
                            </p>
                        </div>
                    </div>

                    <!-- Footer actions: WhatsApp, Follow-up status -->
                    <div class="mt-3.5 pt-2.5 flex flex-wrap items-center justify-between gap-3 text-xs border-t border-gray-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <button type="button" 
                                    onclick="openWhatsApp('<?= $e($call['phone_dialed']) ?>', '<?= $e($call['first_name'] . ' ' . $call['last_name']) ?>', '<?= $e($call['student_admin_id']) ?>')"
                                    class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                <span>WhatsApp Parent</span>
                            </button>

                            <?php if ($call['follow_up_required']): ?>
                                <span class="text-amber-500 flex items-center gap-1 font-medium">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    <span>Follow-up: <?= !empty($call['follow_up_date']) ? date('M d, Y', strtotime($call['follow_up_date'])) : 'Pending' ?></span>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="POST" action="/calls/toggle-status/<?= $call['id'] ?>">
                                <?= $csrf() ?>
                                <input type="hidden" name="redirect" value="/calls?<?= http_build_query($filters) ?>">
                                <button type="submit" class="px-2.5 py-1 rounded-lg border text-[11px] font-medium transition-colors <?= $call['status'] === 'Resolved' ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/30' : 'bg-amber-500/10 text-amber-600 border-amber-500/30 hover:bg-amber-500/20' ?>">
                                    Status: <?= $e($call['status']) ?> (Click to switch)
                                </button>
                            </form>

                            <a href="/students/<?= $call['student_id'] ?>" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                Student Dossier &rarr;
                            </a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
