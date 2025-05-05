document.addEventListener('DOMContentLoaded', function() {
    // Toggle entre precios mensuales/anuales
    const billingToggle = document.getElementById('billing-toggle');
    const priceElements = document.querySelectorAll('.price .amount');
    
    billingToggle.addEventListener('change', function() {
        const isAnnual = this.checked;
        
        priceElements.forEach(element => {
            const monthlyPrice = element.getAttribute('data-monthly');
            const annualPrice = element.getAttribute('data-annual');
            element.textContent = isAnnual ? annualPrice : monthlyPrice;
        });
    });
    
    // Botones de selección de plan
    const planButtons = document.querySelectorAll('.select-plan');
    
    planButtons.forEach(button => {
        button.addEventListener('click', function() {
            const planName = this.closest('.pricing-card').querySelector('h3').textContent;
            alert(`Has seleccionado el plan ${planName}. Serás redirigido al proceso de pago.`);
            // Aquí iría la redirección real al proceso de pago
            // window.location.href = `/checkout?plan=${planName.toLowerCase()}`;
        });
    });
    
    // Botón de contacto
    const contactButton = document.querySelector('.contact-button');
    if (contactButton) {
        contactButton.addEventListener('click', function() {
            alert('Serás redirigido a nuestra página de contacto.');
            // window.location.href = '/contacto';
        });
    }
});