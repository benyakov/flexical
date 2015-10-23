function loadListToTemplate() {
    // reload the reminder list with current data.
    $.postJSON("<?=$installroot?>?action=remind", {"action" = "list"})
        .done(function(rv) {
            $("#reminder-list").html($.hbTemplate_list(rv));
        });
}

$(document).ready(function() {
    // Load mustache list template and attach it to the jquery object
    $.get("<?=$installroot?>, {"load" = remind_list.html"})
        .done(function(rv) {
            var template = Handlebars.compile(rv);
            $.hbTemplate_list = template;
        });
    loadListToTemplate();
});

Handlebars.registerHelper('AllDay', function(allday, otherwise) {
    if (allday) {
        return "<?=__('All_Day')?>";
    } else {
        return otherwise;
    }
}

Handlebars.registerHelper('ReminderType', function(type) {
    // Set up a select drop-down for event, related, or category reminders
    var select = '<select id="reminder-type">';
    var types = [ 'event', 'related', 'category' ];
    var selected = ' ';
    for (i in types) {
        if (type == types[i]) {
            selected = " selected ";
        } else {
            selected = " ";
        }
        select += '<option' + selected + 'value="' + types[i] + '">';
        select += types[i];
        select += '</option>'
    }
    select = select + '</select>';
    return select;
}

