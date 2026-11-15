<div>
    <!-- Document Header -->
    <div class="border-b-2 border-gray-900 pb-4 mb-6 flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900 uppercase">Parent Communication Audit Register</h1>
            <p class="text-xs text-gray-600 mt-1"><?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> • Complete Institutional Call Log</p>
        </div>
        <div class="text-right text-xs">
            <div><strong>Generated:</strong> <?= date('F d, Y - H:i') ?></div>
            <div><strong>Teacher:</strong> <?= $e($teacher['name'] ?? 'Class Teacher') ?></div>
        </div>
    </div>

    <!-- Metadata Box -->
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6 flex flex-wrap justify-between items-center text-xs">
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Filter Scope</span>
            <span class="font-bold text-sm text-gray-900">
                <?= $class ? $e($class['name'] . ' ' . $class['section']) : 'All Managed Classes' ?>
                <?= !empty($category) ? ' • Category: ' . $e(ucfirst($category)) : '' ?>
            </span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Reporting Date Period</span>
            <span class="font-bold text-gray-800">
                <?= !empty($dateFrom) ? $e($dateFrom) : 'All Historical Logs' ?> to <?= !empty($dateTo) ? $e($dateTo) : 'Current Date' ?>
            </span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Total Logged Interactions</span>
            <span class="font-bold text-indigo-700 text-sm"><?= count($calls) ?> Records</span>
        </div>
    </div>

    <!-- Calls Audit Table -->
    <table class="w-full text-left text-xs border-collapse border border-gray-300">
        <thead class="bg-gray-100 border-b border-gray-300 text-gray-700 uppercase font-bold text-[10px]">
            <tr>
                <th class="border border-gray-300 p-2 text-center w-10">#</th>
                <th class="border border-gray-300 p-2 w-24">Date & Time</th>
                <th class="border border-gray-300 p-2 w-36">Student & Admin ID</th>
                <th class="border border-gray-300 p-2 w-24">Category</th>
                <th class="border border-gray-300 p-2 w-36">Contact Person</th>
                <th class="border border-gray-300 p-2">Discussion Notes & Reason</th>
                <th class="border border-gray-300 p-2">Parent Response & Follow-up</th>
                <th class="border border-gray-300 p-2 text-center w-20">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($calls)): ?>
                <tr>
                    <td colspan="8" class="border border-gray-300 p-4 text-center text-gray-500 italic">
                        No parent communication logs found matching criteria.
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                    $idx = 1;
                    foreach ($calls as $c): 
                ?>
                    <tr class="align-top hover:bg-gray-50 <?= ($idx % 2 === 0) ? 'bg-gray-50/50' : '' ?>">
                        <td class="border border-gray-300 p-2 text-center font-mono text-[11px] text-gray-500">
                            <?= $idx++ ?>
                        </td>
                        <td class="border border-gray-300 p-2 font-mono whitespace-nowrap text-[10px] text-gray-700">
                            <?= date('Y-m-d', strtotime($c['call_timestamp'])) ?>
                            <div class="text-gray-400"><?= date('h:i A', strtotime($c['call_timestamp'])) ?></div>
                        </td>
                        <td class="border border-gray-300 p-2">
                            <strong class="text-gray-900 block"><?= $e($c['first_name'] . ' ' . $c['last_name']) ?></strong>
                            <span class="font-mono text-[10px] text-indigo-700">[<?= $e($c['student_admin_id']) ?>]</span>
                            <div class="text-[10px] text-gray-500"><?= $e($c['class_name'] ?? '') ?> - <?= $e($c['class_section'] ?? '') ?></div>
                        </td>
                        <td class="border border-gray-300 p-2">
                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-800 border border-slate-300">
                                <?= $e($c['call_category']) ?>
                            </span>
                        </td>
                        <td class="border border-gray-300 p-2 text-[11px]">
                            <div class="font-semibold text-gray-900"><?= $e($c['contact_person_name']) ?></div>
                            <div class="text-gray-500 text-[10px]"><?= $e($c['relationship']) ?> • <span class="font-mono"><?= $e($c['phone_dialed']) ?></span></div>
                        </td>
                        <td class="border border-gray-300 p-2 text-[11px] text-gray-800 leading-relaxed">
                            <?= $e($c['discussion_summary']) ?>
                        </td>
                        <td class="border border-gray-300 p-2 text-[11px] text-gray-700 italic leading-relaxed">
                            "<?= $e($c['parent_response']) ?>"
                            <?php if ($c['follow_up_required']): ?>
                                <div class="text-amber-700 font-bold not-italic text-[10px] mt-1">
                                    Follow-up: <?= !empty($c['follow_up_date']) ? date('M d, Y', strtotime($c['follow_up_date'])) : 'Pending' ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="border border-gray-300 p-2 text-center font-bold text-[10px] uppercase">
                            <span class="<?= $c['status'] === 'Resolved' ? 'text-emerald-700' : 'text-amber-700' ?>">
                                <?= $e($c['status']) ?>
                            </span>
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
            <span class="font-bold text-gray-900 block">Class Teacher Signature</span>
            <span class="text-gray-500 text-[10px]">Date Signed: ________________________</span>
        </div>
        <div class="text-right flex flex-col items-end">
            <div class="border-b border-gray-400 pb-1 mb-1 w-64"></div>
            <span class="font-bold text-gray-900 block">Principal / Student Affairs Officer</span>
            <span class="text-gray-500 text-[10px]">Official Institutional Log Verification</span>
        </div>
    </div>
</div>
