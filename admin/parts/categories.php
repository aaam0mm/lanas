<?php

/**

 */
$action = $_GET["action"] ?? "";
$taxo = $_GET["taxo"] ?? "article";
?>
<div class="dash-part-form">
	<?php if (!$action): ?>
		<form action="" method="get" id="form_filter">
			<div class="page-action">
				<div class="pull-right">
					<a href="dashboard/categories?section=add_categories&action=add" id="btn_link">إضافة</a>
				</div>
				<div class="pull-left">
					<div class="line-elm-flex">
						<div class="7r-width">
							<select name="taxo" class="on_change_submit">
								<?php foreach (get_all_taxonomies() as $taxos) { ?>
									<option value="<?php echo $taxos["taxo_type"]; ?>" <?php selected_val($taxos["taxo_type"], $taxo); ?>><?php echo json_decode($taxos["taxo_title"])->{M_L}; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</form>
		<div class="clear"></div>
		<div class="table-responsive">
			<table class="table_parent">
				<tr>
					<th></th>
					<th>القسم</th>
					<th>الصنف</th>
					<th>اللغة</th>
					<th>الإجراءات</th>
				</tr>
				<?php
				foreach (get_categories($taxo) as $cat_info_k => $cat_info_v) {
					$cat_id = $cat_info_v["id"];
					$cat_title = $cat_info_v["cat_title"] ?? "";
					$cat_taxonomy = $cat_info_v["cat_taxonomy"] ?? "";
					$cat_lang = $cat_info_v["cat_lang"] ?? "";
					$cat_settings = @unserialize($cat_info_v["cat_settings"]);
					$icon_visible = 'fa-eye-slash';
					if ($cat_settings["visible"] == "yes") {
						$icon_visible = 'fa-eye';
					}
				?>
					<tr>
						<td></td>
						<td><?php esc_html($cat_title); ?></td>
						<td><?php esc_html($cat_taxonomy); ?></td>
						<td><?php esc_html($cat_lang); ?></td>
						<td>
							<table class="child_table">
								<tr>
									<td><button class="action_stg edit-st-btn open-url" title="تعديل" data-url="dashboard/categories?action=edit&cat_id=<?php esc_html($cat_id); ?>" id=""><i class="fa fa-cog"></i></button></td>
									<td><button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=categories&id=<?php esc_html($cat_id); ?>"><i class="fa fa-trash"></i></button></td>
									<td><button class="action_stg btn-warning updateData btn-category-visibility" data-id="<?php esc_html($cat_id); ?>" data-method="category_visibility"><i class="fa <?php echo $icon_visible; ?>"></i></button></td>
								</tr>
							</table>
						</td>
					</tr>
				<?php
				}
				?>
			</table>
		</div>
	<?php
	endif;

	if ($action == "edit" || $action == "add"):
		$cat_id = $_GET["cat_id"] ?? "";
		if ($cat_id) {
			$get_cat = get_categories(null, $cat_id);
		}
		$cat_title = $get_cat["cat_title"] ?? "";
		$cat_taxonomy = $get_cat["cat_taxonomy"] ?? "";
		$cat_lang = $get_cat["cat_lang"] ?? "";
		$cat_keywords = $get_cat["cat_keywords"] ?? "";
		$cat_settings = isset($get_cat["cat_settings"]) ? @unserialize($get_cat["cat_settings"]) : [];
		$cat_parent = $get_cat["cat_parent"] ?? "";
		if (is_array($cat_settings)) {
			extract($cat_settings);
		}
		// category settings input
		$visible_to_s = $visible_to ?? "all";
		$sort_s = $sort ?? "new";
	?>
		<div class="r7-width">
			<form method="post" id="form_data">
				<div class="full-width">
					<select name="category_lang" class="lang_select_cat">
						<option selected="true" disabled="true">إختر لغة القسم</option>
						<?php
						foreach (get_langs() as $lang_k => $lang_v) {
							$lang_code = $lang_v["lang_code"];
							$lang_name = $lang_v["lang_name"];
						?>
							<option value="<?php esc_html($lang_code); ?>" <?php selected_val($lang_code, $cat_lang); ?>><?php echo $lang_name; ?></option>
						<?php
						}
						?>
					</select>
				</div>
				<div class="full-width">
					<label for="title">إسم القسم</label>
					<input type="text" name="category_name" placeholder="إسم القسم" value="<?php esc_html($cat_title); ?>" />
				</div>

				<div class="full-width">
					<label for="keywords">الكلمات المفتاحية</label>
					<input type="text" name="keywords" placeholder="كلمات مفتاحية" value="<?php esc_html($cat_keywords); ?>" />
				</div>
				<div class="full-width">
					<label for="">إختر تصنيف</label>
					<select name="cat_taxonomy" id="cat_taxonomy">
						<option selected="" disabled="">إختر صنف</option>
						<?php
						foreach (get_all_taxonomies() as $taxo_key => $taxo_value) {
							$taxo_id = $taxo_value["id"];
							$taxo_title = json_decode($taxo_value["taxo_title"]);
							$taxo_type = $taxo_value["taxo_type"];
						?>
							<option value="<?php esc_html($taxo_type); ?>" <?php selected_val($taxo_type, $cat_taxonomy); ?>><?php esc_html(get_taxonomy_title($taxo_type, "ar")); ?></option>
						<?php
						}
						?>
					</select>
				</div>
				<div class="full-width">
					<label for="">إختر قسم أو أتركه قسم رئيسي</label>
					<select name="parent" id="cat_parent">
						<option value=""></option>
						<?php
						if ($action == "edit") {
							$load_categories = get_categories($cat_taxonomy);
							if ($load_categories) {
								foreach ($load_categories as $cat_k => $cat_v) {
									$cat_id_cat = $cat_v["id"];
									$cat_title_cat = $cat_v["cat_title"];
						?>
									<option value="<?php esc_html($cat_id_cat); ?>" <?php if ($cat_parent == $cat_id_cat) { ?> selected="" <?php  } ?>><?php esc_html($cat_title_cat); ?></option>
						<?php
								}
							}
						}
						?>
					</select>
				</div>
				<div class="full-width categories-adv-s">
					<label for="sort">ترتيب المواضيع في العرض</label>
					<div class="full-width line-elm-flex">
						<input type="radio" name="category_setting[sort]" value="latest" <?php if ($sort_s == "latest") {
																																								echo 'checked="true"';
																																							} ?> /><span class="mr-3">جديد</span>
						<input type="radio" name="category_setting[sort]" value="random" <?php if ($sort_s == "random") {
																																								echo 'checked="true"';
																																							} ?> /><span>عشوائيا</span>
					</div>
				</div>
				<input type="hidden" name="method" value="add_edit_category" />
				<input type="hidden" name="action" value="<?php esc_html($action); ?>" />
				<input type="hidden" name="cat_id" value="<?php esc_html($cat_id); ?>" />
				<input type="hidden" name="token_request" value="<?php esc_html($global_token_request); ?>" />
				<button id="submit_form" class="saveData">أضف</button>
			</form>
		</div>
		<script>
			$(document).ready(function() {
				$("#cat_taxonomy").on("change", function() {
					var taxo_type = $(this).val();
					$.get(gbj.siteurl + "/ajax/ajax-html.php", {
						path: "cat_taxonomies",
						taxo_type: taxo_type
					}, function(data) {
						$("#cat_parent").html('<option value="0"></option>' + data);
					});
				});
			});
		</script>
	<?php endif; ?>
</div>