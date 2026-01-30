// scripts.js

document.addEventListener("DOMContentLoaded", function() {
    const alerts = document.querySelectorAll(".alert");
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    });

    // Efeito hover suave nos botões
    const buttons = document.querySelectorAll(".btn");
    buttons.forEach(button => {
        button.addEventListener("mouseenter", () => {
            button.style.transform = "scale(1.05)";
            button.style.transition = "all 0.3s ease-in-out";
        });
        button.addEventListener("mouseleave", () => {
            button.style.transform = "scale(1)";
        });
    });

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
            navbar.style.backgroundColor = "#0056b3";
        } else {
            navbar.style.backgroundColor = "#007bff";
        }
    });
});
