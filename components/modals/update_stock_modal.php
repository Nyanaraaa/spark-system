<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">
                    <i class="lni lni-reload me-2"></i> Restock Supplies
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../api/update_stock.php" method="post">
                    <?php echo CSRFProtection::getTokenField(); ?>
                    <input type="hidden" id="update-supplies-id" name="supplies_id">

                    <div class="mb-4">
                        <label for="update-stocks" class="form-label fw-bold" style="color: var(--maroon);">Add
                            Stock Quantity</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-plus"></i>
                            </span>
                            <input type="number" id="update-stocks" name="stocks" class="form-control" min="0"
                                required>
                        </div>
                        <div class="form-text">Enter the quantity you want to add to current stock.</div>
                    </div>

                    <div class="mb-4">
                        <label for="update-stock-limit" class="form-label fw-bold"
                            style="color: var(--maroon);">Stock Limit</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-warning"></i>
                            </span>
                            <input type="number" id="update-stock-limit" name="stock_limit" class="form-control"
                                min="1" required>
                        </div>
                        <div class="form-text">Set the maximum stock for this supply.</div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-secondary me-2"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="lni lni-checkmark-circle me-1"></i> Update Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
