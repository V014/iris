<?php
  session_start();

  // Block access if the user is not logged in
  if (!isset($_SESSION['user_id'])) {
      header('Location: ../index.php');
      exit;
}

// Include the database connection
require_once '../controls/connection.php';

// Include the audit control file
require_once 'controls/audit.php';
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes"
    />
    <title>Audit log</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="css/audit.css">
    <script src="../js/htmx.org@2.0.0.min.js"></script>
  </head>
  <body>
    <div class="app-layout">
      <!-- SIDEBAR (Admin Manager) -->
      <aside class="sidebar" id="sidebar">
        <div class="brand">
          <div class="avatar">
            <img
              src="1774245015_69c0d497743f0.jpeg"
              alt="User avatar"
              onerror="
                this.parentElement.innerHTML =
                  '<i class=\'fas fa-user\' style=\'font-size:1.5rem;color:#1a1a1a;\'></i>'
              "
            />
          </div>
          <div class="user-info">
            <span id="sidebarUserName">System Administrator</span>
            <br /><small><?php echo $_SESSION['username']; ?></small>
          </div>
        </div>

        <!-- <div class="nav-section">Management</div> -->
        <nav class="nav-links">
          <a href="dashboard.php" data-page="dashboard"
            ><i class="fas fa-th-large"></i> Dashboard</a
          >
          <a href="manage.php" data-page="admins"
            ><i class="fas fa-user-cog"></i> Manage Accounts</a
          >
          <a href="branches.php" data-page="branches"
            ><i class="fas fa-shop"></i> Manage Branches</a
          >
          <a href="roles.php" data-page="roles"
            ><i class="fas fa-user-tag"></i> Manage Roles</a
          >
          <a href="audit.php" data-page="audit" class="active-link"
            ><i class="fas fa-clipboard-list"></i> Audit Log</a
          >
        </nav>

        <!-- <div class="nav-section">System</div>
        <nav class="nav-links">
          <a href="#" data-page="settings"
            ><i class="fas fa-cog"></i> Settings</a
          >
          <a href="#" data-page="backup"
            ><i class="fas fa-database"></i> Backup</a
          >
        </nav> -->

        <div class="logout-wrapper">
          <a href="../controls/logout.php" data-page="logout"
            ><i class="fas fa-sign-out-alt"></i> Logout</a
          >
        </div>
      </aside>

      <!-- MAIN CONTENT -->
      <main class="main-content">
        <div class="content-wrapper">
          <!-- BACK BUTTON -->
          <!-- <div class="back-bar">
            <a href="dashboard.php" class="back-btn"
              ><i class="fas fa-arrow-left"></i> Back to Dashboard</a
            >
          </div> -->

          <!-- WELCOME CARD -->
          <div class="panel-card welcome-card">
            <div class="welcome-left">
              <h2>
                <i class="fas fa-clipboard-list"></i>
                Audit Log
              </h2>
            </div>
          </div>

          <!-- STATS MINI -->
          <div class="stats-mini">
            <div class="stat-mini">
              <div class="stat-number"><?php echo $getTotalEvents['total']; ?></div>
              <div class="stat-label">Total Events</div>
            </div>
            <div class="stat-mini">
              <div class="stat-number"><?php echo $getTodaysEvents['total']; ?></div>
              <div class="stat-label">Today</div>
            </div>
            <div class="stat-mini">
              <div class="stat-number"><?php echo $getTotalCreates['total']; ?></div>
              <div class="stat-label">Creates</div>
            </div>
            <div class="stat-mini">
              <div class="stat-number"><?php echo $getTotalUpdates['total']; ?></div>
              <div class="stat-label">Updates</div>
            </div>
            <div class="stat-mini">
              <div class="stat-number"><?php echo $getTotalDeletes['total']; ?></div>
              <div class="stat-label">Deletes</div>
            </div>
            <div class="stat-mini">
              <div class="stat-number"><?php echo $getTotalLogins['total']; ?></div>
              <div class="stat-label">Logins</div>
            </div>
            <div class="stat-mini">
              <div class="stat-number"><?php echo $getTotalLogouts['total']; ?></div>
              <div class="stat-label">Logouts</div>
            </div>
          </div>

          <!-- ACTION BAR (Export only) -->
          <div class="action-bar">
            <button class="action-btn primary-btn" id="exportBtn">
              <i class="fas fa-file-pdf"></i> Export PDF
            </button>
          </div>

          <!-- FILTERS -->
          <div class="filters-container">
            <div class="filter-group">
              <label for="filterUser">User</label>
              <input type="text" id="filterUser" placeholder="Search user..." />
            </div>
            <div class="filter-group">
              <label for="filterAction">Action</label>
              <select id="filterAction">
                <option value="all">All Actions</option>
                <option value="create">Create</option>
                <option value="update">Update</option>
                <option value="delete">Delete</option>
                <option value="login">Login</option>
                <option value="logout">Logout</option>
              </select>
            </div>
            <div class="filter-group">
              <label for="filterStatus">Status</label>
              <select id="filterStatus">
                <option value="all">All Status</option>
                <option value="success">Success</option>
                <option value="failure">Failure</option>
              </select>
            </div>
            <div class="filter-group">
              <label for="filterDate">Date</label>
              <input type="date" id="filterDate" />
            </div>
            <button class="clear-filters-btn" id="clearFilters">
              <i class="fas fa-times"></i> Clear Filters
            </button>
          </div>

          <!-- TABLE -->
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>User</th>
                  <th>Action</th>
                  <th>Target</th>
                  <th>Details</th>
                  <th>Status</th>
                  <th>Timestamp</th>
                </tr>
              </thead>
              <!-- Fetch list of logs -->
              <tbody id=""><?php foreach ($getUserLogs as $logs) 
                            { echo "<tr>
                                      <td>" . $logs['id'] . "</td>
                                      <td>" . $logs['username'] . "</td>
                                      <td><span class='action-badge action-" . $logs['action'] . "'>" . $logs['action'] . "</span></td>
                                      <td>" . $logs['target'] . "</td>
                                      <td>" . $logs['details'] . "</td>
                                      <td><span class='status-badge status-" . $logs['status'] . "'>" . $logs['status'] . "</span></td>
                                      <td>" . $logs['created_at'] . "</td>
                                    </tr>"; 
                              } 
                            ?>
              </tbody>
              <!-- <tbody id="tableBody"></tbody> -->
            </table>
            <div id="emptyState" class="empty-state" style="display: none">
              No audit events found
            </div>
          </div>

          <!-- PAGINATION -->
          <div class="pagination-container">
            <div class="rows-per-page">
              <label for="rowsPerPage">Rows per page:</label>
              <select id="rowsPerPage">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="all">All</option>
              </select>
            </div>
            <div class="pagination-controls">
              <button id="prevPage" disabled>
                <i class="fas fa-chevron-left"></i> Previous
              </button>
              <span class="page-info" id="pageInfo">Page 1 of 1</span>
              <button id="nextPage" disabled>
                Next <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>
    <script src="js/audit.js"></script>
  </body>
</html>
