<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <!-- Categories -->
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold"><?php echo _l('fleet_categories'); ?></h4>
                        <?php if (staff_can('create', 'fleet')) : ?>
                            <?php echo form_open(admin_url('fleet_management/library/category_save')); ?>
                            <div class="input-group mbot15">
                                <input type="text" name="name" class="form-control" placeholder="<?php echo _l('fleet_category'); ?>" required>
                                <span class="input-group-btn"><button class="btn btn-primary" type="submit"><i class="fa fa-plus"></i></button></span>
                            </div>
                            <?php echo form_close(); ?>
                        <?php endif; ?>
                        <table class="table">
                            <tbody>
                            <?php foreach ($categories as $c) : ?>
                                <tr>
                                    <td><?php echo html_escape($c['name']); ?></td>
                                    <td class="text-right">
                                        <?php if (staff_can('delete', 'fleet')) : ?>
                                            <a href="<?php echo admin_url('fleet_management/library/category_delete/' . $c['id']); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-remove"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Brands -->
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold"><?php echo _l('fleet_brands'); ?></h4>
                        <?php if (staff_can('create', 'fleet')) : ?>
                            <?php echo form_open(admin_url('fleet_management/library/brand_save')); ?>
                            <div class="input-group mbot15">
                                <input type="text" name="name" class="form-control" placeholder="<?php echo _l('fleet_brand'); ?>" required>
                                <span class="input-group-btn"><button class="btn btn-primary" type="submit"><i class="fa fa-plus"></i></button></span>
                            </div>
                            <?php echo form_close(); ?>
                        <?php endif; ?>
                        <table class="table">
                            <tbody>
                            <?php foreach ($brands as $b) : ?>
                                <tr>
                                    <td><?php echo html_escape($b['name']); ?></td>
                                    <td class="text-right">
                                        <?php if (staff_can('delete', 'fleet')) : ?>
                                            <a href="<?php echo admin_url('fleet_management/library/brand_delete/' . $b['id']); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-remove"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Models -->
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold"><?php echo _l('fleet_models'); ?></h4>
                        <?php if (staff_can('create', 'fleet')) : ?>
                            <?php echo form_open(admin_url('fleet_management/library/model_save')); ?>
                            <select name="brand_id" class="form-control mbot10" required>
                                <option value=""><?php echo _l('fleet_brand'); ?>...</option>
                                <?php foreach ($brands as $b) : ?>
                                    <option value="<?php echo $b['id']; ?>"><?php echo html_escape($b['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group mbot15">
                                <input type="text" name="name" class="form-control" placeholder="<?php echo _l('fleet_model'); ?>" required>
                                <span class="input-group-btn"><button class="btn btn-primary" type="submit"><i class="fa fa-plus"></i></button></span>
                            </div>
                            <?php echo form_close(); ?>
                        <?php endif; ?>
                        <table class="table">
                            <tbody>
                            <?php foreach ($models as $m) : ?>
                                <tr>
                                    <td><?php echo html_escape($m['brand_name']); ?> <i class="fa fa-angle-right text-muted"></i> <?php echo html_escape($m['name']); ?></td>
                                    <td class="text-right">
                                        <?php if (staff_can('delete', 'fleet')) : ?>
                                            <a href="<?php echo admin_url('fleet_management/library/model_delete/' . $m['id']); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-remove"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
