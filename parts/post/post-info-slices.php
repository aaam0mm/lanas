<?php
/**
 * post-slices.php
 * Add/Edit post slices input HTML markup
 */

if(!function_exists("post_slices_html")) {
    /**
     * post_slices_html()
     *
     */
    function post_slices_html($post_type, $post_id = null) {
        if(in_array($post_type, NO_SLICE)) {
            return;
        }

        // Define the build template
        $build_tpl = [
            // existing slice types...
            "link" => [
                "title" => true,
                "desc" => false,
                "image" => false,
            ],
            // existing slice types...
        ];

        // Define the slices metadata
        $slices = [
            // existing slices...
            "link" => [
                "title" => _t("رابط"),
                "icon" => "fas fa-link",
            ],
            // existing slices...
        ];

        ?>
        <div id="slice-app">
            <!-- add slices -->
            <div class="form-group slices-choose p-2">
                <label class="font-weight-bold"><?php echo _t("من هنا تستطيع إضافة شرائح للمحتوى المنشور"); ?></label>
                <button class="btn btn-green btn-show-slice form-control border-0 mb-3 collapsed" type="button" data-toggle="collapse" data-target="#slicesChoose" aria-expanded="false" aria-controls="collapseExample"></button>
                <div id="slicesChoose" class="collapse">
                    <div class="d-flex flex-wrap justify-content-center">
                        <?php foreach($slices as $slice_type=>$slice): ?>
                        <!-- slice -->
                        <div class="slice">
                            <div class="slice-area text-center add-slice" data-type="<?php echo $slice_type; ?>" data-title="<?php echo $slice["title"]; ?>">
                                <div class="slice-icon rounded-circle"><i class="<?php echo $slice["icon"]; ?>"></i></div>
                                <div class="slice-title"><span class="h6 font-weight-bold"><?php echo $slice["title"]; ?></span></div>
                            </div>
                        </div><!-- / slice -->
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- / add slices -->

            <div id="get_slices"></div>

            <?php foreach($build_tpl as $slice_name=>$tpl): ?>
                <script type="x-tmpl-mustache" id="tmpl-slice-<?php echo $slice_name; ?>">
                    {{#tmpl}}
                    <div class="slice-type-area slice-type-<?php echo $slice_name; ?> slice-type-<?php echo $slice_name; ?>-{{ slice_count }} p-3 mb-3">
                        <div class="slice-top">
                            <div class="d-flex align-items-center">
                                <h5 class="mb-0">{{ slice_title }}</h5>
                                <div class="ml-auto">
                                    <button class="btn btn-warning rounded-circle btn-sm remove-slice" data-remove=".slice-type-<?php echo $slice_name; ?>-{{ slice_count }}"><i class="fas fa-times fa-sm"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="my-3"></div>
                        <?php if($tpl["title"]): ?>
                        <div class="form-group">
                            <input type="text" name="slice[{{ slice_type }}][{{ slice_count }}][title]" value="{{ title }}" class="form-control rounded-0"/>
                        </div>
                        <?php endif; ?>
                        <?php if($slice_name == "link"): ?>
                        <div class="form-group">
                            <input type="text" name="slice[{{ slice_type }}][{{ slice_count }}][url]" value="{{ url }}" placeholder="<?php echo _t('أدخل الرابط هنا'); ?>" class="form-control rounded-0"/>
                        </div>
                        <?php endif; ?>
                        <?php
                        // existing code...
                        ?>
                        <input type="hidden" name="slice[{{ slice_type }}][{{ slice_count }}][slice_id]" value="{{ slice_id }}"/>
                    </div>
                    {{/tmpl}}
                </script>
            <?php endforeach; ?>

            <!-- Existing templates for vote options, quiz answers, etc. -->
        </div>

        <?php
        // Load existing slices and populate data
        $post_slices = load_post_slices($post_id);
        if($post_slices):
            $slices_data = $slices_types = $slice_count = [];
            $l = [];
            foreach($build_tpl as $slice_type=>$slice_settings) {
                $l[$slice_type] = 1; 
            }
            foreach($post_slices as $slice):
                $slices_types[] = $slice["type"];
                $slice_content = $slice["content"];
                
                // Load existing data for the link
                if ($slice["type"] === 'link') {
                    $slice_content->url = $slice_content->post_fetch_url; // assuming post_fetch_url is the URL field
                }
                
                // existing code to populate other slice content...

                $slices_data[][$slice["type"]]["tmpl"] = $slice_content;
                $l[$slice["type"]]++;
            endforeach;
        ?>
            <!-- Render slices -->
            <script>
            var slice_data = <?php echo json_encode($slices_data); ?>;
            var slices = <?php echo json_encode($slices_types); ?>;
            </script>
            <!-- / Render slices -->
        <?php
        endif;
    }
}
?>
