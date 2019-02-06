
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
                    .html("")
                    .append(r[1])
                    .addClass('centershow')
                    .find('#eventForm').submit(ajaxFormSubmit);
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
function ajaxFormSubmit(evt) {
    evt.preventDefault();
    var data = $(this).serializeArray();
    data.push({'name':'use', 'value':'ajax'});
    var url = $(this).attr('action');
    $.ajax({
        type: 'POST',
        url: url,
        data: $.param(data),
        dataType: 'json',
        success: function(r) {
            if (r[0]) {
                var datesAffected = r[1];
                ajaxUpdate(datesAffected);
            } else {
                showMessage("Update unsuccessful: " + r[1]);
            }
            HidePopup();
            $("#smoke").removeClass("coverall");
            $("#dialog").html("")
                .removeClass('centershow');
        },
        error: function(xhr) {
            alert("Unsuccessful");
        }
    });
}
function setupEventActions() {
    $('.eventform').click(ajaxFormClick);
    $('.copyform').click(ajaxFormClick);
}
$(document).ready(function() {
    setupEventActions();
});

// vim: set foldmethod=indent :
