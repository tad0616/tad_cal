<ul style="margin:0px;padding:0px;">
<{foreach from=$block.content item=cal}>
  <dt><{$cal.start}></dt>
  <{foreach from=$cal.event item=event}>
    <li>
      <a href="<{$xoops_url}>/modules/tad_cal/event.php?sn=<{$event.sn}>">
        <{$event.title}>
      </a>
    </li>
  <{/foreach}>
<{/foreach}>
</ul>

<div style="text-align:right;">
  <a href="<{$xoops_url}>/modules/tad_cal" class="label label-info"><{$smarty.const._MB_TADCAL_TO_INDEX}></a>
</div>