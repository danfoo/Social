<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <a href="<?php echo admin_url('fleet_management/maintenance'); ?>" class="btn btn-default mbot15"><i class="fa fa-arrow-left"></i> <?php echo _l('fleet_maintenance'); ?></a>
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold">
                            <?php echo _l('fleet_maintenance_photos'); ?>
                            <small class="text-muted">— <?php echo html_escape($record->vehicle_name); ?> · <?php echo _l('fleet_mtype_' . $record->type); ?> · <?php echo $record->service_date ? _d($record->service_date) : ''; ?></small>
                        </h4>
                        <hr class="hr-panel-heading" />

                        <?php if (staff_can('edit', 'fleet')) : ?>
                            <?php echo form_open_multipart(admin_url('fleet_management/maintenance/upload_file/' . $record->id)); ?>
                            <div class="row">
                                <div class="col-md-5">
                                    <label class="control-label"><?php echo _l('fleet_photo'); ?></label>
                                    <input type="file" name="file" accept="image/*" class="form-control" required>
                                </div>
                                <div class="col-md-4"><?php echo render_date_input('taken_date', 'fleet_photo_taken_date', _d(date('Y-m-d'))); ?></div>
                                <div class="col-md-3">
                                    <label class="control-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-upload"></i> <?php echo _l('fleet_photo_upload'); ?></button>
                                </div>
                            </div>
                            <?php echo form_close(); ?>
                            <hr />
                        <?php endif; ?>

                        <?php if (empty($files)) : ?>
                            <p class="text-muted"><?php echo _l('fleet_no_photos'); ?></p>
                        <?php else : ?>
                            <div class="row">
                                <?php foreach ($files as $f) : ?>
                                    <div class="col-md-3 col-sm-4 col-xs-6 mbot15">
                                        <div class="panel panel-default">
                                            <a href="<?php echo admin_url('fleet_management/maintenance/download_file/' . $f['id']); ?>" target="_blank">
                                                <img src="<?php echo admin_url('fleet_management/maintenance/download_file/' . $f['id']); ?>" class="img-responsive" style="width:100%;height:160px;object-fit:cover;" alt="<?php echo html_escape($f['original_name']); ?>">
                                            </a>
                                            <div class="panel-body text-center">
                                                <small class="text-muted"><i class="fa fa-calendar"></i> <?php echo $f['taken_date'] ? _d($f['taken_date']) : '-'; ?></small>
                                                <?php if (staff_can('delete', 'fleet')) : ?>
                                                    <a href="<?php echo admin_url('fleet_management/maintenance/delete_file/' . $f['id']); ?>" class="btn btn-danger btn-xs pull-right _delete"><i class="fa fa-remove"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
