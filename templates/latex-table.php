\begin{minipage}{.95\linewidth}
\footnotesize
\section*{Some Upcoming Events for the ELS Columbia Gorge Parish}

\footnotesize{Taken from the online calendar on <?=gmdate("Y-m-d H:i:s")?>.
Check \url{<?=$serverdir?>} for more and current information.}

\begin{tabularx}{\linewidth}{rcX}
<?= writeLaTeXevents($d, $m, $y, $l, $u) ?>
\end{tabularx}

\end{minipage}

% vim: set tags+=../../**/tags :
