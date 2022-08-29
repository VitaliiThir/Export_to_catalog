<?php

use PHPOffice\App\Config;

require_once($_SERVER['DOCUMENT_ROOT'] . '/export/PHPOffice/vendor/autoload.php');

$date_db = new DateTime();
$year = $date_db->format('Y');
?>
</main>
<div class="container">
    <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
        <div class="col-md-4 d-flex align-items-center">
            <span class="text-muted">© <?= $year ?> ВПалитре -> <?= $_SESSION['CURRENT_SHOP_NAME'] ?></span>
        </div>
        <div class="col-md-4 d-flex align-items-center justify-content-end">
            <span class="text-muted">Управление магазином</span>
        </div>
    </footer>
</div>
<script>
    const shop_root_dir = '<?= Config::$shops_root_dir ?>'
</script>
<script src="/<?= Config::$shops_root_dir ?>/assets/scripts/libs/jquery_3.6.0.min.js"></script>
<script src="/<?= Config::$shops_root_dir ?>/assets/scripts/libs/jquery.inputmask.min.js"></script>
<script src="/<?= Config::$shops_root_dir ?>/assets/scripts/functions.js"></script>
<script src="/<?= Config::$shops_root_dir ?>/assets/scripts/common.js"></script>
</body>
</html>
