<div>
    <!-- Document Header -->
    <div class="border-b-2 border-gray-900 pb-4 mb-6 flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900 uppercase">Class Examination Result Sheet</h1>
            <p class="text-xs text-gray-600 mt-1"><?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> • Official Gradebook Record</p>
        </div>
        <div class="text-right text-xs">
            <div><strong>Generated:</strong> <?= date('F d, Y - H:i') ?></div>
            <div><strong>Teacher:</strong> <?= $e($teacher['name'] ?? 'Instructor') ?></div>
        </div>
    </div>

    <!-- Exam Metadata Box -->
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Assessment Title</span>
            <span class="font-bold text-sm text-gray-900"><?= $e($exam['title']) ?></span>
            <span class="text-indigo-600 block text-[11px] font-semibold"><?= $e($exam['term']) ?></span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Subject & Class</span>
            <span class="font-bold text-sm text-gray-900"><?= $e($exam['subject_name']) ?></span>
            <span class="text-gray-600 block text-[11px]"><?= $e($exam['class_name'] . ' ' . $exam['class_section']) ?></span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Score Thresholds</span>
            <span class="font-bold text-gray-900">Max: <?= $exam['max_marks'] ?> pts</span>
            <span class="text-emerald-700 block text-[11px]">Pass: <?= $exam['pass_marks'] ?> pts</span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Class Performance</span>
            <span class="font-bold text-gray-900">Average: <?= $analytics['avg_score'] ?? 0 ?></span>
            <span class="text-indigo-700 block text-[11px]">Pass Rate: <?= $analytics['pass_rate'] ?? 0 ?>%</span>
        </div>
    </div>

    <!-- Results Table -->
    <table class="w-full text-left text-xs border-collapse border border-gray-300">
        <thead class="bg-gray-100 border-b border-gray-300 text-gray-700 uppercase font-bold text-[10px]">
            <tr>
                <th class="border border-gray-300 p-2 text-center w-12">#</th>
                <th class="border border-gray-300 p-2 w-32">Admin ID</th>
                <th class="border border-gray-300 p-2">Student Full Name</th>
                <th class="border border-gray-300 p-2 text-center w-28">Score (Max <?= $exam['max_marks'] ?>)</th>
                <th class="border border-gray-300 p-2 text-center w-20">Percentage</th>
                <th class="border border-gray-300 p-2 text-center w-20">Grade</th>
                <th class="border border-gray-300 p-2 text-center w-20">Status</th>
                <th class="border border-gray-300 p-2">Teacher Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="8" class="border border-gray-300 p-4 text-center text-gray-500 italic">
                        No enrolled students found in this class.
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                    $index = 1;
                    foreach ($students as $stu): 
                        $mData = $stu['marks_data'];
                        $hasMarks = ($mData !== null);
                        $score = $hasMarks ? $mData['marks_obtained'] : '-';
                        $pct = $hasMarks ? $mData['percentage'] . '%' : '-';
                        $grade = $hasMarks ? $mData['letter_grade'] : '-';
                        $isPass = $hasMarks && ($mData['marks_obtained'] >= $exam['pass_marks']);
                        $remarks = $hasMarks ? ($mData['remarks'] ?? '') : '';
                ?>
                    <tr class="align-middle hover:bg-gray-50 <?= ($index % 2 === 0) ? 'bg-gray-50/50' : '' ?>">
                        <td class="border border-gray-300 p-2 text-center font-mono text-[11px] text-gray-500">
                            <?= $index++ ?>
                        </td>
                        <td class="border border-gray-300 p-2 font-mono font-bold text-indigo-700 text-[11px] whitespace-nowrap">
                            <?= $e($stu['student_admin_id']) ?>
                        </td>
                        <td class="border border-gray-300 p-2 font-semibold text-gray-900">
                            <?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>
                        </td>
                        <td class="border border-gray-300 p-2 text-center font-mono font-bold text-gray-900 tabular-nums">
                            <?= $score ?>
                        </td>
                        <td class="border border-gray-300 p-2 text-center font-mono tabular-nums font-semibold">
                            <?= $pct ?>
                        </td>
                        <td class="border border-gray-300 p-2 text-center font-bold">
                            <span class="inline-block px-2 py-0.5 rounded font-mono text-[11px] <?= ($grade === 'A+' || $grade === 'A') ? 'bg-emerald-100 text-emerald-800' : (($grade === 'F') ? 'bg-rose-100 text-rose-800' : 'bg-gray-100 text-gray-800') ?>">
                                <?= $grade ?>
                            </span>
                        </td>
                        <td class="border border-gray-300 p-2 text-center font-bold uppercase text-[10px]">
                            <?php if (!$hasMarks): ?>
                                <span class="text-gray-400">UNGRADED</span>
                            <?php elseif ($isPass): ?>
                                <span class="text-emerald-700">PASS</span>
                            <?php else: ?>
                                <span class="text-rose-700">FAIL</span>
                            <?php endif; ?>
                        </td>
                        <td class="border border-gray-300 p-2 text-[11px] text-gray-600 italic">
                            <?= !empty($remarks) ? $e($remarks) : '-' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Signature & Institutional Verification Box -->
    <div class="mt-12 pt-8 border-t border-gray-300 grid grid-cols-2 gap-12 text-xs">
        <div>
            <div class="border-b border-gray-400 pb-1 mb-1 w-64"></div>
            <span class="font-bold text-gray-900 block">Class / Subject Teacher Signature</span>
            <span class="text-gray-500 text-[10px]">Date Signed: ________________________</span>
        </div>
        <div class="text-right flex flex-col items-end">
            <div class="border-b border-gray-400 pb-1 mb-1 w-64"></div>
            <span class="font-bold text-gray-900 block">Principal / Headmaster Stamp & Signature</span>
            <span class="text-gray-500 text-[10px]">Official Academic Seal Required</span>
        </div>
    </div>
</div>
