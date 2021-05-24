<?php
//Définition du fudeau horaire
setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');

// Chargement des fichier css de grocery
if (isset($output)){
    foreach($output->css_files as $file){
        echo "<link type='text/css' rel='stylesheet' href='".$file."'/>";
    }    
}

foreach ($scripts as $script) {
    //----------------------------------------------------------------------------------------//
    //-------------------------------------BOOTSTRAP------------------------------------------//
    //----------------------------------------------------------------------------------------//
    if ($script === 'bootstrap'){
        echo "<link rel='stylesheet' href='".base_url('/assets/plugins/bootstrap/css/bootstrap-theme.min.css')."'/>
            <link rel='stylesheet' href='".base_url('/assets/plugins/bootstrap/css/bootstrap.min.css')."'/>
			<link rel='stylesheet' href='https://use.fontawesome.com/releases/v5.0.13/css/all.css' />
            <link rel='stylesheet' href='".base_url('/assets/plugins/bootstrap/css/font-awesome.min.css')."'/>";
    } 

	//----------------------------------------------------------------------------------------//
	//----------------------------------FULLCALENDAR-----------------------------------------//
	//----------------------------------------------------------------------------------------//
	if ($script == 'calendar'){
        
        echo "<script type='text/javascript' src='" . base_url()."/assets/plugins/fullcalendar-5.7.0/lib/locales-all.min.js'> </script>";
        echo "<script type='text/javascript' src='" . base_url()."/assets/plugins/fullcalendar-5.7.0/lib/main.min.js'> </script>";
        echo "<link href='".base_url('\assets\plugins\fullcalendar-5.7.0\lib\main.css')."' rel='stylesheet' />";

        
    }
    //
	
	 //----------------------------------------------------------------------------------------//
    //-------------------------------------SELECT2------------------------------------------//
    //----------------------------------------------------------------------------------------//
	if ($script == 'select2'){
        echo "<link rel='stylesheet' href='".base_url('/assets/plugins/select2/css/select2.css')."'/>";
    }
	//----------------------------------------------------------------------------------------//
    //-------------------------------------JQUERY-UI------------------------------------------//
    //----------------------------------------------------------------------------------------//
	if ($script == 'jqueryui'){
		echo "<link rel='stylesheet' href='".base_url('/assets/plugins/jquery/ui/jquery-ui.css')."'/>";
	}
    
	if ($script == 'jquery'){
	    echo "<script src='".base_url('/assets/plugins/jquery/js/jquery-3.6.0.js')."'></script>";
        echo "<link rel='stylesheet' href='".base_url('/assets/plugins/jquery/ui/jquery-ui.css')."'/>";
	}
    //----------------------------------------------------------------------------------------//
    //------------------------------------AUTOCOMPLETE----------------------------------------//
    //----------------------------------------------------------------------------------------//
    else if ($script === 'autocomplete'){
        echo "<link rel='stylesheet' href='".base_url('/assets/plugins/autocomplete/css/token-input.css')."'/>
              <link rel='stylesheet' href='".base_url('/assets/plugins/autocomplete/css/token-input-custom.css')."'/>";
    }
	
    
    //----------------------------------------------------------------------------------------//
    //---------------------------------------LTE ADMIN----------------------------------------//
    //----------------------------------------------------------------------------------------//
    if ($script === 'lte'){
        echo "<link rel='stylesheet' href='".base_url('/assets/plugins/lteadmin/css/AdminLTE.min.css')."'/>";
		echo "<link rel='stylesheet' href='".base_url('/assets/plugins/lteadmin/css/skins/skin-blue.min.css')."'/>";
    } 
	
	//----------------------------------------------------------------------------------------//
    //------------------------------------SWEETALERT------------------------------------------//
    //----------------------------------------------------------------------------------------//
	if ($script == 'sweetalert'){
		echo "<link rel='stylesheet' href='".base_url('/assets/plugins/sweetalert/sweetalert.css')."'/>";
	}

    //----------------------------------------------------------------------------------------//
    //------------------------------------Animation Scroll------------------------------------------//
    //----------------------------------------------------------------------------------------//
	if ($script == 'animScroll'){
		echo '<script src="//cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.7/ScrollMagic.min.js"></script>';
        echo '<script src="//cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.7/plugins/debug.addIndicators.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.5.1/gsap.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.5.1/PixiPlugin.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.5.1/ScrollToPlugin.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/tweene/0.5.11/tweene-all.min.js"></script>';
        echo '<script src="https://assets.codepen.io/16327/SplitText3.min.js"></script>';
        echo '<script src="https://unpkg.com/gsap@3/dist/ScrollTrigger.min.js"></script>';
        echo '<script type="text/javascript" src="..\..\..\assets\JavaScript\AnimationScroll.js"></script>';
	}
    
    //----------------------------------------------------------------------------------------//
    //------------------------------------framwork css bulma------------------------------------------//
    //----------------------------------------------------------------------------------------//
	if ($script == 'tinyMCE'){
		echo '<script type="text/javascript" src="..\..\..\assets\plugins\tinymce\js\tinymce\tinymce.min.js"></script>';
        //echo '<script type="text/javascript" src="..\..\..\assets\plugins\tinymce\js\tinymce\jquerry.tinymce.min.js"></script>';
	}

    //----------------------------------------------------------------------------------------//
    //------------------------------------css de la page index------------------------------------------//
    //----------------------------------------------------------------------------------------//
	else if ($script == 'cssIndex'){
		echo '<link rel="stylesheet" href="..\..\..\assets\CSS\index.css">';
	}
    if ($script == 'cssMessagerie'){
		echo '<link rel="stylesheet" href="..\..\..\assets\CSS\messagerie.css">';
	}


    //----------------------------------------------------------------------------------------//
    //---------------------------------------DATEPICKER---------------------------------------//
    //----------------------------------------------------------------------------------------//
    else if ($script == 'datepicker' || $script == 'timepicker' || $script == 'daterangepicker'){
        echo "<link rel='stylesheet' href='".base_url('/assets/plugins/datepicker/css/datepicker3.css')."'/>
            <link rel='stylesheet' href='".base_url('/assets/plugins/datepicker/css/daterangepicker-bs3.css')."'/>";
    }
        //----------------------------------------------------------------------------------------//
    //---------------------------------------CLOCKPICKER---------------------------------------//
    //----------------------------------------------------------------------------------------//
    else if ($script == 'clockpicker'){
        echo "<link rel='stylesheet' href='".base_url('/assets/plugins/clockpicker/dist/jquery-clockpicker.css')."'/>";
    }  
    //----------------------------------------------------------------------------------------//
    //---------------------------------------DATATABLES---------------------------------------//
    //----------------------------------------------------------------------------------------//
    else if ($script == 'datatables' or $script == 'datatables_ajax'){
        echo "<link rel='stylesheet' type='text/css' href='".base_url('/assets/plugins/datatables/src/RowGroup-1.0.0/css/rowGroup.bootstrap.min.css')."'/>
			<link rel='stylesheet' type='text/css' href='".base_url('/assets/plugins/datatables/src/Buttons-1.2.4/css/buttons.bootstrap.min.css')."'/>
			<link rel='stylesheet' type='text/css' href='".base_url('/assets/plugins/datatables/src/DataTables-1.10.13/css/dataTables.bootstrap.min.css')."'/>
			<link rel='stylesheet' type='text/css' href='".base_url('/assets/plugins/datatables/src/Buttons-1.2.4/css/buttons.bootstrap.min.css')."'/>
			<link rel='stylesheet' type='text/css' href='".base_url('/assets/plugins/datatables/src/ColReorder-1.3.2/css/colReorder.bootstrap.min.css')."'/>
			<link rel='stylesheet' type='text/css' href='".base_url('/assets/plugins/datatables/src/FixedColumns-3.2.2/css/fixedColumns.bootstrap.min.css')."'/>
			<link rel='stylesheet' type='text/css' href='".base_url('/assets/plugins/datatables/src/FixedHeader-3.1.2/css/fixedHeader.bootstrap.min.css')."'/>
			<link rel='stylesheet' type='text/css' href='".base_url('/assets/plugins/datatables/src/KeyTable-2.2.0/css/keyTable.bootstrap.min.css')."'/>
			<link rel='stylesheet' type='text/css' href='".base_url('/assets/plugins/datatables/src/Responsive-2.1.0/css/responsive.bootstrap.min.css')."'/>
                        
			<link rel='stylesheet' type='text/css' href='".base_url('/assets/plugins/datatables/src/RowReorder-1.2.0/css/rowReorder.bootstrap.min.css')."'/>
			<link rel='stylesheet' type='text/css' href='".base_url('/assets/plugins/datatables/src/Scroller-1.4.2/css/scroller.bootstrap.min.css')."'/>
			<link rel='stylesheet' type='text/css' href='".base_url('/assets/plugins/datatables/src/Select-1.2.0/css/select.bootstrap.min.css')."'/>";
    }
    else if ($script == 'datatables_custom'){
        include "/assets/plugins/datatables/css/datatables_css.php";
    }
	
	//----------------------------------------------------------------------------------------//
    //----------------------------------COLORSELECTOR-----------------------------------------//
    //----------------------------------------------------------------------------------------//
	else if ($script == 'colorselector'){
		echo "<link rel='stylesheet' type='text/css' href='".base_url('/assets/plugins/colorselector/css/bootstrap-colorselector.min.css')."'/>";
	}
	
	//----------------------------------------------------------------------------------------//
	//----------------------------------MESSAGERIE IMPRESSION-----------------------------------------//
	//----------------------------------------------------------------------------------------//
	else if ($script == 'cssImpression'){
	    echo "<link rel='stylesheet' type='text/css' media='print' href='".base_url('/assets/plugins/Messagerie/cssImpression/Impression.css')."'/>";
	}
	else if($script == 'jsMessagerieHeader')
	{
	    echo "<script src='" . base_url('/assets/plugins/Messagerie/jsMessagerie/alert.js') . "'></script>";
	}
	else if($script == 'cssChargement')
	{
	    echo "<link rel='stylesheet' type='text/css' href='".base_url('/assets/plugins/Messagerie/cssMessagerie/Chargement.css')."'/>";
    }

    //----------------------------------------------------------------------------------------//
	//----------------------------------FULLCALENDAR-----------------------------------------//
	//----------------------------------------------------------------------------------------//
	if ($script == 'calendar'){

        echo '<script type="text/javascript" src="/assets/plugins/fullcalendar-5.7.0/lib/main.min.js"></script>';
        echo "<script type='text/javascript' src='" . base_url()."/assets/plugins/fullcalendar-5.7.0/lib/main.min.js'> </script>";
        echo "<link href='".base_url('\assets\plugins\fullcalendar-5.7.0\lib\main.css')."' rel='stylesheet' />";

        
    }

}
//echo '<script type = "text/javascript" src="'.base_url()."'/assets/plugins/sisyphus/sisyphus.min.js').'"></script>';
?>
<style>
    body{/*correction bug ios iframe responsive*/
    max-width: 100vw !important;
    }
    .dataTables_scrollBody thead tr[role="row"]{ /*empèche affichage 2ème ligne de recherche dans tableaux*/
    visibility: collapse !important;
}
</style>
      </head>
      
    <body>