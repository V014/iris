(function () {
"use strict";

// ----- TOGGLE BRANCH FIELD BASED ON ROLE -----
const roleSelect = document.getElementById("role");
const branchGroup = document.getElementById("branchGroup");
const branchSelect = document.getElementById("branch");

function toggleBranchField() {
    const selectedRole = roleSelect.value;
    if (selectedRole === "accountant") {
    branchGroup.classList.add("visible");
    branchSelect.setAttribute("required", "required");
    } else {
    branchGroup.classList.remove("visible");
    branchSelect.removeAttribute("required");
    branchSelect.value = "";
    }
}

roleSelect.addEventListener("change", toggleBranchField);

// ----- FORM SUBMISSION -----
document
    .getElementById("createAdminForm")
    .addEventListener("submit", function (e) {
    e.preventDefault();
    const toast = document.getElementById("formToast");

    const fullName = document.getElementById("fullName").value.trim();
    const email = document.getElementById("email").value.trim();
    const role = document.getElementById("role").value;
    const branch = document.getElementById("branch").value;
    const status = document.getElementById("status").value;
    const password = document.getElementById("password").value;
    const confirmPassword =
        document.getElementById("confirmPassword").value;
    const twoFA = document.getElementById("twoFA").checked;

    if (!fullName || !email || !role || !password) {
        toast.className = "toast-message error";
        toast.textContent = "Please fill in all required fields.";
        return;
    }

    if (role === "accountant" && !branch) {
        toast.className = "toast-message error";
        toast.textContent =
        "Please select a branch for the accountant role.";
        return;
    }

    if (password !== confirmPassword) {
        toast.className = "toast-message error";
        toast.textContent = "Passwords do not match.";
        return;
    }

    if (password.length < 8) {
        toast.className = "toast-message error";
        toast.textContent = "Password must be at least 8 characters.";
        return;
    }

    const roleLabel = role === "accountant" ? `accountant (${branch})` : role;
    toast.className = "toast-message success";
    toast.textContent = `✅ Account for "${fullName}" (${roleLabel}) created successfully!`;

    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Creating...';

    setTimeout(() => {
        this.reset();
        branchGroup.classList.remove("visible");
        branchSelect.removeAttribute("required");
        submitBtn.disabled = false;
        submitBtn.innerHTML =
        '<i class="fas fa-check-circle"></i> Create Account';

        setTimeout(() => {
        toast.className = "toast-message";
        toast.textContent = "";
        }, 5000);
    }, 1500);
    });
})();