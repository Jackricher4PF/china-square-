let cart = [];

document.addEventListener('DOMContentLoaded', () => {
  fetchProducts();
});

async function fetchProducts(search = '') {
  const res = await fetch(`../api/products.php?search=${encodeURIComponent(search)}`);
  const products = await res.json();
  const grid = document.getElementById('productsGrid');

  if (!products || products.length === 0) {
    grid.innerHTML = '<p class="col-span-full text-center text-gray-500 py-8">No clothing items found.</p>';
    return;
  }

  grid.innerHTML = products.map(p => {
    const sizes = p.sizes ? p.sizes.split(',') : ['S', 'M', 'L', 'XL'];
    const sizeOptions = sizes.map(s => `<option value="${s}">${s}</option>`).join('');

    return `
      <div class="bg-white border rounded-xl shadow-sm hover:shadow-md transition overflow-hidden flex flex-col justify-between">
        <img src="${p.image_url}" alt="${p.title}" class="w-full h-56 object-cover">
        <div class="p-4 flex-grow flex flex-col justify-between">
          <div>
            <span class="text-xs bg-red-100 text-red-800 font-semibold px-2 py-0.5 rounded">${p.category}</span>
            <h3 class="font-bold text-lg mt-2">${p.title}</h3>
            <p class="text-sm text-gray-600 line-clamp-2 mt-1">${p.description}</p>
          </div>
          
          <div class="mt-3">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Select Size:</label>
            <select id="size_select_${p.id}" class="w-full border p-1.5 rounded text-xs bg-gray-50 font-bold">
              ${sizeOptions}
            </select>
          </div>

          <div class="mt-4 flex justify-between items-center border-t pt-3">
            <span class="font-black text-xl text-red-600">$${parseFloat(p.price).toFixed(2)}</span>
            <button onclick="addToCart(${p.id}, '${p.title}', ${p.price})" class="bg-black text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-gray-800">Add to Cart</button>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function searchProducts() {
  const query = document.getElementById('searchInput').value;
  fetchProducts(query);
}

function addToCart(id, title, price) {
  const sizeSelect = document.getElementById(`size_select_${id}`);
  const selectedSize = sizeSelect ? sizeSelect.value : 'M';

  // Differentiate items in cart by ID and Size combination
  const itemKey = `${id}_${selectedSize}`;
  const item = cart.find(i => i.key === itemKey);

  if (item) {
    item.quantity++;
  } else {
    cart.push({ key: itemKey, id, title, size: selectedSize, price: parseFloat(price), quantity: 1 });
  }
  updateCartUI();
}

function updateCartUI() {
  document.getElementById('cartCount').innerText = cart.reduce((acc, i) => acc + i.quantity, 0);
  const container = document.getElementById('cartItems');

  container.innerHTML = cart.map((i, index) => `
    <div class="flex justify-between items-center border-b pb-2">
      <div>
        <h4 class="font-bold text-sm">${i.title} <span class="text-xs text-red-600 bg-red-50 px-1.5 py-0.5 rounded font-mono">Size: ${i.size}</span></h4>
        <p class="text-xs text-gray-500">$${i.price.toFixed(2)} x ${i.quantity}</p>
      </div>
      <div class="flex items-center space-x-2">
        <span class="font-bold text-sm">$${(i.price * i.quantity).toFixed(2)}</span>
        <button onclick="removeFromCart(${index})" class="text-xs text-red-500 font-bold hover:underline">✕</button>
      </div>
    </div>
  `).join('');

  const total = cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);
  document.getElementById('cartTotal').innerText = total.toFixed(2);
}

function removeFromCart(index) {
  cart.splice(index, 1);
  updateCartUI();
}

function toggleCartModal() {
  document.getElementById('cartModal').classList.toggle('hidden');
}

function checkoutWithPaystack(publicKey, userEmail) {
  if (!userEmail) {
    alert('Please login to proceed to checkout.');
    window.location.href = 'login.php';
    return;
  }
  if (cart.length === 0) {
    alert('Your cart is empty.');
    return;
  }

  const totalAmount = cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);

  const handler = PaystackPop.setup({
    key: publicKey,
    email: userEmail,
    amount: totalAmount * 100,
    currency: 'USD',
    ref: 'CS_' + Math.floor((Math.random() * 1000000000) + 1),
    callback: function(response) {
      verifyPaymentOnBackend(response.reference, totalAmount);
    },
    onClose: function() {
      alert('Transaction cancelled.');
    }
  });

  handler.openIframe();
}

async function verifyPaymentOnBackend(reference, totalAmount) {
  const res = await fetch('../api/verify_payment.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ reference, totalAmount })
  });

  const data = await res.json();
  if (data.success) {
    alert('Payment successful! Your order has been placed.');
    cart = [];
    updateCartUI();
    toggleCartModal();
  } else {
    alert('Payment verification failed: ' + data.message);
  }
}