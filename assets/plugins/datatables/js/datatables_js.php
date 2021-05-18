<!-- DataTables -->
<?php 
$chemin = sprintf(
    "%s://%s%s",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
    $_SERVER['SERVER_NAME'],""
  );
$chemin .= substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],"framework"));
$chemin .= "framework";
?>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/JSZip-2.5.0/jszip.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/pdfmake-0.1.18/build/pdfmake.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/pdfmake-0.1.18/build/vfs_fonts.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/DataTables-1.10.13/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/DataTables-1.10.13/js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/Buttons-1.2.4/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/Buttons-1.2.4/js/buttons.bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/Buttons-1.2.4/js/buttons.colVis.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/Buttons-1.2.4/js/buttons.flash.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/Buttons-1.2.4/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/Buttons-1.2.4/js/buttons.print.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/ColReorder-1.3.2/js/dataTables.colReorder.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/FixedColumns-3.2.2/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/FixedHeader-3.1.2/js/dataTables.fixedHeader.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/KeyTable-2.2.0/js/dataTables.keyTable.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/Responsive-2.1.0/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/Responsive-2.1.0/js/responsive.bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/RowReorder-1.2.0/js/dataTables.rowReorder.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/Scroller-1.4.2/js/dataTables.scroller.min.js"></script>
<script type="text/javascript" src="<?php echo $chemin;?>/assets/plugins/datatables/src/Select-1.2.0/js/dataTables.select.min.js"></script>
<script src="<?php echo $chemin;?>/assets/plugins/datatables/src/date-eu.js"></script>
<script src="<?php echo $chemin;?>/assets/plugins/datatables/src/date-uk.js"></script>
<script src="<?php echo $chemin;?>/assets/plugins/datatables/src/num-html.js"></script>
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
				exportOptions: {
                    columns: ':visible'
                }
            },
" : '';
?>

$(document).ready(function() {
	$('.datatable').DataTable( {
		"iDisplayLength": <?php echo $nb_show;?>,
		"stateSave": true,
		"language": {
                
				"url": "//cdn.datatables.net/plug-ins/1.10.13/i18n/French.json"
			 		},
		 "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Tous"]],
		 "deferRender": true,
		dom: 'Blfrtip',
		<?php echo $sort_col ;?>
        buttons: [
			{
                extend: 'excelHtml5',
				exportOptions: {
                    columns: [ 0, ':visible' ]
                }
            },
            {
                extend: 'pdfHtml5',
                download: 'open',
				exportOptions: {
                    columns: [ 0, ':visible' ]
                },
				<?php echo $pdf_export ;?>
            },
			{
				extend: 'copyHtml5',
				text: 'Copier',
				exportOptions: {
                    columns: [ 0, ':visible' ]
                }
    		},
			{
				extend: 'colvis',
				text: 'Colonnes',
       		},
		<?php echo $format ;?>
			<?php //echo $text ;?>
        ],
		<?php //echo $script ;?>
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