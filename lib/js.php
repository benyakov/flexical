<?php

/*** Inline Javascript Provision ***/
function javaScript() {
    global $auth, $m, $y;
    ?>
	<script type="text/javascript" language="JavaScript">
    <!--
    <?php 	if ($auth) { ?>
    function deleteConfirm(eid) {
        var msg = "<?= __('deleteconfirm') ?>";
        if (confirm(msg)) {
            //window.location = "eventsubmit.php?flag=delete&id=" + eid + "&month=<?=$m?>&year=<?=$y?>";
            $.get("eventsubmit.php", {
                use: "ajax",
                flag: "delete",
                id: eid }, function(r) {
                    HidePopup();
                    if (r[0]) {
                        ajaxUpdate(r[1]);
                    } else {
                        showMessage("Delete unsuccessful: " + r[1]);
                    }
                }, 'json');
        } else {
            return;
        }
    }
    function executeMultiDelete(eid, futureOnly) {
        if (futureOnly) {
            var future = "&future_only=1";
        } else {
            var future = "";
        }
        $.get("eventsubmit.php", "flag=delete&include_related=1&use=ajax&id="+eid+future,
            function(r) {
                if (r[0]) {
                    ajaxUpdate(r[1]);
                } else {
                    showMessage("Delete unsuccessful: " + r[1]);
                }
            }, 'json');
    }
    function deleteRelated(eid) {
        $('<div></div>').appendTo('body')
            .html('<div><h6><?= __('futureonlyconfirm') ?></h6></div>')
            .dialog({
                modal: true,
                zIndex: 10000,
                autoOpen: true,
                width: 'auto',
                resizable: false,
                buttons: {
                    Yes: function() {
                        //window.location = "eventsubmit.php?flag=delete&future_only=1&include_related=1&id=" + eid;
                        HidePopup();
                        $(this).remove();
                        executeMultiDelete(eid, true);
                    },
                    No: function() {
                        //window.location = "eventsubmit.php?flag=delete&include_related=1&id=" + eid;
                        HidePopup();
                        $(this).remove();
                        executeMultiDelete(eid, false);
                    }
                },
                close: function (event, ui) {
                    $(this).remove();
                }
        });
    }
    function batchDeleteConfirm() {
        var msg = "<?= __('batchdeleteconfirm') ?>";
        if (confirm(msg)) {
            window.location = "batch.php?flag=delete";
        } else {
            return;
        }
    }
    function relateConfirm() {
        var msg = "<?= __('batchrelateconfirm') ?>";
        if (confirm(msg)) {
            window.location = "batch.php?flag=relate";
        } else {
            return;
        }
    }
    <?php } ?>

    function checkAll(element, matchstr) {
        table=$(element).closest('table');
        $(":checkbox[name$='"+matchstr+"']", table).attr('checked', true);
    }
    function uncheckAll(element, matchstr) {
        table=$(element).closest('table');
        $(":checkbox[name$='"+matchstr+"']", table).attr('checked', false);
    }

    var currentPopup = false;
    function ShowHidePopup(theElement, dbid, related) {
        if (currentPopup == dbid) {
            HidePopup();
            return;
        }
        var contents = "<div class=\"actionicons\">" +
            "<a href=\"index.php?action=eventdisplay&id="+dbid+"\" title=\"<?=__('show')?>\"><img src=\"images/show.png\" alt=\"<?=__('show')?>\"/></a> " +
            "<a href=\"index.php?action=remind&id="+dbid+"\" title=\"<?=__('remind')?>\"><img src=\"images/mail.png\" alt=\"<?=__('remind')?>\"/></a> " ;
        <?php if ($auth>=2) { ?>
            contents += "<a class=\"copyform\" href=\"copyform.php?id="+dbid+"\" title=\"<?=__('copy')?>\"><img src=\"images/copy.png\" alt=\"<?=__('copy')?>\"/></a> " +
            "<a class=\"eventform\" href=\"eventform.php?id="+dbid+"\" title=\"<?=__('edit')?>\"><img src=\"images/edit.png\" alt=\"<?=__('edit')?>\"/></a> " +
            "<a href=\"javascript:void(0);\" onClick=\"deleteConfirm("+dbid+");\" title=\"<?=__('delete')?>\"><img src=\"images/trash.png\" alt=\"<?=__('delete')?>\"></a> ";
        if (related) {
            contents += " <a href=\"filter.php?filterrelated="+related+"\" title=\"<?=__('show related')?>\"><img src=\"images/showall.png\" alt=\"<?php __('show related')?>\"></a> " +
                "<a href=\"javascript:void(0);\" onClick=\"deleteRelated("+dbid+");\" title=\"<?=__('delete related')?>\"><img src=\"images/multitrash.png\" alt=\"<?=__('delete related')?>\"></a> ";
        }
        <?php } ?>
        contents += "<div class=\"cancelbox\"><a href=\"javascript:void(0);\" onClick=\"HidePopup();\">X</a></div>";
        var loc = $(theElement).offset();
        var mm = $("#MonthMenu").empty().append(contents).css(
        { "position": "absolute",
          "top": loc.top + 18,
          "left": loc.left + 0,
        }).fadeIn("fast");
        mm.find(".eventform").click(ajaxFormClick)
        mm.find(".copyform").click(ajaxFormClick);
        currentPopup = dbid;
    }
    function HidePopup() {
        currentPopup = false;
        $("#MonthMenu").fadeOut("fast");
    }
    // -->
	</script>
    <?php
}

function js_zeroTime($formname="eventForm") {
    ?>function zeroTime() {
        if (document.<?=$formname?>.all_day.checked) {
            document.<?=$formname?>.start_hour.disabled = true;
            document.<?=$formname?>.start_minute.disabled = true;
            document.<?=$formname?>.start_am_pm.disabled = true;
            document.<?=$formname?>.end_hour.disabled = true;
            document.<?=$formname?>.end_minute.disabled = true;
            document.<?=$formname?>.end_am_pm.disabled = true;
        } else {
            document.<?=$formname?>.start_hour.disabled = false;
            document.<?=$formname?>.start_minute.disabled = false;
            document.<?=$formname?>.start_am_pm.disabled = false;
            document.<?=$formname?>.end_hour.disabled = false;
            document.<?=$formname?>.end_minute.disabled = false;
            document.<?=$formname?>.end_am_pm.disabled = false;
        }
    }
    <?php
}

function js_revealjsonly() {
    ?><!--
    function revealjsonly(ids) {
        for (i=0; i<ids.length; i++) {
            id = ids[i];
            yr = document.getElementById(id);
            yr.style.visibility = 'visible';
        }
    }
    // -->
    <?php
}

function js_checkrepeat() {
    ?>function checkrepeatcount() {
        if (document.eventForm.repeatcount.value == "0") {
            document.eventForm.month.disabled = false;
            document.eventForm.day.disabled = false;
            document.eventForm.year.disabled = false;
        } else {
            document.eventForm.month.disabled = true;
            document.eventForm.day.disabled = true;
            document.eventForm.year.disabled = true;
        }
    }
    function checkrepeattype() {
        if (document.eventForm.repeattype.value == "single") {
            document.eventForm.repeatskip.disabled = true;
            document.eventForm.repeatcount.disabled = true;
        } else {
            document.eventForm.repeatskip.disabled = false;
            document.eventForm.repeatcount.disabled = false;
        }
    }
    <?php
}

