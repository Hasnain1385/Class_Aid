/**
 * Class Aid — Client Application Engine
 * Liquid Glass UI, Seamless Theme Engine, Page Loading Bar & Skeleton Ladders
 */

// ==========================================================================
// Top Page Loading Progress Bar & Global Overlay
// ==========================================================================
const PageLoader = {
    bar: null,
    overlay: null,
    progress: 0,
    timer: null,

    init() {
        this.bar = document.getElementById('page-loader-bar');
        this.overlay = document.getElementById('page-loader-overlay');

        if (!this.bar) {
            this.bar = document.createElement('div');
            this.bar.id = 'page-loader-bar';
            document.body.prepend(this.bar);
        }

        // Attach to normal navigational links
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link) return;
            const href = link.getAttribute('href');

            // Skip anchors, javascript, external links, downloads, modal triggers
            if (!href || href.startsWith('#') || href.startsWith('javascript:') ||
                link.getAttribute('target') === '_blank' ||
                link.hasAttribute('download') ||
                link.dataset.noLoader !== undefined) {
                return;
            }

            // Internal navigation
            if (href.startsWith('/') || href.startsWith(window.location.origin)) {
                PageLoader.start();
            }
        });

        // Attach to standard form submissions
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form && !form.dataset.noLoader && !form.getAttribute('target')) {
                PageLoader.start();
            }
        });

        // Reset loader on browser back/forward cache restore
        window.addEventListener('pageshow', () => {
            PageLoader.done();
        });
    },

    start() {
        if (!this.bar) return;
        this.progress = 10;
        this.bar.classList.add('loading');
        this.bar.style.width = this.progress + '%';

        clearInterval(this.timer);
        this.timer = setInterval(() => {
            if (this.progress < 85) {
                const step = Math.max(1, (90 - this.progress) * 0.1);
                this.progress += step;
                this.bar.style.width = this.progress + '%';
            }
        }, 120);
    },

    set(percent) {
        if (!this.bar) return;
        this.progress = Math.min(100, Math.max(0, percent));
        this.bar.style.width = this.progress + '%';
    },

    done() {
        if (!this.bar) return;
        clearInterval(this.timer);
        this.progress = 100;
        this.bar.style.width = '100%';

        setTimeout(() => {
            this.bar.classList.remove('loading');
            setTimeout(() => {
                this.bar.style.width = '0%';
                this.progress = 0;
            }, 200);
        }, 200);
    },

    showOverlay(text) {
        if (!this.overlay) {
            this.overlay = document.createElement('div');
            this.overlay.id = 'page-loader-overlay';
            this.overlay.innerHTML = `
                <div class="glass-card p-6 rounded-2xl flex flex-col items-center gap-3 shadow-2xl">
                    <div class="liquid-spinner"></div>
                    <span id="page-loader-text" class="text-xs font-semibold text-slate-700 dark:text-slate-200">${text || 'Loading...'}</span>
                </div>
            `;
            document.body.appendChild(this.overlay);
        } else {
            const txt = this.overlay.querySelector('#page-loader-text');
            if (txt) txt.textContent = text || 'Loading...';
        }
        this.overlay.classList.add('active');
        this.start();
    },

    hideOverlay() {
        if (this.overlay) {
            this.overlay.classList.remove('active');
        }
        this.done();
    }
};

window.PageLoader = PageLoader;

