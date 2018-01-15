function updateEventViewContent(r) {
    if (r[0]) {
        $('#eventview-content').html(r[0]);
        setupEventActions();
        setupRelatedLinks();
    }
}
function ajaxUpdate(datesAffected) {
    $.post({
        url: 'index.php?json=eventdisplay',
        dataType: 'json',
        success: updateEventViewContent
    });
}
function setupRelatedLinks() {
    $(".related-link").click(function(evt) {
        evt.preventDefault();
        var url = $(this).attr('href');
        $.post({
            url: url+"&json=eventdisplay",
            dataType: 'json',
            success: updateEventViewContent
        });
    });
}
$(function(){
    setupRelatedLinks();
});
