<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | China Square</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white p-8 rounded-lg shadow-md w-96">
    <h2 class="text-2xl font-bold mb-6 text-center text-red-700">China Square Login</h2>
    <form id="loginForm" class="space-y-4">
      <div>
        <label class="block text-xs font-bold mb-1">Email</label>
        <input type="email" id="email" required class="w-full border p-2 rounded text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold mb-1">Password</label>
        <input type="password" id="password" required class="w-full border p-2 rounded text-sm">
      </div>
      <button type="submit" class="w-full bg-red-700 text-white py-2 rounded font-bold hover:bg-red-800">Login</button>
    </form>
    <p class="text-xs text-center mt-4">Don't have an account? <a href="register.php" class="text-red-700 underline">Register</a></p>
  </div>
  <script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const res = await fetch('../api/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          email: document.getElementById('email').value,
          password: document.getElementById('password').value
        })
      });
      const data = await res.json();
      if (data.success) {
        window.location.href = data.user.role === 'admin' ? 'admin.php' : 'index.php';
      } else {
        alert(data.message);
      }
    });
  </script>
</body>
</html>