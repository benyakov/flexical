<?php

function getRemotes() {
    global $configuration;
    //require("./version.php");
    //$configuration = new CalendarConfig($version);
    $remotes = $configuration['remotes'];
    $rv = array();
    foreach (explode("\n", $remotes) as $r) {
        list($url, $categories) = explode("(", $r);
        $categories = rtrim($categories, ')');
        $rv[] = array(
            'url'=>$url,
            'categories'=>$categories
        );
    }
    return $rv;
}

// Remote category functions
function getRemoteRows($template) {
    $remoteInstallations = getRemotes();
    $rv = [];
    foreach ($remoteInstallations as $remote) {
        // fetch the data from each configured remote
        $postdata = http_build_query(
            array(
                'mode' => 'remote',
                'action' => urlencode($template),
                'category' => urlencode($remote['categories']);
            ));
        $opts = array('http'=>
            array(
                'method'=>'POST',
                'header'=>'Content-type: application/x-www-form-urlencoded',
                'content'=>$postdata
            )
        );
        $context = stream_context_create($opts);
        $rows = file_get_contents($remote['url'], false, $context);
        // Check data
        $rows = json_decode($rows);
        if (! (is_array($rows) && count($rows)))
            continue;
        else
            array_push($rv, json_decode($rows));
    }
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
}
