<div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestModalLabel">
                    <i class="lni lni-cart me-2"></i>
                    Supply Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../../../modules/staff/api/submit_request.php" method="POST">
                    <?php echo CSRFProtection::getTokenField(); ?>
                    <input type="hidden" id="request-supplies-id" name="supplies_id">
                    <div class="mb-3">
                        <label for="request-supplies-name" class="form-label">Supply Name</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-package"></i>
                            </span>
                            <input type="text" id="request-supplies-name" name="supplies_name" class="form-control"
                                readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="request-stocks" class="form-label">Available Stocks</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-database"></i>
                            </span>
                            <input type="text" id="request-stocks" name="stocks" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="request-quantity" class="form-label">Quantity</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-calculator"></i>
                            </span>
                            <input type="number" id="request-quantity" name="quantity" class="form-control" required
                                min="1">
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="lni lni-checkmark-circle me-2"></i>
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
