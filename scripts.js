document.addEventListener("DOMContentLoaded", function () {
    fetch("session_check.php")
        .then(response => response.json())
        .then(data => {
            if (data.isAdmin) {
                if (!document.querySelector(".navbar a[href='admin_dashboard.php']")) {
                    const adminProfileLink = document.createElement('a');
                    adminProfileLink.href = "admin_dashboard.php";
                    adminProfileLink.textContent = "Профиль администратора";
                    adminProfileLink.classList.add("transparent-button");
                    document.querySelector(".navbar").appendChild(adminProfileLink);
                }
            }
            document.getElementById("profile-btn").href = data.profileUrl;
        });
});

function openModal() {
    document.getElementById('registerModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('registerModal').style.display = 'none';
    document.getElementById('loginModal').style.display = 'none';
}

// Закрытие модального окна при клике вне его области
window.onclick = function(event) {
    const modal = document.getElementById('registerModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
    if(event.target == document.getElementById('loginModal')) {
        document.getElementById('loginModal').style.display = 'none';
    }
}

function showLogin() {
    document.getElementById('registerModal').style.display = 'none';
    document.getElementById('loginModal').style.display = 'block';
}