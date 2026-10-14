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

// Include the dashboard controls
require_once 'controls/branches.php';
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes"
    />
    <title>Afrinance Branches</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <script src="../js/htmx.org@2.0.0.min.js"></script>
    <link rel="stylesheet" href="css/manage.css">
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
          <a href="dashboard.php" data-page="dashboard"
            ><i class="fas fa-th-large"></i> Dashboard</a
          >
          <a href="manage.php" data-page="admins""
            ><i class="fas fa-user-cog"></i> Manage Accounts</a
          >
          <a href="branches.php" data-page="branches" class="active-link"
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
          <!-- BACK BUTTON -->
          <!-- <div class="back-bar">
            <a href="dashboard2.php" class="back-btn"
              ><i class="fas fa-arrow-left"></i> Back to Dashboard</a
            >
          </div> -->

          <!-- WELCOME CARD (no time/date) -->
          <div class="panel-card welcome-card">
            <div class="welcome-left">
              <h2>
                <i
                  class="fas fa-shop"
                  style="color: var(--admin-purple)"
                ></i>
                Manage Branches
              </h2>
            </div>
          </div>

          <!-- ACTION BAR (Create only) -->
          <div class="action-bar">
            <a href="create.php" class="action-btn primary-btn">
              <i class="fas fa-user-plus"></i> Create New Branch
            </a>
          </div>

          <!-- STATS MINI -->
          <div class="stats-mini">
            <div class="stat-mini">
              <div class="stat-number"><?php echo $getTotalBranches['total']; ?></div>
              <div class="stat-label">Total Branches</div>
            </div>
            <div class="stat-mini">
              <div class="stat-number"><?php echo $getTotalActiveBranches['total']; ?></div>
              <div class="stat-label">Active Branches</div>
            </div>
            <div class="stat-mini">
              <div class="stat-number"><?php echo $getTotalInactiveBranches['total']; ?></div>
              <div class="stat-label">Inactive Branches</div>
            </div>
            <div class="stat-mini">
              <div class="stat-number">80%</div>
              <div class="stat-label">Up time</div>
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
                  <th>Description</th>
                  <th>Location</th>
                  <th>Status</th>
                  <th>Created at</th>
                  <th>Updated at</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <!-- Fetch list of users -->
              <tbody id=""><?php foreach ($getBranches as $branch) 
                            { echo "<tr>
                                      <td>" . $branch['id'] . "</td>
                                      <td>" . $branch['name'] . "</td>
                                      <td>" . $branch['description'] . "</td>
                                      <td>
                                        <span class='role-badge'>
                                            <a href='https://www.google.com/maps/search/?api=1&query=" . $branch['location'] . "' 
                                            target='_blank'
                                            style='text-decoration:none; color:var(--admin-purple)'>View on Map
                                            </a>
                                        </span>
                                      </td>
                                      <td><span class='status-badge status-" . $branch['status'] . "'>" . $branch['status'] . "</span></td>
                                      <td>" . $branch['created_at'] . "</td>
                                      <td>" . $branch['updated_at'] . "</td>
                                      <td class='action-icons'>
                                        <a href='" . $branch['id'] . "' class='edit' data-id='" . $branch['id'] . "' title='Edit'>
                                          <i class='fas fa-edit'></i>
                                        </a>
                                        <a href='" . $branch['id'] . "' class='delete' data-id='" . $branch['id'] . "' title='Delete'>
                                          <i class='fas fa-trash-alt'></i>
                                        </a>
                                      </td>
                                    </tr>"; 
                              } 
                            ?>
              </tbody>
              <!-- <tbody id="tableBody"></tbody> -->
            </table>
            <div id="emptyState" class="empty-state" style="display: none">
              No branches found
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

    <!-- DELETE MODAL -->
    <div class="modal-overlay" id="deleteModal">
      <div class="modal-box">
        <div class="modal-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3>Confirm Deletion</h3>
        <p class="modal-sub">
          Are you sure you want to delete
          <strong id="deleteUserName"><?php echo $branch['name']; ?></strong>? <br />This action cannot be
          undone.
        </p>
        <div class="form-group">
          <label for="deletePassword">Enter your password to confirm</label>
          <input
            type="password"
            id="deletePassword"
            placeholder="Enter your password..."
          />
          <div class="modal-error" id="deleteError">
            Incorrect password. Please try again.
          </div>
        </div>
        <div class="modal-actions">
          <button class="btn-cancel-delete" id="cancelDeleteBtn">Cancel</button>
          <button class="btn-confirm-delete" id="confirmDeleteBtn">
            <i class="fas fa-trash-alt"></i> Delete
          </button>
        </div>
      </div>
    </div>
  </body>
  <script src="js/manage.js"></script>
</html>