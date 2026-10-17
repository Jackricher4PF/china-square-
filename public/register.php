<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register | China Square</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white p-8 rounded-lg shadow-md w-96">
    <h2 class="text-2xl font-bold mb-6 text-center text-red-700">Create Account</h2>
    <form id="regForm" class="space-y-4">
      <div>
        <label class="block text-xs font-bold mb-1">Full Name</label>
        <input type="text" id="name" required class="w-full border p-2 rounded text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold mb-1">Email</label>
        <input type="email" id="email" required class="w-full border p-2 rounded text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold mb-1">Password</label>
        <input type="password" id="password" required class="w-full border p-2 rounded text-sm">
      </div>
      <button type="submit" class="w-full bg-red-700 text-white py-2 rounded font-bold hover:bg-red-800">Register</button>
    </form>
    <p class="text-xs text-center mt-4">Already registered? <a href="login.php" class="text-red-700 underline">Login</a></p>
  </div>
  <script>
    document.getElementById('regForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const res = await fetch('../api/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: document.getElementById('name').value,
          email: document.getElementById('email').value,
          password: document.getElementById('password').value
        })
      });
      const data = await res.json();
      alert(data.message);
      if (data.success) window.location.href = 'login.php';
    });
  </script>
</body>
</html>