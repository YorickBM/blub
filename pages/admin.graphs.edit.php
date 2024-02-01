<?php
use ModulairDashboard\DashboardPlugin;


if(!isset($_REQUEST['id'])) {
    wp_die("Je kunt deze pagina niet bekijken zonder id.");
}
$id = wp_unslash($_REQUEST['id']);
$data = DashboardPlugin::get_instance()->graphs_table->select_data("`id` = '$id'");

if(count($data) < 1) {
    wp_die("De grafiek voor $id is niet gevonden!");
}

$data = (object) $data[0];
$_POST['graph_name'] = $data->name;
$_POST['graph_js'] = $data->js;
$_POST['graph_css'] = $data->css;
$_POST['graph_script'] = $data->script;
$_POST['graph_filter'] = $data->filter;
?>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.js" integrity="sha512-8RnEqURPUc5aqFEN04aQEiPlSAdE0jlFS/9iGgUyNtwFnSKCXhmB6ZTNl7LnDtDWKabJIASzXrzD0K+LYexU9g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.css" integrity="sha512-uf06llspW44/LZpHzHT6qBOIVODjWtv4MxCricRxkzvopAlSWnTf6hpZTFxuuZcuNE9CBQhqE0Seu1CoRk84nQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/edit/matchbrackets.min.js" integrity="sha512-GSYCbN/le5gNmfAWVEjg1tKnOH7ilK6xCLgA7c48IReoIR2g2vldxTM6kZlN6o3VtWIe6fHu/qhwxIt11J8EBA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/htmlmixed/htmlmixed.min.js" integrity="sha512-HN6cn6mIWeFJFwRN9yetDAMSh+AK9myHF1X9GlSlKmThaat65342Yw8wL7ITuaJnPioG0SYG09gy0qd5+s777w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/xml/xml.min.js" integrity="sha512-LarNmzVokUmcA7aUDtqZ6oTS+YXmUKzpGdm8DxC46A6AHu+PQiYCUlwEGWidjVYMo/QXZMFMIadZtrkfApYp/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/javascript/javascript.min.js" integrity="sha512-I6CdJdruzGtvDyvdO4YsiAq+pkWf2efgd1ZUSK2FnM/u2VuRASPC7GowWQrWyjxCZn6CT89s3ddGI+be0Ak9Fg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/css/css.min.js" integrity="sha512-rQImvJlBa8MV1Tl1SXR5zD2bWfmgCEIzTieFegGg89AAt7j/NBEe50M5CqYQJnRwtkjKMmuYgHBqtD1Ubbk5ww==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/clike/clike.min.js" integrity="sha512-l8ZIWnQ3XHPRG3MQ8+hT1OffRSTrFwrph1j1oc1Fzc9UKVGef5XN9fdO0vm3nW0PRgQ9LJgck6ciG59m69rvfg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/php/php.min.js" integrity="sha512-jZGz5n9AVTuQGhKTL0QzOm6bxxIQjaSbins+vD3OIdI7mtnmYE6h/L+UBGIp/SssLggbkxRzp9XkQNA4AyjFBw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


