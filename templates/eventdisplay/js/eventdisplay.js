function ajaxUpdate(datesAffected) {
    var params = location.search
    if (params.length == 0) {
        params = "?"
    } else {
        params = params + "&"
    }
    $.post({
        url: 'index.php'+params+'json=eventdisplay',
        dataType: 'json',
        success: function(r) {
            if (r[0]) {
                $('#eventview-content').html(r[0]);
                setupEventActions();
                setupRelatedLinks();
            }
        }
    });
}
function setupRelatedLinks() {
    $(".related-link").click(function(evt) {
        evt.preventDefault();
        var params = location.search
        if (params.length == 0) {
            params = "?"
        } else {
            params = params + "&"
        }
        var url = $(this).attr('href');
        $.ajax({
            type: 'POST',
            url: url+params+"ajax=eventdisplay",
            data: {'use':'ajax'},
            dataType: 'json',
            success: ajaxUpdate
        });
    });
}
$(function(){
    setupRelatedLinks();
});
