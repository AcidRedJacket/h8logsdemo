<?php
// NOTE: For security, a real PHP application would start a session here and
// check if the user is logged in AND has the 'admin' role before rendering any HTML.
// Example:
// session_start();
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     header("Location: login.php");
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management Panel</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

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
        /* Toast Animation from inventory.html */
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

    <div class="max-w-4xl mx-auto space-y-8">
        
        <header class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
            <div>
                <h1 class="text-4xl font-extrabold flex items-center gap-3 dark:text-white">
                    <i data-lucide="shield-check" class="w-8 h-8 text-red-600"></i>
                    Admin Management Panel
                </h1>
                <p class="mt-1 text-slate-500 dark:text-slate-400">
                    Manage user roles and system settings.
                </p>
            </div>
            <div class="flex gap-3 items-center">
                <a href="inventory.php" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg font-medium transition-colors shadow-md">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    Go to Inventory
                </a>
                <button id="logoutBtn" class="flex items-center gap-2 bg-slate-400 hover:bg-slate-500 text-white px-4 py-2.5 rounded-lg font-medium transition-colors shadow-md hidden">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Logout
                </button>
            </div>
        </header>

        <section class="card bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6 md:p-8 transition-colors">
            <div class="flex items-center justify-between mb-6 border-b pb-3">
                <h2 class="text-2xl font-semibold flex items-center gap-2 dark:text-white">
                    <i data-lucide="users" class="w-6 h-6 text-red-500"></i>
                    User Role Management
                </h2>
                <button id="refreshUsersBtn" class="p-2.5 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-900/50 dark:hover:bg-red-900 transition-colors" title="Refresh User List">
                    <i data-lucide="rotate-cw" class="w-5 h-5"></i>
                </button>
            </div>
            
            <div id="userListContainer" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Current Role</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Controls</th>
                        </tr>
                    </thead>
                    <tbody id="userListBody" class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        </tbody>
                </table>
                <div id="loadingState" class="p-4 text-center text-slate-500 hidden">
                    <i data-lucide="loader" class="w-5 h-5 mx-auto animate-spin"></i>
                    <p>Loading users...</p>
                </div>
            </div>
            
        </section>

        <section class="card bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6 md:p-8 transition-colors mt-8">
            <div class="flex items-center justify-between mb-6 border-b pb-3">
                <h2 class="text-2xl font-semibold flex items-center gap-2 dark:text-white">
                    <i data-lucide="key" class="w-6 h-6 text-indigo-500"></i>
                    Registration Code Management
                </h2>
                <button id="refreshCodesBtn" class="p-2.5 rounded-lg bg-indigo-100 text-indigo-600 hover:bg-indigo-200 dark:bg-indigo-900/50 dark:hover:bg-indigo-900 transition-colors" title="Refresh Code List">
                    <i data-lucide="rotate-cw" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="addCodeForm" class="mb-6 p-4 border border-dashed border-slate-300 dark:border-slate-600 rounded-lg flex flex-col md:flex-row gap-4 items-center bg-slate-50 dark:bg-slate-700/50">
                <div class="flex-grow w-full md:w-auto space-y-1">
                    <label for="newCodeInput" class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                        New Registration Code
                    </label>
                    <input type="text" id="newCodeInput" required minlength="3" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white dark:bg-slate-800 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all" placeholder="Enter new unique code (e.g., ADMIN2026)">
                </div>
                <button type="submit" id="addCodeBtn" class="w-full md:w-auto py-2.5 px-6 rounded-lg font-semibold shadow-md transition-all flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white mt-auto">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Add Code
                </button>
            </form>
            
            <div id="codeListContainer" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody id="codeListBody" class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        </tbody>
                </table>
                <div id="codesLoadingState" class="p-4 text-center text-slate-500 hidden">
                    <i data-lucide="loader" class="w-5 h-5 mx-auto animate-spin"></i>
                    <p>Loading registration codes...</p>
                </div>
            </div>
            
        </section>
        
    </div>

    <div id="toastContainer" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2"></div>

    <script>
        // --- App State & Configuration ---
        const App = {
            // NOTE: CHANGE THIS URL to your actual API endpoint if different!
            API_URL: "http://h8logs.run.place/config/api.php", 
            users: [], // Array to hold user data
            codes: [], // NEW: Array to hold registration code data
            currentUser: {
                id: parseInt(localStorage.getItem('userId')),
                username: localStorage.getItem('username'),
                role: localStorage.getItem('userRole')
            },

            // --- DOM Elements ---
            D: {
                userListBody: document.getElementById('userListBody'),
                loadingState: document.getElementById('loadingState'), // for users
                logoutBtn: document.getElementById('logoutBtn'),
                refreshUsersBtn: document.getElementById('refreshUsersBtn'),
                
                // NEW: Code Management DOM
                addCodeForm: document.getElementById('addCodeForm'),
                newCodeInput: document.getElementById('newCodeInput'),
                codeListBody: document.getElementById('codeListBody'),
                codesLoadingState: document.getElementById('codesLoadingState'),
                refreshCodesBtn: document.getElementById('refreshCodesBtn')
            },

            // --- Initialization ---
            init() {
                this.checkAuthAndAdmin();
                this.addEventListeners();
                this.fetchUsers();
                this.fetchCodes(); // NEW: Fetch codes on init
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            },
            
            // NEW: Admin Check
            checkAuthAndAdmin() {
                // If not logged in, redirect to login page
                if (!this.currentUser.username) {
                    window.location.href = 'login.php';
                    return;
                }
                // If logged in but not an admin, deny access
                if (this.currentUser.role !== 'admin') {
                    alert("Access Denied: You must be an Administrator to view this page.");
                    window.location.href = 'inventory.php'; 
                    return;
                }
                
                this.D.logoutBtn.classList.remove('hidden');
            },

            // --- Data Fetching & API Communication ---
            async fetchUsers() {
                this.D.userListBody.innerHTML = '';
                this.D.loadingState.classList.remove('hidden');
                
                try {
                    const response = await fetch(`${this.API_URL}?action=users`);
                    const data = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(data.message || `HTTP error! status: ${response.status}`);
                    }
                    
                    this.users = data;
                    this.renderUserTable();

                } catch (error) {
                    console.error("Error fetching users:", error);
                    this.showToast(`Failed to load user data: ${error.message}`, 'error');
                } finally {
                    this.D.loadingState.classList.add('hidden');
                }
            },
            
            // --- NEW: Code Data Fetching & Rendering ---
            async fetchCodes() {
                this.D.codeListBody.innerHTML = '';
                this.D.codesLoadingState.classList.remove('hidden');
                
                try {
                    const response = await fetch(`${this.API_URL}?action=getCodes`);
                    const data = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(data.message || `HTTP error! status: ${response.status}`);
                    }
                    
                    this.codes = data;
                    this.renderCodeTable();

                } catch (error) {
                    console.error("Error fetching codes:", error);
                    this.showToast(`Failed to load registration codes: ${error.message}`, 'error');
                } finally {
                    this.D.codesLoadingState.classList.add('hidden');
                }
            },

            // --- Rendering ---
            renderUserTable() {
                this.D.userListBody.innerHTML = ''; // Clear existing rows

                if (this.users.length === 0) {
                    this.D.userListBody.innerHTML = `<tr><td colspan="4" class="p-6 text-center text-slate-500">No users found in the database.</td></tr>`;
                    return;
                }

                this.users.forEach(user => {
                    const isCurrentUser = user.id == this.currentUser.id;
                    const isSelfAdmin = isCurrentUser && user.role === 'admin';
                    
                    const row = document.createElement('tr');
                    row.className = `hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors ${isCurrentUser ? 'bg-red-50 dark:bg-red-900/20' : ''}`;
                    
                    const roleColor = user.role === 'admin' ? 'text-red-500 bg-red-100 dark:bg-red-900/30 dark:text-red-300' : 'text-green-500 bg-green-100 dark:bg-green-900/30 dark:text-green-300';
                    const roleText = user.role.toUpperCase();

                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">${user.id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">${user.username} ${isCurrentUser ? '(YOU)' : ''}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold ${roleColor}">
                                ${roleText}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-y-2 md:space-y-0 md:space-x-2 flex flex-col md:flex-row items-center justify-center">
                            
                            ${isSelfAdmin ? `
                                <span class="text-slate-400 text-xs py-2">Cannot edit your own admin role</span>
                            ` : `
                                <button onclick="App.handleRoleChange(${user.id}, '${user.role}')" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors font-medium p-2 rounded-md hover:bg-slate-100 dark:hover:bg-slate-700 block w-full md:w-auto">
                                    Change to ${user.role === 'admin' ? 'USER' : 'ADMIN'}
                                </button>
                            `}

                            <button onclick="App.handlePasswordReset(${user.id}, '${user.username}')" class="text-amber-600 hover:text-amber-900 dark:text-amber-400 dark:hover:text-amber-300 transition-colors font-medium p-2 rounded-md hover:bg-slate-100 dark:hover:bg-slate-700 block w-full md:w-auto">
                                Reset Password
                            </button>

                            ${isCurrentUser ? `
                                <span class="text-slate-400 text-xs py-2">Cannot delete yourself</span>
                            ` : `
                                <button onclick="App.handleDeleteUser(${user.id}, '${user.username}')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors font-medium p-2 rounded-md hover:bg-slate-100 dark:hover:bg-slate-700 block w-full md:w-auto">
                                    Delete User
                                </button>
                            `}
                        </td>
                    `;
                    
                    this.D.userListBody.appendChild(row);
                });
            },

            renderCodeTable() {
                this.D.codeListBody.innerHTML = ''; 

                if (this.codes.length === 0) {
                    this.D.codeListBody.innerHTML = `<tr><td colspan="4" class="p-6 text-center text-slate-500">No registration codes found in the database.</td></tr>`;
                    return;
                }

                this.codes.forEach(codeObj => {
                    const isActive = codeObj.is_active == 1;
                    const statusText = isActive ? 'Active' : 'Inactive';
                    const statusColor = isActive ? 'text-green-500 bg-green-100 dark:bg-green-900/30 dark:text-green-300' : 'text-slate-500 bg-slate-100 dark:bg-slate-700/50 dark:text-slate-400';
                    const actionText = isActive ? 'Deactivate' : 'Activate';
                    const actionColor = isActive ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900';
                    const newStatus = isActive ? 0 : 1;
                    
                    // Format timestamp
                    const date = codeObj.created_at ? new Date(codeObj.created_at).toLocaleDateString() : 'N/A';

                    const row = document.createElement('tr');
                    row.className = `hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors`;
                    
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">${codeObj.code}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold ${statusColor}">
                                ${statusText}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">${date}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <button onclick="App.handleCodeStatusToggle('${codeObj.code}', ${newStatus})" class="${actionColor} dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors font-medium p-2 rounded-md hover:bg-slate-100 dark:hover:bg-slate-700">
                                ${actionText}
                            </button>
                        </td>
                    `;
                    
                    this.D.codeListBody.appendChild(row);
                });
            },

            // --- Handlers ---
            
            async handleRoleChange(userId, currentRole) {
                const newRole = currentRole === 'admin' ? 'user' : 'admin';
                const confirmation = confirm(`Are you sure you want to change the role of user ID ${userId} to **${newRole.toUpperCase()}**?`);
                
                if (!confirmation) return;
                
                this.showToast(`Attempting to change role to ${newRole}...`, 'info');

                try {
                    const response = await fetch(`${this.API_URL}?action=updateRole`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ userId: userId, newRole: newRole })
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) throw new Error(data.message || "Failed to update role on server.");
                    
                    this.showToast('User role updated successfully!', 'success');
                    this.fetchUsers(); // Refresh the table
                    
                } catch (error) {
                    console.error("API Role Update Error:", error);
                    this.showToast(`Role update failed: ${error.message}.`, 'error');
                }
            },
            
            // --- NEW: Password Reset Handler ---
            async handlePasswordReset(userId, username) {
                const newPassword = prompt(`Enter the new password for user **${username}** (min 6 characters):`);

                if (newPassword === null) return; // User cancelled
                
                if (newPassword.length < 6) {
                    this.showToast('Password reset failed: Password must be at least 6 characters.', 'error');
                    return;
                }
                
                this.showToast(`Attempting to reset password for ${username}...`, 'info');

                try {
                    const response = await fetch(`${this.API_URL}?action=updatePassword`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ userId: userId, newPassword: newPassword })
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) throw new Error(data.message || "Failed to reset password on server.");
                    
                    this.showToast(`Password for **${username}** successfully reset!`, 'success');
                    
                } catch (error) {
                    console.error("API Password Reset Error:", error);
                    this.showToast(`Password reset failed: ${error.message}.`, 'error');
                }
            },
            
            // --- NEW: Delete User Handler ---
            async handleDeleteUser(userId, username) {
                const confirmation = confirm(`Are you absolutely sure you want to permanently delete the user **${username}**? This action cannot be undone.`);
                
                if (!confirmation) return;
                
                this.showToast(`Attempting to delete user ${username}...`, 'info');

                try {
                    const response = await fetch(`${this.API_URL}?action=deleteUser`, {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ userId: userId })
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) throw new Error(data.message || "Failed to delete user on server.");
                    
                    this.showToast(`User **${username}** deleted successfully!`, 'success');
                    this.fetchUsers(); // Refresh the table
                    
                } catch (error) {
                    console.error("API Delete User Error:", error);
                    this.showToast(`User deletion failed: ${error.message}.`, 'error');
                }
            },
            
            async handleAddCode(e) {
                e.preventDefault();
                const code = this.D.newCodeInput.value.trim();
                
                if (code.length < 3) {
                    this.showToast('Registration code must be at least 3 characters.', 'error');
                    return;
                }

                this.showToast('Attempting to add new code...', 'info');

                try {
                    const response = await fetch(`${this.API_URL}?action=addCode`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ code: code })
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) throw new Error(data.message || "Failed to add code on server.");
                    
                    this.showToast('New registration code added successfully!', 'success');
                    this.D.addCodeForm.reset();
                    this.fetchCodes(); 
                    
                } catch (error) {
                    console.error("API Add Code Error:", error);
                    this.showToast(`Failed to add code: ${error.message}.`, 'error');
                }
            },

            async handleCodeStatusToggle(code, newStatus) {
                const action = newStatus === 1 ? 'Activate' : 'Deactivate';
                const confirmation = confirm(`Are you sure you want to ${action} the code **${code}**?`);
                
                if (!confirmation) return;
                
                this.showToast(`Attempting to ${action} code...`, 'info');

                try {
                    const response = await fetch(`${this.API_URL}?action=toggleCodeStatus`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ code: code, newStatus: newStatus })
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) throw new Error(data.message || "Failed to update code status on server.");
                    
                    this.showToast(`Code **${code}** has been ${action}d successfully.`, 'success');
                    this.fetchCodes(); // Refresh the table
                    
                } catch (error) {
                    console.error("API Code Status Toggle Error:", error);
                    this.showToast(`Status update failed: ${error.message}.`, 'error');
                }
            },

            async handleLogout() {
                try {
                    // Call API to destroy PHP session
                    await fetch(`${this.API_URL}?action=logout`, { method: 'POST' });
                } catch (error) {
                    console.warn("Logout endpoint failed, proceeding with client-side cleanup:", error);
                }
                
                // Client-side cleanup and redirect 
                localStorage.removeItem('username');
                localStorage.removeItem('userRole');
                localStorage.removeItem('userId'); 
                this.showToast('You have been logged out. Redirecting...', 'info');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 500);
            },

            // --- UI Helpers (Copied from inventory.html) ---
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

            // --- Event Listeners Setup ---
            addEventListeners() {
                this.D.logoutBtn.addEventListener('click', this.handleLogout.bind(this));
                this.D.refreshUsersBtn.addEventListener('click', this.fetchUsers.bind(this));
                
                // NEW: Code Management Listeners
                this.D.refreshCodesBtn.addEventListener('click', this.fetchCodes.bind(this));
                this.D.addCodeForm.addEventListener('submit', this.handleAddCode.bind(this));
            }
        };

        // Start the application after the DOM is fully loaded
        document.addEventListener('DOMContentLoaded', () => {
            App.init();
        });
    </script>
</body>
</html>
