// Обработка формы оформления заказа
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Блокируем кнопку
    submitBtn.disabled = true;
    submitBtn.textContent = 'Оформление...';
    
    fetch('ajax/checkout_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Успешное оформление
            alert('Заказ успешно оформлен!\nНомер заказа: #' + data.order_id);
            window.location.href = 'orders.php';
        } else {
            // Ошибка
            alert('Ошибка: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Оформить заказ';
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при оформлении заказа');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Оформить заказ';
    });
});

// Маска для телефона (простая реализация)
const phoneInput = document.getElementById('phone');
phoneInput.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    if (value.length > 0) {
        if (value[0] !== '7') {
            value = '7' + value;
        }
    }
    
    if (value.length > 11) {
        value = value.slice(0, 11);
    }
    
    let formatted = '+7';
    if (value.length > 1) {
        formatted += ' (' + value.slice(1, 4);
    }
    if (value.length >= 5) {
        formatted += ') ' + value.slice(4, 7);
    }
    if (value.length >= 8) {
        formatted += '-' + value.slice(7, 9);
    }
    if (value.length >= 10) {
        formatted += '-' + value.slice(9, 11);
    }
    
    e.target.value = formatted;
});