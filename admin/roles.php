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
            <span id="sidebarUserName">System Administrator</span>
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

    <script>
      (function () {
        "use strict";

        // ----- SAMPLE DATA (Roles) -----
        let roles = [
          {
            id: 1,
            name: "Cashier",
            description: "Can process sales and handle customer transactions",
            users: 3,
            status: "active",
            created: "2026-01-15",
          },
          {
            id: 2,
            name: "Accountant",
            description: "Can manage financial records and generate reports",
            users: 2,
            status: "active",
            created: "2026-01-15",
          },
          {
            id: 3,
            name: "Admin Manager",
            description: "Full system access with user and role management",
            users: 4,
            status: "active",
            created: "2026-01-15",
          },
          {
            id: 4,
            name: "Viewer",
            description: "Read-only access to system data and reports",
            users: 1,
            status: "inactive",
            created: "2026-02-20",
          },
          {
            id: 5,
            name: "Inventory Manager",
            description: "Can manage stock levels and inventory reports",
            users: 0,
            status: "active",
            created: "2026-03-10",
          },
          {
            id: 6,
            name: "Branch Manager",
            description: "Oversee branch operations and staff",
            users: 2,
            status: "active",
            created: "2026-03-15",
          },
          {
            id: 7,
            name: "Support Agent",
            description: "Handle customer support tickets and inquiries",
            users: 0,
            status: "inactive",
            created: "2026-04-01",
          },
        ];

        // ----- STATE -----
        let currentPage = 1;
        let rowsPerPage = 5;
        let filteredData = [];
        let deleteTargetId = null;

        // ----- UPDATE MINI STATS -----
        function updateMiniStats() {
          const totalUsers = roles.reduce((sum, r) => sum + r.users, 0);
          const totalPermissions = roles.length * 5; // Example: 5 permissions per role

          document.getElementById("miniTotal").textContent = roles.length;
          document.getElementById("miniActive").textContent = roles.filter(
            (r) => r.status === "active",
          ).length;
          document.getElementById("miniUsers").textContent = totalUsers;
          document.getElementById("miniPermissions").textContent =
            totalPermissions;
        }

        // ----- RENDER TABLE -----
        function renderTable() {
          const nameFilter = document
            .getElementById("filterName")
            .value.toLowerCase()
            .trim();
          const statusFilter = document.getElementById("filterStatus").value;

          let filtered = roles.filter((r) => {
            const matchName = r.name.toLowerCase().includes(nameFilter);
            const matchStatus =
              statusFilter === "all" || r.status === statusFilter;
            return matchName && matchStatus;
          });

          filtered = [...filtered].sort((a, b) => a.name.localeCompare(b.name));
          filteredData = filtered;

          const tbody = document.getElementById("tableBody");
          const empty = document.getElementById("emptyState");

          if (filteredData.length === 0) {
            tbody.innerHTML = "";
            empty.style.display = "block";
            document.getElementById("prevPage").disabled = true;
            document.getElementById("nextPage").disabled = true;
            document.getElementById("pageInfo").textContent = "Page 0 of 0";
            return;
          }
          empty.style.display = "none";

          const totalPages =
            rowsPerPage === "all"
              ? 1
              : Math.ceil(filteredData.length / rowsPerPage);
          if (currentPage > totalPages) currentPage = totalPages;
          if (currentPage < 1) currentPage = 1;

          const startIndex =
            rowsPerPage === "all" ? 0 : (currentPage - 1) * rowsPerPage;
          const endIndex =
            rowsPerPage === "all"
              ? filteredData.length
              : Math.min(startIndex + rowsPerPage, filteredData.length);
          const pageData = filteredData.slice(startIndex, endIndex);

          document.getElementById("prevPage").disabled =
            currentPage === 1 || totalPages === 0;
          document.getElementById("nextPage").disabled =
            currentPage === totalPages || totalPages === 0;
          document.getElementById("pageInfo").textContent =
            `Page ${currentPage} of ${totalPages}`;

          const roleClasses = {
            Cashier: "role-cashier",
            Accountant: "role-accountant",
            "Admin Manager": "role-admin-manager",
          };
          const statusLabels = { active: "Active", inactive: "Inactive" };
          const statusClasses = {
            active: "status-active",
            inactive: "status-inactive",
          };

          let html = "";
          pageData.forEach((r, idx) => {
            const rowNum = startIndex + idx + 1;
            const roleClass = roleClasses[r.name] || "";
            html += `<tr data-id="${r.id}">
          <td style="text-align:center;opacity:0.5;">${rowNum}</td>
          <td><span class="role-badge ${roleClass}"><strong>${r.name}</strong></span></td>
          <td style="max-width:250px; opacity:0.8;">${r.description}</td>
          <td style="text-align:center;">${r.users}</td>
          <td><span class="status-badge ${statusClasses[r.status]}">${statusLabels[r.status]}</span></td>
          <td style="opacity:0.7; font-size:0.85rem;">${r.created}</td>
          <td class="action-icons">
            <a href="edit-role.html?id=${r.id}" class="edit" data-id="${r.id}" title="Edit"><i class="fas fa-edit"></i></a>
            <a href="#" class="delete" data-id="${r.id}" title="Delete"><i class="fas fa-trash-alt"></i></a>
          </td>
        </tr>`;
          });
          tbody.innerHTML = html;
        }

        // ----- PAGINATION -----
        function goToPage(page) {
          const totalPages =
            rowsPerPage === "all"
              ? 1
              : Math.ceil(filteredData.length / rowsPerPage);
          if (page < 1 || page > totalPages || totalPages === 0) return;
          currentPage = page;
          renderTable();
        }
        function prevPage() {
          if (currentPage > 1) goToPage(currentPage - 1);
        }
        function nextPage() {
          const totalPages =
            rowsPerPage === "all"
              ? 1
              : Math.ceil(filteredData.length / rowsPerPage);
          if (currentPage < totalPages) goToPage(currentPage + 1);
        }

        // ----- DELETE MODAL LOGIC -----
        const modal = document.getElementById("deleteModal");
        const deletePasswordInput = document.getElementById("deletePassword");
        const deleteError = document.getElementById("deleteError");
        const deleteRoleName = document.getElementById("deleteRoleName");

        function openDeleteModal(roleId) {
          const role = roles.find((r) => r.id === roleId);
          if (!role) return;
          deleteTargetId = roleId;
          deleteRoleName.textContent = role.name;
          deletePasswordInput.value = "";
          deleteError.classList.remove("visible");
          modal.classList.add("active");
          deletePasswordInput.focus();
        }

        function closeDeleteModal() {
          modal.classList.remove("active");
          deleteTargetId = null;
          deletePasswordInput.value = "";
          deleteError.classList.remove("visible");
        }

        function confirmDelete() {
          const password = deletePasswordInput.value.trim();
          if (password === "admin123") {
            if (deleteTargetId !== null) {
              const index = roles.findIndex((r) => r.id === deleteTargetId);
              if (index > -1) {
                roles.splice(index, 1);
                updateMiniStats();
                currentPage = 1;
                renderTable();
                closeDeleteModal();
                // Show success feedback
                const toast = document.createElement("div");
                toast.style.cssText = `
                  position: fixed; bottom: 20px; right: 20px;
                  background: var(--accent-color); color: #1a1a1a;
                  padding: 0.8rem 1.5rem; border-radius: var(--border-radius);
                  font-weight: 600; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                  z-index: 2000; animation: slideUp 0.3s ease;
                `;
                toast.textContent = "✅ Role deleted successfully!";
                document.body.appendChild(toast);
                setTimeout(() => {
                  toast.style.opacity = "0";
                  toast.style.transition = "opacity 0.3s";
                  setTimeout(() => toast.remove(), 300);
                }, 3000);
              }
            }
          } else {
            deleteError.classList.add("visible");
            deletePasswordInput.value = "";
            deletePasswordInput.focus();
          }
        }

        // Modal event listeners
        document
          .getElementById("cancelDeleteBtn")
          .addEventListener("click", closeDeleteModal);
        document
          .getElementById("confirmDeleteBtn")
          .addEventListener("click", confirmDelete);
        deletePasswordInput.addEventListener("keydown", function (e) {
          if (e.key === "Enter") {
            e.preventDefault();
            confirmDelete();
          }
          if (e.key === "Escape") {
            closeDeleteModal();
          }
        });
        modal.addEventListener("click", function (e) {
          if (e.target === modal) closeDeleteModal();
        });

        // ----- DELETE BUTTON (modal trigger) -----
        document.addEventListener("click", function (e) {
          const target = e.target.closest("a");
          if (!target) return;

          if (target.classList.contains("delete")) {
            e.preventDefault();
            const id = parseInt(target.dataset.id);
            if (id) {
              openDeleteModal(id);
            }
          }
        });

        // ----- EVENT LISTENERS -----
        document
          .getElementById("filterName")
          .addEventListener("input", function () {
            currentPage = 1;
            renderTable();
          });
        document
          .getElementById("filterStatus")
          .addEventListener("change", function () {
            currentPage = 1;
            renderTable();
          });

        document
          .getElementById("clearFilters")
          .addEventListener("click", function () {
            document.getElementById("filterName").value = "";
            document.getElementById("filterStatus").value = "all";
            currentPage = 1;
            renderTable();
          });

        document
          .getElementById("rowsPerPage")
          .addEventListener("change", function () {
            rowsPerPage = this.value === "all" ? "all" : parseInt(this.value);
            currentPage = 1;
            renderTable();
          });
        document.getElementById("prevPage").addEventListener("click", prevPage);
        document.getElementById("nextPage").addEventListener("click", nextPage);

        // ----- INIT -----
        updateMiniStats();
        renderTable();
        document.getElementById("sidebarUserName").textContent =
          "Admin Manager";

        // Add keyframe animation for toast
        const style = document.createElement("style");
        style.textContent = `
          @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
          }
        `;
        document.head.appendChild(style);
      })();
    </script>
  </body>
</html>
