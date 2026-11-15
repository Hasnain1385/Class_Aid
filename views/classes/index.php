<div class="space-y-6">

    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="book-open" class="w-6 h-6 text-indigo-500"></i>
                <span>Class Rosters & Subjects</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Manage your primary Home Class and other subject classes, with multi-subject curriculum assignments.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Clean Mock / Demo Data Button -->
            <button type="button" 
                    onclick="document.getElementById('modal-clean-mock-data').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-all cursor-pointer">
                <i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i>
                <span>Clean Mock Data</span>
            </button>

            <!-- Create New Class Button -->
            <button type="button" 
                    onclick="document.getElementById('modal-create-class').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Create New Class</span>
            </button>
        </div>
    </div>

    <!-- Class Cards Grid -->
    <?php if (empty($classes)): ?>
        <div class="glass-card p-12 text-center text-slate-500 dark:text-slate-400">
            <i data-lucide="book-open" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">No classes configured</h3>
            <p class="text-xs mt-1">Get started by creating your first primary class roster or subject section.</p>
            <button type="button" 
                    onclick="document.getElementById('modal-create-class').classList.remove('hidden')"
                    class="mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold shadow-sm transition-all">
                Create First Class
            </button>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($classes as $c): ?>
                <div class="glass-card p-5 space-y-4 relative <?= $c['is_home_class'] ? 'border-2 border-indigo-500/50 bg-indigo-50/20 dark:bg-indigo-950/20' : '' ?>">
                    
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                                    <?= $e($c['name']) ?> - <?= $e($c['section']) ?>
                                </h2>
                                <?php if ($c['is_home_class']): ?>
                                    <span class="text-xs px-2 py-0.5 rounded-full badge-indigo font-bold uppercase">
                                        Primary Home Class
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 tabular-nums">
                                Academic Year: <?= $e($c['academic_year']) ?> • <?= $c['student_count'] ?> Enrolled Students
                            </p>
                        </div>

                        <!-- Action Toolbar -->
                        <div class="flex items-center gap-1">
                            <a href="/students/import?class_id=<?= $c['id'] ?>" class="p-2 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 transition-colors" title="Bulk Import Students (CSV / OCR)">
                                <i data-lucide="scan-line" class="w-4 h-4"></i>
                            </a>
                            <a href="/attendance/sheet/<?= $c['id'] ?>" class="p-2 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-500/20 transition-colors" title="Roll Call">
                                <i data-lucide="calendar-check" class="w-4 h-4"></i>
                            </a>
                            <a href="/students?class_id=<?= $c['id'] ?>" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" title="Students">
                                <i data-lucide="users" class="w-4 h-4"></i>
                            </a>
                            <!-- Edit Class Button -->
                            <button type="button" 
                                    onclick="openEditClassModal(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>)"
                                    class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer" 
                                    title="Edit Class Details">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </button>
                            <!-- Delete Class Button -->
                            <button type="button" 
                                    onclick="openDeleteClassModal(<?= $c['id'] ?>, '<?= $e($c['name'] . ' - ' . $c['section']) ?>', <?= (int)$c['student_count'] ?>)"
                                    class="p-2 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 transition-colors cursor-pointer" 
                                    title="Delete Class">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Subjects taught in this class -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Assigned Subjects (<?= count($c['subjects']) ?>)
                            </span>
                            <button type="button" 
                                    onclick="openAddSubjectModal(<?= $c['id'] ?>, '<?= $e($c['name'] . ' ' . $c['section']) ?>')" 
                                    class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold flex items-center gap-1 cursor-pointer">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Add Subject</span>
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            <?php if (empty($c['subjects'])): ?>
                                <span class="text-xs text-slate-400 italic">No specific subjects defined yet.</span>
                            <?php else: ?>
                                <?php foreach ($c['subjects'] as $sub): ?>
                                    <span class="pl-2.5 pr-1.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-medium text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 flex items-center gap-1.5 group">
                                        <span><?= $e($sub['name']) ?></span>
                                        <?php if (!empty($sub['code'])): ?>
                                            <span class="font-mono text-xs text-indigo-600 dark:text-indigo-400 font-semibold tabular-nums"><?= $e($sub['code']) ?></span>
                                        <?php endif; ?>
                                        <!-- Remove Subject Button -->
                                        <button type="button" 
                                                onclick="openDeleteSubjectModal(<?= $sub['id'] ?>, '<?= $e($sub['name']) ?>')"
                                                class="text-slate-400 hover:text-rose-500 dark:hover:text-rose-400 p-0.5 rounded transition-colors"
                                                title="Remove Subject">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Modal: Create New Class -->
