<div>
    <!-- Document Header -->
    <div class="border-b-2 border-gray-900 pb-4 mb-6 flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900 uppercase">Class Communication Audit Log</h1>
            <p class="text-xs text-gray-600 mt-1"><?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> • Institutional Parent Engagement Record</p>
        </div>
        <div class="text-right text-xs">
            <div><strong>Generated:</strong> <?= date('F d, Y - H:i') ?></div>
            <div><strong>Class Teacher:</strong> <?= $e($teacher['name'] ?? 'Class Teacher') ?></div>
        </div>
    </div>

    <!-- Class Metadata Box -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6 flex flex-wrap justify-between items-center text-xs">
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Target Class</span>
            <span class="font-bold text-base text-gray-900"><?= $e($class['name'] . ' ' . $class['section']) ?> (Academic Year: <?= $e($class['academic_year']) ?>)</span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Reporting Period</span>
            <span class="font-bold text-gray-800">
                <?= !empty($dateFrom) ? $e($dateFrom) : 'All past' ?> to <?= !empty($dateTo) ? $e($dateTo) : 'Current Date' ?>
            </span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Total Logged Interactions</span>
            <span class="font-bold text-indigo-700 text-base"><?= count($calls) ?> Calls</span>
        </div>
    </div>

    <!-- Audit Table of Calls -->
    <table class="w-full text-left text-xs border-collapse border border-gray-300">
        <thead class="bg-gray-100 border-b border-gray-300 text-gray-700 uppercase font-bold text-[10px]">
            <tr>
                <th class="border border-gray-300 p-2">Date</th>
                <th class="border border-gray-300 p-2">Student & Admin ID</th>
                <th class="border border-gray-300 p-2">Category</th>
                <th class="border border-gray-300 p-2">Contact & Phone</th>
                <th class="border border-gray-300 p-2">Discussion Summary</th>
                <th class="border border-gray-300 p-2">Parent Response & Commitment</th>
                <th class="border border-gray-300 p-2">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($calls)): ?>
                <tr>
                    <td colspan="7" class="border border-gray-300 p-4 text-center text-gray-500 italic">
                        No parent interaction records found for this class and date range.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($calls as $c): ?>
                    <tr class="align-top hover:bg-gray-50">
                        <td class="border border-gray-300 p-2 font-mono whitespace-nowrap text-[11px]">
                            <?= date('Y-m-d', strtotime($c['call_timestamp'])) ?>
                        </td>
                        <td class="border border-gray-300 p-2">
                            <strong class="text-gray-900"><?= $e($c['first_name'] . ' ' . $c['last_name']) ?></strong>
                            <div class="font-mono text-[10px] text-indigo-700"><?= $e($c['student_admin_id']) ?></div>
                        </td>
                        <td class="border border-gray-300 p-2 font-semibold">
                            <?= $e($c['call_category']) ?>
                        </td>
                        <td class="border border-gray-300 p-2">
                            <div><?= $e($c['contact_person_name']) ?></div>
                            <div class="font-mono text-[10px] text-gray-500"><?= $e($c['phone_dialed']) ?></div>
                        </td>
                        <td class="border border-gray-300 p-2 text-[11px] leading-tight">
                            <?= $e($c['discussion_summary']) ?>
                        </td>
                        <td class="border border-gray-300 p-2 italic text-[11px] text-gray-800 leading-tight">
                            "<?= $e($c['parent_response']) ?>"
                        </td>
                        <td class="border border-gray-300 p-2 whitespace-nowrap font-medium text-[10px]">
                            <?= $e($c['status']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Signature / Sign-off Footer -->
    <div class="pt-10 border-t border-gray-300 grid grid-cols-2 gap-8 text-xs text-gray-600 mt-12">
        <div>
            <div class="border-b border-gray-400 pb-8 mb-1"></div>
            <span>Class Teacher Signature: <strong><?= $e($teacher['name'] ?? 'Class Teacher') ?></strong></span>
        </div>
        <div>
            <div class="border-b border-gray-400 pb-8 mb-1"></div>
            <span>Principal / Head of Institution Signature</span>
        </div>
    </div>
</div>
