<?php
// NOTE: For security and better user experience, a real PHP application would
// check if the user is already logged in and redirect them away from the login page.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holiday Access - Secure Tracker</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'holiday-red': '#b91c1c',
                        'holiday-green': '#15803d',
                        // Updated to Midnight Blue shades
                        'midnight-blue': '#0f172a',
                        'midnight-deep': '#020617',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            /* Applied Midnight Blue Gradient */
            background: radial-gradient(circle at center, #1e293b 0%, #020617 100%);
            color: #f1f5f9;
            height: 100vh; 
            margin: 0;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Festive pattern overlay */
        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: -1;
        }
        
        /* Holiday Sparkle Animation */
        .snow-flake {
            position: fixed;
            top: -10px;
            color: white;
            user-select: none;
            z-index: 0;
            pointer-events: none;
            animation: fall linear forwards;
        }

        @keyframes fall {
            to {
                transform: translateY(105vh);
            }
        }

        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>

    <div class="w-full max-w-md relative z-10 px-4">
        <div class="text-center mb-8">
            <i data-lucide="gift" class="w-12 h-12 mx-auto text-holiday-red mb-3 animate-bounce"></i>
            <h1 class="text-3xl font-extrabold text-white">Holiday Portal</h1>
            <p class="mt-1 text-slate-300">Sign in to check your list (twice!).</p>
        </div>

        <div class="glass-panel rounded-2xl shadow-2xl p-8 space-y-6">
            
            <div class="flex border-b border-slate-700">
                <button id="loginTab" class="py-3 px-4 text-sm font-bold border-b-2 border-transparent text-slate-400 hover:text-holiday-red transition-colors" data-form="login">
                    Sign In
                </button>
                <button id="registerTab" class="py-3 px-4 text-sm font-bold border-b-2 border-transparent text-slate-400 hover:text-holiday-green transition-colors" data-form="register">
                    Join the Party
                </button>
            </div>
            
            <form id="loginForm" class="space-y-5">
                <div class="space-y-1">
                    <label for="loginUsername" class="block text-sm font-medium text-slate-200">Username</label>
                    <input type="text" id="loginUsername" required class="w-full px-4 py-2.5 rounded-lg border border-slate-600 bg-slate-900/50 text-white focus:ring-2 focus:ring-holiday-red/50 focus:border-holiday-red transition-all" placeholder="Enter username">
                </div>
                <div class="space-y-1">
                    <label for="loginPassword" class="block text-sm font-medium text-slate-200">Password</label>
                    <input type="password" id="loginPassword" required class="w-full px-4 py-2.5 rounded-lg border border-slate-600 bg-slate-900/50 text-white focus:ring-2 focus:ring-holiday-red/50 focus:border-holiday-red transition-all" placeholder="Enter password">
                </div>
                <button type="submit" id="loginBtn" class="w-full py-3 px-4 rounded-lg font-bold shadow-lg transition-all flex items-center justify-center gap-2 bg-holiday-red hover:bg-red-700 text-white">
                    <i data-lucide="snowflake" class="w-5 h-5"></i>
                    <span>Enter Workshop</span>
                </button>
            </form>
            
            <form id="registerForm" class="space-y-5 hidden">
                <div class="space-y-1">
                    <label for="registerUsername" class="block text-sm font-medium text-slate-200">Username</label>
                    <input type="text" id="registerUsername" required class="w-full px-4 py-2.5 rounded-lg border border-slate-600 bg-slate-900/50 text-white focus:ring-2 focus:ring-holiday-green/50 focus:border-holiday-green transition-all" placeholder="New username">
                </div>
                <div class="space-y-1">
                    <label for="registerPassword" class="block text-sm font-medium text-slate-200">Password</label>
                    <input type="password" id="registerPassword" required class="w-full px-4 py-2.5 rounded-lg border border-slate-600 bg-slate-900/50 text-white focus:ring-2 focus:ring-holiday-green/50 focus:border-holiday-green transition-all" placeholder="Strong password">
                </div>
                <div class="space-y-1">
                    <label for="registrationCode" class="block text-sm font-medium text-slate-200">Invite Code</label>
                    <input type="text" id="registrationCode" required class="w-full px-4 py-2.5 rounded-lg border border-slate-600 bg-slate-900/50 text-white focus:ring-2 focus:ring-holiday-green/50 focus:border-holiday-green transition-all" placeholder="Holiday Invite Code">
                </div>
                <button type="submit" id="registerBtn" class="w-full py-3 px-4 rounded-lg font-bold shadow-lg transition-all flex items-center justify-center gap-2 bg-holiday-green hover:bg-green-700 text-white">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                    <span>Create Account</span>
                </button>
            </form>
            
            <div id="toastContainer" class="mt-4"></div>
        </div>
    </div>

    <script>
        const API_URL = "http://h8logs.run.place/config/api.php"; 
        
        const D = {
            loginForm: document.getElementById('loginForm'),
            registerForm: document.getElementById('registerForm'),
            loginTab: document.getElementById('loginTab'),
            registerTab: document.getElementById('registerTab'),
            toastContainer: document.getElementById('toastContainer'),
        };

        function createSnowflake() {
            const flake = document.createElement('div');
            flake.classList.add('snow-flake');
            flake.innerHTML = '❄';
            flake.style.left = Math.random() * 100 + 'vw';
            flake.style.animationDuration = Math.random() * 3 + 2 + 's';
            flake.style.opacity = Math.random();
            flake.style.fontSize = Math.random() * 10 + 10 + 'px';
            document.body.appendChild(flake);
            setTimeout(() => flake.remove(), 5000);
        }
        setInterval(createSnowflake, 200);

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const colorMap = {
                'success': 'bg-green-900/80 border-green-700 text-green-100',
                'error': 'bg-red-900/80 border-red-700 text-red-100',
            };
            toast.className = `p-3 rounded-lg shadow-md border flex items-center gap-3 text-sm font-bold backdrop-blur-sm ${colorMap[type]}`;
            toast.textContent = message;
            D.toastContainer.innerHTML = '';
            D.toastContainer.appendChild(toast);
            setTimeout(() => toast.remove(), 5000);
        }

        function switchForm(formName) {
            D.loginForm.classList.toggle('hidden', formName !== 'login');
            D.registerForm.classList.toggle('hidden', formName !== 'register');
            
            D.loginTab.classList.toggle('border-holiday-red', formName === 'login');
            D.loginTab.classList.toggle('text-holiday-red', formName === 'login');
            D.loginTab.classList.toggle('text-slate-400', formName !== 'login');
            
            D.registerTab.classList.toggle('border-holiday-green', formName === 'register');
            D.registerTab.classList.toggle('text-holiday-green', formName === 'register');
            D.registerTab.classList.toggle('text-slate-400', formName !== 'register');
        }

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
                    showToast('Welcome back! Redirecting...', 'success');
                    localStorage.setItem('userRole', data.role);
                    localStorage.setItem('username', data.username);
                    localStorage.setItem('userId', data.userId);
                    setTimeout(() => window.location.href = 'inventory.php', 1000);
                } else {
                    showToast(data.message || 'Login failed.', 'error');
                }
            } catch (error) {
                showToast('Network error.', 'error');
            }
        }

        async function handleRegister(e) {
            e.preventDefault();
            const username = document.getElementById('registerUsername').value.trim();
            const password = document.getElementById('registerPassword').value.trim();
            const registrationCode = document.getElementById('registrationCode').value.trim(); 
            try {
                const response = await fetch(`${API_URL}?action=register`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password, registrationCode })
                });
                const data = await response.json();
                if (response.ok) {
                    showToast('Registered! Please log in.', 'success');
                    switchForm('login');
                } else {
                    showToast(data.message || 'Registration failed.', 'error');
                }
            } catch (error) {
                showToast('Network error.', 'error');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            switchForm('login');
            D.loginForm.addEventListener('submit', handleLogin);
            D.registerForm.addEventListener('submit', handleRegister);
            D.loginTab.addEventListener('click', () => switchForm('login'));
            D.registerTab.addEventListener('click', () => switchForm('register'));
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</body>
</html>
