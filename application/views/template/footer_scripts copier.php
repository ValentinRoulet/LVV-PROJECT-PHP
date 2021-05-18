<?php
foreach ($scripts as $script) {
    //----------------------------------------------------------------------------------------//
    //------------------------------------JQUERY----------------------------------------------//
    //----------------------------------------------------------------------------------------//
    if ($script == 'jquery3'){
        echo "<script src='".base_url('/assets/plugins/jquery/js/jquery-3.2.1.min.js')."'></script>";
    } else if ($script == 'jquery2'){
        echo "<script src='".base_url('/assets/plugins/jquery/js/jquery-2.1.4.min.js')."'></script>";
    } else if ($script == 'jquery1'){
        echo "<script src='".base_url('/assets/plugins/jquery/js/jquery-1.11.1.min.js')."'></script>";
    } /*else if ($script == 'jquery4'){
        echo "<script src='".base_url('/assets/plugins/jquery/js/jquery-1.8.3.min.js')."'></script>";
    }*/
    
    
	
	if ($script == 'jqueryui'){
		echo "<script src='".base_url('/assets/plugins/jquery/ui/jquery-ui.js')."'></script>";
	}
	
	if($script == 'jqscroll'){
	    echo "<script src='".base_url('/assets/plugins/jquery/ui/jquery.ui.autocomplete.scroll')."'</script>";
	    echo "<script src='".base_url('/assets/plugins/jquery/ui/jquery.ui.autocomplete.scroll.min')."'</script>";
	}
	
	//----------------------------------------------------------------------------------------//
    //------------------------------------SELECT2----------------------------------------------//
    //----------------------------------------------------------------------------------------//
	
	if ($script == 'select2') {
        echo "<script src='" . base_url('/assets/plugins/select2/js/select2.min.js') . "'></script>";
    }  
	//----------------------------------------------------------------------------------------//
    //--------------------------------------CHOSEN--------------------------------------------//
    //----------------------------------------------------------------------------------------//
    else if ($script == 'chosen'){
		echo "<script src='".base_url('/assets/plugins/grocery_crud/js/jquery_plugins/jquery.chosen.min.js')."'></script>
			  <script src='".base_url('/assets/plugins/grocery_crud/js/jquery_plugins/config/jquery.chosen.config.js')."'></script>";
		if (isset($custom_chosen)){
			echo $custom_chosen;
		}
    } 
    
    //----------------------------------------------------------------------------------------//
    //-------------------------------------BOOTSTRAP------------------------------------------//
    //----------------------------------------------------------------------------------------//
    else if ($script == 'bootstrap'){
		echo "<script src='".base_url('/assets/plugins/bootstrap/js/bootstrap.min.js')."'></script>";
    } 
	
	 if ($script == 'wysihtml5') {
        echo "<script src='" . base_url('/assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') . "'></script>";
    } 
	
	//----------------------------------------------------------------------------------------//
    //------------------------------------AUTOHEIGHT------------------------------------------//
    //----------------------------------------------------------------------------------------//
    else if ($script == 'autoheight'){
		echo "<script src='".base_url('/assets/plugins/autoheight/jquery-iframe-auto-height.min.js')."'></script>";
		echo "<script src='".base_url('/assets/plugins/autoheight/jquery.browser.min.js')."'></script>";
    } 
	
	//----------------------------------------------------------------------------------------//
    //-------------------------------------LTE-ADMIN------------------------------------------//
    //----------------------------------------------------------------------------------------//
    else if ($script == 'lte'){
		echo "<script src='".base_url('/assets/plugins/lteadmin/js/app.min.js')."'></script>";
                echo "<script src='".base_url('/assets/plugins/input-mask/jquery.inputmask.js')."'></script>";
		echo "<script src='".base_url('/assets/plugins/input-mask/jquery.inputmask.extensions.js')."'></script>";
		echo "<script src='".base_url('/assets/plugins/input-mask/jquery.inputmask.date.extensions.js')."'></script>";
    }
	
	//----------------------------------------------------------------------------------------//
    //--------------------------------------LAZYLOAD------------------------------------------//
    //----------------------------------------------------------------------------------------//
    else if ($script == 'lazyload'){
		echo "<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/lazysizes/1.3.1/lazysizes.min.js'></script>";
    } 
	
    //----------------------------------------------------------------------------------------//
    //------------------------------------AUTOCOMPLETE----------------------------------------//
    //----------------------------------------------------------------------------------------//
    else if ($script == 'autocomplete'){
		echo "<script src='".base_url('/assets/plugins/autocomplete/js/jquery.tokeninput.js')."'></script>";
        foreach ($autocomplete as $item){
            echo "<script>
                $(function() {
                $( '#".$item[0]."' ).tokenInput('".base_url('/index.php/autocomplete/Autocomplete')."',{
                    tokenLimit : ".$item[2].",
                    hintText : 'Tapez pour rechercher',
                    noResultsText : \"<span class='pas-de-resultat'>Pas de r&eacute;sultats <i class='fa fa-exclamation'></i></span><a href='http://localhost/sapadv1/framework/index.php/sapad/C_enseignant/index/add' target='_blank'>test</a><button class='btn btn-info pull-right' onclick='ajouter_ville()' ><i class='fa fa-plus'></i></button>\",
                    searchingText : 'Recherche..',
                    animateDropdown : false});
                });</script>";
        }
    } 
    //----------------------------------------------------------------------------------------//
    //------------------------------------CLOCKPCIKER----------------------------------------//
    //----------------------------------------------------------------------------------------//
    //Passer variable $data['clocks'] en array avec le liste des id de champs à utiliser pour le clock//
    else if ($script == 'clockpicker'){
        echo "<script type='text/javascript' src='".base_url('/assets/plugins/clockpicker/dist/bootstrap-clockpicker.min.js')."'></script>"; 
        foreach ($clocks as $clock){
        echo "<script>
                    $( '#".$clock."' ).clockpicker( {
                    placement: 'top', // clock popover placement
                    align: 'bottom', // popover arrow align
                    donetext: 'Valider', // done button text
                    autoclose: true,
                    vibrate: true // vibrate the device when dragging clock hand
                    } )
	</script>";
    }
    }
    //----------------------------------------------------------------------------------------//
    //---------------------------------------DATATABLES---------------------------------------//
    //----------------------------------------------------------------------------------------//
    else if ($script == 'datatables' or $script == 'datatables_ajax'){
		echo "<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/JSZip-2.5.0/jszip.min.js')."'></script>
		 	<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/pdfmake-0.1.18/build/pdfmake.min.js')."'></script>
	  		<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/pdfmake-0.1.18/build/vfs_fonts.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/DataTables-1.10.13/js/jquery.dataTables.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/DataTables-1.10.13/js/dataTables.bootstrap.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/Buttons-1.2.4/js/dataTables.buttons.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/Buttons-1.2.4/js/buttons.bootstrap.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/Buttons-1.2.4/js/buttons.colVis.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/Buttons-1.2.4/js/buttons.flash.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/Buttons-1.2.4/js/buttons.html5.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/Buttons-1.2.4/js/buttons.print.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/ColReorder-1.3.2/js/dataTables.colReorder.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/FixedColumns-3.2.2/js/dataTables.fixedColumns.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/FixedHeader-3.1.2/js/dataTables.fixedHeader.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/KeyTable-2.2.0/js/dataTables.keyTable.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/Responsive-2.1.0/js/dataTables.responsive.min.js')."'></script>
                        
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/Responsive-2.1.0/js/responsive.bootstrap.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/RowReorder-1.2.0/js/dataTables.rowReorder.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/Scroller-1.4.2/js/dataTables.scroller.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/Select-1.2.0/js/dataTables.select.min.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/date-eu.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/date-uk.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/num-html.js')."'></script>
			<script type='text/javascript' src='".base_url('/assets/plugins/datatables/src/RowGroup-1.0.0/js/dataTables.rowGroup.min.js')."'></script>
                        <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js'></script>
                        <script type='text/javascript' src='https://cdn.datatables.net/plug-ins/1.10.16/sorting/datetime-moment.js'></script>";?>
			<script>
				
				
				
				
			<?php 
			(!isset($pdf_export)) ? $pdf_export = '' : '';
			(!isset($sort_col)) ? $sort_col = '' : '';
			(!isset($nb_show)) ? $nb_show = '25' : '';
			(!isset($script)) ? $script = '' : '';
			(!isset($text)) ? $text = "'print'" : '';
			(!isset($format)) ? $format = "
						{
							extend: 'print',
							text: 'Imprimer',
							message: '',
							footer:true,
							
						},
			" : '';
                        
                        if ($script == 'datatables')
                        {    
			?>          
			$(document).ready(function() {
				
				//change les br en /n/r
				var fixNewLine = {
        exportOptions: {
            format: {
                body: function ( data, column, row ) {
                    return column === 5 ?
                        data.replace( /<br\s*\/?>/gi, '"'+"\r\n"+'"' ) :
                        data;
                }
            }
        }
    };
                moment.locale('fr');
                $.fn.dataTable.moment( 'DD/MM/YYYY' );
				
				$('.datatable').DataTable( {
                                        "autoWidth": true,
					"iDisplayLength": <?php echo $nb_show;?>,
					"stateSave": true,
					"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Tous"]],
					"deferRender": true,
					"scrollX": true,
					
					<?php if(isset($custom_datatables)){
						echo $custom_datatables;
					}?>
					
					dom: 'Blfrtip',
					<?php echo $sort_col ;?>
					buttons: [
						{
							extend: 'excelHtml5',
							exportOptions: {
								columns: [ 0, ':visible' ]
							}
						},
						//{
						//	extend: 'pdfHtml5',
						//	download: 'open',
						//	exportOptions: {
						//		columns: [ 0, ':visible' ]
						//	},
						//	<?php echo $pdf_export ;?>
						//},
						{
							extend: 'copyHtml5',
							text: 'Copier',
							//exportOptions: {
							//	columns: [ 0, ':visible' ]
							//}
						},
						{
							extend: 'colvis',
							text: 'Colonnes',
						},
						<?php echo $format ;?>
					],
					
					language: {
                                                
						buttons: {
							copyTitle: 'Ajout au presse-papiers',
							copyKeys: 'Appuyez sur <i>ctrl</i> ou <i>\u2318</i> + <i>C</i> pour copier les données du tableau à votre presse-papiers. <br><br>Pour annuler, cliquez sur ce message ou appuyez sur Echap.',
							copySuccess: {
								_: '%d lignes copiées',
								1: '1 ligne copiée'
							}
						}
					}
				} );
			} );
			</script>


<script>
$.extend( $.fn.dataTable.defaults, {
 fnInitComplete: function(oSettings, json) {
 
  // Add "Clear Filter" button to Filter
  var btnClear = $('<i class="far fa-times-circle btnClearDataTableFilter" style="color:LightGray;margin-left:5px"></i>');
  btnClear.appendTo($('#' + oSettings.sTableId).parents('.dataTables_wrapper').find('.dataTables_filter'));
  $('#' + oSettings.sTableId + '_wrapper .btnClearDataTableFilter').click(function () {
   $('#' + oSettings.sTableId).dataTable().fnFilter('');
  });
 }
});
</script>

                        <?php	
                        }
                        else if($script == 'datatables_ajax')
                        { 
                        ?>
                        $(document).ready(function() {
								
                                moment.locale('fr');
                                $.fn.dataTable.moment( 'DD/MM/YYYY' );
				$('.datatable').DataTable( {
                                "processing": true,
                                "serverSide": true,
                                "scrollX": true,
                                "ajax":{
		                   			"url": "<?php echo $ajax_source; ?>",
                                   	"dataType": "json",
                                   	"type": "POST"
									},
                                "autoWidth": true,
								"iDisplayLength": <?php echo $nb_show;?>,
								"stateSave": true,
								"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Tous"]],
								"deferRender": true,
					
					<?php if(isset($custom_datatables)){
						echo $custom_datatables;
					}?>
					
					dom: 'Blfrtip',
					<?php echo $sort_col ;?>
					buttons: [
						{
							extend: 'excelHtml5',
							exportOptions: {
                    			columns: ':visible'
                				}
						},
						//{
						//	extend: 'pdfHtml5',
						//	download: 'open',
						//	exportOptions: {
						//		columns: ':visible'
						//	},
						//	<?php echo $pdf_export ;?>
						//},
						{
							extend: 'copyHtml5',
							text: 'Copier',
							exportOptions: {
								columns: ':visible'
							}
						},
						{
							extend: 'colvis',
							text: 'Colonnes',
						},
						<?php echo $format ;?>
					],
					language: {
						buttons: {
							copyTitle: 'Ajout au presse-papiers',
							copyKeys: 'Appuyez sur <i>ctrl</i> ou <i>\u2318</i> + <i>C</i> pour copier les données du tableau à votre presse-papiers. <br><br>Pour annuler, cliquez sur ce message ou appuyez sur Echap.',
							copySuccess: {
								_: '%d lignes copiées',
								1: '1 ligne copiée'
							}
						}
					}
				} );
			} );
			</script>
