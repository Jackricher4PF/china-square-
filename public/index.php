<?php
session_start();
require_once '../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>China Square | Modern Clothing Store</title>
  <link rel="stylesheet" href="css/index.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://js.paystack.co/v1/inline.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">

  <nav class="bg-red-700 text-white px-6 py-4 flex justify-between items-center shadow-lg">
    <a href="index.php" class="text-2xl font-black uppercase tracking-wider">China Square</a>
    <div class="flex items-center space-x-6">
      <input id="searchInput" oninput="searchProducts()" type="text" placeholder="Search clothes..." class="px-3 py-1.5 text-black rounded text-sm focus:outline-none">
      <button onclick="toggleCartModal()" class="font-semibold">
        🛒 Cart (<span id="cartCount">0</span>)
      </button>
      <div>
        <?php if (isset($_SESSION['name'])): ?>
          <span class="text-sm font-semibold">Hi, <?= htmlspecialchars($_SESSION['name']) ?></span>
          <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="admin.php" class="bg-yellow-400 text-black text-xs font-bold px-2 py-1 rounded ml-2">Admin Dashboard</a>
          <?php endif; ?>
          <a href="logout.php" class="text-xs bg-black px-2.5 py-1 rounded hover:bg-gray-800 ml-2">Logout</a>
        <?php else: ?>
          <a href="login.php" class="hover:underline font-semibold text-sm">Login</a>
          <a href="register.php" class="bg-black text-white text-xs px-3 py-1.5 rounded font-semibold ml-2">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <main class="container mx-auto p-6 flex-grow">
    <h2 class="text-2xl font-bold mb-6 border-b pb-2">Latest Clothing Collection</h2>
    <div id="imageLightbox" class="image-lightbox">
    <button class="close-lightbox"
            aria-label="Close image viewer">&amp;times;</button>
    <img id="lightboxImage" src="" alt="Full product image">
</div>
    <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"></div>
  </main>

  <div id="cartModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-end z-50">
    <div class="bg-white w-full max-w-md h-full p-6 flex flex-col justify-between shadow-2xl">
      <div>
        <div class="flex justify-between items-center border-b pb-3 mb-4">
          <h3 class="text-xl font-bold">Your Cart</h3>
          <button onclick="toggleCartModal()" class="text-gray-500 font-bold text-xl hover:text-black">✕</button>
        </div>
        <div id="cartItems" class="space-y-4 max-h-[60vh] overflow-y-auto"></div>
      </div>
      <div class="border-t pt-4">
        <div class="flex justify-between font-bold text-lg mb-4">
          <span>Total:</span>
          <span>$<span id="cartTotal">0.00</span></span>
        </div>
        <button onclick="checkoutWithPaystack('<?= PAYSTACK_PUBLIC_KEY ?>', '<?= $_SESSION['email'] ?? '' ?>')" class="w-full bg-red-600 text-white py-3 rounded-lg hover:bg-red-700 font-bold">Pay with Card / Paystack</button>
      </div>
    </div>
  </div>

  <footer class="bg-red-800 text-white text-center py-4 text-xs">
    © 2026 China Square Fashion Store.
  </footer>

  <script src="js/app.js"></script>
</body>
</html>
