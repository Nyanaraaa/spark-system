 <link href="navbar.css" rel="stylesheet" />

 <!-- Menu Button -->
    <button class="btn btn-tertiary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar"
        aria-controls="offcanvasSidebar">
        <i class="lni lni-menu"></i>
    </button>

    <!-- Sidebar Navigation -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasSidebarLabel">MENU</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="sidebar-nav list-unstyled">
                <li class="sidebar-item">
                    <a href="supervisorprofile.php" class="sidebar-link">
                        <i class="lni lni-user"></i>
                        <span>Account</span>
                    </a>
                </li>
                 <li class="sidebar-item">
                    <a href="supervisorassessment.php" class="sidebar-link">
                        <i class="lni lni-bar-chart"></i>
                        <span>Assessment</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="supervisor_leaderboard.php" class="sidebar-link">
                        <i class="lni lni-crown"></i>
                        <span>Leaderboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth" aria-expanded="false" aria-controls="auth">
                        <i class="lni lni-users"></i>
                        <span>Manage Staff</span>
                        <i class="lni lni-chevron-down" style="margin-left: auto;"></i>
                    </a>
                    <ul id="auth" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#offcanvasSidebar">
                        <li class="sidebar-item">
                            <a href="staff_list.php" class="sidebar-link">Staff List</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="staff.php" class="sidebar-link">Staff Record</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="manage_location.php" class="sidebar-link">
                        <i class="lni lni-map"></i>
                        <span>Manage Location</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="manage_schedule.php" class="sidebar-link">
                        <i class="lni lni-calendar"></i>
                        <span>Manage Schedule</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="supervisorupdate.php" class="sidebar-link">
                        <i class="lni lni-archive"></i>
                        <span>Manage Supplies</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="supervisor_usage_history.php" class="sidebar-link">
                        <i class="lni lni-timer"></i>
                        <span>Supplies Usage History</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="supervisor_request.php" class="sidebar-link">
                        <i class="lni lni-clipboard"></i>
                        <span>Supplies Request</span>
                    </a>
                </li>
                <div class="sidebar-item mt-4">
                    <a href="index.php" class="sidebar-link" id="logout-link">
                        <i class="lni lni-exit"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </ul>
        </div>
    </div>