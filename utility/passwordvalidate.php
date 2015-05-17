<script language="JavaScript">
    function validate(f, next) {
        var regex = /\W+/;
        var pw = f.pw.value;
        var str = "";
        if (pw == "") { str += "\n<?=__('pwblank')?>"; }
        if (pw != f.pwconfirm.value) { str += "\n<?=__('pwmatch')?>"; }
        if (pw.length < 4) { str += "\n<?=__('pwlength')?>"; }
        if (regex.test(pw)) { str += "\n<?=__('pwchars')?>"; }

        if (str == "") {
            f.method = "post";
            f.action = "http://<?=$serverdir?>/useradmin.php?flag="+next;
            f.submit();
        } else {
            alert(str);
            return false;
        }
    }
</script>
<!-- vim: set tags+=../../**/tags : -->
