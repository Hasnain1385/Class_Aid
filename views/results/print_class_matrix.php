<div>
    <!-- Document Header -->
    <div class="border-b-2 border-slate-900 pb-2.5 mb-3 flex justify-between items-start">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 uppercase">Consolidated Class Gradebook &amp; Assessment Matrix</h1>
            <p class="text-xs text-slate-600 mt-0.5"><?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> • Official Institutional Transcript Record</p>
        </div>
        <div class="text-right text-xs">
            <div><strong>Generated:</strong> <?= date('F d, Y - H:i') ?></div>
            <div><strong>Teacher:</strong> <?= $e($teacher['name'] ?? 'Instructor') ?></div>
        </div>
    </div>

    <!-- Metadata & Statistics Box -->
    <div class="bg-slate-50 border border-slate-300 rounded-xl p-2.5 mb-3.5 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Target Class</span>
            <span class="font-bold text-sm text-slate-900"><?= $e($class['name'] . ' - ' . $class['section']) ?></span>
            <span class="text-slate-600 block text-[11px]">Academic Year: <?= $e($class['academic_year']) ?></span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Evaluated Assessments</span>
            <span class="font-bold text-sm text-indigo-700"><?= count($selectedExams) ?> Tests Included</span>
            <span class="text-slate-600 block text-[11px]">Total Max: <?= $totalMaxMarks ?> Points</span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Class Enrollment</span>
            <span class="font-bold text-sm text-slate-900"><?= count($students) ?> Students</span>
            <span class="text-emerald-700 block text-[11px] font-semibold">Consolidated Roster</span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Cohort Performance</span>
            <?php
                $pcts = array_map(fn($r) => $r['overall_pct'], $students);
                $cohortAvg = count($pcts) > 0 ? round(array_sum($pcts) / count($pcts), 1) : 0;
            ?>
            <span class="font-bold text-sm text-slate-900"><?= $cohortAvg ?>% Class Average</span>
            <span class="text-indigo-600 block text-[10px] font-semibold">Standardized Metric</span>
        </div>
    </div>

    <!-- Consolidated Assessment Matrix Table (Compact Layout) -->
    <table class="w-full text-left text-xs border-collapse border border-slate-300 compact-table">
        <thead class="bg-slate-100 border-b border-slate-300 text-slate-700 uppercase font-bold text-[9pt]">
            <tr>
                <th class="border border-slate-300 py-1 px-1 text-center w-7">#</th>
                <th class="border border-slate-300 py-1 px-1.5 w-24">Admin ID</th>
                <th class="border border-slate-300 py-1 px-1.5 min-w-[120px]">Student Full Name</th>
                <?php foreach ($selectedExams as $ex): ?>
                    <th class="border border-slate-300 py-1 px-1 text-center">
                        <div class="truncate max-w-[100px] mx-auto text-[8.5pt]" title="<?= $e($ex['title']) ?>"><?= $e($ex['title']) ?></div>
                        <span class="text-[8pt] text-slate-500 font-mono font-normal">/<?= $ex['max_marks'] ?></span>
                    </th>
                <?php endforeach; ?>
                <th class="border border-slate-300 py-1 px-1 text-center w-20">Total /<?= $totalMaxMarks ?></th>
                <th class="border border-slate-300 py-1 px-1 text-center w-14">Pct %</th>
                <th class="border border-slate-300 py-1 px-1 text-center w-14">Grade</th>
                <th class="border border-slate-300 py-1 px-1 text-center w-16">Standing</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="<?= 7 + count($selectedExams) ?>" class="border border-slate-300 p-4 text-center text-slate-500 italic">
                        No student result records found for this class.
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                    $index = 1;
                    foreach ($students as $row): 
                        $stu = $row['student'];
                        $grade = $row['overall_grade'];
                        $isPass = $row['is_pass'];
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
                        <?php foreach ($selectedExams as $ex): ?>
                            <?php 
                                $eId = (int) $ex['id'];
                                $m = $row['marks'][$eId] ?? null;
                            ?>
                            <td class="border border-slate-300 py-1 px-1 text-center font-mono text-[9pt] tabular-nums">
                                <?php if ($m !== null && isset($m['marks_obtained'])): ?>
                                    <span class="font-bold text-slate-900"><?= $m['marks_obtained'] ?></span>
                                    <span class="text-[8pt] text-slate-500 font-semibold">(<?= $m['letter_grade'] ?? '-' ?>)</span>
                                <?php else: ?>
                                    <span class="text-slate-400 italic text-[8pt]">-</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="border border-slate-300 py-1 px-1 text-center font-mono font-bold text-slate-900 tabular-nums text-[9pt]">
                            <?= $row['total_obtained'] ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1 text-center font-mono font-bold tabular-nums text-[9pt]">
                            <?= $row['overall_pct'] ?>%
                        </td>
                        <td class="border border-slate-300 py-1 px-1 text-center font-bold">
                            <span class="inline-block px-1.5 py-0.2 rounded font-mono text-[8pt] <?= ($grade === 'A+' || $grade === 'A') ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : (($grade === 'F') ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-slate-100 text-slate-800 border border-slate-300') ?>">
                                <?= $grade ?>
                            </span>
                        </td>
                        <td class="border border-slate-300 py-1 px-1 text-center font-bold uppercase text-[8pt]">
                            <?php if ($row['graded_count'] === 0): ?>
                                <span class="text-slate-400">UNGRADED</span>
                            <?php elseif ($isPass): ?>
                                <span class="text-emerald-700">PASS</span>
                            <?php else: ?>
                                <span class="text-rose-700">FAIL</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <!-- Class Averages Summary Row -->
                <tr class="bg-slate-100/90 font-bold border-t-2 border-slate-400 text-slate-900" style="page-break-inside: avoid;">
                    <td colspan="3" class="border border-slate-300 py-1.5 px-2 text-right uppercase text-[8.5pt]">
                        Class Cohort Averages:
                    </td>
                    <?php foreach ($selectedExams as $ex): ?>
                        <?php $avg = $examAverages[(int) $ex['id']] ?? 0.0; ?>
                        <td class="border border-slate-300 py-1.5 px-1 text-center font-mono text-[9pt] tabular-nums text-indigo-700">
                            <?= $avg ?>
                        </td>
                    <?php endforeach; ?>
                    <td class="border border-slate-300 py-1.5 px-1 text-center font-mono text-[9pt] tabular-nums text-indigo-700">
                        <?= round(array_sum($examAverages), 1) ?>
                    </td>
                    <td class="border border-slate-300 py-1.5 px-1 text-center font-mono text-[9pt] tabular-nums text-indigo-700">
                        <?= $cohortAvg ?>%
                    </td>
                    <td colspan="2" class="border border-slate-300 py-1.5 px-1 text-center text-[8pt] text-slate-500 uppercase">
                        Mean Grade: <?= \App\Utils\GradeCalculator::calculateGrade($cohortAvg) ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Institutional Sign-off Block -->
    <div class="avoid-break pt-6 border-t border-slate-300 grid grid-cols-2 gap-8 text-xs text-slate-600 mt-6">
        <div>
            <div class="border-b border-slate-400 pb-6 mb-1"></div>
            <span>Evaluating Faculty: <strong><?= $e($teacher['name'] ?? 'Instructor') ?></strong></span>
        </div>
        <div>
            <div class="border-b border-slate-400 pb-6 mb-1"></div>
            <span>Dean / Examination Controller Signature</span>
        </div>
    </div>
</div>
