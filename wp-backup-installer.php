<?php
/*
Plugin Name: WP Backup Installer
Plugin URI: https://github.com/babaralijamali/wp-backup-installer
Description: A simple and fastest plugin to restore WordPress backups.
Version: 1.0
Author: Babar Ali Jamali
Author URI: https://www.facebook.com/babaralijamali.official or https://github.com/babaralijamali
License: GPL2
*/

defined('ABSPATH') or die('You cannot access this file.');

class WPBackupInstaller {

    // Constructor to add hooks
    public function __construct() {
        add_action('admin_menu', array($this, 'create_menu'));
    }

    // Create menu for the plugin
    public function create_menu() {
        add_menu_page('Backup Installer', 'Backup Installer', 'manage_options', 'wp-backup-installer', array($this, 'restore_page'));
    }

    // Create the page layout
    public function restore_page() {
        echo '<div class="wrap">';
        echo '<h1>Backup Installer</h1>';
        echo '<form method="post" enctype="multipart/form-data">';
        echo '<input type="file" name="backup_file" class="button">';
        echo '<br><br>';
        echo '<input type="submit" name="restore_now" class="button button-primary" value="Restore Backup Now">';
        echo '</form>';

        if (isset($_FILES['backup_file']) && isset($_POST['restore_now'])) {
            $this->restore_backup($_FILES['backup_file']);
        }

        echo '</div>';
    }

    // Function to restore the backup
    public function restore_backup($backup_file) {
        if ($backup_file['error'] !== UPLOAD_ERR_OK) {
            echo '<div class="error"><p>Error uploading file. Please try again.</p></div>';
            return;
        }

        // Check if the uploaded file is an SQL file
        $file_type = pathinfo($backup_file['name'], PATHINFO_EXTENSION);
        if ($file_type !== 'sql') {
            echo '<div class="error"><p>Invalid file type. Please upload a .sql backup file.</p></div>';
            return;
        }

        // Upload the file to a temporary location
        $file_path = WP_CONTENT_DIR . '/uploads/' . $backup_file['name'];
        move_uploaded_file($backup_file['tmp_name'], $file_path);

        // Execute the SQL commands to restore the database
        global $wpdb;
        $sql_content = file_get_contents($file_path);

        // Split the SQL file into individual queries
        $queries = explode(";\n", $sql_content);
        foreach ($queries as $query) {
            if (trim($query) !== '') {
                $wpdb->query($query);
            }
        }

        // Remove the uploaded file after restoring
        unlink($file_path);

        echo '<div class="updated"><p>Backup restored successfully!</p></div>';
    }
}

new WPBackupInstaller();
