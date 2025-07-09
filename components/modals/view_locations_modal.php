<div class="modal fade" id="viewLocationsModal" tabindex="-1" aria-labelledby="viewLocationsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewLocationsModalLabel">
                    <i class="lni lni-map me-2"></i>
                    Locations and Staff
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="lni lni-search"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control"
                            placeholder="Search by building, location or staff" onkeyup="filterLocations()">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="locationsTable">
                        <thead>
                            <tr>
                                <th width="20%">Building</th>
                                <th width="25%">Location Name</th>
                                <th width="40%">Assigned Staff</th>
                                <th width="15%">Number of Assigned Staff</th>
                            </tr>
                        </thead>
                        <tbody id="locationsTableBody">
                            <!-- Table rows will be populated here -->
                        </tbody>
                    </table>
                    <div id="noRecordMessage" style="display: none;" class="text-center py-4">
                        <i class="lni lni-search-alt" style="font-size: 2rem; color: var(--maroon-light);"></i>
                        <p class="mt-2 mb-0">No matching records found</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="printLocationsBtn">
                    <i class="lni lni-printer me-2"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>