<script>
$.extend( $.fn.dataTable.defaults, {
 fnInitComplete: function(oSettings, json) {
 
  // Add "Clear Filter" button to Filter
  var btnClear = $('<i class="far fa-times-circle btnClearDataTableFilter" style="color:LightGray;margin-left:5px"></i>');
  btnClear.appendTo($('#' + oSettings.sTableId).parents('.dataTables_wrapper').find('.dataTables_filter'));
  $('#' + oSettings.sTableId + '_wrapper .btnClearDataTableFilter').click(function () {
   $('#' + oSettings.sTableId).dataTable().fnFilter('');
  });
 }
});
</script>
                       <?php
                        }
    }
    
    //----------------------------------------------------------------------------------------//
    //---------------------------------------DATERANGEPICKER----------------------------------//
    //----------------------------------------------------------------------------------------//
    else if ($script == 'daterangepicker'){
		echo "<script src='".base_url('/assets/plugins/datepicker/js/moment.js')."'></script>";
		echo "<script>
		moment.locale('fr');";
		if (isset($periode) && $periode == 'civile') {
		   echo "var pickerRanges = { 
				   'Aujourd\'hui': [moment(), moment()],
				   'Hier': [moment().subtract('days', 1), moment().subtract('days', 1)],
				   'Mois en cours': [moment().startOf('month'), moment().endOf('month')],
				   'Mois précedent':  [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
				   'Dernier trimestre':  ['".date("d/m/Y",mktime(0,0,0,$tab_tri['date_mois1_tri'],1,$tab_tri['annee']))."', '".date("d/m/Y",mktime(0,0,0,$tab_tri['date_mois3_tri'],date("t",$tab_tri['date_mois3_tri_date']),$tab_tri['annee']))."],
				   'Année en cours':  [moment().startOf('year'), moment().endOf('year')],
				   'Année dernière':  [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
				};";
		} else if (isset($periode) && $periode == 'scolaire') {
			if (mktime(0,0,0,date('n'),date('j'),date('Y'))<mktime(0,0,0,8,1,date('Y'))){
				echo "var pickerRanges = {
					   'Année dernière': ['".date("d/m/Y",mktime(0,0,0,9,1,date("Y")-2))."', '".date("d/m/Y",mktime(0,0,0,6,30,date("Y")-1))."'],
					   'Cette Année': ['".date("d/m/Y",mktime(0,0,0,9,1,date("Y")-1))."', '".date("d/m/Y",mktime(0,0,0,6,30,date("Y")))."'],
					   'Année prochaine':  ['".date("d/m/Y",mktime(0,0,0,9,1,date("Y")))."', '".date("d/m/Y",mktime(0,0,0,6,30,date("Y")+1))."']
					};";
			} else {
				echo "var pickerRanges = {
					   'Année dernière': ['".date("d/m/Y",mktime(0,0,0,9,1,date("Y")-1))."', '".date("d/m/Y",mktime(0,0,0,6,30,date("Y")))."'],
					   'Cette Année': ['".date("d/m/Y",mktime(0,0,0,9,1,date("Y")))."', '".date("d/m/Y",mktime(0,0,0,6,30,date("Y")+1))."'],
					   'Année prochaine':  ['".date("d/m/Y",mktime(0,0,0,9,1,date("Y")+1))."', '".date("d/m/Y",mktime(0,0,0,6,30,date("Y")+2))."']
					};";
			}
		} else if (isset($periode) && $periode == 'base') {
			echo "var pickerRanges = {
				   'Aujourd\'hui': [moment(), moment()],
				   'Hier': [moment().subtract('days', 1), moment().subtract('days', 1)],
				   'Semaine précedente':  [moment().subtract(1, 'week').startOf('week'), moment().subtract(1, 'week').endOf('week')],
				   'Semaine en cours': [moment().startOf('week'), moment().endOf('week')],
				   'Semaine suivante': [moment().add(1, 'week').startOf('week'), moment().add(1, 'week').endOf('week')]
				};";	
		} else {
			echo "var pickerRanges = null";
		}
		echo "
		var pickerLocale = {
					applyLabel: 'OK',
					cancelLabel: 'Annuler',
					fromLabel: 'Entre',
					toLabel: 'et',
					autoUpdateInput: false,
					customRangeLabel: 'P&eacuteriode personnalis&eacutee',
					daysOfWeek: moment().locale('fr')._weekdaysMin,
					monthNames: moment().locale('fr')._months,
					firstDay: 1
				};
        $(document).ready(function() {
			$('.daterange').daterangepicker(
				{
					  'showWeekNumbers': true,
					  format: 'DD/MM/YYYY',
					  ranges: pickerRanges,
					  separator: ' - ',
					  'opens': 'left',
					  'autoApply': true,
					  locale: pickerLocale,
					  autoUpdateInput: false
				});
			   $('.daterange').on('apply.daterangepicker', function(ev, picker) {
				  $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
			  });
		});</script>";
		echo "<script src='".base_url('/assets/plugins/datepicker/js/daterangepicker.js')."'></script>";
    } 
    
	//----------------------------------------------------------------------------------------//
    //---------------------------------------DATEPICKER---------------------------------------//
    //----------------------------------------------------------------------------------------//
    else if ($script == 'datepicker'){
		echo "<script src='".base_url('/assets/plugins/input-mask/jquery.inputmask.js')."'></script>";
		echo "<script src='".base_url('/assets/plugins/input-mask/jquery.inputmask.extensions.js')."'></script>";
		echo "<script src='".base_url('/assets/plugins/input-mask/jquery.inputmask.date.extensions.js')."'></script>";
		echo "<script src='".base_url('/assets/plugins/datepicker/js/moment.js')."'></script>";
		echo "<script src='".base_url('/assets/plugins/datepicker/js/moment.js')."'></script>";
		echo "<script>
			moment.locale('fr');
			var pickerLocale = {
					applyLabel: 'OK',
					cancelLabel: 'Annuler',
					fromLabel: 'Entre',
					toLabel: 'et',
					autoUpdateInput: false,
					customRangeLabel: 'P&eacuteriode personnalis&eacutee',
					daysOfWeek: moment().locale('fr')._weekdaysMin,
					monthNames: moment().locale('fr')._months,
					firstDay: 1
				};
			$(document).ready(function() {
				$(':input').inputmask();
				$('.datepicker').daterangepicker(
				   {
					  singleDatePicker: true,
					  showDropdowns: true,
					  format: 'DD/MM/YYYY',
					  autoUpdateInput: false,
					  locale: pickerLocale
				   }
			   );
			   $('.datepicker').on('apply.daterangepicker', function(ev, picker) {
				  $(this).val(picker.startDate.format('DD/MM/YYYY'));
			   });
			});
            </script>";
		echo "<script src='".base_url('/assets/plugins/datepicker/js/daterangepicker.js')."'></script>";
	}
	
   //----------------------------------------------------------------------------------------//
   //---------------------------------------TIMEPICKER---------------------------------------//
   //----------------------------------------------------------------------------------------//
   else if ($script == 'timepicker'){
       echo "<script src='".base_url('/assets/plugins/datepicker/js/moment.js')."'></script>
           <script>
           moment.locale('fr');
var pickerLocale = {
applyLabel: 'OK',
cancelLabel: 'Annuler',
fromLabel: 'Entre',
toLabel: 'et',
autoUpdateInput: false,
customRangeLabel: 'P&eacuteriode personnalis&eacutee',
daysOfWeek: moment().locale('fr')._weekdaysMin,
monthNames: moment().locale('fr')._months,
firstDay: 1
};
               //On teste l'existance de cette variable si on veut remplir le champ de l'heure quand on clique sur l'input
               if (typeof date_time_value !== 'undefined' )
               {               
                   $(document).ready(function() {
                       $('.datepicker').daterangepicker(
                       {
                           useCurrent: false,
                           singleDatePicker: true,
                           timePicker: true,
                           timePicker24Hour: true,
                           startDate: moment(date_time_value,'DD-MM-YYYY H:mm'),
                           showDropdowns: true,
                           format: 'DD/MM/YYYY H:mm',
                           autoUpdateInput: false,
           locale: pickerLocale,
                       });
                       $('.datepicker').on('apply.daterangepicker', function(ev, picker) {
                           $(this).val(picker.startDate.format('DD/MM/YYYY H:mm'));
       });
                   });
               }
               else
               {                  
                   $(document).ready(function() {
                       $('.datepicker').daterangepicker(
                       {
                           useCurrent: false,
                           singleDatePicker: true,
                           timePicker: true,
                           timePicker24Hour: true,
                           showDropdowns: true,
                           format: 'DD/MM/YYYY H:mm',
                           autoUpdateInput: false,
           locale: pickerLocale,
                       });
                   $('.datepicker').on('apply.daterangepicker', function(ev, picker) {
                          $(this).val(picker.startDate.format('DD/MM/YYYY H:mm'));
   });
                  });
               }
           </script>";
       echo "<script src='".base_url('/assets/plugins/datepicker/js/daterangepicker.js')."'></script>";
   }
	
	//----------------------------------------------------------------------------------------//
    //------------------------------------SWEETALERT------------------------------------------//
    //----------------------------------------------------------------------------------------//
	else if ($script == 'sweetalert'){
		echo "<script src='".base_url('/assets/plugins/sweetalert/sweetalert.min.js')."'></script>";
		echo "<script>
			function archiver(href)
			{
				swal({
					title: 'Vous êtes sur le point d\'archiver cet élément',
					type: 'warning',
					showCancelButton: true,
  					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					cancelButtonText: 'Annuler',
					confirmButtonText: 'Continuer'
				}, function(){ window.location.replace(href); });
			}
			
			function desarchiver(href)
			{
				swal({
					title: 'Vous êtes sur le point de désarchiver cet élément',
					type: 'warning',
					showCancelButton: true,
  					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					cancelButtonText: 'Annuler',
					confirmButtonText: 'Continuer'
				}, function(){ window.location.replace(href); });
			}
			
			function supprimer(href)
			{
				swal({
					title: 'Vous êtes sur le point de supprimer cet élément',
					type: 'warning',
					showCancelButton: true,
  					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					cancelButtonText: 'Annuler',
					confirmButtonText: 'Continuer'
				}, function(){ window.location.replace(href); });
			}</script>";
	}
	//----------------------------------------------------------------------------------------//
    //---------------------------------------MODAL--------------------------------------------//
    //----------------------------------------------------------------------------------------//
    else if ($script == 'modal'){
		echo "<script>
			$(function() {
				 $(document).on('click','a.big_modal', function (e) {
						e.preventDefault();
						var page = $(this).attr('href');
						var pagetitle = $(this).attr('title');
						var dialog = $('<div></div>')
						.html(\"<iframe style='border: 0px;' src='\" + page + \"' width='98%' height='98%'></iframe>\")
						.dialog({
							autoOpen: false,
							modal: true,
							height: 750,
							width: '500px',
							title: pagetitle,
							close: function(event, ui) { window.location.reload(true); }
						});
						dialog.dialog('open');
					});
					$(document).on('click','a.extrem_modal', function (e) {
						e.preventDefault();
						var page = $(this).attr('href');
						var pagetitle = $(this).attr('title');
						var string = $(this).attr('class');
						var close_var = null;
						if (string.indexOf('reload') !== -1){
							close_var = function(event, ui) { window.location.reload(true); }
						}
						var dialog = $('<div></div>')
						.html(\"<iframe style='border: 0px;' src='\" + page + \"' width='98%' height='98%'></iframe>\")
						.dialog({
							autoOpen: false,
							modal: true,
							height: 800,
							width: '900',
							title: null,
							close: close_var
						});
						dialog.dialog('open');
					});
				});
			</script>
			";
    }
	
	//----------------------------------------------------------------------------------------//
    //----------------------------------COLORSELECTOR-----------------------------------------//
    //----------------------------------------------------------------------------------------//
	else if ($script == 'colorselector'){
		echo "<script src='".base_url('/assets/plugins/colorselector/js/bootstrap-colorselector.min.js')."'></script>
			  <script>$(document).ready(function(){ $('.colorselector').colorselector(); });</script>";
	}
	
	// ----------------------------------------------------------------------------------------//
    // ----------------------------------MSSAGERIE-----------------------------------------//
    // ----------------------------------------------------------------------------------------//
    
    else if ($script == 'jsMessagerieFooter') {
        echo "<script src='" . base_url('/assets/plugins/Messagerie/jsMessagerie/fonctions.js') . "'></script>";
    } 
    else if ($script == 'refresh') {
        echo '<script type="text/javascript">
        
        var update = function() {
            $.ajax({
                url : "C_affiche_nb_message",
                context: document.body,
		        success: function(result){
                   $("#nbmessage").html(result);
                },
            });
        };
        update();
        setInterval("update()", 2000);
        </script>';
        // Pour les 5mins il faut mett
	}
	// ----------------------------------------------------------------------------------------//
    // ----------------------------------Sisyphus-----------------------------------------//
    // ----------------------------------------------------------------------------------------//
	else if ($script == 'sisyphus') {
		echo '<script type = "text/javascript" src="'.base_url('/assets/plugins/sisyphus/sisyphus.min.js').'">';
		echo '  $(window).load(function() {
                    $("form").sisyphus(); 
			    });';
	    echo '</script>';
	}
}

if (isset($custom_script)){
	echo $custom_script;
}

// Chargement des scripts pour le crud
if (isset($output)){
    foreach($output->js_files as $file){
        echo "<script src='".$file."'></script>";
    }
}