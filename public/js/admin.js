document.addEventListener('DOMContentLoaded', () => {
  loadProducts();

  const form = document.getElementById('productForm');
  if (form) {
    form.addEventListener('submit', saveProduct);
  }
});

async function loadProducts() {
  try {
    const res = await fetch('../api/products.php');
    const products = await res.json();
    const table = document.getElementById('adminTable');

    if (!Array.isArray(products)) {
      console.error("Products response is not an array:", products);
      return;
    }

    table.innerHTML = products.map(p => `
      <tr class="border-b hover:bg-gray-50">
        <td class="p-3"><img src="${p.image_url}" class="w-12 h-12 object-cover rounded"></td>
        <td class="p-3 font-semibold">${escapeHtml(p.title)}</td>
        <td class="p-3 text-gray-500">${escapeHtml(p.category)}</td>
        <td class="p-3 text-xs font-mono bg-gray-50 rounded">${escapeHtml(p.sizes || 'N/A')}</td>
        <td class="p-3">$${parseFloat(p.price).toFixed(2)}</td>
        <td class="p-3 text-right space-x-2">
          <button type="button" onclick="editProduct(${p.id}, \`${escapeHtml(p.title)}\`, \`${escapeHtml(p.category)}\`, ${p.price}, \`${escapeHtml(p.sizes || '')}\`, \`${escapeHtml(p.description)}\`)" class="text-blue-600 font-bold hover:underline">Edit</button>
          <button type="button" onclick="deleteProduct(${p.id})" class="text-red-600 font-bold hover:underline">Delete</button>
        </td>
      </tr>
    `).join('');
  } catch (err) {
    console.error("Failed to load products:", err);
  }
}

async function saveProduct(e) {
  e.preventDefault();
  
  const saveBtn = document.getElementById('saveBtn');
  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.innerText = "Saving...";
  }

  const id = document.getElementById('productId').value;
  const fileInput = document.getElementById('imageFile');

  if (!id && (!fileInput || fileInput.files.length === 0)) {
    alert('Please select an image file to upload.');
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.innerText = "Save Item";
    }
    return;
  }

  // Get checked sizes
  const selectedSizes = Array.from(document.querySelectorAll('input[name="sizeOpt"]:checked'))
                             .map(cb => cb.value)
                             .join(',');

  const formData = new FormData();
  if (id) formData.append('id', id);
  formData.append('title', document.getElementById('title').value);
  formData.append('category', document.getElementById('category').value);
  formData.append('price', document.getElementById('price').value);
  formData.append('sizes', selectedSizes || 'S,M,L,XL');
  formData.append('description', document.getElementById('description').value);
  
  if (fileInput && fileInput.files.length > 0) {
    formData.append('image_file', fileInput.files[0]);
  }

  try {
    const res = await fetch('../api/products.php', {
      method: 'POST',
      body: formData
    });

    const data = await res.json();
    if (data.success) {
      alert(data.message || 'Item saved successfully!');
      resetForm();
      loadProducts();
    } else {
      alert('Failed to save product: ' + (data.message || 'Unknown error'));
    }
  } catch (err) {
    console.error("API Connection Error:", err);
    alert('Error connecting to backend API.');
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.innerText = "Save Item";
    }
  }
}

function editProduct(id, title, category, price, sizes, description) {
  document.getElementById('formTitle').innerText = 'Edit Item';
  document.getElementById('productId').value = id;
  document.getElementById('title').value = title;
  document.getElementById('category').value = category;
  document.getElementById('price').value = price;
  document.getElementById('description').value = description;

  // Check matching size checkboxes
  const sizeArray = sizes ? sizes.split(',') : [];
  document.querySelectorAll('input[name="sizeOpt"]').forEach(cb => {
    cb.checked = sizeArray.includes(cb.value);
  });

  document.getElementById('cancelBtn').classList.remove('hidden');
}

function resetForm() {
  document.getElementById('formTitle').innerText = 'Add Clothing Item';
  document.getElementById('productId').value = '';
  document.getElementById('productForm').reset();
  
  // Reset default checkboxes
  document.querySelectorAll('input[name="sizeOpt"]').forEach(cb => {
    cb.checked = ['S', 'M', 'L', 'XL'].includes(cb.value);
  });

  document.getElementById('cancelBtn').classList.add('hidden');
}

async function deleteProduct(id) {
  if (!confirm('Are you sure you want to delete this item?')) return;

  try {
    const res = await fetch(`../api/products.php?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
      loadProducts();
    } else {
      alert('Failed to delete product.');
    }
  } catch (err) {
    console.error(err);
    alert('Error deleting product.');
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}