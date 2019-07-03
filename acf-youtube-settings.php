<?php


// SETTINGS FIELD
function acf_youtube_settings_field()
{
	add_submenu_page('options-general.php', __('YouTube Field','acf-youtube'), __('YouTube Field','acf-youtube'), 'manage_options', 'acf-youtube-settings', 'acf_youtube_settings_field_html');
}

add_action('admin_menu', 'acf_youtube_settings_field', 13, 0);


// HTML FOR SETTINGS
function acf_youtube_settings_field_html()
{
?>
<div class="wrap">
    <h2><?php _e('YouTube Field', 'acf-youtube'); ?></h2>
    <form action="options.php" method="POST">
        <?php settings_fields( 'acf-youtube-basic-settings-group' ); ?>
        <?php do_settings_sections( 'acf-youtube-field' ); ?>
        <?php submit_button(); ?>
    </form>
</div>
<?php
}

// BUILD SETTINGS
add_action( 'admin_init', 'acf_youtube_setting_init' );
function acf_youtube_setting_init()
{
    register_setting( 'acf-youtube-basic-settings-group', 'acf-youtube-updater-email-account' );
    register_setting( 'acf-youtube-basic-settings-group', 'acf-youtube-api-key' );
    register_setting( 'acf-youtube-basic-settings-group', 'acf-youtube-api-parts', 'acf_youtube_api_parts_save' );
    
    add_settings_section( 'acf-youtube-basic-settings', '', 'acf_youtube_nothing', 'acf-youtube-field' );
	add_settings_field( 'acf-youtube-api-key', __('YouTube API Key', 'acf-youtube'), 'acf_youtube_api_key', 'acf-youtube-field', 'acf-youtube-basic-settings' );
	add_settings_field( 'acf-youtube-api-parts', __('YouTube Extra API Parts', 'acf-youtube'), 'acf_youtube_api_parts', 'acf-youtube-field', 'acf-youtube-basic-settings' );
}


function acf_youtube_nothing() { }

function acf_youtube_api_key()
{
	echo "<input type='text' name='acf-youtube-api-key' value='" . esc_attr( get_option( 'acf-youtube-api-key' ) ) . "' style='width:70%;' />";
	echo "<p>";
	echo __("As of April 20th, 2015 this is required to get data from the new YouTube Data API V3.", 'acf-youtube');
	echo "</p>";
}

function acf_youtube_all_parts()
{
	return array('fileDetails','liveStreamingDetails','player','processingDetails','recordingDetails','status','suggestions','topicDetails');
}

function acf_youtube_required_parts()
{
	return array('contentDetails','snippet','statistics');
}


function acf_youtube_api_parts()
{
	$parts = get_option( 'acf-youtube-api-parts' );
	if(!$parts) $parts = array();
	
	$all_parts 			= acf_youtube_all_parts();
	
	echo "<p>";
	foreach( $all_parts as $part )
	{
		$chked = in_array($part, $parts) ? " checked=\"checked\"" : "";
		echo "<span style='white-space:nowrap'><input type='checkbox' name='acf-youtube-api-parts[]'{$chked} value='{$part}'  /> {$part} &nbsp; &nbsp;</span> ";
	}
	echo "</p>";
		
	echo "<hr><p>";
	echo __("What 'part' should be requested through the YouTube API. Some parts require more credentials than others. <a href=\"https://developers.google.com/youtube/v3/docs/videos/list\" target=\"_blank\">More Info about this section is available in the YouTube documentation.</a> The required parts for this plugin (contentDetails,statistics,snippet) are always requested. ", 'acf-youtube');
	echo "</p>";
	echo "<hr>";
	echo "<p>";
	echo __("Note: If you make changes to the Extra Parts API setting you will need to clear the transient cache saved for each video. You can clear the transient cache on any video by adding <code>?refresh</code> to the URL or use a plugin like <a href=\"https://wordpress.org/plugins/transients-manager/\" target=\"_blank\">Transient Manager</a>. ", "acf-youtube");
	echo "</p>";
}

function acf_youtube_api_parts_save( $options )
{
	// NOTHING RETURN ONLY THE DEFAULT (REQUIRED) PARTS
	if( !is_array( $options ) || empty( $options ) || ( false === $options ) )
	{
		$options = array();
	}
	
	// MERGE WITH REQUIRED PARTS
	return array_merge(acf_youtube_required_parts(), $options);
}
