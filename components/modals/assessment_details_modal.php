<div class="modal fade" id="viewAssessmentModal" tabindex="-1" aria-labelledby="viewAssessmentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewAssessmentModalLabel">
                    <i class="lni lni-star-filled me-2"></i>Assessment Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <div class="dashboard-card mb-3">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="lni lni-star me-2"></i>Overall Rating
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <div class="rating-display" id="ratingDisplay">-</div>
                                <div class="text-muted">Performance Score</div>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-center">
                                        <div class="px-2">
                                            <i class="lni lni-star-filled" style="color: var(--gold);"></i>
                                        </div>
                                        <div class="px-2">
                                            <i class="lni lni-star-filled" style="color: var(--gold);"></i>
                                        </div>
                                        <div class="px-2">
                                            <i class="lni lni-star-filled" style="color: var(--gold);"></i>
                                        </div>
                                        <div class="px-2">
                                            <i class="lni lni-star-filled" style="color: var(--gold);"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-muted mt-2" id="evaluationTime">Evaluated on: <span>-</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="dashboard-card mb-3">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="lni lni-list me-2"></i>Evaluation Criteria
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="criteria-list">
                                    <li class="criteria-item">
                                        <span class="criteria-label">
                                            <i class="lni lni-checkmark me-2" style="color: var(--maroon);"></i>Task
                                            Completion
                                        </span>
                                        <span class="criteria-score" id="criteria-task_completion">-</span>
                                    </li>
                                    <li class="criteria-item">
                                        <span class="criteria-label">
                                            <i class="lni lni-eye me-2" style="color: var(--maroon);"></i>Attention
                                            to Detail
                                        </span>
                                        <span class="criteria-score" id="criteria-attention_to_detail">-</span>
                                    </li>
                                    <li class="criteria-item">
                                        <span class="criteria-label">
                                            <i class="lni lni-cart me-2" style="color: var(--maroon);"></i>Trash
                                            Management
                                        </span>
                                        <span class="criteria-score" id="criteria-trash_management">-</span>
                                    </li>
                                    <li class="criteria-item">
                                        <span class="criteria-label">
                                            <i class="lni lni-brush me-2" style="color: var(--maroon);"></i>Floor
                                            Care
                                        </span>
                                        <span class="criteria-score" id="criteria-floor_care">-</span>
                                    </li>
                                    <li class="criteria-item">
                                        <span class="criteria-label">
                                            <i class="lni lni-grid-alt me-2"
                                                style="color: var(--maroon);"></i>Organization
                                        </span>
                                        <span class="criteria-score" id="criteria-organization">-</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="lni lni-comments me-2"></i>Supervisor Remarks
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="remarks-container" id="remarksText">No remarks provided.</div>
                            </div>
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
