document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('product');

    if (!productId) {
        console.error("Ошибка: отсутствует идентификатор товара в URL");
        displayError("Товар не найден.");
        return;
    }

    fetch(`load_product.php?id=${productId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Ошибка сервера: ${response.status}`);
            }
            return response.json();
        })
        .then(product => {
            if (!product || Object.keys(product).length === 0) {
                throw new Error("Товар не найден.");
            }

            // Обновляем изображение
            const imgElement = document.querySelector("#product-description img");
            if (imgElement) {
                imgElement.src = product.image;
                imgElement.alt = product.name;
            }

            // Обновляем текст в полупрозрачном прямоугольнике
            const overlayElement = document.querySelector(".overlay");
            if (overlayElement) {
                overlayElement.innerHTML = `
                    <h2>${product.name}</h2>
                    <p>${product.description}</p>
                    <p><strong>Вес:</strong> ${product.weight}</p>
                    <p><strong>Цена:</strong> ${product.price} ₽</p>
                    <p><strong>Количество:</strong> ${product.quantity} шт.</p>
                    <p><strong>Белки:</strong> ${product.proteins} г</p>
                    <p><strong>Жиры:</strong> ${product.fats} г</p>
                    <p><strong>Углеводы:</strong> ${product.carbohydrates} г</p>
                    <p><strong>Калорийность:</strong> ${product.calories} ккал</p>
                    <form action="add_to_cart.php" method="post">
                        <input type="hidden" name="product_id" value="${product.id}">
                        <button type="submit" class="cart-button">🛒 Добавить в корзину</button>
                    </form>
                `;
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки данных о товаре:', error);
            displayError(error.message);
        });

    function displayError(message) {
        const overlayElement = document.querySelector(".overlay");
        if (overlayElement) {
            overlayElement.innerHTML = `<p style="color: red;">${message}</p>`;
        }
        const imgElement = document.querySelector("#product-description img");
        if (imgElement) {
            imgElement.src = "placeholder.jpg"; // Заглушка для изображения
            imgElement.alt = "Изображение отсутствует";
        }
    }
});