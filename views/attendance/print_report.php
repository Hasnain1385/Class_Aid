<?php
$isSingleStudent = ($scope === 'single' || count($report['students']) === 1);
$student = ($isSingleStudent && !empty($report['students'])) ? $report['students'][0] : null;

// If single student, compile all calendar dates in period
$allDatesInRange = [];
if ($isSingleStudent && $student) {
    $curDt = new DateTime($dateFrom);
    $endDt = new DateTime($dateTo);
    if ($curDt > $endDt) {
        $tmp = $curDt;
        $curDt = $endDt;
        $endDt = $tmp;
    }
    $interval = new DateInterval('P1D');
    $endPlusOne = (clone $endDt)->modify('+1 day');
    $period = new DatePeriod($curDt, $interval, $endPlusOne);
    foreach ($period as $dt) {
        $allDatesInRange[] = $dt->format('Y-m-d');
    }
}
?>

<div>
    <!-- Document Header -->
    <div class="border-b-2 border-slate-900 pb-2.5 mb-3 flex justify-between items-start">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 uppercase">
                <?= $isSingleStudent ? 'Student Attendance Audit Dossier' : 'Institutional Attendance Audit Register' ?>
            </h1>
            <p class="text-xs text-slate-600 mt-0.5">
                <?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> &bull; <?= $isSingleStudent ? 'Comprehensive Day-by-Day Attendance Log & Truancy Audit' : 'Official Attendance Records & Truancy Log' ?>
            </p>
        </div>
        <div class="text-right text-xs">
            <div><strong>Generated:</strong> <?= date('F d, Y - H:i') ?></div>
            <div><strong>Teacher:</strong> <?= $e($teacher['name'] ?? 'Class Teacher') ?></div>
        </div>
    </div>

    <?php if ($isSingleStudent && $student): ?>
        <!-- Single Student Profile & Aggregate Performance Card -->
        <div class="bg-slate-50 border border-slate-300 rounded-xl p-3 mb-3.5 grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
            <div>
                <span class="text-slate-500 uppercase block font-semibold text-[10px]">Student Record</span>
                <span class="font-bold text-sm text-slate-900 block"><?= $e($student['first_name'] . ' ' . $student['last_name']) ?></span>
                <span class="font-mono text-indigo-700 font-bold block text-[11px]">[<?= $e($student['student_admin_id']) ?>]</span>
            </div>
            <div>
                <span class="text-slate-500 uppercase block font-semibold text-[10px]">Class & Academic Year</span>
                <span class="font-bold text-slate-900 block"><?= $e($class['name'] . ' - ' . $class['section']) ?></span>
                <span class="text-slate-600 block text-[11px]">Year: <?= $e($class['academic_year']) ?></span>
            </div>
            <div>
                <span class="text-slate-500 uppercase block font-semibold text-[10px]">Guardian Contact</span>
                <span class="font-bold text-slate-900 block"><?= $e($student['primary_guardian_name'] ?? 'Not Specified') ?> (<?= $e($student['primary_guardian_relation'] ?? 'Guardian') ?>)</span>
                <span class="font-mono text-slate-600 block text-[11px] tabular-nums"><?= $e($student['primary_phone'] ?? 'N/A') ?></span>
            </div>
            <div>
                <span class="text-slate-500 uppercase block font-semibold text-[10px]">Audit Period & Standing</span>
                <span class="font-bold text-slate-900 block"><?= date('M d', strtotime($dateFrom)) ?> &mdash; <?= date('M d, Y', strtotime($dateTo)) ?></span>
                <span class="block text-[11px] font-bold <?= $student['is_chronic'] ? 'text-rose-700' : 'text-emerald-700' ?>">
                    <?= $student['attendance_rate'] ?>% &bull; <?= $student['is_chronic'] ? 'CHRONIC ALERT' : ($student['attendance_rate'] >= 90 ? 'EXCELLENT' : 'REGULAR') ?>
                </span>
            </div>
        </div>

        <!-- Metric Summary Chips -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 mb-3 text-center text-xs">
            <div class="border border-slate-200 bg-white rounded-lg py-1.5 px-2">
                <span class="text-[10px] text-slate-500 uppercase block">Total Days</span>
                <span class="font-bold text-slate-900 text-sm tabular-nums"><?= count($allDatesInRange) ?></span>
            </div>
            <div class="border border-slate-200 bg-white rounded-lg py-1.5 px-2">
                <span class="text-[10px] text-slate-500 uppercase block">Sessions Held</span>
                <span class="font-bold text-indigo-700 text-sm tabular-nums"><?= $student['total_sessions'] ?></span>
            </div>
            <div class="border border-emerald-200 bg-emerald-50/50 rounded-lg py-1.5 px-2">
                <span class="text-[10px] text-emerald-700 uppercase block font-semibold">Present (P)</span>
                <span class="font-bold text-emerald-800 text-sm tabular-nums"><?= $student['present_count'] ?></span>
            </div>
            <div class="border border-rose-200 bg-rose-50/50 rounded-lg py-1.5 px-2">
                <span class="text-[10px] text-rose-700 uppercase block font-semibold">Absent (A)</span>
                <span class="font-bold text-rose-800 text-sm tabular-nums"><?= $student['absent_count'] ?></span>
            </div>
            <div class="border border-amber-200 bg-amber-50/50 rounded-lg py-1.5 px-2">
                <span class="text-[10px] text-amber-700 uppercase block font-semibold">Late / Excused</span>
                <span class="font-bold text-amber-800 text-sm tabular-nums"><?= $student['late_count'] + $student['excused_count'] ?></span>
            </div>
        </div>

        <!-- Single Student Day-by-Day Attendance Table (All Dates Shown) -->
        <table class="w-full text-left text-xs border-collapse border border-slate-300 compact-table mb-4">
            <thead class="bg-slate-100 border-b border-slate-300 text-slate-700 uppercase font-bold text-[9pt]">
                <tr>
                    <th class="border border-slate-300 py-1 px-1.5 text-center w-8">#</th>
                    <th class="border border-slate-300 py-1 px-2 w-28">Date</th>
                    <th class="border border-slate-300 py-1 px-2 w-24">Day</th>
                    <th class="border border-slate-300 py-1 px-2 text-center w-36">Attendance Status</th>
                    <th class="border border-slate-300 py-1 px-2">Session Audit Notes & Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $dIdx = 1;
                    foreach ($allDatesInRange as $dItem):
                        $dTs = strtotime($dItem);
                        $dayName = date('l', $dTs);
                        $isWeekend = (date('N', $dTs) >= 6);
                        $rec = $student['date_map'][$dItem] ?? null;
                ?>
                    <tr class="align-middle hover:bg-slate-50 <?= ($dIdx % 2 === 0) ? 'bg-slate-50/50' : '' ?>" style="page-break-inside: avoid;">
                        <td class="border border-slate-300 py-1 px-1 text-center font-mono text-[9pt] text-slate-500">
                            <?= $dIdx++ ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-2 font-mono font-bold text-[9pt] text-slate-900 whitespace-nowrap tabular-nums">
                            <?= date('M d, Y', $dTs) ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-2 text-[9pt] text-slate-700 font-medium">
                            <?= $dayName ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 text-center font-bold text-[8.5pt]">
                            <?php if ($rec): ?>
                                <?php $st = strtoupper((string) ($rec['status'] ?? '')); ?>
                                <?php if ($st === 'P'): ?>
                                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300">✔ Present (P)</span>
                                <?php elseif ($st === 'A'): ?>
                                    <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 border border-rose-300">✖ Absent (A)</span>
                                <?php elseif ($st === 'L'): ?>
                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-300">⏰ Late (L)</span>
                                <?php elseif ($st === 'E'): ?>
                                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 border border-blue-300">ℹ Excused (E)</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700"><?= $e($st) ?></span>
                                <?php endif; ?>
                            <?php elseif ($isWeekend): ?>
                                <span class="px-2 py-0.5 rounded text-slate-400 bg-slate-100 border border-slate-200 font-normal">Weekend</span>
                            <?php elseif (in_array($dItem, $report['dates'])): ?>
                                <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-semibold">Unrecorded</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded text-slate-500 bg-slate-50 border border-slate-200 font-normal">No Session</span>
                            <?php endif; ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-2 text-[9pt] text-slate-600">
                            <?php if ($rec && !empty($rec['remarks'])): ?>
                                <span class="font-medium text-slate-900"><?= $e($rec['remarks']) ?></span>
                            <?php elseif ($rec): ?>
                                <span class="text-slate-400 italic">Roll call recorded</span>
                            <?php elseif ($isWeekend): ?>
                                <span class="text-slate-400 italic">Institutional weekend off</span>
                            <?php elseif (in_array($dItem, $report['dates'])): ?>
                                <span class="text-amber-600 italic">Class session held; student unrecorded</span>
                            <?php else: ?>
                                <span class="text-slate-400 italic">No class session scheduled</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-slate-100 border-t-2 border-slate-300 font-bold text-[9pt] text-slate-800">
                <tr>
                    <td colspan="3" class="border border-slate-300 py-1.5 px-2 text-right">
                        Day-by-Day Audit Totals:
                    </td>
                    <td class="border border-slate-300 py-1.5 px-2 text-center text-indigo-700 font-bold">
                        <?= $student['attendance_rate'] ?>% Net Rate
                    </td>
                    <td class="border border-slate-300 py-1.5 px-2 text-[8.5pt]">
                        Present: <?= $student['present_count'] ?> &bull; Absent: <?= $student['absent_count'] ?> &bull; Late: <?= $student['late_count'] ?> &bull; Excused: <?= $student['excused_count'] ?> (<?= $student['total_sessions'] ?> Sessions)
                    </td>
                </tr>
            </tfoot>
        </table>

    <?php else: ?>
        <!-- Cohort Attendance Metadata Box -->
        <div class="bg-slate-50 border border-slate-300 rounded-xl p-2.5 mb-3.5 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
            <div>
                <span class="text-slate-500 uppercase block font-semibold text-[10px]">Class Roster</span>
                <span class="font-bold text-sm text-slate-900"><?= $e($class['name'] . ' - ' . $class['section']) ?></span>
                <span class="text-slate-600 block text-[11px]">Academic Year: <?= $e($class['academic_year']) ?></span>
            </div>
            <div>
                <span class="text-slate-500 uppercase block font-semibold text-[10px]">Audit Period</span>
                <span class="font-bold text-slate-900"><?= date('M d, Y', strtotime($dateFrom)) ?> &mdash; <?= date('M d, Y', strtotime($dateTo)) ?></span>
                <span class="text-indigo-600 block text-[11px] font-semibold"><?= $report['summary']['total_sessions_taken'] ?> Sessions Recorded</span>
            </div>
            <div>
                <span class="text-slate-500 uppercase block font-semibold text-[10px]">Cohort Aggregates</span>
                <span class="font-bold text-emerald-700 text-sm">Present: <?= $report['summary']['total_p'] ?></span>
                <span class="text-rose-700 block text-[11px]">Absent: <?= $report['summary']['total_a'] ?> | Late: <?= $report['summary']['total_l'] ?></span>
            </div>
            <div>
                <span class="text-slate-500 uppercase block font-semibold text-[10px]">Attendance Performance</span>
                <span class="font-bold text-slate-900 text-sm"><?= $report['summary']['overall_rate'] ?>% Rate</span>
                <span class="text-rose-700 block text-[11px] font-semibold"><?= $report['summary']['chronic_count'] ?> Chronic Alerts</span>
            </div>
        </div>

        <!-- Cohort Attendance Table (A4 Compact Layout for 40+ Records) -->
        <table class="w-full text-left text-xs border-collapse border border-slate-300 compact-table mb-4">
            <thead class="bg-slate-100 border-b border-slate-300 text-slate-700 uppercase font-bold text-[9.5pt]">
                <tr>
                    <th class="border border-slate-300 py-1 px-1.5 text-center w-8">#</th>
                    <th class="border border-slate-300 py-1 px-1.5 w-28">Admin ID</th>
                    <th class="border border-slate-300 py-1 px-1.5">Student Full Name</th>
                    <th class="border border-slate-300 py-1 px-1.5 text-center w-16">Sessions</th>
                    <th class="border border-slate-300 py-1 px-1.5 text-center w-14 text-emerald-800">Present</th>
                    <th class="border border-slate-300 py-1 px-1.5 text-center w-14 text-rose-800">Absent</th>
                    <th class="border border-slate-300 py-1 px-1.5 text-center w-14 text-amber-800">Late</th>
                    <th class="border border-slate-300 py-1 px-1.5 text-center w-16">Rate %</th>
                    <th class="border border-slate-300 py-1 px-1.5 text-center w-24">Standing</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($report['students'])): ?>
                    <tr>
                        <td colspan="9" class="border border-slate-300 p-4 text-center text-slate-500 italic">
                            No student attendance records found matching criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php
                        $idx = 1;
                        foreach ($report['students'] as $stu):
                            $rate = $stu['attendance_rate'];
                            $isChronic = $stu['is_chronic'];
                    ?>
                        <tr class="align-middle hover:bg-slate-50 <?= ($idx % 2 === 0) ? 'bg-slate-50/50' : '' ?>" style="page-break-inside: avoid;">
                            <td class="border border-slate-300 py-1 px-1 text-center font-mono text-[9pt] text-slate-500">
                                <?= $idx++ ?>
                            </td>
                            <td class="border border-slate-300 py-1 px-1.5 font-mono font-bold text-indigo-700 text-[9pt] whitespace-nowrap">
                                <?= $e($stu['student_admin_id']) ?>
                            </td>
                            <td class="border border-slate-300 py-1 px-1.5 font-semibold text-slate-900 text-[9.5pt]">
                                <?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>
                                <?php if ($stu['consecutive_absences'] >= 3): ?>
                                    <span class="text-[8pt] text-rose-600 font-bold block">(<?= $stu['consecutive_absences'] ?> Consecutive Absences)</span>
                                <?php endif; ?>
                            </td>
                            <td class="border border-slate-300 py-1 px-1.5 text-center font-mono tabular-nums text-slate-700 text-[9pt]">
                                <?= $stu['total_sessions'] ?>
                            </td>
                            <td class="border border-slate-300 py-1 px-1.5 text-center font-mono font-bold text-emerald-700 tabular-nums text-[9pt]">
                                <?= $stu['present_count'] ?>
                            </td>
                            <td class="border border-slate-300 py-1 px-1.5 text-center font-mono font-bold text-rose-700 tabular-nums text-[9pt]">
                                <?= $stu['absent_count'] ?>
                            </td>
                            <td class="border border-slate-300 py-1 px-1.5 text-center font-mono font-bold text-amber-700 tabular-nums text-[9pt]">
                                <?= $stu['late_count'] ?>
                            </td>
                            <td class="border border-slate-300 py-1 px-1.5 text-center font-mono font-bold tabular-nums text-[9pt]">
                                <?= $rate ?>%
                            </td>
                            <td class="border border-slate-300 py-1 px-1 text-center font-bold text-[8pt] uppercase">
                                <?php if ($isChronic): ?>
                                    <span class="px-1.5 py-0.2 rounded bg-rose-100 text-rose-800 border border-rose-300">CHRONIC</span>
                                <?php elseif ($rate >= 90): ?>
                                    <span class="px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 border border-emerald-300">EXCELLENT</span>
                                <?php else: ?>
                                    <span class="px-1.5 py-0.2 rounded bg-blue-100 text-blue-800 border border-blue-300">REGULAR</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Signature & Institutional Verification Box -->
    <div class="mt-8 pt-6 border-t border-slate-300 grid grid-cols-2 gap-12 text-xs avoid-break">
        <div>
            <div class="border-b border-slate-400 pb-1 mb-1 w-64"></div>
            <span class="font-bold text-slate-900 block">Class Teacher Signature</span>
            <span class="text-slate-500 text-[10px]">Date Signed: ________________________</span>
        </div>
        <div class="text-right flex flex-col items-end">
            <div class="border-b border-slate-400 pb-1 mb-1 w-64"></div>
            <span class="font-bold text-slate-900 block">Principal / Academic Registrar Seal</span>
            <span class="text-slate-500 text-[10px]">Official Institutional Seal Required</span>
        </div>
    </div>
</div>
