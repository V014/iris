<?php
  session_start();

  // Block access if the user is not logged in
  if (!isset($_SESSION['user_id'])) {
      header('Location: ../index.php');
      exit;
}

// Include the database connection
require_once '../controls/connection.php';

// Include the dashboard controls
require_once 'controls/dashboard.php';
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes"
    />
    <title>Admin Dashboard</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <script src="../js/htmx.org@2.0.0.min.js"></script>
    <link rel="stylesheet" href="css/dashboard.css">
  </head>
  <body>
    <div class="app-layout">
      <!-- SIDEBAR (Admin Manager) -->
      <aside class="sidebar" id="sidebar">
        <div class="brand">
          <div class="avatar">
            <img
              src="img/1774245015_69c0d497743f0.jpeg"
              alt="User avatar"
              onerror="
                this.parentElement.innerHTML =
                  '<i class=\'fas fa-user\' style=\'font-size:1.5rem;color:#1a1a1a;\'></i>'
              "
            />
          </div>
          <div class="user-info">
            <span id="sidebarUserName">Admin Manager</span>
            <br /><small><?php echo $_SESSION['username']; ?></small>
          </div>
        </div>

        <!-- <div class="nav-section">Management</div> -->
        <nav class="nav-links">
          <a href="dashboard.php" data-page="dashboard" class="active-link"
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
          <a href="audit.php" data-page="audit"
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
          <!-- WELCOME CARD -->
          <div class="panel-card welcome-card">
            <div class="welcome-left">
              <h2>
                <i
                  class="fas fa-user-shield"
                  style="color: var(--admin-purple)"
                ></i>
                Overview · <span id="dashUserName">System</span>
              </h2>
            </div>
            <div class="welcome-center">
              <strong>Time:</strong> <span id="liveTime">--:--:--</span>
            </div>
            <div class="welcome-right">
              <strong>Day:</strong> <span id="liveDay">----</span>
            </div>
          </div>

          <!-- STATS CARDS -->
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-header">
                <span class="stat-icon"><i class="fas fa-users-cog"></i></span>
                <span class="stat-label">Total Admins</span>
              </div>
              <div class="stat-detail">
                <span class="detail-label">Active</span>
                <span class="detail-value" id="statActive"><?php echo $getTotalActiveAdmins['total']; ?></span>
              </div>
              <div class="stat-detail">
                <span class="detail-label">Inactive</span>
                <span class="detail-value" id="statInactive"><?php echo $getTotalInactiveAdmins['total']; ?></span>
              </div>
              <div class="stat-detail">
                <span class="detail-label">Total</span>
                <span class="detail-value" id="statTotalAdmins"><?php echo $getTotalAdmins['total']; ?></span>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="stat-icon"><i class="fas fa-user-tag"></i></span>
                <span class="stat-label">Roles</span>
              </div>
              <div class="stat-detail">
                <span class="detail-label">Operators</span>
                <span class="detail-value" id="statRoleCashier"><?php echo $getTotalActiveOperators['total']; ?></span>
              </div>
              <div class="stat-detail">
                <span class="detail-label">Accountants</span>
                <span class="detail-value" id="statRoleAccountant"><?php echo $getTotalAccountants['total']; ?></span>
              </div>
              <div class="stat-detail">
                <span class="detail-label">Admin Managers</span>
                <span class="detail-value" id="statRoleAdminManager"><?php echo $getTotalManagers['total']; ?></span>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="stat-icon"><i class="fas fa-clock"></i></span>
                <span class="stat-label">Recent Activity</span>
              </div>
              <div class="stat-detail">
                <span class="detail-label">Last 7 days</span>
                <span class="detail-value" id="statRecent7"><?php echo $getActivityIn7Days['COUNT(user_id)']; ?></span>
              </div>
              <div class="stat-detail">
                <span class="detail-label">Last 30 days</span>
                <span class="detail-value" id="statRecent30"><?php echo $getActivityIn30Days['COUNT(user_id)']; ?></span>
              </div>
              <div class="stat-detail">
                <span class="detail-label">Last login</span>
                <span class="detail-value" id="statLastLogin"><?php echo $getLastLogin['MAX(created_at)']; ?></span>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="stat-icon"><i class="fas fa-shield-alt"></i></span>
                <span class="stat-label">Security</span>
              </div>
              <div class="stat-detail">
                <span class="detail-label">2FA Enabled</span>
                <span class="detail-value" id="stat2fa">0</span>
              </div>
              <div class="stat-detail">
                <span class="detail-label">Pending invites</span>
                <span class="detail-value" id="statPending">0</span>
              </div>
              <div class="stat-detail">
                <span class="detail-label">Locked accounts</span>
                <span class="detail-value" id="statLocked">0</span>
              </div>
            </div>
          </div>

          <!-- FILTERS (from table columns) -->
          <div class="filters-container">
            <div class="filter-group">
              <label for="filterName">Name</label>
              <input type="text" id="filterName" placeholder="Search name..." />
            </div>

            <div class="filter-group">
              <label for="filterEmail">Email</label>
              <input
                type="text"
                id="filterEmail"
                placeholder="Search email..."
              />
            </div>
            <div class="filter-group">
              <label for="filterRole">Role</label>
              <select id="filterRole">
                <option value="all">All Roles</option>
                <option value="cashier">Cashier</option>
                <option value="accountant">Accountant</option>
                <option value="admin-manager">Admin Manager</option>
              </select>
            </div>
            <div class="filter-group">
              <label for="filterStatus">Status</label>
              <select id="filterStatus">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
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
                  <th>Name</th>
                  <th>Contact</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Last Login</th>
                  <th>2FA</th>
                </tr>
              </thead>
              <!-- Fetch list of users -->
              <tbody id=""><?php foreach ($getUsers as $user) 
                            { echo "<tr>
                                      <td>" . $user['id'] . "</td>
                                      <td>" . $user['username'] . "</td>
                                      <td>" . $user['contact'] . "</td>
                                      <td><span class='role-badge role-" . $user['role'] . "'>" . $user['role'] . "</span></td>
                                      <td><span class='status-badge status-" . $user['status'] . "'>" . $user['status'] . "</span></td>
                                      <td>" . $user['last_login'] . "</td>
                                      <td>" . ($user['has_2fa'] ? '<i class="fas fa-check-circle twoFA-true"></i>' : '<i class="fas fa-times-circle twoFA-false"></i>') . "</td>
                                    </tr>"; 
                              } 
                            ?>
              </tbody>
              <!-- <tbody id="tableBody"></tbody> -->
            </table>
            <div id="emptyState" class="empty-state" style="display: none">
              No admin accounts found
            </div>
          </div>

          <!-- PAGINATION -->
          <div class="pagination-container">
            <div class="rows-per-page">
              <label for="rowsPerPage">Rows per page:</label>
              <select id="rowsPerPage">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="20">20</option>
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
    <script src="js/dashboard.js"></script>
  </body>
</html>
