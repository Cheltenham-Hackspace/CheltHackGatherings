/**
 * Created by ryan on 13/06/15.
 */
$('#logout').click(function(){
    var logoutForm = $('<form></form>');
    logoutForm.attr('action', "/CHG/php/login/logout.php");
    logoutForm.attr('method', 'post');
    logoutForm.submit();
    console.log("Called");
});