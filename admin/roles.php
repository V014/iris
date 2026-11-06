<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes"
    />
    <title>Manage roles</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <link rel="stylesheet" href="css/roles.css">
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
            <span>System Administrator</span>
            <br /><small>System Admin</small>
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
          <a href="roles.php" data-page="roles" class="active-link"
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
          <a href="login.php" data-page="logout"
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
                  class="fas fa-user-tag"
                  style="color: var(--admin-purple)"
                ></i>
                Manage Roles
              </h2>
            </div>
          </div>

          <!-- ACTION BAR -->
          <div class="action-bar">
            <a href="create-role.php" class="action-btn primary-btn">
              <i class="fas fa-plus-circle"></i> Create New Role
            </a>
          </div>

          <!-- STATS MINI -->
          <div class="stats-mini">
            <div class="stat-mini">
              <div class="stat-number" id="miniTotal">0</div>
              <div class="stat-label">Total Roles</div>
            </div>
            <div class="stat-mini">
              <div class="stat-number" id="miniActive">0</div>
              <div class="stat-label">Active</div>
            </div>
            <div class="stat-mini">
              <div class="stat-number" id="miniUsers">0</div>
              <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-mini">
              <div class="stat-number" id="miniPermissions">0</div>
              <div class="stat-label">Total Permissions</div>
            </div>
          </div>

          <!-- FILTERS -->
          <div class="filters-container">
            <div class="filter-group">
              <label for="filterName">Role Name</label>
              <input type="text" id="filterName" placeholder="Search role..." />
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
                  <th>Role Name</th>
                  <th>Description</th>
                  <th>Users</th>
                  <th>Status</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="tableBody"></tbody>
            </table>
            <div id="emptyState" class="empty-state" style="display: none">
              No roles found
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
          Are you sure you want to delete the role
          <strong id="deleteRoleName">Role</strong>? <br />This action cannot be
          undone and will affect all users with this role.
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

    <script src="js/roles.js"></script>
  </body>
</html>
