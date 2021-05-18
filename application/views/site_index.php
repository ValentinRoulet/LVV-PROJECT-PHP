
<!DOCTYPE html>
<html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>AdminLTE 3 | Dashboard</title>

            <!-- Font Awesome -->
            <link rel="stylesheet" href="<?php echo base_url(); ?>/assets/plugins/fontawesome-free/css/all.min.css">
            <!-- Tempusdominus Bootstrap 4 -->
            <link rel="stylesheet" href="<?php echo base_url(); ?>/assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
            <!-- iCheck -->
            <link rel="stylesheet" href="<?php echo base_url(); ?>/assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
            <!-- JQVMap -->
            <link rel="stylesheet" href="<?php echo base_url(); ?>/assets/plugins/jqvmap/jqvmap.min.css">
            <!-- Theme style -->
            <link rel="stylesheet" href="<?php echo base_url(); ?>/assets/dist/css/adminlte.min.css">
            <!-- overlayScrollbars -->
            <link rel="stylesheet" href="<?php echo base_url(); ?>/assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
            <!-- Daterange picker -->
            <link rel="stylesheet" href="<?php echo base_url(); ?>/assets/plugins/daterangepicker/daterangepicker.css">
            <!-- summernote -->
            <link rel="stylesheet" href="<?php echo base_url(); ?>/assets/plugins/summernote/summernote-bs4.min.css">
        </head>

        <body class="hold-transition sidebar-mini layout-fixed">

            <div class="card card-primary col-md-10 offset-md-1">
                <div class="card-header with-border">
                    <div class="card-title">Liste des utilisateurs</div>
                </div>
                <div class="card-body">
                    <table id="test" class="display" style="width:80%">
                        <thead>
                            <th>ID</th>
                            <th>Prenom</th>
                            <th>Nom</th>
                            <th>Age</th>
                            <th>Metier</th>
                        </thead>
                        <tbody>
                            <?php 
                                foreach($data as $row){?>
                                    <tr>
                                        <td><?php echo $row->id;?></td>
                                        <td><?php echo $row->Nom;?></td>
                                        <td><?php echo $row->Prenom;?></td>
                                        <td><?php echo $row->age;?></td>
                                        <td><?php echo $row->LibMetier;?></td>
                                    </tr>
                            <?php 
                                }
                            ?>
                        </tbody>
                    </table>
                    <br>


                    <section class="content col-md-10 offset-md-1">
                        <div class="card card-secondary ">
                            <div class="card-header with-border">
                                <h3 class="card-title">Ajout utilisateur</h3>
                            </div>
                            
                            <form method="post" action="">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="form-group col-3">
                                            <input name="prenom" id="prenom" type="text" class="form-control" placeholder="Prénom">
                                        </div>
                                        <div class="form-group col-3">
                                            <input name="nom" id="nom" type="text" class="form-control" placeholder="Nom">
                                        </div>
                                        <div class="form-group col-3">
                                            <input name="age" id="age" type="text" class="form-control" placeholder="Age">
                                        </div>
                                        <div class="form-group col-3">
                                            <select name="idMetier" id="idMetier" class="custom-select">
                                                <?php 
                                                    foreach($data_Metier as $row1){
                                                ?>
                                                    <option value="<?php echo $row1->idMetier; ?>"><?php echo $row1->LibMetier;  ?></option>
                                                <?php 
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="car-footer col-md-4 offset-md-5">
                                    <button type="submit" class="btn btn-secondary">Ajouter</button>
                                </div>
                            </form>
                            <br>
                            <?php echo validation_errors("<div class='alert alert-danger'>", "</div>"); ?>
                        </div>
                    </section>

                    <section class="content col-md-10 offset-md-1">
                        <div class="card card-danger ">
                            <div class="card-header with-border">
                                <h3 class="card-title">Supprimer utilisateur</h3>
                            </div>
                            
                            <form method="post" action="">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="form-group col-5 offset-md-3">
                                            <select name="idMetier" id="idMetier" class="custom-select">
                                                <option value="0">-----------------</option>
                                                <?php 
                                                    foreach($data as $row2){
                                                ?>
                                                    <option value="<?php echo $row2->id; ?>"><?php echo $row2->Prenom . ' ' . $row2->Nom; ?></option>
                                                <?php 
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="car-footer col-md-4 offset-md-5">
                                    <button type="submit" class="btn btn-danger">Supprimer</button>
                                </div>
                            </form>
                            <br>
                            <?php echo validation_errors("<div class='alert alert-danger'>", "</div>"); ?>
                        </div>
                    </section>
                </div>
            </div>
            <br>


            <!-- jQuery -->
            <script src="<?php echo base_url(); ?>/assets/plugins/jquery/jquery.min.js"></script>
            <!-- jQuery UI 1.11.4 -->
            <script src="<?php echo base_url(); ?>/assets/plugins/jquery-ui/jquery-ui.min.js"></script>
            <!-- Bootstrap 4 -->
            <script src="<?php echo base_url(); ?>/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
            <!-- ChartJS -->
            <script src="<?php echo base_url(); ?>/assets/plugins/chart.js/Chart.min.js"></script>
            <!-- Sparkline -->
            <script src="<?php echo base_url(); ?>/assets/plugins/sparklines/sparkline.js"></script>
            <!-- JQVMap -->
            <script src="<?php echo base_url(); ?>/assets/plugins/jqvmap/jquery.vmap.min.js"></script>
            <script src="<?php echo base_url(); ?>/assets/plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
            <!-- jQuery Knob Chart -->
            <script src="<?php echo base_url(); ?>/assets/plugins/jquery-knob/jquery.knob.min.js"></script>
            <!-- daterangepicker -->
            <script src="<?php echo base_url(); ?>/assets/plugins/moment/moment.min.js"></script>
            <script src="<?php echo base_url(); ?>/assets/plugins/daterangepicker/daterangepicker.js"></script>
            <!-- Tempusdominus Bootstrap 4 -->
            <script src="<?php echo base_url(); ?>/assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
            <!-- Summernote -->
            <script src="<?php echo base_url(); ?>/assets/plugins/summernote/summernote-bs4.min.js"></script>
            <!-- overlayScrollbars -->
            <script src="<?php echo base_url(); ?>/assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
            <!-- AdminLTE App -->
            <script src="<?php echo base_url(); ?>/assets/dist/js/adminlte.js"></script>
                        
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.23/css/jquery.dataTables.css">
            <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.js"></script>

            <script>
                $(document).ready( function () {
                    $('#test').DataTable({"pageLength":100});
                } );
            </script>
        </body>
</html>
