$(function () {

    /**
     * Menu handler
     */

    $('.menu-btn').on('click', function() {
        $('.m-menu').fadeIn();
    });

    $('.js-close-menu').on('click', function(e) {
        e.preventDefault();

        $('.m-menu').hide();
    });


    /**
     *
     * package ajax send
     *
     */
    $(".prices-items__bar").on('click', function() {
        let packageid = $(this).data('id');
        let paymentTypeInput = $('.prices-items__type').find('input[name=payment]:checked');
        let paymentType = 'alipay';

        console.log(packageid);
        console.log(paymentTypeInput.attr('id'));

        if (paymentTypeInput.attr('id') === 'alipayCheck') {
            paymentType = "alipay";
        } else {
            paymentType = "card";
        }

        $.ajax({
            method: "POST",
            url: "/",
            data: { packageID: packageid, payment: paymentType }
        })
        .done(function( msg ) {
        });
    });

});
