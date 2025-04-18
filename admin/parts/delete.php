<?php
$id = $_GET["id"] ?? "";
$type = $_GET["type"] ?? "";

if (empty($id) || empty($type)) {
	exit(0);
}
/** Show notices to admin */
$notices = [];
if ($type == "users") {
	$notices[] = "سيتم حدف المستخدم(مين) و كل مواضيع غير موثوقة و سيتم نقل المواضيع الموثوقة إلى الأدمن الرئيسي.";
} elseif ($type == "files") {
	$notices[] = "سيتم حدف الملف المرجو إنتباه أن لا يكون مستعمل في الموقع.";
} elseif ($type == "roles") {
	$notices[] = "المرجو إختيار الرتبة التي سيتم تحويل لها المستخدمين الحاملين للرتبة المراد حدفها";
} elseif ($type == "file_category" || $type == "categories") {
	$notices[] = "المرجو إختيار القسم الذي سيتم نقل له الملفات الموجودة بالقسم مراد حدفه";
} elseif ($type == "posts") {
	$notices[] = "سيتم حدف المشاركة و كل ملفات المرفقة معها.";
} elseif ($type == "link") {
	$notices[] = "سيتم حدف الرابط و كل المشاركات و ملفات المرفقة معه.";
} elseif ($type == "authors") {
	$notices[] = "سيتم حدف المؤلف و من جميع الكتب المضافة اليه.";
} elseif ($type == "boot_comments") {
	$notices[] = "سيتم حدف التعليق و من جميع البوتات المضافة اليه.";
}
?>
<div class="dash-part seo-part">
	<div class="dash-part-title">
		<h1>حدف</h1>
	</div>
	<div class="dash-part-form">
		<div class="danger-text">
			<h3>تحذير ! أنت على وشك حذف محتوى</h3>
		</div>
		<div class="notices">
			<ul>
				<?php foreach ($notices as $notice): ?>
					<li><?php esc_html($notice); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="clear"></div>
		<form action="" method="POST">
			<?php if ($type == "roles"): ?>
				<select name="new_role">
					<option value="" selected="" disabled="">إختر رتبة</option>
					<?php
					$get_roles = $dsql->dsql()->table('roles_permissions')->where('id', 'NOT IN', $id)->get();
					if (is_array($get_roles)):
						foreach ($get_roles as $role):
							$role_title = json_decode($role["role_title"]);
						?>
							<option value="<?php esc_html($role["id"]); ?>"><?php echo $role_title->{M_L} ?></option>
					<?php
						endforeach;
					endif;
					?>
				</select>
			<?php elseif ($type == "file_category"): ?>
				<select name="new_category">
					<option value="" selected="" disbaled="">إختر قسم</option>
					<?php
					$get_file_cats = $dsql->dsql()->table('files_categories')->where('id', '!=', $id)->get();
					if (is_array($get_file_cats)):
						foreach ($get_file_cats as $file_cat):
							$cat_title = json_decode($file_cat["category_title"]);
					?>
							<option value="<?php esc_html($file_cat["id"]); ?>"><?php echo $cat_title->{M_L} ?></option>
					<?php
						endforeach;
					endif;
					?>
				</select>
			<?php elseif ($type == "categories"): ?>
				<select name="new_category">
					<option value="" selected="" disbaled="">إختر قسم</option>
					<?php
					$get_taxonomy = get_category_field($id, 'cat_taxonomy');
					$get_cats = $dsql->dsql()->table('categories')->where('id', '!=', $id)->where('cat_taxonomy', $get_taxonomy)->get();
					if (is_array($get_cats)):
						foreach ($get_cats as $cat):
					?>
							<option value="<?php esc_html($cat["id"]); ?>"><?php echo $cat["cat_title"]; ?></option>
					<?php
						endforeach;
					endif;
					?>
				</select>
			<?php endif; ?>
			<input type="submit" name="delete" value="حدف" />
			<a href="" class="go_back btn btn-danger mt-2"><i class="fas fa-times mr-2"></i>إلغاء</a>
		</form>
		<?php
		$response = [];
		if (isset($_POST["delete"])) {
			$delete = new Delete($id);
			if ($type == "users") {
				if ($delete->delete_users()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "files") {
				if ($delete->delete_files()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "ads") {
				if ($delete->delete_ads()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "badges") {
				if ($delete->delete_badges()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "roles") {
				$new_role = $_POST["new_role"];
				if ($delete->delete_roles($new_role)) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "file_category") {
				$new_category = $_POST["new_category"];
				if ($delete->delete_file_category($new_category)) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "categories") {
				$new_category = $_POST["new_category"];
				if ($delete->delete_categories($new_category)) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "social_account") {
				$get_socialaccounts_settings = @unserialize(get_settings("s_c_settings"));
				if (isset($get_socialaccounts_settings[$id])) {
					unset($get_socialaccounts_settings[$id]);
					$new = @serialize($get_socialaccounts_settings);
					if (update_meta_settings("s_c_settings", $new)) {
						$response["success"] = "تم حدف بنجاح.";
					} else {
						$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
					}
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "page") {
				if ($delete->delete_pages()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "posts") {
				if ($delete->delete_posts()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "countries") {
				if ($delete->delete_countries()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "external_links") {
				if ($delete->delete_external_links()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "languages") {
				if ($delete->delete_lang()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "contact_form") {
				if ($delete->delete_contact_form()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "complain") {
				if ($delete->delete_complain()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "link") {
				if ($delete->delete_link()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "authors") {
				if ($delete->delete_authors()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "boot_comments") {
				if ($delete->delete_boot_comments()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($type == "boots") {
				if ($delete->delete_boots()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			}

			if (is_null($delete->errors())) {
		?>
				<div class="alert alert-success mt-3"><?php echo _t("تم حدف بنجاح"); ?></div>
				<?php
			} else {
				foreach ($delete->errors() as $error) {
				?>
					<div class="alert alert-danger mt-3">
						<?php echo $error["error"]; ?>
					</div>
		<?php
				}
			}
		}
		?>
	</div>
</div>