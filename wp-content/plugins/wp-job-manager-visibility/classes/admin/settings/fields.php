<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Settings_Fields {

	/**
	 * CheckBox Field
	 *
	 *
	 * @since @@version
	 *
	 * @param $a
	 */
	function checkbox_field( $a ) {

		$o       = $a[ 'option' ];
		$checked = checked( $a[ 'value' ], 1, FALSE );

		echo "<label><input id=\"{$o['name']}\" type=\"checkbox\" class=\"{$a['field_class']}\" name=\"{$o['name']}\" value=\"1\"  {$a['attributes']} {$checked} /> {$o['cb_label']} </label>";
		$this->description( $o );

	}

	/**
	 * Default Header
	 *
	 *
	 * @since @@version
	 *
	 * @param $args
	 */
	function default_header( $args ) {

	}

	/**
	 * About Field
	 *
	 *
	 * @since @@version
	 *
	 * @param $a
	 */
	function about_field( $a ) {

		?>

		<p><strong>Version:</strong> <?php echo JOB_MANAGER_VISIBILITY_VERSION; ?></p>
		<p><strong>Author:</strong> Myles McNamara</p>
		<br>
		<p>Did you know im also the Founder and CEO of Host Tornado?</p>
		<p><a href="https://plugins.smyl.es/contact/" target="_blank">Contact me</a> for an exclusive sMyles Plugins customer promo code discount for any shared SSD (Solid State Drive) hosting packages!  Data centers in Florida USA, Arizona USA, Montreal Canada, and France. Your site will run faster than it ever has, or your money back!</p>

		<?php
	}

	/**
	 * Support Field
	 *
	 *
	 * @since @@version
	 *
	 * @param $a
	 */
	function support_field( $a ) {

		?>

		<p>
		Currently the best way to report any issues you are having or get support with this plugin is to submit a support ticket via<br/>
		your <a href="https://plugins.smyl.es/" target="_blank">sMyles Plugins</a> account.  This will get you the quickest support possible and will allow me to track any support issues.
		<br/><br/>
		You can submit a new support ticket here:<br/>
		<a href="https://plugins.smyl.es/support/new/" target="_blank">https://plugins.smyl.es/support/new/</a> <small>( opens in new window )</small>
	</p>
		<br/>
		<p>
		You can also view any documentation available for this plugin on my website as well:<br/>
		<a href="https://plugins.smyl.es/docs/" target="_blank">https://plugins.smyl.es/docs/</a>
	</p>

		<?php
	}

	/**
	 * Backup Field
	 *
	 *
	 * @since @@version
	 *
	 * @param $a
	 */
	function backup_field( $a ) {

		$o   = $a[ 'option' ];
		$url = get_admin_url();

		echo "<input type=\"hidden\" name=\"content\" value=\"{$o['post_type_slug']}\" />";
		echo "<input type=\"hidden\" name=\"download\" value=\"true\" />";
		echo "<button formmethod=\"GET\" formaction=\"{$url}export.php\" id=\"{$o['name']}\" name=\"button_submit\" value=\"{$o['action']}\" type=\"submit\" class=\"button {$a['field_class']}\" {$a['attributes']}>{$o['caption']}</button>";
		$this->description( $o );

	}

	/**
	 * Button Field
	 *
	 *
	 * @since @@version
	 *
	 * @param $a
	 */
	function button_field( $a ) {

		$o = $a[ 'option' ];

		echo "<button id=\"{$o['name']}\" name=\"button_submit\" value=\"{$o['action']}\" type=\"submit\" class=\"button {$a['field_class']}\" {$a['attributes']}>{$o['caption']}</button>";
		$this->description( $o );

	}

	/**
	 * Cache Button Field
	 *
	 *
	 * @since @@version
	 *
	 * @param $a
	 */
	function cache_button_field( $a ) {

		$o = $a[ 'option' ];

		$btn_caption = "{$o[ 'caption' ]}";

		if( isset( $o[ 'cache_count' ] ) ){
			$count_method = $o[ 'cache_count' ];
			$cache        = new WP_Job_Manager_Visibility_User_Transients();
			$cache_count  = $cache->$count_method();
			$btn_caption .= " ({$cache_count})";
		}

		echo "<button id=\"{$o['name']}\" name=\"button_submit\" value=\"{$o['action']}\" type=\"submit\" class=\"button {$a['field_class']}\" {$a['attributes']}>{$btn_caption}</button>";
		$this->description( $o );

	}

	/**
	 * Link Field
	 *
	 *
	 * @since @@version
	 *
	 * @param $a
	 */
	function link_field( $a ) {

		$o = $a[ 'option' ];

		echo "<a id=\"{$o['name']}\" href=\"{$o['href']}\" class=\"{$a['field_class']}\" {$a['attributes']}>{$o['caption']}</a>";
		$this->description( $o );

	}

	/**
	 * Select Field
	 *
	 *
	 * @since @@version
	 *
	 * @param $a
	 */
	function select_field( $a ) {

		$o = $a[ 'option' ];

		echo "<select id=\"{$o['name']}\" class=\"{$a['field_class']}\" name=\"{$o['name']}\" {$a['attributes']}>";

		foreach ( $o[ 'options' ] as $key => $name ) {
			$value    = esc_attr( $key );
			$label    = esc_attr( $name );
			$selected = selected( $a[ 'value' ], $key, FALSE );

			echo "<option value=\"{$value}\" {$selected}> {$label} </option>";
		}

		echo "</select>";
		$this->description( $o );

	}

	/**
	 * Textarea Field
	 *
	 *
	 * @since @@version
	 *
	 * @param $a
	 */
	function textarea_field( $a ) {

		$o = $a[ 'option' ];

		echo "<textarea cols=\"50\" rows=\"3\" id=\"{$o['name']}\" class=\"{$a['field_class']}\" name=\"{$o['name']}\" {$a['attributes']}>";
		echo esc_textarea( $o[ 'value' ] );
		echo "</textarea>";
		$this->description( $o );

	}

	/**
	 * Textbox Field
	 *
	 *
	 * @since @@version
	 *
	 * @param $a
	 */
	function textbox_field( $a ) {

		$o = $a[ 'option' ];

		echo "<input id=\"{$o['name']}\" type=\"text\" class=\"{$a['field_class']}\" name=\"{$o['name']}\" value=\"{$a['value']}\" {$a['placeholder']} {$a['attributes']} />";
		$this->description( $o );

	}

	/**
	 * Description Field
	 *
	 *
	 * @since @@version
	 *
	 * @param $o
	 */
	function description( $o ) {

		if ( ! empty( $o[ 'desc' ] ) ) echo "<p class=\"description\">{$o['desc']}</p>";

	}

}