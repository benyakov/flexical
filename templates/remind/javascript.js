$(document).ready(function() {
    $.postJSON("<?=$installroot?>?action=remind", {"option" = "list"},
        function(rv) {
            // TODO: Use a Javascript template here instead of building by hand
            // See https://en.wikipedia.org/wiki/JavaScript_templating
            $("#reminder-list").html();
        });
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

