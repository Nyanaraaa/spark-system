<div class="modal fade" id="addSupplyModal" tabindex="-1" aria-labelledby="addSupplyModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSupplyModalLabel">
                    <i class="lni lni-plus-circle me-2"></i> Add New Supply
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../api/add_supplies.php" method="post">
                    <?php echo CSRFProtection::getTokenField(); ?>
                    <div class="mb-3">
                        <label for="supply-name" class="form-label fw-bold" style="color: var(--maroon);">Supply
                            Name</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-package"></i>
                            </span>
                            <input type="text" id="supply-name" name="supply_name" class="form-control"
                                required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label fw-bold" style="color: var(--maroon);">Brand
                            Name</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-tag"></i>
                            </span>
                            <input type="text" id="category" name="category" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="classification" class="form-label fw-bold"
                            style="color: var(--maroon);">Classification</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-folder"></i>
                            </span>
                            <select id="classification" name="classification" class="form-select"
                                onchange="toggleOtherInput()" required>
                                <option value="" disabled selected>Select Classification</option>
                                <option value="Floor Cleaner">Floor Cleaner</option>
                                <option value="All-purpose Cleaner">All-purpose Cleaner</option>
                                <option value="Bathroom Cleaner">Bathroom Cleaner</option>
                                <option value="Disinfectant">Disinfectant</option>
                                <option value="Heavy-duty Cleaner">Heavy-duty Cleaner</option>
                                <option value="Rust Remover">Rust Remover</option>
                                <option value="Stain Remover">Stain Remover</option>
                                <option value="Upholstery Cleaner">Upholstery Cleaner</option>
                                <option value="Pool/Surface Cleaner">Pool/Surface Cleaner</option>
                                <option value="Surface Scrubber">Surface Scrubber</option>
                                <option value="Wiping Cloth">Wiping Cloth</option>
                                <option value="Painting Tool">Painting Tool</option>
                                <option value="Hand Protection">Hand Protection</option>
                                <option value="Waste Disposal">Waste Disposal</option>
                                <option value="Plumbing Accessory">Plumbing Accessory</option>
                                <option value="Dish Soap">Dish Soap</option>
                                <option value="Laundry Detergent">Laundry Detergent</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3" id="otherClassificationDiv" style="display: none;">
                        <label for="otherClassification" class="form-label fw-bold"
                            style="color: var(--maroon);">Custom Classification</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-pencil"></i>
                            </span>
                            <input type="text" id="otherClassification" class="form-control"
                                placeholder="Enter new classification" onblur="updateDropdown()">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="initial-stock" class="form-label fw-bold"
                            style="color: var(--maroon);">Initial Stock</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-database"></i>
                            </span>
                            <input type="number" id="initial-stock" name="initial_stock" class="form-control"
                                min="0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="stock-limit" class="form-label fw-bold" style="color: var(--maroon);">Stock
                            Limit</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-warning"></i>
                            </span>
                            <input type="number" id="stock-limit" name="stock_limit" class="form-control"
                                min="1" required>
                        </div>
                        <div class="form-text">Set the maximum stock for this supply.</div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-secondary me-2"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="lni lni-checkmark-circle me-1"></i> Add Supply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
