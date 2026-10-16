<?php
  session_start();

  // Block access if the user is not logged in
  if (!isset($_SESSION['user_id'])) {
      header('Location: ../index.php');
      exit;
  }

  // Include the database connection
  require_once '../controls/connection.php';

  // Include the controls file
  require_once 'controls/edit-users.php';

  if (isset($_GET['id']) && !empty($_GET['id'])) {
    $user_id = (int) $_GET['id'];
    
    // Fetch the user from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If the user doesn't exist, stop execution
    if (!$user) {
        die("User not found.");
    }
  } else {
      die("No user ID specified.");
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
    <title>Edit user</title>
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
        <h2><i class="fas fa-user-pen"></i> Edit User Account</h2>
      </div>

      <!-- CREATE FORM -->
      <div class="form-card">
        <div class="form-title">
          <i class="fas fa-user-shield"></i> Edit User Account
        </div>
        <div class="form-subtitle">
          Edit the fields below to change user details. All fields
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
                value="<?php echo htmlspecialchars($user['username']); ?>"
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
                value="<?php echo htmlspecialchars($user['contact']); ?>"
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
                <option value="System-admin" <?php echo htmlspecialchars($user['role'] === 'System-admin') ? 'selected' : ''; ?>>System Admin</option>
                <option value="Machine-operator" <?php echo htmlspecialchars($user['role'] === 'Machine-operator') ? 'selected' : ''; ?>>Machine Operator</option>
                <option value="Branch-manager" <?php echo htmlspecialchars($user['role'] === 'Branch-manager') ? 'selected' : ''; ?>>Branch Manager</option>
                <option value="Accountant" <?php echo htmlspecialchars($user['role'] === 'Accountant') ? 'selected' : ''; ?>>Accountant</option>
                <option value="Business-owner" <?php echo htmlspecialchars($user['role'] === 'Business-owner') ? 'selected' : ''; ?>>Business Owner</option>
              </select>
            </div>

            <!-- Status -->
            <div class="form-group">
              <label>Status <span class="required">*</span></label>
              <select id="status" name="status" required>
                <option value="active" <?php echo htmlspecialchars($user['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo htmlspecialchars($user['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
              </select>
            </div>

            <!-- Password -->
            <div class="form-group">
              <label>Reset Password</label>
              <input
                type="password"
                id="password"
                name="password"
                value=""
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

            <!-- 2FA Toggle -->
            <div class="form-group">
              <label>Security Settings</label>
              <div class="toggle-group">
                <label class="toggle-switch">
                  <input type="checkbox" 
                          id="twoFA-toggle" 
                          name="twoFA" 
                          value="true"
                           <?php echo (!empty($user['2FA_secret'])) ? 'checked' : ''; ?> 
                  />
                  <span class="toggle-slider"></span>
                </label>
                <label for="twoFA-toggle">Enable Two-Factor Authentication (2FA)</label>
              </div>
              <div class="helper-text">
                Recommended for enhanced account security
              </div>
            </div>
          </div>

          <!-- Toast message -->
          <div id="formToast" class="toast-message"></div>

          <!-- Form Actions -->
          <div class="form-actions">
            <button id="submit-btn" type="submit" class="btn-primary">
              <i class="fas fa-check-circle"></i> Update Account
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
    
    <script src="js/twoFA.js"></script>
    <script src="../js/password_limit.js"></script>
  </body>
</html>
