<?php
if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <?php unset($_SESSION['flash_success']);
endif;

if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-error">
        <?php echo htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <?php unset($_SESSION['flash_error']);
endif;
?>
