<?php
switch ($params['action']) {

case 'add':
    // Check to see if it's already there.
    $dbh->beginTransaction();
    $q = $dbh->prepare("SELECT `id` FROM `{$tablepre}reminders`
        WHERE `eventid` = :id AND `type` = :type AND `uid` = :user");
    $q->bindParam("id", $params['id']);
    $q->bindParam("type", $params['type']);
    $q->bindParam("user", $_SESSION[$sprefix]["authdata"]["uid"]);
    $q->execute();
    if ($q->rowCount()) {
        echo json_encode(__('Reminder exists'));
        exit(0);
    }
    $q = $dbh->prepare("INSERT INTO `{$tablepre}reminders`
        SET `eventid` = :id, `uid` = :user, `type` = :type, `days` = :days");
    $q->bindParam("id", $params['id']);
    $q->bindParam("uid", $_SESSION[$sprefix]["authdata"]["uid"]);
    $q->bindParam("type", $params['type']);
    $q->bindParam("days", $params['days']);
    $q->execute();
    echo json_encode(__('Reminder set'));
    $dbh->commit();
    break;
case 'delete':
    $dbh->beginTransaction();
    $q = $dbh->prepare("DELETE FROM `{$tablepre}reminders`
        where `eventid` = :id AND `uid` = :user AND `type` = :type");
    $q->bindParam("id", $params['id']);
    $q->bindParam("user", $params['user']);
    $q->bindParam("type", $params['type']);
    $q->execute();
    echo json_encode(__('Reminder deleted'));
    $dbh->commit();
    break;
case 'list':
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
    $result = $q->fetchAll(PDO::FETCH_ASSOC);
    foreach (array_keys($result) as $k) {
        $result[$k]["text"] = Markdown($result[$k]["text"]);
    }
    echo json_encode($result);
    $dbh->commit();
    break;
}

?>
