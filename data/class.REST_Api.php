<?php
use ModulairDashboard\Dataset;
use ModulairDashboard\DashboardPlugin;

/**
 * Rest API Overhead dataset to be used within sensors
 * @author - Yorick <info@yorickblom.nl>
 */
class RestApi implements Dataset {

    private $dbh;
    private $data;

    public function getDelay() { return 60 * 15; } //Only update every 15 minutes!!

    public function toString() {
        return "Rest API";
    }

    public function __toString() {
        return "RestApi";
    }

    /**
     * Constructor
     */
    public function __construct($url = null, $method = "GET", $data = null) {
        $this->data = (object) array(
            "url" => $url,
            "method" => $method,
            
            "data" => $data
        );
    }

    /**
     * Return encoded data object
     */
    function getObject() {
        $obj = (array) $this->encode();
        return $obj;
    }

    function fromJson($data) {
        $this->data = $data;
        $this->decode();
    }

    /**
     * Encode data
     */
    private function encode() {
        $object = $this->data; //Make a clone
        $object->data = DashboardPlugin::get_instance()->encrypt($this->data->data);
        
        return $object;
    }

    /**
     * Decode encoded data
     */
    private function decode() {
        $this->data->data = DashboardPlugin::get_instance()->decrypt($this->data->data);
    }

    /**
     * Basic connect & disconnect hook
     */
    function connect() {
        $cURL = $this->cURL();

        if (0 !== $cURL->errno) {
            throw new RuntimeException($cURL->error, $cURL->errno);
        }

        return $cURL->response;
    }
    function disconnect() {
        //Silence is golden
    }

    private function cURL() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->data->url);
        if($this->data->method == "POST") {        
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data->data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);

        curl_close($ch);

        return (object)array("response" => json_decode($response), "error" => $error, "errno" => $errno);
    }

    /**
     * Get all data from the database
     */
    function getData() {
        $cURL = $this->cURL();
        if($cURL->errno !== 0) return array(); //Prevent error returns

        //Check if we did not just a JSON Array instead of JSON Object
        if(is_array($cURL->response)) {
            return $cURL->response;
        }

        //Got JSON Object, so get first array (Might need to make a setting option for this??)
        foreach($cURL->response as $key => $value) {
            if(is_array($value)) {
                return $cURL->response->$key; //Return first array value in JSON Object
            }
        }

        //Return empty array as we got no data
        return array();
    }

    /**
     * Return headers of data 
     */
    function getHeaders() {
        //Get data we return, go over key values of JSON
        $headers = array();
        foreach($this->getData()[0] as $key => $value) {
            array_push($headers, $key);
        }

        //Return found keys from dataset
        return $headers;
    }

    function setFields($data) {
        $data['rest_method'] = $this->data->method;
        $data['rest_url'] = $this->data->url;
        $data['rest_data'] = $this->data->data;

        return $data;
    }

    /**
     * Form fields for connection type within admin page
     */
    function fields() {
        ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr class="form-field form-required">
                        <th scope="row">
                            <label for="rest_url">URL <span class="description">(vereist)</span></label>
                        </th>
                        <td>
                            <div style="display:flex;gap:8px;width: 95%;">
                                <input name="rest_url" type="text" id="rest_url" placeholder="Rest API Endpoint" value="<?php if(isset($_POST['rest_url'])) echo $_POST['rest_url']; ?>" autocapitalize="none" autocorrect="off" autocomplete="off">
                                
                                <select name="rest_method" id="rest_method" style="width: 120px;">
                                    <option <?php if(isset($_POST['rest_method']) && $_POST['rest_method'] == 'GET') echo "selected"; ?>>GET</option>
                                    <option <?php if(isset($_POST['rest_method'])  && $_POST['rest_method'] == 'POST') echo "selected"; ?>>POST</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr class="form-field form-required">
                        <th scope="row">
                            <label for="rest_data">POST Data <span class="description"></span></label>
                        </th>
                        <td>
                            <textarea 
                                name="rest_data" 
                                type="text" 
                                id="rest_data" 
                                placeholder="JSON geformatteerde POST data." 
                                autocapitalize="none" 
                                autocorrect="off" 
                                autocomplete="off"
                            ><?php if(isset($_POST['rest_data'])) echo $_POST['rest_data']; ?></textarea>
                        </td>
                    </tr>
                    <tr class="form-field form-required">
                        <th scope="row">
                            
                        </th>
                        <td>
                        <input style="float:right;" type="submit" name="testsensor" id="testsensor" class="button button-primary" value="Test Connectie">
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php
    }
}