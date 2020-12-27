$(function() {

    /* changes modal */
    $('.modal-trigger').on('click', function(e) {
        e.preventDefault();
        $('.modal').modal('hide');
        let modalTarget = $(this).data('modal');

        setTimeout(function() {
            $(modalTarget).modal('show');
        }, 500);
    });

    /* Download platform section toggle */
    $(".platform-chooser__link").on('click', function(e) {
        e.preventDefault();
        $(".platform-chooser__link").removeClass('active');

        $(this).addClass('active');
        let id = $(this).attr('href');

        $('.app-download').hide();
        $(id).show();
    });


    /* Init testimonial slider */
    if ($('.testimonial-slider').length) {
        var mySwiper = new Swiper('.testimonial-slider', {
            loop: true,
            slidesPerView: 1,
            spaceBetween: 30,
            breakpoints: {
                640: {
                    slidesPerView: 2,
                },
                768: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 2,
                },
                1280: {
                    slidesPerView: 3,
                },
            }
        });

        $(document).on('click', '.testimonial-items-wrapper .next', function() {
            mySwiper.slideNext();
        });

        $(document).on('click', '.testimonial-items-wrapper .prev', function() {
            mySwiper.slidePrev();
        });
    }

    /* jump to top of the page */
    $('.jump-page-top').on('click', function() {
        $('html, body').animate({ scrollTop: 0 }, 500);
    });

    /* accord state handler*/
    $(document).on('click', '.payment-item', function(e) {

        $(this).toggleClass('active');

    });

    $(window).scroll(function() {
        let scrollTop = $(window).scrollTop();
        if (scrollTop > 800) {
            $('.jump-page-top').css('display', 'flex');
        } else {
            $('.jump-page-top').css('display', 'none');
        }
    });

    /** Form Input handler */
    $(document).on('focus', 'input', function() {
        let $this = $(this);
        $this.siblings('i').addClass('is-focus');
    });

    $(document).on('blur', 'input', function() {
        let $this = $(this);
        $this.siblings('i').removeClass('is-focus');
    });

});