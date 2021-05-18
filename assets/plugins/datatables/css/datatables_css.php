<?php 
$chemin = sprintf(
    "%s://%s%s",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
    $_SERVER['SERVER_NAME'],""
  );
$chemin .= substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],"framework"));
$chemin .= "framework";
?>
<link rel='stylesheet' type='text/css' href='<?php echo $chemin;?>/assets/plugins/datatables/src/Buttons-1.2.4/css/buttons.bootstrap.min.css'>
<link rel='stylesheet' type='text/css' href='<?php echo $chemin;?>/assets/plugins/datatables/src/DataTables-1.10.13/css/dataTables.bootstrap.min.css'/>
<link rel='stylesheet' type='text/css' href='<?php echo $chemin;?>/assets/plugins/datatables/src/Buttons-1.2.4/css/buttons.bootstrap.min.css'/>
<link rel='stylesheet' type='text/css' href='<?php echo $chemin;?>/assets/plugins/datatables/src/ColReorder-1.3.2/css/colReorder.bootstrap.min.css'/>
<link rel='stylesheet' type='text/css' href='<?php echo $chemin;?>/assets/plugins/datatables/src/FixedColumns-3.2.2/css/fixedColumns.bootstrap.min.css'/>
<link rel='stylesheet' type='text/css' href='<?php echo $chemin;?>/assets/plugins/datatables/src/FixedHeader-3.1.2/css/fixedHeader.bootstrap.min.css'/>
<link rel='stylesheet' type='text/css' href='<?php echo $chemin;?>/assets/plugins/datatables/src/KeyTable-2.2.0/css/keyTable.bootstrap.min.css'/>
<link rel='stylesheet' type='text/css' href='<?php echo $chemin;?>/assets/plugins/datatables/src/Responsive-2.1.0/css/responsive.bootstrap.min.css'/> 
<link rel='stylesheet' type='text/css' href='<?php echo $chemin;?>/assets/plugins/datatables/src/RowReorder-1.2.0/css/rowReorder.bootstrap.min.css'/>
<link rel='stylesheet' type='text/css' href='<?php echo $chemin;?>/assets/plugins/datatables/src/Scroller-1.4.2/css/scroller.bootstrap.min.css'/>
<link rel='stylesheet' type='text/css' href='<?php echo $chemin;?>/assets/plugins/datatables/src/Select-1.2.0/css/select.bootstrap.min.css'/>