<div class="wrap">
	<!-- Head -->
    <h1 class="wp-heading-inline">Bewerk Grafiek: <?php echo $data->name; ?></h1>
    <p>Bewerk een bestaande grafiek, zo kun je fouten verbeteren of code optimalizeren!</p>
    <hr class="wp-header-end">

    <!-- Content -->
    <?php do_action( 'graph_notices' ); ?>

    <form method="post" name="updategraph" id="updategraph" style="margin-right: 24px;">
        <input type="number" name="type_selected" id="type_selected" value="<?php 
            if(isset($_POST['type_selected'])) echo 1;
            else if(isset($_POST['loadcon'])) echo 1;
        ?>" hidden>

        <input type="number" name="graph_id" id="graph_id" value="<?php echo $data->id; ?>" hidden>

        <table class="form-table" role="presentation">
            <tbody>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="graph_name">Grafieknaam <span class="description">(vereist)</span></label>
                    </th>
                    <td>
                        <input name="graph_name" type="text" id="graph_name" value="<?php if(isset($_POST['graph_name']) && strlen($_POST['graph_name']) > 1) echo $_POST['graph_name']; else echo uniqid(); ?>" autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60">
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="graph_js">Externe Scripten <span class="description"></span></label>
                    </th>
                    <td>
                        <textarea name="graph_js" type="text" id="graph_js" placeholder="Splits meerdere externe script URLS met een ;"><?php if(isset($_POST['graph_js']) && strlen($_POST['graph_js']) > 1) echo $_POST['graph_js']; ?></textarea>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="graph_css">Externe CSS <span class="description"></span></label>
                    </th>
                    <td>
                        <textarea name="graph_css" type="text" id="graph_css" placeholder="Splits meerdere externe style URLS met een ;"><?php if(isset($_POST['graph_css']) && strlen($_POST['graph_css']) > 1) echo $_POST['graph_css']; ?></textarea>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="graph_css">Filter Optie <span class="description"></span></label>
                    </th>
                    <td>
                        <span><input type="radio" name="graph_filter" value="XY" <?php if(isset($_POST['graph_filter']) && $_POST['graph_filter'] == 'XY') echo 'checked';?> required> X & Y-Axis filter</span>
                        <span style="margin-left: 12px;"><input type="radio" name="graph_filter" value="X" <?php if(isset($_POST['graph_filter']) && $_POST['graph_filter'] == 'X') echo 'checked';?> required> Enkelkeuze filter</span>
                        <span style="margin-left: 12px;"><input type="radio" name="graph_filter" value="Y" <?php if(isset($_POST['graph_filter']) && $_POST['graph_filter'] == 'Y') echo 'checked';?> required> Meerkeuze filter</span>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="graph_script">Grafiek Script <span class="description"></span></label>
                    </th>
                    <td>
                        <textarea name="graph_script" id="graph_script"><?php
                        if(isset($_POST['graph_script']) && strlen($_POST['graph_script']) > 1) { echo stripslashes($_POST['graph_script']); } else {
    ?><div>Element hier</div>
    <script>
        var info = "Javascript hier";
        //data.cols = ["Header-1", "Header-2", "Header-3"];
        //data.entries = [{"Header-1":"A", "Header-2":12, "Header-3":4}, {"Header-1":"B", "Header-2":20, "Header-3":94}, {"Header-1":"C", "Header-2":28, "Header-3":68}];
        function initialize() {
            console.log(info);
        }
        initialize();
    </script><?php } ?></textarea>
                        <p>Je kunt zowel Javascript, JQuery, CSS en HTML gebruiken in de bovenstaande editor.</p>
                        <p>Hieronder de custom variables die u kunt gebruiken:
                            <ul style="margin-top:2px;">
                                <li>%id% - Voor de unique id die de grafiek zal krijgen in de front-end.</li>
                                <li>data.cols - Dit bevat een lijst met 'strings' voor elk type data die in de dataset zitten.</li>
                                <li>data.entries - Dit bevat een lijst met 'JSON Objects' voor voor elke entry in de dataset.</li>
                                <li>data.filters - Dit bevat een lijst met 'strings' voor welke kolomen gekozen zijn.</li>
                            </ul>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr>
        <p class="submit">
            <input type="submit" name="updategraph" id="updategraphsub" class="button button-primary" value="Grafiek updaten">
        </p>

    </form>

    <script>
        var editor = CodeMirror.fromTextArea(document.getElementById('graph_script'), {
            lineNumbers: true,
            matchBrackets: true,
            lineWrapping: true,
            mode: "application/x-httpd-php",
        });
        editor.save()
    </script>

    <!-- Footer -->
    <div class="clear"></div>
</div>