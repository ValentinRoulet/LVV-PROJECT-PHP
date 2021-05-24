<!DOCTYPE html>
<body>
    <div class="col-md-6 col-md-offset-3">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">TOTP</h3>
            </div>
            <form class="form-horizontal" action="<?php echo base_url(); ?>index.php/totp/c_totp_login/valider_totp" method="POST">
                <div class="box-body">
                    <div class="form-group">
                        <label for="inputKey" class="col-sm-2 control-label">Clé</label>
                        <div class="col-sm-10">
                            <input type="text" name="key" id="inputKey" class="form-control" placeholder="Clé double authentification">
                        </div>
                        <span class="text-danger"><?php echo form_error('key'); ?></span>
                    </div>
                </div>
                <div class="box-footer text-center">
                    <button type="submit" class="btn btn-info">Valider</button>
                </div>
            </form>
        </div>
    </div>
</body>