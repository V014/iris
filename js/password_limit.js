const passwordInput = document.getElementById('password');
  const lengthRule = document.getElementById('length-rule');
  const submitBtn = document.getElementById('submit-btn');

  passwordInput.addEventListener('input', () => {
    const value = passwordInput.value;
    const isLengthValid = value.length >= 8;

    if (isLengthValid) {
      lengthRule.textContent = '✓ Minimum 8 characters';
      lengthRule.style.color = '#3ddc84';
      submitBtn.disabled = false;
    } else {
      lengthRule.textContent = '✕ Must be at least 8 characters';
      lengthRule.style.color = '#e74c3c';
      submitBtn.disabled = true;
    }
  });