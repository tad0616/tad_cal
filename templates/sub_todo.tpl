<{if $tag=="todo"}>
    <a href="event.php?op=todo_status_todo_ok&sn=<{$sn|default:''}>">
    <i class="fa-regular fa-square" aria-hidden="true"></i>
    </a>
<{elseif $tag=="todo-ok"}>
    <a href="event.php?op=todo_status_todo&sn=<{$sn|default:''}>">
    <i class="fa fa-check-square" aria-hidden="true"></i>
    </a>
<{/if}>