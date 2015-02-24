/**
 * Created by mitko on 2.2.2014.
 */

$(window).ready(function(){

    var webURL="http://192.168.2.9/projekt/"
    $('#loginForm').submit(function(e){
        e.preventDefault();
        $('#result').removeClass();
        var dataString = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: webURL + 'login',
            data: dataString,
            dataType: 'json',
            success: function(data){
                $('#result').html(data.message);
                if(data.success==true){
                    $('#result').addClass('success');
                    setTimeout(function() {document.location.href=webURL}, 750);
                }else{
                    $('#result').addClass('error');
                    $('#PassInput').val('');
                    $('#NameInput').val('');
                    if(data.changelocation==true){
                        setTimeout(function() {document.location.href=webURL}, 750);
                    }
                }
            },
            error: function () {
                alert('Nepodarilo sa nadviazat spojenie. Kontaktujte prosím administratora.');
                setTimeout(function() {document.location.reload(true);}, 750);
            }
        });
    });

    $('#logout').click(function(){
        $.ajax({
            type: 'POST',
            url: webURL + 'login/logout',
            dataType: 'json',
            success: function(data){
                if(data.success==true){
                    alert("Úspešne ste sa odhlásili.");
                    setTimeout(function() {document.location.reload(true);}, 200);
                }else{
                    alert(data.message);
                    setTimeout(function() {document.location.reload(true);}, 200);
                }
            },
            error: function () {
                alert('Nepodarilo sa nadviazat spojenie. Kontaktujte prosím administratora.');
                setTimeout(function() {document.location.reload(true);}, 750);
            }
        });
    });
});


