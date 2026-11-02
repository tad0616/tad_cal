<?php
require __DIR__ . '/header.php';

$adminObject = \Xmf\Module\Admin::getInstance();
$adminObject->displayNavigation(basename(__FILE__));

$moduleDir = $xoopsModule->getVar('dirname');
$xoopsUrl = rtrim(XOOPS_URL, '/');
$redirectUri = $xoopsUrl . '/modules/' . $moduleDir . '/google_sync.php';
$documentUrl = '../docs/google_sync.html?redirect_uri=' . rawurlencode($redirectUri);

echo '<iframe id="tad-cal-google-sync-guide" title="' . htmlspecialchars(_MI_TADCAL_ADMENU2, ENT_QUOTES, 'UTF-8') . '" src="' . htmlspecialchars($documentUrl, ENT_QUOTES, 'UTF-8') . '" style="width: 100%; min-height: 80vh; border: 0;"></iframe>';
echo <<<'HTML'
<script>
    (function () {
        var frame = document.getElementById('tad-cal-google-sync-guide');
        function resizeGuide() {
            frame.style.height = frame.contentWindow.document.documentElement.scrollHeight + 'px';
        }
        frame.addEventListener('load', function () {
            resizeGuide();
            window.addEventListener('resize', resizeGuide);
        });
    }());
</script>
HTML;

require __DIR__ . '/footer.php';
