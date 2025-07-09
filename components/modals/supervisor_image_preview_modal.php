<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">
                    <i class="lni lni-image me-2"></i> Image Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-8">
                        <div class="p-3 d-flex justify-content-center align-items-center bg-light"
                            style="min-height: 300px;">
                            <img id="modalImage" src="../../../assets/images/spark_logo.png" alt="Report Image"
                                class="img-fluid rounded shadow-sm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3">
                            <h6 class="border-bottom pb-2 mb-3" style="color: var(--maroon);">Description</h6>
                            <div id="modalDescription" class="text-start"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
