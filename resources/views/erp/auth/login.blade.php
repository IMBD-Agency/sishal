<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login | ERP Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #10b981;
            --accent-color: #34d399;
            --dark-bg: #f8fafc;
            --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark-bg);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            padding: 40px;
        }

        .brand-section {
            text-align: center;
            margin-bottom: 35px;
        }

        .brand-logo {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-bottom: 15px;
            box-shadow: 0 4px-12px rgba(16, 185, 129, 0.3);
        }

        .brand-name {
            font-weight: 700;
            color: #064e3b;
            font-size: 24px;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }

        .brand-subtitle {
            color: #64748b;
            font-size: 14px;
        }

        .form-label {
            font-weight: 600;
            font-size: 13px;
            color: #475569;
            margin-bottom: 8px;
        }

        .input-group-text {
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #64748b;
        }

        .form-control {
            border: 1px solid #e2e8f0;
            padding: 11px 15px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .toggle-password {
            cursor: pointer;
            transition: color 0.2s;
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
            color: #94a3b8;
        }

        .alert {
            font-size: 13px;
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-card">
            <div class="brand-section">
                <div class="brand-logo">
                    <i class="fas fa-crown"></i>
                </div>
                <h1 class="brand-name">Sisal Fashion</h1>
                <p class="brand-subtitle">Quality & Style Management</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="login_source" value="erp">
                
                <div class="mb-3">
                    <label class="form-label">Email or Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-circle small"></i></span>
                        <input type="text" name="email" class="form-control" placeholder="Enter identifier" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label class="form-label">Password</label>
                        <a href="{{ route('password.request') }}" class="text-decoration-none small fw-medium" style="color: var(--primary-color)">Forgot?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock small"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                        <span class="input-group-text toggle-password" data-target="password">
                            <i class="fas fa-eye small"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small text-muted" for="remember">
                            Stay signed in for 30 days
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Sign In to Dashboard <i class="fas fa-arrow-right ms-2 small"></i>
                </button>
            </form>
        </div>
        
        <div class="login-footer">
            &copy; {{ date('Y') }} ERP Solution. Professional Edition.
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Password toggle
            $('.toggle-password').on('click', function() {
                const targetId = $(this).data('target');
                const input = $('#' + targetId);
                const icon = $(this).find('i');
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        });
    </script>
</body>
</html>
