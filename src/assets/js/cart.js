// Функция для форматирования цены
function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
}

// Функция для пересчета итоговой суммы
function updateTotals() {
    let subtotal = 0;
    
    document.querySelectorAll('.cart-item').forEach(item => {
        const priceText = item.querySelector('.cart-item__price').textContent;
        const price = parseInt(priceText.replace(/\D/g, ''));
        const quantity = parseInt(item.querySelector('.quantity-input').value);
        const itemTotal = price * quantity;
        
        // Обновляем итоговую цену товара
        item.querySelector('.item-total-price').textContent = formatPrice(itemTotal);
        
        subtotal += itemTotal;
    });
    
    // Обновляем итоговые суммы
    document.querySelector('.cart-subtotal').textContent = formatPrice(subtotal);
    document.querySelector('.cart-total').textContent = formatPrice(subtotal);
}

// Увеличение количества
document.querySelectorAll('.quantity-plus').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
        let quantity = parseInt(input.value);
        
        if (quantity < 99) {
            input.value = quantity + 1;
            updateQuantityOnServer(productId, 1);
            updateTotals();
            
            // Обновляем счетчик в header
            updateCartBadgeCount(1);
        }
    });
});

// Уменьшение количества
document.querySelectorAll('.quantity-minus').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
        let quantity = parseInt(input.value);
        
        if (quantity > 1) {
            input.value = quantity - 1;
            updateQuantityOnServer(productId, -1);
            updateTotals();
            
            // Обновляем счетчик в header
            updateCartBadgeCount(-1);
        }
    });
});

// Обновление счетчика в header (изменение на величину change)
function updateCartBadgeCount(change) {
    const cartBadge = document.getElementById('cartBadge');
    
    if (cartBadge) {
        let currentCount = parseInt(cartBadge.textContent);
        let newCount = currentCount + change;
        
        if (newCount > 0) {
            cartBadge.textContent = newCount;
            cartBadge.style.animation = 'none';
            setTimeout(() => {
                cartBadge.style.animation = 'cartPulse 0.3s ease';
            }, 10);
        } else {
            cartBadge.remove();
        }
    }
}

// Обновление количества на сервере
function updateQuantityOnServer(productId, change) {
    const action = change > 0 ? 'add' : 'remove';
    
    fetch('ajax/cart_update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&change=${Math.abs(change)}&action=${action}`
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Ошибка обновления корзины:', data.message);
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
    });
}

// Удаление товара из корзины
document.querySelectorAll('.cart-item__remove').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('Удалить товар из корзины?')) {
            return;
        }
        
        const productId = this.dataset.productId;
        const cartItem = this.closest('.cart-item');
        
        fetch('ajax/cart_remove.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Анимация удаления
                cartItem.style.opacity = '0';
                cartItem.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    cartItem.remove();
                    updateTotals();
                    
                    // Обновляем счетчик в header
                    updateCartBadge(data.cart_count);
                    
                    // Если корзина пуста - перезагружаем страницу
                    if (document.querySelectorAll('.cart-item').length === 0) {
                        location.reload();
                    }
                }, 300);
            } else {
                alert('Ошибка: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при удалении товара');
        });
    });
});

// Функция обновления счетчика корзины
function updateCartBadge(count) {
    const cartBadge = document.getElementById('cartBadge');
    
    if (count > 0) {
        if (cartBadge) {
            cartBadge.textContent = count;
            cartBadge.style.animation = 'none';
            setTimeout(() => {
                cartBadge.style.animation = 'cartPulse 0.3s ease';
            }, 10);
        }
    } else {
        // Удаляем badge если товаров нет
        if (cartBadge) {
            cartBadge.remove();
        }
    }
}