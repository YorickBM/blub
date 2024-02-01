<?php
use ModulairDashboard\SensorRequestTable;

$table = new SensorRequestTable();
$table->setup();
$table->prepare_items();
?>

<div class="wrap">
	<!-- Head -->
    <h1 class="wp-heading-inline">Sensor Verzoeken</h1>
    <!--<a href="<?php echo admin_url('admin.php?page=new_sensors'); ?>" class="page-title-action">Nieuwe toevoegen</a>-->
    <hr class="wp-header-end">
    
    <!-- Content -->
	<form action="", method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
		
		<?php
        $table->views();
        $table->display();
		?>
	</form>
	
	<!-- Footer -->
    <div class="clear"></div>
</div>