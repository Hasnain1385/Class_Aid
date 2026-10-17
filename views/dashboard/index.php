<div class="space-y-6">

    <!-- Welcome Hero & Mobile Quick Action Cockpit -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                <span>Teacher Cockpit</span>
                <?php if ($homeClass): ?>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 font-medium">
                        Home Class: <?= $e($homeClass['name']) ?> - <?= $e($homeClass['section']) ?>
                    </span>
                <?php endif; ?>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Overview of your 5 classes, attendance alerts, and parental communications.
            </p>
        </div>

        <!-- Quick Action Floating/Header Bar -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
            <?php if ($homeClass): ?>
                <a href="/attendance/sheet/<?= $homeClass['id'] ?>" class="flex-shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/30 transition-all">
                    <i data-lucide="check-square" class="w-4 h-4"></i>
                    <span>Take Today's Roll Call</span>
                </a>
            <?php endif; ?>
            <a href="/calls/create" class="flex-shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-800 dark:text-gray-200 glass-card glass-card-hover border border-indigo-500/30 transition-all">
                <i data-lucide="phone-outgoing" class="w-4 h-4 text-emerald-500"></i>
                <span>Log Parent Call</span>
            </a>
            <a href="/students/create" class="flex-shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-800 dark:text-gray-200 glass-card glass-card-hover border border-gray-200/50 dark:border-white/10 transition-all">
                <i data-lucide="user-plus" class="w-4 h-4 text-violet-500"></i>
                <span>Add Student</span>
            </a>
        </div>
    </div>

    <!-- Chronic Absenteeism Priority Alert (Requested in User PRD) -->
    <?php if (!empty($flaggedStudents)): ?>
        <div class="p-4 sm:p-5 rounded-2xl bg-rose-500/10 border border-rose-500/30 backdrop-blur-md">
            <div class="flex items-start sm:items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2.5 text-rose-600 dark:text-rose-400 font-bold text-sm">
                    <div class="p-1.5 rounded-lg bg-rose-500/20">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <span>Chronic Absenteeism Alert (<?= count($flaggedStudents) ?> Student<?= count($flaggedStudents) > 1 ? 's' : '' ?> with 3+ Consecutive Absences)</span>
                </div>
                <span class="text-[11px] px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-700 dark:text-rose-300 font-bold uppercase tracking-wider">Immediate Follow-up</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <?php foreach ($flaggedStudents as $stu): ?>
                    <div class="p-3 rounded-xl bg-white/70 dark:bg-slate-900/70 border border-rose-500/20 flex items-center justify-between shadow-sm">
                        <div>
                            <div class="text-xs font-bold text-gray-900 dark:text-white">
                                <?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>
                            </div>
                            <div class="text-[11px] text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1">
                                <span class="font-mono"><?= $e($stu['student_admin_id']) ?></span>
                                <span>•</span>
                                <span><?= $stu['consecutive_absences'] ?> Days Absent</span>
                            </div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                                Parent: <?= $e($stu['primary_guardian_name']) ?> (<?= $e($stu['primary_phone']) ?>)
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" 
                                    onclick="openWhatsApp('<?= $e($stu['primary_phone']) ?>', '<?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>', '<?= $e($stu['student_admin_id']) ?>')"
                                    class="p-2 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 transition-colors" 
                                    title="WhatsApp Parent">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                            </button>
                            <a href="/calls/create?student_id=<?= $stu['id'] ?>&category=Absenteeism" 
                               class="p-2 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 transition-colors" 
                               title="Log Call Now">
                                <i data-lucide="phone-call" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- 4 Key Glassmorphic Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        
        <!-- Total Students -->
        <div class="glass-card p-4 sm:p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Enrolled</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white mt-1"><?= $totalStudents ?></h3>
                <p class="text-[10px] text-indigo-500 dark:text-indigo-400 mt-1 font-medium">Across <?= count($classes) ?> Classes</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Today's Attendance -->
        <div class="glass-card p-4 sm:p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Today's Attendance</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">
                    <?= $todayAttendance['rate'] ?>%
                </h3>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">
                    <?= $todayAttendance['present'] ?> Present • <?= $todayAttendance['absent'] ?> Absent
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i data-lucide="calendar-check" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Pending Call Follow-ups -->
        <div class="glass-card p-4 sm:p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Call Follow-ups</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-amber-500 mt-1"><?= count($pendingFollowups) ?></h3>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Pending parent actions</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
                <i data-lucide="phone-missed" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Managed Classes -->
        <div class="glass-card p-4 sm:p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Managed Rosters</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-violet-600 dark:text-violet-400 mt-1"><?= count($classes) ?></h3>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">1 Home + 4 Subject</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-violet-500/10 text-violet-600 dark:text-violet-400 flex items-center justify-center">
                <i data-lucide="school" class="w-6 h-6"></i>
            </div>
        </div>

    </div>

    <!-- Main 2-Column Split: Classes Overview & Recent Parent Interactions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1: Classes & Quick Roll Call (1 col on desktop) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-2">
                    <i data-lucide="book-open" class="w-4 h-4 text-indigo-500"></i>
                    <span>Your Class Rosters</span>
                </h2>
                <a href="/classes" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Manage</a>
            </div>

            <div class="space-y-3">
                <?php foreach ($classes as $c): ?>
                    <div class="glass-card p-4 flex items-center justify-between <?= $c['is_home_class'] ? 'border-l-4 border-indigo-600 dark:border-indigo-500' : '' ?>">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-gray-900 dark:text-white"><?= $e($c['name']) ?> - <?= $e($c['section']) ?></span>
                                <?php if ($c['is_home_class']): ?>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 font-bold uppercase">Home</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                <?= $c['student_count'] ?> Students enrolled • AY <?= $e($c['academic_year']) ?>
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a href="/attendance/sheet/<?= $c['id'] ?>" class="p-2 rounded-lg bg-gray-100 hover:bg-indigo-500/10 text-gray-600 hover:text-indigo-600 dark:bg-white/5 dark:text-gray-300 dark:hover:text-indigo-400 transition-colors" title="Attendance">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </a>
                            <a href="/students?class_id=<?= $c['id'] ?>" class="p-2 rounded-lg bg-gray-100 hover:bg-indigo-500/10 text-gray-600 hover:text-indigo-600 dark:bg-white/5 dark:text-gray-300 dark:hover:text-indigo-400 transition-colors" title="Students Roster">
                                <i data-lucide="users" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Column 2 & 3: Recent Parent Call Interactions (2 cols on desktop) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-2">
                    <i data-lucide="phone-call" class="w-4 h-4 text-emerald-500"></i>
                    <span>Recent Parent Communication Log</span>
                </h2>
                <a href="/calls" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">View All Log</a>
            </div>

            <?php if (empty($recentCalls)): ?>
                <div class="glass-card p-8 text-center text-gray-500 dark:text-gray-400 text-xs">
                    <i data-lucide="phone-off" class="w-8 h-8 mx-auto mb-2 text-gray-400 opacity-50"></i>
                    No parent communications recorded yet. Click "Log Parent Call" to document your first call.
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recentCalls as $call): ?>
                        <div class="glass-card p-4 transition-all hover:border-indigo-500/40">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 mb-2 border-b border-gray-100 dark:border-white/5">
                                <div>
                                    <span class="font-bold text-sm text-gray-900 dark:text-white">
                                        <?= $e($call['first_name'] . ' ' . $call['last_name']) ?>
                                    </span>
                                    <span class="text-xs text-indigo-500 font-mono ml-1.5">(<?= $e($call['student_admin_id']) ?>)</span>
                                    <span class="text-xs text-gray-400 ml-1">• <?= $e($call['class_name']) ?>-<?= $e($call['class_section']) ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] px-2.5 py-0.5 rounded-full font-semibold border <?= \App\Models\ParentCallLog::getCategoryBadgeClass($call['call_category']) ?>">
                                        <?= $e($call['call_category']) ?>
                                    </span>
                                    <span class="text-[10px] text-gray-400">
                                        <?= date('M d, H:i', strtotime($call['call_timestamp'])) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase font-semibold">Teacher Discussion Notes:</span>
                                    <p class="text-gray-800 dark:text-gray-200 line-clamp-2"><?= $e($call['discussion_summary']) ?></p>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase font-semibold">Parent Commitment / Response:</span>
                                    <p class="text-gray-800 dark:text-gray-200 line-clamp-2 italic font-medium">"<?= $e($call['parent_response']) ?>"</p>
                                </div>
                            </div>

                            <div class="mt-3 pt-2 flex items-center justify-between text-[11px] border-t border-gray-100 dark:border-white/5">
                                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                    <span>Contact: <strong class="text-gray-700 dark:text-gray-300"><?= $e($call['contact_person_name']) ?></strong> (<?= $e($call['relationship']) ?>)</span>
                                    <span>•</span>
                                    <span><?= $e($call['phone_dialed']) ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" 
                                            onclick="openWhatsApp('<?= $e($call['phone_dialed']) ?>', '<?= $e($call['first_name'] . ' ' . $call['last_name']) ?>', '<?= $e($call['student_admin_id']) ?>')"
                                            class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 hover:underline font-semibold">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                        <span>WhatsApp</span>
                                    </button>
                                    <a href="/students/<?= $call['student_id'] ?>" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                        View Dossier &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>
