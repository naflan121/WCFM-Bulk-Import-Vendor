<?php
/*
Plugin Name: CSV Merchant Importer
Description: Import merchant from a CSV file and assign the wcfm_vendor role.
Version: 1.0.0
Author: Mohamed Naflan
*/

// Plugin activation hook
register_activation_hook(__FILE__, 'csv_user_importer_activate');

function csv_user_importer_activate()
{
    // Add any activation tasks here
}

// Plugin deactivation hook
register_deactivation_hook(__FILE__, 'csv_user_importer_deactivate');

function csv_user_importer_deactivate()
{
    // Add any deactivation tasks here
}




// Function to handle CSV user import
function csv_user_importer_process()
{
    // Get the uploaded file information
    $file = $_FILES['csv_file'];

    // Check if a file was uploaded
    if (!empty($file['tmp_name'])) {
        $csv_file_path = $file['tmp_name'];

        // Read the CSV file
        if (($handle = fopen($csv_file_path, 'r')) !== false) {
            // Get the column headers from the first row
            $headers = fgetcsv($handle);

            // Find the index of the custom fields in the headers
            $store_name_index = array_search('Store Name', $headers);
            $mid_index = array_search('MID', $headers);
            $hash_index = array_search('Hash', $headers);
            $stall_number_index = array_search('Stall Number', $headers);
            $category_index = array_search('Category', $headers);

            $food_truck_index = array_search('Food Truck', $headers);
            $retail_index = array_search('Retail', $headers);
            $airventure_index = array_search('AirVenture', $headers);
            $food_garden_index = array_search('Food And Garden', $headers);
            // Loop through each row
            while (($data = fgetcsv($handle)) !== false) {
                $email    = $data[0];
                $username = $data[1];
                $phone    = $data[2];
                $password = $data[3];
                $mid      = $data[$mid_index];
                $hash     = $data[$hash_index];
                $stall_number = $data[$stall_number_index];
                $store_category = $data[$category_index];
                $store_name = $data[$store_name_index];



                //store category mapping 
                $food_truck = $data[$food_truck_index];
                $retail = $data[$retail_index];
                $airventure = $data[$airventure_index];
                $food_garden = $data[$food_garden_index];

                //category_id 
                $food_truck_cat = "1";
                $retail_cat = "2";
                $food_garden_cat = "3";
                $airventure_cat = "4";


                //push category id to array 

                $wcfm_profile_form = array(
                    'wcfm_vendor_store_categories' => array()
                );

                if ($food_truck === "1") {
                    $wcfm_profile_form['wcfm_vendor_store_categories'][] = $food_truck_cat;
                }
                
                if ($retail === "1") {
                    $wcfm_profile_form['wcfm_vendor_store_categories'][] = $retail_cat;
                }
                
                if ($food_garden === "1") {
                    $wcfm_profile_form['wcfm_vendor_store_categories'][] = $food_garden_cat;
                }
                
                if ($airventure === "1") {
                    $wcfm_profile_form['wcfm_vendor_store_categories'][] = $airventure_cat;
                }





                // Check if the user already exists
                $user = get_user_by('email', $email);
                if ($user) {
                    // User already exists, update the user's details or custom fields
                    $user_id = $user->ID;

                    // Update user's details
                    wp_update_user(array(
                        'ID'         => $user_id,
                        'user_login' => $username,
                        'display_name' => $store_name
                    ));

                    // Update user's custom fields
                    update_user_meta($user_id, 'wcfm_vendor_store_mid', $mid);
                    update_user_meta($user_id, 'wcfm_vendor_store_hash', $hash);
                    update_user_meta($user_id, 'wcfm_vendor_store_stall_number', $stall_number);
                    update_user_meta($user_id, 'wcfmmp_store_name', $store_name);

                    // Additional data and customization can be done here
                    $wcfm_settings_form_data_storetype = array();
                    $wcfm_settings_form_data_storetype['store_number'] = $stall_number;
                    $wcfm_settings_form_data_storetype['store_mid'] = $mid;
                    $wcfm_settings_form_data_storetype['store_hash'] = $hash;
                    $wcfm_settings_form_data_storetype['wcfmmp_store_name'] = $store_name;
                    // $wcfm_settings_form_data_storetype['wcfm_vendor_store_categories'] = $store_category;



                    update_user_meta($user_id, 'wcfmmp_profile_settings', $wcfm_settings_form_data_storetype);

                    //if (function_exists('wcfmsc_profile_store_categories_settings_update')) {
                    // Call the function

                    // }

                    if (is_plugin_active('wc-frontend-manager-store-categories/wc-frontend-manager-store-categories.php')) {
                        // Plugin is active, include the necessary file
                        require_once WP_PLUGIN_DIR . '/wc-frontend-manager-store-categories/core/class-wcfmsc-frontend.php';

                        // Create an instance of the WCFMsc_Frontend class
                        $wcfmsc_frontend = new WCFMsc_Frontend();

                        // Call the desired function
                        $wcfmsc_frontend->wcfmsc_profile_store_categories_settings_update($user_id, $wcfm_profile_form);
                    }



                    continue;
                }



                // Create the user
                $user_id = wp_create_user($username, $password, $email);

                //update store display name

                wp_update_user(array(
                    'ID'         => $user_id,
                    'user_login' => $username,
                    'display_name' => $store_name
                ));

                // Assign the wcfm_vendor role to the user
                $user = new WP_User($user_id);
                $user->set_role('wcfm_vendor');

                //WCFM Setting update start here 

                $wcfm_settings_form_data_storetype = array();
                $wcfm_settings_form_data_storetype['store_number'] = $stall_number;
                $wcfm_settings_form_data_storetype['store_mid'] = $mid;
                $wcfm_settings_form_data_storetype['store_hash'] = $hash;
                $wcfm_settings_form_data_storetype['wcfmmp_store_name'] = $store_name;
                $wcfm_settings_form_data_storetype['wcfm_vendor_store_categories'] = $store_category;

                update_user_meta($user_id, 'wcfmmp_profile_settings', $wcfm_settings_form_data_storetype);


                update_user_meta($user_id, 'wcfmmp_profile_settings', $wcfm_settings_form_data_storetype);

                if (is_plugin_active('wc-frontend-manager-store-categories/wc-frontend-manager-store-categories.php')) {
                    // Plugin is active, include the necessary file
                    require_once WP_PLUGIN_DIR . '/wc-frontend-manager-store-categories/core/class-wcfmsc-frontend.php';

                    // Create an instance of the WCFMsc_Frontend class
                    $wcfmsc_frontend = new WCFMsc_Frontend();

                    // Call the desired function
                    $wcfmsc_frontend->wcfmsc_profile_store_categories_settings_update($user_id, $wcfm_profile_form);
                }

                //WCFM Setting update end here 

                // Set custom user meta fields
                update_user_meta($user_id, 'wcfm_vendor_store_mid', $mid);
                update_user_meta($user_id, 'wcfm_vendor_store_hash', $hash);
                update_user_meta($user_id, 'wcfm_vendor_store_stall_number', $stall_number);
                update_user_meta($user_id, 'wcfmmp_store_name', $store_name);

                // Additional data and customization can be done here

            }

            // Close the CSV file
            fclose($handle);

            // Redirect back to the admin page after import
            wp_redirect(admin_url('admin.php?page=csv_user_importer&imported=true'));
            exit;
        }
    }
}

