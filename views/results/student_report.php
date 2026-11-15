<div>
    <!-- Document Header -->
    <div class="border-b-2 border-slate-900 pb-2.5 mb-3 flex justify-between items-start">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 uppercase">Student Academic Report Card</h1>
            <p class="text-xs text-slate-600 mt-0.5"><?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> • Official Academic Performance Record</p>
        </div>
        <div class="text-right text-xs">
            <div><strong>Academic Year:</strong> <?= $e($student['academic_year'] ?? '2026-2027') ?></div>
            <div><strong>Issue Date:</strong> <?= date('F d, Y') ?></div>
        </div>
    </div>

    <!-- Student Metadata Card -->
    <div class="bg-slate-50 border border-slate-300 rounded-xl p-2.5 mb-3 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Student Full Name</span>
            <span class="font-bold text-sm text-slate-900"><?= $e($student['first_name'] . ' ' . $student['last_name']) ?></span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Student-Admin ID</span>
            <span class="font-mono font-bold text-sm text-indigo-700"><?= $e($student['student_admin_id']) ?></span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Class &amp; Section</span>
            <span class="font-bold text-sm text-slate-900"><?= $e($student['class_name'] . ' ' . $student['class_section']) ?></span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Class Teacher</span>
            <span class="font-bold text-slate-900"><?= $e($teacher['name'] ?? 'Class Teacher') ?></span>
        </div>
    </div>

    <!-- Attendance Performance Summary -->
    <div class="mb-3.5 p-2.5 border border-slate-300 rounded-xl flex justify-between items-center text-xs bg-slate-50/50">
        <div>
            <span class="font-bold text-slate-800 uppercase text-[10px] block">Attendance Performance</span>
            <span class="text-sm font-extrabold text-indigo-700"><?= $attendanceStats['percentage'] ?>% Overall Attendance</span>
        </div>
        <div class="flex gap-4 sm:gap-6 text-slate-600 text-[11px]">
            <div><strong>Present:</strong> <?= $attendanceStats['present'] ?></div>
            <div><strong>Absent:</strong> <?= $attendanceStats['absent'] ?></div>
            <div><strong>Excused:</strong> <?= $attendanceStats['excused'] ?></div>
            <div><strong>Total Days:</strong> <?= $attendanceStats['total'] ?></div>
        </div>
    </div>

    <!-- Academic Marks Table -->
    <div class="mb-4">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 mb-2">Assessment &amp; Examination Results</h2>

        <table class="w-full text-left text-xs border-collapse border border-slate-300 compact-table">
            <thead class="bg-slate-100 border-b border-slate-300 text-slate-700 uppercase font-bold text-[9.5pt]">
                <tr>
                    <th class="border border-slate-300 py-1 px-1.5">Subject &amp; Examination</th>
                    <th class="border border-slate-300 py-1 px-1.5 w-20">Term</th>
                    <th class="border border-slate-300 py-1 px-1.5 text-center w-20">Max Marks</th>
                    <th class="border border-slate-300 py-1 px-1.5 text-center w-24">Marks Scored</th>
                    <th class="border border-slate-300 py-1 px-1.5 text-center w-20">Percentage</th>
                    <th class="border border-slate-300 py-1 px-1.5 text-center w-16">Grade</th>
                    <th class="border border-slate-300 py-1 px-1.5">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($marks)): ?>
                    <tr>
                        <td colspan="7" class="border border-slate-300 p-4 text-center text-slate-500 italic">
                            No examinations recorded for this student.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                        $totalMax = 0;
                        $totalScored = 0;
                    ?>
                    <?php foreach ($marks as $m): ?>
                        <?php 
                            $totalMax += (float) $m['max_marks'];
                            $totalScored += (float) $m['marks_obtained'];
                        ?>
                        <tr class="hover:bg-slate-50" style="page-break-inside: avoid;">
                            <td class="border border-slate-300 py-1 px-1.5 font-bold text-slate-900 text-[9.5pt]">
                                <?= $e($m['subject_name'] ?? 'General') ?>
                                <span class="block text-[8.5pt] font-normal text-slate-500"><?= $e($m['exam_title']) ?></span>
                            </td>
                            <td class="border border-slate-300 py-1 px-1.5 text-[9pt]"><?= $e($m['term']) ?></td>
                            <td class="border border-slate-300 py-1 px-1.5 text-center font-mono text-[9pt]"><?= $m['max_marks'] ?></td>
                            <td class="border border-slate-300 py-1 px-1.5 text-center font-mono font-bold text-slate-900 text-[9pt]"><?= $m['marks_obtained'] ?></td>
                            <td class="border border-slate-300 py-1 px-1.5 text-center font-mono font-bold text-indigo-700 text-[9pt]"><?= $m['percentage'] ?>%</td>
                            <td class="border border-slate-300 py-1 px-1 text-center font-bold">
                                <span class="px-1.5 py-0.2 rounded text-[8pt] <?= $m['is_passed'] ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' ?>">
                                    <?= $m['letter_grade'] ?>
                                </span>
                            </td>
                            <td class="border border-slate-300 py-1 px-1.5 italic text-[9pt] text-slate-600">
                                <?= !empty($m['remarks']) ? $e($m['remarks']) : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Total / Summary Row -->
                    <?php 
                        $overallPct = ($totalMax > 0) ? round(($totalScored / $totalMax) * 100, 1) : 0;
                        $overallGrade = \App\Utils\GradeCalculator::calculateGrade($overallPct);
                    ?>
                    <tr class="bg-slate-100 font-bold border-t-2 border-slate-400" style="page-break-inside: avoid;">
                        <td colspan="2" class="border border-slate-300 py-1.5 px-2 uppercase text-slate-800 text-[9pt]">Overall Aggregate</td>
                        <td class="border border-slate-300 py-1.5 px-1.5 text-center font-mono text-[9pt]"><?= $totalMax ?></td>
                        <td class="border border-slate-300 py-1.5 px-1.5 text-center font-mono text-slate-900 text-[9pt]"><?= $totalScored ?></td>
                        <td class="border border-slate-300 py-1.5 px-1.5 text-center font-mono text-indigo-800 text-[9pt]"><?= $overallPct ?>%</td>
                        <td class="border border-slate-300 py-1.5 px-1 text-center text-[9pt] font-black"><?= $overallGrade ?></td>
                        <td class="border border-slate-300 py-1.5 px-2 text-[8.5pt] text-slate-600 uppercase">
                            <?= ($overallPct >= 40) ? 'Passed' : 'Needs Improvement' ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Grading Scale Key -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-8 text-[10px] text-gray-600 flex justify-between items-center">
        <span class="font-bold uppercase text-gray-700">Grading Scale:</span>
        <span>A+ (90-100%) Outstanding</span>
        <span>A (80-89%) Excellent</span>
        <span>B (70-79%) Good</span>
        <span>C (60-69%) Satisfactory</span>
        <span>D (50-59%) Pass</span>
        <span>F (&lt;50%) Remedial Required</span>
    </div>

    <!-- Signatures Section -->
    <div class="pt-8 border-t border-gray-300 grid grid-cols-3 gap-8 text-xs text-gray-600 mt-10">
        <div>
            <div class="border-b border-gray-400 pb-8 mb-1"></div>
            <span>Class Teacher Signature: <strong><?= $e($teacher['name'] ?? 'Class Teacher') ?></strong></span>
        </div>
        <div>
            <div class="border-b border-gray-400 pb-8 mb-1"></div>
            <span>Principal / Head of Institution</span>
        </div>
        <div>
            <div class="border-b border-gray-400 pb-8 mb-1"></div>
            <span>Parent / Guardian Signature</span>
        </div>
    </div>
</div>
