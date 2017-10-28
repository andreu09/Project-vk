$(document).ready(function() {
    $('#add_user_ticket').click(function(){

        $.ajax({
            type: "POST",
            url: '/Ajax/ticket_add',
            data: { user_uid: $('#user_uid').val(),
                message: { text: $('#user_ticket_message').val(), 
                type:  $('#type_user_ticket').val()  } },
            success: function(data) {

                console.log(data);

            },
            error: function() {

                alert('Извините, сервис поддержки не доступен!');

            },
            dataType: 'json'
        });

    })
})