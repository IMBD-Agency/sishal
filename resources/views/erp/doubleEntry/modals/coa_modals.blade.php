<!-- Add Parent Account Modal -->
<div class="modal fade" id="addParentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-folder-plus me-2"></i>Add Parent Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="parentForm" action="{{ route('chart-of-account.parent.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Parent Account Name *</label>
                        <input type="text" class="form-control" id="parent_name" name="name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Account Code *</label>
                            <input type="text" class="form-control" id="parent_code" name="code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Account Type *</label>
                            <select class="form-select" id="parent_type_id" name="type_id" required>
                                <option value="">Select Type</option>
                                @foreach($accountTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Sub Type *</label>
                        <select class="form-select" id="parent_sub_type_id" name="sub_type_id" required>
                            <option value="">Select Sub Type</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea class="form-control" id="parent_description" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Parent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Chart Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Chart Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="accountForm" action="{{ route('chart-of-account.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Account Name *</label>
                        <input type="text" class="form-control" id="account_name" name="name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Account Code *</label>
                            <input type="text" class="form-control" id="account_code" name="code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Parent Account *</label>
                            <select class="form-select" id="account_parent_id" name="parent_id" required>
                                <option value="">Select Parent</option>
                                @foreach($accountParents as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Account Type *</label>
                            <select class="form-select" id="account_type_id" name="type_id" required>
                                <option value="">Select Type</option>
                                @foreach($accountTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Sub Type *</label>
                            <select class="form-select" id="account_sub_type_id" name="sub_type_id" required>
                                <option value="">Select Sub Type</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea class="form-control" id="account_description" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4 shadow-sm">Save Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
