<style>
    .align{
        margin-top:20px;margin-left:12px;
    }
    
</style>
<div class="box box-info ">
    <div class='box-header gs_reveal2 gs_reveal_fromBot with-border bg-aqua color-palette'>
        <h2 class='box-title'><i class='<?php echo $titre[1]?>'></i>&emsp;<?php echo $titre[0];?> 
        <?php if (isset($titre[3])){
			echo $titre[3];
		}?>
        </h2>
        
        <?php 
        
        foreach($boutons as $bouton) {
            
        if(isset($bouton[4]))
            {
                echo form_open(site_url($bouton[2]),$bouton[5],$bouton[6]);
                echo "<button type='submit' class='btn btn-primary pull-right no-print'/><i class='".$bouton[1]."'></i>&emsp;".$bouton[0]."</button>
                <div class='pull-right'>&emsp;</div>"; 
                echo form_close();
                
            }
            
            // Creation d'un bouton avec lien
            elseif (isset($bouton[2])){
                echo "<a href='".site_url($bouton[2])."' id='lien_bouton' target=".$bouton[3]."><button type='button' class='btn btn-primary pull-right no-print' /><i class='".$bouton[1]."'></i>&emsp;".$bouton[0]."</button></a>
                    <div class='pull-right'>&emsp;</div>"; 
                
            }
            
            // Creation d'un bouton avec onclick
            else {
                echo "<button type='button' class='btn btn-primary pull-right no-print' onClick='".$bouton[3]."'/><i class='".$bouton[1]."'></i>&emsp;".$bouton[0]."</button>
                    <div class='pull-right'>&emsp;</div>";
                
            }
        }?>
    </div>
</div>