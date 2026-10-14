<?php
  session_start();

  // Block access if the user is not logged in
  if (!isset($_SESSION['user_id'])) {
      header('Location: ../index.php');
      exit;
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes"
    />
    <title>Afrinance</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <link rel="stylesheet" href="css/create.css">
  </head>
  <body>
    <div class="main-content">
      <!-- BACK BUTTON -->
      <div class="back-bar">
        <a href="manage.php" class="back-btn"
          ><i class="fas fa-arrow-left"></i> Back</a
        >
      </div>

      <!-- WELCOME CARD (no time/day) -->
      <div class="welcome-card">
        <h2><i class="fas fa-user-plus"></i> Create User Account</h2>
      </div>

      <!-- CREATE FORM -->
      <div class="form-card">
        <div class="form-title">
          <i class="fas fa-user-shield"></i> New User Account
        </div>
        <div class="form-subtitle">
          Fill in the details below to create a new user account. All fields
          marked with <span style="color: var(--expenses)">*</span> are
          required.
        </div>

        <form id="createAdminForm" method="POST" action="controls/create.php">
          <div class="form-grid">
            <!-- Username -->
            <div class="form-group">
              <label>Username <span class="required">*</span></label>
              <input
                type="text"
                id="userame"
                name="username"
                placeholder="e.g. Jane Doe"
                required
              />
            </div>

            <!-- Contact -->
            <div class="form-group">
              <label>Contact <span class="required">*</span></label>
              <input
                type="tel"
                id="contact"
                placeholder="e.g. 0912345678"
                required
                name="contact"
                maxlength=10
                minlength=10
                pattern="^(\+265|0)(1|31|[89]\d)\d{6,7}$"
                title="Enter a valid Malawian phone number (e.g., 0912345678 or +265912345678)."
                oninput="this.value = this.value.replace(/[^0-9+]/g, '')"
              />
            </div>

            <!-- Role -->
            <div class="form-group">
              <label>Role <span class="required">*</span></label>
              <select id="role" name="role" required>
                <option value="">Select a role...</option>
                <option value="accountant">Operator</option>
                <option value="accountant">Accountant</option>
                <option value="admin-manager">Branch Manager</option>
                <option value="admin-manager">Business Owner</option>
              </select>
            </div>

            <!-- Branch (visible only for accountant) -->
            <div class="form-group branch-field" id="branchGroup">
              <label>Branch <span class="required">*</span></label>
              <select id="branch" name="branch">
                <option value="">Select a branch...</option>
                <option value="safalawo">Safalawo</option>
                <option value="zambia">Zambia</option>
                <option value="makata">Makata</option>
              </select>
              <div class="helper-text">Required for Accountant role</div>
            </div>

            <!-- Status -->
            <div class="form-group">
              <label>Status <span class="required">*</span></label>
              <select id="status" name="status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <!-- Password -->
            <div class="form-group">
              <label>Password <span class="required">*</span></label>
              <input
                type="password"
                id="password"
                name="password"
                placeholder="Min 8 characters"
                required
                minlength="8"
              />
              <div class="helper-text">Must be at least 8 characters</div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
              <label>Confirm Password <span class="required">*</span></label>
              <input
                type="password"
                id="confirmPassword"
                name="confirmPassword"
                placeholder="Re-enter password"
                required
              />
            </div>

            <!-- 2FA Toggle -->
            <div class="form-group">
              <label>Security Settings</label>
              <div class="toggle-group">
                <label class="toggle-switch">
                  <input type="checkbox" id="twoFA-toggle" name="twoFA" value="true" />
                  <span class="toggle-slider"></span>
                </label>
                <label for="twoFA-toggle">Enable Two-Factor Authentication (2FA)</label>
              </div>
              <div class="helper-text">
                Recommended for enhanced account security
              </div>
            </div>

            <!-- 2FA secret -->
            <div class="form-group">
              <label>Two-Factor secret(Optional)</label>
              <input
                type="password"
                id="twoFA-secret"
                name="twoFA"
                placeholder="Either pin or phrase"
                disabled
              />
              </input>
            </div>
          </div>

          <!-- Toast message -->
          <div id="formToast" class="toast-message"></div>

          <!-- Form Actions -->
          <div class="form-actions">
            <button type="submit" class="btn-primary">
              <i class="fas fa-check-circle"></i> Create Account
            </button>
            <!-- <a href="manage-admins.php" class="btn-secondary"
              ><i class="fas fa-times"></i> Cancel</a
            > -->
            <button type="reset" class="btn-danger">
              <i class="fas fa-undo"></i> Reset Form
            </button>
          </div>
        </form>
      </div>
    </div>
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <script src="js/twoFA.js"></script>
    <!-- <script src="js/create.js"></script> -->
  </body>
</html>
