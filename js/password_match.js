// checks to see if the password and confirm password fields match and warns the user if they do not match
document.addEventListener('DOMContentLoaded', function() {
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirmPassword');
  const submitButton = document.querySelector('button[type="submit"]');
  const lengthRule = document.getElementById('length-confirm-rule');

    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (password === confirmPassword) {
            lengthRule.textContent = "✓ Passwords match";
            lengthRule.style.color = '#3ddc84';
            // submitButton.disabled = false;
        } else {
            lengthRule.textContent = "✕ Passwords do not match";
            lengthRule.style.color = '#E74C3C';
            // submitButton.disabled = true;
        }
    }

    passwordInput.addEventListener('input', checkPasswordMatch);
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
});