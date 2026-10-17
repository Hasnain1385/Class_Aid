<div class="space-y-6">

    <!-- Top Header & Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="/attendance" class="text-xs text-gray-400 hover:text-indigo-500 flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Attendance</span>
                </a>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs font-semibold text-indigo-500 uppercase"><?= $e($class['name']) ?> - <?= $e($class['section']) ?></span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mt-1 flex items-center gap-2">
                <span>Roll Call Register</span>
                <?php if ($isSaved): ?>
                    <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 font-bold">
                        Saved
                    </span>
                <?php else: ?>
                    <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 font-bold">
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
                       class="px-3 py-2 text-xs rounded-xl glass-input font-medium">
            </form>

            <!-- 1-Tap Mark All Present Button -->
            <button type="button" 
                    id="mark-all-present-btn" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/30 transition-all">
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
                <div class="glass-card p-3.5 sm:p-4 attendance-student-row flex flex-col sm:flex-row sm:items-center justify-between gap-3 <?= $consecutive >= 3 ? 'border-rose-500/40 bg-rose-500/5' : '' ?>">
                    
                    <!-- Hidden Status Input -->
                    <input type="hidden" 
                           name="attendance[<?= $stu['id'] ?>][status]" 
                           value="<?= $currentStatus ?>" 
                           class="attendance-status-input">

                    <!-- Student Info & Chronic Absence Flag -->
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-sm text-gray-900 dark:text-white">
                                <?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>
                            </span>
                            <span class="font-mono text-xs font-bold text-indigo-500">
                                <?= $e($stu['student_admin_id']) ?>
                            </span>

                            <!-- 3+ Consecutive Absences Flag (User Core Requirement) -->
                            <?php if ($consecutive >= 3): ?>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-600 dark:text-rose-400 font-extrabold flex items-center gap-1 animate-pulse">
                                    <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                    <span><?= $consecutive ?> Consecutive Absences!</span>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Guardian & Quick WhatsApp / Call actions -->
                        <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <span>Guardian: <?= $e($stu['primary_guardian_name']) ?></span>
                            <span>•</span>
                            <span class="font-mono"><?= $e($stu['primary_phone']) ?></span>
                            <button type="button" 
                                    onclick="openWhatsApp('<?= $e($stu['primary_phone']) ?>', '<?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>', '<?= $e($stu['student_admin_id']) ?>')"
                                    class="text-emerald-500 hover:text-emerald-600 inline-flex items-center gap-1">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <span class="hidden sm:inline">WhatsApp</span>
                            </button>
                            <?php if ($consecutive >= 3): ?>
                                <a href="/calls/create?student_id=<?= $stu['id'] ?>&category=Absenteeism" 
                                   class="text-rose-500 hover:underline font-semibold flex items-center gap-1">
                                    <i data-lucide="phone-outgoing" class="w-3 h-3"></i>
                                    <span>Log Call</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 1-Tap Toggle Buttons & Remarks -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        
                        <!-- 4 Status Toggle Buttons -->
                        <div class="grid grid-cols-4 gap-1 sm:w-64">
                            
                            <!-- Present [P] -->
                            <button type="button" 
                                    data-status="P" 
                                    class="att-toggle-btn py-2 text-xs font-bold rounded-lg transition-all <?= $currentStatus === 'P' ? 'bg-emerald-600 text-white ring-2 ring-offset-2 ring-emerald-500 opacity-100' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 opacity-40 hover:opacity-80' ?>">
                                P (Present)
                            </button>

                            <!-- Absent [A] -->
                            <button type="button" 
                                    data-status="A" 
                                    class="att-toggle-btn py-2 text-xs font-bold rounded-lg transition-all <?= $currentStatus === 'A' ? 'bg-rose-600 text-white ring-2 ring-offset-2 ring-rose-500 opacity-100' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 opacity-40 hover:opacity-80' ?>">
                                A (Absent)
                            </button>

                            <!-- Late [L] -->
                            <button type="button" 
                                    data-status="L" 
                                    class="att-toggle-btn py-2 text-xs font-bold rounded-lg transition-all <?= $currentStatus === 'L' ? 'bg-amber-600 text-white ring-2 ring-offset-2 ring-amber-500 opacity-100' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 opacity-40 hover:opacity-80' ?>">
                                L (Late)
                            </button>

                            <!-- Excused [E] -->
                            <button type="button" 
                                    data-status="E" 
                                    class="att-toggle-btn py-2 text-xs font-bold rounded-lg transition-all <?= $currentStatus === 'E' ? 'bg-blue-600 text-white ring-2 ring-offset-2 ring-blue-500 opacity-100' : 'bg-blue-500/10 text-blue-600 dark:text-blue-400 opacity-40 hover:opacity-80' ?>">
                                E (Excused)
                            </button>

                        </div>

                        <!-- Remarks Input -->
                        <input type="text" 
                               name="attendance[<?= $stu['id'] ?>][remarks]" 
                               value="<?= $e($stu['current_remarks'] ?? '') ?>" 
                               placeholder="Remarks (optional)" 
                               class="w-full sm:w-36 px-2.5 py-1.5 text-xs rounded-lg glass-input">

                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sticky Save Action Bar -->
        <div class="sticky bottom-16 md:bottom-4 z-30 p-4 rounded-2xl glass-card backdrop-blur-2xl shadow-2xl flex items-center justify-between">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Recording for <strong><?= count($students) ?> Students</strong> on <strong><?= date('M d, Y', strtotime($date)) ?></strong>
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Save Roll Call Sheet</span>
            </button>
        </div>

    </form>

</div>
