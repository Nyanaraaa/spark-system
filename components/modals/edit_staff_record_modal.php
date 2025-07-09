<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="lni lni-pencil me-2"></i>
                    Edit Staff Record
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../api/edit_staff_record.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="edit_staff_id" name="staff_id">
                    <div class="mb-3">
                        <label for="edit_first_name" class="form-label">First Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="lni lni-user"></i></span>
                            <input type="text" id="edit_first_name" name="first_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_last_name" class="form-label">Last Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="lni lni-user"></i></span>
                            <input type="text" id="edit_last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_contact_no" class="form-label">Contact Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="lni lni-phone"></i></span>
                            <input type="text" id="edit_contact_no" name="contact_no" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email_address" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="lni lni-envelope"></i></span>
                            <input type="email" id="edit_email_address" name="email_address" class="form-control"
                                required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_position" class="form-label">Position</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="lni lni-briefcase"></i></span>
                            <input type="text" id="edit_position" name="position" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_employee_id" class="form-label">Employee ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="lni lni-id-card"></i></span>
                            <input type="text" id="edit_employee_id" name="employee_id" class="form-control"
                                required>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="lni lni-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
