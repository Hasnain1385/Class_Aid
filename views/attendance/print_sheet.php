<div>
    <!-- Document Header -->
    <div class="border-b-2 border-slate-900 pb-2.5 mb-3 flex justify-between items-start">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 uppercase"><?= ucfirst($sessionType ?? 'Morning') ?> Attendance Register &amp; Roll Call</h1>
            <p class="text-xs text-slate-600 mt-0.5"><?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> • <?= ucfirst($sessionType ?? 'Morning') ?> Session Attendance Record</p>
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
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Session &amp; Date</span>
            <span class="font-bold text-sm text-slate-900"><?= ucfirst($sessionType ?? 'Morning') ?> &bull; <?= date('M d, Y', strtotime($date)) ?></span>
            <span class="text-indigo-600 block text-[11px] font-mono"><?= date('l', strtotime($date)) ?> (<?= $e($date) ?>)</span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Roll Summary</span>
            <span class="font-bold text-emerald-700 text-sm">Present: <?= $summary['present'] ?></span>
            <span class="text-rose-700 block text-[11px]">Absent: <?= $summary['absent'] ?> | Late: <?= $summary['late'] ?></span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Total Enrolled</span>
            <span class="font-bold text-slate-900 text-sm"><?= count($students) ?> Students</span>
            <?php 
                $rate = count($students) > 0 ? round(($summary['present'] / count($students)) * 100, 1) : 0;
            ?>
            <span class="text-indigo-700 block text-[11px] font-semibold">Attendance Rate: <?= $rate ?>%</span>
        </div>
    </div>

    <!-- Attendance Table (A4 Compact Layout for 40+ Records) -->
    <table class="w-full text-left text-xs border-collapse border border-slate-300 compact-table">
        <thead class="bg-slate-100 border-b border-slate-300 text-slate-700 uppercase font-bold text-[9.5pt]">
            <tr>
                <th class="border border-slate-300 py-1 px-1.5 text-center w-8">#</th>
                <th class="border border-slate-300 py-1 px-1.5 w-28">Admin ID</th>
                <th class="border border-slate-300 py-1 px-1.5">Student Full Name</th>
                <th class="border border-slate-300 py-1 px-1.5 text-center w-32">Attendance Status</th>
                <th class="border border-slate-300 py-1 px-1.5">Remarks / Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="5" class="border border-slate-300 p-4 text-center text-slate-500 italic">
                        No enrolled students in this class.
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                    $index = 1;
                    foreach ($students as $stu): 
                        $status = strtoupper((string) ($stu['current_status'] ?? 'P'));
                        $statusLabel = match($status) {
                            'P' => 'PRESENT',
                            'A' => 'ABSENT',
                            'L' => 'LATE',
                            'E' => 'EXCUSED',
                            default => 'PRESENT'
                        };
                        $statusClass = match($status) {
                            'P' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                            'A' => 'bg-rose-100 text-rose-800 border-rose-300',
                            'L' => 'bg-amber-100 text-amber-800 border-amber-300',
                            'E' => 'bg-blue-100 text-blue-800 border-blue-300',
                            default => 'bg-slate-100 text-slate-800 border-slate-300'
                        };
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
                        <td class="border border-slate-300 py-1 px-1 text-center">
                            <span class="inline-block px-1.5 py-0.2 rounded text-[8pt] font-bold border <?= $statusClass ?>">
                                <?= $statusLabel ?>
                            </span>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 text-[9pt] text-slate-600 italic">
                            <?= !empty($stu['current_remarks']) ? $e($stu['current_remarks']) : '-' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </table>

    <!-- Signature Section -->
    <div class="mt-12 pt-8 border-t border-gray-300 grid grid-cols-2 gap-12 text-xs">
        <div>
            <div class="border-b border-gray-400 pb-1 mb-1 w-64"></div>
            <span class="font-bold text-gray-900 block">Class Teacher Verification</span>
            <span class="text-gray-500 text-[10px]">Date Signed: ________________________</span>
        </div>
        <div class="text-right flex flex-col items-end">
            <div class="border-b border-gray-400 pb-1 mb-1 w-64"></div>
            <span class="font-bold text-gray-900 block">School Administrator / Principal</span>
            <span class="text-gray-500 text-[10px]">Verified Official Roll</span>
        </div>
    </div>
</div>
