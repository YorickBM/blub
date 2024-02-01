<?php

namespace ModulairDashboard;

//TODO: Make this a class!!

/**
 * Override email a new user receives on creation in admin dashboard
 */
function dashboard_new_user_email_notification( $email, $user, $blogname ) {
    $key = get_password_reset_key( $user );
    $replacements = array(
        "%email%" => $user->user_email,
        "%user%" => $user->display_name,
        "%role%" => implode(', ', array_map(function($role) { return ucfirst($role); }, array_values($user->roles))),
        "%url%" => network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ),
    );

    ob_start();
    include DashboardPlugin::get_instance()->getDir() . '/mails/mail.new.user.html';
    $content = ob_get_clean();

    $email['headers'] = array('Content-Type: text/html; charset=UTF-8');
    $email['subject'] = sprintf( '%s - Account registratie', $blogname );
	$email['message'] = str_replace(array_keys($replacements), array_values($replacements), $content);
	
    return $email;
}

/**
 * OVerride email a user receives on password reset request
 */
function dashboard_reset_password_notification( $email, $key, $user_login, $user_data ) {
    $replacements = array(
        "%email%" => $user_data->user_email,
        "%user%" => $user_data->display_name,
        "%ip%" => DashboardPlugin::get_instance()->getClientIP(),
        "%url%" => network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ),
    );

    ob_start();
    include DashboardPlugin::get_instance()->getDir() . '/mails/mail.reset.password.html';
    $content = ob_get_clean();

    $email['headers'] = array('Content-Type: text/html; charset=UTF-8');
    $email['subject'] = sprintf('%s - Wachtwoord resetten', get_bloginfo('name'));
	$email['message'] = str_replace(array_keys($replacements), array_values($replacements), $content);
	return $email;
}

/**
 * OVerride email a user receives on email update
 */
function dashboard_update_user_email($email_change_email, $user, $userdata) {
    $replacements = array(
        "%new_email%" => $userdata['user_email'],
        "%old_email%" => $user['user_email'],
        "%user%" => $userdata['display_name'],
        "%ip%" => DashboardPlugin::get_instance()->getClientIP(),
    );

    ob_start();
    include DashboardPlugin::get_instance()->getDir() . '/mails/mail.update.email.html';
    $content = ob_get_clean();

    $email_change_email['headers'] = array('Content-Type: text/html; charset=UTF-8');
    $email_change_email['subject'] = sprintf('%s - E-mailadres gewijzigd', get_bloginfo('name'));
    $email_change_email['message'] = str_replace(array_keys($replacements), array_values($replacements), $content);

    return $email_change_email;
}