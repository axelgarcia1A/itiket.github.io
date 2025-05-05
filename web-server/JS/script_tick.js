
  // Ticket page specific functions
  function initTicketPage() {
    // Ticket button functionality
    const ticketButton = document.getElementById("open-ticket");
    if (ticketButton) {
      ticketButton.addEventListener("click", function() {
        const ticketUrl = "https://itiket.com/abrir-ticket";
        window.open(ticketUrl, "_blank");
      });
    }
    
    // Login button functionality
    const loginButton = document.querySelector(".login .button a");
    if (loginButton) {
      loginButton.addEventListener("click", function(e) {
        e.preventDefault();
        alert("Redirigiendo al sistema de login...");
      });
    }
    
    // Theme toggle functionality
    const themeToggle = document.getElementById("theme-toggle");
    if (themeToggle) {
      themeToggle.addEventListener("change", function() {
        const currentTheme = document.documentElement.getAttribute("data-theme");
        const newTheme = currentTheme === "dark" ? "light" : "dark";
        
        document.documentElement.setAttribute("data-theme", newTheme);
        localStorage.setItem("theme", newTheme);
      });
    }
  }
  
  // Initialize ticket page
  document.addEventListener("DOMContentLoaded", function() {
    loadTheme();
    initTicketPage();
  });