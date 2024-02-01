<?php

use ModulairDashboard\DashboardPlugin;
use ModulairDashboard\Dashboard;

/**
 * Render code for settings form shortcode.
 */
function dashboard_shortcode_form_user_settings() {
        if(!is_user_logged_in()) return; //Not loggedin so we cannot load settings

        $current_user = get_userdata(get_current_user_id());
        ob_start();
    ?>
        <!-- User Settings Form -->
        <div class="widget-container">
            <div class="widget-title">
                <span>Profiel instellingen</span>
                <div>
                    <button class="hideb" onclick="hide(this)" style="display:inline-block;"><i class="fas fa-chevron-up" aria-hidden="true"></i></button>
                    <button class="showb" onclick="show(this)" style="display:none;"><i class="fas fa-chevron-down" aria-hidden="true"></i></button>
                </div>
            </div>
            <div class="widget-content">
                <form id="user-settings" class="user-settings" method="post">
                    <?php wp_nonce_field('dashboard_user_settings', 'settings_nonce'); ?>

                    <table class="form-table">
                        <tbody>
                            <tr class="form-field">
                                <th><label for="first_name"><?php _e('Voornaam'); ?></label></th>
                                <td><input style="width: 100%;" name="first_name" id="first_name" type="text" value="<?php echo esc_attr($current_user->user_firstname); ?>" required minlength="4"/></td>
                            </tr>
                            <tr class="form-field">
                                <th><label for="last_name"><?php _e('Achternaam'); ?></label></th>
                                <td><input style="width: 100%;" name="last_name" id="last_name" type="text" value="<?php echo esc_attr($current_user->user_lastname); ?>" required minlength="4"/></td>
                            </tr>

                            <tr class="form-field">
                                <th><label for="email"><?php _e('Email'); ?></label></th>
                                <td><input style="width: 100%;" name="email" id="email" type="email" value="<?php echo esc_attr($current_user->user_email); ?>" required/></td>
                            </tr>
                            <tr>
                                <th>
                                    <button class="form-button submit" name="settingsform" type="button" id="submit-settings" class="form-submit"><?php _e('Save'); ?></button>
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>

        <!-- Password Forum -->
        <div class="widget-container">
            <div class="widget-title">
                <span>Wachtwoord instellingen</span>
                <div>
                    <button class="hideb" onclick="hide(this)" style="display:inline-block;"><i class="fas fa-chevron-up" aria-hidden="true"></i></button>
                    <button class="showb" onclick="show(this)" style="display:none;"><i class="fas fa-chevron-down" aria-hidden="true"></i></button>
                </div>
            </div>
            <div class="widget-content">
                <form id="user-password" class="user-password" method="post">
                    <?php wp_nonce_field('dashboard_user_password', 'password_nonce'); ?>

                    <table class="form-table">
                        <tbody>
                            <tr class="form-field">
                                <th><label for="password"><?php _e('Password'); ?></label></th>
                                <td><input style="width: 100%;" name="password" id="password" type="password" required/></td>
                            </tr>
                            <tr class="form-field">
                                <th><label for="repeatpassword"><?php _e('Herhaal'); ?> <?php _e('Password'); ?></label></th>
                                <td><input style="width: 100%;" name="repeatpassword" id="repeatpassword" type="password" required/></td>
                            </tr>
                            <tr>
                                <th>
                                    <button class="form-button submit" name="passwordform" type="button" id="submit-password" class="form-submit"><?php _e('Save'); ?></button>
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>

        <!-- Forum submission JQuery -->
        <script>
            jQuery(document).ready(function($) {
                $('#submit-settings').click(function() {
                    var inputs = $('#user-settings').find('input');
                    var isValid = true;
                    inputs.each(function () { //Run validity checks on all inputs
                        if (!this.checkValidity()) {
                            isValid = false;
                            this.reportValidity(); // Display the validation error message
                        }
                    });
                    if(!isValid) return;

                    // Serialize form data
                    var formData = $('#user-settings').serialize();
                    formData += '&action=dashboard_form_submit_user_settings';

                    Dashboard.showLoader($('#submit-settings').closest('div'));

                    // AJAX request
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo admin_url('admin-post.php'); ?>',
                        data: formData,
                        success: function(response) {
                            // Handle the AJAX response (you can update UI or show a success message)
                            if(response.success) Dashboard.showToast({ backgroundColor: "#4CAF50", color: "#fff" }, response.data.message, 3);
                            else Dashboard.showToast({ backgroundColor: "#e94b4b", color: "#fff" }, response.data[0].message, 3);

                            Dashboard.removeLoader($('#submit-settings').closest('div'));
                        },
                        error: function(xhr, status, error) {
                            // Handle AJAX errors
                            Dashboard.showToast({ backgroundColor: "#e94b4b", color: "#fff" }, "Woops! Er is iets mis gegaan...", 3);
                            console.error(xhr.responseText);
                        }
                    });
                });
                $('#submit-password').click(function() {
                    var inputs = $('#user-password').find('input');
                    var isValid = true;
                    inputs.each(function () { //Run validity checks on all inputs
                        if (!this.checkValidity()) {
                            isValid = false;
                            this.reportValidity(); // Display the validation error message
                        }
                    });
                    if(!isValid) return;

                    // Serialize form data
                    var formData = $('#user-password').serialize();
                    formData += '&action=dashboard_form_submit_user_password';

                    Dashboard.showLoader($('#submit-password').closest('div'));

                    // AJAX request
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo admin_url('admin-post.php'); ?>',
                        data: formData,
                        success: function(response) {
                            // Handle the AJAX response (you can update UI or show a success message)
                            if(response.success) Dashboard.showToast({ backgroundColor: "#4CAF50", color: "#fff" }, response.data.message, 3);
                            else Dashboard.showToast({ backgroundColor: "#e94b4b", color: "#fff" }, response.data[0].message, 3);

                            Dashboard.removeLoader($('#submit-password').closest('div'));
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                        },
                        error: function(xhr, status, error) {
                            // Handle AJAX errors
                            Dashboard.showToast({ backgroundColor: "#e94b4b", color: "#fff" }, "Woops! Er is iets mis gegaan...", 3);
                            console.error(xhr.responseText);
                        }
                    });
                });
            });
        </script>
    <?php
    $html_content = ob_get_clean();
    return $html_content;
};