// ==========================================================================
// Skeleton Ladders Engine
// ==========================================================================
const SkeletonLadder = {
    /**
     * Render a skeleton ladder inside a container element
     * @param {HTMLElement|string} target - Container element or selector
     * @param {number} rows - Number of shimmer ladder rows
     * @param {string} type - 'table' | 'cards' | 'roster' | 'search'
     */
    render(target, rows = 4, type = 'table') {
        const el = (typeof target === 'string') ? document.querySelector(target) : target;
        if (!el) return;

        let html = '';
        if (type === 'table') {
            html = `
                <div class="skeleton-ladder space-y-2 p-2">
                    ${Array.from({ length: rows }).map(() => `
                        <div class="skeleton-ladder-row flex items-center justify-between gap-4 p-3 rounded-xl border border-slate-200/60 dark:border-white/5">
                            <div class="skeleton w-24 h-4 rounded"></div>
                            <div class="skeleton w-36 h-4 rounded hidden sm:block"></div>
                            <div class="skeleton w-20 h-4 rounded"></div>
                            <div class="skeleton w-16 h-4 rounded hidden md:block"></div>
                            <div class="skeleton w-12 h-6 rounded-full"></div>
                        </div>
                    `).join('')}
                </div>
            `;
        } else if (type === 'search') {
            html = `
                <div class="p-2 space-y-2">
                    ${Array.from({ length: rows }).map(() => `
                        <div class="flex items-center justify-between p-2.5 rounded-lg border border-slate-100 dark:border-slate-800/60">
                            <div class="space-y-1.5 flex-1">
                                <div class="skeleton w-32 h-3.5 rounded"></div>
                                <div class="skeleton w-20 h-2.5 rounded"></div>
                            </div>
                            <div class="skeleton w-16 h-3 rounded"></div>
                        </div>
                    `).join('')}
                </div>
            `;
        } else if (type === 'cards') {
            html = `
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    ${Array.from({ length: rows }).map(() => `
                        <div class="glass-card p-5 space-y-3">
                            <div class="skeleton w-20 h-3 rounded"></div>
                            <div class="skeleton w-16 h-7 rounded"></div>
                            <div class="skeleton w-28 h-3 rounded"></div>
                        </div>
                    `).join('')}
                </div>
            `;
        } else {
            // Roster / dossier default
            html = `
                <div class="skeleton-ladder space-y-3">
                    ${Array.from({ length: rows }).map(() => `
                        <div class="glass-card p-4 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="skeleton skeleton-circle w-10 h-10 flex-shrink-0"></div>
                                <div class="space-y-1.5 flex-1">
                                    <div class="skeleton w-36 h-4 rounded"></div>
                                    <div class="skeleton w-24 h-3 rounded"></div>
                                </div>
                            </div>
                            <div class="skeleton w-16 h-6 rounded-full"></div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        el.innerHTML = html;
    }
};

window.SkeletonLadder = SkeletonLadder;

// ==========================================================================
// Theme Engine (Dark / Light with LocalStorage & OS Sync)
// ==========================================================================
function applyTheme() {
    const savedTheme = localStorage.getItem('class_aid_theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const shouldBeDark = (savedTheme === 'dark' || (!savedTheme && prefersDark));

    if (shouldBeDark) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

// Immediate theme execution to prevent flash
applyTheme();

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

function initTheme() {
    applyTheme();
}

// ==========================================================================
// Live Student Search with Debounce & Skeleton Ladder
// ==========================================================================
function initSearch() {
    const searchInputs = document.querySelectorAll('.global-student-search');
    searchInputs.forEach(input => {
        const resultsContainer = input.closest('.search-wrapper')?.querySelector('.search-results-dropdown');
        if (!resultsContainer) return;

        let debounceTimer = null;

        input.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.trim();

            if (query.length < 1) {
                if (input.closest('#global-search-modal')) {
                    resultsContainer.innerHTML = `
                        <div id="search-modal-prompt" class="p-8 text-center text-xs text-slate-400 dark:text-slate-500">
                            <i data-lucide="search" class="w-8 h-8 mx-auto mb-2 opacity-30 text-indigo-500"></i>
                            <p class="font-medium text-slate-600 dark:text-slate-400">Search Students Directory</p>
                            <p class="text-[11px] mt-1 text-slate-400">Type a student's first name, last name, phone number, or Admin ID (e.g. ADM-1001).</p>
                        </div>`;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } else {
                    resultsContainer.classList.add('hidden');
                    resultsContainer.innerHTML = '';
                }
                return;
            }

            // Show skeleton ladder inside dropdown immediately
            resultsContainer.classList.remove('hidden');
            SkeletonLadder.render(resultsContainer, 3, 'search');

            debounceTimer = setTimeout(async () => {
                try {
                    const res = await fetch(`/students/search/api?q=${encodeURIComponent(query)}`);
                    const data = await res.json();

                    if (!Array.isArray(data) || data.length === 0) {
                        resultsContainer.innerHTML = `
                            <div class="p-4 text-xs text-slate-500 dark:text-slate-400 text-center">
                                No students found for "${query}"
                            </div>`;
                    } else {
                        resultsContainer.innerHTML = data.map(s => `
                            <a href="${s.url}" class="flex items-center justify-between p-2.5 hover:bg-indigo-500/10 rounded-xl transition-colors border-b border-slate-100 dark:border-slate-800/60 last:border-0 group">
                                <div>
                                    <div class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">${s.name}</div>
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400 font-mono">${s.admin_id} • ${s.class}</div>
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1 font-mono">
                                    <span>${s.phone}</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-500 transition-colors"></i>
                                </div>
                            </a>
                        `).join('');
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                } catch (err) {
                    console.error('Search error:', err);
                    resultsContainer.innerHTML = `<div class="p-3 text-xs text-rose-500 text-center">Search error</div>`;
                }
            }, 180);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (input.closest('#global-search-modal')) return;
            if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.classList.add('hidden');
            }
        });
    });
}

// ==========================================================================
// Attendance 1-Tap Toggle
// ==========================================================================
function initAttendanceToggles() {
    const statusConfig = {
        P: {
            active: ['bg-emerald-600', 'text-white', 'ring-2', 'ring-emerald-400', 'ring-offset-1', 'dark:ring-offset-slate-900', 'shadow-sm'],
            inactive: ['bg-slate-100', 'dark:bg-slate-800', 'text-emerald-700', 'dark:text-emerald-400', 'border', 'border-slate-200', 'dark:border-slate-700']
        },
        A: {
            active: ['bg-rose-600', 'text-white', 'ring-2', 'ring-rose-400', 'ring-offset-1', 'dark:ring-offset-slate-900', 'shadow-sm'],
            inactive: ['bg-slate-100', 'dark:bg-slate-800', 'text-rose-700', 'dark:text-rose-400', 'border', 'border-slate-200', 'dark:border-slate-700']
        },
        L: {
            active: ['bg-amber-600', 'text-white', 'ring-2', 'ring-amber-400', 'ring-offset-1', 'dark:ring-offset-slate-900', 'shadow-sm'],
            inactive: ['bg-slate-100', 'dark:bg-slate-800', 'text-amber-700', 'dark:text-amber-400', 'border', 'border-slate-200', 'dark:border-slate-700']
        },
        E: {
            active: ['bg-blue-600', 'text-white', 'ring-2', 'ring-blue-400', 'ring-offset-1', 'dark:ring-offset-slate-900', 'shadow-sm'],
            inactive: ['bg-slate-100', 'dark:bg-slate-800', 'text-blue-700', 'dark:text-blue-400', 'border', 'border-slate-200', 'dark:border-slate-700']
        }
    };

    const updateRowButtons = (row, activeStatus) => {
        const buttons = row.querySelectorAll('.att-toggle-btn');
        buttons.forEach(btn => {
            const st = btn.dataset.status;
            const cfg = statusConfig[st];
            if (!cfg) return;

            Object.values(statusConfig).forEach(c => {
                btn.classList.remove(...c.active, ...c.inactive);
            });

            if (st === activeStatus) {
                btn.classList.add(...cfg.active);
            } else {
                btn.classList.add(...cfg.inactive);
            }
        });
    };

    const rows = document.querySelectorAll('.attendance-student-row');
    rows.forEach(row => {
        const input = row.querySelector('.attendance-status-input');
        const buttons = row.querySelectorAll('.att-toggle-btn');
        if (!input || !buttons.length) return;

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetStatus = btn.dataset.status;
                input.value = targetStatus;
                updateRowButtons(row, targetStatus);
            });
        });
    });

    // "Mark All Present" button
    const markAllPresentBtn = document.getElementById('mark-all-present-btn');
    if (markAllPresentBtn) {
        markAllPresentBtn.addEventListener('click', () => {
            document.querySelectorAll('.attendance-student-row').forEach(row => {
                const presentBtn = row.querySelector('.att-toggle-btn[data-status="P"]');
                if (presentBtn) {
                    presentBtn.click();
                }
            });
        });
    }
}

