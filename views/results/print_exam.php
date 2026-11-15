<div>
    <!-- Document Header -->
    <div class="border-b-2 border-slate-900 pb-2.5 mb-3 flex justify-between items-start">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 uppercase">Class Examination Result Sheet</h1>
            <p class="text-xs text-slate-600 mt-0.5"><?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> • Official Gradebook Record</p>
        </div>
        <div class="text-right text-xs">
            <div><strong>Generated:</strong> <?= date('F d, Y - H:i') ?></div>
            <div><strong>Teacher:</strong> <?= $e($teacher['name'] ?? 'Instructor') ?></div>
        </div>
    </div>

    <!-- Exam Metadata Box -->
    <div class="bg-slate-50 border border-slate-300 rounded-xl p-2.5 mb-3.5 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Assessment Title</span>
            <span class="font-bold text-sm text-slate-900"><?= $e($exam['title']) ?></span>
            <span class="text-indigo-600 block text-[11px] font-semibold"><?= $e($exam['term']) ?></span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Subject & Class</span>
            <span class="font-bold text-sm text-slate-900"><?= $e($exam['subject_name']) ?></span>
            <span class="text-slate-600 block text-[11px]"><?= $e($exam['class_name'] . ' ' . $exam['class_section']) ?></span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Score Thresholds</span>
            <span class="font-bold text-slate-900">Max: <?= $exam['max_marks'] ?> pts</span>
            <span class="text-emerald-700 block text-[11px]">Pass: <?= $exam['pass_marks'] ?> pts</span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Class Performance</span>
            <span class="font-bold text-slate-900">Average: <?= $analytics['avg_score'] ?? 0 ?></span>
            <span class="text-indigo-700 block text-[11px]">Pass Rate: <?= $analytics['pass_rate'] ?? 0 ?>%</span>
        </div>
    </div>

    <!-- Results Table (A4 Compact Layout) -->
    <table class="w-full text-left text-xs border-collapse border border-slate-300 compact-table">
        <thead class="bg-slate-100 border-b border-slate-300 text-slate-700 uppercase font-bold text-[9.5pt]">
            <tr>
                <th class="border border-slate-300 py-1 px-1.5 text-center w-8">#</th>
                <th class="border border-slate-300 py-1 px-1.5 w-28">Admin ID</th>
                <th class="border border-slate-300 py-1 px-1.5">Student Full Name</th>
                <th class="border border-slate-300 py-1 px-1.5 text-center w-24">Score (Max <?= $exam['max_marks'] ?>)</th>
                <th class="border border-slate-300 py-1 px-1.5 text-center w-16">Pct %</th>
                <th class="border border-slate-300 py-1 px-1.5 text-center w-16">Grade</th>
                <th class="border border-slate-300 py-1 px-1.5 text-center w-16">Status</th>
                <th class="border border-slate-300 py-1 px-1.5">Teacher Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="8" class="border border-slate-300 p-4 text-center text-slate-500 italic">
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
                    <tr class="align-middle hover:bg-slate-50 <?= ($index % 2 === 0) ? 'bg-slate-50/50' : '' ?>" style="page-break-inside: avoid;">
                        <td class="border border-slate-300 py-1 px-1 text-center font-mono text-[9pt] text-slate-500">
                            <?= $index++ ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 font-mono font-bold text-indigo-700 text-[9pt] whitespace-nowrap">
                            <?= $e($stu['student_admin_id']) ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 font-semibold text-slate-900 text-[9.5pt]">
                            <?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 text-center font-mono font-bold text-slate-900 tabular-nums text-[9pt]">
                            <?= $score ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 text-center font-mono tabular-nums font-semibold text-[9pt]">
                            <?= $pct ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1 text-center font-bold">
                            <span class="inline-block px-1.5 py-0.2 rounded font-mono text-[8pt] <?= ($grade === 'A+' || $grade === 'A') ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : (($grade === 'F') ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-slate-100 text-slate-800 border border-slate-300') ?>">
                                <?= $grade ?>
                            </span>
                        </td>
                        <td class="border border-slate-300 py-1 px-1 text-center font-bold uppercase text-[8pt]">
                            <?php if (!$hasMarks): ?>
                                <span class="text-slate-400">UNGRADED</span>
                            <?php elseif ($isPass): ?>
                                <span class="text-emerald-700">PASS</span>
                            <?php else: ?>
                                <span class="text-rose-700">FAIL</span>
                            <?php endif; ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 text-[9pt] text-slate-600 italic">
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
