<?php

namespace ModulairDashboard;

class AdminWordpress {
    public static function register() {
        //Remove unecesarry menus
        remove_menu_page('edit-comments.php');
    
        //Remove posts menu, so we can create our custom interaction overlay withit
        remove_menu_page('edit.php');
    }
}