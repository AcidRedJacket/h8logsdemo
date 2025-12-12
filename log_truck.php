<?php
// CRITICAL: Start the session and check authentication
session_start();

// If the user is not logged in, redirect them to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the logged-in username to pre-fill the form
$logged_in_user = $_SESSION['username'] ?? 'Unknown User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Truck Movement</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <script>
        // Tailwind Configuration (Same as other files)
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
    </style>
</head>
<body>

    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <div class="max-w-3xl mx-auto bg-white dark:bg-slate-800 shadow-xl rounded-xl p-6 md:p-10">
        <header class="mb-8 border-b pb-4 dark:border-slate-700">
            <h1 class="text-3xl font-bold flex items-center gap-3 text-indigo-600 dark:text-indigo-400">
                <i data-lucide="truck" class="w-7 h-7"></i>
                Log Truck Movement
            </h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Record inbound or outbound truck activity.</p>
        </header>

        <form id="truckLogForm" class="space-y-6">
            
            <div>
                <label for="truckName" class="block text-sm font-medium mb-1">Truck Name / Unit # <span class="text-red-500">*</span></label>
                <input 
                    type="text" 
                    id="truckName" 
                    name="truckName" 
                    required 
                    class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="e.g., Swift 104, Unit A-5">
            </div>

            <div>
                <label for="loggedBy" class="block text-sm font-medium mb-1">Person Logged By <span class="text-red-500">*</span></label>
                <input 
                    type="text" 
                    id="loggedBy" 
                    name="loggedBy" 
                    required 
                    value="<?php echo htmlspecialchars($logged_in_user); ?>"
                    readonly
                    class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 cursor-not-allowed">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="dateLogged" class="block text-sm font-medium mb-1">Date Logged</label>
                    <input 
                        type="text" 
                        id="dateLogged" 
                        name="dateLogged" 
                        readonly 
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 cursor-not-allowed">
                </div>
                <div>
                    <label for="timeLogged" class="block text-sm font-medium mb-1">Time Logged</label>
                    <input 
                        type="text" 
                        id="timeLogged" 
                        name="timeLogged" 
                        readonly 
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 cursor-not-allowed">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Movement Type</label>
                <div class="flex gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="movementType" value="Inbound" checked class="form-radio text-indigo-600 dark:text-indigo-400">
                        <span class="ml-2">Inbound (Arrival)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="movementType" value="Outbound" class="form-radio text-indigo-600 dark:text-indigo-400">
                        <span class="ml-2">Outbound (Departure)</span>
                    </label>
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium mb-1">Notes (Optional)</label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    rows="3" 
                    class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Details about load, destination, or issues..."></textarea>
            </div>

            <button 
                type="submit" 
                class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:ring-offset-slate-800">
                <i data-lucide="send" class="w-4 h-4"></i>
                Log Truck Movement
            </button>
        </form>

        <div id="logHistory" class="mt-10 pt-6 border-t dark:border-slate-700">
            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                <i data-lucide="history" class="w-5 h-5"></i>
                Last Logged Activity
            </h2>
            <div id="historyTable" class="overflow-x-auto">
                <p class="text-slate-500 dark:text-slate-400" id="noHistoryMsg">Loading truck log history...</p>
            </div>
        </div>

    </div>

    <script>
        // --- Utility Functions ---

        const API_URL = 'http://h8logs.run.place/config/api.php'; // Your main API endpoint
        const showToast = (message, type = 'info') => {
            // ... [Toast code from previous file] ...
            const iconMap = {
                success: 'check-circle',
                error: 'x-circle',
                info: 'info',
                warning: 'alert-triangle'
            };
            const colorMap = {
                success: 'bg-green-100 text-green-800 border-green-300 dark:bg-green-900/50 dark:text-green-300 dark:border-green-700',
                error: 'bg-red-100 text-red-800 border-red-300 dark:bg-red-900/50 dark:text-red-300 dark:border-red-700',
                info: 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-900/50 dark:text-blue-300 dark:border-blue-700',
                warning: 'bg-yellow-100 text-yellow-800 border-yellow-300 dark:bg-yellow-900/50 dark:text-yellow-300 dark:border-yellow-700'
            };

            const toast = document.createElement('div');
            toast.className = `toast-enter p-4 rounded-xl shadow-lg border flex items-center gap-3 ${colorMap[type]}`;
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            toast.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
            
            toast.innerHTML = `<i data-lucide="${iconMap[type]}" class="w-5 h-5"></i><span class="font-medium">${message}</span>`;
            
            document.getElementById('toastContainer').appendChild(toast);
            lucide.createIcons(); // Re-render icons

            // Animate in
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            }, 10);

            // Animate out and remove
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        };

        // --- Core Application Logic ---
        const App = {
            // Data store for fetched history
            truckLogs: [],

            // Element references
            D: {
                dateLogged: document.getElementById('dateLogged'),
                timeLogged: document.getElementById('timeLogged'),
                form: document.getElementById('truckLogForm'),
                historyContainer: document.getElementById('historyTable'),
            },

            // Format current date/time and update fields
            updateDateTime() {
                const now = new Date();
                const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
                const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
                
                this.D.dateLogged.value = now.toLocaleDateString('en-US', dateOptions);
                this.D.timeLogged.value = now.toLocaleTimeString('en-US', timeOptions);
            },

            // Fetch history from API
            async fetchLogs() {
                try {
                    const response = await fetch(`${API_URL}?action=truckLogs`, {
                        method: 'GET',
                        headers: { 'Content-Type': 'application/json' }
                    });
                    
                    if (!response.ok) {
                        throw new Error('Failed to fetch truck logs from API.');
                    }

                    this.truckLogs = await response.json();
                    this.renderHistory();

                } catch (error) {
                    console.error("Fetch Truck Logs Error:", error);
                    this.D.historyContainer.innerHTML = '<p class="text-red-500 dark:text-red-400">Error loading history. Check API connection.</p>';
                    showToast('Failed to load log history.', 'error');
                }
            },

            // Handle form submission
            async handleFormSubmit(event) {
                event.preventDefault();
                
                // Get form data
                const formData = new FormData(this.D.form);
                const logData = {};
                for (let [key, value] of formData.entries()) {
                    logData[key] = value.trim();
                }

                // Add the exact timestamp for server processing
                logData.timestamp = new Date().toISOString(); 
                
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'logTruck', ...logData })
                    });

                    const result = await response.json();

                    if (response.ok) {
                        showToast(`Logged truck ${logData.truckName} as ${logData.movementType}.`, 'success');
                        
                        // Refetch logs to update the history table
                        this.fetchLogs();

                        // Reset form fields
                        this.D.form.querySelector('#truckName').value = '';
                        this.D.form.querySelector('#notes').value = '';
                        this.D.form.querySelector('#truckName').focus(); // Focus back on the main input
                        
                    } else {
                        showToast(result.message || 'Error logging truck movement.', 'error');
                    }

                } catch (error) {
                    console.error("Log Truck Error:", error);
                    showToast('A network error occurred. Could not log movement.', 'error');
                }
            },

            // Render the log history table
            renderHistory() {
                if (this.truckLogs.length === 0) {
                    this.D.historyContainer.innerHTML = '<p class="text-slate-500 dark:text-slate-400">No truck movements logged yet.</p>';
                    return;
                }
                
                let html = `
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 rounded-lg overflow-hidden">
                        <thead class="bg-slate-50 dark:bg-slate-700">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">Truck</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">Movement</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">Logged By</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">Time/Date</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                `;

                this.truckLogs.forEach(log => {
                    // Format timestamp
                    const dateObj = new Date(log.log_timestamp);
                    const timeStr = dateObj.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                    const dateStr = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

                    const isOutbound = log.movementType === 'Outbound';
                    const movementClass = isOutbound ? 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' : 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300';
                    const movementIcon = isOutbound ? 'log-out' : 'log-in';

                    html += `
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                            <td class="px-3 py-4 whitespace-nowrap text-sm font-medium">${log.truckName}</td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${movementClass}">
                                    <i data-lucide="${movementIcon}" class="w-3 h-3 mr-1"></i>
                                    ${log.movementType}
                                </span>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm">${log.loggedBy}</td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm">
                                <div class="text-xs text-slate-600 dark:text-slate-400">${timeStr}</div>
                                <div>${dateStr}</div>
                            </td>
                            <td class="px-3 py-4 text-sm max-w-xs truncate" title="${log.notes}">${log.notes || '—'}</td>
                        </tr>
                    `;
                });

                html += `
                        </tbody>
                    </table>
                `;
                this.D.historyContainer.innerHTML = html;
                lucide.createIcons(); // Re-render icons
            },

            init() {
                // Initial setup of date and time
                this.updateDateTime(); 
                
                // Update time every second
                setInterval(this.updateDateTime.bind(this), 1000); 

                // Event Listeners
                this.D.form.addEventListener('submit', this.handleFormSubmit.bind(this));
                
                // Fetch initial history from the database
                this.fetchLogs();

                // Initial icon rendering
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            App.init();
        });
    </script>
</body>
</html>