<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes"">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="js/htmx.org@2.0.0.min.js"></script>
    <title>Iris</title>
</head>
<body>
    <!-- ========== LOGIN PAGE (standalone) ========== -->
    <div class="login-wrapper">
      <div class="login-panel panel-card">
        <div class="text-center mb-6">
          <!-- IMAGE REPLACES THE ICON -->
          <div class="brand-image-wrapper">
            <img class="brand-image" src="brand/logo-green.png" alt="Iris logo" onerror="this.style.display = 'none'"/>
          </div>
          <h1 style="font-size: 1.8rem; font-weight: 700; margin-top: 0.25rem">
            Iris login
          </h1>
          <p style="opacity: 0.6; font-size: 0.95rem; margin-top: 0.25rem">
            Enter your credentials
          </p>
        </div>

        <div id="login-feedback" class="feedback" role="status" aria-live="polite"></div>

        <form
          id="loginForm"
          class="space-y-5"
          hx-post="controls/login.php"
          hx-target="#login-feedback"
          hx-swap="innerHTML"
        >
          <div>
            <!-- <label for="loginUsername">Username</label> -->
            <input
              id="loginUsername"
              type="text"
              placeholder="Enter username"
              name="username"
              required
            />
          </div>
          <div>
            <!-- <label for="loginPassword">Password</label> -->
            <input
              id="loginPassword"
              type="password"
              placeholder="Enter password"
              name="password"
              required
            />
          </div>
          <button type="submit" class="btn-primary">
            <i class="fas fa-sign-in-alt"></i> Login
          </button>
        </form>

        <!-- <div class="demo-hint">demo: cashier1 / 1234</div> -->
      </div>
    </div>
    
</body>
</html>