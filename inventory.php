<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Package and Inventory Tracker</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        // Tailwind Configuration
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'app-bg-light': '#f7f8fc',
                        'app-bg-dark': '#1e293b',
                    }
                }
            }
        }
    </script>

    <style>
        /* Base Styling and Dark Mode */
        body {
            @apply bg-app-bg-light dark:bg-app-bg-dark text-slate-800 dark:text-slate-100 transition-colors duration-300 min-h-screen font-sans p-4 md:p-8;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        
        /* Print Styles */
        @media print {
            @page { margin: 0.5cm; }
            body { 
                -webkit-print-color-adjust: exact; 
                background-color: white !important; 
                color: black !important; 
                padding: 0;
            }
            .no-print { display: none !important; }
            .card { 
                border: 1px solid #eee; 
                box-shadow: none; 
                page-break-inside: avoid; /* Prevents cards from breaking across pages */
            }
        }
        
        /* Toast Animation */
        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .toast-enter {
            animation: slideUp 0.3s ease-out forwards;
        }
    </style>
</head>
<body>

    <div class="max-w-6xl mx-auto space-y-6">
        
        <header class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8 no-print">
            <div>
                <h1 class="text-4xl font-extrabold flex items-center gap-3 dark:text-white">
                    <i data-lucide="package-check" class="w-8 h-8 text-indigo-600"></i>
                    Secure Inventory Tracker
                </h1>
                <p class="mt-1 text-slate-500 dark:text-slate-400">
                    Log and track all incoming and outgoing packages securely.
                </p>
            </div>
            <div class="flex gap-3 items-center">
                <div id="userInfo" class="text-sm font-medium text-slate-700 dark:text-slate-300 hidden">
                    </div>
                
<button id="exportPdfBtn" class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg font-medium transition-colors shadow-md">
        <i data-lucide="file-text" class="w-4 h-4"></i>
        Export PDF
    </button>
    <button onclick="window.location.href='admin.php'" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg font-medium transition-colors shadow-md">
        <i data-lucide="user-check" class="w-4 h-4"></i>
        Admin
    </button>
    <button id="logoutBtn" class="flex items-center gap-2 bg-slate-400 hover:bg-slate-500 text-white px-4 py-2.5 rounded-lg font-medium transition-colors shadow-md hidden">
        <i data-lucide="log-out" class="w-4 h-4"></i>
        Logout
    </button>
</div>
        </header>

        <section class="card bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6 md:p-8 transition-colors no-print">
            <div class="flex items-center justify-between mb-6">
                <h2 id="formTitle" class="text-xl font-semibold flex items-center gap-2 dark:text-white">
                    <i data-lucide="package-plus" class="w-5 h-5 text-indigo-500"></i>
                    Log New Package
                </h2>
                </div>

            <form id="packageForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label for="personName" class="block text-sm font-medium dark:text-slate-200">Name of Recipient *</label>
                        <input type="text" id="personName" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all" placeholder="e.g., Jane Doe / Dept A">
                    </div>
                    <div class="space-y-1">
                        <label for="loggedBy" class="block text-sm font-medium dark:text-slate-200">Logged By *</label>
                        <input type="text" id="loggedBy" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all" placeholder="Your name (Will be set automatically)">
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="itemName" class="block text-sm font-medium dark:text-slate-200">Item Name/Description *</label>
                    <input type="text" id="itemName" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all" placeholder="e.g., Server Rack Unit, 5 boxes of manuals">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label for="quantity" class="block text-sm font-medium dark:text-slate-200">Quantity *</label>
                        <input type="number" id="quantity" required min="1" value="1" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all">
                    </div>
                    <div class="space-y-1">
                        <label for="weight" class="block text-sm font-medium dark:text-slate-200">Weight (lbs) *</label>
                        <input type="number" id="weight" step="0.1" required min="0" value="0.0" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-1">
                        <label for="tracking" class="block text-sm font-medium dark:text-slate-200">Tracking Number</label>
                        <input type="text" id="tracking" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all" placeholder="Optional">
                    </div>
                    <div class="space-y-1">
                        <label for="poNumber" class="block text-sm font-medium dark:text-slate-200">PO Number</label>
                        <input type="text" id="poNumber" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all" placeholder="Optional">
                    </div>
                    <div class="space-y-1">
                        <label for="location" class="block text-sm font-medium dark:text-slate-200">Location Sent To</label>
                        <input type="text" id="location" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all" placeholder="e.g., Warehouse 3, Desk 12">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label class="block text-sm font-medium dark:text-slate-200">Was Tally Performed? *</label>
                        <div class="flex gap-6 mt-2 dark:text-slate-300">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="isTally" value="Yes" checked class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                <span>Yes</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="isTally" value="No" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                <span>No</span>
                            </label>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium dark:text-slate-200">Was Damaged? *</label>
                        <div class="flex gap-6 mt-2 dark:text-slate-300">
                            <label class="flex items-center gap-2 cursor-pointer text-red-500 font-medium">
                                <input type="radio" name="isDamaged" value="Yes" class="w-4 h-4 text-red-600 focus:ring-red-500 border-red-300">
                                <span>Yes, Flagged</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="isDamaged" value="No" checked class="w-4 h-4 text-red-600 focus:ring-red-500 border-slate-300">
                                <span>No</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" id="packageIdToEdit">

                <div class="flex gap-4">
                    <button type="submit" id="submitBtn" class="flex-grow py-3 px-4 rounded-lg font-semibold shadow-xl transition-all flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white transform hover:scale-[1.01] active:scale-95">
                        <i data-lucide="archive" class="w-5 h-5"></i>
                        <span>Log New Package</span>
                    </button>
                    <button type="button" id="cancelEditBtn" class="hidden py-3 px-4 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 bg-slate-300 hover:bg-slate-400 text-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-white active:scale-95">
                        <i data-lucide="x" class="w-5 h-5"></i>
                        <span>Cancel Edit</span>
                    </button>
                </div>
            </form>
        </section>

        <div class="relative no-print">
            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
            <input type="text" id="searchInput" placeholder="Search by recipient, item description, PO #, or tracking..." class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 bg-white dark:bg-slate-800 dark:border-slate-700 dark:text-white shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
        </div>

        <section>
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4 dark:text-slate-300 no-print">
                <div id="itemCount" class="text-sm font-medium text-slate-600 dark:text-slate-400">
                    0 Items Logged
                </div>
                
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Sort By:</span>
                    <select id="sortKeySelect" class="bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="id">Log Date</option>
                        <option value="personName">Recipient Name</option>
                        <option value="itemName">Item Name</option>
                        <option value="weight">Weight</option>
                    </select>
                    <button id="sortDirectionToggle" class="p-2 rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors" title="Toggle Sort Direction">
                        <i data-lucide="chevron-down" id="sortIcon" class="w-4 h-4 text-indigo-500"></i>
                    </button>
                </div>
            </div>

            <div id="cardGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                </div>

            <div id="emptyState" class="hidden p-16 text-center text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 mt-6">
                <i data-lucide="package-search" class="w-8 h-8 mx-auto mb-3 text-slate-400"></i>
                <p class="font-medium">No packages found matching your criteria.</p>
                <p class="text-sm">Try clearing the search bar or logging a new package above.</p>
            </div>
        </section>

    </div>

    <div id="toastContainer" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2"></div>

    <script>
        // Use window.jspdf to access the library
        const { jsPDF } = window.jspdf;

        // --- App State & Configuration ---
        const App = {
            // NOTE: CHANGE THIS URL to your actual API endpoint if different!
            // If you are using the same PHP file for API (e.g., using a query string), change this to './inventory.php?action=api' or similar
            API_URL: "http://h8logs.run.place/tracker/api.php", 
            
            packages: [], // Data will be fetched from the database
            
            // NEW: Authentication State using localStorage
            user: {
                username: localStorage.getItem('username'),
                role: localStorage.getItem('userRole')
            },
            editingId: null, // NEW: Track the package ID currently being edited

            darkMode: localStorage.getItem("darkMode") === "true",
            sort: {
                key: localStorage.getItem("sortKey") || 'id', 
                direction: localStorage.getItem("sortDirection") || 'desc' 
            },

            // --- DOM Elements ---
            D: {
                form: document.getElementById('packageForm'),
                cardGrid: document.getElementById('cardGrid'),
                emptyState: document.getElementById('emptyState'),
                searchInput: document.getElementById('searchInput'),
                exportPdfBtn: document.getElementById('exportPdfBtn'), 
                formTitle: document.getElementById('formTitle'),
                submitBtn: document.getElementById('submitBtn'),
                
                // NEW: Admin/Auth Elements
                cancelEditBtn: document.getElementById('cancelEditBtn'),
                packageIdToEdit: document.getElementById('packageIdToEdit'),
                logoutBtn: document.getElementById('logoutBtn'),
                userInfo: document.getElementById('userInfo'),
                
                itemCount: document.getElementById('itemCount'),
                body: document.body,
                sortKeySelect: document.getElementById('sortKeySelect'),
                sortDirectionToggle: document.getElementById('sortDirectionToggle'),
                sortIcon: document.getElementById('sortIcon'),
            },

            // --- Initialization ---
            init() {
                // NEW: Check for authentication immediately
                this.checkAuth(); 

                this.applyDarkMode();
                this.addEventListeners();
                this.updateSortUI();
                this.fetchPackages();
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            },
            
            // NEW: Auth Check
            checkAuth() {
                // If username is not in local storage, redirect to login page
                if (!this.user.username) {
                    window.location.href = 'login.php';
                    return;
                }
                
                // Display user info and logout button
                this.D.userInfo.classList.remove('hidden');
                this.D.userInfo.innerHTML = `Signed in: <strong>${this.user.username}</strong> (<span class="${this.user.role === 'admin' ? 'text-red-500' : 'text-green-500'}">${this.user.role.toUpperCase()}</span>)`;
                this.D.logoutBtn.classList.remove('hidden');

                // Pre-fill the 'Logged By' field with the current user's name
                document.getElementById('loggedBy').value = this.user.username;
                
                // Read-only check for non-admin/non-logger users (optional)
                // If you want all users to be able to log packages, remove this conditional check
                // if (this.user.role === 'user') {
                //    this.D.form.querySelector('input[name="loggedBy"]').readOnly = true;
                // }
            },
            
            // NEW: Logout Handler
            async handleLogout() {
                try {
                    // Call API to destroy PHP session
                    await fetch(`${App.API_URL}?action=logout`, { method: 'POST' });
                } catch (error) {
                    console.warn("Logout endpoint failed, proceeding with client-side cleanup:", error);
                }
                
                // Client-side cleanup and redirect
                localStorage.removeItem('username');
                localStorage.removeItem('userRole');
                this.showToast('You have been logged out. Redirecting...', 'info');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 500);
            },
            
            applyDarkMode() {
                this.D.body.classList.toggle('dark', this.darkMode);
                localStorage.setItem("darkMode", this.darkMode);
            },

            saveToStorage() {
                localStorage.setItem("darkMode", this.darkMode);
                localStorage.setItem("sortKey", this.sort.key);
                localStorage.setItem("sortDirection", this.sort.direction);
            },

            // --- Data Fetching & API Communication ---
            async fetchPackages() {
                try {
                    const response = await fetch(this.API_URL);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    
                    const data = await response.json();
                    
                    this.packages = data.map(pkg => ({
                        ...pkg,
                        id: parseInt(pkg.id), 
                        personName: String(pkg.personName), 
                        itemName: String(pkg.itemName),     
                        quantity: parseInt(pkg.quantity),
                        weight: parseFloat(pkg.weight)
                    }));
                    
                    this.renderTable();
                } catch (error) {
                    console.error("Error fetching packages:", error);
                    this.showToast('Failed to load data from server. Check XAMPP/API setup.', 'error');
                }
            },

            // --- UI Helpers ---
            showToast(message, type = 'success') {
                const toast = document.createElement('div');
                const colorMap = {
                    'success': 'bg-emerald-100 border-emerald-300 text-emerald-800 dark:bg-emerald-900/50 dark:border-emerald-700 dark:text-emerald-300',
                    'error': 'bg-red-100 border-red-300 text-red-800 dark:bg-red-900/50 dark:border-red-700 dark:text-red-300',
                    'info': 'bg-blue-100 border-blue-300 text-blue-800 dark:bg-blue-900/50 dark:border-blue-700 dark:text-blue-300',
                };
                const iconMap = {
                    'success': 'check-circle-2',
                    'error': 'alert-triangle',
                    'info': 'info',
                };
                
                toast.className = `toast-enter p-4 rounded-xl shadow-lg border flex items-center gap-3 ${colorMap[type]}`;
                toast.innerHTML = `<i data-lucide="${iconMap[type]}" class="w-5 h-5"></i><span class="font-medium">${message}</span>`;
                
                document.getElementById('toastContainer').appendChild(toast);
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(10px)';
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            },
            
            // --- Data Processing ---
            getFormattedDate(timestamp) {
                const date = new Date(timestamp);
                const timeStr = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const dateStr = date.toLocaleDateString([], { year: 'numeric', month: 'short', day: 'numeric' });
                return { full: `${dateStr} @ ${timeStr}`, short: dateStr, time: timeStr };
            },

            // --- Sorting Logic ---
            sortPackages(a, b) {
                const { key, direction } = App.sort;
                let valA, valB;

                switch (key) {
                    case 'id':
                    case 'weight':
                        valA = parseFloat(a[key] || 0);
                        valB = parseFloat(b[key] || 0);
                        break;
                    case 'personName':
                    case 'itemName':
                        valA = a[key].toLowerCase();
                        valB = b[key].toLowerCase();
                        if (valA < valB) return direction === 'asc' ? -1 : 1;
                        if (valA > valB) return direction === 'asc' ? 1 : -1;
                        return 0;
                    default:
                        return 0;
                }
                
                return direction === 'asc' ? valA - valB : valB - valA;
            },
            
            handleSortChange() {
                App.sort.key = App.D.sortKeySelect.value;
                App.sort.direction = (App.sort.key === 'id' || App.sort.key === 'weight') ? 'desc' : 'asc'; 
                App.updateSortUI();
                App.renderTable();
                App.saveToStorage();
            },

            handleSortToggle() {
                App.sort.direction = App.sort.direction === 'asc' ? 'desc' : 'asc';
                App.updateSortUI();
                App.renderTable();
                App.saveToStorage();
            },

            updateSortUI() {
                const icon = App.D.sortIcon;
                if (App.sort.direction === 'asc') {
                    icon.setAttribute('data-lucide', 'chevron-up');
                } else {
                    icon.setAttribute('data-lucide', 'chevron-down');
                }
                App.D.sortKeySelect.value = App.sort.key;
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            },

            // --- Rendering ---
            renderTable() {
                const query = this.D.searchInput.value.toLowerCase();
                
                let filtered = this.packages.filter(pkg => 
                    pkg.personName.toLowerCase().includes(query) ||
                    pkg.itemName.toLowerCase().includes(query) ||
                    (pkg.tracking || '').toLowerCase().includes(query) ||
                    (pkg.poNumber || '').toLowerCase().includes(query) ||
                    pkg.loggedBy.toLowerCase().includes(query)
                );

                filtered.sort(this.sortPackages.bind(this));

                this.D.cardGrid.innerHTML = '';
                this.D.itemCount.textContent = `${filtered.length} Item${filtered.length !== 1 ? 's' : ''} Logged`;
                
                if (filtered.length === 0) {
                    this.D.emptyState.classList.remove('hidden');
                    this.D.cardGrid.classList.add('hidden');
                } else {
                    this.D.emptyState.classList.add('hidden');
                    this.D.cardGrid.classList.remove('hidden');
                    
                    filtered.forEach(pkg => {
                        const dateData = this.getFormattedDate(pkg.id);
                        const isDamaged = pkg.isDamaged === 'Yes';
                        const hasTracking = pkg.tracking || pkg.poNumber || pkg.location;

                        // --- Card HTML Structure ---
                        const card = document.createElement('div');
                        card.className = `card bg-white dark:bg-slate-800 rounded-xl shadow-lg border ${isDamaged ? 'border-red-400 dark:border-red-600 ring-4 ring-red-100 dark:ring-red-900/30' : 'border-slate-200 dark:border-slate-700'} p-5 transition-all hover:shadow-xl hover:scale-[1.01]`;

                        card.innerHTML = `
                            <div class="flex justify-between items-start mb-3 border-b pb-3 border-slate-100 dark:border-slate-700/50">
                                <div>
                                    <div class="text-xl font-bold ${isDamaged ? 'text-red-600 dark:text-red-400' : 'text-indigo-600 dark:text-indigo-400'}">${pkg.personName}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Logged by: ${pkg.loggedBy}</div>
                                </div>
                                <div class="text-right">
                                    <div class="flex flex-col gap-1 items-end">
                                        ${isDamaged ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300"><i data-lucide="alert-triangle" class="w-3 h-3 mr-1"></i> DAMAGED</span>` : ''}
                                        ${pkg.isTally === 'Yes' ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300"><i data-lucide="check" class="w-3 h-3 mr-1"></i> TALLY DONE</span>` : `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300"><i data-lucide="x" class="w-3 h-3 mr-1"></i> NO TALLY</span>`}
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div>
                                    <div class="text-xs font-semibold uppercase text-slate-400 dark:text-slate-500">Item Description</div>
                                    <div class="text-base font-medium dark:text-white">${pkg.itemName}</div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <div class="text-xs font-semibold uppercase text-slate-400 dark:text-slate-500">Quantity</div>
                                        <div class="font-mono font-bold text-lg">${pkg.quantity} <span class="text-xs font-normal">pcs</span></div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold uppercase text-slate-400 dark:text-slate-500">Weight</div>
                                        <div class="font-mono font-bold text-lg">${parseFloat(pkg.weight).toFixed(1)} <span class="text-xs font-normal">lbs</span></div>
                                    </div>
                                </div>
                                
                                <div class="pt-3 border-t border-slate-100 dark:border-slate-700/50 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                                    ${pkg.location ? `<div><span class="font-medium text-slate-500 dark:text-slate-400">Location:</span> ${pkg.location}</div>` : ''}
                                    ${pkg.tracking ? `<div><span class="font-medium text-slate-500 dark:text-slate-400">Tracking:</span> ${pkg.tracking}</div>` : ''}
                                    ${pkg.poNumber ? `<div><span class="font-medium text-slate-500 dark:text-slate-400">PO #:</span> ${pkg.poNumber}</div>` : ''}
                                    ${!hasTracking ? '<div class="text-xs opacity-50">No tracking or location details provided.</div>' : ''}
                                </div>
                            </div>

                            <div class="flex justify-between items-center pt-4 border-t border-slate-200 dark:border-slate-700/50">
                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                    <i data-lucide="clock" class="w-3 h-3 inline mr-1 -mt-0.5"></i>
                                    ${dateData.full}
                                </div>
                                ${App.user.role === 'admin' ? `
                                <div class="flex gap-2">
                                    <button onclick="App.handleEdit(${pkg.id})" class="p-1.5 rounded-md text-indigo-500 hover:bg-indigo-100 dark:hover:bg-slate-700 transition-colors" title="Edit Item">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="App.handleDelete(${pkg.id})" class="p-1.5 rounded-md text-red-500 hover:bg-red-100 dark:hover:bg-slate-700 transition-colors" title="Delete Item">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                ` : ''}
                                </div>
                        `;

                        this.D.cardGrid.appendChild(card);
                    });
                    
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }
            },

            // --- Form Handlers ---

            async submitForm(e) {
                e.preventDefault();
                
                const formData = {
                    // FIX: Explicitly cast to String to prevent backend numeric coercion on new records
                    personName: String(document.getElementById('personName').value).trim(),
                    loggedBy: document.getElementById('loggedBy').value.trim(),
                    // FIX: Explicitly cast to String to prevent backend numeric coercion on new records
                    itemName: String(document.getElementById('itemName').value).trim(),
                    quantity: parseInt(document.getElementById('quantity').value),
                    weight: parseFloat(document.getElementById('weight').value),
                    tracking: document.getElementById('tracking').value.trim(),
                    poNumber: document.getElementById('poNumber').value.trim(),
                    location: document.getElementById('location').value.trim(),
                    isTally: document.querySelector('input[name="isTally"]:checked').value,
                    isDamaged: document.querySelector('input[name="isDamaged"]:checked').value,
                };
                
                // New validation to ensure text fields are not empty
                if (!formData.personName) {
                    App.showToast('Name of Recipient cannot be empty.', 'error');
                    return;
                }
                if (!formData.itemName) {
                    App.showToast('Item Name/Description cannot be empty.', 'error');
                    return;
                }
                
                if (isNaN(formData.weight) || isNaN(formData.quantity) || formData.quantity <= 0) {
                    App.showToast('Please ensure Quantity is a positive number and Weight is valid.', 'error');
                    return;
                }
                
                let method = 'POST';
                let endpoint = App.API_URL;
                
                // Check if in edit mode
                if (App.editingId) {
                    if (App.user.role !== 'admin') {
                        App.showToast('Error: Only Admins can submit edits.', 'error');
                        return;
                    }
                    // Add ID to formData for update
                    formData.id_to_edit = App.editingId; 
                    App.showToast('Submitting update...', 'info');
                } else {
                    // Create new entry
                    formData.id = Date.now(); 
                    App.showToast('Submitting new package...', 'info');
                }

                try {
                    const response = await fetch(endpoint, {
                        method: method,
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(formData)
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) throw new Error(data.message || "Failed to process record on server.");
                    
                    if (App.editingId) {
                        App.showToast('Package successfully updated!', 'success');
                        App.exitEditMode();
                    } else {
                        App.showToast('New package successfully added to inventory!', 'success');
                        App.D.form.reset();
                    }
                    
                    App.D.searchInput.value = '';
                    // Re-set defaults after successful create/edit
                    document.querySelector('input[name="isTally"][value="Yes"]').checked = true;
                    document.querySelector('input[name="isDamaged"][value="No"]').checked = true;
                    document.getElementById('loggedBy').value = App.user.username; // Reset logger name
                    
                    await App.fetchPackages();

                } catch (error) {
                    console.error("API Submission Error:", error);
                    App.showToast(`Server operation failed: ${error.message}.`, 'error');
                }
            },

            // NEW: Delete Package (Admin Only)
            async handleDelete(id) {
                if (App.user.role !== 'admin') {
                    App.showToast('Access Denied. Only Admin can delete packages.', 'error');
                    return;
                }
                
                if (!confirm(`Are you sure you want to permanently delete package ID ${id}? This action cannot be undone.`)) {
                    return;
                }
                
                try {
                    const response = await fetch(App.API_URL, {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    });
                    
                    const data = await response.json();

                    if (!response.ok) throw new Error(data.message || "Failed to delete record.");
                    
                    App.showToast('Package successfully deleted.', 'success');
                    await App.fetchPackages(); // Reload data

                } catch (error) {
                    console.error("API Deletion Error:", error);
                    App.showToast(`Server operation failed: ${error.message}.`, 'error');
                }
            },
            
            // NEW: Enter Edit Mode
            handleEdit(id) {
                if (App.user.role !== 'admin') {
                    App.showToast('Access Denied. Only Admin can edit packages.', 'error');
                    return;
                }

                const pkg = App.packages.find(p => p.id === id);
                if (!pkg) {
                    App.showToast('Package not found.', 'error');
                    return;
                }
                
                // Set form fields to package values
                document.getElementById('personName').value = pkg.personName;
                document.getElementById('loggedBy').value = pkg.loggedBy;
                document.getElementById('itemName').value = pkg.itemName;
                document.getElementById('quantity').value = pkg.quantity;
                document.getElementById('weight').value = pkg.weight.toFixed(1);
                document.getElementById('tracking').value = pkg.tracking || '';
                document.getElementById('poNumber').value = pkg.poNumber || '';
                document.getElementById('location').value = pkg.location || '';
                document.querySelector(`input[name="isTally"][value="${pkg.isTally}"]`).checked = true;
                document.querySelector(`input[name="isDamaged"][value="${pkg.isDamaged}"]`).checked = true;

                // Update UI for editing
                App.editingId = id;
                App.D.packageIdToEdit.value = id;
                App.D.formTitle.innerHTML = `<i data-lucide="pencil" class="w-5 h-5 text-indigo-500"></i>Edit Package ID ${id}`;
                App.D.submitBtn.innerHTML = `<i data-lucide="save" class="w-5 h-5"></i><span>Save Changes</span>`;
                App.D.submitBtn.classList.add('bg-orange-600', 'hover:bg-orange-700');
                App.D.submitBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
                App.D.cancelEditBtn.classList.remove('hidden');

                // Scroll to the form
                App.D.form.scrollIntoView({ behavior: 'smooth', block: 'start' });

                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
                App.showToast(`Editing Package ID ${id}`, 'info');
            },
            
            // NEW: Exit Edit Mode
            exitEditMode() {
                App.editingId = null;
                App.D.form.reset();
                App.D.packageIdToEdit.value = '';
                
                App.D.formTitle.innerHTML = `<i data-lucide="package-plus" class="w-5 h-5 text-indigo-500"></i>Log New Package`;
                App.D.submitBtn.innerHTML = `<i data-lucide="archive" class="w-5 h-5"></i><span>Log New Package</span>`;
                App.D.submitBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
                App.D.submitBtn.classList.remove('bg-orange-600', 'hover:bg-orange-700');
                App.D.cancelEditBtn.classList.add('hidden');
                
                // Restore defaults
                document.querySelector('input[name="isTally"][value="Yes"]').checked = true;
                document.querySelector('input[name="isDamaged"][value="No"]').checked = true;
                document.getElementById('loggedBy').value = App.user.username;

                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            },

            // --- Utility Functions: PDF Generation ---

            handleExportPdf() {
                if (App.packages.length === 0) {
                    App.showToast('No data to export.', 'info');
                    return;
                }
                
                // Initialize PDF document (A4 size, portrait orientation)
                const doc = new jsPDF();
                let y = 15; // Starting Y position

                const currentDate = new Date().toLocaleDateString();
                const filename = `Inventory_Log_${new Date().toISOString().slice(0, 10)}.pdf`;

                // Header
                doc.setFontSize(18);
                doc.text("Secure Inventory Tracker Log", 15, y);
                y += 7;

                doc.setFontSize(10);
                doc.text(`Export Date: ${currentDate} | Total Items: ${App.packages.length}`, 15, y);
                y += 10;
                
                // Sorting packages for the PDF (Newest first)
                const sortedPackages = [...App.packages].sort((a, b) => b.id - a.id);

                doc.setFontSize(10);
                
                // Helper function for adding new page and header
                const addNewPage = () => {
                    doc.addPage();
                    y = 15;
                    doc.setFontSize(14);
                    doc.text("Inventory Log (Continued)", 15, y);
                    y += 5;
                    doc.setFontSize(10);
                    doc.text(`Page: ${doc.internal.pages.length - 1}`, 180, 10);
                    y += 5;
                };

                // Add content for each package
                sortedPackages.forEach((pkg, index) => {
                    const lineSpacing = 6;
                    
                    // Check if we need a new page
                    if (y + (lineSpacing * 6) > doc.internal.pageSize.height - 15) {
                        addNewPage();
                    }
                    
                    const logDate = App.getFormattedDate(pkg.id).full;
                    const damageStatus = pkg.isDamaged === 'Yes' ? 'FLAGGED: DAMAGED' : 'No Damage';
                    const tallyStatus = pkg.isTally === 'Yes' ? 'Tally Done' : 'NO TALLY';
                    
                    // Boxed item appearance
                    doc.setDrawColor(200);
                    doc.rect(14, y, 182, lineSpacing * 5); // Draw a rectangle around the entry
                    y += lineSpacing * 0.75;
                    
                    doc.setFont("helvetica", "bold");
                    doc.setFontSize(12);
                    doc.text(`${index + 1}. ${pkg.personName}`, 16, y);
                    doc.setFont("helvetica", "normal");
                    
                    doc.setFontSize(10);
                    y += lineSpacing;
                    doc.text(`Item: ${pkg.itemName}`, 16, y);
                    doc.text(`Qty: ${pkg.quantity} / Wt: ${pkg.weight} lbs`, 120, y);
                    
                    y += lineSpacing;
                    doc.text(`Logged By: ${pkg.loggedBy}`, 16, y);
                    doc.text(`Status: ${damageStatus} | ${tallyStatus}`, 120, y);
                    
                    y += lineSpacing;
                    doc.text(`Location: ${pkg.location || 'N/A'}`, 16, y);
                    doc.text(`Tracking/PO: ${pkg.tracking || pkg.poNumber || 'N/A'}`, 120, y);
                    
                    y += lineSpacing;
                    doc.text(`Log Date: ${logDate}`, 16, y);
                    
                    y += lineSpacing * 1.5; // Extra spacing before the next box
                });

                // Final save
                doc.save(filename);
                App.showToast('Exported inventory log to PDF!', 'success');
            },

            // --- Event Listeners Setup ---

            addEventListeners() {
                this.D.form.addEventListener('submit', this.submitForm.bind(this));
                this.D.searchInput.addEventListener('input', () => this.renderTable());
                
                
                this.D.exportPdfBtn.addEventListener('click', this.handleExportPdf);
                
                // NEW: Cancel Edit Button Listener
                this.D.cancelEditBtn.addEventListener('click', this.exitEditMode.bind(this));
                
                // NEW: Logout Button Listener
                this.D.logoutBtn.addEventListener('click', this.handleLogout.bind(this));

                this.D.sortKeySelect.addEventListener('change', this.handleSortChange);
                this.D.sortDirectionToggle.addEventListener('click', this.handleSortToggle);
            }
        };

        // Start the application after the DOM is fully loaded
        document.addEventListener('DOMContentLoaded', () => {
            App.init();
        });
    </script>
</body>
</html>
