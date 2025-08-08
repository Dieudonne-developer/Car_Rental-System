// Theme switcher (js/theme-switcher.js)
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle.querySelector('i');
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    const currentTheme = localStorage.getItem('theme');
    
    // Set initial theme
    if (currentTheme === 'dark' || (!currentTheme && prefersDarkScheme.matches)) {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
        themeIcon.classList.remove('bi-moon-stars');
        themeIcon.classList.add('bi-sun');
    } else {
        document.documentElement.setAttribute('data-bs-theme', 'light');
        themeIcon.classList.remove('bi-sun');
        themeIcon.classList.add('bi-moon-stars');
    }
    
    // Toggle theme
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        if (currentTheme === 'dark') {
            document.documentElement.setAttribute('data-bs-theme', 'light');
            localStorage.setItem('theme', 'light');
            themeIcon.classList.remove('bi-sun');
            themeIcon.classList.add('bi-moon-stars');
        } else {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            themeIcon.classList.remove('bi-moon-stars');
            themeIcon.classList.add('bi-sun');
        }
    });
});

// Main application JS
document.addEventListener('DOMContentLoaded', function() {
    // Enable tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Enable popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Image preview for upload forms
    const imageUploads = document.querySelectorAll('.image-upload');
    imageUploads.forEach(function(upload) {
        upload.addEventListener('change', function(e) {
            const previewId = this.dataset.preview;
            const preview = document.getElementById(previewId);
            const defaultText = preview.querySelector('.default-text');
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.style.backgroundImage = `url(${e.target.result})`;
                    preview.classList.add('has-image');
                    if (defaultText) defaultText.style.display = 'none';
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // Date picker initialization for rental dates
    const datePickers = document.querySelectorAll('.datepicker');
    if (datePickers.length > 0) {
        // Load flatpickr only if needed
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
        script.onload = function() {
            flatpickr('.datepicker', {
                minDate: 'today',
                dateFormat: 'Y-m-d',
                disable: [
                    function(date) {
                        // Disable weekends
                        return (date.getDay() === 0 || date.getDay() === 6);
                    }
                ]
            });
        };
        document.head.appendChild(script);
    }
    
    // AJAX car availability check
    const availabilityForms = document.querySelectorAll('.check-availability');
    availabilityForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const button = form.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Checking...';
            
            const formData = new FormData(form);
            
            fetch('/api/check-availability.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    const results = form.closest('.rental-container').querySelector('.availability-results');
                    results.innerHTML = `
                        <div class="alert alert-success">
                            <h5>Car Available!</h5>
                            <p>Total for ${data.days} days: $${data.total_cost.toFixed(2)}</p>
                            <button class="btn btn-success proceed-to-book">Proceed to Book</button>
                        </div>
                    `;
                    
                    // Store the calculated data in the form for submission
                    form.querySelector('input[name="total_cost"]').value = data.total_cost;
                } else {
                    const results = form.closest('.rental-container').querySelector('.availability-results');
                    results.innerHTML = `
                        <div class="alert alert-danger">
                            <p>${data.message}</p>
                            ${data.suggestions ? `<p>Suggestions: ${data.suggestions}</p>` : ''}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while checking availability');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        });
    });
    
    // Price calculator
    const priceCalculators = document.querySelectorAll('.price-calculator');
    priceCalculators.forEach(function(calculator) {
        const pricePerDay = parseFloat(calculator.dataset.price);
        const daysInput = calculator.querySelector('input[name="days"]');
        const totalDisplay = calculator.querySelector('.total-price');
        
        function calculateTotal() {
            const days = parseInt(daysInput.value) || 0;
            const total = days * pricePerDay;
            totalDisplay.textContent = total.toFixed(2);
        }
        
        daysInput.addEventListener('input', calculateTotal);
        calculateTotal(); // Initial calculation
    });
});