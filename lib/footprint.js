function mkClickDialog(url) {
    return function(evt) {
        evt.preventDefault();
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
    };
}
$(document).ready(function() {
    $('#filterbutton').click(mkClickDialog("filter.php"));
    $('#categorychooserbutton').click(mkClickDialog("categorychooser.php"));
});
