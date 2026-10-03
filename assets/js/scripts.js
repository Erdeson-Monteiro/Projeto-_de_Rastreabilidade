// scripts.js

document.addEventListener("DOMContentLoaded", function() {
    // Some apenas com as mensagens de sucesso; avisos e erros continuam visíveis
    const alerts = document.querySelectorAll(".alert-success");
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    });

    // O efeito hover dos botões fica no styles.css (.btn:hover)

    // Animação suave para carregamento de elementos
    const fadeElements = document.querySelectorAll(".fade-in");
    fadeElements.forEach(element => {
        element.style.opacity = "0";
        element.style.transition = "opacity 1s ease-in-out";
        setTimeout(() => {
            element.style.opacity = "1";
        }, 500);
    });

    // Navbar efeito de rolagem
    window.addEventListener("scroll", function() {
        let navbar = document.querySelector(".navbar");
        if (window.scrollY > 50) {
            navbar.style.backgroundColor = "#2a5580";
        } else {
            navbar.style.backgroundColor = "#34699A";
        }
    });
});
