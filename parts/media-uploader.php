<?php
if (is_login_in()) {
    $general_settings = @unserialize(get_settings("site_general_settings"));
    $site_max_upload = $general_settings["site_max_upload"] ?? MAX_UPLOAD_SIZE;
    $accept_extension = $general_settings["library_allowed_ext"];
    if (is_array($accept_extension)) {
        foreach ($accept_extension as $key => $ext) {
            $accept_extension[$key] = "." . $ext;
        }
    }
    $site_allowed_ext = @implode(",", $accept_extension) ?? "*";
    $get_files_categories = get_files_categories();
?>
    <!-- media uploader -->
    <div class="modal px-3 " id="mediaUploader" style="z-index:9999;">
        <div class="modal-dialog modal-full">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title"><?php echo _t("مكتبة الوسائط"); ?></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body p-0 overflow-hidden">
                    <div class="row m-0 h-100 w-100 position-absolute">
                        <div class="col-md-9 p-0 media-files">
                            <ul class="nav mb-3 border-bottom" id="media-library-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active p-2" id="media-library-tab" data-toggle="pill" href="#media-library" role="tab" aria-controls="media-library" aria-selected="true">
                                        <?php echo _t("مكتبة الوسائط"); ?>
                                    </a>
                                </li>
                                <?php if (user_authority()->upload == true): ?>
                                    <li class="nav-item">
                                        <a class="nav-link" id="upload-file-tab" data-toggle="pill" href="#upload-file" role="tab" aria-controls="upload-file" aria-selected="false">
                                            <?php echo _t("رفع ملفات"); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                            <div class="tab-content p-3" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="media-library" role="tabpanel" aria-labelledby="media-library-tab">
                                    <form action="" method="GET">
                                        <div class="d-flex">
                                            <div class="form-inline">
                                                <select name="file_category" id="select-file-category" class="custom-select form-control mr-2 rounded-0">
                                                    <option selected="true" disabled="true"><?php echo _t("إختر صنف الملف"); ?></option>
                                                    <option value=""><?php echo _t("الكل"); ?></option>
                                                    <?php foreach ($get_files_categories as $cat): ?>
                                                        <option value="<?php esc_html($cat["id"]); ?>"><?php esc_html(get_files_category_name($cat["category_title"])); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <select name="source" id="select-file-source" class="custom-select form-control rounded-0">
                                                    <option value="my"><?php echo _t("ملفاتي"); ?></option>
                                                    <option value="gallery"><?php echo _t("معرض"); ?></option>
                                                    <option value="trusted"><?php echo _t("معرض 2"); ?></option>
                                                </select>
                                            </div>
                                            <div class="ml-auto">
                                                <input type="text" class="form-control  rounded-0" placeholder="<?php echo _t(" إبحث عن ملف "); ?>" />
                                            </div>
                                        </div>
                                    </form>
                                    <div class="my-4"></div>
                                    <h5 class="text-danger text-center"><?php echo _t('إختر صورة مناسبة لموضوعك. إن لم تجد إرفع صورة جديدة'); ?></h5>
                                    <div class="media-library-explore">
                                        <div class="text-center w-100">
                                            <i class="fas fa-spinner fa-spin"></i>
                                            <?php echo _t("جاري التحميل ..."); ?>
                                        </div>
                                    </div>
                                    <div class="media-library-load-more">

                                    </div>
                                </div>
                                <?php if (user_authority()->upload == true): ?>
                                    <div class="tab-pane fade" id="upload-file" role="tabpanel" aria-labelledby="upload-file-tab">
                                        <div class="d-flex">
                                            <div class="bg-white shadow-lg rounded form-uploader mx-auto p-5 text-center">
                                                <h5><?php echo _t("للعثور على صورة مناسبة لموضوعك إستفد من هذا الموقع"); ?>&nbsp;<a href="https://pixabay.com/" target="_blank"><?php echo _t('هنا'); ?></a></h5>
                                                <i class="fas fa-cloud-upload-alt fa-4x"></i>
                                                <div class="my-3"></div>
                                                <h4 class="text-muted"><?php echo _t("إسحب ملفات لرفعها,أو"); ?></h4>
                                                <div class="my-2"></div>
                                                <form id="form-media" action="" method="post" enctype="multipart/form-data">
                                                    <div class="box__input position-relative w-100">
                                                        <input class="box__file position-absolute top-0 right-0 w-100 h-100" type="file" name="file" accept="<?php echo $site_allowed_ext; ?>" id="file" />
                                                        <span class="btn btn-primary w-25 btn-lg btn-upload"><?php echo _t("اختر ملف"); ?></span>
                                                    </div>
                                                    <div class="my-3"></div>
                                                    <select name="attachment_category" class="custom-select attachment_category">
                                                        <option selected="true" disabled="true"><?php echo _t("إختر صنف الملف"); ?></option>
                                                        <?php foreach ($get_files_categories as $cat): ?>
                                                            <option value="<?php esc_html($cat["id"]); ?>"><?php esc_html(get_files_category_name($cat["category_title"])); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <div class="upload-progress progress">
                                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                                                    </div>
                                                    <div class="my-5"></div>
                                                    <small class="text-muted"> <?php echo sprintf(_t('الحد الأقصى لرفع الملفات %s ميغا بابت. الإمتدادت المسموح بها %s'), $site_max_upload, $site_allowed_ext); ?> </small>
                                                    <p class="small text-muted"><?php echo _t("برفعك لهذا الملف فأنت توافق على"); ?>&nbsp;<a href="<?php echo get_terms_conditions_page()["link"]; ?>" class="color-link"><?php echo get_terms_conditions_page()["text"]; ?></a>.</p>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">...</div>
                            </div>
                        </div>
                        <div class="col-md-3 bg-light border-left py-3">
                            <div class="uploader-image-preview">
                                <h5 class="font-weight-bold border-bottom pb-3"><?php echo _t("تفاصيل المرفق"); ?></h5>
                                <div class="d-flex py-2 border-bottom">
                                    <div class="mr-3 w-25 prv-file-thumb">

                                    </div>
                                    <ul class="list-unstyled text-muted">
                                        <li class=""><b class="prv-file-name"></b></li>
                                        <li class="prv-file-dimension"></li>
                                        <li class="prv-file-update"></li>
                                        <li><a href="#" class="prv-file-id btn btn-sm btn-danger delete-media-file" data-id=""><?php echo _t("حدف الملف"); ?></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary add-media" data-media="" data-value=""><?php echo _t("إدارج الملف"); ?></button>
                </div>

            </div>
        </div>
    </div>
    <!-- / media uploader -->
<?php
}
?>