<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied | {{ config('app.name', 'ERP') }}</title>
    
    <!-- Google Fonts & Bootstrap & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-green: #198754;
            --primary-dark: #0f172a;
            --accent-red: #ef4444;
            --bg-gradient: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            background-image: 
                radial-gradient(at 0% 0%, rgba(239, 68, 68, 0.08) 0px, transparent 50%),
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
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.75rem;
            position: relative;
            box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.25);
            animation: pulse 2.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .icon-container i.main-icon {
            font-size: 2.75rem;
            color: #dc2626;
        }

        .badge-403 {
            position: absolute;
            bottom: -4px;
            right: -4px;
            background: #dc2626;
            color: white;
            font-weight: 800;
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 20px;
            border: 2px solid white;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.3);
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
            margin-bottom: 1.5rem;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            margin-bottom: 2rem;
            text-align: left;
            font-size: 0.875rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.25rem 0;
        }

        .info-row:not(:last-child) {
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 0.5rem;
            margin-bottom: 0.5rem;
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
            box-shadow: 0 6px 16px rgba(25, 135, 84, 0.35);
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
            border-color: #94a3b8;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-container">
            <i class="fas fa-shield-halved main-icon"></i>
            <span class="badge-403">403</span>
        </div>

        <h1 class="error-title">Access Restricted</h1>
        <p class="error-subtitle mb-4">
            You do not have permission to access this page or perform this action.
        </p>

        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
            <button type="button" class="btn-secondary-action" onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ auth()->check() ? route('erp.dashboard') : url('/') }}'">
                <i class="fas fa-arrow-left me-2"></i>Go Back
            </button>
            @if(auth()->check())
                <a href="{{ route('erp.dashboard') }}" class="btn-primary-action">
                    <i class="fas fa-home me-2"></i>Go to Dashboard
                </a>
            @else
                <a href="{{ url('/') }}" class="btn-primary-action">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
            @endif
        </div>
        
        <p class="text-muted mt-4 mb-0" style="font-size: 0.8rem;">
            If you need access to this section, please contact your Super Admin.
        </p>
    </div>
</body>
</html>
