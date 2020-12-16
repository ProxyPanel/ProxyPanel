$(function() {

    $('.form--signin').on('submit', function(e) {

        $.ajax({
            type: 'POST',
            url:  $('.form--signin').attr('action'),
            data: $('.form--signin').serialize(),
            dataType: 'json',
            success: function(data) {

                // refresh page and showed the logged header
                // window.location.reload();

            }, error: function(data) {
                console.log(data);
            }
        });

    });

});
