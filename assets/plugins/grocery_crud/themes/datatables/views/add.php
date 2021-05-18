<?php
    $this->set_css($this->default_theme_path.'/bootstrap/css/bootstrap/bootstrap.min.css');
    $this->set_css($this->default_theme_path.'/bootstrap/css/font-awesome/css/font-awesome.min.css');
    $this->set_css($this->default_theme_path.'/bootstrap/css/common.css');
    $this->set_css($this->default_theme_path.'/bootstrap/css/general.css');
    $this->set_css($this->default_theme_path.'/bootstrap/css/add-edit-form.css');

    if ($this->config->environment == 'production') {
        $this->set_js_lib($this->default_javascript_path.'/'.grocery_CRUD::JQUERY);
        $this->set_js_lib($this->default_theme_path.'/bootstrap/build/js/global-libs.min.js');
        $this->set_js_config($this->default_theme_path.'/bootstrap/js/form/add.js');
    } else {
        $this->set_js_lib($this->default_javascript_path.'/'.grocery_CRUD::JQUERY);
        $this->set_js_lib($this->default_theme_path.'/bootstrap/js/jquery-plugins/jquery.form.min.js');
        $this->set_js_lib($this->default_theme_path.'/bootstrap/js/common/common.min.js');
        $this->set_js_config($this->default_theme_path.'/bootstrap/js/form/add.js');
    }
include(__DIR__ . '/common_javascript_vars.php');
?>
<div class="crud-form" data-unique-hash="<?php echo $unique_hash; ?>">
    <div class="gc-container container">
                <div class="row">
                	<div class="col-md-12">
                        <?php echo form_open( $insert_url, 'method="post" id="crudForm"  enctype="multipart/form-data" class="form form-horizontal"'); ?>

                            <?php foreach($fields as $field) { ?>
                                <div class="form-group <?php echo $field->field_name; ?>_form_group">
                                    <label class="col-xs-3 control-label">
                                        <?php echo $input_fields[$field->field_name]->display_as; ?><?php echo ($input_fields[$field->field_name]->required) ? "<span class='required'>*</span> " : ""; ?>
                                    </label>
                                    <div class="col-xs-9">
                                        <?php echo $input_fields[$field->field_name]->input?>
                                    </div>
                                </div>
                            <?php }?>
                            <!-- Start of hidden inputs -->
                            <?php
                            foreach ($hidden_fields as $hidden_field) {
                                echo $hidden_field->input;
                            }
                            ?>
                            <!-- End of hidden inputs -->
                            <?php if ($is_ajax) { ?><input type="hidden" name="is_ajax" value="true" /><?php }?>

                            <div class='small-loading hidden' id='FormLoading'><?php echo $this->l('form_insert_loading'); ?></div>

                            <div class="form-group">
                                <div id='report-error' class='report-div error bg-danger' style="display:none"></div>
                                <div id='report-success' class='report-div success bg-success' style="display:none"></div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-7">
                                    <button class="btn btn-success b10" type="submit" id="form-button-save">
                                        <i class="fa fa-check"></i>
                                        <?php echo $this->l('form_save'); ?>
                                    </button>
                                    <?php 	if(!$this->unset_back_to_list) { ?>
                                        <button class="btn btn-default cancel-button b10" type="button" id="cancel-button">
                                            <i class="fa fa-warning"></i>
                                            <?php echo $this->l('form_cancel'); ?>
                                        </button>
                                    <?php }
                                    if(isset($this->add_custom_redirection)) {
                                        if (isset($this->add_custom_redirection[1])){
                                            foreach ($hidden_fields as $hidden_field) {
                                                if($hidden_field->name == $this->add_custom_redirection[1]){?>
                                                    <a href="<?php echo $this->add_custom_redirection[0] . $hidden_field->extras . $this->add_custom_redirection[2];?>">
                                                        <button class="btn btn-default" type="button">
                                                            <i class="fa fa-warning"></i>
                                                            <?php echo $this->l('form_cancel'); ?>
                                                        </button>
                                                    </a>
                                    <?php       }
                                            }
                                        } else {?>
                                            <a href="<?php echo $this->add_custom_redirection[0];?>">
                                                <button class="btn btn-default" type="button">
                                                    <i class="fa fa-warning"></i>
                                                    <?php echo $this->l('form_cancel'); ?>
                                                </button>
                                            </a>
                                    <?php }
                                    }?>
                                </div>
                            </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
    </div>
</div>
<script>
    var validation_url = '<?php echo $validation_url;?>';
    var list_url = '<?php echo $list_url;?>';

    var message_alert_add_form = "<?php echo $this->l('alert_add_form');?>";
    var message_insert_error = "<?php echo $this->l('insert_error');?>";
</script>
