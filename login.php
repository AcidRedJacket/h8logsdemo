<?php
// NOTE: For security and better user experience, a real PHP application would
// check if the user is already logged in and redirect them away from the login page.
// Example:
// session_start();
// if (isset($_SESSION['user_id'])) {
//     header("Location: inventory.php"); // Corrected redirect
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register - Secure Tracker</title>
    
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
        /* Base Styling */
        body {
            @apply bg-app-bg-light dark:bg-app-bg-dark text-slate-800 dark:text-slate-100 transition-colors duration-300 font-sans p-4;
            /* 🔑 NEW CENTERING TECHNIQUE: Explicitly setting the height of the body to 100% of the viewport height */
            height: 100vh; 
            margin: 0; /* Ensures no default body margin interferes */
        }
        
        /* 🔑 PURE CSS CENTERING RULE (Absolute + Translate) */
        .login-center-box {
            position: absolute; /* Takes the element out of normal flow */
            top: 50%;           /* Moves the top edge to the vertical center */
            left: 50%;          /* Moves the left edge to the horizontal center */
            /* Shifts the element back by half its own height/width to truly center it */
            transform: translate(-50%, -50%); 
        }
    </style>
</head>
<body>

    <div class="w-full max-w-md login-center-box">
        <div class="text-center mb-8">
            <i data-lucide="lock" class="w-10 h-10 mx-auto text-indigo-600 mb-3"></i>
            <h1 class="text-3xl font-extrabold dark:text-white">Secure Access</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400">Sign in or create an account to manage inventory.</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 p-8 space-y-6">
            
            <div class="flex border-b border-slate-200 dark:border-slate-700">
                <button id="loginTab" class="py-3 px-4 text-sm font-medium border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" data-form="login">
                    Login
                </button>
                <button id="registerTab" class="py-3 px-4 text-sm font-medium border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" data-form="register">
                    Register
                </button>
            </div>
            
            <form id="loginForm" class="space-y-5">
                <h2 class="text-xl font-semibold dark:text-white">Sign In</h2>
                <div class="space-y-1">
                    <label for="loginUsername" class="block text-sm font-medium dark:text-slate-200">Username</label>
                    <input type="text" id="loginUsername" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all" placeholder="Your username">
                </div>
                <div class="space-y-1">
                    <label for="loginPassword" class="block text-sm font-medium dark:text-slate-200">Password</label>
                    <input type="password" id="loginPassword" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all" placeholder="Enter your password">
                </div>
                <button type="submit" id="loginBtn" class="w-full py-3 px-4 rounded-lg font-semibold shadow-md transition-all flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white">
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                    <span>Log In</span>
                </button>
            </form>
            
            <form id="registerForm" class="space-y-5 hidden">
                <h2 class="text-xl font-semibold dark:text-white">Create Account</h2>
                <div class="space-y-1">
                    <label for="registerUsername" class="block text-sm font-medium dark:text-slate-200">Username</label>
                    <input type="text" id="registerUsername" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all" placeholder="Choose a username">
                </div>
                <div class="space-y-1">
                    <label for="registerPassword" class="block text-sm font-medium dark:text-slate-200">Password</label>
                    <input type="password" id="registerPassword" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all" placeholder="Create a strong password">
                </div>
                <div class="space-y-1">
                    <label for="registrationCode" class="block text-sm font-medium dark:text-slate-200">Registration Code (Admin Provided)</label>
                    <input type="text" id="registrationCode" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all" placeholder="Enter your invite code">
                </div>
                <button type="submit" id="registerBtn" class="w-full py-3 px-4 rounded-lg font-semibold shadow-md transition-all flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    <span>Register New Account</span>
                </button>
            </form>
            
            <div id="toastContainer" class="mt-4"></div>
        </div>
    </div>

    <script>
        // NOTE: Ensure this URL points to your api.php file!
        const API_URL = "http://h8logs.run.place/tracker/api.php"; 
        
        const D = {
            loginForm: document.getElementById('loginForm'),
            registerForm: document.getElementById('registerForm'),
            loginTab: document.getElementById('loginTab'),
            registerTab: document.getElementById('registerTab'),
            toastContainer: document.getElementById('toastContainer'),
        };

        let currentForm = 'login';
        
        // --- UI Helpers ---
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const colorMap = {
                'success': 'bg-emerald-100 border-emerald-300 text-emerald-800 dark:bg-emerald-900/50 dark:border-emerald-700 dark:text-emerald-300',
                'error': 'bg-red-100 border-red-300 text-red-800 dark:bg-red-900/50 dark:border-red-700 dark:text-red-300',
                'info': 'bg-blue-100 border-blue-300 text-blue-800 dark:bg-blue-900/50 dark:border-blue-700 dark:text-blue-300',
            };
            
            toast.className = `p-3 rounded-lg shadow-md border flex items-center gap-3 text-sm font-medium ${colorMap[type]}`;
            toast.textContent = message;
            
            D.toastContainer.innerHTML = ''; // Clear previous
            D.toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        function switchForm(formName) {
            currentForm = formName;
            D.loginForm.classList.toggle('hidden', formName !== 'login');
            D.registerForm.classList.toggle('hidden', formName !== 'register');
            
            // Manage active tab styling for Login
            D.loginTab.classList.toggle('border-indigo-600', formName === 'login');
            D.loginTab.classList.toggle('dark:text-white', formName === 'login');
            D.loginTab.classList.toggle('text-indigo-600', formName === 'login');
            
            // Manage active tab styling for Register
            D.registerTab.classList.toggle('border-indigo-600', formName === 'register');
            D.registerTab.classList.toggle('dark:text-white', formName === 'register');
            D.registerTab.classList.toggle('text-indigo-600', formName === 'register');

            
            // Reset colors for the inactive tab
            if (formName === 'login') {
                D.registerTab.classList.remove('text-indigo-600', 'dark:text-white');
            } else {
                D.loginTab.classList.remove('text-indigo-600', 'dark:text-white');
            }


            D.toastContainer.innerHTML = ''; // Clear toast on switch
        }
        
        // --- API Handlers ---
        
        async function handleLogin(e) {
            e.preventDefault();
            const username = document.getElementById('loginUsername').value.trim();
            const password = document.getElementById('loginPassword').value.trim();

            try {
                const response = await fetch(`${API_URL}?action=login`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();

                if (response.ok) {
                    showToast(`Login successful! Role: ${data.role.toUpperCase()}. Redirecting...`, 'success');
                    // Store user info in localStorage for the inventory page to check access
                    localStorage.setItem('userRole', data.role);
                    localStorage.setItem('username', data.username);
                    localStorage.setItem('userId', data.userId); // Store ID for admin panel self-check
                    // FIX: Redirect to inventory.php 
                    window.location.href = 'inventory.php'; 
                } else {
                    showToast(data.message || 'Login failed. Please check your credentials.', 'error');
                }
            } catch (error) {
                console.error("Login Error:", error);
                showToast('A network error occurred. Check server/API status.', 'error');
            }
        }
        
        async function handleRegister(e) {
            e.preventDefault();
            const username = document.getElementById('registerUsername').value.trim();
            const password = document.getElementById('registerPassword').value.trim();
            // === NEW: GET THE REGISTRATION CODE ===
            const registrationCode = document.getElementById('registrationCode').value.trim(); 

            if (username.length < 3 || password.length < 6) {
                showToast('Username must be 3+ chars and Password 6+ chars.', 'error');
                return;
            }
            // === NEW: CHECK CODE LENGTH (Optional but good practice) ===
            if (registrationCode.length === 0) {
                showToast('Registration code must be provided by an administrator.', 'error');
                return;
            }

            try {
                const response = await fetch(`${API_URL}?action=register`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    // === NEW: INCLUDE THE CODE IN THE BODY ===
                    body: JSON.stringify({ username, password, registrationCode })
                });
                
                const data = await response.json();

                if (response.ok) {
                    showToast('Registration successful! Please log in.', 'success');
                    D.registerForm.reset();
                    switchForm('login');
                } else {
                    showToast(data.message || 'Registration failed. Username may be taken or code is invalid.', 'error');
                }
            } catch (error) {
                console.error("Registration Error:", error);
                showToast('A network error occurred. Check server/API status.', 'error');
            }
        }
        
        // --- Initialization ---
        document.addEventListener('DOMContentLoaded', () => {
            // ===============================================
            // REMOVED: The previous auto-redirect logic that was causing issues.
            // Users must now click \"Log In\" to proceed.
            // ===============================================
            
            switchForm('login'); // Initial state
            D.loginForm.addEventListener('submit', handleLogin);
            D.registerForm.addEventListener('submit', handleRegister);
            D.loginTab.addEventListener('click', () => switchForm('login'));
            D.registerTab.addEventListener('click', () => switchForm('register'));

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