// ==========================================================================
// WhatsApp Helper
// ==========================================================================
function openWhatsApp(phone, studentName, adminId) {
    if (!phone) return;
    const cleanPhone = phone.replace(/[^0-9]/g, '');
    const message = encodeURIComponent(`Hello, this is regarding ${studentName} (Admin ID: ${adminId}) from Class Aid.`);
    window.open(`https://wa.me/${cleanPhone}?text=${message}`, '_blank');
}

// ==========================================================================
// Browser Push & Overdue Notifications Engine
// ==========================================================================
const OverdueNotifications = {
    checkInterval: 60000, // 60 seconds
    timer: null,

    init() {
        if (!document.getElementById('notifications-wrapper')) return;

        // Auto request permission on first interaction if supported and default
        if ('Notification' in window && Notification.permission === 'default') {
            document.addEventListener('click', () => {
                if (Notification.permission === 'default' && !sessionStorage.getItem('notif_prompted')) {
                    sessionStorage.setItem('notif_prompted', 'true');
                    Notification.requestPermission();
                }
            }, { once: true });
        }

        this.fetchOverdue();
        this.timer = setInterval(() => this.fetchOverdue(), this.checkInterval);
    },

    async fetchOverdue() {
        try {
            const res = await fetch('/api/notifications/overdue', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data || !data.success || !Array.isArray(data.items)) return;

            const alertedMap = JSON.parse(sessionStorage.getItem('alerted_overdue_ids') || '{}');
            let hasNewAlert = false;

            data.items.forEach(item => {
                if (!alertedMap[item.id]) {
                    alertedMap[item.id] = Date.now();
                    hasNewAlert = true;
                    this.notifyUser(item);
                }
            });

            if (hasNewAlert) {
                sessionStorage.setItem('alerted_overdue_ids', JSON.stringify(alertedMap));
            }
        } catch (e) {
            // Ignore background network error
        }
    },

    notifyUser(item) {
        // 1. Native Browser Push Notification
        if ('Notification' in window && Notification.permission === 'granted') {
            try {
                const notif = new Notification(item.title, {
                    body: item.message,
                    icon: '/favicon.ico',
                    tag: item.id
                });
                notif.onclick = () => {
                    window.focus();
                    if (item.url) window.location.href = item.url;
                };
            } catch (err) {
                // Ignore restriction
            }
        }

        // 2. In-App Liquid Glass Toast Alert
        this.showInAppToast(item);
    },

    showInAppToast(item) {
        let toastContainer = document.getElementById('class-aid-toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'class-aid-toast-container';
            toastContainer.className = 'fixed top-16 right-4 z-50 flex flex-col gap-2 max-w-sm pointer-events-none';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto p-4 rounded-2xl glass-card bg-white/95 dark:bg-slate-900/95 border border-indigo-500/40 shadow-2xl flex items-start gap-3 animate-in slide-in-from-top-4 duration-300';
        toast.innerHTML = `
            <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                <i data-lucide="${item.type === 'call_followup' ? 'phone-outgoing' : 'calendar-clock'}" class="w-4 h-4"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-xs font-bold text-slate-900 dark:text-white">${item.title}</h4>
                <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 leading-relaxed">${item.message}</p>
                ${item.url ? `<a href="${item.url}" class="inline-block mt-2 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">View & Settle &rarr;</a>` : ''}
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" onclick="this.parentElement.remove()">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        `;
        toastContainer.appendChild(toast);
        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }

        setTimeout(() => {
            toast.classList.add('opacity-0', 'transition-opacity');
            setTimeout(() => toast.remove(), 400);
        }, 8000);
    }
};

