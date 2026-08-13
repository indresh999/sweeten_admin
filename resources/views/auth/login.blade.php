<x-guest-layout>
    <section class="login-section">
        <div class="login-container">
            {{-- Left/Top Panel - Brand --}}
            <div class="login-brand-panel">
                <div class="login-brand-content">
                    <div class="brand-logo">
                        <svg width="48" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="-0.757324" y="19.2427" width="28" height="4" rx="2" transform="rotate(-45 -0.757324 19.2427)" fill="currentColor"/>
                            <rect x="7.72803" y="27.728" width="28" height="4" rx="2" transform="rotate(-45 7.72803 27.728)" fill="currentColor"/>
                            <rect x="10.5366" y="16.3945" width="16" height="4" rx="2" transform="rotate(45 10.5366 16.3945)" fill="currentColor"/>
                            <rect x="10.5562" y="-0.556152" width="28" height="4" rx="2" transform="rotate(45 10.5562 -0.556152)" fill="currentColor"/>
                        </svg>
                    </div>
                    <h1 class="brand-title">Sweeten</h1>
                    <p class="brand-subtitle">Admin Panel</p>
                    <div class="brand-features">
                        <div class="feature-item">
                            <i class="fas fa-chart-line"></i>
                            <span>Real-time Analytics</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-store"></i>
                            <span>Multi-vendor Management</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-motorcycle"></i>
                            <span>Delivery Tracking</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right/Bottom Panel - Login Form --}}
            <div class="login-form-panel">
                <div class="login-form-wrapper">
                    <div class="form-header">
                        <h2 class="form-title">Welcome back</h2>
                        <p class="form-subtitle">Sign in to your admin account</p>
                    </div>

                    <x-auth-session-status class="mb-4" :status="session('status')" />
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />

                    <form method="POST" action="{{ route('login') }}" class="login-form" data-toggle="validator">
                        {{ csrf_field() }}

                        <div class="form-group">
                            <label for="email" class="form-label">Email address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope input-icon"></i>
                                <input id="email" type="email" name="email"
                                    value="{{ env('IS_DEMO') ? 'admin@example.com' : old('email') }}"
                                    class="form-input" placeholder="admin@example.com" required autofocus>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input id="password" type="password" name="password"
                                    class="form-input" placeholder="Enter your password"
                                    value="{{ env('IS_DEMO') ? 'password' : '' }}"
                                    required autocomplete="current-password">
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" name="remember" id="remember">
                                <span class="checkmark"></span>
                                <span class="checkbox-label">Remember me</span>
                            </label>
                            <a href="{{ route('auth.recoverpw') }}" class="forgot-link">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn-login">
                            <span>Sign In</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>

                    <div class="form-footer">
                        <p>Don't have an account? <a href="{{ route('auth.signup') }}">Create one</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        .login-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f2f5;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .login-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.12);
            margin: 16px;
        }

        /* Brand Panel */
        .login-brand-panel {
            flex: 1;
            background: linear-gradient(135deg, #3a57e8 0%, #2d45c7 100%);
            padding: 48px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .login-brand-panel::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
            top: -80px;
            right: -80px;
        }

        .login-brand-panel::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            bottom: -60px;
            left: -60px;
        }

        .login-brand-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.15);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            backdrop-filter: blur(10px);
        }

        .brand-logo svg { color: #fff; }

        .brand-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }

        .brand-subtitle {
            font-size: 16px;
            opacity: 0.85;
            margin-bottom: 40px;
            font-weight: 400;
        }

        .brand-features {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            opacity: 0.9;
        }

        .feature-item i {
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        /* Form Panel */
        .login-form-panel {
            flex: 1;
            padding: 48px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-form-wrapper {
            width: 100%;
            max-width: 380px;
        }

        .form-header {
            margin-bottom: 32px;
        }

        .form-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 8px;
        }

        .form-subtitle {
            font-size: 15px;
            color: #6b7280;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #9ca3af;
            font-size: 16px;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 14px 14px 14px 44px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            color: #1f2937;
            background: #f9fafb;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: #3a57e8;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(58,87,232,0.1);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            font-size: 16px;
        }

        .password-toggle:hover {
            color: #374151;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 14px;
            color: #4b5563;
        }

        .checkbox-wrapper input { display: none; }

        .checkmark {
            width: 18px;
            height: 18px;
            border: 2px solid #d1d5db;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .checkbox-wrapper input:checked + .checkmark {
            background: #3a57e8;
            border-color: #3a57e8;
        }

        .checkbox-wrapper input:checked + .checkmark::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: #fff;
            font-size: 10px;
        }

        .forgot-link {
            font-size: 14px;
            color: #3a57e8;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #3a57e8 0%, #2d45c7 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(58,87,232,0.35);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(58,87,232,0.45);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .form-footer {
            text-align: center;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .form-footer p {
            font-size: 14px;
            color: #6b7280;
        }

        .form-footer a {
            color: #3a57e8;
            text-decoration: none;
            font-weight: 600;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 440px;
                min-height: auto;
                margin: 0;
                border-radius: 0;
                min-height: 100vh;
            }

            .login-brand-panel {
                padding: 32px 24px 24px;
            }

            .brand-logo {
                width: 60px;
                height: 60px;
                margin-bottom: 16px;
            }

            .brand-title { font-size: 26px; }
            .brand-subtitle { font-size: 14px; margin-bottom: 0; }
            .brand-features { display: none; }

            .login-form-panel {
                padding: 32px 24px 48px;
            }

            .form-title { font-size: 24px; }
        }

        @media (max-width: 380px) {
            .login-brand-panel { padding: 24px 20px 20px; }
            .login-form-panel { padding: 28px 20px 40px; }
            .form-title { font-size: 22px; }
        }

        /* Demo mode styling */
        .badge.bg-demo {
            background: #fef3c7 !important;
            color: #92400e !important;
        }
    </style>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</x-guest-layout>
