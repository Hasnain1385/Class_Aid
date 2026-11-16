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
                <div class="glass-input-group !w-44" onclick="this.querySelector('input')?.focus()">
                    <div class="input-icon-box">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                    <input type="date"
                           name="date"
                           value="<?= $e($selectedDate) ?>"
                           onchange="this.form.submit()"
                           class="font-medium tabular-nums">
                </div>
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
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2.5">
                    <?php 
                        $sStat = $c['session_status'] ?? [];
                        $isFri = !empty($sStat['is_friday']);
                    ?>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">
                            <?= date('D, M d, Y', strtotime($selectedDate)) ?>
                        </span>
                        <?php if ($isFri): ?>
                            <span class="text-[10px] px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 font-semibold border border-indigo-200 dark:border-indigo-800/50">
                                Friday (Morning Only)
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Dual Session Status Chips -->
                    <div class="grid <?= $isFri ? 'grid-cols-1' : 'grid-cols-2' ?> gap-1.5 text-xs">
                        <!-- Morning Session -->
                        <div class="flex items-center justify-between p-2 rounded-xl border <?= !empty($sStat['morning_recorded']) ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' ?>">
                            <span class="flex items-center gap-1 font-semibold text-[11px]">
                                <span>🌅</span>
                                <span>Morning</span>
                            </span>
                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded font-mono <?= !empty($sStat['morning_recorded']) ? 'bg-emerald-200/60 dark:bg-emerald-800/40 text-emerald-900 dark:text-emerald-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' ?>">
                                <?= !empty($sStat['morning_recorded']) ? 'Recorded (' . $sStat['morning_count'] . ')' : 'Pending' ?>
                            </span>
                        </div>

                        <!-- Midday Session (Not shown on Fridays) -->
                        <?php if (!$isFri): ?>
                            <div class="flex items-center justify-between p-2 rounded-xl border <?= !empty($sStat['midday_recorded']) ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' ?>">
                                <span class="flex items-center gap-1 font-semibold text-[11px]">
                                    <span>☀️</span>
                                    <span>Midday</span>
                                </span>
                                <span class="text-[10px] font-bold px-1.5 py-0.2 rounded font-mono <?= !empty($sStat['midday_recorded']) ? 'bg-emerald-200/60 dark:bg-emerald-800/40 text-emerald-900 dark:text-emerald-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' ?>">
                                    <?= !empty($sStat['midday_recorded']) ? 'Recorded (' . $sStat['midday_count'] . ')' : 'Pending' ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Toolbar -->
                    <div class="flex items-center justify-between gap-1.5 flex-wrap pt-1">
                        <!-- History & Reports Button -->
                        <a href="/attendance/reports?class_id=<?= $c['id'] ?>" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors shadow-sm" title="View historical records, custom date ranges, and analytics">
                            <i data-lucide="bar-chart-2" class="w-3.5 h-3.5 text-indigo-500"></i>
                            <span>History &amp; Reports</span>
                        </a>

                        <div class="flex items-center gap-1 flex-wrap">
                            <!-- Morning Roll Call Button -->
                            <a href="/attendance/sheet/<?= $c['id'] ?>?date=<?= $selectedDate ?>&session=morning" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold text-white shadow-sm transition-all <?= !empty($sStat['morning_recorded']) ? 'bg-slate-700 hover:bg-slate-600' : 'bg-indigo-600 hover:bg-indigo-500' ?>" title="Take or Edit Morning Roll Call">
                                <span>🌅 Morning</span>
                            </a>

                            <!-- Midday Roll Call Button (Excluded on Friday) -->
                            <?php if (!$isFri): ?>
                                <a href="/attendance/sheet/<?= $c['id'] ?>?date=<?= $selectedDate ?>&session=midday" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold text-white shadow-sm transition-all <?= !empty($sStat['midday_recorded']) ? 'bg-slate-700 hover:bg-slate-600' : 'bg-amber-600 hover:bg-amber-500' ?>" title="Take or Edit Midday Roll Call">
                                    <span>☀️ Midday</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

</div>
