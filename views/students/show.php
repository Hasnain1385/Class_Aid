<div class="space-y-6">

    <!-- Top Profile Dossier Card -->
    <div class="glass-card p-6 sm:p-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            
            <!-- Avatar & Primary Info -->
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-2xl sm:text-3xl shadow-md flex-shrink-0">
                    <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            <?= $e($student['first_name'] . ' ' . $student['last_name']) ?>
                        </h1>
                        <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 tabular-nums">
                            <?= $e($student['student_admin_id']) ?>
                        </span>
                        <span class="text-xs px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium border border-slate-200 dark:border-slate-700">
                            <?= $e($student['class_name'] . ' ' . $student['class_section']) ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5 text-xs text-slate-500 dark:text-slate-400 mt-2.5">
                        <div>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Guardian:</span> 
                            <?= $e($student['primary_guardian_name']) ?> (<?= $e($student['primary_guardian_relation']) ?>)
                        </div>
                        <div>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Contact:</span> 
                            <span class="font-mono tabular-nums"><?= $e($student['primary_phone']) ?></span>
                            <?php if (!empty($student['secondary_phone'])): ?>
                                / <span class="font-mono tabular-nums"><?= $e($student['secondary_phone']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">DOB:</span> 
                            <?= !empty($student['dob']) ? date('M d, Y', strtotime($student['dob'])) : 'Not recorded' ?>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Email:</span> 
                            <?= !empty($student['guardian_email']) ? $e($student['guardian_email']) : 'N/A' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons for Mobile & Desktop -->
            <div class="flex flex-wrap items-center gap-2 pt-4 md:pt-0 border-t md:border-t-0 border-slate-100 dark:border-slate-800">
                <button type="button" 
                        onclick="openWhatsApp('<?= $e($student['primary_phone']) ?>', '<?= $e($student['first_name'] . ' ' . $student['last_name']) ?>', '<?= $e($student['student_admin_id']) ?>')"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 flex items-center gap-1.5 transition-all cursor-pointer">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>WhatsApp</span>
                </button>
                <a href="/calls/create?student_id=<?= $student['id'] ?>" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-sm flex items-center gap-1.5 transition-all">
                    <i data-lucide="phone-outgoing" class="w-4 h-4"></i>
                    <span>Log Call</span>
                </a>
                <a href="/calls/print/student/<?= $student['id'] ?>" target="_blank" class="px-3 py-2 rounded-xl text-xs font-medium bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center gap-1.5 shadow-sm transition-all" title="Print Parent Communication Audit">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Print Calls Audit</span>
                </a>
                <a href="/results/report/<?= $student['id'] ?>" target="_blank" class="px-3 py-2 rounded-xl text-xs font-medium bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center gap-1.5 shadow-sm transition-all" title="Print Student Report Card">
                    <i data-lucide="award" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Report Card</span>
                </a>
                <a href="/students/<?= $student['id'] ?>/edit" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" title="Edit Student">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                </a>
            </div>

        </div>
    </div>

    <!-- Attendance & Academic Overview Metric Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        
        <div class="glass-card p-4">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Attendance Rate</span>
            <div class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1 tabular-nums">
                <?= $attendanceStats['percentage'] ?>%
            </div>
            <span class="text-xs text-slate-400 tabular-nums"><?= $attendanceStats['present'] ?> of <?= $attendanceStats['total'] ?> days present</span>
        </div>

        <div class="glass-card p-4">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Absences</span>
            <div class="text-xl sm:text-2xl font-black <?= $attendanceStats['absent'] >= 3 ? 'text-rose-500' : 'text-slate-900 dark:text-white' ?> mt-1 tabular-nums">
                <?= $attendanceStats['absent'] ?> Days
            </div>
            <span class="text-xs text-slate-400 tabular-nums"><?= $attendanceStats['excused'] ?> excused</span>
        </div>

        <div class="glass-card p-4">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Parent Interactions</span>
            <div class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 tabular-nums">
                <?= count($callLogs) ?>
            </div>
            <span class="text-xs text-slate-400">Recorded calls & notes</span>
        </div>

        <div class="glass-card p-4">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Exams Completed</span>
            <div class="text-xl sm:text-2xl font-black text-violet-600 dark:text-violet-400 mt-1 tabular-nums">
                <?= count($marks) ?>
            </div>
            <span class="text-xs text-slate-400">Graded assessments</span>
        </div>

    </div>

    <!-- Section 1: Parent Communication Journal -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="phone-call" class="w-5 h-5 text-indigo-500"></i>
                <span>Parent Communication Journal & Contact Trail</span>
            </h2>
            <div class="flex items-center gap-2">
                <a href="/calls/print/student/<?= $student['id'] ?>" target="_blank" class="text-xs text-slate-500 dark:text-slate-400 hover:text-indigo-500 flex items-center gap-1">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Print Audit</span>
                </a>
                <a href="/calls/create?student_id=<?= $student['id'] ?>" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    + Log New Call
                </a>
            </div>
        </div>

        <?php if (empty($callLogs)): ?>
            <div class="glass-card p-8 text-center text-xs text-slate-500 dark:text-slate-400">
                <i data-lucide="phone" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                No parent communication records found for this student.
                <div class="mt-3">
                    <a href="/calls/create?student_id=<?= $student['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-semibold shadow-sm transition-all">
                        Log First Call Now
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($callLogs as $call): ?>
                    <div class="glass-card p-4 sm:p-5 hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-2 flex-wrap">
                                <?= \App\Models\ParentCallLog::renderPill($call['call_category']) ?>
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                    Contacted: <?= $e($call['contact_person_name']) ?> (<?= $e($call['relationship']) ?>)
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-slate-400 tabular-nums">
                                <span class="font-mono"><?= $e($call['phone_dialed']) ?></span>
                                <span>•</span>
                                <span><?= date('M d, Y h:i A', strtotime($call['call_timestamp'])) ?></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            <div class="bg-slate-50 dark:bg-slate-950/60 p-3.5 rounded-xl border border-slate-200/60 dark:border-slate-800">
                                <span class="text-slate-400 block text-xs font-bold uppercase tracking-wider mb-1">
                                    Teacher Discussion Summary:
                                </span>
                                <p class="text-slate-800 dark:text-slate-200 whitespace-pre-line leading-relaxed">
                                    <?= $e($call['discussion_summary']) ?>
                                </p>
                            </div>

                            <div class="bg-indigo-50/30 dark:bg-indigo-950/20 p-3.5 rounded-xl border border-indigo-200/60 dark:border-indigo-900/50">
                                <span class="text-indigo-600 dark:text-indigo-400 block text-xs font-bold uppercase tracking-wider mb-1">
                                    Parent Response & Commitment:
                                </span>
                                <p class="text-slate-800 dark:text-slate-200 italic whitespace-pre-line leading-relaxed font-medium">
                                    "<?= $e($call['parent_response']) ?>"
                                </p>
                            </div>
                        </div>

                        <?php if ($call['follow_up_required']): ?>
                            <div class="mt-3 pt-3 flex items-center justify-between text-xs border-t border-slate-100 dark:border-slate-800">
                                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 font-medium">
                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                    <span>Follow-up Scheduled: <?= !empty($call['follow_up_date']) ? date('M d, Y', strtotime($call['follow_up_date'])) : 'Pending' ?></span>
                                    <span class="text-xs px-2 py-0.5 rounded-full badge-amber font-bold"><?= $e($call['status']) ?></span>
                                </div>
                                <form method="POST" action="/calls/toggle-status/<?= $call['id'] ?>">
                                    <?= $csrf() ?>
                                    <input type="hidden" name="redirect" value="/students/<?= $student['id'] ?>">
                                    <button type="submit" class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs font-semibold cursor-pointer">
                                        Mark as <?= ($call['status'] === 'Resolved') ? 'Pending' : 'Resolved' ?>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Section 2: Academic Exam Marks & Gradebook -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="award" class="w-5 h-5 text-violet-500"></i>
                <span>Academic Assessments & Marks</span>
            </h2>
            <a href="/results/report/<?= $student['id'] ?>" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">
                Generate Printable Report Card &rarr;
            </a>
        </div>

        <?php if (empty($marks)): ?>
            <div class="glass-card p-6 text-center text-xs text-slate-500 dark:text-slate-400">
                No exam or test marks recorded for this student yet.
            </div>
        <?php else: ?>
            <div class="glass-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-semibold text-xs tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Exam / Test</th>
                                <th class="py-3 px-4">Term</th>
                                <th class="py-3 px-4">Marks Scored</th>
                                <th class="py-3 px-4">Percentage</th>
                                <th class="py-3 px-4">Letter Grade</th>
                                <th class="py-3 px-4">Result</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            <?php foreach ($marks as $m): ?>
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60 transition-colors">
                                    <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">
                                        <?= $e($m['exam_title']) ?>
                                        <span class="block text-xs text-slate-400 font-normal"><?= $e($m['subject_name'] ?? '') ?></span>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300"><?= $e($m['term']) ?></td>
                                    <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white tabular-nums">
                                        <?= $m['marks_obtained'] ?> / <?= $m['max_marks'] ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-indigo-600 dark:text-indigo-400 tabular-nums">
                                        <?= $m['percentage'] ?>%
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-bold px-2.5 py-0.5 rounded-md text-xs <?= \App\Utils\GradeCalculator::badgeClass($m['letter_grade']) ?>">
                                            <?= $m['letter_grade'] ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php if ($m['is_passed']): ?>
                                            <span class="text-emerald-500 font-bold flex items-center gap-1">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i> Pass
                                            </span>
                                        <?php else: ?>
                                            <span class="text-rose-500 font-bold flex items-center gap-1">
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i> Needs Improvement
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Section 3: Recent Attendance Roll Call History -->
    <div class="space-y-4">
        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <i data-lucide="calendar-check" class="w-5 h-5 text-emerald-500"></i>
            <span>Recent Attendance Log</span>
        </h2>

        <?php if (empty($attendanceHistory)): ?>
            <div class="glass-card p-6 text-center text-xs text-slate-500 dark:text-slate-400">
                No attendance recorded yet.
            </div>
        <?php else: ?>
            <div class="glass-card p-4">
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($attendanceHistory as $att): ?>
                        <?php
                            $badge = match($att['status']) {
                                'P' => 'badge-emerald',
                                'A' => 'badge-rose',
                                'L' => 'badge-amber',
                                default => 'badge-indigo',
                            };
                        ?>
                        <div class="p-2.5 rounded-xl border text-center text-xs <?= $badge ?> min-w-[70px]">
                            <div class="text-xs font-semibold opacity-75 tabular-nums"><?= date('M d', strtotime($att['attendance_date'])) ?></div>
                            <div class="font-black text-sm mt-0.5"><?= $att['status'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
