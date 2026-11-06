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