<!DOCTYPE html>
<body>
    <div class="col-md-6 col-md-offset-3">
        <div class="box box-info">

            <div class="box-header with-border">
                <h3 class="box-title">Login</h3>
            </div>

            <form class="form-horizontal" action="<?php echo base_url(); ?>index.php/totp/c_totp_login/index" method="POST">

                <div class="box-body">

                    <div class="form-group">
                        <label for="inputLogin" class="col-sm-2 control-label">Login</label>

                        <div class="col-sm-10">
                            <input type="text" name="login" id="inputLogin" class="form-control" placeholder="Login">
                        </div>

                        <span class="text-danger"><?php echo form_error('login'); ?></span>
                    </div>

                    <div class="form-group">
                        <label for="inputPassword" class="col-sm-2 control-label">Mot de passe</label>

                        <div class="col-sm-10">
                            <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Mot de passe">
                        </div>
                        
                        <span class="text-danger"><?php echo form_error('password'); ?></span>
                    </div>

                </div>

                <div class="box-footer text-center">
                    <button type="submit" class="btn btn-info">Valider</button>
                </div>

            </form>
        </div>
    </div>
</body>