<div class="space-y-6">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="<?= \App\Core\Session::isSuperAdmin() ? '/admin' : '/dashboard' ?>" class="text-xs text-slate-400 hover:text-indigo-500 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Dashboard</span>
                </a>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs font-semibold text-indigo-500 uppercase">Account Settings</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                User Profile & Credentials
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Manage your educator identity, custom profile avatar, contact details, and password security.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl text-xs font-semibold <?= $user['role'] === 'super_admin' ? 'badge-rose' : 'badge-indigo' ?>">
                <?= $user['role'] === 'super_admin' ? 'Super Administrator' : 'Classroom Educator' ?>
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Avatar & Account Snapshot -->
        <div class="space-y-6">

            <!-- Avatar & Identity Card -->
            <div class="glass-card p-6 text-center">
                <form method="POST" action="/profile" enctype="multipart/form-data" id="avatar-form">
                    <?= $csrf() ?>
                    <input type="hidden" name="name" value="<?= $e($user['name']) ?>">
                    <input type="hidden" name="email" value="<?= $e($user['email']) ?>">
                    <input type="hidden" name="school_name" value="<?= $e($user['school_name'] ?? '') ?>">
                    <input type="hidden" name="phone" value="<?= $e($user['phone'] ?? '') ?>">

                    <!-- Profile Photo with Hover Overlay -->
                    <div class="relative w-28 h-28 mx-auto mb-4 group cursor-pointer" onclick="document.getElementById('avatar-input').click()">
                        <?php if (!empty($user['avatar']) && file_exists(dirname(__DIR__, 2) . '/public' . $user['avatar'])): ?>
                            <img id="avatar-preview" src="<?= $e($user['avatar']) ?>" alt="Avatar" class="w-28 h-28 rounded-2xl object-cover shadow-lg ring-2 ring-indigo-500/30 group-hover:opacity-80 transition-opacity">
                        <?php else: ?>
                            <div id="avatar-fallback" class="w-28 h-28 rounded-2xl bg-indigo-600 text-white font-black text-3xl flex items-center justify-center shadow-md ring-2 ring-indigo-500/30 group-hover:scale-[1.02] transition-transform">
                                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <img id="avatar-preview" src="" alt="Avatar" class="w-28 h-28 rounded-2xl object-cover shadow-lg ring-2 ring-indigo-500/30 hidden">
                        <?php endif; ?>

                        <!-- Camera Overlay -->
                        <div class="absolute inset-0 rounded-2xl bg-black/50 text-white flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <i data-lucide="camera" class="w-6 h-6 mb-1"></i>
                            <span class="text-xs font-bold">Change</span>
                        </div>
                    </div>

                    <!-- Hidden File Input -->
                    <input type="file" id="avatar-input" name="avatar" class="hidden" accept="image/png,image/jpeg,image/webp,image/gif" onchange="handleAvatarSelected(this)">

                    <h2 class="text-base font-bold text-slate-900 dark:text-white"><?= $e($user['name']) ?></h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-mono"><?= $e($user['email']) ?></p>

                    <div class="mt-3 flex items-center justify-center gap-2">
                        <button type="button" onclick="document.getElementById('avatar-input').click()" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 transition-all flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="upload" class="w-3.5 h-3.5 text-indigo-500"></i>
                            <span>Upload Photo</span>
                        </button>

                        <?php if (!empty($user['avatar'])): ?>
                            <button type="submit" formaction="/profile/avatar/remove" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer" title="Remove Profile Photo">
                                Remove
                            </button>
                        <?php endif; ?>
                    </div>

                    <div id="avatar-upload-prompt" class="mt-3 hidden p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 text-xs border border-indigo-200 dark:border-indigo-800">
                        <span>New photo selected. Click below to save:</span>
                        <button type="submit" class="mt-1.5 w-full py-1.5 px-3 rounded-lg bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-500 shadow-sm cursor-pointer">
                            Save Profile Photo
                        </button>
                    </div>
                </form>
            </div>

            <!-- Workspace Snapshot -->
            <div class="glass-card p-5 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Workspace Summary
                </h3>

                <div class="space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">School / Organization</span>
                        <strong class="text-slate-800 dark:text-slate-200 truncate max-w-[160px]"><?= $e($user['school_name'] ?? 'Not set') ?></strong>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Managed Classes</span>
                        <strong class="text-slate-800 dark:text-slate-200 font-mono tabular-nums"><?= $classesCount ?></strong>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Total Enrolled Students</span>
                        <strong class="text-slate-800 dark:text-slate-200 font-mono tabular-nums"><?= $studentsCount ?></strong>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Account Created</span>
                        <span class="text-slate-800 dark:text-slate-200 font-mono tabular-nums"><?= !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : date('M d, Y') ?></span>
                    </div>

                    <?php if ($subscription): ?>
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">SaaS Plan</span>
                            <a href="/subscription" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                <span><?= $e($subscription['plan_name']) ?></span>
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Right Column: Edit Details & Security -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card 1: Personal Details Form -->
            <div class="glass-card p-6">
                <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="user-check" class="w-4 h-4 text-indigo-500"></i>
                        <span>Account Information</span>
                    </h2>
                    <span class="text-xs text-slate-400">Update your public identity</span>
                </div>

                <form method="POST" action="/profile" class="space-y-4">
                    <?= $csrf() ?>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Full Name <span class="text-rose-500">*</span>
                            </label>
                            <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                                <div class="input-icon-box">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                </div>
                                <input type="text" id="name" name="name" value="<?= $e($user['name']) ?>" required class="font-medium">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Email Address <span class="text-rose-500">*</span>
                            </label>
                            <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                                <div class="input-icon-box">
                                    <i data-lucide="mail" class="w-4 h-4"></i>
                                </div>
                                <input type="email" id="email" name="email" value="<?= $e($user['email']) ?>" required class="font-medium font-mono">
                            </div>
                        </div>

                        <div>
                            <label for="school_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                School / Institution Name
                            </label>
                            <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                                <div class="input-icon-box">
                                    <i data-lucide="building" class="w-4 h-4"></i>
                                </div>
                                <input type="text" id="school_name" name="school_name" value="<?= $e($user['school_name'] ?? '') ?>" placeholder="e.g. St. Jude High School">
                            </div>
                        </div>

                        <div>
                            <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Phone Number / WhatsApp
                            </label>
                            <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                                <div class="input-icon-box">
                                    <i data-lucide="phone" class="w-4 h-4"></i>
                                </div>
                                <input type="text" id="phone" name="phone" value="<?= $e($user['phone'] ?? '') ?>" placeholder="+1 555-0199" class="font-mono tabular-nums">
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Save Profile Changes</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Security & Password Update -->
            <div class="glass-card p-6">
                <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i>
                        <span>Security & Password</span>
                    </h2>
                    <span class="text-xs text-slate-400">Ensure account safety</span>
                </div>

                <form method="POST" action="/profile/password" class="space-y-4">
                    <?= $csrf() ?>

                    <div>
                        <label for="current_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Current Password <span class="text-rose-500">*</span>
                        </label>
                        <div class="glass-input-group max-w-md" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input type="password" id="current_password" name="current_password" required placeholder="••••••••">
                            <button type="button"
                                    onclick="event.stopPropagation(); togglePasswordVisibility('current_password', this)"
                                    class="input-action-btn"
                                    title="Toggle password visibility">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="new_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                New Password <span class="text-rose-500">*</span>
                            </label>
                            <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                                <div class="input-icon-box">
                                    <i data-lucide="key" class="w-4 h-4"></i>
                                </div>
                                <input type="password" id="new_password" name="new_password" minlength="6" required placeholder="Min 6 characters">
                                <button type="button"
                                        onclick="event.stopPropagation(); togglePasswordVisibility('new_password', this)"
                                        class="input-action-btn"
                                        title="Toggle password visibility">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="new_password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Confirm New Password <span class="text-rose-500">*</span>
                            </label>
                            <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                                <div class="input-icon-box">
                                    <i data-lucide="key" class="w-4 h-4"></i>
                                </div>
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation" minlength="6" required placeholder="Re-type password">
                                <button type="button"
                                        onclick="event.stopPropagation(); togglePasswordVisibility('new_password_confirmation', this)"
                                        class="input-action-btn"
                                        title="Toggle password visibility">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="text-xs text-slate-400 flex items-center gap-1.5">
                            <i data-lucide="info" class="w-3.5 h-3.5 text-indigo-400"></i>
                            <span>Use at least 6 characters with letters and numbers.</span>
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all cursor-pointer">
                            <i data-lucide="check-shield" class="w-4 h-4"></i>
                            <span>Update Password</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</div>

<script>
    function handleAvatarSelected(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatar-preview');
                const fallback = document.getElementById('avatar-fallback');
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (fallback) fallback.classList.add('hidden');
                document.getElementById('avatar-upload-prompt').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    }

    function togglePasswordVisibility(fieldId, btn) {
        const input = document.getElementById(fieldId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '<i data-lucide="eye-off" class="w-4 h-4 text-indigo-500"></i>';
        } else {
            input.type = 'password';
            btn.innerHTML = '<i data-lucide="eye" class="w-4 h-4 text-slate-400"></i>';
        }
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
</script>