<div id="modal-create-class" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="glass-card w-full max-w-md p-6 space-y-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Create New Class Roster</h3>
            <button type="button" onclick="document.getElementById('modal-create-class').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="/classes" class="space-y-4">
            <?= $csrf() ?>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Class Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Grade 10" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Section *</label>
                    <input type="text" name="section" required placeholder="e.g. A" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Academic Year</label>
                <input type="text" name="academic_year" value="<?= date('Y') ?>-<?= date('Y') + 1 ?>" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono tabular-nums">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Initial Subject</label>
                    <input type="text" name="initial_subject" placeholder="e.g. Mathematics" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Subject Code</label>
                    <input type="text" name="initial_code" placeholder="e.g. MTH-10" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_home_class" value="1" id="modal_is_home" class="rounded text-indigo-600">
                <label for="modal_is_home" class="text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                    Designate as Primary Home Class
                </label>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-create-class').classList.add('hidden')" class="px-4 py-2 text-xs rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-sm transition-all">
                    Create Class
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Class -->
<div id="modal-edit-class" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="glass-card w-full max-w-md p-6 space-y-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Class Details</h3>
            <button type="button" onclick="document.getElementById('modal-edit-class').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-edit-class" method="POST" action="" class="space-y-4">
            <?= $csrf() ?>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Class Name *</label>
                    <input type="text" name="name" id="edit-class-name" required class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Section *</label>
                    <input type="text" name="section" id="edit-class-section" required class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Academic Year</label>
                <input type="text" name="academic_year" id="edit-class-year" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono tabular-nums">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_home_class" value="1" id="edit-class-home" class="rounded text-indigo-600">
                <label for="edit-class-home" class="text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                    Designate as Primary Home Class
                </label>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-edit-class').classList.add('hidden')" class="px-4 py-2 text-xs rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-sm transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Delete Class Confirmation -->
<div id="modal-delete-class" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="glass-card w-full max-w-md p-6 space-y-4 bg-white dark:bg-slate-900 border border-rose-500/40 shadow-2xl">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Delete Class Roster</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400" id="delete-class-title"></p>
            </div>
        </div>

        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed" id="delete-class-warning">
            Are you sure you want to remove this class? All associated enrolled students, attendance records, exam marks, and subjects will be permanently deleted.
        </p>

        <form id="form-delete-class" method="POST" action="" class="pt-2 flex justify-end gap-2">
            <?= $csrf() ?>
            <button type="button" onclick="document.getElementById('modal-delete-class').classList.add('hidden')" class="px-4 py-2 text-xs rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                Cancel
            </button>
            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-sm transition-all">
                Yes, Delete Class
            </button>
        </form>
    </div>
</div>

<!-- Modal: Delete Subject Confirmation -->
<div id="modal-delete-subject" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="glass-card w-full max-w-sm p-6 space-y-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Remove Subject</h3>
            <button type="button" onclick="document.getElementById('modal-delete-subject').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <p class="text-xs text-slate-600 dark:text-slate-300" id="delete-subject-msg">
            Are you sure you want to remove this subject?
        </p>
        <form id="form-delete-subject" method="POST" action="" class="pt-2 flex justify-end gap-2">
            <?= $csrf() ?>
            <button type="button" onclick="document.getElementById('modal-delete-subject').classList.add('hidden')" class="px-4 py-2 text-xs rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                Cancel
            </button>
            <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-sm transition-all">
                Remove Subject
            </button>
        </form>
    </div>
</div>

