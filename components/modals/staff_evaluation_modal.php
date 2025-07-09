<div class="modal fade" id="evaluateModal" tabindex="-1" aria-labelledby="evaluateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="evaluateModalLabel">
                    <i class="lni lni-star me-2"></i> Staff Performance Evaluation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <img id="evaluationImage" src="../../../assets/images/spark_logo.png" alt="Report Image"
                        class="img-fluid rounded shadow-sm"
                        style="max-width: 100%; height: auto; max-height: 300px; object-fit: contain;" />
                </div>

                <div id="evaluationDescription" class="mb-4 p-3 border rounded bg-light">
                    <strong style="color: var(--maroon);">Description:</strong> <span></span>
                </div>

                <div id="ratingDisplay" class="mt-3"></div>

                <form id="evaluationForm" action="../api/submit_evaluation.php" method="POST">
                    <?php echo CSRFProtection::getTokenField(); ?>
                    <input type="hidden" name="employee_id" value="">
                    <input type="hidden" name="report_id" value="">

                    <div class="bg-light p-4 rounded shadow-sm mb-4">
                        <h6 style="color: var(--maroon);" class="text-start mb-3">Standards Criteria</h6>

                        <!-- Modified criteria grid for better mobile display -->
                        <div class="criteria-grid mb-4 border-bottom pb-3">
                            <div class="criteria-cell excellent">4<br><small>Excellent</small></div>
                            <div class="criteria-cell good">3<br><small>Good</small></div>
                            <div class="criteria-cell needs-improvement">2<br><small>Needs Improvement</small></div>
                            <div class="criteria-cell unsatisfactory">1<br><small>Unsatisfactory</small></div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label"><strong style="color: var(--maroon);">Task
                                    Completion</strong></label>
                            <div class="rating-options-grid">
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="task_completion"
                                        value="Excellent" id="taskCompletionExcellent" required>
                                    <label class="rating-badge excellent" for="taskCompletionExcellent">4</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="task_completion" value="Good"
                                        id="taskCompletionGood">
                                    <label class="rating-badge good" for="taskCompletionGood">3</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="task_completion"
                                        value="Needs Improvement" id="taskCompletionNeedsImprovement">
                                    <label class="rating-badge needs-improvement"
                                        for="taskCompletionNeedsImprovement">2</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="task_completion"
                                        value="Unsatisfactory" id="taskCompletionUnsatisfactory">
                                    <label class="rating-badge unsatisfactory"
                                        for="taskCompletionUnsatisfactory">1</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label"><strong style="color: var(--maroon);">Attention To
                                    Detail</strong></label>
                            <div class="rating-options-grid">
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="attention_to_detail"
                                        value="Excellent" id="attentionToDetailExcellent" required>
                                    <label class="rating-badge excellent" for="attentionToDetailExcellent">4</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="attention_to_detail"
                                        value="Good" id="attentionToDetailGood">
                                    <label class="rating-badge good" for="attentionToDetailGood">3</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="attention_to_detail"
                                        value="Needs Improvement" id="attentionToDetailNeedsImprovement">
                                    <label class="rating-badge needs-improvement"
                                        for="attentionToDetailNeedsImprovement">2</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="attention_to_detail"
                                        value="Unsatisfactory" id="attentionToDetailUnsatisfactory">
                                    <label class="rating-badge unsatisfactory"
                                        for="attentionToDetailUnsatisfactory">1</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label"><strong style="color: var(--maroon);">Trash
                                    Management</strong></label>
                            <div class="rating-options-grid">
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="trash_management"
                                        value="Excellent" id="trashManagementExcellent" required>
                                    <label class="rating-badge excellent" for="trashManagementExcellent">4</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="trash_management"
                                        value="Good" id="trashManagementGood">
                                    <label class="rating-badge good" for="trashManagementGood">3</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="trash_management"
                                        value="Needs Improvement" id="trashManagementNeedsImprovement">
                                    <label class="rating-badge needs-improvement"
                                        for="trashManagementNeedsImprovement">2</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="trash_management"
                                        value="Unsatisfactory" id="trashManagementUnsatisfactory">
                                    <label class="rating-badge unsatisfactory"
                                        for="trashManagementUnsatisfactory">1</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label"><strong style="color: var(--maroon);">Floor
                                    Care</strong></label>
                            <div class="rating-options-grid">
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="floor_care" value="Excellent"
                                        id="floorCareExcellent" required>
                                    <label class="rating-badge excellent" for="floorCareExcellent">4</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="floor_care" value="Good"
                                        id="floorCareGood">
                                    <label class="rating-badge good" for="floorCareGood">3</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="floor_care"
                                        value="Needs Improvement" id="floorCareNeedsImprovement">
                                    <label class="rating-badge needs-improvement"
                                        for="floorCareNeedsImprovement">2</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="floor_care"
                                        value="Unsatisfactory" id="floorCareUnsatisfactory">
                                    <label class="rating-badge unsatisfactory"
                                        for="floorCareUnsatisfactory">1</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label"><strong
                                    style="color: var(--maroon);">Organization</strong></label>
                            <div class="rating-options-grid">
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="organization"
                                        value="Excellent" id="organizationExcellent" required>
                                    <label class="rating-badge excellent" for="organizationExcellent">4</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="organization" value="Good"
                                        id="organizationGood">
                                    <label class="rating-badge good" for="organizationGood">3</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="organization"
                                        value="Needs Improvement" id="organizationNeedsImprovement">
                                    <label class="rating-badge needs-improvement"
                                        for="organizationNeedsImprovement">2</label>
                                </div>
                                <div class="rating-option">
                                    <input class="form-check-input" type="radio" name="organization"
                                        value="Unsatisfactory" id="organizationUnsatisfactory">
                                    <label class="rating-badge unsatisfactory"
                                        for="organizationUnsatisfactory">1</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <label for="remark" class="form-label fw-bold" style="color: var(--maroon);">Remarks</label>
                        <textarea class="form-control" id="remark" name="remark" rows="4"
                            placeholder="Add detailed remarks here..." required
                            style="border-color: var(--maroon);"></textarea>
                        <div class="form-text">Please provide specific feedback to help with improvement.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEvaluation">
                    <i class="lni lni-checkmark-circle me-1"></i> Send Evaluation
                </button>
            </div>
        </div>
    </div>
</div>
