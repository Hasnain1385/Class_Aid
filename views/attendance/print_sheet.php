<div>
    <!-- Document Header -->
    <div class="border-b-2 border-gray-900 pb-4 mb-6 flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900 uppercase">Daily Attendance Register & Roll Call</h1>
            <p class="text-xs text-gray-600 mt-1"><?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> • Official Attendance Records</p>
        </div>
        <div class="text-right text-xs">
            <div><strong>Generated:</strong> <?= date('F d, Y - H:i') ?></div>
            <div><strong>Teacher:</strong> <?= $e($teacher['name'] ?? 'Class Teacher') ?></div>
        </div>
    </div>

    <!-- Metadata & Attendance Stats Box -->
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Class Roster</span>
            <span class="font-bold text-sm text-gray-900"><?= $e($class['name'] . ' - ' . $class['section']) ?></span>
            <span class="text-gray-600 block text-[11px]">Academic Year: <?= $e($class['academic_year']) ?></span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Attendance Date</span>
            <span class="font-bold text-sm text-gray-900"><?= date('l, F d, Y', strtotime($date)) ?></span>
            <span class="text-indigo-600 block text-[11px] font-mono"><?= $e($date) ?></span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Roll Summary</span>
            <span class="font-bold text-emerald-700 text-sm">Present: <?= $summary['present'] ?></span>
            <span class="text-rose-700 block text-[11px]">Absent: <?= $summary['absent'] ?> | Late: <?= $summary['late'] ?></span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Total Enrolled</span>
            <span class="font-bold text-gray-900 text-sm"><?= count($students) ?> Students</span>
            <?php 
                $rate = count($students) > 0 ? round(($summary['present'] / count($students)) * 100, 1) : 0;
            ?>
            <span class="text-indigo-700 block text-[11px] font-semibold">Attendance Rate: <?= $rate ?>%</span>
        </div>
    </div>

    <!-- Attendance Table -->
    <table class="w-full text-left text-xs border-collapse border border-gray-300">
        <thead class="bg-gray-100 border-b border-gray-300 text-gray-700 uppercase font-bold text-[10px]">
            <tr>
                <th class="border border-gray-300 p-2 text-center w-12">#</th>
                <th class="border border-gray-300 p-2 w-32">Admin ID</th>
                <th class="border border-gray-300 p-2">Student Full Name</th>
                <th class="border border-gray-300 p-2 text-center w-36">Attendance Status</th>
                <th class="border border-gray-300 p-2">Remarks / Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="5" class="border border-gray-300 p-4 text-center text-gray-500 italic">
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
                            default => 'bg-gray-100 text-gray-800 border-gray-300'
                        };
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
                        <td class="border border-gray-300 p-2 text-center">
                            <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold border <?= $statusClass ?>">
                                <?= $statusLabel ?>
                            </span>
                        </td>
                        <td class="border border-gray-300 p-2 text-[11px] text-gray-600 italic">
                            <?= !empty($stu['current_remarks']) ? $e($stu['current_remarks']) : '-' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
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
