<h1>Import Users</h1>
<?php
include WOOAIOIE_PATH . 'admin/partials/menu.php';

global $wpdb;

$users_ser = '';

if (empty($users_ser)) {
    return;
}

$users = unserialize($users_ser);

if (empty($users)) {
    return;
}

echo "<pre>";
$prefix = $wpdb->prefix;
foreach ($users as $user_id => $user) {
    if ('syntheticafreon@gmail.com' === trim($user['user_email'])) {
        continue;
    }
    $sql = "SELECT ID FROM {$wpdb->users} WHERE user_email = '{$user['user_email']}'";
    $email_exists = $wpdb->get_row( $sql, ARRAY_A );

    if (!empty($email_exists)) {
        $created_user_id = $email_exists['ID'];
        update_user_meta( $created_user_id, '_import_user_ID', $user['ID'] );
        update_user_meta( $created_user_id, '_import_user_data', serialize($user) );
    } else {
        $wpdb->insert(
            $wpdb->users,
            array(
                'user_login' => $user['user_login'],
                'user_pass' => $user['user_pass'],
                'user_nicename' => $user['user_nicename'],
                'user_email' => $user['user_email'],
                'user_url' => $user['user_url'],
                'user_registered' => $user['user_registered'],
                'user_activation_key' => $user['user_activation_key'],
                'user_status' => $user['user_status'],
                'display_name' => $user['display_name'],
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
        );

        $created_user_id = $wpdb->insert_id;
        update_user_meta( $created_user_id, '_import_user_ID', $user['ID'] );
        update_user_meta( $created_user_id, '_import_user_data', serialize($user) );
    }

    if (!empty($user['capabilities'])) {
        $capabilities = unserialize($user['capabilities']);
        update_user_meta( $created_user_id, $prefix . 'capabilities', $capabilities );
    }

    if (!empty($user['user_level'])) {
        update_user_meta( $created_user_id, $prefix . 'user_level', $user['user_level'] );
    }

//    print_r($user['user_email']);
//    echo PHP_EOL;
//    print_r($created_user_id);
//    echo PHP_EOL;

    foreach ($user['usermeta'] as $usermeta_key => $usermeta_value) {
        update_user_meta( $created_user_id, $usermeta_key, $usermeta_value );
    }
    print_r($user);
}
echo "</pre>";

