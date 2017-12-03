
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
                    .addClass('centershow')
                    .find('#eventForm').submit(ajaxFormSubmit(evt));
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
    var data = this.serialize();
    var url = $(this).attr('href');
    $.ajax({
        type: 'POST',
        url: url,
        data: {'use':'ajax'},
        dataType: 'json',
        success: function(r) {
            if (r[0]) {
                var datesAffected = r[1];
                ajaxUpdate(datesAffected);
            } else {
                showMessage("Update unsuccessful: " + r[1]);
            }
            $("#smoke").removeClass("coverall");
            $("#dialog").html("")
                .removeClass('centershow');
        }
    });
}
function ajaxUpdate(datesAffected) {
    for (var i = datesAffected.length - 1; i >= 0; i--) {
        $('.day-cell[data-date="'.datesAffected[i].'"]').ajax({
            type: 'POST',
            url: 'index.php?action=calendar&json=day'.mkDayReq(datesAffected[i]),
            dataType: 'json',
            success: function(r) {
                if (r) {
                    $(this).html(r[0]);
                    updateCategories(r[1]);
                }
            }
        });
    }
}
function updateCategories(categories) {
    // TODO: Ensure that the listing of categories present includes the given ones.
    var existing = $(".categorykey span")
        .map(function() { return $(this).text() }).get();
}
$(document).ready(function() {
    $('.eventform').click(ajaxFormClick);
    $('.copyform').click(ajaxFormClick);
});
