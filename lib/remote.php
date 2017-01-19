<?php

function getRemotes() {
    global $configuration;
    //require("./version.php");
    //$configuration = new CalendarConfig($version);
    $remotes = $configuration['remotes'];
    $rv = array();
    foreach (explode("\n", $remotes) as $r) {
        list $url, $categories = explode("(", $r);
        $categories = rtrim($categories, ")");
        $categories = explode("," $categories);
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
                'action' => $template
            ));
        $opts = array('http'=>
            array(
                'method'=>'POST',
                'header'=>'Content-type: application/x-www-form-urlencoded',
                'content'=>$postdata
            )
        )
        $context = stream_context_create($opts);
        $rows = file_get_contents(urlencode($remote['url']), false, $context);

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
