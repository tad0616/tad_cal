<{if $tag=="todo"}>
    <a href="event.php?op=todo_status_todo_ok&sn=<{$sn}>">
    <i class="fa fa-square-o" aria-hidden="true"></i>
    </a>
<{elseif $tag=="todo-ok"}>
    <a href="event.php?op=todo_status_todo&sn=<{$sn}>">
    <i class="fa fa-check-square-o" aria-hidden="true"></i>
    </a>
<{/if}>