<!-- Modal: Clean Mock / Demo Data -->
<div id="modal-clean-mock-data" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="glass-card w-full max-w-md p-6 space-y-4 bg-white dark:bg-slate-900 border border-amber-500/40 shadow-2xl">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Clean Mock / Demo Data</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Clean demo seed records for actual school usage</p>
            </div>
        </div>

        <form method="POST" action="/classes/clean-mock-data" class="space-y-4">
            <?= $csrf() ?>

            <div class="space-y-2.5 text-xs text-slate-700 dark:text-slate-300">
                <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer">
                    <input type="radio" name="clean_target" value="demo_students" checked class="mt-0.5 text-indigo-600">
                    <div>
                        <strong class="block text-slate-900 dark:text-white font-semibold">Clean Mock Demo Students Only (Recommended)</strong>
                        <span class="text-slate-500 dark:text-slate-400 text-[11px] block mt-0.5">
                            Removes sample students (ADM-2026-%), sample calls, and attendance logs. Keeps your class structures and subjects intact so you can import real students.
                        </span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 rounded-xl border border-rose-200 dark:border-rose-900/40 hover:bg-rose-50/30 dark:hover:bg-rose-950/20 cursor-pointer">
                    <input type="radio" name="clean_target" value="all_classes" class="mt-0.5 text-rose-600">
                    <div>
                        <strong class="block text-rose-600 dark:text-rose-400 font-semibold">Clean All Classes & Rosters (Fresh Start)</strong>
                        <span class="text-slate-500 dark:text-slate-400 text-[11px] block mt-0.5">
                            Removes all classes, subjects, rosters, and exams. Gives you a completely clean empty slate. Your account and SaaS subscription remain untouched.
                        </span>
                    </div>
                </label>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-clean-mock-data').classList.add('hidden')" class="px-4 py-2 text-xs rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 rounded-xl shadow-sm transition-all">
                    Clean Up Selected Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add Subject -->
<div id="modal-add-subject" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="glass-card w-full max-w-sm p-6 space-y-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Add Subject</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" id="add-subject-class-name"></p>
            </div>
            <button type="button" onclick="document.getElementById('modal-add-subject').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="/classes/subjects" class="space-y-4">
            <?= $csrf() ?>
            <input type="hidden" name="class_id" id="add-subject-class-id">

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Subject Name *</label>
                <input type="text" name="name" required placeholder="e.g. Computer Science" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Subject Code</label>
                <input type="text" name="code" placeholder="e.g. CS-101" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono">
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-add-subject').classList.add('hidden')" class="px-4 py-2 text-xs rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-sm transition-all">
                    Save Subject
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddSubjectModal(classId, className) {
        document.getElementById('add-subject-class-id').value = classId;
        document.getElementById('add-subject-class-name').innerText = 'To ' + className;
        document.getElementById('modal-add-subject').classList.remove('hidden');
    }

    function openEditClassModal(classData) {
        document.getElementById('form-edit-class').action = '/classes/update/' + classData.id;
        document.getElementById('edit-class-name').value = classData.name || '';
        document.getElementById('edit-class-section').value = classData.section || '';
        document.getElementById('edit-class-year').value = classData.academic_year || '';
        document.getElementById('edit-class-home').checked = (classData.is_home_class == 1);
        document.getElementById('modal-edit-class').classList.remove('hidden');
    }

    function openDeleteClassModal(classId, className, studentCount) {
        document.getElementById('form-delete-class').action = '/classes/delete/' + classId;
        document.getElementById('delete-class-title').innerText = className;
        document.getElementById('delete-class-warning').innerText = 'Are you sure you want to remove ' + className + '? ' + 
            (studentCount > 0 ? 'This class currently has ' + studentCount + ' enrolled students. ' : '') + 
            'All associated student records, attendance registers, and marks will be permanently removed.';
        document.getElementById('modal-delete-class').classList.remove('hidden');
    }

    function openDeleteSubjectModal(subjectId, subjectName) {
        document.getElementById('form-delete-subject').action = '/classes/subjects/delete/' + subjectId;
        document.getElementById('delete-subject-msg').innerText = 'Are you sure you want to remove subject "' + subjectName + '"?';
        document.getElementById('modal-delete-subject').classList.remove('hidden');
    }
</script>