/**
 * Function to handle the processing of the form in the above shortcode
 */
function dashboard_form_submit_user_settings() {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check the nonce for security
        $nonce = isset($_POST['settings_nonce']) ? $_POST['settings_nonce'] : '';

        if (!wp_verify_nonce($nonce, 'dashboard_user_settings')) {
            // Nonce verification failed, handle accordingly (e.g., show an error, redirect, etc.)
            wp_send_json_error(new WP_Error( 'nonce.invalid', "We hebben uw verzoek momenteel niet kunnen verwerken, probeer het nog een keer.", '' ));
            die();
        }

        $user_data = array(
            'ID'         => get_current_user_id(),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name'  => sanitize_text_field($_POST['last_name']),
            'user_email' => sanitize_email($_POST['email']),
            'display_name' => sanitize_text_field($_POST['first_name']) . ' ' . sanitize_text_field($_POST['last_name'])
        );

        $result = wp_update_user($user_data); 
        if (is_wp_error($result)) {
            //Send notification of success to front-end
            wp_send_json_error(new WP_Error( 'wp.is.error', "We hebben uw gebruikers instellingen niet kunnen bijwerken, probeer het op een later moment nog een keer. (".$result->get_error_code().")", '' ));
            die();
        } else {
            //Send notification of success to front-end
            wp_send_json_success(array("message" => "Uw gebruikers instellingen zijn bijgewerkt!"));
            die();
        }
    }
}

/**
 * Function to handle the processing of the form in the above shortcode
 */
function dashboard_form_submit_user_password() {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check the nonce for security
        $nonce = isset($_POST['password_nonce']) ? $_POST['password_nonce'] : '';

        if (!wp_verify_nonce($nonce, 'dashboard_user_password')) {
            // Nonce verification failed, handle accordingly (e.g., show an error, redirect, etc.)
            wp_send_json_error(new WP_Error( 'nonce.invalid', "We hebben uw verzoek momenteel niet kunnen verwerken, probeer het nog een keer.", '' ));
            die();
        }

        if(sanitize_text_field($_POST['password']) != sanitize_text_field($_POST['repeatpassword'])) {
            //Same check not passed
            wp_send_json_error(new WP_Error( 'password.not.same', "Uw twee wachtwoord velden komen niet overeen!", '' ));
            die();
        }

        wp_set_password(sanitize_text_field($_POST['password']), get_current_user_id());

        wp_send_json_success(array("message" => "We hebben uw wachtwoord bijgewerkt!"));
        die();
    }
}