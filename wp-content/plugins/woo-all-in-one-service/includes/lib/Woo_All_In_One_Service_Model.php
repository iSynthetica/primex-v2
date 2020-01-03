<?php


class Woo_All_In_One_Service_Model {
    private static $repairs_table_name = 'wooaioservice_repairs';
    private static $repairsmeta_table_name = 'wooaioservice_repairsmeta';

    private static function get_schema($table = 'repairs') {
        global $wpdb;
        $max_index_length = 191;
        $repairs_table_name = $wpdb->prefix . self::$repairs_table_name;
        $repairsmeta_table_name = $wpdb->prefix . self::$repairsmeta_table_name;

        $charset_collate = $wpdb->get_charset_collate();

        $repairs = "CREATE TABLE {$repairs_table_name} (
	ID bigint(20) unsigned NOT NULL auto_increment,
	author bigint(20) unsigned NOT NULL default '0',
	name varchar(255) NOT NULL,
	email varchar(255) NOT NULL,
	phone varchar(255) NOT NULL,
	created datetime NOT NULL default '0000-00-00 00:00:00',
	fault longtext NOT NULL,
	title text NOT NULL,
	product text NOT NULL,
	status varchar(20) NOT NULL default 'wait',
	modified datetime NOT NULL default '0000-00-00 00:00:00',
	PRIMARY KEY  (ID),
	KEY status_date (status,created,ID),
	KEY author (author)
) $charset_collate;";

        $repairsmeta = "CREATE TABLE {$repairsmeta_table_name} (
	meta_id bigint(20) unsigned NOT NULL auto_increment,
	repair_id bigint(20) unsigned NOT NULL default '0',
	meta_key varchar(255) default NULL,
	meta_value longtext,
	PRIMARY KEY  (meta_id),
	KEY repair_id (repair_id),
	KEY meta_key (meta_key($max_index_length))
) $charset_collate;";

        $sql_array = array(
            'repairs' => $repairs,
            'repairsmeta' => $repairsmeta,
        );

        if (!empty($sql_array[$table])) {
            return $sql_array[$table];
        }

        return false;
    }

    public static function create_repairs_table() {
        global $wpdb;

        $schema = self::get_schema('repairs');

        if (!$schema) {
            return;
        }

        $wpdb->hide_errors();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta( $schema );
    }

    public static function create_repairsmeta_table() {
        global $wpdb;

        $schema = self::get_schema('repairsmeta');

        if (!$schema) {
            return;
        }

        $wpdb->hide_errors();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta( $schema );
    }

    public static function create($data) {
        global $wpdb;
        $repairs_table_name = $wpdb->prefix . self::$repairs_table_name;
        $now = time();
        $created = gmdate( 'Y-m-d H:i:s', $now );

        $wpdb->insert(
            $repairs_table_name,
            array(
                'author' => $data['repair_author'],
                'title' => 'NEW REPAIR',
                'name' => $data['repair_name'],
                'email' => $data['repair_email'],
                'phone' => $data['repair_phone'],
                'product' => $data['repair_product'],
                'fault' => $data['repair_fault'],
                'created' => $created,
                'status' => 'wait',
                'modified' => $created,
            ),
            array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        $id = (int) $wpdb->insert_id;

        $wpdb->update(
            $repairs_table_name,
            array('title' => $id . '/' . date('ymdHis', $now)),
            array('ID' => $id),
            array('%s')
        );

        return $id;
    }

    public static function get($where = array()) {
        global $wpdb;
        $repairs_table_name = $wpdb->prefix . self::$repairs_table_name;
        $sql = "SELECT * FROM {$repairs_table_name} WHERE 1 = 1";
        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }
}