// ==========================================================================
// Desktop Navigation Ribbon Scroll Engine (Hover, Mouse Wheel & Arrows)
// ==========================================================================
const NavRibbonScroll = {
    ribbon: null,
    leftBtn: null,
    rightBtn: null,

    init() {
        this.ribbon = document.getElementById('desktop-nav-ribbon');
        this.leftBtn = document.getElementById('nav-scroll-left-btn');
        this.rightBtn = document.getElementById('nav-scroll-right-btn');

        if (!this.ribbon) return;

        // Horizontal mouse-wheel support
        this.ribbon.addEventListener('wheel', (e) => {
            if (e.deltaY !== 0) {
                e.preventDefault();
                this.ribbon.scrollLeft += e.deltaY;
                this.updateArrows();
            }
        }, { passive: false });

        this.ribbon.addEventListener('scroll', () => {
            this.updateArrows();
        }, { passive: true });

        window.addEventListener('resize', () => {
            this.updateArrows();
        });

        // Initialize state
        this.updateArrows();
    },

    scroll(amount) {
        if (!this.ribbon) return;
        this.ribbon.scrollBy({ left: amount, behavior: 'smooth' });
        setTimeout(() => this.updateArrows(), 250);
    },

    updateArrows() {
        if (!this.ribbon || !this.leftBtn || !this.rightBtn) return;
        const maxScroll = this.ribbon.scrollWidth - this.ribbon.clientWidth;

        // If content fits completely without scroll
        if (maxScroll <= 4) {
            this.leftBtn.classList.add('hidden');
            this.rightBtn.classList.add('hidden');
            return;
        }

        // Show buttons
        this.leftBtn.classList.remove('hidden');
        this.rightBtn.classList.remove('hidden');

        // Toggle state based on scroll boundaries
        if (this.ribbon.scrollLeft <= 5) {
            this.leftBtn.classList.add('opacity-30', 'pointer-events-none');
            this.leftBtn.classList.remove('opacity-100');
        } else {
            this.leftBtn.classList.remove('opacity-30', 'pointer-events-none');
            this.leftBtn.classList.add('opacity-100');
        }

        if (this.ribbon.scrollLeft >= maxScroll - 5) {
            this.rightBtn.classList.add('opacity-30', 'pointer-events-none');
            this.rightBtn.classList.remove('opacity-100');
        } else {
            this.rightBtn.classList.remove('opacity-30', 'pointer-events-none');
            this.rightBtn.classList.add('opacity-100');
        }
    }
};

window.scrollNavRibbon = function(amount) {
    NavRibbonScroll.scroll(amount);
};

// ==========================================================================
// DOM Initialization
// ==========================================================================
function initApp() {
    PageLoader.init();
    applyTheme();
    initSearch();
    initAttendanceToggles();
    OverdueNotifications.init();
    NavRibbonScroll.init();
    if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
        lucide.createIcons();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
