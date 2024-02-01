<?php
use ModulairDashboard\Dataset;
use ModulairDashboard\DashboardPlugin;
use ModulairDashboard\SelectQuery;

/**
 * MySQL Overhead dataset to be used within sensors
 * @author - Yorick <info@yorickblom.nl>
 */
class MySQL implements Dataset {

    private $dbh;
    private $data;

    public function getDelay() { return 30; } //Only update every 30 seconds, this way if many sensors load they use the same data.

    public function toString() {
        return "MySQL";
    }

    public function __toString() {
        return "MySQL";
    }

    /**
     * Constructor
     */
    public function __construct($host = null, $port = null, $dbname = null, $user = null, $password = null, $table = null) {
        $this->data = (object) array(
            "host" => $host,
            "port" => $port,
            
            "dbname" => $dbname,
            "table" => $table,

            "user" => $user,
            "password" => $password 
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
        
        $object->host = DashboardPlugin::get_instance()->encrypt($this->data->host);
        $object->user = DashboardPlugin::get_instance()->encrypt($this->data->user);
        $object->password = DashboardPlugin::get_instance()->encrypt($this->data->password);
        
        return $object;
    }

    /**
     * Decode encoded data
     */
    private function decode() {
        $this->data->host = DashboardPlugin::get_instance()->decrypt($this->data->host);
        $this->data->user = DashboardPlugin::get_instance()->decrypt($this->data->user);
        $this->data->password = DashboardPlugin::get_instance()->decrypt($this->data->password);
    }

    /**
     * Basic connect & disconnect hook
     */
    function connect() {
        $this->dbh = new PDO("mysql:host=".$this->data->host.";port=".$this->data->port.";dbname=".$this->data->dbname.";", $this->data->user, $this->data->password);
    }
    function disconnect() {
        $this->dbh = null;
    }

    /**
     * Get all data from the database
     */
    function getData() {
        if($this->dbh == null) $this->connect();

        $query = new SelectQuery($this->dbh, $this->data->table, "*");
        return $query->execute();
    }

    /**
     * Return headers of data 
     */
    function getHeaders() {
        if($this->dbh == null) $this->connect();

        $query = new SelectQuery($this->dbh, $this->data->table, "*");
        $data = $query->limit(1)->execute();
            
        return array_keys($data[0]);
    }

    function setFields($data) {
        $data['mysql_ip'] = $this->data->host;
        $data['mysql_port'] = $this->data->port;
        $data['mysql_user'] = $this->data->user;
        $data['mysql_password'] = $this->data->password;
        $data['mysql_dbname'] = $this->data->dbname;
        $data['mysql_table'] = $this->data->table;
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
                            <label for="mysql_ip">IP <span class="description">(vereist)</span></label>
                        </th>
                        <td>
                            <div style="display:flex;gap:8px;width: 95%;">
                                <input name="mysql_ip" type="text" id="mysql_ip" placeholder="MySQL database ip" value="<?php if(isset($_POST['mysql_ip'])) echo $_POST['mysql_ip']; ?>" autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60">
                                <input name="mysql_port" type="number" id="mysql_port" placeholder="MySQL port" style="width: 120px;" value="<?php if(isset($_POST['mysql_port'])) echo $_POST['mysql_port']; ?>" autocapitalize="none" autocorrect="off" autocomplete="off" min=1>
                            </div>
                        </td>
                    </tr>
                    <tr class="form-field form-required">
                        <th scope="row">
                            <label for="mysql_user">Gebruikersnaam <span class="description">(vereist)</span></label>
                        </th>
                        <td>
                            <input name="mysql_user" type="text" id="mysql_user" placeholder="Database gebruikersnaam" value="<?php if(isset($_POST['mysql_user'])) echo $_POST['mysql_user']; ?>" autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60">
                        </td>
                    </tr>
                    <tr class="form-field form-required">
                        <th scope="row">
                            <label for="mysql_password">Wachtwoord <span class="description">(vereist)</span></label>
                        </th>
                        <td>
                            <input name="mysql_password" type="text" id="mysql_password" placeholder="Database wachtwoord" value="<?php if(isset($_POST['mysql_password'])) echo $_POST['mysql_password']; ?>" autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60">
                        </td>
                    </tr>
                    <tr class="form-field form-required">
                        <th scope="row">
                            <label for="mysql_dbname">Database <span class="description">(vereist)</span></label>
                        </th>
                        <td>
                            <input name="mysql_dbname" type="text" id="mysql_dbname" placeholder="Database naam" value="<?php if(isset($_POST['mysql_dbname'])) echo $_POST['mysql_dbname']; ?>" autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60">
                        </td>
                    </tr>
                    <tr class="form-field form-required">
                        <th scope="row">
                            <label for="mysql_table">Tabel <span class="description">(vereist)</span></label>
                        </th>
                        <td>
                            <input name="mysql_table" type="text" id="mysql_table" placeholder="Database tabel" value="<?php if(isset($_POST['mysql_table'])) echo $_POST['mysql_table']; ?>" autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60">
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