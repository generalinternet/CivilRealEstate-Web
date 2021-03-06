// Create a Stripe client.
var stripe = Stripe('pk_test_6eXqhxgw7I58yDMcvjdymEm200JKybHY8I'); //The publishable API key 

// Create an instance of Elements.
var elements = stripe.elements();

var style = {

};

var cardNum = elements.create('cardNumber', {style: style});
var cardExp = elements.create('cardExpiry', {style: style});
var cardCVC = elements.create('cardCvc', {style: style});

cardNum.mount('#card-number');
cardExp.mount('#card-exp');
cardCVC.mount('#card-cvc');



var cardBrandToPfClass = {
    'visa': 'pf-visa',
    'mastercard': 'pf-mastercard',
    'amex': 'pf-american-express',
    'discover': 'pf-discover',
    'diners': 'pf-diners',
    'jcb': 'pf-jcb',
    'unknown': 'pf-credit-card'
};

function setBrandIcon(brand) {
    var brandIconElement = document.getElementById('brand-icon');
    var pfClass = 'pf-credit-card';
    if (brand in cardBrandToPfClass) {
        pfClass = cardBrandToPfClass[brand];
    }
    for (var i = brandIconElement.classList.length - 1; i >= 0; i--) {
        brandIconElement.classList.remove(brandIconElement.classList[i]);
    }
    brandIconElement.classList.add('pf');
    brandIconElement.classList.add(pfClass);
}

cardNum.addEventListener('change', function (event) {
    var displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
    // Switch brand logo
    if (event.brand) {
        setBrandIcon(event.brand);
    }
});

cardExp.addEventListener('change', function (event) {
    var displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});

cardCVC.addEventListener('change', function (event) {
    var displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});


$(document).on('submit', '#payment_form', function (event) {
    event.preventDefault();

    var newCard = true;
    if ($('[name="selected_card"]:checked').length) {
        $('[name="selected_card"]:checked').each(function () {
            var selectedCardId = $(this).val();
            if (selectedCardId !== 'new') {
                newCard = false;
            }
        });
    }

    if (newCard) {
        var name = $('#field_name').val();
        var postal = $('#field_addr_region').val();
        var data = {
            'name':name,
            'address_zip':postal
        };
        stripe.createToken(cardNum, data).then(function (result) {
            if (result.error) {
                // Inform the user if there was an error.
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
                var payment_form = $('#payment_form');
                if (payment_form.length > 0 && payment_form.hasClass('submitting')) {
                    payment_form.removeClass('submitting');
                    payment_form.data('submitting', false);
                }
            } else {
                // Send the token to your server.
                stripeTokenHandler(result.token);
            }
        });
    } else {
      var form = document.getElementById('payment_form');
      form.submit();
    }

});

// Submit the form with the token ID.
function stripeTokenHandler(token) {
    // Insert the token ID into the form so it gets submitted to the server
    var form = document.getElementById('payment_form');
    var hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'stripeToken');
    hiddenInput.setAttribute('value', token.id);
    form.appendChild(hiddenInput);

    // Submit the form
    form.submit();
}