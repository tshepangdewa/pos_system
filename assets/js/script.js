// Global function to load cart on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load cart immediately
    loadCart();
    
    // Add click handlers to all "Add to Cart" buttons
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.product-card');
            const productId = card.getAttribute('data-id');
            addToCart(productId);
        });
    });
    
    // Checkout button
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            checkout();
        });
    }
});

// Add item to cart
function addToCart(productId) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart display immediately
            loadCart();
            showMessage('Item added to cart!', 'success');
        } else {
            showMessage(data.error || 'Could not add item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error adding item', 'error');
    });
}

// Update quantity in cart
function updateQuantity(productId, newQuantity) {
    fetch('update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId + '&quantity=' + newQuantity
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCart();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Remove item from cart
function removeFromCart(productId) {
    fetch('remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCart();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Load cart contents
function loadCart() {
    fetch('get_cart.php')
    .then(response => response.json())
    .then(cart => {
        displayCart(cart);
    })
    .catch(error => {
        console.error('Error loading cart:', error);
        document.getElementById('cart-items').innerHTML = '<p>Error loading cart</p>';
    });
}

// Display cart items
function displayCart(cart) {
    const cartContainer = document.getElementById('cart-items');
    const totalElement = document.getElementById('cart-total');
    
    // Check if cart is empty
    if (!cart || Object.keys(cart).length === 0) {
        cartContainer.innerHTML = '<p style="text-align:center; color:#999;">Cart is empty</p>';
        totalElement.textContent = '0.00';
        return;
    }
    
    let html = '';
    let total = 0;
    
    // Loop through cart items
    for (let productId in cart) {
        const item = cart[productId];
        const subtotal = item.price * item.qty;
        total += subtotal;
        
        html += `
            <div class="cart-item">
                <div class="cart-item-info">
                    <span class="cart-item-name">${item.name}</span>
                    <span class="cart-item-price">$${parseFloat(item.price).toFixed(2)} each</span>
                </div>
                <div class="cart-item-controls">
                    <div class="qty-controls">
                        <button onclick="updateQuantity(${productId}, ${item.qty - 1})" class="qty-btn">-</button>
                        <span class="qty-display">${item.qty}</span>
                        <button onclick="updateQuantity(${productId}, ${item.qty + 1})" class="qty-btn">+</button>
                    </div>
                    <span class="item-subtotal">$${subtotal.toFixed(2)}</span>
                    <button onclick="removeFromCart(${productId})" class="remove-btn" title="Remove item">×</button>
                </div>
            </div>
        `;
    }
    
    cartContainer.innerHTML = html;
    totalElement.textContent = total.toFixed(2);
}

// Checkout
function checkout() {
    // Check if cart is empty first
    const cartItems = document.getElementById('cart-items');
    if (cartItems.innerHTML.includes('Cart is empty')) {
        showMessage('Cart is empty!', 'error');
        return;
    }
    
    const checkoutBtn = document.getElementById('checkout-btn');
    checkoutBtn.disabled = true;
    checkoutBtn.textContent = 'Processing...';
    
    fetch('checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Get the raw text first to see what's coming back
        return response.text();
    })
    .then(text => {
        console.log('Raw response:', text);
        
        // Try to parse as JSON
        try {
            const data = JSON.parse(text);
            console.log('Parsed data:', data);
            
            if (data.success) {
                showMessage(`Sale completed! Total: $${data.total}`, 'success');
                loadCart();
                
                // Open receipt in a new window
                if (data.sale_id) {
                    window.open(
                        'receipt.php?sale_id=' + data.sale_id,
                        'receipt',
                        'width=400,height=600'
                    );
                }
                
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showMessage(data.error || 'Checkout failed', 'error');
                checkoutBtn.disabled = false;
                checkoutBtn.textContent = 'Complete Sale';
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            showMessage('Server error: ' + text.substring(0, 100), 'error');
            checkoutBtn.disabled = false;
            checkoutBtn.textContent = 'Complete Sale';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showMessage('Network error. Please try again.', 'error');
        checkoutBtn.disabled = false;
        checkoutBtn.textContent = 'Complete Sale';
    });
}

// Show message
function showMessage(message, type) {
    const messageDiv = document.getElementById('message');
    messageDiv.innerHTML = `<div class="${type}">${message}</div>`;
    setTimeout(() => {
        messageDiv.innerHTML = '';
    }, 3000);
}

// ---- SEARCH FUNCTIONALITY ----
const searchInput = document.getElementById('product-search');
if (searchInput) {
    // Real-time filtering as you type
    searchInput.addEventListener('input', filterProducts);
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Press '/' to focus the search box (unless already typing in an input)
        if (e.key === '/' && document.activeElement !== searchInput) {
            e.preventDefault();
            searchInput.focus();
        }
        // Escape to clear search and blur
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            searchInput.blur();
            filterProducts();
        }
    });
}

function filterProducts() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    const cards = document.querySelectorAll('.product-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const productName = card.querySelector('h3').textContent.toLowerCase();
        
        // Show card only if it matches the search term
        if (productName.includes(searchTerm)) {
            card.style.display = '';  // show (reset to default)
            visibleCount++;
        } else {
            card.style.display = 'none';  // hide
        }
    });
    
    // Optional: show a "no results" message
    const grid = document.querySelector('.product-grid');
    let noResultsMsg = document.getElementById('no-results');
    if (visibleCount === 0 && searchTerm !== '') {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('p');
            noResultsMsg.id = 'no-results';
            noResultsMsg.textContent = 'No products found.';
            noResultsMsg.style.textAlign = 'center';
            noResultsMsg.style.gridColumn = '1 / -1';
            noResultsMsg.style.padding = '2rem';
            noResultsMsg.style.color = '#999';
            grid.appendChild(noResultsMsg);
        }
    } else {
        if (noResultsMsg) noResultsMsg.remove();
    }
}

// Update cart item count badge (call this after each cart change)
function updateCartCount() {
    const cartCountEl = document.getElementById('cart-count');
    if (!cartCountEl) return;
    fetch('get_cart.php')
        .then(res => res.json())
        .then(cart => {
            let totalItems = 0;
            for (let id in cart) totalItems += cart[id].qty;
            cartCountEl.textContent = totalItems;
        });
}

// Modify existing loadCart() to also update the count:
const originalLoadCart = loadCart;
loadCart = function() {
    originalLoadCart();
    updateCartCount();
};