<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">
                    <i class="lni lni-user-add me-2"></i>
                    Create Staff Record
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../api/create_staff_record.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="lni lni-user"></i></span>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="lni lni-user"></i></span>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="contact_no" class="form-label">Contact Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="lni lni-phone"></i></span>
                            <input type="text" id="contact_no" name="contact_no" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email_address" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="lni lni-envelope"></i></span>
                            <input type="email" id="email_address" name="email_address" class="form-control"
                                required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">Position</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="lni lni-briefcase"></i></span>
                            <select id="position" name="position" class="form-select" required
                                onchange="toggleOtherInput()">
                                <option value="" disabled selected>Select a position</option>
                                <option value="Janitor">Janitor</option>
                                <option value="Janitress">Janitress</option>
                                <option value="Gardener">Gardener</option>
                                <option value="Custodian">Custodian</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div id="otherPositionDiv" class="mb-3" style="display: none;">
                        <label for="otherPosition" class="form-label">Please specify</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="lni lni-pencil"></i></span>
                            <input type="text" id="otherPosition" name="otherPosition" class="form-control"
                                oninput="updateDropdown()">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="employee_id" class="form-label">Employee ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="lni lni-id-card"></i></span>
                            <input type="text" id="employee_id" name="employee_id" class="form-control" required>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="lni lni-save me-1"></i> Create Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
