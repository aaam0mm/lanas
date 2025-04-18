<?php
if (!is_login_in()) {
	include_once __DIR__ . '/parts/signin-modal.php';
}
include_once __DIR__ . '/parts/language-settings-modal.php';
$get_all_taxonomies = get_all_taxonomies();
$current_content_lang = current_content_lang();
$current_user = get_current_user_info();
$header_bloc = extract(switch_blocs("header"));
$header_menu = get_the_menu(${"bloc_header_menu_" . current_lang() . "_header_bloc"});
?>
<!-- Modal -->
<div class="modal fade" id="loadModal" tabindex="-1" role="dialog" aria-labelledby="loadModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="loadModalLabel"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body text-center">
				<i class="fas fa-spinner fa-spin fa-4x"></i>
			</div>
		</div>
	</div>
</div><!-- / Modal -->

<div id="preload" class="position-fixed d-flex align-items-center justify-content-center bg-white top-0 right-0 w-100 h-100">
	<img src="<?php echo siteurl() . '/assets/images/loading.gif'; ?>" class="img-fluid" />
</div>

<!-- Header top -->
<div class="header-top bg-darker">
	<div class="header-top-link container d-flex align-items-center">
		<!-- Nav menu -->
		<nav class="navbar navbar-expand navbar-dark px-0">

			<!-- Toggler/collapse Button -->
			<button class="navbar-toggler ml-auto" type="button" data-toggle="collapse" href="#sidebar-fixed" role="button" aria-expanded="false" aria-controls="sidebar-fixed">
				<i class="fas fa-bars"></i>
			</button><!-- / Toggler/collapse Button -->

			<div class="collapse navbar-collapse" id="collapseHeaderNavbar">
				<button class="btn btn-transparent btn-toggle-right-menu color-primary p-0 mr-2" type="button" data-toggle="collapse" href="#sidebar-fixed" role="button" aria-expanded="false" aria-controls="sidebar-fixed">
					<i class="fas fa-bars fa-lg"></i>
				</button>
				<ul class="list-unstyled navbar-nav header-top-navbar">
					<li class="nav-item mr-3 position-relative link-explore-taxos">
						<a href="#" class="taxos-explore-text"><i class="fas fa-sitemap mr-2 color-primary"></i><?php echo _t("الأقسام"); ?></a>
						<div id="explore-taxos" class="position-absolute">

							<!-- explore taxos -->
							<div class="explore-taxos d-flex position-relative bg-white shadow">

								<!-- taxonomies explore -->
								<div class="taxonomies-explore">
									<ul class="nav flex-column">
										<?php foreach ($get_all_taxonomies as $taxo): ?>
											<a href="<?php echo siteurl(); ?>/posts/<?php esc_html($taxo["taxo_type"]); ?>" class="nav-link text-dark h5" data-taxonomy="<?php esc_html($taxo["taxo_type"]); ?>"><?php esc_html(json_decode($taxo["taxo_title"])->$current_content_lang); ?></a>
										<?php endforeach; ?>
									</ul>
								</div><!--/ taxonomies explore -->

								<!-- categories explore -->
								<div class="categories-explore d-none d-sm-block">
									<?php foreach ($get_all_taxonomies as $taxo): ?>
										<div class="nav flex-column top-categories top-categories-taxonomy-<?php esc_html($taxo["taxo_type"]); ?>">
											<i class="fas fa-spinner fa-spin"></i>
										</div>
									<?php endforeach; ?>
								</div><!-- / categories explore -->

							</div><!-- /explore taxos -->

						</div>
					</li>
					<?php if (!is_login_in()): ?>
						<li class="nav-item"><a href="<?php echo siteurl(); ?>/signin.php"><i class="fas fa-users mr-2 color-primary"></i><?php echo _t("دخول"); ?></a></li>
					<?php endif; ?>
					<li class="nav-item d-sm-none d-block"></li>
				</ul>
			</div>
		</nav>
		<!-- Header User top -->
		<div class="ml-auto">
			<ul class="list-unstyled d-flex mb-0">
				<?php if (is_login_in()): header_buttons();
				else: $get_menu_lang = get_langs(current_lang()); ?>
					<!-- Select language -->
					<li>
						<div class="btn-group">
					<li class="btn rounded-0 open-lang-settings" type="button"><i style="font-size: 27px;" class="fas fa-language mr-2 color-primary"></i></a></li>
		</div>
		</li><!-- / Select language -->
	<?php endif; ?>
	</ul>
	</div>
</div><!-- /Header User top -->
</div><!-- / Header top -->

<!-- Sidebar fixed -->
<div id="sidebar-fixed" class="position-fixed right-0 top-0 w-100 h-100 collapse">
	<div class="sidebar-fixed position-absolute h-100  animated <?php if (is_ltr()) : ?>fadeInLeft<?php else: ?> fadeInRight<?php endif; ?>">
		<ul class="nav flex-column">
			<li class="nav-item"><a class="nav-link active" href="#"><i class="fas fa-home mr-2"></i><?php echo _t("الرئيسية"); ?></a></li>
			<?php if (!is_login_in()): ?>
				<li class="nav-item"><a class="nav-link link-signup font-weight-bold" href="<?php echo siteurl(); ?>/signin.php"><?php echo _t("دخول إلى الحساب"); ?></a></li>
				<li class="nav-item"><a class="nav-link text-white" href="contact.php"><?php echo _t("إتصل بنا"); ?></a></li>
				<?php
			endif;
			if (is_array($header_menu)): foreach ($header_menu as $link_h):
				?>
					<li class="nav-item">
						<a class="nav-link text-white" target="<?php esc_html($link_h["target"]); ?>" href="<?php esc_html($link_h["link"]); ?>"><?php esc_html($link_h["title"]); ?></a>
					</li>
			<?php endforeach;
			endif; ?>
		</ul>
	</div>
</div><!-- / Sidebar fixed -->