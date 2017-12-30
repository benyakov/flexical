function ajaxUpdate(datesAffected) {
    var params = location.search
    if (params.length == 0) {
        params = "?"
    } else {
        params = params + "&"
    }
    $.post({
        url: 'index.php'+params+'json=eventlist',
        dataType: 'json',
        success: function(r) {
            if (r[0]) {
                $('#page').html(r[0]);
                setupEventActions();
                setupEventList();
            }
        }
    });
}
function setupEventList() {
    $(".jsonly").css("visibility", "visible");
    $("#DatePicker").datepicker({
        buttonImage: 'images/calendarbutton.png',
        buttonImageOnly: true,
        changeMonth: true,
        changeYear: true,
        showOn: 'both',
        onSelect: function(chosenDate, picker){
            var dateitems = chosenDate.split('/'); // Y/M/D
            document.displaySpan.month.value = dateitems[0];
            document.displaySpan.day.value = dateitems[1];
            document.displaySpan.year.value = dateitems[2];
        } }).css("visibility", "visible");
}
$(function(){
    setupEventList();
});
