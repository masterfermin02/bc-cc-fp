<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21/12/2018
 * Time: 9:38 AM
 */



if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class BC_cc_fp_list_users extends WP_List_Table {

    /** Class constructor */
    public function __construct() {

        parent::__construct( [
            'singular' => __( 'User', 'bc-cc-fp' ), //singular name of the listed records
            'plural'   => __( 'Users', 'bc-cc-fp' ), //plural name of the listed records
            'ajax'     => false //does this table support ajax?
        ] );

    }


    /**
     * Retrieve customers data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_customers( $per_page = 5, $page_number = 1 ) {

        $args = [
            'role' => isset($_REQUEST['role']) ? $_REQUEST['role'] : '',
            'count_total' => true,
            'fields' => ['ID', 'display_name', 'user_email', 'Role'],
            'orderby' => esc_sql( $_REQUEST['orderby'] ),
            'order' => ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC',
            'number' => $per_page,
            'offset' => ( $page_number - 1 ) * $per_page,
        ];

        $users = new WP_User_Query( $args );

        return $users->get_results();
    }


    /**
     * Delete a customer record.
     *
     * @param int $id customer ID
     */
    public static function delete_customer( $id ) {
        global $wpdb;

        $wpdb->delete(
            "{$wpdb->prefix}users",
            [ 'ID' => $id ],
            [ '%d' ]
        );
    }


    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count() {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}users";

        return $wpdb->get_var( $sql );
    }


    /** Text displayed when no customer data is available */
    public function no_items() {
        _e( 'No users avaliable.', 'bc-cc-fp' );
    }


    /**
     * Render a column when no column specific method exist.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        print_r( $item, true );
        switch ( $column_name ) {
            case 'display_name':
                $update_nonce = wp_create_nonce( 'bc_cc_fp_update_user_status' );
                $actions = [
                    'edit' => sprintf(
                        '<a href="?page=bcccfp_users_edit&user_id=%s">%s</a>',
                        $item->ID,
                        __('Edit', 'bc-cc-fp')
                    ),
                    'update_status' => sprintf(
                        '<a href="?page=%s&action=update_status&user_id=%s&_wponce=%s">%s</a>',
                        $_REQUEST['page'],
                        $item->ID,
                        $update_nonce,
                        __('Active / Inactive', 'bc-cc-fp')
                    ),
                ];
                return sprintf('%s %s',
                    $item->display_name,
                    $this->row_actions($actions)
                );
            case 'user_email':
                return $item->$column_name;
            case 'roles':
                return implode(',', $item->$column_name);
            case 'user_status':
                return $item->$column_name ? 'Active' : 'Inactive';
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="user_id[]" value="%s" />', $item->ID
        );
    }


    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_name( $item ) {

        $update_nonce = wp_create_nonce( 'bc_cc_fp_update_user_status' );

        $title = '<strong>' . $item['name'] . '</strong>';

        $actions = [
            'update_status' => sprintf( '<a href="?page=%s&action=%s&users=%s&_wpnonce=%s">Update</a>', esc_attr( $_REQUEST['page'] ), 'update_status', absint( $item['ID'] ), $update_nonce )
        ];

        return $title . $this->row_actions( $actions );
    }


    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
        $columns = [
            'cb'      => '<input type="checkbox" />',
            'display_name'    => __( 'Name', 'bc-cc-fp' ),
            'user_email' => __( 'Email', 'bc-cc-fp' ),
            'roles'    => __( 'Role', 'bc-cc-fp' ),
            'user_status'    => __( 'Status', 'bc-cc-fp' )
        ];

        return $columns;
    }


    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'display_name' => array( 'display_name', true ),
            'user_email' => array( 'user_email', false )
        );

        return $sortable_columns;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
            'bulk_update_user_status' => __('Toggle User Status', 'bc-cc-fp')
        ];

        return $actions;
    }


    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];
        $this->process_bulk_action();
        $per_page = 10;
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' )  : 1;
        $offset = ( $paged - 1 ) * $per_page;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'display_name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';
        $role = isset($_REQUEST['role']) ? $_REQUEST['role'] : '';
        $args = [
            'role' => $role,
            'count_total' => true,
            'orderby' => $orderby,
            'order' => $order,
            'number' => $per_page,
            'offset' => $offset,
        ];
        $users = new WP_User_Query( $args );
        $this->items = $users->get_results();
        $total_items = $users->get_total();
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    public function process_bulk_action() {

        //update user status when a bulk action is being triggered...
        if ( 'update_status' === $this->current_action() ) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wponce'] );

            if ( ! wp_verify_nonce( $nonce, 'bc_cc_fp_update_user_status' ) ) {
                die( 'Go get a life script kiddies' );
            }
            else {
                self::update_user_status( absint( $_GET['user_id'] ) );

                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                // add_query_arg() return the current url
                //wp_redirect( esc_url_raw(add_query_arg()) );
                //exit;
            }

        }

        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk_update_user_status' )
            || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk_update_user_status' )
        ) {

            $ids = esc_sql( $_POST['user_id'] );
            // loop over the array of record IDs and delete them
            foreach ($ids as $id ) {
                self::update_user_status( $id );

            }

            // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
            // add_query_arg() return the current url
           //wp_redirect( esc_url_raw(add_query_arg()) );
            //exit;
        }
    }

    private function update_user_status($user_id)
    {
        global $wpdb;
        $user = get_user_by('ID', $user_id);
        $result = $wpdb->update(
            $wpdb->users,
            [
                'user_status' => ($user->user_status) ? 0 : 1,
            ],
            [ 'ID' => $user->ID ]
        );
        return $result;
    }

}