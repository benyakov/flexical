<?php
require_once("./lib/remote.php");
if (needsRemoteRows()) {
    writeLaTeXevents($d, $m, $y, $l, $u, "remote");
    exit(0);
} else {
    $mode = "normal";
}
?>
\smallskip\noindent
\begin{tabularx}{\linewidth}{ll@{\hspace{.5\tabcolsep}}c@{\hspace{.25em}}X}
<?= writeLaTeXevents($d, $m, $y, $l, $u) ?>
 \end{tabularx}
% vim: set tags+=../../**/tags :
