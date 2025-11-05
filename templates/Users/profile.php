<div id="profile-container" style="max-width: 800px; margin: 50px auto;">
  <div style="background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h2 style="margin-bottom: 30px;">Personal information</h2>
    
    <form id="profileForm">
      <div style="margin-bottom: 20px;">
        <label>Email</label>
        <input id="email" type="text" disabled
          style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; background: #f5f7fa;">
      </div>
      
      <div style="margin-bottom: 20px;">
        <label>Full Name</label>
        <input id="full_name" name="full_name" required
          style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
      </div>
      
      <div style="margin-bottom: 20px;">
        <label>Phone Number</label>
        <input id="phone" name="phone"
          style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
      </div>
      
      <div style="margin-bottom: 20px;">
        <label>Address</label>
        <textarea id="address" name="address"
          style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; min-height: 100px;"></textarea>
      </div>
      
      <button type="submit" class="btn btn-primary">Update</button>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const token = localStorage.getItem('authToken');
  console.log('Fetching profile with token:', token);
  if (!token) {
    alert('You need to login first!');
    window.location.href = '/users/login';
    return;
  }

  // --- Load profile data ---
  fetch('/users/profile', {
    method: 'GET',
    headers: {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
    }
  })
    .then(function (res) {
      if (res.status === 401) {
        alert('Session expired. Please login again.');
        localStorage.removeItem('authToken');
        window.location.href = '/users/login';
        throw new Error('Unauthorized');
      }
      return res.json();
    })
    .then(function (data) {
      console.log('Profile data:', data);
      const user = data.data;

      document.querySelector('#email').value = user.email || '';
      document.querySelector('#full_name').value = user.full_name || '';
      document.querySelector('#phone').value = user.phone || '';
      document.querySelector('#address').value = user.address || '';
    })
    .catch(function (err) {
      console.error('Error fetching profile:', err);
    });

  // --- Update profile handler ---
  document.querySelector('#profileForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const payload = {
      full_name: document.querySelector('#full_name').value,
      phone: document.querySelector('#phone').value,
      address: document.querySelector('#address').value
    };

    fetch('/users/profile', {
      method: 'PUT',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify(payload)
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        if (data.success) {
          alert('Profile updated successfully!');
        } else {
          alert('Update failed: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(function (err) {
        console.error('Error updating profile:', err);
      });
  });
});

</script>