// Function to render the admin page
function csv_user_importer_admin_page()
{
    if (isset($_GET['imported']) && $_GET['imported'] == 'true') {
        csv_user_importer_success_message();
    }
?>
    <div class="wrap">
        <h1>CSV User Importer</h1>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv">
            <?php wp_nonce_field('csv_user_importer', 'csv_user_importer_nonce'); ?>
            <input type="submit" class="button button-primary" value="Import Users">
        </form>
    </div>
<?php
}

// Function to handle the form submission
function csv_user_importer_handle_form_submission()
{
    // Verify the nonce for security
    if (isset($_POST['csv_user_importer_nonce']) && wp_verify_nonce($_POST['csv_user_importer_nonce'], 'csv_user_importer')) {
        csv_user_importer_process();
    }
}

// Add the admin menu page
function csv_user_importer_add_admin_page()
{
    add_menu_page(
        'CSV User Importer',
        'CSV Importer',
        'manage_options',
        'csv_user_importer',
        'csv_user_importer_admin_page',
        'dashicons-upload',
        50
    );
}




// Hook into WordPress admin menu and form submission
add_action('admin_menu', 'csv_user_importer_add_admin_page');
add_action('admin_init', 'csv_user_importer_handle_form_submission');



// Function to display the success message
function csv_user_importer_success_message()
{
?>
    <div class="notice notice-success is-dismissible">
        <p>Merchant import completed successfully.</p>
    </div>
<?php
}
