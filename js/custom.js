var trimitereFormularContact = (function(){

    var urlContact     = 'http://localhost/aplicatieweb/ro/contact';    
    var urlContactAjax = 'http://localhost/aplicatieweb/ro/contactAjax';    
    var currentUrl     =  window.location.href;

    if(currentUrl.indexOf('contact') > -1){
        
        $('#contactForm').submit(function(event){
            
            event.preventDefault(); // Previn submitul formularui de contact

            var nume    = $('#name').val();
            var email   = $('#email').val();
            var subiect = $('#subject').val();
            var message = $('#message').val();

            $.ajax({    //create an ajax request to load_page.php
                type: "POST",
                url: urlContactAjax,
                data: {
                    "name" : name,
                    "email": email,
                    "subject": subiect,
                    "message": message
                },
                dataType: "json",   //expect html to be returned
                success: function(msg){

                    console.log(msg);
                }

            }).done(function(data){
                console.log(data);
            });

        })
    }
})

// $(document).ready(trimitereFormularContact);