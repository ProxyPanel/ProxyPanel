$(function () {

    
    let totalPrice = 0;

    const stripe = Stripe('pk_test_JZOOnMThjoHKJdVDc8MwKAH500b2mHbiWF');
    const elements = stripe.elements();

    let cardNumberElement,
        cardExpiryElement,
        cardCVCElement;

    // create stripe elements
    cardNumberElement = elements.create('cardNumber');
    cardNumberElement.mount('#card-element');

    cardExpiryElement = elements.create("cardExpiry")
    cardExpiryElement.mount('#card-expire');

    cardCVCElement = elements.create("cardCvc")
    cardCVCElement.mount('#card-cvc');

    cardNumberElement.on('change', ({ error }) => {
        const displayError = document.getElementById('card-errors');
        if (error) {
            displayError.textContent = error.message;
        } else {
            displayError.textContent = '';
        }
    });

    // Submit ALIPAY payment
    $(document).on('click', '.js-checkout-alipay', function (e) {
        e.stopPropagation();

        if (totalPrice <= 0) {
            return;
        }
        let $this = $(this);
        $this.prop('disabled', true);
        let token = $this.data('token');
        console.log(packageSku);
        var response = fetch('payment/purchase',
            {
                method: 'post',
                credentials: "same-origin",
                headers: {
                    'Content-Type': 'application/json',
                    "X-CSRF-Token": token
                },
                body: JSON.stringify({
                    amount: totalPrice,
                    goods_id: packageSku,
                    method: 'stripe',
                    coupon_sn: 2001,
                    pay_type: 'alipay',
                    pay_mode:'intent'
                })
            }
        ).then(function (response) {
            console.log(response);
            return response.json();
        }).then(function (responseJson) {
            console.log(responseJson);
            var clientSecret = responseJson.client_secret;

            stripe.confirmAlipayPayment(clientSecret, {
                // Return URL where the customer should be redirected to after payment
                return_url: `http://${window.location.host}/payment/payment-success`,
            }).then((result) => {
                if (result.error) {
                    $this.prop('disabled', false);
                    // Inform the customer that there was an error.
                    var alipayError = document.getElementById('alipay-error');
                    alipayError.textContent = result.error.message;
                }
            });
        });
    });

    // Submit credit card option - create token
    
    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (totalPrice <= 0) {
            return;
        }

        $('button[name="card-submit"]').prop('disabled', true);
        token = $('input[name="_token"]').val();
        console.log(token); 
        var response = fetch('payment/purchase', {
            method: 'post',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json',
                "X-CSRF-Token": token
            },
            body: JSON.stringify({
                amount: totalPrice,
                goods_id: packageSku,
                method: 'stripe',
                coupon_sn: 2001,
                pay_type: 'card',
                pay_mode:'intent'
              //  sku: packageSku,
                
            })
        }).then(function(response) {
            
            return response.json();
            
          }).then(function(responseJson) {
               console.log(responseJson);
            var clientSecret = responseJson.client_secret;

            stripe.confirmCardPayment(clientSecret, {
                payment_method: {
                    card: cardNumberElement,
                    billing_details: {
                        name: document.getElementById('firstName').value + " " + document.getElementById('lastName').value,
                        // address: {
                        //     postal_code: document.getElementById('postalCode').value
                        // }
                    }
            }
            }).then(function (result) {
                console.log(result);
                $('button[name="card-submit"]').prop('disabled', true);
                if (result.error) {
                    // Show error to your customer (e.g., insufficient funds)
                    console.log(result.error.message);
                } else {
                    // The payment has been processed!
                    if (result.paymentIntent.status === 'succeeded') {
                        // Show a success message to your customer
                        // There's a risk of the customer closing the window before callback
                        // execution. Set up a webhook or plugin to listen for the
                        // payment_intent.succeeded event that handles any business critical
                        // post-payment actions.
                        // REDIRECTING TO STORE THIS PAYMENT DATAS
                        var paymentIntent = result.paymentIntent;
                       // var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        var redirect = '/usercenter';
                        var token = $('input[name="_token"]').val();
                          console.log(token);  
                       
                      fetch(
                       'payment/payment-success',
                       {
                        headers:{
                            'Content-Type': 'application/json',
                            "X-CSRF-Token": token
                        },
                        method : 'get',
                        //body: JSON.stringify({
                        //    paymentIntent: paymentIntent
                       //})
                       
                        }
                       ).then((data) => {
                          console.log(data);
                       //  window.location.href = redirect;
                       }).catch((error) => {
                         console.log(error);
                       });
                      //  console.log(result);
                       // alert("Successfully payment with card666");
                    }
                }
            });
          });


    });


    // var cardData = {
    //     address_zip: document.getElementById('postalCode').value,
    //     name: document.getElementById('firstName').value + " " + document.getElementById('lastName').value,
    // };

    // stripe.createToken(cardNumberElement, cardData).then(function (result) {
    //     if (result.error) {
    //         var errorElement = document.getElementById('card-errors');
    //         errorElement.textContent = result.error.message;
    //     } else {
    //         // Send the token to your server
    //         stripeTokenHandler(result.token);
    //     }
    // });


    // function stripeTokenHandler(token) {
    //     var formItem = document.getElementById('payment-form');
    //     var hiddenInput = document.createElement('input');
    //     hiddenInput.setAttribute('type', 'hidden');
    //     hiddenInput.setAttribute('name', 'stripeToken');
    //     hiddenInput.setAttribute('value', token.id);
    //     form.appendChild(hiddenInput);

    //     hiddenInput.setAttribute('type', 'hidden');
    //     hiddenInput.setAttribute('name', 'amount');
    //     hiddenInput.setAttribute('value', totalPrice);
    //     form.appendChild(hiddenInput);
    //     // Submit the form
    //     formItem[0].submit();
    // }

    /**
     * Changed plans
     */

    setTimeout(function () {
        $('.price-item--hot').click();
    }, 500);


    $('.price-item').on('click', function (e) {
        e.preventDefault();
        let $this = $(this);
        $('.price-item').removeClass("selected");
        $this.addClass("selected");
        setPaymentData($this);
    });

    function setPaymentData($data) {
        let time = $data.find('.price-item__time').data('package-time');
        let price = parseFloat($data.find('.price-item__value').data('package-price'), 2);
        let sku = parseFloat($data.find('.price-item__id').data('package-id'), 2);
        let sale = parseFloat($data.find('.price-item__sale').data('package-sale'), 2);
        let currentPrice = 0;

        let amounts = $('.amounts');
        if (!sale) {
            amounts.find('.discount').hide();
            amounts.find('.amount-row.value').html('<span>' + time + 'day</span><span>$' + price + '</span>');
            amounts.find('.amounts-total').find('.amount-total-value').html('$' + price);

            currentPrice = price;
        } else {
            let discountPrice = parseFloat(price * (sale / 100)).toFixed(2);
            priceVal = parseFloat(price - discountPrice).toFixed(2);
            amounts.find('.amount-row.value').html('<span>' + time + 'day</span><span>$' + price + '</span>');
            amounts.find('.discount').html('<span>Discount -' + sale + '%</span><span>$-' + discountPrice + '</span>');
            amounts.find('.discount').show();
            amounts.find('.amounts-total').find('.amount-total-value').html('$' + priceVal);

            currentPrice = priceVal;
        }

        // this will going into payment
        totalPrice = currentPrice;
        packageSku = sku;

    }

});
