let cart = JSON.parse(localStorage.getItem("chinaSquareCart")) || [];

function saveCart() {
    localStorage.setItem("chinaSquareCart", JSON.stringify(cart));
}
document.addEventListener('DOMContentLoaded', () => {
  updateCartUI();
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
            <button onclick="handleAddToCart(${p.id}, '${p.title.replace(/'/g, "\\'")}', ${p.price})" class="bg-black text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-gray-800">Add to Cart</button>
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
  saveCart();
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
    saveCart();
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
    saveCart();
    updateCartUI();
    toggleCartModal();
  } else {
    alert('Payment verification failed: ' + data.message);
  }
}

const imageLightbox = document.getElementById('imageLightbox');
const lightboxImage = document.getElementById('lightboxImage');
const productsGrid = document.getElementById('productsGrid');

productsGrid.addEventListener('click', function (event) {
    const img = event.target.closest('img');

    if (!img || !productsGrid.contains(img)) return;

    lightboxImage.src = img.src;
    lightboxImage.alt = img.alt || 'Full product image';

    imageLightbox.classList.add('active');
    document.body.style.overflow = 'hidden';
});

function closeLightbox() {
    imageLightbox.classList.remove('active');
    lightboxImage.src = '';
    document.body.style.overflow = '';
}

imageLightbox.querySelector('.close-lightbox')
    .addEventListener('click', closeLightbox);

imageLightbox.addEventListener('click', function (event) {
    if (event.target === imageLightbox) {
        closeLightbox();
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeLightbox();
    }
});



const originalAddToCart = addToCart;

function showCartToast(message, type = "success") {
    let toast = document.getElementById("cartToast");

    if (!toast) {
        toast = document.createElement("div");
        toast.id = "cartToast";
        document.body.appendChild(toast);
    }

    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 999999;
        padding: 14px 22px;
        max-width: calc(100vw - 40px);
        color: white;
        background: ${type === "success" ? "#16a34a" : "#dc2626"};
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        opacity: 0;
        transform: translateX(120%);
        transition: all 0.3s ease;
    `;

    toast.textContent = message;

    requestAnimationFrame(() => {
        toast.style.opacity = "1";
        toast.style.transform = "translateX(0)";
    });

    clearTimeout(window.cartToastTimer);

    window.cartToastTimer = setTimeout(() => {
        toast.style.opacity = "0";
        toast.style.transform = "translateX(120%)";
    }, 3000);
}

addToCart = function(id, title, price) {
    try {
        originalAddToCart(id, title, price);
        showCartToast("✓ " + title + " added to cart successfully!");
    } catch (error) {
        console.error(error);
        showCartToast("✗ Failed to add " + title + " to cart.", "error");
    }
};

