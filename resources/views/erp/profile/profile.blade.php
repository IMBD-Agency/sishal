@extends('erp.master')

@section('title', 'Profile Settings')

@push('styles')
<style>
    .profile-page {
        background: #f8f9fa;
        min-height: 100vh;
    }
    
    .profile-header {
        background: white;
        border-bottom: 1px solid #e9ecef;
        padding: 2rem 0;
        margin-bottom: 2rem;
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #0da2e7;
    }
    
    .card-simple {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
    }
    
    .card-header-simple {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
        font-weight: 600;
        color: #333;
    }
    
    .card-body-simple {
        padding: 1.5rem;
    }
    
    .form-control-simple {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 0.5rem 0.75rem;
        font-size: 14px;
    }
    
    .form-control-simple:focus {
        border-color: #0da2e7;
        box-shadow: 0 0 0 0.2rem rgba(13, 162, 231, 0.25);
        outline: none;
    }
    
    .btn-simple {
        background: #0da2e7;
        border: 1px solid #0da2e7;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
    }
    
    .btn-simple:hover {
        background: #0b8cc7;
        border-color: #0b8cc7;
        color: white;
    }
    
    .btn-outline-simple {
        background: transparent;
        border: 1px solid #6c757d;
        color: #6c757d;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .btn-outline-simple:hover {
        background: #6c757d;
        color: white;
    }
    
    .form-label-simple {
        font-weight: 500;
        color: #555;
        margin-bottom: 0.25rem;
        font-size: 14px;
    }
    
    .activity-item-simple {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .activity-item-simple:last-child {
        border-bottom: none;
    }
    
    .text-muted-simple {
        color: #6c757d;
        font-size: 13px;
    }
    
    .password-strength {
        height: 3px;
        border-radius: 2px;
        margin-top: 5px;
    }
    
    .strength-weak { background: #dc3545; }
    .strength-medium { background: #ffc107; }
    .strength-strong { background: #28a745; }
</style>
@endpush

@section('body')
@include('erp.components.sidebar')
<div class="main-content" id="mainContent">
    @include('erp.components.header')
    <div class="container py-4">
        <!-- Simple Profile Header -->
        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <img src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->first_name . ' ' . Auth::user()->last_name) . '&background=0da2e7&color=fff&size=80' }}" 
                             alt="Profile Avatar" class="profile-avatar me-3">
                        <div>
                            <h3 class="mb-1">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h3>
                            <p class="mb-1 text-muted-simple">{{ Auth::user()->email }}</p>
                            <small class="text-muted-simple">Member since {{ Auth::user()->created_at->format('M Y') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <small class="text-muted-simple">Last active {{ Auth::user()->last_login_at?->diffForHumans() ?? 'Never' }}</small>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Profile Information -->
            <div class="col-lg-8">
                <div class="card-simple">
                    <div class="card-header-simple">
                        Profile Information
                    </div>
                    <div class="card-body-simple">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('erp.profile.update') }}" id="profileForm">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label-simple">First Name</label>
                                        <input type="text" 
                                               class="form-control-simple @error('first_name') is-invalid @enderror" 
                                               id="first_name" 
                                               name="first_name" 
                                               value="{{ old('first_name', Auth::user()->first_name) }}" 
                                               required
                                               maxlength="50">
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label-simple">Last Name</label>
                                        <input type="text" 
                                               class="form-control-simple @error('last_name') is-invalid @enderror" 
                                               id="last_name" 
                                               name="last_name" 
                                               value="{{ old('last_name', Auth::user()->last_name) }}" 
                                               required
                                               maxlength="50">
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label-simple">Email Address</label>
                                <input type="email" 
                                       class="form-control-simple @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', Auth::user()->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label-simple">Phone Number</label>
                                        <input type="tel" 
                                               class="form-control-simple @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone', Auth::user()->phone) }}"
                                               placeholder="+1 (555) 123-4567">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="timezone" class="form-label-simple">Timezone</label>
                                        <select class="form-control-simple @error('timezone') is-invalid @enderror" 
                                                id="timezone" 
                                                name="timezone">
                                            <option value="">Select Timezone</option>
                                            <option value="UTC" {{ old('timezone', Auth::user()->timezone) == 'UTC' ? 'selected' : '' }}>UTC</option>
                                            <option value="America/New_York" {{ old('timezone', Auth::user()->timezone) == 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                            <option value="America/Chicago" {{ old('timezone', Auth::user()->timezone) == 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                            <option value="America/Denver" {{ old('timezone', Auth::user()->timezone) == 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                            <option value="America/Los_Angeles" {{ old('timezone', Auth::user()->timezone) == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                        </select>
                                        @error('timezone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center pt-2">
                                <button type="submit" class="btn-simple">
                                    Update Profile
                                </button>
                                <button type="button" class="btn-outline-simple" onclick="resetForm()">
                                    Reset Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card-simple">
                    <div class="card-header-simple">
                        Change Password
                    </div>
                    <div class="card-body-simple">
                        @if (session('password_status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('password_status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('erp.profile.password') }}" id="passwordForm">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label-simple">Current Password</label>
                                <input type="password" 
                                       class="form-control-simple @error('current_password') is-invalid @enderror" 
                                       id="current_password" 
                                       name="current_password" 
                                       required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label-simple">New Password</label>
                                <input type="password" 
                                       class="form-control-simple @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required
                                       minlength="8"
                                       oninput="checkPasswordStrength(this.value)">
                                <div class="password-strength" id="passwordStrength"></div>
                                <small class="text-muted-simple">
                                    Password must be at least 8 characters with uppercase, lowercase, number, and special character.
                                </small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label-simple">Confirm New Password</label>
                                <input type="password" 
                                       class="form-control-simple" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required
                                       oninput="checkPasswordMatch()">
                                <div id="passwordMatch" class="text-muted-simple"></div>
                            </div>
                            
                            <button type="submit" class="btn-simple" id="changePasswordBtn" disabled>
                                Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar Information -->
            <div class="col-lg-4">
                <!-- Account Activity -->
                <div class="card-simple">
                    <div class="card-header-simple">
                        Recent Activity
                    </div>
                    <div class="card-body-simple">
                        <div class="activity-item-simple">
                            <small class="text-muted-simple">Last login</small>
                            <div class="fw-semibold">{{ Auth::user()->last_login_at?->diffForHumans() ?? 'Never' }}</div>
                        </div>
                        <div class="activity-item-simple">
                            <small class="text-muted-simple">Profile updated</small>
                            <div class="fw-semibold">{{ Auth::user()->updated_at->diffForHumans() }}</div>
                        </div>
                        <div class="activity-item-simple">
                            <small class="text-muted-simple">Account created</small>
                            <div class="fw-semibold">{{ Auth::user()->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="card-simple">
                    <div class="card-header-simple">
                        Security Settings
                    </div>
                    <div class="card-body-simple">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="fw-semibold">Two-Factor Authentication</div>
                                <small class="text-muted-simple">Add an extra layer of security</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="twoFactorSwitch" 
                                       {{ Auth::user()->two_factor_enabled ? 'checked' : '' }}>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">Email Notifications</div>
                                <small class="text-muted-simple">Receive security alerts</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" 
                                       {{ Auth::user()->email_notifications ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Password visibility toggle
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker
function checkPasswordStrength(password) {
    const strengthBar = document.getElementById('passwordStrength');
    const btn = document.getElementById('changePasswordBtn');
    
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    strengthBar.className = 'password-strength';
    
    if (strength < 3) {
        strengthBar.classList.add('strength-weak');
    } else if (strength < 5) {
        strengthBar.classList.add('strength-medium');
    } else {
        strengthBar.classList.add('strength-strong');
    }
    
    checkPasswordMatch();
}

// Password match checker
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('password_confirmation').value;
    const matchDiv = document.getElementById('passwordMatch');
    const btn = document.getElementById('changePasswordBtn');
    
    if (confirm === '') {
        matchDiv.innerHTML = '';
        btn.disabled = true;
        return;
    }
    
    if (password === confirm) {
        matchDiv.innerHTML = '<span class="text-success"><i class="fas fa-check me-1"></i>Passwords match</span>';
        btn.disabled = password.length < 8;
    } else {
        matchDiv.innerHTML = '<span class="text-danger"><i class="fas fa-times me-1"></i>Passwords do not match</span>';
        btn.disabled = true;
    }
}

// Reset form
function resetForm() {
    document.getElementById('profileForm').reset();
}

// Avatar upload (placeholder - requires backend implementation)
document.getElementById('avatarInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

document.querySelector('.avatar-upload').addEventListener('click', function() {
    document.getElementById('avatarInput').click();
});

// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>
@endpush
@endsection