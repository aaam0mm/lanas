<?php
$action = $_GET["action"] ?? "";
?>
    <div class="dash-part-form">
        <div class="full-width">
            <?php
            if(!$action) {
                ?>
                    <div class="table-responsive">
                    <table class="table_parent">
                        <tr>
                            <th>إسم الدولة</th>
                            <th>علم الدولة</th>
                            <th>كود الدولة</th>
                            <th>الإجراءات</th> 
                        </tr>
                        <?php foreach(sort_json(get_countries(),"country_name","asc",M_L) as $country): ?>
                        <tr>
                            <td><?php esc_html($country["country_name"]); ?></td>
                            <td><img src="<?php echo get_thumb($country["country_flag"]); ?>" width="18" hegith="18"/></td>
                            <td><?php esc_html($country["country_code"]); ?></td>
                            <td>
                    <table class="table_child">
                    <tr>  
                        <td><button class="action_stg edit-st-btn open-url" data-url="dashboard/countries?action=edit&country_code=<?php esc_html($country["country_code"]); ?>" id=""><i class="fa fa-cog"></i></button></td>
						<td><button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=countries&id=<?php esc_html($country["id"]); ?>"><i class="fa fa-trash"></i></button></td> 
                    </tr>
                    </table>                     
                                
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    </div>
                <?php
            }elseif($action == "edit" || $action == "add") {
                if($action == "edit") {
                    $country_code = $_GET["country_code"];
                    $country = get_countries($country_code);
                }else{
                    $country = ["country_name" => "","country_code" => "","country_flag" => ""];
                }
                if($country) {
                    ?>
                    <form method="post" id="form_data">
                        <div class="full-width input-label-noline">
                            <label for="country_name">إسم الدولة</label>
                            <?php multi_input_languages("country_name","text",$country["country_name"]); ?>
                        </div>
                        <div class="cleae"></div>
                        <div class="full-width input-label-noline">
                            <label for="country_code">رمز الدولة</label>
                            <input type="text" name="country_code" placeholder="رمز الدولة" value="<?php esc_html($country["country_code"]); ?>"/>
                        </div>
                        <div class="clear"></div>
						<div class="full-width input-label-noline">
							<div class="up-upload-input">
								<input type="hidden" id="country_flag" name="country_flag" value="<?php esc_html($country["country_flag"]); ?>"/>
								<button class="upload-btn" data-input="#country_flag"><i class="fas fa-upload"></i>&nbsp;<span>تصفح ...</span></button>
								<div class="clear"></div>
									<div class="img-preview">
										<img src="<?php esc_html(get_thumb($country["country_flag"])); ?>" id="country_flag_prv"/>
								</div>							
							</div>
						</div>
                        <input type="hidden" name="method" value="countries"/>
						<?php if($action == "edit") { ?>
							<input type="hidden" name="country_id" value="<?php esc_html($country["id"]); ?>"/>
						<?php } ?>
						<input type="hidden" name="action" value="<?php esc_html($action); ?>"/>
						<button id="submit_form" class="saveData">أضف</button>
                    </form>
                    
                    <?php
                }else{
                    no_content();
                }
            }
            ?>
        </div>
    </div>