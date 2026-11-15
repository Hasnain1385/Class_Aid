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

    <!-- Classes Cards Grid for Roll Call & Historical Records -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($classes as $c): ?>
            <?php
                $isRecorded = !empty($c['attendance_recorded']);
            ?>
            <div class="glass-card p-5 space-y-4 flex flex-col justify-between <?= $c['is_home_class'] ? 'border-2 border-indigo-500/50 bg-indigo-50/20 dark:bg-indigo-950/20' : '' ?>">

                <!-- Clickable Card Header: Takes user directly to historical records & reports -->
                <a href="/attendance/reports?class_id=<?= $c['id'] ?>" class="block group transition-transform" title="Click to view full attendance records &amp; analytics">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors flex items-center gap-1.5">
                                    <span><?= $e($c['name']) ?> - <?= $e($c['section']) ?></span>
                                    <i data-lucide="chevron-right" class="w-4 h-4 opacity-0 group-hover:opacity-100 -translate-x-1 group-hover:translate-x-0 transition-all text-indigo-500"></i>
                                </h2>
                                <?php if ($c['is_home_class']): ?>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 font-bold uppercase border border-indigo-500/30">Home</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 tabular-nums">
                                <?= $c['student_count'] ?> Students enrolled • AY <?= $e($c['academic_year']) ?>
                            </p>
                        </div>
                        <div class="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex-shrink-0 group-hover:bg-indigo-500/20 transition-colors">
                            <i data-lucide="calendar-check" class="w-5 h-5"></i>
                        </div>
                    </div>
                </a>

                <!-- Attendance Status for Selected Date -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between text-xs mb-3">
                        <span class="text-slate-500 dark:text-slate-400">
                            <?= date('M d, Y', strtotime($selectedDate)) ?>:
                        </span>
                        <?php if ($isRecorded): ?>
                            <span class="text-[11px] px-2 py-0.5 rounded-full badge-emerald font-bold flex items-center gap-1">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                <span>Recorded (<?= $c['recorded_count'] ?> Marked)</span>
                            </span>
                        <?php else: ?>
                            <span class="text-[11px] px-2 py-0.5 rounded-full badge-amber font-bold flex items-center gap-1">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                <span>Roll Call Pending</span>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Action Toolbar -->
                    <div class="flex items-center justify-between gap-1.5 flex-wrap pt-1">
                        <!-- History & Reports Button -->
                        <a href="/attendance/reports?class_id=<?= $c['id'] ?>" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors shadow-sm" title="View historical records, custom date ranges, and analytics">
                            <i data-lucide="bar-chart-2" class="w-3.5 h-3.5 text-indigo-500"></i>
                            <span>History &amp; Reports</span>
                        </a>

                        <div class="flex items-center gap-1">
                            <!-- Print Daily Sheet -->
                            <a href="/attendance/print/sheet/<?= $c['id'] ?>?date=<?= $selectedDate ?>" target="_blank" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" title="Print Today's Attendance Sheet">
                                <i data-lucide="printer" class="w-4 h-4 text-indigo-500"></i>
                            </a>

                            <!-- Print Custom Range Report -->
                            <a href="/attendance/print/report?class_id=<?= $c['id'] ?>" target="_blank" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" title="Print Date Range Audit Report">
                                <i data-lucide="file-text" class="w-4 h-4 text-emerald-500"></i>
                            </a>

                            <!-- Primary Roll Call Button (Edit if saved, Take if pending) -->
                            <a href="/attendance/sheet/<?= $c['id'] ?>?date=<?= $selectedDate ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white shadow-sm transition-all <?= $isRecorded ? 'bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600' : 'bg-indigo-600 hover:bg-indigo-500 shadow-indigo-600/25' ?>">
                                <i data-lucide="<?= $isRecorded ? 'pencil' : 'calendar-check' ?>" class="w-3.5 h-3.5"></i>
                                <span><?= $isRecorded ? 'Edit Roll Call' : 'Take Roll Call' ?></span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

</div>
