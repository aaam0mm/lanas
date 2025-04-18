<div class="dash-part members-part">
    <div class="dash-part-title">
        <h1>إعدادات الرتب</h1>
    </div>
    <div class="dash-part-form">
        <div class="full-width">
            <form method="post" id="form_data">
                <div class="full-width input-label-noline">
                    <label for="title">عنوان الصفحة</label>
                    <input type="text" name="title" placeholder="عنوان الصفحة"/>
                </div>
                <div class="full-width input-label-noline">
                   <label for="content">محتوى الصفحة</label>
                    <textarea name="content"></textarea>
                </div>
                <div class="r3-width">
                       <p>إظهار بالقائمة الجانبية</p>
                        <div class="col-s-setting">
                            <input type="checkbox" name="sidebar" id="checkbox1" class="ios-toggle" checked>
                            <label for="checkbox1" class="checkbox-label"></label>
                        </div>
                </div>
                <div class="r3-width">
                       <p>إظهار بالفوتر</p>
                        <div class="col-s-setting">
                            <input type="checkbox" name="footer" id="checkbox2" class="ios-toggle" checked>
                            <label for="checkbox2" class="checkbox-label"></label>
                        </div>
                </div>
            <input type="hidden" name="token_request" value="<?php esc_html($global_token_request); ?>"/>
            </form>
            <button id="submit_form" class="saveData">أضف</button>
        </div>
    </div>
</div>