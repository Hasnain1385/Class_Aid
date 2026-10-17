<div class="space-y-6">

    <!-- Top Profile Dossier Card -->
    <div class="glass-card p-6 sm:p-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            
            <!-- Avatar & Primary Info -->
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white flex items-center justify-center font-bold text-2xl sm:text-3xl shadow-xl shadow-indigo-600/30 flex-shrink-0">
                    <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white">
                            <?= $e($student['first_name'] . ' ' . $student['last_name']) ?>
                        </h1>
                        <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded-lg bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-500/30">
                            <?= $e($student['student_admin_id']) ?>
                        </span>
                        <span class="text-xs px-2.5 py-0.5 rounded-lg bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-medium">
                            <?= $e($student['class_name'] . ' ' . $student['class_section']) ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-xs text-gray-500 dark:text-gray-400 mt-2">
                        <div>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Guardian:</span> 
                            <?= $e($student['primary_guardian_name']) ?> (<?= $e($student['primary_guardian_relation']) ?>)
                        </div>
                        <div>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Contact:</span> 
                            <span class="font-mono"><?= $e($student['primary_phone']) ?></span>
                            <?php if (!empty($student['secondary_phone'])): ?>
                                / <span class="font-mono"><?= $e($student['secondary_phone']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">DOB:</span> 
                            <?= !empty($student['dob']) ? date('M d, Y', strtotime($student['dob'])) : 'Not recorded' ?>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Email:</span> 
                            <?= !empty($student['guardian_email']) ? $e($student['guardian_email']) : 'N/A' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons for Mobile & Desktop -->
            <div class="flex flex-wrap items-center gap-2 pt-4 md:pt-0 border-t md:border-t-0 border-gray-100 dark:border-white/5">
                <button type="button" 
                        onclick="openWhatsApp('<?= $e($student['primary_phone']) ?>', '<?= $e($student['first_name'] . ' ' . $student['last_name']) ?>', '<?= $e($student['student_admin_id']) ?>')"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 flex items-center gap-1.5 transition-all">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>WhatsApp</span>
                </button>
                <a href="/calls/create?student_id=<?= $student['id'] ?>" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/30 flex items-center gap-1.5 transition-all">
                    <i data-lucide="phone-outgoing" class="w-4 h-4"></i>
                    <span>Log Call</span>
                </a>
                <a href="/calls/print/student/<?= $student['id'] ?>" target="_blank" class="px-3 py-2 rounded-xl text-xs font-medium glass-card hover:border-indigo-500/40 text-gray-700 dark:text-gray-300 flex items-center gap-1.5 transition-all" title="Print Parent Communication Audit">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Print Calls Audit</span>
                </a>
                <a href="/results/report/<?= $student['id'] ?>" target="_blank" class="px-3 py-2 rounded-xl text-xs font-medium glass-card hover:border-indigo-500/40 text-gray-700 dark:text-gray-300 flex items-center gap-1.5 transition-all" title="Print Student Report Card">
                    <i data-lucide="award" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Report Card</span>
                </a>
                <a href="/students/<?= $student['id'] ?>/edit" class="p-2 rounded-xl glass-card text-gray-500 hover:text-gray-900 dark:hover:text-white" title="Edit Student">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                </a>
            </div>

        </div>
    </div>

    <!-- Attendance & Academic Overview Metric Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        
        <div class="glass-card p-4">
            <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Attendance Rate</span>
            <div class="text-xl sm:text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">
                <?= $attendanceStats['percentage'] ?>%
            </div>
            <span class="text-[10px] text-gray-400"><?= $attendanceStats['present'] ?> of <?= $attendanceStats['total'] ?> days present</span>
        </div>

        <div class="glass-card p-4">
            <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Total Absences</span>
            <div class="text-xl sm:text-2xl font-extrabold <?= $attendanceStats['absent'] >= 3 ? 'text-rose-500' : 'text-gray-900 dark:text-white' ?> mt-1">
                <?= $attendanceStats['absent'] ?> Days
            </div>
            <span class="text-[10px] text-gray-400"><?= $attendanceStats['excused'] ?> excused</span>
        </div>

        <div class="glass-card p-4">
            <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Parent Interactions</span>
            <div class="text-xl sm:text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">
                <?= count($callLogs) ?>
            </div>
            <span class="text-[10px] text-gray-400">Recorded calls & notes</span>
        </div>

        <div class="glass-card p-4">
            <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Exams Completed</span>
            <div class="text-xl sm:text-2xl font-extrabold text-violet-600 dark:text-violet-400 mt-1">
                <?= count($marks) ?>
            </div>
            <span class="text-[10px] text-gray-400">Graded assessments</span>
        </div>

    </div>

    <!-- Section 1: Parent Communication Journal (Priority Core Feature) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="phone-call" class="w-5 h-5 text-indigo-500"></i>
                <span>Parent Communication Journal & Contact Trail</span>
            </h2>
            <div class="flex items-center gap-2">
                <a href="/calls/print/student/<?= $student['id'] ?>" target="_blank" class="text-xs text-gray-500 dark:text-gray-400 hover:text-indigo-500 flex items-center gap-1">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Print Audit</span>
                </a>
                <a href="/calls/create?student_id=<?= $student['id'] ?>" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    + Log New Call
                </a>
            </div>
        </div>

        <?php if (empty($callLogs)): ?>
            <div class="glass-card p-8 text-center text-xs text-gray-500 dark:text-gray-400">
                <i data-lucide="phone" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                No parent communication records found for this student.
                <div class="mt-3">
                    <a href="/calls/create?student_id=<?= $student['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white rounded-lg font-semibold">
                        Log First Call Now
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($callLogs as $call): ?>
                    <div class="glass-card p-4 sm:p-5 hover:border-indigo-500/40 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 mb-3 border-b border-gray-100 dark:border-white/5">
                            <div class="flex items-center gap-2">
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold border <?= \App\Models\ParentCallLog::getCategoryBadgeClass($call['call_category']) ?>">
                                    <?= $e($call['call_category']) ?>
                                </span>
                                <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">
                                    Contacted: <?= $e($call['contact_person_name']) ?> (<?= $e($call['relationship']) ?>)
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-gray-400">
                                <span class="font-mono"><?= $e($call['phone_dialed']) ?></span>
                                <span>•</span>
                                <span><?= date('M d, Y h:i A', strtotime($call['call_timestamp'])) ?></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            <div class="bg-gray-50/50 dark:bg-white/5 p-3 rounded-xl">
                                <span class="text-gray-400 block text-[10px] font-bold uppercase tracking-wider mb-1">
                                    Teacher Discussion Summary:
                                </span>
                                <p class="text-gray-800 dark:text-gray-200 whitespace-pre-line leading-relaxed">
                                    <?= $e($call['discussion_summary']) ?>
                                </p>
                            </div>

                            <div class="bg-indigo-50/20 dark:bg-indigo-950/20 p-3 rounded-xl border border-indigo-500/10">
                                <span class="text-indigo-500 dark:text-indigo-400 block text-[10px] font-bold uppercase tracking-wider mb-1">
                                    Parent Response & Commitment:
                                </span>
                                <p class="text-gray-800 dark:text-gray-200 italic whitespace-pre-line leading-relaxed font-medium">
                                    "<?= $e($call['parent_response']) ?>"
                                </p>
                            </div>
                        </div>

                        <?php if ($call['follow_up_required']): ?>
                            <div class="mt-3 pt-2.5 flex items-center justify-between text-xs border-t border-gray-100 dark:border-white/5">
                                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 font-medium">
                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                    <span>Follow-up Scheduled: <?= !empty($call['follow_up_date']) ? date('M d, Y', strtotime($call['follow_up_date'])) : 'Pending' ?></span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-500/10 font-bold"><?= $e($call['status']) ?></span>
                                </div>
                                <form method="POST" action="/calls/toggle-status/<?= $call['id'] ?>">
                                    <?= $csrf() ?>
                                    <input type="hidden" name="redirect" value="/students/<?= $student['id'] ?>">
                                    <button type="submit" class="text-indigo-600 dark:text-indigo-400 hover:underline text-[11px] font-semibold">
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
            <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="award" class="w-5 h-5 text-violet-500"></i>
                <span>Academic Assessments & Marks</span>
            </h2>
            <a href="/results/report/<?= $student['id'] ?>" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                Generate Printable Report Card &rarr;
            </a>
        </div>

        <?php if (empty($marks)): ?>
            <div class="glass-card p-6 text-center text-xs text-gray-500 dark:text-gray-400">
                No exam or test marks recorded for this student yet.
            </div>
        <?php else: ?>
            <div class="glass-card overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-200/50 dark:border-white/10 text-gray-500 uppercase font-semibold">
                        <tr>
                            <th class="py-3 px-4">Exam / Test</th>
                            <th class="py-3 px-4">Term</th>
                            <th class="py-3 px-4">Marks Scored</th>
                            <th class="py-3 px-4">Percentage</th>
                            <th class="py-3 px-4">Letter Grade</th>
                            <th class="py-3 px-4">Result</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php foreach ($marks as $m): ?>
                            <tr>
                                <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">
                                    <?= $e($m['exam_title']) ?>
                                    <span class="block text-[10px] text-gray-400"><?= $e($m['subject_name'] ?? '') ?></span>
                                </td>
                                <td class="py-3 px-4 text-gray-600 dark:text-gray-300"><?= $e($m['term']) ?></td>
                                <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">
                                    <?= $m['marks_obtained'] ?> / <?= $m['max_marks'] ?>
                                </td>
                                <td class="py-3 px-4 font-bold text-indigo-600 dark:text-indigo-400">
                                    <?= $m['percentage'] ?>%
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-bold px-2 py-0.5 rounded-md <?= \App\Utils\GradeCalculator::badgeClass($m['letter_grade']) ?>">
                                        <?= $m['letter_grade'] ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
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
        <?php endif; ?>
    </div>

    <!-- Section 3: Recent Attendance Roll Call History -->
    <div class="space-y-4">
        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i data-lucide="calendar-check" class="w-5 h-5 text-emerald-500"></i>
            <span>Recent Attendance Log</span>
        </h2>

        <?php if (empty($attendanceHistory)): ?>
            <div class="glass-card p-6 text-center text-xs text-gray-500 dark:text-gray-400">
                No attendance recorded yet.
            </div>
        <?php else: ?>
            <div class="glass-card p-4">
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($attendanceHistory as $att): ?>
                        <?php
                            $badge = match($att['status']) {
                                'P' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/30',
                                'A' => 'bg-rose-500/10 text-rose-600 border-rose-500/30',
                                'L' => 'bg-amber-500/10 text-amber-600 border-amber-500/30',
                                default => 'bg-blue-500/10 text-blue-600 border-blue-500/30',
                            };
                        ?>
                        <div class="p-2 rounded-xl border text-center text-xs <?= $badge ?> min-w-[70px]">
                            <div class="text-[10px] font-semibold text-gray-500 dark:text-gray-400"><?= date('M d', strtotime($att['attendance_date'])) ?></div>
                            <div class="font-extrabold text-sm mt-0.5"><?= $att['status'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
