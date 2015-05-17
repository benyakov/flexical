<?php
if (! is_numeric($auth)) {
    setMessage(__('subscribers must log in'));
    header("location:login.php");
    exit(0);
}
    $uid = $_SESSION[$sprefix]["authdata"]["uid"];
    // Show the user's reminder list.
    // TODO: Query all the user's reminders, sorted by event date.
    $dbh->beginTransaction();
    $q = $dbh->prepare("SELECT r.eventid, r.type,
        r.days, e.date, e.all_day, e.start_time, e.title,
        e.category, e.text
        FROM `{$tablepre}reminders` AS r JOIN `{$tablepre}eventstb` AS e
        ON (r.eventid == e.id)
        WHERE r.uid == :user
        ORDER BY e.date, e.start_time");
    $q->bindParam("user", $_SESSION[$sprefix]["authdata"]["uid"]);
    $q->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?=$configuration['site_title']?></title>
    <meta http-equiv=Content-Type content=text/html;charset=utf-8>
    <link rel=stylesheet href=css/styles-pop.css>
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
    <tbody>
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
