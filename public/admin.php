<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | China Square</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <nav class="bg-red-800 text-white px-6 py-4 flex justify-between items-center shadow">
    <h1 class="text-xl font-bold">China Square — Admin Inventory</h1>
    <a href="index.php" class="text-sm underline hover:text-gray-300">← Back to Storefront</a>
  </nav>

  <main class="container mx-auto p-6 max-w-5xl">
    <div class="bg-white p-6 rounded-lg shadow-sm border mb-8">
      <h2 id="formTitle" class="text-lg font-bold mb-4">Add Clothing Item</h2>
      
      <form id="productForm" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="hidden" id="productId">
        <div>
          <label class="block text-xs font-bold mb-1">Title</label>
          <input type="text" id="title" required class="w-full border p-2 rounded text-sm">
        </div>
        <div>
          <label class="block text-xs font-bold mb-1">Category</label>
          <input type="text" id="category" placeholder="e.g. Shoes, Jackets, Shirts" required class="w-full border p-2 rounded text-sm">
        </div>
        <div>
          <label class="block text-xs font-bold mb-1">Price ($)</label>
          <input type="number" step="0.01" id="price" required class="w-full border p-2 rounded text-sm">
        </div>
        <div>
          <label class="block text-xs font-bold mb-1">Select Image File</label>
          <input type="file" id="imageFile" accept="image/*" class="w-full border p-1 rounded text-sm bg-gray-50">
        </div>

        <!-- Available Sizes Checkboxes -->
        <div class="md:col-span-2">
          <label class="block text-xs font-bold mb-2">Available Sizes</label>
          <div class="flex flex-wrap gap-4 text-xs font-semibold">
            <label class="flex items-center space-x-1 cursor-pointer">
              <input type="checkbox" name="sizeOpt" value="XS" class="rounded text-red-600 focus:ring-red-500">
              <span>XS</span>
            </label>
            <label class="flex items-center space-x-1 cursor-pointer">
              <input type="checkbox" name="sizeOpt" value="S" checked class="rounded text-red-600 focus:ring-red-500">
              <span>S</span>
            </label>
            <label class="flex items-center space-x-1 cursor-pointer">
              <input type="checkbox" name="sizeOpt" value="M" checked class="rounded text-red-600 focus:ring-red-500">
              <span>M</span>
            </label>
            <label class="flex items-center space-x-1 cursor-pointer">
              <input type="checkbox" name="sizeOpt" value="L" checked class="rounded text-red-600 focus:ring-red-500">
              <span>L</span>
            </label>
            <label class="flex items-center space-x-1 cursor-pointer">
              <input type="checkbox" name="sizeOpt" value="XL" checked class="rounded text-red-600 focus:ring-red-500">
              <span>XL</span>
            </label>
            <label class="flex items-center space-x-1 cursor-pointer">
              <input type="checkbox" name="sizeOpt" value="2XL" class="rounded text-red-600 focus:ring-red-500">
              <span>2XL</span>
            </label>
            <label class="flex items-center space-x-1 cursor-pointer">
              <input type="checkbox" name="sizeOpt" value="3XL" class="rounded text-red-600 focus:ring-red-500">
              <span>3XL</span>
            </label>
          </div>
        </div>

        <div class="md:col-span-2">
          <label class="block text-xs font-bold mb-1">Description</label>
          <textarea id="description" rows="3" required class="w-full border p-2 rounded text-sm"></textarea>
        </div>

        <div class="md:col-span-2 flex justify-end space-x-2">
          <button type="button" id="cancelBtn" onclick="resetForm()" class="bg-gray-400 text-white px-4 py-2 rounded text-sm hidden">Cancel</button>
          <button type="submit" id="saveBtn" class="bg-red-700 text-white px-6 py-2 rounded text-sm font-bold hover:bg-red-800">Save Item</button>
        </div>
      </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <table class="w-full text-left text-sm">
        <thead class="bg-gray-100 border-b">
          <tr>
            <th class="p-3">Image</th>
            <th class="p-3">Title</th>
            <th class="p-3">Category</th>
            <th class="p-3">Sizes</th>
            <th class="p-3">Price</th>
            <th class="p-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="adminTable"></tbody>
      </table>
    </div>
  </main>

  <script src="js/admin.js"></script>
</body>
</html>