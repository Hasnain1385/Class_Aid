<div class="space-y-6">

    <!-- Top Header & Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="/attendance" class="text-xs text-slate-400 hover:text-indigo-500 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Attendance</span>
                </a>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs font-semibold text-indigo-500 uppercase"><?= $e($class['name']) ?> - <?= $e($class['section']) ?></span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1 flex items-center gap-2.5">
                <span>Roll Call Register</span>
                <?php if ($isSaved): ?>
                    <span class="text-xs px-2.5 py-0.5 rounded-full badge-emerald font-bold">
                        Saved
                    </span>
                <?php else: ?>
                    <span class="text-xs px-2.5 py-0.5 rounded-full badge-amber font-bold">
                        Unsaved Changes
                    </span>
                <?php endif; ?>
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Date change form -->
            <form method="GET" action="/attendance/sheet/<?= $class['id'] ?>" class="flex items-center gap-1.5">
                <input type="date" 
                       name="date" 
                       value="<?= $e($date) ?>" 
                       onchange="this.form.submit()" 
                       class="px-3 py-2 text-xs rounded-xl glass-input font-medium tabular-nums">
            </form>

            <!-- 1-Tap Mark All Present Button -->
            <button type="button" 
                    id="mark-all-present-btn" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold badge-emerald hover:opacity-90 transition-all cursor-pointer">
                <i data-lucide="check-check" class="w-4 h-4"></i>
                <span>Mark All Present</span>
            </button>
        </div>
    </div>

    <!-- Attendance Form -->
    <form method="POST" action="/attendance/sheet/<?= $class['id'] ?>" class="space-y-4">
        <?= $csrf() ?>
        <input type="hidden" name="date" value="<?= $e($date) ?>">

        <div class="space-y-2.5">
            <?php foreach ($students as $stu): ?>
                <?php 
                    $currentStatus = $stu['current_status'] ?? 'P';
                    $consecutive = (int) ($stu['consecutive_absences'] ?? 0);
                ?>
                <div class="glass-card p-3.5 sm:p-4 attendance-student-row flex flex-col lg:flex-row lg:items-center justify-between gap-3 <?= $consecutive >= 3 ? 'border-rose-500/50 bg-rose-50/30 dark:bg-rose-950/20' : '' ?>">
                    
                    <!-- Hidden Status Input -->
                    <input type="hidden" 
                           name="attendance[<?= $stu['id'] ?>][status]" 
                           value="<?= $currentStatus ?>" 
                           class="attendance-status-input">

                    <!-- Student Info & Chronic Absence Flag -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-sm text-slate-900 dark:text-white">
                                <?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>
                            </span>
                            <span class="font-mono text-xs font-bold text-indigo-500 tabular-nums">
                                <?= $e($stu['student_admin_id']) ?>
                            </span>

                            <!-- 3+ Consecutive Absences Flag -->
                            <?php if ($consecutive >= 3): ?>
                                <span class="text-xs px-2 py-0.5 rounded-full badge-rose font-bold flex items-center gap-1 animate-pulse">
                                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                                    <span><?= $consecutive ?> Consecutive Absences!</span>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Guardian & Quick WhatsApp / Call actions -->
                        <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-1 flex-wrap">
                            <span>Guardian: <strong class="text-slate-700 dark:text-slate-300"><?= $e($stu['primary_guardian_name']) ?></strong></span>
                            <span>•</span>
                            <span class="font-mono tabular-nums"><?= $e($stu['primary_phone']) ?></span>
                            <button type="button" 
                                    onclick="openWhatsApp('<?= $e($stu['primary_phone']) ?>', '<?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>', '<?= $e($stu['student_admin_id']) ?>')"
                                    class="text-emerald-500 hover:text-emerald-600 inline-flex items-center gap-1 font-semibold">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <span>WhatsApp</span>
                            </button>
                            <?php if ($consecutive >= 3): ?>
                                <a href="/calls/create?student_id=<?= $stu['id'] ?>&category=Absenteeism" 
                                   class="text-rose-500 hover:underline font-semibold flex items-center gap-1">
                                    <i data-lucide="phone-outgoing" class="w-3.5 h-3.5"></i>
                                    <span>Log Call</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 1-Tap Toggle Buttons & Remarks -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-shrink-0">
                        
                        <!-- 4 Status Toggle Buttons -->
                        <div class="grid grid-cols-4 gap-1.5 sm:w-80">
                            
                            <!-- Present [P] -->
                            <button type="button" 
                                    data-status="P" 
                                    class="att-toggle-btn py-1.5 px-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 <?= $currentStatus === 'P' ? 'bg-emerald-600 text-white ring-2 ring-emerald-400 ring-offset-1 dark:ring-offset-slate-900 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-emerald-700 dark:text-emerald-400 border border-slate-200 dark:border-slate-700' ?>">
                                <span class="font-extrabold text-xs">P</span>
                                <span class="text-xs font-medium">Present</span>
                            </button>

                            <!-- Absent [A] -->
                            <button type="button" 
                                    data-status="A" 
                                    class="att-toggle-btn py-1.5 px-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 <?= $currentStatus === 'A' ? 'bg-rose-600 text-white ring-2 ring-rose-400 ring-offset-1 dark:ring-offset-slate-900 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-rose-700 dark:text-rose-400 border border-slate-200 dark:border-slate-700' ?>">
                                <span class="font-extrabold text-xs">A</span>
                                <span class="text-xs font-medium">Absent</span>
                            </button>

                            <!-- Late [L] -->
                            <button type="button" 
                                    data-status="L" 
                                    class="att-toggle-btn py-1.5 px-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 <?= $currentStatus === 'L' ? 'bg-amber-600 text-white ring-2 ring-amber-400 ring-offset-1 dark:ring-offset-slate-900 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-amber-700 dark:text-amber-400 border border-slate-200 dark:border-slate-700' ?>">
                                <span class="font-extrabold text-xs">L</span>
                                <span class="text-xs font-medium">Late</span>
                            </button>

                            <!-- Excused [E] -->
                            <button type="button" 
                                    data-status="E" 
                                    class="att-toggle-btn py-1.5 px-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 <?= $currentStatus === 'E' ? 'bg-blue-600 text-white ring-2 ring-blue-400 ring-offset-1 dark:ring-offset-slate-900 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-blue-700 dark:text-blue-400 border border-slate-200 dark:border-slate-700' ?>">
                                <span class="font-extrabold text-xs">E</span>
                                <span class="text-xs font-medium">Excused</span>
                            </button>

                        </div>

                        <!-- Remarks Input -->
                        <input type="text" 
                               name="attendance[<?= $stu['id'] ?>][remarks]" 
                               value="<?= $e($stu['current_remarks'] ?? '') ?>" 
                               placeholder="Remarks (optional)" 
                               class="w-full sm:w-40 px-3 py-1.5 text-xs rounded-lg glass-input">

                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sticky Save Action Bar -->
        <div class="sticky bottom-16 md:bottom-4 z-30 p-4 rounded-2xl bg-white/95 dark:bg-slate-900/95 border border-slate-200 dark:border-slate-800 shadow-xl flex items-center justify-between backdrop-blur-md">
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Recording for <strong class="text-slate-800 dark:text-slate-200"><?= count($students) ?> Students</strong> on <strong class="text-slate-800 dark:text-slate-200"><?= date('M d, Y', strtotime($date)) ?></strong>
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm flex items-center gap-2 transition-all">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Save Roll Call Sheet</span>
            </button>
        </div>

    </form>

</div>
