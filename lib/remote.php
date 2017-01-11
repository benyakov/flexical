<?php

// Remote category functions
function getRemoteRows() {
    global $remoteInstallations;
    $rv = [];
    foreach ($remoteInstallations as $remote) {
        // fetch the data from each configured remote
        $postdata = http_build_query(
            array(
                'action' => 'get-remote'
            ));
        file_get_contents($remote['url']

        $rv += json_decode($rows);
    }
    return $rv;
}

function provideRemoteRows($rows) {
    if ($rows) {
        echo json_encode($rows);
    } else {
        global $provideRemote;
        return $provideRemote;
    }
}
