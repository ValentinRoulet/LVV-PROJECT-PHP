<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

$this->set_css($this->default_theme_path.'/flexiajax/css/flexigrid.css');
$this->set_js($this->default_javascript_path.'/jquery-1.7.1.min.js');
$this->set_js($this->default_theme_path.'/flexiajax/js/cookies.js');
$this->set_js($this->default_theme_path.'/flexiajax/js/flexigrid.js');
$this->set_js($this->default_theme_path.'/flexiajax/js/jquery.form.js');
$this->set_js($this->default_theme_path.'/flexiajax/js/jquery.numeric.js');

?>
<script type='text/javascript'>

    if(typeof(uniques_hash) == 'undefined' || uniques_hash === null)
    {
        var base_url = '<?php echo base_url();?>';
        var uniques_hash = {};
        var subjects = {};
        var unic_ids = {};
        var ajax_list_info_urls = {};
    }
    var unic_name = 'grid_<?php echo $unic_id?>';

    subjects[unic_name] = '<?php echo $subject?>';
    unic_ids[unic_name] = '<?php echo $unic_id?>';

    ajax_list_info_urls[unic_name] = '<?php echo $ajax_list_info_url?>/<?php echo $unic_id ?>';

    uniques_hash[unic_name] = '<?php echo $unique_hash; ?>';

    var message_alert_delete = "<?php echo $this->l('alert_delete'); ?>";
</script>
<div class="flexigrid" id="grid_<?php echo $unic_id?>" style='width: 100%;'>
    <div class='report-error report-div error'></div>
    <div class='report-success report-div success report-list' <?php if($success_message !== null){?>style="display:block"<?php }?>>
        <?php if($success_message !== null){?>
        <p><?php echo $success_message; ?></p>
        <?php }?>
    </div>
    <div class="mDiv">
        <div class="ftitle">
            &nbsp;
        </div>
        <div title="Minimize/Maximize Table" class="ptogtitle">
            <span></span>
        </div>
    </div>
    <div id='main-table-box'>
        <?php if(!$unset_add){?>
        <div class="tDiv">
            <div class="tDiv2">
                <a href='<?php echo $add_url.'/'.$unic_id?>' title='<?php echo $this->l('list_add'); ?> <?php echo $subject?>' class='add-anchor'>
                    <div class="fbutton">
                        <div>
                            <span class="add"><?php echo $this->l('list_add'); ?> <?php echo $subject?></span>
                        </div>
                    </div>
                </a>
                <div class="btnseparator">
                </div>
            </div>
            <div class='clear'></div>
        </div>
        <?php }?>
        <div class='ajax_list'>
            <?php echo $list_view?>
        </div>
        <?php echo form_open( $ajax_list_url.'/'.$unic_id, 'method="post" class="filtering_form" autocomplete = "off"'); ?>
        <div class="sDiv quickSearchBox">
            <div class="sDiv2">
                <?php echo $this->l('list_search');?>: <input type="text" class="qsbsearch_fieldox search_text" name="search_text" size="30">
                <select name="search_field" class="search_field">
                    <option value=""><?php echo $this->l('list_search_all');?></option>
                    <?php foreach($columns as $column){?>
                    <option value="<?php echo $column->field_name?>"><?php echo $column->display_as?>&nbsp;&nbsp;</option>
                    <?php }?>
                </select>
                <input type="button" value="<?php echo $this->l('list_search');?>" class="crud_search">
            </div>
            <div class='search-div-clear-button'>
                <input type="button" value="<?php echo $this->l('list_clear_filtering');?>" class="search_clear">
            </div>
        </div>
        <div class="pDiv">
            <div class="pDiv2">
                <div class="pGroup">
                    <div class="pSearch pButton quickSearchButton" title="<?php echo $this->l('list_search');?>">
                        <span></span>
                    </div>
                </div>
                <div class="btnseparator">
                </div>
                <div class="pGroup">
                    <select name="per_page" class="per_page">
                        <?php foreach($paging_options as $option){?>
                        <option value="<?php echo $option; ?>" <?php if($option == $default_per_page){?>selected="selected"<?php }?>><?php echo $option; ?>&nbsp;&nbsp;</option>
                        <?php }?>
                    </select>
                    <input type="hidden" name="order_by[0]" class="hidden-sorting" value="<?php if(!empty($order_by[0])){?><?php echo $order_by[0]?><?php }?>" />
                    <input type="hidden" name="order_by[1]" class="hidden-ordering"  value="<?php if(!empty($order_by[1])){?><?php echo $order_by[1]?><?php }?>"/>
                </div>
                <div class="btnseparator">
                </div>
                <div class="pGroup">
                    <div class="pFirst pButton first-button">
                        <span></span>
                    </div>
                    <div class="pPrev pButton prev-button">
                        <span></span>
                    </div>
                </div>
                <div class="btnseparator">
                </div>
                <div class="pGroup">
				<span class="pcontrol"><?php echo $this->l('list_page'); ?> <input name='page' type="text" value="1" size="4" class="crud_page">
                    <?php echo $this->l('list_paging_of'); ?>
                    <span class="last-page-number"><?php echo ceil($total_results / $default_per_page)?></span></span>
                </div>
                <div class="btnseparator">
                </div>
                <div class="pGroup">
                    <div class="pNext pButton next-button" >
                        <span></span>
                    </div>
                    <div class="pLast pButton last-button">
                        <span></span>
                    </div>
                </div>
                <div class="btnseparator">
                </div>
                <div class="pGroup">
                    <div class="pReload pButton ajax_refresh_and_loading">
                        <span></span>
                    </div>
                </div>
                <div class="btnseparator">
                </div>
                <div class="pGroup">
				<span class="pPageStat">
					<?php $paging_starts_from = "<span class='page-starts-from'>1</span>"; ?>
                    <?php $paging_ends_to = "<span class='page-ends-to'>". ($total_results < $default_per_page ? $total_results : $default_per_page) ."</span>"; ?>
                    <?php $paging_total_results = "<span class='total_items'>$total_results</span>"?>
                    <?php echo str_replace( array('{start}','{end}','{results}'),
                    array($paging_starts_from, $paging_ends_to, $paging_total_results),
                    $this->l('list_displaying')
                ); ?>
				</span>
                </div>
            </div>
            <div style="clear: both;">
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
