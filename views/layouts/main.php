<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $e($title ?? 'Class Aid') ?> - EduAid Suite</title>
    <!-- Prevent flash of wrong theme -->
    <script>
        (function() {
            const saved = localStorage.getItem('class_aid_theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
        window.toggleTheme = function(e) {
            if (e) {
                if (typeof e.preventDefault === 'function') e.preventDefault();
                if (typeof e.stopPropagation === 'function') e.stopPropagation();
                if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
            }
            const now = Date.now();
            if (window._lastThemeToggle && (now - window._lastThemeToggle) < 250) {
                return document.documentElement.classList.contains('dark');
            }
            window._lastThemeToggle = now;

            const isDark = document.documentElement.classList.toggle('dark');
            try {
                localStorage.setItem('class_aid_theme', isDark ? 'dark' : 'light');
            } catch (err) {}
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
            return isDark;
        };
    </script>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        glass: {
                            50: 'rgba(255, 255, 255, 0.05)',
                            100: 'rgba(255, 255, 255, 0.1)',
                            200: 'rgba(255, 255, 255, 0.2)',
                            300: 'rgba(255, 255, 255, 0.3)',
                            800: 'rgba(15, 23, 42, 0.75)',
                            900: 'rgba(11, 15, 25, 0.85)',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Custom Glassmorphism CSS -->
    <link rel="stylesheet" href="/assets/css/glassmorphism.css">
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-indigo-500 selection:text-white text-slate-900 dark:text-slate-100 transition-colors pb-20 md:pb-0">

    <!-- Top Page Loading Progress Bar -->
    <div id="page-loader-bar"></div>

    <!-- Top Navigation Bar (Liquid Glass) -->
    <header class="sticky top-0 z-40 w-full liquid-header shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">

            <!-- Brand Logo & School Name -->
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="/dashboard" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-600/20 group-hover:scale-105 transition-transform flex-shrink-0">
                        <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-extrabold text-base tracking-tight text-slate-900 dark:text-white">
                            Class Aid
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 hidden sm:block truncate max-w-[160px] font-medium">
                            <?= $e($user['school_name'] ?? 'Classroom Suite') ?>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Global Live Student Search Trigger -->
            <div class="flex items-center">
                <!-- Mobile Search Icon Button -->
                <button type="button"
                        onclick="openGlobalSearch()"
                        class="sm:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                        title="Search Students (Ctrl+K)">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </button>

                <!-- Desktop Search Trigger Bar -->
                <button type="button"
                        onclick="openGlobalSearch()"
                        class="hidden sm:flex items-center gap-2.5 px-3.5 py-1.5 rounded-xl glass-input text-slate-400 dark:text-slate-500 hover:border-indigo-500 hover:text-slate-600 dark:hover:text-slate-300 transition-all text-xs w-60 md:w-72 cursor-pointer text-left">
                    <i data-lucide="search" class="w-4 h-4 text-indigo-500 flex-shrink-0"></i>
                    <span class="flex-1 truncate">Search students (name, ID)...</span>
                    <kbd class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500">⌘K</kbd>
                </button>
            </div>

            <!-- Right Controls: Theme Toggle & User Menu -->
            <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                <!-- Super Admin Quick Switch (if role is super_admin) -->
                <?php if (\App\Core\Session::isSuperAdmin()): ?>
                    <a href="/admin" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/30 hover:bg-rose-500/20 flex items-center gap-1.5 transition-colors" title="Super Admin Portal">
                        <i data-lucide="shield-alert" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Admin</span>
                    </a>
                <?php endif; ?>

                <!-- Notification Center Bell & Flyout -->
                <div class="relative" id="notifications-wrapper">
                    <button type="button"
                            id="notifications-bell-btn"
                            onclick="toggleNotificationsDropdown(event)"
                            class="relative p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none"
                            title="Notifications & Announcements"
                            aria-expanded="false"
                            aria-haspopup="true">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <!-- Live Unread Badge Counter -->
                        <span id="notif-badge-indicator" class="hidden absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-600 text-white font-mono text-[10px] font-extrabold flex items-center justify-center shadow-md shadow-rose-600/40 pointer-events-none ring-2 ring-white dark:ring-slate-900">
                            0
                        </span>
                    </button>

                    <!-- Notifications Dropdown Flyout (Liquid Glass) -->
                    <div id="notifications-flyout"
                         class="hidden absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl glass-card border border-slate-200/90 dark:border-white/10 shadow-2xl z-50 overflow-hidden text-left animate-in fade-in zoom-in-95 duration-150 origin-top-right">

                        <!-- Header Bar -->
                        <div class="px-4 py-3 bg-slate-50/80 dark:bg-slate-900/80 border-b border-slate-200/80 dark:border-white/10 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                    <i data-lucide="bell-ring" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Notifications</h3>
                                    <span id="notif-unread-count-pill" class="text-xs text-slate-500 dark:text-slate-400 font-medium">0 unread</span>
                                </div>
                            </div>
                            <button type="button" onclick="markAllNotificationsAsRead(event)" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 flex items-center gap-1 hover:underline">
                                <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                                <span>Mark all read</span>
                            </button>
                        </div>

                        <!-- Segmented Switcher -->
                        <div class="flex border-b border-slate-200/80 dark:border-white/10 bg-slate-100/70 dark:bg-slate-950/70 p-1 text-xs gap-1">
                            <button type="button"
                                    id="notif-tab-announcements"
                                    onclick="switchNotifTab('announcements')"
                                    class="flex-1 py-1.5 px-3 rounded-lg font-bold text-center transition-all bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm flex items-center justify-center gap-1.5">
                                <i data-lucide="megaphone" class="w-3.5 h-3.5"></i>
                                <span>Announcements</span>
                                <span id="tab-badge-announcements" class="text-xs px-1.5 py-0.2 rounded-full bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 font-mono font-bold">
                                    <?= count($activeAnnouncements) ?>
                                </span>
                            </button>
                            <button type="button"
                                    id="notif-tab-reminders"
                                    onclick="switchNotifTab('reminders')"
                                    class="flex-1 py-1.5 px-3 rounded-lg font-medium text-center text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all flex items-center justify-center gap-1.5">
                                <i data-lucide="calendar-clock" class="w-3.5 h-3.5"></i>
                                <span>Reminders</span>
                                <?php if (!empty($pendingRemindersCount)): ?>
                                    <span class="text-xs px-1.5 py-0.2 rounded-full bg-amber-500/20 text-amber-600 dark:text-amber-400 font-mono font-bold">
                                        <?= $pendingRemindersCount ?>
                                    </span>
                                <?php endif; ?>
                            </button>
                        </div>

                        <!-- Tab 1: Announcements List -->
                        <div id="notif-panel-announcements" class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                            <?php if (empty($activeAnnouncements)): ?>
                                <div class="py-8 text-center text-slate-400 text-xs px-4">
                                    <div class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                        <i data-lucide="bell-off" class="w-5 h-5"></i>
                                    </div>
                                    <p class="font-medium text-slate-700 dark:text-slate-300">No active announcements</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Super Admin broadcasts and updates will appear here.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($activeAnnouncements as $ann): ?>
                                    <?php
                                        $annMeta = \App\Models\Announcement::TYPES[$ann['type']] ?? \App\Models\Announcement::TYPES['info'];
                                    ?>
                                    <div class="ann-item p-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer transition-colors flex items-start gap-3 relative group"
                                         id="ann-item-<?= $ann['id'] ?>"
                                         data-id="<?= $ann['id'] ?>"
                                         onclick="openGlobalAnnouncementModal(<?= htmlspecialchars(json_encode($ann), ENT_QUOTES, 'UTF-8') ?>)">
                                        <!-- Unread Dot -->
                                        <div class="ann-unread-dot w-2 h-2 rounded-full bg-indigo-600 mt-1.5 flex-shrink-0 animate-pulse"></div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1 mb-1">
                                                <span class="text-xs px-2 py-0.5 rounded-full font-bold uppercase tracking-wider <?= $annMeta['badge'] ?>">
                                                    <?= $e($annMeta['label']) ?>
                                                </span>
                                                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                    <?= date('M j', strtotime($ann['created_at'] ?? 'now')) ?>
                                                </span>
                                            </div>
                                            <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                <?= $e($ann['title']) ?>
                                            </h4>
                                            <p class="text-xs text-slate-600 dark:text-slate-300 line-clamp-2 mt-0.5 leading-relaxed">
                                                <?= $e($ann['content']) ?>
                                            </p>
                                            <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mt-1 inline-block group-hover:underline">Read full announcement &rarr;</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Tab 2: Reminders List -->
                        <div id="notif-panel-reminders" class="hidden max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                            <?php if (empty($pendingRemindersList)): ?>
                                <div class="py-8 text-center text-slate-400 text-xs px-4">
                                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center mx-auto mb-2">
                                        <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                                    </div>
                                    <p class="font-medium text-slate-700 dark:text-slate-300">All caught up!</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">No pending deadline reminders on your schedule.</p>
                                    <a href="/reminders" class="inline-flex items-center gap-1 mt-3 px-3 py-1.5 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold hover:bg-indigo-500/20 text-xs transition-colors">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        <span>Create a Reminder</span>
                                    </a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($pendingRemindersList as $rem): ?>
                                    <div class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5 mb-1">
                                                <span class="text-xs px-1.5 py-0.5 rounded font-bold uppercase font-mono <?= ($rem['priority'] ?? '') === 'high' ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400' : (($rem['priority'] ?? '') === 'medium' ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400' : 'bg-blue-500/15 text-blue-600 dark:text-blue-400') ?>">
                                                    <?= $e($rem['priority'] ?? 'normal') ?>
                                                </span>
                                                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                    Due <?= date('M j', strtotime($rem['due_date'])) ?>
                                                </span>
                                            </div>
                                            <p class="text-xs font-semibold text-slate-900 dark:text-slate-200 line-clamp-1">
                                                <?= $e($rem['title']) ?>
                                            </p>
                                        </div>
                                        <form method="POST" action="/reminders/<?= $rem['id'] ?>/toggle" class="flex-shrink-0">
                                            <?= $csrf() ?>
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-500 hover:bg-emerald-500/10 transition-colors" title="Mark as Done">
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Footer Link to Reminders Cockpit -->
                        <div class="p-2.5 bg-slate-50 dark:bg-slate-800/60 border-t border-slate-200 dark:border-slate-800 text-center">
                            <a href="/reminders" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 flex items-center justify-center gap-1 transition-colors">
                                <span>Open Full Reminders & Calendar Cockpit</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Theme Toggle Button -->
                <button type="button" onclick="window.toggleTheme(event)" class="theme-toggle p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer" title="Toggle Theme">
                    <i data-lucide="sun" class="w-5 h-5 theme-icon-sun"></i>
                    <i data-lucide="moon" class="w-5 h-5 theme-icon-moon"></i>
                </button>

                <!-- Teacher Menu & Profile Link -->
                <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-800">
                    <a href="/profile" class="flex items-center gap-2 group p-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Manage Profile & Settings">
                        <?php if (!empty($user['avatar']) && file_exists(dirname(__DIR__, 2) . '/public' . $user['avatar'])): ?>
                            <img src="<?= $e($user['avatar']) ?>" alt="Avatar" class="w-8 h-8 rounded-xl object-cover ring-2 ring-indigo-500/30">
                        <?php else: ?>
                            <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-bold text-xs flex items-center justify-center shadow-md shadow-indigo-600/20 group-hover:scale-105 transition-transform flex-shrink-0">
                                <?= strtoupper(substr($user['name'] ?? 'T', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="hidden md:block text-left">
                            <div class="text-xs font-semibold text-slate-900 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors leading-tight"><?= $e($user['name'] ?? 'Teacher') ?></div>
                            <div class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                                <?= ($user['role'] ?? '') === 'super_admin' ? 'Master Admin' : 'Class Teacher' ?>
                            </div>
                        </div>
                    </a>
                    <a href="/logout" class="p-2 text-rose-500 hover:bg-rose-500/10 rounded-xl transition-colors" title="Logout">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                    </a>
                </div>

            </div>
        </div>
    </header>

    <!-- Desktop Navigation Ribbon (Liquid Glass) -->
    <nav class="hidden md:block liquid-header border-b transition-colors relative z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between py-1.5 gap-3">

            <!-- Scrollable Nav Section with Arrow Buttons & Smooth Wheel Scroll -->
            <div class="relative flex items-center min-w-0 flex-1 overflow-hidden group">
                <!-- Left Scroll Arrow Button -->
                <button type="button"
                        id="nav-scroll-left-btn"
                        onclick="scrollNavRibbon(-180)"
                        aria-label="Scroll navigation left"
                        class="hidden p-1.5 rounded-lg bg-white/90 dark:bg-slate-800/90 text-slate-600 dark:text-slate-300 shadow-md border border-slate-200/80 dark:border-white/10 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all flex-shrink-0 mr-1.5 z-10 cursor-pointer">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </button>

                <!-- Nav Ribbon Scroll Container -->
                <div id="desktop-nav-ribbon"
                     class="flex items-center gap-1 overflow-x-auto no-scrollbar scroll-smooth min-w-0 flex-1 py-0.5">
                    <a href="/dashboard" class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 text-indigo-500"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="/calls" class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                        <i data-lucide="phone-call" class="w-4 h-4 text-emerald-500"></i>
                        <span>Parent Calls</span>
                    </a>
                    <a href="/attendance" class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                        <i data-lucide="calendar-check" class="w-4 h-4 text-blue-500"></i>
                        <span>Attendance</span>
                    </a>
                    <a href="/attendance/reports" class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                        <i data-lucide="bar-chart-2" class="w-4 h-4 text-cyan-500"></i>
                        <span>Attendance Reports</span>
                    </a>
                    <a href="/students" class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                        <i data-lucide="users" class="w-4 h-4 text-violet-500"></i>
                        <span>Students</span>
                    </a>
                    <a href="/results" class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                        <i data-lucide="award" class="w-4 h-4 text-amber-500"></i>
                        <span>Marks & Exams</span>
                    </a>
                    <a href="/classes" class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                        <i data-lucide="book-open" class="w-4 h-4 text-sky-500"></i>
                        <span>Classes & Subjects</span>
                    </a>
                    <a href="/reminders" class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors whitespace-nowrap flex-shrink-0">
                        <i data-lucide="calendar-clock" class="w-4 h-4 text-rose-500"></i>
                        <span>Reminders</span>
                    </a>
                </div>

                <!-- Right Scroll Arrow Button -->
                <button type="button"
                        id="nav-scroll-right-btn"
                        onclick="scrollNavRibbon(180)"
                        aria-label="Scroll navigation right"
                        class="hidden p-1.5 rounded-lg bg-white/90 dark:bg-slate-800/90 text-slate-600 dark:text-slate-300 shadow-md border border-slate-200/80 dark:border-white/10 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all flex-shrink-0 ml-1.5 z-10 cursor-pointer">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Profile & Subscription Badge Links (Fixed Container: Never Overlapped) -->
            <div class="flex items-center gap-2 flex-shrink-0 z-20 pl-3 border-l border-slate-200 dark:border-slate-800">
                <a href="/profile" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg hover:bg-white/80 dark:hover:bg-slate-800/80 text-slate-700 dark:text-slate-300 transition-colors whitespace-nowrap">
                    <i data-lucide="user-cog" class="w-3.5 h-3.5 text-indigo-500"></i>
                    <span>Profile</span>
                </a>
                <a href="/subscription" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-500/25 transition-all border border-indigo-500/30 whitespace-nowrap">
                    <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                    <span><?= !empty($userSubscription['plan_code']) ? strtoupper($userSubscription['plan_code']) : 'SaaS Plan' ?></span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Viewport Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- Subscription Expiry Warning Banner (If within 7 days or expired) -->
        <?php if ($userSubscription && (!empty($userSubscription['is_expiring']) || !empty($userSubscription['is_expired']))): ?>
            <?php
                $isExp = !empty($userSubscription['is_expired']);
                $daysLeft = $userSubscription['days_left'] ?? 0;
            ?>
            <div class="mb-6 p-4 rounded-2xl border flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm <?= $isExp ? 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800/80 text-rose-950 dark:text-rose-100' : 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800/80 text-amber-950 dark:text-amber-100' ?>">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 <?= $isExp ? 'bg-rose-500/20 text-rose-600 dark:text-rose-300' : 'bg-amber-500/20 text-amber-600 dark:text-amber-300' ?>">
                        <i data-lucide="<?= $isExp ? 'alert-octagon' : 'alert-triangle' ?>" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold text-sm">
                            <?= $isExp ? 'Subscription Expired' : "Subscription Expiring in {$daysLeft} Day" . ($daysLeft == 1 ? '' : 's') ?>
                        </div>
                        <div class="text-xs <?= $isExp ? 'text-rose-700 dark:text-rose-300' : 'text-amber-700 dark:text-amber-300' ?> mt-0.5">
                            <?= $isExp ? 'Your plan has expired. Renew today to keep unlimited access to student audits and parent call journals.' : 'Your plan is up for renewal soon. Keep your educational records active without interruption.' ?>
                        </div>
                    </div>
                </div>
                <a href="/subscription" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm whitespace-nowrap flex-shrink-0 transition-colors">
                    Renew / Upgrade Plan &rarr;
                </a>
            </div>
        <?php endif; ?>

        <!-- Flash Messages -->
        <?php if ($flashSuccess): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80 text-emerald-900 dark:text-emerald-100 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5 text-sm font-medium">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0"></i>
                    <span><?= $e($flashSuccess) ?></span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:opacity-75 p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/80 text-rose-900 dark:text-rose-100 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5 text-sm font-medium">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0"></i>
                    <span><?= $e($flashError) ?></span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400 hover:opacity-75 p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <!-- Mobile-First Sticky Bottom Navigation Bar (Liquid Glass) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 liquid-header px-2 py-1.5 shadow-lg border-t">
        <div class="grid grid-cols-5 text-center">

            <a href="/dashboard" class="flex flex-col items-center justify-center py-1 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1 font-medium">Home</span>
            </a>

            <a href="/calls" class="flex flex-col items-center justify-center py-1 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="phone-call" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1 font-medium">Calls</span>
            </a>

            <a href="/attendance" class="flex flex-col items-center justify-center py-1 text-indigo-600 dark:text-indigo-400">
                <div class="w-10 h-10 -mt-5 rounded-full bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-600/40">
                    <i data-lucide="calendar-check" class="w-5 h-5"></i>
                </div>
                <span class="text-[10px] mt-0.5 font-bold">Roll Call</span>
            </a>

            <a href="/students" class="flex flex-col items-center justify-center py-1 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1 font-medium">Students</span>
            </a>

            <a href="/results" class="flex flex-col items-center justify-center py-1 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="award" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1 font-medium">Marks</span>
            </a>

        </div>
    </nav>

    <!-- Global Announcement Modal (Available Across All Pages) -->
    <div id="global-announcement-modal" onclick="if(event.target === this) closeGlobalAnnouncementModal()" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in duration-200">
        <div class="glass-card max-w-lg w-full p-6 sm:p-8 rounded-3xl border border-white/10 shadow-2xl relative bg-slate-900 text-white space-y-4">
            <div class="flex items-start justify-between gap-3 pb-3 border-b border-white/10">
                <div class="flex items-center gap-2">
                    <span id="global-modal-ann-badge" class="text-xs px-2.5 py-0.5 rounded-full font-bold uppercase"></span>
                    <h3 id="global-modal-ann-title" class="text-base font-extrabold text-white"></h3>
                </div>
                <button type="button" onclick="closeGlobalAnnouncementModal()" class="text-gray-400 hover:text-white p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="text-xs text-gray-300 leading-relaxed max-h-72 overflow-y-auto whitespace-pre-wrap" id="global-modal-ann-content"></div>

            <div class="pt-4 border-t border-white/10 flex items-center justify-between text-[11px] text-gray-400">
                <span id="global-modal-ann-date"></span>
                <button type="button" onclick="closeGlobalAnnouncementModal()" class="px-4 py-1.5 rounded-xl font-bold text-xs text-white bg-indigo-600 hover:bg-indigo-500 shadow transition-colors">
                    Close Notice
                </button>
            </div>
        </div>
    </div>

    <!-- App Scripts -->
    <script src="/assets/js/app.js"></script>
    <script>
        lucide.createIcons();

        const STORAGE_KEY_READ_ANNOUNCEMENTS = 'classaid_read_announcements';
        const pendingRemindersCount = <?= (int) ($pendingRemindersCount ?? 0) ?>;

        function getReadAnnouncementIds() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY_READ_ANNOUNCEMENTS) || '[]');
            } catch(e) {
                return [];
            }
        }

        function saveReadAnnouncementId(id) {
            const readIds = getReadAnnouncementIds();
            if (!readIds.includes(id)) {
                readIds.push(id);
                localStorage.setItem(STORAGE_KEY_READ_ANNOUNCEMENTS, JSON.stringify(readIds));
            }
            refreshNotificationBadges();
        }

        function toggleNotificationsDropdown(e) {
            if (e) e.stopPropagation();
            const flyout = document.getElementById('notifications-flyout');
            const btn = document.getElementById('notifications-bell-btn');
            if (!flyout) return;

            const isHidden = flyout.classList.contains('hidden');
            if (isHidden) {
                flyout.classList.remove('hidden');
                btn?.setAttribute('aria-expanded', 'true');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            } else {
                flyout.classList.add('hidden');
                btn?.setAttribute('aria-expanded', 'false');
            }
        }

        function switchNotifTab(tabName) {
            const tabAnn = document.getElementById('notif-tab-announcements');
            const tabRem = document.getElementById('notif-tab-reminders');
            const panelAnn = document.getElementById('notif-panel-announcements');
            const panelRem = document.getElementById('notif-panel-reminders');

            if (tabName === 'announcements') {
                tabAnn.className = 'flex-1 py-1.5 px-3 rounded-lg font-bold text-center transition-all bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm flex items-center justify-center gap-1.5';
                tabRem.className = 'flex-1 py-1.5 px-3 rounded-lg font-medium text-center text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-all flex items-center justify-center gap-1.5';
                panelAnn?.classList.remove('hidden');
                panelRem?.classList.add('hidden');
            } else {
                tabRem.className = 'flex-1 py-1.5 px-3 rounded-lg font-bold text-center transition-all bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm flex items-center justify-center gap-1.5';
                tabAnn.className = 'flex-1 py-1.5 px-3 rounded-lg font-medium text-center text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-all flex items-center justify-center gap-1.5';
                panelRem?.classList.remove('hidden');
                panelAnn?.classList.add('hidden');
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function openGlobalAnnouncementModal(ann) {
            if (!ann) return;
            document.getElementById('global-modal-ann-title').textContent = ann.title || 'Notice';
            document.getElementById('global-modal-ann-content').textContent = ann.content || '';
            document.getElementById('global-modal-ann-badge').textContent = (ann.type || 'info').toUpperCase();
            document.getElementById('global-modal-ann-date').textContent = 'Broadcasted on: ' + (ann.created_at || 'Recently');

            const modal = document.getElementById('global-announcement-modal');
            modal.classList.remove('hidden');
            saveReadAnnouncementId(parseInt(ann.id, 10));

            // If on dashboard and toast is present, dismiss it
            if (typeof dismissSingleToast === 'function') {
                dismissSingleToast(ann.id);
            }
        }
        window.openAnnouncementModal = openGlobalAnnouncementModal;

        function closeGlobalAnnouncementModal() {
            document.getElementById('global-announcement-modal').classList.add('hidden');
        }
        window.closeAnnouncementModal = closeGlobalAnnouncementModal;

        function markAllNotificationsAsRead(e) {
            if (e) e.stopPropagation();
            const items = document.querySelectorAll('.ann-item');
            const allIds = [];
            items.forEach(el => {
                const id = parseInt(el.dataset.id, 10);
                if (!isNaN(id)) allIds.push(id);
            });
            localStorage.setItem(STORAGE_KEY_READ_ANNOUNCEMENTS, JSON.stringify(allIds));

            // Also if dashboard toasts are present, mark them read
            if (typeof markAllAnnouncementsRead === 'function') {
                markAllAnnouncementsRead();
            }

            refreshNotificationBadges();
        }

        function refreshNotificationBadges() {
            const readIds = getReadAnnouncementIds();
            const items = document.querySelectorAll('.ann-item');
            let unreadAnnouncements = 0;

            items.forEach(el => {
                const id = parseInt(el.dataset.id, 10);
                const dot = el.querySelector('.ann-unread-dot');
                if (readIds.includes(id)) {
                    if (dot) dot.classList.add('hidden');
                    el.classList.add('opacity-60');
                } else {
                    if (dot) dot.classList.remove('hidden');
                    el.classList.remove('opacity-60');
                    unreadAnnouncements++;
                }
            });

            const totalUnread = unreadAnnouncements + pendingRemindersCount;
            const badge = document.getElementById('notif-badge-indicator');
            const countPill = document.getElementById('notif-unread-count-pill');
            const tabAnnBadge = document.getElementById('tab-badge-announcements');

            if (tabAnnBadge) {
                tabAnnBadge.textContent = unreadAnnouncements > 0 ? unreadAnnouncements : items.length;
            }

            if (countPill) {
                countPill.textContent = unreadAnnouncements > 0 ? (unreadAnnouncements + ' unread') : 'All read';
            }

            if (badge) {
                if (totalUnread > 0) {
                    badge.textContent = totalUnread > 99 ? '99+' : totalUnread;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        }

        // Close flyout on click outside or escape key
        document.addEventListener('click', (e) => {
            const wrapper = document.getElementById('notifications-wrapper');
            const flyout = document.getElementById('notifications-flyout');
            if (wrapper && flyout && !wrapper.contains(e.target)) {
                flyout.classList.add('hidden');
                document.getElementById('notifications-bell-btn')?.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.getElementById('notifications-flyout')?.classList.add('hidden');
                closeGlobalAnnouncementModal();
                closeGlobalSearch();
            }
            // Global Ctrl+K / Cmd+K shortcut
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                openGlobalSearch();
            }
        });

        function openGlobalSearch() {
            const modal = document.getElementById('global-search-modal');
            if (!modal) return;
            modal.classList.remove('hidden');
            const input = document.getElementById('global-modal-search-input');
            if (input) {
                setTimeout(() => input.focus(), 50);
            }
        }

        function closeGlobalSearch() {
            const modal = document.getElementById('global-search-modal');
            if (modal) modal.classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            refreshNotificationBadges();
        });
    </script>

    <!-- Global Search Modal Popup (Mobile-Optimized & Responsive) -->
    <div id="global-search-modal"
         class="hidden fixed inset-0 z-50 flex items-start justify-center pt-12 sm:pt-20 px-3 sm:px-4 bg-black/60 backdrop-blur-md"
         onclick="if(event.target === this) closeGlobalSearch()">
        <div class="relative w-full max-w-xl glass-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 shadow-2xl rounded-2xl overflow-hidden search-wrapper">
            <!-- Search Header -->
            <div class="flex items-center px-4 py-3.5 border-b border-slate-200 dark:border-slate-800 gap-3">
                <i data-lucide="search" class="w-5 h-5 text-indigo-500 flex-shrink-0"></i>
                <input type="text"
                       id="global-modal-search-input"
                       class="global-student-search flex-1 bg-transparent border-0 outline-none text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-0 p-0"
                       placeholder="Search student by name, phone, or Admin ID..."
                       autocomplete="off">
                <kbd class="hidden sm:inline-block px-1.5 py-0.5 text-[10px] font-mono text-slate-400 bg-slate-100 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700">ESC</kbd>
                <button type="button" onclick="closeGlobalSearch()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Search Results Container inside Modal -->
            <div class="search-results-dropdown max-h-[60vh] overflow-y-auto p-2">
                <div id="search-modal-prompt" class="p-8 text-center text-xs text-slate-400 dark:text-slate-500">
                    <i data-lucide="search" class="w-8 h-8 mx-auto mb-2 opacity-30 text-indigo-500"></i>
                    <p class="font-medium text-slate-600 dark:text-slate-400">Search Students Directory</p>
                    <p class="text-[11px] mt-1 text-slate-400">Type a student's first name, last name, phone number, or Admin ID (e.g. ADM-1001).</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
