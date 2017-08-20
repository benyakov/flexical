
function ajaxFormClick(evt) {
    evt.preventDefault();
    var url = $(this).attr('href');
    $.ajax({
        type: 'POST',
        url: url,
        data: {'use':'ajax'},
        dataType: 'json',
        success: function(r) {
            if (r[0]) {
                $("#smoke").addClass("coverall");
                $('#dialog')
                    .html(r[1])
                    .addClass('centershow');
                $('#cancelButton').click(function(evt) {
                    evt.preventDefault();
                    $("#smoke").removeClass("coverall");
                    $("#dialog").html("")
                        .removeClass('centershow');
                });
            }
        }
    });
}
$(document).ready(function() {
    $('.eventform').click(ajaxFormClick);
    $('.copyform').click(ajaxFormClick);
});
