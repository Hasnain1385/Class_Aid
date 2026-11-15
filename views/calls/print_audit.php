<div>
    <!-- Document Header -->
    <div class="border-b-2 border-slate-900 pb-2.5 mb-3 flex justify-between items-start">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 uppercase">Parent Communication Audit Register</h1>
            <p class="text-xs text-slate-600 mt-0.5"><?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> • Complete Institutional Call Log</p>
        </div>
        <div class="text-right text-xs">
            <div><strong>Generated:</strong> <?= date('F d, Y - H:i') ?></div>
            <div><strong>Teacher:</strong> <?= $e($teacher['name'] ?? 'Class Teacher') ?></div>
        </div>
    </div>

    <!-- Metadata Box -->
    <div class="bg-slate-50 border border-slate-300 rounded-xl p-2.5 mb-3.5 flex flex-wrap justify-between items-center text-xs gap-3">
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Filter Scope</span>
            <span class="font-bold text-sm text-slate-900">
                <?= $class ? $e($class['name'] . ' ' . $class['section']) : 'All Managed Classes' ?>
                <?= !empty($category) ? ' • Category: ' . $e(ucfirst($category)) : '' ?>
            </span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Reporting Date Period</span>
            <span class="font-bold text-slate-800 text-xs">
                <?= !empty($dateFrom) ? $e($dateFrom) : 'All Historical Logs' ?> to <?= !empty($dateTo) ? $e($dateTo) : 'Current Date' ?>
            </span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Total Logged Interactions</span>
            <span class="font-bold text-indigo-700 text-sm"><?= count($calls) ?> Records</span>
        </div>
    </div>

    <!-- Calls Audit Table (Compact Proportional Grid) -->
    <table class="w-full table-fixed text-left text-xs border-collapse border border-slate-300 compact-table">
        <thead class="bg-slate-100 border-b border-slate-300 text-slate-700 uppercase font-bold text-[9pt]">
            <tr>
                <th class="border border-slate-300 py-1 px-1 text-center w-7">#</th>
                <th class="border border-slate-300 py-1 px-1.5 w-[11%]">Date &amp; Time</th>
                <th class="border border-slate-300 py-1 px-1.5 w-[17%]">Student &amp; Admin ID</th>
                <th class="border border-slate-300 py-1 px-1.5 w-[11%]">Category</th>
                <th class="border border-slate-300 py-1 px-1.5 w-[15%]">Contact Person</th>
                <th class="border border-slate-300 py-1 px-1.5 w-[23%]">Discussion Notes &amp; Reason</th>
                <th class="border border-slate-300 py-1 px-1.5 w-[15%]">Parent Response</th>
                <th class="border border-slate-300 py-1 px-1 text-center w-[8%]">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($calls)): ?>
                <tr>
                    <td colspan="8" class="border border-slate-300 p-4 text-center text-slate-500 italic">
                        No parent communication logs found matching criteria.
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                    $idx = 1;
                    foreach ($calls as $c): 
                ?>
                    <tr class="align-top hover:bg-slate-50 <?= ($idx % 2 === 0) ? 'bg-slate-50/50' : '' ?>" style="page-break-inside: avoid;">
                        <td class="border border-slate-300 py-1 px-1 text-center font-mono text-[9pt] text-slate-500">
                            <?= $idx++ ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 font-mono whitespace-nowrap text-[8.5pt] text-slate-700">
                            <?= date('Y-m-d', strtotime($c['call_timestamp'])) ?>
                            <div class="text-slate-400 text-[8pt]"><?= date('h:i A', strtotime($c['call_timestamp'])) ?></div>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5">
                            <strong class="text-slate-900 block text-[9pt]"><?= $e($c['first_name'] . ' ' . $c['last_name']) ?></strong>
                            <span class="font-mono text-[8pt] text-indigo-700">[<?= $e($c['student_admin_id']) ?>]</span>
                            <div class="text-[8pt] text-slate-500"><?= $e($c['class_name'] ?? '') ?> - <?= $e($c['class_section'] ?? '') ?></div>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5">
                            <span class="inline-block px-1.5 py-0.2 rounded text-[7.5pt] font-bold uppercase bg-slate-100 text-slate-800 border border-slate-300">
                                <?= $e($c['call_category']) ?>
                            </span>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 text-[8.5pt]">
                            <div class="font-semibold text-slate-900 truncate"><?= $e($c['contact_person_name']) ?></div>
                            <div class="text-slate-500 text-[8pt]"><?= $e($c['relationship']) ?> • <span class="font-mono"><?= $e($c['phone_dialed']) ?></span></div>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 text-[8.5pt] text-slate-800 leading-snug break-words">
                            <?= $e($c['discussion_summary']) ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 text-[8.5pt] text-slate-700 italic leading-snug break-words">
                            "<?= $e($c['parent_response']) ?>"
                            <?php if ($c['follow_up_required']): ?>
                                <div class="text-amber-700 font-bold not-italic text-[8pt] mt-0.5">
                                    Follow-up: <?= !empty($c['follow_up_date']) ? date('M d, Y', strtotime($c['follow_up_date'])) : 'Pending' ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1 text-center font-bold text-[8pt] uppercase">
                            <span class="inline-block px-1 py-0.2 rounded <?= $c['status'] === 'Resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                                <?= $e($c['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Signature Section -->
    <div class="avoid-break mt-6 pt-6 border-t border-slate-300 grid grid-cols-2 gap-12 text-xs">
        <div>
            <div class="border-b border-slate-400 pb-1 mb-1 w-64"></div>
            <span class="font-bold text-slate-900 block">Class Teacher Signature</span>
            <span class="text-slate-500 text-[10px]">Date Signed: ________________________</span>
        </div>
        <div class="text-right flex flex-col items-end">
            <div class="border-b border-slate-400 pb-1 mb-1 w-64"></div>
            <span class="font-bold text-slate-900 block">Principal / Student Affairs Officer</span>
            <span class="text-slate-500 text-[10px]">Official Institutional Log Verification</span>
        </div>
    </div>
</div>
