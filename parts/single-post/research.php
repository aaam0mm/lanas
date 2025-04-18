<?php
$post_content = json_decode($post["post_content"]);
$research_titles = $post_content->titles;
$research_descs = $post_content->descs;
$research_source = json_decode(json_encode($post_content->source), true);
$research_author = get_post_meta($post["id"],"research_author");
$research_translator = get_post_meta($post["id"],"research_translator");
$research_daralnasher = get_post_meta($post["id"],"research_daralnasher");
?>
<div class="" style="overflow-x:hidden; overflow-y:auto; height:1000px;">
    <div id="container-flip" class="container-flip">
        <div class="menu-panel">
            <h3>✍📖💡</h3>
            <ul id="menu-toc" class="menu-toc">
                <!-- <li class="menu-toc-current"><a href="#item1">Self-destruction</a></li> -->
                <li><a href="#item1"></a></li>
				<?php foreach($research_titles as $index=>$research_title): ?>
                <li><a href="#item<?php echo $index + 1; ?>"><?php esc_html($research_title); ?></a></li>
				<?php endforeach; ?>
            </ul>
        </div>

        <div class="bb-custom-wrapper">
            <div id="bb-bookblock" class="bb-bookblock">
                <div class="bb-item" id="item-1">
                    <div class="content">
                        <div class="scroller text-center">
                            <h1><?php esc_html( $post["post_title"] ); ?></h1>
                            <p class="h4 text-center"><?php echo _t("مؤلف"); ?></p>
                            <p class="h4 text-center"><?php esc_html($research_author); ?></p>
                            <p class="h4 text-center"><?php echo _t("مترجم"); ?></p>
                            <p class="h4 text-center"><?php esc_html($research_translator); ?></p>
                            <p></p>
                            <p class="h4 text-center"><?php echo _t("دار النشر"); ?></p>
                            <p class="h4 text-center"><?php esc_html($research_daralnasher); ?></p>
                        </div>
                    </div>
                </div>
				<?php foreach($research_descs as $index=>$research_desc): ?>
                <div class="bb-item" id="item<?php echo $index + 2; ?>">
                    <div class="content">
                        <div class="scroller">
                            <h2><?php esc_html($research_titles[$index]); ?></h2>
							<?php echo validate_html($research_desc); ?>
							<div class="post-sources">
								<a class="text-primary h5 font-weight-bold border-bottom d-block pb-2" data-toggle="collapse" href="#collapseSources-<?php echo $index + 1; ?>" role="button" aria-expanded="false" aria-controls="collapseSources-<?php echo $index + 1; ?>"><i class="fas fa-share-square mr-2"></i><?php echo _t("المصادر"); ?><i class="fas fa-caret-down ml-2"></i></a>
								<div class="collapse show" id="collapseSources-<?php echo $index + 1; ?>">
									<?php
									if($research_source[$index + 1]):
									if(is_login_in()):
									if(user_authority()->read_sources):
									?>
									<ol class="list-unstyled ordered-source-list">
										<?php foreach($research_source[$index + 1] as $src):  ?>
										<li class="bg-light"><a href="<?php esc_html($src["url"]); ?>" target="_blank"><?php esc_html($src["text"]); ?></a></li>
										<?php endforeach; ?>
									</ol>
									<?php
									else:
										echo '<h4 class="text-danger">'._t("المعذرة ! رتبتك لا تسمح لك بالإطلاع على المصادر").'</h4>';
									endif;
									else:
										echo '<span class="text-danger">'._t("قم بتسجيل الدخول لكي تتمكن من رؤية المصادر").'</span>';
									endif;
									endif;
									?>
								</div>
							</div>
							
                        </div>
                    </div>
                </div>
				<?php endforeach; ?>
            </div>

            <nav>
                <span id="bb-nav-prev">&larr;</span>
                <span id="bb-nav-next">&rarr;</span>
            </nav>

            <span id="tblcontents" class="menu-button"><?php echo _t("صفحات"); ?></span>

        </div>

    </div>
    <!-- /container-flip -->
</div>