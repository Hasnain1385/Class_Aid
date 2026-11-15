<div class="space-y-6">

    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="calendar-check" class="w-6 h-6 text-indigo-500"></i>
                <span>Daily Attendance Register</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Fast 1-tap roll call, chronic absenteeism alerts, and monthly attendance tracking.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="/attendance/reports" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-all cursor-pointer">
                <i data-lucide="bar-chart-2" class="w-4 h-4 text-indigo-500"></i>
                <span>Reports &amp; Analytics Dashboard</span>
            </a>

            <form method="GET" action="/attendance" class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Date:</label>
                <input type="date" 
                       name="date" 
                       value="<?= $e($selectedDate) ?>" 
                       onchange="this.form.submit()" 
                       class="px-3 py-2 text-xs rounded-xl glass-input font-medium tabular-nums">
            </form>
        </div>
    </div>

    <!-- Classes Cards Grid for Roll Call -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($classes as $c): ?>
            <div class="glass-card p-5 space-y-4 <?= $c['is_home_class'] ? 'border-2 border-indigo-500/50 bg-indigo-50/20 dark:bg-indigo-950/20' : '' ?>">
                
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                <?= $e($c['name']) ?> - <?= $e($c['section']) ?>
                            </h2>
                            <?php if ($c['is_home_class']): ?>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 font-bold uppercase border border-indigo-500/30">Home</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 tabular-nums">
                            <?= $c['student_count'] ?> Students enrolled • AY <?= $e($c['academic_year']) ?>
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex-shrink-0">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        Date: <strong class="text-slate-800 dark:text-slate-200"><?= date('M d, Y', strtotime($selectedDate)) ?></strong>
                    </span>
                    <div class="flex items-center gap-1.5">
                        <a href="/attendance/print/sheet/<?= $c['id'] ?>?date=<?= $selectedDate ?>" target="_blank" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" title="Print Attendance Table">
                            <i data-lucide="printer" class="w-4 h-4 text-indigo-500"></i>
                        </a>
                        <a href="/attendance/sheet/<?= $c['id'] ?>?date=<?= $selectedDate ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Open Roll Call</span>
                        </a>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

</div>
