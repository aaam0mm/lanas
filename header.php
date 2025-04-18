<?php
$header_bloc = extract(switch_blocs("header"));
?>
<!-- Header -->
<div id="header">
	<?php 
	include 'header-top.php' 
	?>
	<!-- second part -->
	<div class="position-relative header-second bg-dark py-4">		
		<div class="container d-sm-flex">
			<div class="logo-header w-25">
				<a href="#"><img src="<?php echo get_thumb($site_logo_header_bloc,null,false); ?>" class="img-fluid"/></a>
			</div>
			<div class="header-text ml-auto align-items-center d-sm-flex">
				<span class="color-primary font-weight-bold h4"><?php esc_html($header_bloc_text_header_bloc[current_lang()] ?? ""); ?></span>
			</div>
		</div>
	</div>
	<div class="header-search-bar bg-dark py-2">
	   <div class="container">
		<div class="d-sm-flex position-relative">
			<div class="add-new-btn-div d-none d-sm-block">
				<button class="add-new-post btn bg-green <?php if(!is_login_in()) { echo 'login-modal'; } ?> "><?php echo _t("أضف موضوعا"); ?></button>
			</div>
			<div class="search-form ml-auto">
				    <form action="search.php" method="get" id="searchForm" class="form-inline">
					<div class="input-group form-search">
						<select name="post_type" class="form-control custom-select select-category-search">
							<option value=""><?php echo _t("إختر قسما"); ?></option>
							<?php foreach(get_all_taxonomies() as $search_taxo): ?>
								<option value="<?php esc_html($search_taxo['taxo_type']); ?>"><?php esc_html( get_taxonomy_title($search_taxo) ); ?></option>
							<?php endforeach; ?>
						</select>
						<input type="text" name="s" class="form-control header-search-field search-ajax w-75"/>
						<button class="btn bg-primary form-control text-white"><i class="fas fa-search"></i></button>
					</div>
					</form>
				<!-- instant search -->
				<div class="instant-search-box bg-white position-absolute right-0 w-100 shadow-lg p-2">
					<h4 class="border-bottom pb-2"><?php echo _t("نتائج البحث"); ?></h4>
					<!-- instant results -->
					<div class="instant-results">
					</div><!-- instant results -->	
					<a href="" class="btn btn-light btn-block rounded-0 w-25 mx-auto explore-search-results"><?php echo _t("عرض نتائج البحث"); ?></a>
				</div>			
				<!-- /instant search -->
			</div>
		</div>
		</div>
	</div><!-- / second part -->
</div>