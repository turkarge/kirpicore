<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}
?>
<footer class="footer footer-transparent d-print-none">
          <div class="container-xl">
            <div class="row text-center align-items-center flex-row-reverse">
              <div class="col-lg-auto ms-lg-auto">
                <ul class="list-inline list-inline-dots mb-0">
                  <li class="list-inline-item"><a href="#" target="_blank" class="link-secondary" rel="noopener">Dokümantasyon</a></li>
                  <li class="list-inline-item"><a href="#" class="link-secondary">Lisans</a></li>
                  <li class="list-inline-item">Framework v<?php echo e(app_ver()); ?></li>
                </ul>
              </div>
              <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                <ul class="list-inline list-inline-dots mb-0">
                  <li class="list-inline-item">
                    Copyright © 2026
                    <a href="https://www.kirpinetwork.com"  target="_blank" class="link-secondary">Kirpi Network</a>. Tüm hakları saklıdır.
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </footer>
        </div>
</div>
<script src="<?php echo asset_url('js/jquery-3.7.1.min.js'); ?>"></script>
<script src="<?php echo asset_url('js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?php echo asset_url('js/tabler.min.js'); ?>"></script>
<script src="<?php echo asset_url('js/toastr.min.js'); ?>"></script>
<script src="<?php echo asset_url('js/app.js'); ?>"></script>

<?php
global $current_route;
$route_file = $current_route['file'] ?? null;
$page_script = resolve_page_script($route_file);

if ($page_script):
?>
<script src="<?php echo base_url($page_script); ?>"></script>
<?php endif; ?>

<?php
require BASE_PATH . '/layouts/main_modal.php';
require BASE_PATH . '/layouts/confirm_modal.php';
require BASE_PATH . '/layouts/secondary_modal.php';
?>

</body>
</html>
