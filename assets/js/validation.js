// Form Validation Functions

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[0-9+\-\s()]{7,}$/;
    return re.test(phone);
}

function validateDateRange(checkInDate, checkOutDate) {
    const checkIn = new Date(checkInDate);
    const checkOut = new Date(checkOutDate);
    
    if (checkIn >= checkOut) {
        alert('Check-out date must be after check-in date');
        return false;
    }
    
    return true;
}

function validateCardNumber(cardNumber) {
    const re = /^[0-9]{13,19}$/;
    return re.test(cardNumber.replace(/\s/g, ''));
}

function validateCVV(cvv) {
    const re = /^[0-9]{3,4}$/;
    return re.test(cvv);
}

function formatCardNumber(input) {
    let value = input.value.replace(/\s/g, '');
    let formattedValue = '';
    
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formattedValue += ' ';
        }
        formattedValue += value[i];
    }
    
    input.value = formattedValue;
}

// Form submission handlers
document.addEventListener('DOMContentLoaded', function() {
    // Auto-format card number
    const cardInputs = document.querySelectorAll('input[name="card_number"]');
    cardInputs.forEach(input => {
        input.addEventListener('input', function() {
            formatCardNumber(this);
        });
    });
    
    // Date validation
    const dateForm = document.querySelector('form[method="POST"]');
    if (dateForm) {
        dateForm.addEventListener('submit', function(e) {
            const checkInInput = this.querySelector('input[name="check_in_date"]');
            const checkOutInput = this.querySelector('input[name="check_out_date"]');
            
            if (checkInInput && checkOutInput) {
                if (!validateDateRange(checkInInput.value, checkOutInput.value)) {
                    e.preventDefault();
                }
            }
        });
    }
});

// Show/hide password
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}
