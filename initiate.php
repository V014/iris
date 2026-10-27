<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes"">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="css/setup.css" />
    <script src="js/htmx.org@2.0.0.min.js"></script>
    <title>Iris Initiate</title>
</head>
<body>
    <!-- ========== Initiate PAGE (standalone) ========== -->
    <div class="login-wrapper">
      <div class="login-panel panel-card">
        <div class="text-center mb-6">
          <!-- IMAGE REPLACES THE ICON -->
          <div class="brand-image-wrapper">
            <img class="brand-image" src="brand/logo-green.png" alt="Iris" onerror="this.style.display = 'none'"/>
          </div>
          <h1 style="font-size: 1.8rem; font-weight: 700; margin-top: 0.25rem">
            Iris Initiation
          </h1>
          <p style="opacity: 0.6; font-size: 0.95rem; margin-top: 0.25rem">
            Create your database
          </p>
        </div>

        <!-- Error messages from PHP render here -->
        <div id="initiateFeedback" class="feedback" role="status" aria-live="polite"></div>
        <!-- Progress bar -->
        <div class="progress-bar-container">
            <ul class="progress-bar">
                <li class="complete">Installation</li>
                <li class="active">Database</li>
                <li>Admin</li>
            </ul>
        </div>
        <form
          id="initiateForm"
          class="space-y-5"
          hx-post="controls/initiate.php"
          hx-target="#initiateFeedback"
          hx-swap="innerHTML"
        >
          <label for="terms">
            I agree to the <a href=terms.php>terms and conditions</a>
            <input type="checkbox" class="terms_checkbox" name="terms" id="terms" value="agree" required>
          </label>
          
          <button type="submit" class="btn-primary">
            <i class="fas fa-sign-in-alt"></i> Create Database
          </button>
        </form>

        <!-- <div class="demo-hint">demo: cashier1 / 1234</div> -->
      </div>
    </div>
    
</body>
</html>