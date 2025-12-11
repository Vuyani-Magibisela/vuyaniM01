<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Sign Up - Vuyani Magibisela'; ?></title>

    <?php
    require_once dirname(__DIR__, 2) . '/config/config.php';
    ?>

    <!-- Favicon and App Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $baseUrl; ?>/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $baseUrl; ?>/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $baseUrl; ?>/images/favicon/favicon-16x16.png">

    <!-- External Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Stylesheets -->
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/responsive.css">

    <style>
        /* Register Page Specific Styles */
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: var(--bg-color);
        }

        .register-box {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 550px;
            border: 1px solid var(--border-color);
        }

        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .register-header h1 {
            color: var(--text-color);
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .register-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group label .required {
            color: #ef4444;
            margin-left: 2px;
        }

        .form-input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            padding-right: 2.5rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            background: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-input.error {
            border-color: #ef4444;
        }

        .input-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .password-toggle {
            cursor: pointer;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }

        .password-strength-bar {
            height: 4px;
            background: var(--border-color);
            border-radius: 2px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .password-strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .password-strength-fill.weak {
            width: 33%;
            background: #ef4444;
        }

        .password-strength-fill.medium {
            width: 66%;
            background: #f59e0b;
        }

        .password-strength-fill.strong {
            width: 100%;
            background: #10b981;
        }

        .password-requirements {
            margin-top: 0.75rem;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .password-requirements ul {
            list-style: none;
            padding: 0;
            margin: 0.5rem 0 0 0;
        }

        .password-requirements li {
            padding: 0.25rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .password-requirements li i {
            font-size: 0.75rem;
        }

        .password-requirements li.valid {
            color: #10b981;
        }

        .password-requirements li.invalid {
            color: var(--text-muted);
        }

        .register-btn {
            width: 100%;
            padding: 0.95rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .register-btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .register-btn:disabled {
            background: var(--text-muted);
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .alert-icon {
            font-size: 1.25rem;
            margin-top: 2px;
        }

        .login-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
            color: var(--text-muted);
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .login-link a:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        .back-home {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-home a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-home a:hover {
            color: var(--primary-color);
        }

        /* Theme Toggle */
        .theme-toggle-register {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .theme-toggle-register:hover {
            transform: scale(1.1);
        }

        .theme-toggle-register i {
            font-size: 1.25rem;
            color: var(--text-color);
        }

        /* Dark mode specific adjustments */
        [data-theme="dark"] .register-box {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .register-box {
                padding: 2rem 1.5rem;
            }

            .register-header h1 {
                font-size: 1.75rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .theme-toggle-register {
                top: 1rem;
                right: 1rem;
                width: 45px;
                height: 45px;
            }
        }
    </style>
</head>
<body>
    <!-- Theme Toggle -->
    <div class="theme-toggle-register" id="themeToggle">
        <i class="fas fa-moon" id="themeIcon"></i>
    </div>

    <div class="register-container">
        <div class="register-box">
            <div class="register-header">
                <h1>Create Account</h1>
                <p>Join us and start building your portfolio</p>
            </div>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <form action="<?php echo $baseUrl; ?>/auth/processRegistration" method="POST" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <div class="form-input-wrapper">
                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                class="form-input"
                                placeholder="John"
                                required
                                autocomplete="given-name"
                                autofocus
                            >
                            <i class="fas fa-user input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <div class="form-input-wrapper">
                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                class="form-input"
                                placeholder="Doe"
                                required
                                autocomplete="family-name"
                            >
                            <i class="fas fa-user input-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <div class="form-input-wrapper">
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-input"
                            placeholder="Choose a unique username"
                            required
                            autocomplete="username"
                            minlength="3"
                        >
                        <i class="fas fa-at input-icon"></i>
                    </div>
                    <small style="color: var(--text-muted); font-size: 0.85rem; display: block; margin-top: 0.5rem;">
                        Minimum 3 characters
                    </small>
                </div>

                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <div class="form-input-wrapper">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="your.email@example.com"
                            required
                            autocomplete="email"
                        >
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="form-input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Create a strong password"
                            required
                            autocomplete="new-password"
                            minlength="8"
                        >
                        <i class="fas fa-eye-slash input-icon password-toggle" id="passwordToggle"></i>
                    </div>
                    <div class="password-strength-bar">
                        <div class="password-strength-fill" id="strengthBar"></div>
                    </div>
                    <div class="password-requirements">
                        <ul id="passwordReqs">
                            <li class="invalid" id="req-length">
                                <i class="fas fa-circle"></i>
                                <span>At least 8 characters</span>
                            </li>
                            <li class="invalid" id="req-uppercase">
                                <i class="fas fa-circle"></i>
                                <span>One uppercase letter</span>
                            </li>
                            <li class="invalid" id="req-lowercase">
                                <i class="fas fa-circle"></i>
                                <span>One lowercase letter</span>
                            </li>
                            <li class="invalid" id="req-number">
                                <i class="fas fa-circle"></i>
                                <span>One number</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <div class="form-input-wrapper">
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            class="form-input"
                            placeholder="Re-enter your password"
                            required
                            autocomplete="new-password"
                        >
                        <i class="fas fa-eye-slash input-icon password-toggle" id="confirmPasswordToggle"></i>
                    </div>
                    <small id="passwordMatchMsg" style="display: none; margin-top: 0.5rem; font-size: 0.85rem;"></small>
                </div>

                <button type="submit" class="register-btn" id="submitBtn">
                    <i class="fas fa-user-plus"></i>
                    <span>Create Account</span>
                </button>
            </form>

            <div class="login-link">
                Already have an account? <a href="<?php echo $baseUrl; ?>/auth">Sign in here</a>
            </div>

            <div class="back-home">
                <a href="<?php echo $baseUrl; ?>/">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Portfolio</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Theme Management
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const html = document.documentElement;

        // Get saved theme or default to light
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });

        function updateThemeIcon(theme) {
            themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }

        // Password Toggle
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');
        const confirmPasswordInput = document.getElementById('confirm_password');

        passwordToggle.addEventListener('click', () => {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            passwordToggle.classList.toggle('fa-eye-slash');
            passwordToggle.classList.toggle('fa-eye');
        });

        confirmPasswordToggle.addEventListener('click', () => {
            const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
            confirmPasswordInput.type = type;
            confirmPasswordToggle.classList.toggle('fa-eye-slash');
            confirmPasswordToggle.classList.toggle('fa-eye');
        });

        // Password Strength Checker
        const strengthBar = document.getElementById('strengthBar');
        const reqLength = document.getElementById('req-length');
        const reqUppercase = document.getElementById('req-uppercase');
        const reqLowercase = document.getElementById('req-lowercase');
        const reqNumber = document.getElementById('req-number');

        passwordInput.addEventListener('input', () => {
            const password = passwordInput.value;
            let strength = 0;

            // Check length
            if (password.length >= 8) {
                reqLength.classList.add('valid');
                reqLength.classList.remove('invalid');
                reqLength.querySelector('i').className = 'fas fa-check-circle';
                strength++;
            } else {
                reqLength.classList.remove('valid');
                reqLength.classList.add('invalid');
                reqLength.querySelector('i').className = 'fas fa-circle';
            }

            // Check uppercase
            if (/[A-Z]/.test(password)) {
                reqUppercase.classList.add('valid');
                reqUppercase.classList.remove('invalid');
                reqUppercase.querySelector('i').className = 'fas fa-check-circle';
                strength++;
            } else {
                reqUppercase.classList.remove('valid');
                reqUppercase.classList.add('invalid');
                reqUppercase.querySelector('i').className = 'fas fa-circle';
            }

            // Check lowercase
            if (/[a-z]/.test(password)) {
                reqLowercase.classList.add('valid');
                reqLowercase.classList.remove('invalid');
                reqLowercase.querySelector('i').className = 'fas fa-check-circle';
                strength++;
            } else {
                reqLowercase.classList.remove('valid');
                reqLowercase.classList.add('invalid');
                reqLowercase.querySelector('i').className = 'fas fa-circle';
            }

            // Check number
            if (/[0-9]/.test(password)) {
                reqNumber.classList.add('valid');
                reqNumber.classList.remove('invalid');
                reqNumber.querySelector('i').className = 'fas fa-check-circle';
                strength++;
            } else {
                reqNumber.classList.remove('valid');
                reqNumber.classList.add('invalid');
                reqNumber.querySelector('i').className = 'fas fa-circle';
            }

            // Update strength bar
            strengthBar.className = 'password-strength-fill';
            if (strength <= 2) {
                strengthBar.classList.add('weak');
            } else if (strength === 3) {
                strengthBar.classList.add('medium');
            } else {
                strengthBar.classList.add('strong');
            }

            // Check password match
            checkPasswordMatch();
        });

        // Password Match Checker
        const passwordMatchMsg = document.getElementById('passwordMatchMsg');

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (confirmPassword.length === 0) {
                passwordMatchMsg.style.display = 'none';
                confirmPasswordInput.classList.remove('error');
                return;
            }

            if (password === confirmPassword) {
                passwordMatchMsg.style.display = 'block';
                passwordMatchMsg.style.color = '#10b981';
                passwordMatchMsg.textContent = ' Passwords match';
                confirmPasswordInput.classList.remove('error');
            } else {
                passwordMatchMsg.style.display = 'block';
                passwordMatchMsg.style.color = '#ef4444';
                passwordMatchMsg.textContent = ' Passwords do not match';
                confirmPasswordInput.classList.add('error');
            }
        }

        confirmPasswordInput.addEventListener('input', checkPasswordMatch);

        // Form Validation
        const registerForm = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');

        registerForm.addEventListener('submit', (e) => {
            const firstName = document.getElementById('first_name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            // Validate all fields are filled
            if (!firstName || !lastName || !username || !email || !password || !confirmPassword) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return false;
            }

            // Validate username length
            if (username.length < 3) {
                e.preventDefault();
                alert('Username must be at least 3 characters long');
                return false;
            }

            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }

            // Validate password length
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return false;
            }

            // Validate passwords match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }

            // Disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Creating Account...</span>';
        });
    </script>
</body>
</html>
