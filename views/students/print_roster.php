<div>
    <!-- Document Header -->
    <div class="border-b-2 border-slate-900 pb-2.5 mb-3 flex justify-between items-start">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 uppercase">Institutional Classroom Roster &amp; Student Directory</h1>
            <p class="text-xs text-slate-600 mt-0.5"><?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> • Official Enrolled Student Dossier</p>
        </div>
        <div class="text-right text-xs">
            <div><strong>Generated:</strong> <?= date('F d, Y - H:i') ?></div>
            <div><strong>Teacher:</strong> <?= $e($teacher['name'] ?? 'Instructor') ?></div>
        </div>
    </div>

    <!-- Metadata & Statistics Box -->
    <div class="bg-slate-50 border border-slate-300 rounded-xl p-2.5 mb-3.5 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Cohort Scope</span>
            <span class="font-bold text-sm text-slate-900">
                <?= $class ? $e($class['name'] . ' - ' . $class['section']) : 'All Managed Classes' ?>
            </span>
            <span class="text-slate-600 block text-[11px]">Academic Year: <?= $class ? $e($class['academic_year']) : date('Y') ?></span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Total Enrollment</span>
            <span class="font-bold text-sm text-slate-900"><?= count($students) ?> Students</span>
            <span class="text-indigo-600 block text-[11px] font-semibold">Active Roster Directory</span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Gender Distribution</span>
            <?php
                $m = 0; $f = 0; $other = 0;
                foreach ($students as $s) {
                    $g = strtolower((string)($s['gender'] ?? ''));
                    if ($g === 'male' || $g === 'm') $m++;
                    elseif ($g === 'female' || $g === 'f') $f++;
                    else $other++;
                }
            ?>
            <span class="font-bold text-slate-800 text-xs">Male: <?= $m ?> | Female: <?= $f ?><?= $other ? " | Other: {$other}" : '' ?></span>
            <span class="text-slate-500 block text-[10px]">Recorded Demographic</span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Attendance Performance</span>
            <?php
                $rates = array_filter(array_map(fn($s) => $s['stats']['rate'] ?? null, $students), fn($r) => $r !== null);
                $avgRate = count($rates) > 0 ? round(array_sum($rates) / count($rates), 1) : 0;
            ?>
            <span class="font-bold text-slate-900 text-sm"><?= $avgRate ?>% Avg Rate</span>
            <span class="text-emerald-700 block text-[10px] font-semibold">Class Attendance Index</span>
        </div>
    </div>

    <!-- Student List Table (A4 Compact Layout for 40+ Records) -->
    <table class="w-full text-left text-xs border-collapse border border-slate-300 compact-table">
        <thead class="bg-slate-100 border-b border-slate-300 text-slate-700 uppercase font-bold text-[9.5pt]">
            <tr>
                <th class="border border-slate-300 py-1 px-1.5 text-center w-8">#</th>
                <th class="border border-slate-300 py-1 px-1.5 w-28">Admin ID</th>
                <th class="border border-slate-300 py-1 px-1.5">Student Full Name</th>
                <th class="border border-slate-300 py-1 px-1.5 w-14 text-center">Roll #</th>
                <?php if (!$class): ?>
                    <th class="border border-slate-300 py-1 px-1.5 w-24">Class</th>
                <?php endif; ?>
                <th class="border border-slate-300 py-1 px-1.5 w-16 text-center">Gender</th>
                <th class="border border-slate-300 py-1 px-1.5">Primary Guardian</th>
                <th class="border border-slate-300 py-1 px-1.5 w-28">Contact Phone</th>
                <th class="border border-slate-300 py-1 px-1.5 w-16 text-center">Attend. %</th>
                <th class="border border-slate-300 py-1 px-1.5 w-16 text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="<?= $class ? 9 : 10 ?>" class="border border-slate-300 p-4 text-center text-slate-500 italic">
                        No enrolled students found in this roster.
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                    $index = 1;
                    foreach ($students as $stu): 
                        $rate = $stu['stats']['rate'] ?? 0;
                        $consecutive = $stu['stats']['consecutive_absences'] ?? 0;
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
                            <?php if ($consecutive >= 3): ?>
                                <span class="text-[8pt] text-rose-600 font-bold block">(<?= $consecutive ?> Consecutive Absences)</span>
                            <?php endif; ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1 text-center font-mono text-[9pt] text-slate-700 tabular-nums">
                            <?= !empty($stu['roll_number']) ? $e($stu['roll_number']) : '-' ?>
                        </td>
                        <?php if (!$class): ?>
                            <td class="border border-slate-300 py-1 px-1.5 text-[9pt] text-slate-700 whitespace-nowrap">
                                <?= $e($stu['class_name'] ?? '') ?><?= !empty($stu['class_section']) ? ' - ' . $e($stu['class_section']) : '' ?>
                            </td>
                        <?php endif; ?>
                        <td class="border border-slate-300 py-1 px-1 text-center text-[9pt] text-slate-600">
                            <?= !empty($stu['gender']) ? $e(ucfirst($stu['gender'])) : '-' ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 text-[9pt] text-slate-800">
                            <?= !empty($stu['primary_guardian_name']) ? $e($stu['primary_guardian_name']) : '-' ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 font-mono text-[9pt] text-slate-700 tabular-nums whitespace-nowrap">
                            <?= !empty($stu['primary_phone']) ? $e($stu['primary_phone']) : '-' ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1 text-center font-mono font-bold text-[9pt] tabular-nums <?= $rate < 75 ? 'text-rose-700' : 'text-emerald-700' ?>">
                            <?= $rate ?>%
                        </td>
                        <td class="border border-slate-300 py-1 px-1 text-center">
                            <span class="inline-block px-1.5 py-0.2 rounded text-[8pt] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                ACTIVE
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Institutional Sign-Off Block -->
    <div class="avoid-break pt-6 border-t border-slate-300 grid grid-cols-2 gap-8 text-xs text-slate-600 mt-6">
        <div>
            <div class="border-b border-slate-400 pb-6 mb-1"></div>
            <span>Teacher In-Charge: <strong><?= $e($teacher['name'] ?? 'Class Teacher') ?></strong></span>
        </div>
        <div>
            <div class="border-b border-slate-400 pb-6 mb-1"></div>
            <span>Principal / School Administration Verification</span>
        </div>
    </div>
</div>
