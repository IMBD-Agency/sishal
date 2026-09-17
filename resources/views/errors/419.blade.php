<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Page Expired | {{ config('app.name', 'ERP') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(25, 135, 84, 0.08) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #334155;
            overflow-x: hidden;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(226, 232, 240, 0.6);
            max-width: 580px;
            width: 100%;
            padding: 3rem 2.5rem;
            text-align: center;
            position: relative;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .icon-container {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.75rem;
            position: relative;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.25);
        }

        .icon-container i.main-icon {
            font-size: 2.75rem;
            color: #4f46e5;
        }

        .badge-419 {
            position: absolute;
            bottom: -4px;
            right: -4px;
            background: #4f46e5;
            color: white;
            font-weight: 800;
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 20px;
            border: 2px solid white;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
        }

        .error-title {
            font-weight: 800;
            font-size: 1.75rem;
            color: #0f172a;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .error-subtitle {
            font-size: 1rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 1.75rem;
        }

        .btn-primary-action {
            background: #198754;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            border: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.25);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary-action:hover {
            background: #157347;
            color: white;
            transform: translateY(-1px);
        }

        .btn-secondary-action {
            background: #ffffff;
            color: #475569;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-secondary-action:hover {
            background: #f8fafc;
            color: #0f172a;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-container">
            <i class="fas fa-hourglass-end main-icon"></i>
            <span class="badge-419">419</span>
        </div>

        <h1 class="error-title">Page Expired</h1>
        <p class="error-subtitle">
            Your session has expired due to inactivity. Please refresh the page and try again.
        </p>

        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
            <button type="button" class="btn-secondary-action" onclick="window.location.reload()">
                <i class="fas fa-rotate-right me-2"></i>Refresh Page
            </button>
            <a href="{{ auth()->check() ? route('erp.dashboard') : route('login') }}" class="btn-primary-action">
                <i class="fas fa-sign-in-alt me-2"></i>{{ auth()->check() ? 'Go to Dashboard' : 'Log In' }}
            </a>
        </div>
    </div>
</body>
</html>
