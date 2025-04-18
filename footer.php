<?php
$s_c_settings = @unserialize(get_settings("s_c_settings"));
$footer_bloc = extract(switch_blocs("footer"));
$footer_menu = get_the_menu(${"bloc_footer_menu_" . current_lang() . "_footer_bloc"});
?>

<footer class="bg-dark">
	<div class="container py-5">
		<div class="row">
			<!-- Footer col -->
			<div class="col-md-4 col-sm-12">
				<div class="col-12">
					<div class="footer-logo">
						<span><img src="<?php esc_html(get_thumb($bloc_footer_logo_footer_bloc, null)); ?>" class="img-fluid" alt="" /></span>
					</div>
					<div class="footer-col-body pt-3">
						<?php echo $bloc_footer_desc_footer_bloc[current_lang()]; ?>
					</div>
				</div>
			</div>
			<!-- / Footer col -->

			<!-- Footer col -->
			<div class="col-md-4 col-sm-12">
				<div class="col-12">
					<div class="footer-col-title  pb-3">
						<span class="h4 color-primary"><?php echo _t("روابط"); ?></span>
					</div>
					<div class="footer-col-body row pt-3">
						<ul class="list-unstyled col-6">
							<?php
							if (is_array($footer_menu)):
								$l = 1;
								foreach ($footer_menu as $link):
							?>
									<li><a href="<?php esc_html($link["link"]); ?>" target="<?php esc_html($link["target"]); ?>" class="text-white"><?php esc_html($link["title"]); ?></a></li>
							<?php
									if (($l % 5) == 0) {
										echo '</ul><ul class="list-unstyled col-6">';
									}
									$l++;
								endforeach;
							endif;
							?>
						</ul>
					</div>
				</div>
			</div>
			<!-- / Footer col -->

			<!-- Footer col -->
			<div class="col-md-4 col-sm-12">
				<div class="col-12">
					<div class="footer-col-title pb-3">
						<span class="h4 color-primary"><?php echo _t("تنبيه هام"); ?></span>
					</div>
					<div class="footer-col-body pt-3">
						<?php echo $bloc_footer_notice_footer_bloc[current_lang()]; ?>
					</div>
				</div>
			</div>
			<!-- / Footer col -->
		</div>
	</div>
	<div class="footer-sub bg-darker py-2">
		<div class="d-sm-flex container align-items-center">
			<p class="m-0 text-white mb-2 mb-sm-0 text-center text-sm-right"><?php echo sprintf(_t("تصميم و برمجة فريق %s %s"), "لاناس", date("Y")); ?></p>
			<div class="row ml-sm-auto m-0 justify-content-center">
				<?php
				if ($s_c_settings):
					foreach ($s_c_settings as $plat):
						$fa_icon = null;
						$icon_html = '';
						$plat_icon = (int) $plat["icon"];
						if (empty($plat_icon)) {
							$icon_html .= '<i class="' . $plat["icon"] . ' fa-lg"></i>';
						} else {
							$icon_html .= '<img src="' . get_thumb($plat_icon, ["w" => 38, "h" => 38]) . '" class="img-fluid"/>';
						}
				?>
						<div class="col social-account-link">
							<a href="<?php esc_html($plat["account_link"]); ?>" class="rounded-circle smooth-transition"><?php echo $icon_html; ?></a>
						</div>
				<?php endforeach;
				endif; ?>
			</div>
		</div>
	</div>
</footer>