const toggle = document.getElementById('twoFA-toggle');
const secret = document.getElementById('twoFA-secret');

function syncSecret() {
  secret.disabled = !toggle.checked;
  if (!toggle.checked) secret.value = ''; // optional: clear when off
}

toggle.addEventListener('change', syncSecret);
syncSecret(); // run on load so state matches