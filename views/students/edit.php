<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                Edit Student Profile
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Updating record for <?= $e($student['first_name'] . ' ' . $student['last_name']) ?> (<?= $e($student['student_admin_id']) ?>)
            </p>
        </div>
        <a href="/students/<?= $student['id'] ?>" class="text-xs text-slate-500 dark:text-slate-400 hover:text-indigo-500 flex items-center gap-1 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Cancel</span>
        </a>
    </div>

    <form method="POST" action="/students/<?= $student['id'] ?>" class="glass-card p-6 sm:p-8 space-y-6">
        <?= $csrf() ?>

        <!-- Universal Student Admin ID & Class -->
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-3 flex items-center gap-1.5">
                <i data-lucide="fingerprint" class="w-4 h-4"></i>
                <span>Institutional Identification</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Universal Student-Admin ID *
                    </label>
                    <input type="text" 
                           name="student_admin_id" 
                           value="<?= $e($student['student_admin_id']) ?>"
                           required 
                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input font-mono tabular-nums">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Assigned Class & Section *
                    </label>
                    <select name="class_id" required class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($student['class_id'] == $c['id']) ? 'selected' : '' ?>>
                                <?= $e($c['name']) ?> - <?= $e($c['section']) ?> <?= $c['is_home_class'] ? '(Home Class)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Student Personal Details -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
            <h2 class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-3 flex items-center gap-1.5">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Student Personal Information</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">First Name *</label>
                    <input type="text" name="first_name" value="<?= $e($student['first_name']) ?>" required class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Last Name *</label>
                    <input type="text" name="last_name" value="<?= $e($student['last_name']) ?>" required class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Gender</label>
                    <select name="gender" class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                        <option value="Male" <?= $student['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $student['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= $student['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Date of Birth</label>
                    <input type="date" name="dob" value="<?= $e($student['dob']) ?>" class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input tabular-nums">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Enrollment Status</label>
                    <select name="status" class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                        <option value="active" <?= ($student['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($student['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="transferred" <?= ($student['status'] ?? '') === 'transferred' ? 'selected' : '' ?>>Transferred</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Residential Address</label>
                    <input type="text" name="address" value="<?= $e($student['address']) ?>" class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                </div>
            </div>
        </div>

        <!-- Guardian & Contact -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
            <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-3 flex items-center gap-1.5">
                <i data-lucide="phone-call" class="w-4 h-4"></i>
                <span>Primary Parent / Guardian Contact Details</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Primary Guardian Name *</label>
                    <input type="text" name="primary_guardian_name" value="<?= $e($student['primary_guardian_name']) ?>" required class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Relationship *</label>
                    <select name="primary_guardian_relation" class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                        <option value="Mother" <?= $student['primary_guardian_relation'] === 'Mother' ? 'selected' : '' ?>>Mother</option>
                        <option value="Father" <?= $student['primary_guardian_relation'] === 'Father' ? 'selected' : '' ?>>Father</option>
                        <option value="Guardian" <?= $student['primary_guardian_relation'] === 'Guardian' ? 'selected' : '' ?>>Legal Guardian</option>
                        <option value="Grandparent" <?= $student['primary_guardian_relation'] === 'Grandparent' ? 'selected' : '' ?>>Grandparent</option>
                        <option value="Other" <?= $student['primary_guardian_relation'] === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Primary Phone Number *</label>
                    <input type="tel" name="primary_phone" value="<?= $e($student['primary_phone']) ?>" required class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input font-mono tabular-nums">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Secondary / Emergency Phone</label>
                    <input type="tel" name="secondary_phone" value="<?= $e($student['secondary_phone']) ?>" class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input font-mono tabular-nums">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Guardian Email Address</label>
                    <input type="email" name="guardian_email" value="<?= $e($student['guardian_email']) ?>" class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                </div>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3">
            <a href="/students/<?= $student['id'] ?>" class="px-4 py-2.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm flex items-center gap-2 transition-all cursor-pointer">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Update Student Record</span>
            </button>
        </div>
    </form>

</div>
