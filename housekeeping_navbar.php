<link href="navbar.css" rel="stylesheet" />

<!-- Menu Button -->
<button class="btn btn-menu" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar"
    aria-controls="offcanvasSidebar">
    <i class="lni lni-menu"></i>
</button>

<!-- Sidebar -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasSidebarLabel">MENU</h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="sidebar-nav list-unstyled">
            <li class="sidebar-item">
                <a href="housekeepingdashboard.php" class="sidebar-link">
                    <i class="lni lni-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="housekeepingprofile.php" class="sidebar-link">
                    <i class="lni lni-user"></i>
                    <span>Account</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#auth" aria-expanded="false" aria-controls="auth">
                    <i class="lni lni-agenda"></i>
                    <span>Task</span>
                    <i class="lni lni-chevron-down" style="margin-left: auto;"></i>
                </a>
                <ul id="auth" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#offcanvasSidebar">
                    <li class="sidebar-item">
                        <a href="progressreport.php" class="sidebar-link">Progress Report</a>
                    </li>
                    <li class="sidebar-item">
                        <a href="progressassessment.php" class="sidebar-link">Progress Assessment</a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a href="housekeeping_leaderboard.php" class="sidebar-link">
                    <i class="lni lni-crown"></i>
                    <span>Leaderboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="housekeeping_history.php" class="sidebar-link">
                    <i class="lni lni-timer"></i>
                    <span>Supply Usage History</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="housekeeping_request.php" class="sidebar-link">
                    <i class="lni lni-reply"></i>
                    <span>Supply Request</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="index.php" class="sidebar-link" id="logout-link">
                    <i class="lni lni-exit"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</div>