<div style="max-width: 500px; margin: 50px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
  <h2 style="text-align: center; margin-bottom: 30px;">Login</h2>

  <form id="loginForm">
    <div style="margin-bottom: 20px;">
      <label>Email</label>
      <input type="email" name="email" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
    </div>

    <div style="margin-bottom: 20px;">
      <label>Password</label>
      <input type="password" name="password" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
    </div>

    <button type="submit" style="width: 100%; padding: 12px;">Login</button>
  </form>
</div>

<script>
document.querySelector('#loginForm').addEventListener('submit', (e) => {
  e.preventDefault();

  const formData = new FormData(e.target);
  const payload = Object.fromEntries(formData.entries());

  fetch('/users/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
    .then(res => res.json())
    .then(data => {
      console.log('Response data:', data);

      if (data.success && data.token) {
        localStorage.setItem('authToken', data.token);
        alert('Login successful!');
        window.location.href = '/products';
      } else {
        alert('Login failed: ' + (data.error || 'Invalid credentials'));
      }
    })
    .catch(err => {
      console.error('Fetch error:', err);
      alert('An error occurred during login.');
    });
});
</script>