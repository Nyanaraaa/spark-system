<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">
                    <i class="lni lni-files me-2"></i>Progress Report Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="detailsImage" src="../../../assets/images/spark_logo.png" alt="Report Image" class="img-fluid"
                        style="max-height: 250px;" />
                </div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <strong><i class="lni lni-text-format me-2"
                                style="color: var(--maroon);"></i>Description:</strong>
                        <p id="detailsDescription" class="mt-2 p-2 bg-light rounded"></p>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="lni lni-calendar me-2" style="color: var(--maroon);"></i><strong>Submitted
                                At:</strong></span>
                        <span id="detailsCreated"></span>
                    </li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
