<?php
$post_content = json_decode($post_content);
$research_titles = $post_content->titles;
$research_descs = $post_content->descs;
$research_source = json_decode(json_encode($post_content->source), true);
$research_content = [];

foreach($research_titles as $index=>$research_title) {
    foreach($research_source[$index + 1] as $key=>$val) {
         $research_source[$index + 1][$key]["count"] = $key;
         $research_source[$index + 1][$key]["page"] = $index + 1;
    }
	$research_content[] = ["title" => $research_title,"desc" => $research_descs[$index],"source" => array_values($research_source[$index + 1]) ,"count" => $index + 1];
}
$research_author = get_post_meta($post_id,"research_author");
$research_translator = get_post_meta($post_id,"research_translator");
$research_daralnasher = get_post_meta($post_id,"research_daralnasher");
?>
<div id="post-categoy-select2" class="form-group">
    <label for="post_category" class="font-weight-bold"><?php echo _t("فروع الأقسام"); ?></label>
    <select name="post_category[]" id="post_category" class="form-control" multiple>
		<?php foreach($cats as $category): ?>
		    <option value="<?php esc_html($category["main"]["id"]); ?>" <?php if(in_array($category["main"]["id"],$post_category)) { ?> selected="true" <?php } ?> >
		        <?php esc_html($category["main"]["cat_title"]); ?>
		    </option>
		    <?php foreach($category["sub"] as $cat_sub): ?>
		    <option value="<?php esc_html($cat_sub["id"]); ?>" <?php if(in_array($cat_sub["id"],$post_category)) { ?> selected="true" <?php } ?> >
		        - <?php esc_html($cat_sub["cat_title"]); ?>
		    </option>
		    <?php endforeach; ?>
		<?php endforeach; ?>
	</select>
    <div id="post-categoy-select2_error_txt" class="invalid-feedback d-block"></div>
</div>
<div class="form-group">
    <label for="post_title" class="font-weight-bold"><?php echo _t("عنوان الكتاب"); ?><sup class="text-danger">*</sup></label>
    <input type="text" name="post_title" id="post_title" class="form-control rounded-0" value="<?php esc_html($post_title); ?>" />
    <div id="post_title_error_txt" class="invalid-feedback"></div>
</div>

<div class="form-row">
    <div class="col-md-6">
        <div class="form-group ">
          <label class="font-weight-bold"><?php echo _t("إسم المؤلف"); ?><sup class="text-danger">*</sup></label>
          <input type="text" name="post_meta[research_author]" id="research_author" class="form-control rounded-0" value="<?php esc_html($research_author); ?>"/>
          <div id="research_author_error_txt" class="invalid-feedback"></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group ">
          <label class="font-weight-bold"><?php echo _t("إسم المترجم"); ?></label>
          <input type="text" name="post_meta[research_translator]" id="research_translator" class="form-control rounded-0" value="<?php esc_html($research_translator); ?>"/>
          <div id="research_translator_error_txt" class="invalid-feedback"></div>
         </div>
    </div>
</div>
<div class="form-group">
	<label class="font-weight-bold"><?php echo _t("دار النشر"); ?></label>
	<input type="text" name="post_meta[research_daralnasher]" class="form-control rounded-0" value="<?php esc_html($research_daralnasher); ?>"/>
</div>
<?php post_thumbnail_html( 'main-post-thumb','post_thumbnail', 'post_thumbnail',$post_thumbnail,_t("إضافة صورة"),$post_id ); ?>
<div class="form-group">
	<div id="get_researchs" class="p-2">
		<div class="form-group">
		    <label class="font-weight-bold"><?php echo _t("صفحات متتالية مثل الكتاب"); ?></label>
			<button class="btn btn-success rounded-0 btn-block add-research"><i class="fas fa-plus mr-2"></i><?php echo _t("إضافة صفحة أخرى"); ?></button>
		</div>
	</div>
</div>
<div class="form-group">
	<div id="post_research" class="form-control border-0 p-0"></div>
	<div id="post_research_error_txt" class="invalid-feedback"></div>
