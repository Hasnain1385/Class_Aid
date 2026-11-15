<div>
    <!-- Document Header -->
    <div class="border-b-2 border-slate-900 pb-2.5 mb-3 flex justify-between items-start">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 uppercase">Class Communication Audit Log</h1>
            <p class="text-xs text-slate-600 mt-0.5"><?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> • Institutional Parent Engagement Record</p>
        </div>
        <div class="text-right text-xs">
            <div><strong>Generated:</strong> <?= date('F d, Y - H:i') ?></div>
            <div><strong>Class Teacher:</strong> <?= $e($teacher['name'] ?? 'Class Teacher') ?></div>
        </div>
    </div>

    <!-- Class Metadata Box -->
    <div class="bg-slate-50 border border-slate-300 rounded-xl p-2.5 mb-3.5 flex flex-wrap justify-between items-center text-xs gap-3">
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Target Class</span>
            <span class="font-bold text-sm text-slate-900"><?= $e($class['name'] . ' ' . $class['section']) ?> (Academic Year: <?= $e($class['academic_year']) ?>)</span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Reporting Period</span>
            <span class="font-bold text-slate-800 text-xs">
                <?= !empty($dateFrom) ? $e($dateFrom) : 'All past' ?> to <?= !empty($dateTo) ? $e($dateTo) : 'Current Date' ?>
            </span>
        </div>
        <div>
            <span class="text-slate-500 uppercase block font-semibold text-[10px]">Total Logged Interactions</span>
            <span class="font-bold text-indigo-700 text-sm"><?= count($calls) ?> Calls Recorded</span>
        </div>
    </div>

    <!-- Audit Table of Calls (Compact Proportional Grid) -->
    <table class="w-full table-fixed text-left text-xs border-collapse border border-slate-300 compact-table">
        <thead class="bg-slate-100 border-b border-slate-300 text-slate-700 uppercase font-bold text-[9pt]">
            <tr>
                <th class="border border-slate-300 py-1 px-1.5 w-[11%]">Date</th>
                <th class="border border-slate-300 py-1 px-1.5 w-[17%]">Student &amp; Admin ID</th>
                <th class="border border-slate-300 py-1 px-1.5 w-[11%]">Category</th>
                <th class="border border-slate-300 py-1 px-1.5 w-[15%]">Contact &amp; Phone</th>
                <th class="border border-slate-300 py-1 px-1.5 w-[23%]">Discussion Summary</th>
                <th class="border border-slate-300 py-1 px-1.5 w-[15%]">Parent Response</th>
                <th class="border border-slate-300 py-1 px-1.5 w-[8%] text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($calls)): ?>
                <tr>
                    <td colspan="7" class="border border-slate-300 p-4 text-center text-slate-500 italic">
                        No parent interaction records found for this class and date range.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($calls as $c): ?>
                    <tr class="align-top hover:bg-slate-50" style="page-break-inside: avoid;">
                        <td class="border border-slate-300 py-1 px-1.5 font-mono whitespace-nowrap text-[8.5pt]">
                            <?= date('Y-m-d', strtotime($c['call_timestamp'])) ?>
                            <div class="text-slate-400 text-[8pt]"><?= date('h:i A', strtotime($c['call_timestamp'])) ?></div>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5">
                            <strong class="text-slate-900 block text-[9pt]"><?= $e($c['first_name'] . ' ' . $c['last_name']) ?></strong>
                            <div class="font-mono text-[8pt] text-indigo-700">[<?= $e($c['student_admin_id']) ?>]</div>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5">
                            <span class="inline-block px-1.5 py-0.2 rounded text-[7.5pt] font-bold uppercase bg-slate-100 text-slate-800 border border-slate-300">
                                <?= $e($c['call_category']) ?>
                            </span>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 text-[8.5pt]">
                            <div class="font-semibold text-slate-900 truncate"><?= $e($c['contact_person_name']) ?></div>
                            <div class="font-mono text-[8pt] text-slate-500"><?= $e($c['phone_dialed']) ?></div>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 text-[8.5pt] leading-snug break-words">
                            <?= $e($c['discussion_summary']) ?>
                        </td>
                        <td class="border border-slate-300 py-1 px-1.5 italic text-[8.5pt] text-slate-800 leading-snug break-words">
                            "<?= $e($c['parent_response']) ?>"
                        </td>
                        <td class="border border-slate-300 py-1 px-1 text-center whitespace-nowrap font-medium text-[8pt]">
                            <span class="inline-block px-1 py-0.2 rounded <?= $c['status'] === 'Resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                                <?= $e($c['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Signature / Sign-off Footer -->
    <div class="avoid-break pt-6 border-t border-slate-300 grid grid-cols-2 gap-8 text-xs text-slate-600 mt-6">
        <div>
            <div class="border-b border-slate-400 pb-6 mb-1"></div>
            <span>Class Teacher Signature: <strong><?= $e($teacher['name'] ?? 'Class Teacher') ?></strong></span>
        </div>
        <div>
            <div class="border-b border-slate-400 pb-6 mb-1"></div>
            <span>Principal / Head of Institution Signature</span>
        </div>
    </div>
</div>
