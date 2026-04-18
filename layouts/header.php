<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$user = current_user();
$flash = get_flash_message();

global $current_route;
$route_file = $current_route['file'] ?? null;
$page_script = resolve_page_script($route_file);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo e(app_name()); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="<?php echo asset_url('css/tabler.min.css'); ?>" rel="stylesheet"/>
    <link href="<?php echo asset_url('css/tabler-icons.min.css'); ?>" rel="stylesheet"/>
    <link href="<?php echo asset_url('css/app.css'); ?>" rel="stylesheet"/>
    <link href="<?php echo asset_url('css/toastr.min.css'); ?>" rel="stylesheet">
</head>

<body>
<script>
window.KIRPI_CONFIG = {
    baseUrl: "<?php echo e(BASE_URL); ?>",
    csrfToken: "<?php echo e(get_csrf_token()); ?>",
    flashMessage: <?php echo json_encode($flash, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
};
</script>

<div class="page">

    <?php require BASE_PATH . '/layouts/nav.php'; ?>

    <div class="page-wrapper">
