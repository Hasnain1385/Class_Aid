<div>
    <!-- Document Header -->
    <div class="border-b-2 border-gray-900 pb-4 mb-6 flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900 uppercase">Student Academic Report Card</h1>
            <p class="text-xs text-gray-600 mt-1"><?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> • Official Academic Performance Record</p>
        </div>
        <div class="text-right text-xs">
            <div><strong>Academic Year:</strong> <?= $e($student['academic_year'] ?? '2026-2027') ?></div>
            <div><strong>Issue Date:</strong> <?= date('F d, Y') ?></div>
        </div>
    </div>

    <!-- Student Metadata Card -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Student Full Name</span>
            <span class="font-bold text-sm text-gray-900"><?= $e($student['first_name'] . ' ' . $student['last_name']) ?></span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Student-Admin ID</span>
            <span class="font-mono font-bold text-sm text-indigo-700"><?= $e($student['student_admin_id']) ?></span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Class & Section</span>
            <span class="font-bold text-sm text-gray-900"><?= $e($student['class_name'] . ' ' . $student['class_section']) ?></span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Class Teacher</span>
            <span class="font-bold text-gray-900"><?= $e($teacher['name'] ?? 'Class Teacher') ?></span>
        </div>
    </div>

    <!-- Attendance Performance Summary -->
    <div class="mb-6 p-4 border border-gray-300 rounded-lg flex justify-between items-center text-xs">
        <div>
            <span class="font-bold text-gray-800 uppercase text-[10px] block">Attendance Performance</span>
            <span class="text-sm font-extrabold text-indigo-700"><?= $attendanceStats['percentage'] ?>% Overall Attendance</span>
        </div>
        <div class="flex gap-6 text-gray-600">
            <div><strong>Days Present:</strong> <?= $attendanceStats['present'] ?></div>
            <div><strong>Days Absent:</strong> <?= $attendanceStats['absent'] ?></div>
            <div><strong>Excused:</strong> <?= $attendanceStats['excused'] ?></div>
            <div><strong>Total School Days:</strong> <?= $attendanceStats['total'] ?></div>
        </div>
    </div>

    <!-- Academic Marks Table -->
    <div class="mb-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-800 mb-3">Assessment & Examination Results</h2>

        <table class="w-full text-left text-xs border-collapse border border-gray-300">
            <thead class="bg-gray-100 border-b border-gray-300 text-gray-700 uppercase font-bold text-[10px]">
                <tr>
                    <th class="border border-gray-300 p-2.5">Subject & Examination</th>
                    <th class="border border-gray-300 p-2.5">Term</th>
                    <th class="border border-gray-300 p-2.5 text-center">Max Marks</th>
                    <th class="border border-gray-300 p-2.5 text-center">Marks Scored</th>
                    <th class="border border-gray-300 p-2.5 text-center">Percentage</th>
                    <th class="border border-gray-300 p-2.5 text-center">Grade</th>
                    <th class="border border-gray-300 p-2.5">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($marks)): ?>
                    <tr>
                        <td colspan="7" class="border border-gray-300 p-4 text-center text-gray-500 italic">
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
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 p-2.5 font-bold text-gray-900">
                                <?= $e($m['subject_name'] ?? 'General') ?>
                                <span class="block text-[10px] font-normal text-gray-500"><?= $e($m['exam_title']) ?></span>
                            </td>
                            <td class="border border-gray-300 p-2.5"><?= $e($m['term']) ?></td>
                            <td class="border border-gray-300 p-2.5 text-center font-mono"><?= $m['max_marks'] ?></td>
                            <td class="border border-gray-300 p-2.5 text-center font-mono font-bold text-gray-900"><?= $m['marks_obtained'] ?></td>
                            <td class="border border-gray-300 p-2.5 text-center font-mono font-bold text-indigo-700"><?= $m['percentage'] ?>%</td>
                            <td class="border border-gray-300 p-2.5 text-center font-bold">
                                <span class="px-2 py-0.5 rounded text-[11px] <?= $m['is_passed'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                                    <?= $m['letter_grade'] ?>
                                </span>
                            </td>
                            <td class="border border-gray-300 p-2.5 italic text-[11px] text-gray-600">
                                <?= !empty($m['remarks']) ? $e($m['remarks']) : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Total / Summary Row -->
                    <?php 
                        $overallPct = ($totalMax > 0) ? round(($totalScored / $totalMax) * 100, 1) : 0;
                        $overallGrade = \App\Utils\GradeCalculator::calculateGrade($overallPct);
                    ?>
                    <tr class="bg-gray-100 font-bold border-t-2 border-gray-400">
                        <td colspan="2" class="border border-gray-300 p-2.5 uppercase text-gray-800">Overall Aggregate</td>
                        <td class="border border-gray-300 p-2.5 text-center font-mono"><?= $totalMax ?></td>
                        <td class="border border-gray-300 p-2.5 text-center font-mono text-gray-900"><?= $totalScored ?></td>
                        <td class="border border-gray-300 p-2.5 text-center font-mono text-indigo-800"><?= $overallPct ?>%</td>
                        <td class="border border-gray-300 p-2.5 text-center text-sm font-black"><?= $overallGrade ?></td>
                        <td class="border border-gray-300 p-2.5 text-[10px] text-gray-600 uppercase">
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
