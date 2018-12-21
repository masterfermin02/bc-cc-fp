<?php

namespace Bcccfp\Admin;


/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Your Name <email@example.com>
 */
class BC_cc_fp_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $wpmodels;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $wpmodels) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->wpmodels = $wpmodels;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bc-cc-fp-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bc-cc-fp-admin.js', array( 'jquery' ), $this->version, false );

	}

    public function plugin_menu() {

        $hook = add_menu_page(
            'List Users',
            'List Users',
            'manage_options',
            'wp_list_table_class',
            [ $this, 'plugin_settings_page' ]
        );

        add_action( "load-$hook", [ $this, 'screen_option' ] );

        add_submenu_page(
            null,
            __('Edit User', 'bc-cc-fp'),
            __('Edit User', 'bc-cc-fp'),
            'edit_users',
            'bcccfp_users_edit',
            [$this, 'bcccfp_users_form_page']
        );

    }

    /**
     * Screen options
     */
    public function screen_option() {

        $option = 'per_page';
        $args   = [
            'label'   => 'users',
            'default' => 5,
            'option'  => 'paged'
        ];

        add_screen_option( $option, $args );
        $this->customers_obj = new $this->wpmodels['BC_cc_fp_list_users']();
    }

    /**
     * Plugin settings page
     */
    public function plugin_settings_page() {
        ?>


        <div class="wrap">
            <h2>List Users</h2>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post">
                                <p class="search-box">
                                    <select id="role" name="role">
                                        <option value=""><?php _e('All Roles', 'bc-cc-fp'); ?></option>
                                        <?php wp_dropdown_roles(isset($_REQUEST['role']) ? $_REQUEST['role'] : ''); ?>
                                    </select>
                                    <input type="submit" id="search-submit" class="button" value="<?php _e('Filter by Role', 'bc-cc-fp'); ?>">
                                </p>
                                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                                <?php
                               $this->customers_obj->prepare_items();
                               $this->customers_obj->display(); ?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
        <?php
    }

    function bcccfp_users_form_page() {
        global $wpdb;
        $message = '';
        $notice = '';
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
        $user = false;
        if ( isset($_REQUEST['nonce']) && check_admin_referer( 'bcccfp_users_form', 'nonce' )) {
            $user = $_REQUEST;
            if ($this->bcccfp_validate_user($user)) {
                if ($user['user_id'] > 0) {
                    $update = $wpdb->update(
                        $wpdb->users,
                        [
                            'display_name' => $user['display_name'],
                            'user_status' => $user['user_status'],
                        ],
                        [ 'ID' => $user['user_id'] ]
                    );
                    if ( $update ) {
                        $message = __('User was successfully updated', 'bc-cc-fp');
                    } else {
                        $notice = __('There was an error while updating user', 'bc-cc-fp');
                    }
                    $user = get_user_by('ID', $user['user_id']);
                }
            } else {
                $notice = false;
            }
        } else {
            if ($user_id > 0) {
                $user = get_user_by('ID', $user_id);
                if (false === $user) {
                    $notice = __('User not found', 'bc-cc-fp');
                }
            }
        }
        add_meta_box(
            'user_form_meta_box',
            __('Edit User', 'bc-cc-fp'),
            [$this, 'bcccfp_user_form_meta_box'],
            'user',
            'normal',
            'default'
        );

        ?>
        <div class="wrap">
            <h2><?php _e('Edit User', 'bc-cc-fp')?>
                <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=wp_list_table_class');?>">
                    <?php _e('back to users', 'bc-cc-fp')?>
                </a>
            </h2>

            <?php if ( !empty($notice) ): ?>
                <div id="notice" class="error">
                    <p><?php echo $notice; ?></p>
                </div>
            <?php endif;?>

            <?php if ( !empty($message) ): ?>
                <div id="message" class="updated">
                    <p><?php echo $message; ?></p>
                </div>
            <?php endif;?>

            <form id="form" method="POST">
                <?php wp_nonce_field( 'bcccfp_users_form', 'nonce' ); ?>

                <input type="hidden" name="user_id" value="<?php echo $user->ID ?>"/>

                <div class="metabox-holder" id="poststuff">
                    <div id="post-body">
                        <div id="post-body-content">
                            <?php do_meta_boxes('user', 'normal', $user); ?>
                            <input type="submit" value="<?php _e('Save', 'bcccfp')?>" id="submit" class="button-primary" name="submit">
                        </div>
                    </div>
                </div>
            </form>
        </div>
       <?php
    }

    public function bcccfp_user_form_meta_box($user) {
        ?>
        <div class="formdata">
            <p>
                <label for="name">
                    <?php _e('Name:', 'bc-cc-fp')?>
                </label>
                <br>
                <input type="text" id="display_name" name="display_name" required
                       value="<?php echo esc_attr($user->display_name)?>">
            </p>
            <p>
                <label><?php _e('Status:', 'bc-cc-fp')?></label>
                <br>
                <label for="status-active">
                    <input type="radio" name="user_status" id="status-active"
                           value="1" <?php echo ($user->user_status) ? 'checked':''?>>
                    <?php _e('Active', 'bc-cc-fp'); ?>
                </label>
                <label for="status-inactive">
                    <input type="radio" name="user_status" id="status-inactive"
                           value="0" <?php echo (!$user->user_status) ? 'checked':''?>>
                    <?php _e('Inactive', 'bc-cc-fp'); ?>
                </label>
            </p>
        </div>
<?php
    }

    function bcccfp_validate_user($user) {
        $messages = [];
        if ('' === $user['display_name']) $messages[] = __('Display Name is required', 'bc-cc-fp');
        if (empty($messages)) return true;
        return implode('<br />', $messages);
    }


}
