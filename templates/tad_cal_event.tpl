<{$toolbar}>
<{if $now_op|default:false}>
    <{include file="$xoops_rootpath/modules/$xoops_dirname/templates/op_`$now_op`.tpl"}>
<{/if}>

<script language="JavaScript" type="text/javascript">
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
