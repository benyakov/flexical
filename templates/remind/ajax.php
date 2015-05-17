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
    $dbh->beginTransaction();
    $q = $dbh->prepare("INSERT INTO `{$tablepre}reminders`
        SET `eventid` = :id, `uid` = :user, `type` = :type, `days` = :days");
    $q->bindParam("id", $params['id']);
    $q->bindParam("uid", $_SESSION[$sprefix]["authdata"]["uid"]);
    $q->bindParam("type", $params['type']);
    $q->bindParam("days", $params['days']);
    $q->execute();
    echo json_encode(__('Reminder set'));
    break;
case 'delete':

    break;
case 'update':

    break;
}

?>
