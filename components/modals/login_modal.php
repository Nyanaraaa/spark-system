<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="loginModalLabel">Select Your Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted text-center" id="modalDescription">Please choose your role to continue:</p>
                <div class="row text-center">
                    <div class="col-12 col-md-6 mb-3">
                        <button type="button" class="btn btn-outline btn-lg w-100 py-3 role-btn"
                            data-bs-toggle="modal" data-bs-target="#supervisorLoginModal" data-bs-dismiss="modal"
                            aria-label="Select Supervisor Role">
                            <i class="bi bi-person-workspace fs-3 me-2"></i> Supervisor
                        </button>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <button type="button" class="btn btn-lg w-100 py-3 role-btn" data-bs-toggle="modal"
                            data-bs-target="#staffLoginModal" data-bs-dismiss="modal"
                            aria-label="Select Housekeeping Staff Role">
                            <i class="bi bi-person fs-3 me-2"></i> Housekeeping Staff
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