</div>
<script type="x-tmpl-mustache" id="tmpl-research">
{{#tmpl}}
<div class="research-page research-page-{{ count }} p-3 mb-3">
    <div class="slice-top">
        <div class="d-flex align-items-center">
            <h5 class="mb-0">{{ title }}</h5>
            <div class="ml-auto">
                <button class="btn btn-warning rounded-circle btn-sm remove-research-page" data-remove=".research-page-{{ count }}"><i class="fas fa-times fa-sm"></i></button>
            </div>
        </div>
    </div>
    <div class="my-3"></div>
    <div class="form-group">
        <input type="text" name="research[titles][]" value="{{ title }}" placeholder="<?php echo _t("عنوان الصفحة.. فإن لم يوجد عنوان تتركه فارغا, فتكون صفحة تابعة لما قبلها "); ?>" class="form-control rounded-0"/>
    </div>
    <div class="form-group">
        <textarea class="tinymce-area" id="tinymce-research-{{ count }}" name="research[descs][]">{{ desc }}</textarea>
    </div>
    <div class="research-append-source-{{ count }}">
        {{#source}}
        	<div class="form-row source-row form-group rsource-queue-{{ page }}-{{ count }}">
        		<div class="col-lg-6 col-sm-12">
        			<input type="text" name="research[source][{{ page }}][{{count}}][text]" value="{{ text }}" class="form-control rounded-0" placeholder="<?php echo _t("نص"); ?>"/>
        		</div>								
        		<div class="col-lg-6 col-sm-12 input-group">
        			<input type="text" name="research[source][{{ page }}][{{count}}][url]" value="{{ url }}" class="form-control rounded-0" placeholder="<?php echo _t("رابط المصدر"); ?>"/>
        			<button class="btn btn-warning rounded-circle btn-sm remove-slice ml-2" data-remove=".rsource-queue-{{ page }}-{{ count }}"><i class="fas fa-times fa-sm"></i></button>				
        		</div>
        	</div>
        {{/source}}
    </div>
	<button class="add-source-research btn btn-primary rounded-0 btn-block mb-3" data-page='{{count}}'><?php echo _t("أضف مصدر"); ?></button>
</div>
{{/tmpl}}
</script>
<script type="x-tmpl-mustache" id="tmpl-research-source">
    {{#tmpl}}
	<div class="form-row source-row form-group rsource-queue-{{ page }}-{{ count }}">
		<div class="col-lg-6 col-sm-12">
			<input type="text" name="research[source][{{ page }}][{{count}}][text]" value="{{ text }}" class="form-control rounded-0" placeholder="<?php echo _t("نص"); ?>"/>
		</div>								
		<div class="col-lg-6 col-sm-12 input-group">
			<input type="text" name="research[source][{{ page }}][{{count}}][url]" value="{{ url }}" class="form-control rounded-0" placeholder="<?php echo _t("رابط المصدر"); ?>"/>
			<button class="btn btn-warning rounded-circle btn-sm remove-slice ml-2" data-remove=".rsource-queue-{{ page }}-{{ count }}"><i class="fas fa-times fa-sm"></i></button>				
		</div>
	</div>
    {{/tmpl}}
</script>
<script>
$(document).ready(function() {
	var title = "";
	var data_research = {
		"tmpl" : <?php echo json_encode($research_content); ?>
		
	};
	var template = $('#tmpl-research').html();
	Mustache.parse(template);   // optional, speeds up future uses
	var rendered = Mustache.render(template,data_research);	
	$('#get_researchs').prepend(rendered);
	// Apply tinymce Editor to textarea elements
	$.each($(".research-page"),function(k,v) {
		var k = k+1;
	    $("#tinymce-research-"+k+"").add_tinymce();
	});
	
	$(document).on("click",".add-source-research",function(e) {
	   var page = $(this).data("page");
	   var count = $(".research-append-source-"+page+" .source-row").length + 1;
	   var template = $('#tmpl-research-source').html();
	   var rendered = Mustache.render(template,{
	       "tmpl" : {
	           count : count,
	           url : "",
	           text : "",
	           page : page
	       }
	   });	
	   $('.research-append-source-'+page+'').prepend(rendered);
	   e.preventDefault(); 
	});
	
});
</script>