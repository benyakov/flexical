<?php

require("./db.php");

$dbh->beginTransaction();
$qm = $dbh->query("SELECT * FROM `{$tablepre}eventstb`");

$deleted = 0;
while ($row = $qm->fetch(PDO::FETCH_ASSOC)) {
    // Make sure the current record is still there.
    $q = $dbh->prepare("SELECT 1 FROM `{$tablepre}eventstb`
        WHERE `id` = :id");
    $q->bindParam($row['id']);
    $q->execute();
    if ($q->rowCount()) {
        // Delete other rows with the same date, times, and title.
        $q = $dbh->prepare("DELETE FROM '{$tablepre}eventstb'
            WHERE `id` != :id
            AND `date` = :date
            AND `start_time` = :start_time
            AND `end_time` = :end_time
            AND `title` = :escaped_title");
        $q->bindParam(':id', $row['id']);
        $q->bindParam(':date', $row['date']);
        $q->bindParam(':start_time', $row['start_time']);
        $q->bindParam(':end_time', $row['end_time']);
        $q->bindParam(':title', $row['title']);
        $q->execute();
        $deleted += $q->rowCount();
    }
}

echo "{$deleted} duplicate rows deleted."

// vim: set tags+=../../**/tags :
