<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ProjetPhP</title>


    <script type="text/javascript" src="..\..\..\assets\JavaScript\FullCalendar.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.css" />

    


    

    <!-- Font <p> ---------->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Rokkitt&display=swap" rel="stylesheet">
    <!-- ---------------- -->


</head>
  <body class="html">

    
    <section class="section">
      <div id="Texte1">
        <div class="Texte1 test"><p style=" font-size: 3.0vw; margin: auto;"> PLUS QU'UN SIMPLE BTS </p> </div>
        <div class="Texte1"> <p style=" font-size: 1.5vw; margin: auto;"> Tu aimes l’informatiques et le développement ? Alors rejoins-nous ! </p> </div>
        <div class="Texte1"> <p style=" font-size: 1.5vw; margin: auto;"> Ce BTS est composé en deux option : SLAM et SISR </p> </div>
      </div>

      <div class="spacer s3" name="espace"></div>

      <!-- groupement des deux images ------------->
      <div class="">
        <form class="form-horizontal" action="<?php echo base_url(); ?>index.php/index/c_index" method="POST">
          <div class="GroupImage1">
            <div class="zoomImg">
              <!-- <a href="<?php echo base_url(); ?>index.php/index/c_index"> -->
                <img class="imageR grayscaleImg gs_reveal gs_reveal_fromLeft" data-toggle="modal" data-target="#sisr" name="oui" src="..\..\..\assets\images\index\reseau.png"  alt="En apprendre plus sur l'option sisr">
              <!-- </a> -->
            </div>
            <div class="zoomImg">
                <img class="imageC grayscaleImg gs_reveal gs_reveal_fromRight" data-toggle="modal" data-target="#slam" src="..\..\..\assets\images\index\code.png" alt="En apprendre plus sur l'option slam">
            </div>
          </div>
        </form>
      </div>
      <!-- fin groupement des deux images --------------->



      <!-- Modal -->
      <div class="modal fade" id="sisr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h3 class="modal-title textNoir" id="">Présentation SISR</h3>
              <h4 class="modal-title textNoir" id=""> des chomeurs sa mère</h4>
            </div>
            <div class="modal-body">
              ...
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="slam" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h3 class="modal-title textNoir" id="">Présentation SLAM</h3>
              <h4 class="modal-title textNoir" id=""> Les boss en fait</h4>
            </div>
            <div class="modal-body">
              ...
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
      <!-- ---- FIN MODAL ------- -->

    </section>




    <div class="spacer s10" name="espace"></div>
  
    
    <script>

    </script>

    
    <div id='calendar'></div>

            <!-- <button type='submit' class='btn btn-primary center no-print' value="Edit" name="changelog"  >ChangeLog </button> -->
            <!-- <button type='submit' class='btn btn-primary center no-print' value="Edit" name="totp"  >totp </button> -->
            <!-- <button type='submit' class='btn btn-primary  no-print' value="Edit" name="fratrie"  >Fratrie </button> -->


            
        
  </body>
</html>

