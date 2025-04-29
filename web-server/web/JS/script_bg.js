const themeToggle = document.getElementById("theme-toggle");

// Toggle theme on button click
themeToggle.addEventListener("click", () => {
  const currentTheme = document.documentElement.getAttribute("data-theme");
  const newTheme = currentTheme === "dark" ? "light" : "dark";
  
  document.documentElement.setAttribute("data-theme", newTheme);
  localStorage.setItem("theme", newTheme);
});

// Footer functionality can be added here if needed
// Currently the footer doesn't require specific JavaScript functionality
// as it's mainly static content with CSS hover effects

// If you want to add dynamic year for copyright:
document.addEventListener('DOMContentLoaded', function() {
  // Update copyright year automatically
  const copyrightElement = document.querySelector('.copyright');
  if (copyrightElement) {
      const currentYear = new Date().getFullYear();
      copyrightElement.textContent = copyrightElement.textContent.replace('2023', currentYear);
  }
  
  // Add smooth scrolling to footer links
  document.querySelectorAll('.footer-links a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
          e.preventDefault();
          
          const targetId = this.getAttribute('href');
          const targetElement = document.querySelector(targetId);
          
          if (targetElement) {
              targetElement.scrollIntoView({
                  behavior: 'smooth',
                  block: 'start'
              });
              
              // Update URL without reloading
              if (history.pushState) {
                  history.pushState(null, null, targetId);
              } else {
                  location.hash = targetId;
              }
          }
      });
  });
  
  // Social links analytics tracking (example)
  document.querySelectorAll('.social-link').forEach(link => {
      link.addEventListener('click', function(e) {
          const platform = this.getAttribute('aria-label');
          console.log(`Social link clicked: ${platform}`);
          // Here you would typically send data to analytics service
      });
  });
});