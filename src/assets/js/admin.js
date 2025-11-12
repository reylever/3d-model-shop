// Просмотр деталей заказа
function viewOrder(orderId) {
    const modal = document.getElementById('orderModal');
    const details = document.getElementById('orderDetails');
    
    // Показываем загрузку
    details.innerHTML = '<p>Загрузка...</p>';
    modal.classList.add('active');
    
    // Загружаем детали заказа
    fetch(`../ajax/get_order_details.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                details.innerHTML = formatOrderDetails(data.order, data.items);
            } else {
                details.innerHTML = '<p>Ошибка загрузки данных</p>';
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            details.innerHTML = '<p>Произошла ошибка</p>';
        });
}

// Форматирование деталей заказа
function formatOrderDetails(order, items) {
    const statuses = {
        'pending': 'Ожидает',
        'processing': 'В обработке',
        'completed': 'Выполнен',
        'cancelled': 'Отменен'
    };
    
    let html = `
        <h2>Заказ #${order.id}</h2>
        <div style="margin: 20px 0;">
            <p><strong>Клиент:</strong> ${order.username}</p>
            <p><strong>Email:</strong> ${order.email}</p>
            <p><strong>Статус:</strong> <span class="status-badge status-${order.status}">${statuses[order.status]}</span></p>
            <p><strong>Дата:</strong> ${new Date(order.created_at).toLocaleString('ru-RU')}</p>
        </div>
        
        <h3>Товары:</h3>
        <table style="width: 100%; margin-top: 16px;">
            <thead>
                <tr style="border-bottom: 2px solid #dee2e6;">
                    <th style="text-align: left; padding: 8px;">Товар</th>
                    <th style="text-align: center; padding: 8px;">Кол-во</th>
                    <th style="text-align: right; padding: 8px;">Цена</th>
                    <th style="text-align: right; padding: 8px;">Сумма</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    items.forEach(item => {
        html += `
            <tr style="border-bottom: 1px solid #e9ecef;">
                <td style="padding: 12px 8px;">${item.product_name}</td>
                <td style="text-align: center; padding: 12px 8px;">${item.quantity}</td>
                <td style="text-align: right; padding: 12px 8px;">${formatPrice(item.price)} ₽</td>
                <td style="text-align: right; padding: 12px 8px;"><strong>${formatPrice(item.price * item.quantity)} ₽</strong></td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
            <tfoot>
                <tr style="border-top: 2px solid #dee2e6;">
                    <td colspan="3" style="text-align: right; padding: 12px 8px;"><strong>Итого:</strong></td>
                    <td style="text-align: right; padding: 12px 8px;"><strong style="color: #0dcaf0; font-size: 1.2rem;">${formatPrice(order.total_price)} ₽</strong></td>
                </tr>
            </tfoot>
        </table>
    `;
    
    return html;
}

// Форматирование цены
function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price);
}

// Закрытие модального окна
function closeModal() {
    document.getElementById('orderModal').classList.remove('active');
}

// Изменение статуса заказа
function changeStatus(orderId, currentStatus) {
    const modal = document.getElementById('statusModal');
    document.getElementById('statusOrderId').value = orderId;
    document.getElementById('statusSelect').value = currentStatus;
    modal.classList.add('active');
}

// Закрытие модального окна статуса
function closeStatusModal() {
    document.getElementById('statusModal').classList.remove('active');
}

// Закрытие по клику вне модального окна
window.onclick = function(event) {
    const orderModal = document.getElementById('orderModal');
    const statusModal = document.getElementById('statusModal');
    
    if (event.target === orderModal) {
        closeModal();
    }
    if (event.target === statusModal) {
        closeStatusModal();
    }
}