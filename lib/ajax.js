
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
            $("#smoke").removeClass("coverall");
            $("#dialog").html("")
                .removeClass('centershow');
        },
        error: function(xhr) {
            alert("Unsuccessful");
        }
    });
}
function ajaxUpdate(datesAffected) {
    var allDatesInPage = $('.day-cell').map(function(){
        return $(this).data('date');
    }).get();
    allDatesInPage = new Set(allDatesInPage);
    // Use only dates currently displayed
    if (-1 !== $.inArray("all", datesAffected)) {
        datesAffected = allDatesInPage;
    } else {
        datesAffected = new Set(datesAffected);
        for (var elem of allDatesInPage) {
            if (! datesAffected.has(elem)) {
                datesAffected.delete(elem);
            }
        }
    }
    datesAffected = Array.from(datesAffected);
    if (1 == datesAffected.length) {
        var da = datesAffected[0].split("-");
        var dayReq = "&year="+da[0]+"&month="+da[1]+"&day="+da[2];
        console.log(datesAffected);
        $.post({
            url: 'index.php?action=calendar&json=day'+dayReq,
            dataType: 'json',
            success: function(r) {
                if (r) {
                    $('.day-cell[data-date="'+datesAffected[0]+'"]').replaceWith(r[0]);
                    updateCategories(r[1]);
                }
            }
        });
    } else {
        $.ajax({
            type: 'POST',
            url: 'index.php?action=calendar&json=days',
            dataType: 'json',
            data: {'dates': JSON.stringify(datesAffected)},
            success: function(r) {
                if (r[0]) {
                    for (var i = r[1].length-1; i>=0; i--) {
                        $('.day-cell[data-date="'+r[1][i]['date']+'"]')
                            .replaceWith(r[1][i]['content']);
                    }
                    updateCategories(r[2]);
                } else {
                    console.log("Problem updating days: "+r[1]);
                }
            }
        });
    }
}
function updateCategories(categories) {
    // TODO: Ensure that the listing of categories present includes the given ones.
    var existing = $(".categorykey span")
        .map(function() { return $(this).text() }).get();
    console.log("Existing categories: "+existing);
}
$(document).ready(function() {
    $('.eventform').click(ajaxFormClick);
    $('.copyform').click(ajaxFormClick);
});
