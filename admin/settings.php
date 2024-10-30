<?php
/*
* Settings Page for Infinite Scroll GeneratePress
*/

class Infinite_Scroll_GeneratePress_Settings extends Infinite_Scroll_GeneratePress
{
  private $ifsg_options;

  public function __construct()
  {
    $this->ifsg_options = get_option( 'ifsg_settings' );
    add_action( 'admin_menu', array($this, 'ifsg_add_admin_menu' ) );
    add_action( 'admin_init', array($this, 'ifsg_settings_init' ) );
  }

  public function ifsg_get_public_post_types(){
    // get all registered public post types
    $options = (!empty($this->ifsg_options['ifsg_text_field_0']) ? $this->ifsg_options['ifsg_text_field_0'] : '');;
    $pag        = 'ifsg_settings';
    $pag2       = 'ifsg_text_field_0';
    $html       = '';

    $args = array(
       'public'   => true,
       // '_builtin' => false
    );

    $output = 'names'; // names or objects, note names is the default
    $operator = 'and'; // 'and' or 'or'

    $post_types = get_post_types( $args, $output, $operator );

    foreach ( $post_types  as $post_type ) {

       $checked =  (!empty($this->ifsg_options['ifsg_text_field_0']) ? (in_array($post_type, $options) ? 'checked="checked"' : '') : '');
       $html .= sprintf( '<input type="checkbox" id="%1$s[%2$s]" name="%1$s[%4$s][]" value="%2$s" %3$s />', $pag, $post_type, $checked, $pag2 );
       $html .= sprintf( '<label for="%1$s[%3$s]"> %2$s</label><br>', $pag, $post_type, $post_type );
    }
    $html .= sprintf( '<span class="description"> %s</label>', '' );

    echo $html;
  }

  public function ifsg_add_admin_menu(  ) {
    if ( is_admin() )
    { // admin actions
  	   add_menu_page( 'Infinite Scroll GeneratePress', 'Infinite Scroll GeneratePress', 'manage_options', 'infinite_scroll_generatepress', array($this, 'ifsg_options_page' )  );
    } else {
      // non-admin enqueues, actions, and filters
    }
  }

  public function ifsg_settings_init(  ) {
  	register_setting( 'ifsg_Settings_Page', 'ifsg_settings' );

  	add_settings_section(
  		'ifsg_ifsg_Settings_Page_section',
  		__( '<!--Your section description-->', 'wordpress' ),
  		array($this,'ifsg_settings_section_callback'),
  		'ifsg_Settings_Page'
  	);

  	add_settings_field(
  		'ifsg_text_field_0',
  		__( 'Post Types', 'wordpress' ),
  		array($this,'ifsg_text_field_0_render'),
  		'ifsg_Settings_Page',
  		'ifsg_ifsg_Settings_Page_section'
  	);

  	add_settings_field(
  		'ifsg_text_field_1',
  		__( 'Posts Per Page', 'wordpress' ),
  		array($this,'ifsg_text_field_1_render'),
  		'ifsg_Settings_Page',
  		'ifsg_ifsg_Settings_Page_section'
  	);
    add_settings_field(
  		'ifsg_text_field_2',
  		__( 'Custom Content', 'wordpress' ),
  		array($this,'ifsg_text_field_2_render'),
  		'ifsg_Settings_Page',
  		'ifsg_ifsg_Settings_Page_section'
  	);
  }

  public function ifsg_text_field_0_render(  ) {
    // Option to choose from any of the registered public post types
    $this->ifsg_get_public_post_types();
    echo __( '<p>Select which post types you want to use with this plugin.</p>', 'wordpress' );
  }

  public function ifsg_text_field_1_render(  ) {
    // Option to choose the number of posts to load in each AJAX call
    $posts_per_page = (!empty($this->ifsg_options['ifsg_text_field_1']) ? $this->ifsg_options['ifsg_text_field_1'] : '');
  	?>
  	<input type='number' name='ifsg_settings[ifsg_text_field_1]' value='<?php echo $posts_per_page; ?>'>
  	<?php
    echo __( '<p>Choose how many posts should load in each infinite scroll request ( 4-6 recommended ).</p>', 'wordpress' );

  }

  public function ifsg_text_field_2_render(  ) {
    // Option to append custom content to the end of each AJAX call
    $content = (!empty($this->ifsg_options['ifsg_text_field_2']) ? $this->ifsg_options['ifsg_text_field_2'] : '');
    $editor_id = 'ifsg_text_field_2';

    wp_editor( $content, $editor_id, $settings = array('textarea_name' => 'ifsg_settings[ifsg_text_field_2]','textarea_rows'=> '15') );
    echo __( '<p>Append custom content, such as HTML or Javascript, to the end of each AJAX call.</p>', 'wordpress' );

  }

  public function ifsg_settings_section_callback(  ) {
  	echo __( '<p>Use the options below to customize Infinite Scroll GeneratePress.</p>', 'wordpress' );
    echo __( '<b>Note</b>: While most builtin and custom post types and pages should work fine with this plugin, others may not.', 'wordpress' );
  }

  public function ifsg_options_page(  ) {

  	?>
    <div class="wrap">
    	<form action='options.php' method='post'>

    		<h1>Infinite Scroll GeneratePress Settings</h1>

    		<?php
    		settings_fields( 'ifsg_Settings_Page' );
    		do_settings_sections( 'ifsg_Settings_Page' );
    		submit_button();
    		?>

    	</form>
    </div>
  	<?php

  }
}

$Infinite_Scroll_GeneratePress_Settings = new Infinite_Scroll_GeneratePress_Settings();
