<?php

function getRemotes() {
    global $configuration;
    //require("./version.php");
    //$configuration = new CalendarConfig($version);
    $remotes = $configuration['remotes'];
    $rv = array();
    foreach (explode("\n", $remotes) as $r) {
        list($url, $remote_categories, $local_category) = explode("::", $r);
        if ($url) {
            $rv[] = array(
                'url'=>$url,
                'categories'=>$remote_categories,
                'local_category'=>$local_category
            );
        }
    }
    return $rv;
}

// Remote category functions
function getRemoteRows($template, $rangedata) {
    list($day, $month, $year, $length, $unit) = $rangedata;
    $remoteInstallations = getRemotes();
    if (! $remoteInstallations) return array();
    $rv = array();
    // $logfile = fopen("remotelog.txt", "w"); // For Debugging
    foreach ($remoteInstallations as $remote) {
        // fetch the data from each configured remote
        $postdata = http_build_query( // This already urlencodes everything
            array(
                'mode'     => 'remote',
                'day'      => $day,
                'month'    => $month,
                'year'     => $year,
                'length'   => $length,
                'unit'     => $unit,
                'action'   => $template,
                'categories' => $remote['categories']
            ));
        $rows = file_get_contents($remote['url']."?".$postdata);
        // fwrite($logfile, $remote['url']."?".$postdata);
        $decoded = json_decode($rows, true);
        // fwrite($logfile, print_r($decoded, true));
        if (! (is_array($decoded) && count($decoded))) {
            continue;
        } else {
            foreach ($decoded as $event) {
                if (0 != $event['id']) { // No remote-remote events
                    $event["remoteid"] = $event['id'];
                    $event["id"] = 0;    // Don't set up event interface for these
                    $event["category"] = $remote['local_category'];
                    $event["urlbase"] = $remote['url'];
                    $rv[] = $event;
                }
            }
        }
    }
    // fclose($logfile);
    return $rv;
}

function needsRemoteRows() {
    if ('remote' == getGET('mode'))
        return true;
    else
        return false;
}

function provideRemoteRows($rows) {
    echo json_encode($rows);
    die(0);
}
