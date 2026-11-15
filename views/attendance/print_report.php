<div>
    <!-- Document Header -->
    <div class="border-b-2 border-slate-900 pb-2.5 mb-3 flex justify-between items-start">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 uppercase">Institutional Attendance Audit Register</h1>
            <p class="text-xs text-slate-600 mt-0.5"><?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> • Official Attendance Records &amp; Truancy Log</p>
        </div>
        <div class="text-right text-xs">
            <div><strong>Generated:</strong> <?= date('F d, Y - H:i') ?></div>
            <div><strong>Teacher:</strong> <?= $e($teacher['name'] ?? 'Class Teacher') ?></div>
        </div>
    </div>

    <!-- Metadata & Attendance Stats Box -->
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

    <!-- Attendance Table (A4 Compact Layout for 40+ Records) -->
    <table class="w-full text-left text-xs border-collapse border border-slate-300 compact-table">
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

    <!-- Signature & Institutional Verification Box -->
    <div class="mt-12 pt-8 border-t border-slate-300 grid grid-cols-2 gap-12 text-xs avoid-break">
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
