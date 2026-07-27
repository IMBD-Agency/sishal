@extends('erp.master')

@section('title', 'Profile Settings')

@push('styles')
<!-- Profile styles are loaded from public/erp.css -->
@endpush

@section('body')
@include('erp.components.sidebar')
<div class="main-content" id="mainContent">
    @include('erp.components.header')
    <div class="container-fluid py-4">
        <!-- Modern Cover Header -->
        <div class="profile-cover-banner">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-4 flex-wrap flex-sm-nowrap">
                        <div class="profile-avatar-wrapper">
                            <img src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->first_name . ' ' . Auth::user()->last_name) . '&background=198754&color=fff&size=100' }}" 
                                 alt="Profile Avatar" class="profile-avatar">
                            <span class="profile-status-badge" title="Active Account"></span>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <h3 class="mb-0 text-white fw-bold">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h3>
                                <span class="badge bg-white text-success px-3 py-1 rounded-pill fw-semibold shadow-sm">
                                    <i class="fas fa-shield-halved me-1"></i>{{ Auth::user()->roles->first()->name ?? 'User' }}
                                </span>
                            </div>
                            <p class="mb-2 text-white-50 fs-6">
                                <i class="far fa-envelope me-1 text-white-50"></i> {{ Auth::user()->email }} 
                                @if(Auth::user()->employee) 
                                    <span class="mx-2 opacity-50">|</span> 
                                    <i class="fas fa-briefcase me-1 text-white-50"></i> <span class="fw-semibold text-white">{{ Auth::user()->employee->designation }}</span> 
                                @endif
                            </p>
                            <div class="d-flex align-items-center gap-3 text-white-50 small">
                                <span><i class="far fa-calendar-alt me-1"></i> Member since {{ Auth::user()->created_at->format('M Y') }}</span>
                                <span><i class="fas fa-circle me-1 text-success" style="font-size: 8px;"></i> System Online</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Forms -->
            <div class="col-lg-8">
                <!-- Profile Information -->
                <div class="card-simple">
                    <div class="card-header-simple">
                        <span><i class="fas fa-user-gear text-success me-2"></i> Profile Information</span>
                        <span class="badge bg-light text-muted fw-normal fs-7">Basic Account Details</span>
                    </div>
                    <div class="card-body-simple">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('erp.profile.update') }}" id="profileForm">
                            @csrf
                            @method('PUT')
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label-simple">First Name</label>
                                        <div class="input-group-modern">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" 
                                                   class="form-control-simple @error('first_name') is-invalid @enderror" 
                                                   id="first_name" 
                                                   name="first_name" 
                                                   value="{{ old('first_name', Auth::user()->first_name) }}" 
                                                   required
                                                   maxlength="50"
                                                   placeholder="First Name">
                                        </div>
                                        @error('first_name')
                                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label-simple">Last Name</label>
                                        <div class="input-group-modern">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" 
                                                   class="form-control-simple @error('last_name') is-invalid @enderror" 
                                                   id="last_name" 
                                                   name="last_name" 
                                                   value="{{ old('last_name', Auth::user()->last_name) }}" 
                                                   required
                                                   maxlength="50"
                                                   placeholder="Last Name">
                                        </div>
                                        @error('last_name')
                                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="email" class="form-label-simple">Email Address</label>
                                <div class="input-group-modern">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" 
                                           class="form-control-simple @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', Auth::user()->email) }}" 
                                           required
                                           placeholder="Email Address"
                                           {{ str_ends_with(Auth::user()->email, '@staff.internal') ? 'readonly' : '' }}>
                                </div>
                                @if(str_ends_with(Auth::user()->email, '@staff.internal'))
                                    <div class="mt-1 text-muted-simple"><i class="fas fa-info-circle text-info me-1"></i> Internal staff email cannot be modified.</div>
                                @endif
                                @error('email')
                                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <button type="submit" class="btn-simple">
                                    <i class="fas fa-save me-1"></i> Update Profile
                                </button>
                                <button type="button" class="btn-outline-simple" onclick="resetForm()">
                                    <i class="fas fa-rotate-left me-1"></i> Reset Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card-simple">
                    <div class="card-header-simple">
                        <span><i class="fas fa-shield-alt text-success me-2"></i> Security & Password</span>
                        <span class="badge bg-light text-muted fw-normal fs-7">Credentials</span>
                    </div>
                    <div class="card-body-simple">
                        @if (session('password_status'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('password_status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('erp.profile.password') }}" id="passwordForm">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label-simple">Current Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-lock"></i></span>
                                    <input type="password" 
                                           class="form-control form-control-simple border-start-0 @error('current_password') is-invalid @enderror" 
                                           id="current_password" 
                                           name="current_password" 
                                           placeholder="Enter current password"
                                           required>
                                    <button type="button" class="btn btn-outline-secondary border-start-0 text-muted" onclick="togglePassword('current_password')">
                                        <i class="fas fa-eye" id="current_password_icon"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label-simple">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-key"></i></span>
                                    <input type="password" 
                                           class="form-control form-control-simple border-start-0 @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           required
                                           minlength="8"
                                           placeholder="At least 8 characters"
                                           oninput="checkPasswordStrength(this.value)">
                                    <button type="button" class="btn btn-outline-secondary border-start-0 text-muted" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password_icon"></i>
                                    </button>
                                </div>
                                <div class="password-strength-container">
                                    <div class="password-strength" id="passwordStrength"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted-simple">
                                        Must contain uppercase, lowercase, numbers, and symbols.
                                    </small>
                                    <small id="strengthText" class="fw-semibold text-muted-simple"></small>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label-simple">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-check-double"></i></span>
                                    <input type="password" 
                                           class="form-control form-control-simple border-start-0" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           placeholder="Re-enter new password"
                                           required
                                           oninput="checkPasswordMatch()">
                                    <button type="button" class="btn btn-outline-secondary border-start-0 text-muted" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="password_confirmation_icon"></i>
                                    </button>
                                </div>
                                <div id="passwordMatch" class="mt-1 text-muted-simple"></div>
                            </div>
                            
                            <div class="pt-2 border-top">
                                <button type="submit" class="btn-simple" id="changePasswordBtn" disabled>
                                    <i class="fas fa-lock me-1"></i> Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar Info -->
            <div class="col-lg-4">
                <!-- Account Overview -->
                <div class="card-simple">
                    <div class="card-header-simple">
                        <span><i class="fas fa-id-card text-success me-2"></i> Account Details</span>
                    </div>
                    <div class="card-body-simple">
                        <div class="activity-item-simple">
                            <div class="d-flex align-items-center gap-3">
                                <div class="info-icon-badge bg-success-subtle text-success">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div>
                                    <div class="text-muted-simple mb-0">System Role</div>
                                    <div class="fw-bold text-dark fs-6">{{ Auth::user()->roles->first()->name ?? 'User' }}</div>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                        </div>

                        @if(Auth::user()->employee)
                        <div class="activity-item-simple">
                            <div class="d-flex align-items-center gap-3">
                                <div class="info-icon-badge bg-primary-subtle text-primary">
                                    <i class="fas fa-user-tag"></i>
                                </div>
                                <div>
                                    <div class="text-muted-simple mb-0">Designation</div>
                                    <div class="fw-bold text-dark fs-6">{{ Auth::user()->employee->designation ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="activity-item-simple">
                            <div class="d-flex align-items-center gap-3">
                                <div class="info-icon-badge bg-info-subtle text-info">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div>
                                    <div class="text-muted-simple mb-0">Assigned Branch</div>
                                    <div class="fw-bold text-dark fs-6">{{ Auth::user()->employee->branch->name ?? 'Global' }}</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="activity-item-simple">
                            <div class="d-flex align-items-center gap-3">
                                <div class="info-icon-badge bg-warning-subtle text-warning">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div>
                                    <div class="text-muted-simple mb-0">Account Created</div>
                                    <div class="fw-bold text-dark fs-6">{{ Auth::user()->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Help / Support Card -->
                <div class="card-simple bg-light border-0">
                    <div class="card-body-simple text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-shield-cat fa-2x text-success"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Need Security Help?</h6>
                        <p class="text-muted-simple small mb-3">If you notice suspicious activity or need account privileges changed, please contact system administration.</p>
                        <a href="mailto:admin@sisalfashion.com" class="btn btn-sm btn-outline-success rounded-pill px-3">
                            <i class="fas fa-headset me-1"></i> Contact Admin
                        </a>
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
    const strengthText = document.getElementById('strengthText');
    
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    strengthBar.className = 'password-strength';
    
    if (password.length === 0) {
        strengthBar.style.width = '0%';
        if (strengthText) strengthText.textContent = '';
    } else if (strength < 3) {
        strengthBar.classList.add('strength-weak');
        if (strengthText) { strengthText.textContent = 'Weak'; strengthText.className = 'fw-semibold text-danger'; }
    } else if (strength < 5) {
        strengthBar.classList.add('strength-medium');
        if (strengthText) { strengthText.textContent = 'Medium'; strengthText.className = 'fw-semibold text-warning'; }
    } else {
        strengthBar.classList.add('strength-strong');
        if (strengthText) { strengthText.textContent = 'Strong'; strengthText.className = 'fw-semibold text-success'; }
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
        matchDiv.innerHTML = '<span class="text-success small fw-semibold"><i class="fas fa-check-circle me-1"></i>Passwords match</span>';
        btn.disabled = password.length < 8;
    } else {
        matchDiv.innerHTML = '<span class="text-danger small fw-semibold"><i class="fas fa-times-circle me-1"></i>Passwords do not match</span>';
        btn.disabled = true;
    }
}

// Reset form
function resetForm() {
    document.getElementById('profileForm').reset();
    if (window.showToast) {
        window.showToast('Profile form reset', 'info');
    }
}

// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        if (window.bootstrap && bootstrap.Alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    });
}, 5000);
</script>
@endpush
@endsection