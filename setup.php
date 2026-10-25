<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes"">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="css/setup.css" />
    <script src="js/htmx.org@2.0.0.min.js"></script>
    <title>Iris Setup</title>
</head>
<body>
    <!-- ========== SETUP PAGE (standalone) ========== -->
    <div class="login-wrapper">
      <div class="login-panel panel-card">
        <div class="text-center mb-6">
          <!-- IMAGE REPLACES THE ICON -->
          <div class="brand-image-wrapper">
            <img class="brand-image" src="brand/logo-green.png" alt="Iris" onerror="this.style.display = 'none'"/>
          </div>
          <h1 style="font-size: 1.8rem; font-weight: 700; margin-top: 0.25rem">
            Iris Setup
          </h1>
          <p style="opacity: 0.6; font-size: 0.95rem; margin-top: 0.25rem">
            Create your first user
          </p>
        </div>

        <!-- Error messages from PHP render here -->
        <div id="setup-feedback" class="feedback" role="status" aria-live="polite"></div>
        <!-- Progress bar -->
        <div class="progress-bar-container">
            <ul class="progress-bar">
                <li class="complete">Installation</li>
                <li class="complete">Database</li>
                <li class="active">Admin</li>
            </ul>
        </div>
        <!-- Status message / checklist -->
        <div id="password-feedback" class="password-feedback">
          <span id="length-rule" class="length-rule">✕ Minimum 8 character password</span>
        </div>
        <form
          id="setupForm"
          class="space-y-5"
          hx-post="controls/setup.php"
          hx-target="#setup-feedback"
          hx-swap="innerHTML"
        >
          <div>
            <!-- <label for="loginUsername">Username</label> -->
            <input
              id="loginUsername"
              type="text"
              placeholder="Enter username"
              required
              name="username"
            />
          </div>
          <div>
            <!-- <label for="loginPassword">Password</label> -->
            <input
              id="password"
              type="password"
              placeholder="Enter password"
              required
              name="password"
              minlength=8
              title="You must have at least 8 characters"
            />
          </div>
          <div>
            <!-- <label for="loginPassword">Confirm Password</label> -->
            <input
              id="loginConfirmPassword"
              type="password"
              placeholder="Confirm password"
              required
              name="confirm_password"
              minlength=8
              title="You must have at least 8 characters"
            />
          </div>
          <div>
            <input
              id="contact"
              type="tel"
              placeholder="Phone number"
              required
              name="contact"
              maxlength=10
              minlength=10
              pattern="^(\+265|0)(1|31|[89]\d)\d{6,7}$"
              title="Enter a valid Malawian phone number (e.g., 0912345678 or +265912345678)."
              oninput="this.value = this.value.replace(/[^0-9+]/g, '')"
            />
          </div>
          <div>
            <!-- <label for="2FA">2FA</label> -->
            <div class="toggle-group">
              <label for="twoFA-toggle">Enable Two-Factor Authentication (2FA)</label>
              <label class="toggle-switch">
                  <input type="checkbox" id="twoFA-toggle" name="twoFA" value="true" />
                  <span class="toggle-slider"></span>
              </label>
            </div>
            <input
              type="password"
              id="twoFA-secret"
              name="twoFA_secret"
              placeholder="Either pin or phrase"
              disabled
            />
          </div>
          <button type="submit" class="btn-primary">
            <i class="fas fa-sign-in-alt"></i> Create User
          </button>
        </form>

        <!-- <div class="demo-hint">demo: cashier1 / 1234</div> -->
      </div>
    </div>
    
</body>
<script src="js/twoFA.js"></script>
<script src="js/password_limit.js"></script>
</html>