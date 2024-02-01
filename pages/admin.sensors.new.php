<?php
use ModulairDashboard\DashboardPlugin;
?>

<div class="wrap">
	<!-- Head -->
    <h1 class="wp-heading-inline"> Nieuwe sensor </h1>
    <p>Nieuwe sensor aanmaken en koppelen aan deze website, zo kan deze voor grafiek worden gebruikt.</p>
    <hr class="wp-header-end">

    <?php do_action( 'sensor_notices' ); ?>

    <form method="post" name="createsensor" id="createsensor" style="margin-right: 24px;">
        <input type="number" name="type_selected" id="type_selected" value="<?php 
            if(isset($_POST['type_selected'])) echo 1;
            else if(isset($_POST['loadcon'])) echo 1;
        ?>" hidden>

        <table class="form-table" role="presentation">
            <tbody>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="sensor_name">Sensornaam <span class="description">(vereist)</span></label>
                    </th>
                    <td>
                        <input name="sensor_name" type="text" id="sensor_name" value="<?php if(isset($_POST['sensor_name']) && strlen($_POST['sensor_name']) > 1) echo $_POST['sensor_name']; else echo uniqid(); ?>" autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60">
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="sensor_type">Sensor Type <span class="description">(vereist)</span></label>
                    </th>
                    <td>

                    <div style="display:flex;gap:8px;width: 95%;">
                        <select name="sensor_type" id="sensor_type" style="width: 95%;">
                            <?php
                                foreach(DashboardPlugin::get_instance()->getConnectionTypes() as $id => $type) {
                                    $selected = "";
                                    $klass = new $type();
                                    if(isset($_POST['sensor_type']) && $type == $_POST['sensor_type']) $selected = "selected";

                                    var_dump($klass);
                                echo "<option value='$type' $selected>". call_user_func(array($klass, 'toString')) ."</option>";
                                }
                            ?>
                        </select>
                        <input type="submit" name="loadcon" id="loadcon" class="button button-primary" style="width: 120px;" value="Select">
                    </div>

                        
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php if(isset($_POST['type_selected'])) { 
            $sensorType = new $_POST['sensor_type'](); ?>
        <hr>
        <h2>Connectie gegevens</h2>
        <?php $sensorType->fields(); ?>
        <?php } ?>
        <hr>
        <p class="submit">
            <input type="submit" name="createsensor" id="createsensorsub" class="button button-primary" value="Nieuwe sensor toevoegen">
        </p>

    </form>

    <!-- Footer -->
    <div class="clear"></div>
</div>