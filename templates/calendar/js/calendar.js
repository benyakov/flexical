$(document).ready(function() {
    $("a[data-event-id]").click(popupClick);
});

function popupClick(evt) {
    evt.preventDefault();
    ShowHidePopup(this, $(this).attr('data-event-id'),
       $(this).attr('data-event-related'));
}

function ajaxUpdate(datesAffected) {
    console.log(datesAffected);
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
        $.post({
            url: 'index.php?action=calendar&json=day'+dayReq,
            dataType: 'json',
            success: function(r) {
                if (r) {
                    $('.day-cell[data-date="'+datesAffected[0]+'"]').replaceWith(r[0]);
                    var newcell = $('.day-cell[data-date="'+datesAffected[0]+'"]');
                    newcell.find('a[data-event-id]').click(popupClick);
                    newcell.find('.eventform').click(ajaxFormClick);
                    updateCategories(r[1]);
                }
            }
        });
    } else {
        console.log(datesAffected);
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
                        var newcell = $('.day-cell[data-date="'+datesAffected[0]+'"]');
                        newcell.find('a[data-event-id]').click(popupClick);
                        newcell.find('.eventform').click(ajaxFormClick);
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

// vim: set foldmethod=indent :
