<?php
use ModulairDashboard\DashboardPlugin;

if(!isset($_REQUEST['id'])) {
    wp_die("Je kunt deze pagina niet bekijken zonder id.");
}
$id = wp_unslash($_REQUEST['id']);
$data = DashboardPlugin::get_instance()->getSensorById($id);

if($data === null) {
    wp_die("De sensor voor $id is niet gevonden!");
}

if(!isset($_POST['sensor_name'])) {
    $_POST['sensor_name'] = $data->getName();
    $_POST['type_selected'] = (string)$data->getDataset();
    $_POST['sensor_type'] = (string)$data->getDataset();

    $_POST = $data->getDataset()->setFields($_POST);
}

?>

<div class="wrap">
	<!-- Head -->
    <h1 class="wp-heading-inline"> Bewerk Sensor: <?php echo $data->getName(); ?></h1>
    <p>Bewerk je sensor, zodat je makkelijk errors of problemen kunt oplossen.</p>
    <hr class="wp-header-end">

    <?php do_action( 'sensor_notices' ); ?>

    <form method="post" name="updatesensor" id="updatesensor" style="margin-right: 24px;">
        <input type="number" name="type_selected" id="type_selected" value="<?php 
            if(isset($_POST['type_selected'])) echo 1;
            else if(isset($_POST['loadcon'])) echo 1;
        ?>" hidden>

        <input name="sensor_id" id="sensor_id" value="<?php echo $id; ?>" hidden>

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
            <input type="submit" name="updatesensor" id="updatesensorsub" class="button button-primary" value="Sensor bijwerken">
        </p>

    </form>

    <!-- Footer -->
    <div class="clear"></div>
</div>