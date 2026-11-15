<div>
    <!-- Document Header -->
    <div class="border-b-2 border-gray-900 pb-4 mb-6 flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900 uppercase">Parent Communication Audit Record</h1>
            <p class="text-xs text-gray-600 mt-1"><?= $e($teacher['school_name'] ?? 'Class Aid Suite') ?> • Official Teacher Records</p>
        </div>
        <div class="text-right text-xs">
            <div><strong>Generated:</strong> <?= date('F d, Y - H:i') ?></div>
            <div><strong>Class Teacher:</strong> <?= $e($teacher['name'] ?? 'Class Teacher') ?></div>
        </div>
    </div>

    <!-- Student & Guardian Metadata Box -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Student Name</span>
            <span class="font-bold text-sm text-gray-900"><?= $e($student['first_name'] . ' ' . $student['last_name']) ?></span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Student-Admin ID</span>
            <span class="font-mono font-bold text-sm text-indigo-700"><?= $e($student['student_admin_id']) ?></span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Class & Section</span>
            <span class="font-bold text-sm text-gray-900"><?= $e($student['class_name'] . ' - ' . $student['class_section']) ?></span>
        </div>
        <div>
            <span class="text-gray-500 uppercase block font-semibold text-[10px]">Guardian Contact</span>
            <span class="font-bold text-gray-900"><?= $e($student['primary_guardian_name']) ?> (<?= $e($student['primary_phone']) ?>)</span>
        </div>
    </div>

    <!-- Audit Table of Calls -->
    <div class="mb-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-800 mb-3">Communication Timeline (<?= count($calls) ?> Records)</h2>

        <?php if (empty($calls)): ?>
            <p class="text-xs text-gray-500 italic p-4 border rounded">No parent communications recorded for this student.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($calls as $idx => $call): ?>
                    <div class="border border-gray-300 rounded-lg p-4 text-xs">
                        <div class="flex justify-between items-center pb-2 mb-2 border-b border-gray-200">
                            <div>
                                <span class="font-bold text-sm">#<?= $idx + 1 ?> [<?= $e($call['call_category']) ?>]</span>
                                <span class="text-gray-600 ml-2">• Contact: <?= $e($call['contact_person_name']) ?> (<?= $e($call['relationship']) ?>)</span>
                            </div>
                            <div class="font-mono text-gray-600">
                                <?= date('Y-m-d H:i', strtotime($call['call_timestamp'])) ?> • Dialed: <?= $e($call['phone_dialed']) ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div>
                                <strong class="text-gray-700 block uppercase text-[10px]">Discussion / Reason:</strong>
                                <p class="text-gray-900 mt-1 whitespace-pre-line"><?= $e($call['discussion_summary']) ?></p>
                            </div>
                            <div>
                                <strong class="text-gray-700 block uppercase text-[10px]">Parent Response & Commitment:</strong>
                                <p class="text-gray-900 italic font-medium mt-1 whitespace-pre-line">"<?= $e($call['parent_response']) ?>"</p>
                            </div>
                        </div>

                        <?php if ($call['follow_up_required']): ?>
                            <div class="mt-2 pt-2 border-t border-gray-100 flex justify-between text-[11px] text-gray-600">
                                <span><strong>Follow-up Action:</strong> Scheduled for <?= $e($call['follow_up_date'] ?? 'Pending') ?></span>
                                <span><strong>Status:</strong> <?= $e($call['status']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

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
