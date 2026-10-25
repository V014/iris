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
    <title>Create user</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <link rel="stylesheet" href="css/create.css">
    <script src="../js/htmx.org@2.0.0.min.js"></script>
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
        <!-- Error messages from PHP render here -->
        <div id="dashboardFeedback" class="feedback" role="status" aria-live="polite"></div>

        <form id="createAdminForm" hx-post="controls/create.php" hx-target="#dashboardFeedback" hx-swap="innerHTML">
          <div class="form-grid">
            <!-- Username -->
            <div class="form-group">
              <label>Username <span class="required">*</span></label>
              <input
                type="text"
                id="username"
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
                <option value="System-admin">System Admin</option>
                <option value="Machine-operator">Machine Operator</option>
                <option value="Branch-manager">Branch Manager</option>
                <option value="Accountant">Accountant</option>
                <option value="Business-owner">Business Owner</option>
              </select>
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
              <div id="password-feedback">
                <div class="helper-text">
                  <span id="length-rule" class="length-rule">
                    ✕ Must be at least 8 characters
                  </span>
                </div>
              </div>
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
                <label for="twoFA-toggle">Two-Factor Authentication (2FA)</label>
                <label class="toggle-switch">
                  <input type="checkbox" id="twoFA-toggle" name="twoFA" value="true" />
                  <span class="toggle-slider"></span>
                </label>
                
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
            <button id="submit-btn" type="submit" class="btn-primary">
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
    
    <script src="../js/twoFA.js"></script>
    <script src="../js/password_limit.js"></script>
  </body>
</html>
