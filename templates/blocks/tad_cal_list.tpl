<{if $block.content}>
    <ul class="vertical_menu">
        <{foreach from=$block.content item=cal}>
            <li class="selected"><{$cal.start}></li>
            <{foreach from=$cal.event item=event}>
                <li>
                    <a href="<{$xoops_url}>/modules/tad_cal/event.php?sn=<{$event.sn}>">
                        <{$event.title}>
                    </a>
                </li>
            <{/foreach}>
        <{/foreach}>
    </ul>

    <div class="text-right text-end">
        <a href="<{$xoops_url}>/modules/tad_cal" class="btn btn-sm btn-xs btn-info"><{$smarty.const._MB_TADCAL_TO_INDEX}></a>
    </div>
<{/if}>