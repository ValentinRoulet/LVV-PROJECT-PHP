<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>
<table cellpadding="0" cellspacing="0" border="0" class="display groceryCrudTable datatable table table-bordered table-striped" id="datatable" >
	<thead>
		<tr>
			<?php foreach($columns as $column){?>
				<th><?php echo $column->display_as; ?></th>
			<?php }?>
			<?php if(!$unset_delete || !$unset_edit || !$unset_read || !empty($actions)){?>
			<th class='actions'><?php echo $this->l('list_actions'); ?></th>
			<?php }?>
		</tr>
	</thead>
	<tbody>
		<?php foreach($list as $num_row => $row){ ?>
		<tr id='row-<?php echo $num_row?>'>
			<?php foreach($columns as $column){?>
				<td><?php echo $row->{$column->field_name}?></td>
			<?php }?>
			<?php if(!$unset_delete || !$unset_edit || !$unset_read || !empty($actions)){?>
			<td class='actions'>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                    Action &nbsp; &nbsp; &nbsp;<i class="fa fa-bars"></i>
                                </button>
                                <ul class="dropdown-menu" role="menu">
				<?php if(!$unset_read){?>
                                    <li><a href="<?php echo $row->read_url?>">
                                        <i class="fa fa-eye"></i>
                                        <span class="ui-button-text">&nbsp;<?php echo $this->l('list_view'); ?></span>
                                    </a></li>
				<?php }?>

				<?php if(!$unset_edit){?>
                                    <li><a href="<?php echo $row->edit_url?>">
						<i class="fa fa-pencil"></i>
						<span class="ui-button-text">&nbsp;<?php echo $this->l('list_edit'); ?></span>
                                    </a></li>
				<?php }?>
				<?php if(!$unset_delete){?>
                                    <li><a onclick = "javascript: return delete_row('<?php echo $row->delete_url?>', '<?php echo $num_row?>')"
						href="javascript:void(0)" >
						<span class="ui-button-icon-primary ui-icon ui-icon-circle-minus"></span>
						<span class="ui-button-text">&nbsp;<?php echo $this->l('list_delete'); ?></span>
                                    </a></li>
				<?php }?>
                                <?php
				if(!empty($row->action_urls)){
					foreach($row->action_urls as $action_unique_id => $action_url){
	$action = $actions[$action_unique_id];
						if($action->label == 'Archiver'){?>
                        	<li><a href="#" onClick="archiver('<?php echo $action_url;?>')"><i class="<?php echo $action->css_class; ?>"></i>
				 <span class="ui-button-text">&nbsp;<?php echo $action->label?></span>
                 			</a></li>
                        <?php } else if($action->label == 'D&eacute;sarchiver'){?>
                        	<li><a href="#" onClick="desarchiver('<?php echo $action_url;?>')"><i class="<?php echo $action->css_class; ?>"></i>
				 <span class="ui-button-text">&nbsp;<?php echo $action->label?></span>
                 			</a></li>
                        <?php } else {?>
						<li><a href="<?php echo $action_url; ?>">
							<i class="<?php echo $action->css_class; ?>"></i>
							<span class="ui-button-text">&nbsp;<?php echo $action->label?></span>
						</a></li>
<?php					}
					}
				}
				?>
                                </ul>
                            </div>
			</td>
			<?php }?>
		</tr>
		<?php }?>
	</tbody>
       
	<tfoot>
		<tr> <!--
                    <?php foreach($columns as $column){?>
                    <th>
                        <div class="input-group">
                            <span class="input-group-addon"><i class='fa fa-search fa-1'></i></span>
                            <input type="text" style="width: 100%;" name="<?php echo $column->field_name; ?>" class="form-control"/>
                        </div>
                    </th>
                    <?php }?>
                    <?php if(!$unset_delete || !$unset_edit || !$unset_read || !empty($actions)){?>
			<th>
                            <a href="javascript:void(0)" class="clear-filtering btn btn-default">
                                <i class="fa fa-eraser"></i>&nbsp;
                                <span class="ui-button-text"><?php echo $this->l('list_clear_filtering');?></span>
                            </a>
			</th>
                    <?php }?>-->
		</tr>
	</tfoot>
</table>
