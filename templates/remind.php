<?php
if (! is_numeric($auth)) {
    setMessage(__('subscribers must log in'));
    header("location:login.php");
    exit(0);
}

if ($_POST) { // Called via ajax
    $params = $_POST;
    require("./templates/require/ajax.php");
    exit(0);
}
$uid = $_SESSION[$sprefix]["authdata"]["uid"];
// Show the user's reminder list.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?=$configuration['site_title']?></title>
    <meta http-equiv=Content-Type content=text/html;charset=utf-8>
    <meta content="width=device-width, initial-scale=1" name="viewport"></meta>
    <link rel=stylesheet href=css/styles-pop.css>
    <?php
    jqueryCDN();
    jqueryuiCDN();
    ?>
    <script type="text/javascript" language="JavaScript">
    $(document).ready(function() {
        $.postJSON("<?=$this_script?>?action=remind", {"action" = "list"},
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

    </script>
</head>
<body>
<header>
<span class=add_new_header><?= __('Remind Header') ?></span>
</header>
<form name=remindForm method=post onsubmit=formSubmit();>
<input form=remindForm type=hidden name=uid value="<?=$uid?>" >
</form>
    <table border=0 cellspacing=7 cellpadding=0>
    <thead><tr>
        <th><?= __('Delete') ?></th>
        <th><?= __('Event') ?></th>
        <th><?= __('Type') ?></th>
        <th><?= __('Advance') ?></th>
    </tr></thead>
    <tfoot><tr>
    <td><button form=remindForm type=submit value="<?= __('Delete') ?>" onclick=deleteChecked() >
    <button form=remindForm type=submit value="<?= __('Submit') ?>"></td>
    </tr></tfoot>
    <tbody id="reminder-list">
<?php  while ($row = $q->fetch(PDO::FETCH_ASSOC)) { //delete, summary, type, days
?>
        <tr eventid="<?=$row["eventid"]?>">
            <td><input form=remindForm type=checkbox></td>
            <td><article>
            <header>
            <h4><?=$row['date']?> <i><?=$row['title']?></i></h4>
            </header>
            <p><?=$row['all_day']?__('All_Day'):$row['start_time']?></p>
            <div id="<?="{$row['eventid']}-text"?>"
                style="visibility: hidden;"><?=Markdown($row['text'])?>
            </div>
            </article></td>
<?php # TODO Continue Here ?>
            <td><input type=text><!-- drop-down for type of reminder (event or related)--></td>
            <td><!-- textbox for days before the reminder is sent --></td>
    </tr>
    </tbody>
    </table>
</body>
</html>
<?php
}
