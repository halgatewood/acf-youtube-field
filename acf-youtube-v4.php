<?php

class acf_field_youtube extends acf_field
{
	var $settings, $defaults;
		
	function __construct()
	{
		$this->name 							= 'youtube';
		$this->label 							= __('YouTube Video', 'acf-youtube');
		$this->category 						= __("Basic",'acf');
		$this->reg_ex 							= ACF_YOUTUBE_REG_EX;
		$this->transient_time 					= apply_filters( 'acf_youtube_transient_time', 28800 );
		$this->youtube_video_id_length 			= ACF_YOUTUBE_VIDEO_LENGTH;
		
		$this->defaults = array( 
									'return_format' 	=> 'embed', 
									'thumbnail_size' 	=> 'hqdefault' 
								);
		
		
    	parent::__construct(); // do not delete!
    	
		$this->settings = array(
			'path' => apply_filters('acf/helpers/get_path', __FILE__),
			'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
			'version' => '1.0.0'
		);

	}
	
	function create_options( $field ) 
	{ 
		$key = $field['name'];
	?>
		<tr id="acf-youtube-return-value-<?php echo $key; ?>" class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Return Value",'acf-youtube'); ?></label>
				<p><?php _e("Type of data returned when using the_field()",'acf-youtube') ?></p>
			</td>
			<td>
				<?php 
				do_action('acf/create_field', array(
					'type' => 'radio',
					'name'	=>	'fields['.$key.'][return_format]',
					'value'	=>	$field['return_format'],
					'layout' =>	'horizontal',
					'choices' => array(
						'embed'				=>	__("Embed",'acf-youtube'),
						'object'			=>	__("Video Object",'acf-youtube'),
						'id'				=>	__("YouTube ID",'acf-youtube'),
						'url'				=>	__("URL",'acf-youtube'),
						'thumbnail'			=>	__("Thumbnail",'acf-youtube'),
						'thumbnail-object'	=>	__("Thumbnail Object",'acf-youtube')
					)
				));
				?>
			</td>
		</tr>
		
		<tr id="acf-youtube-thumbnail-size-<?php echo $key; ?>" <?php if($field['return_format'] != "thumbnail") echo "style='display:none;' "; ?> class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Thumbnail Size",'acf-youtube'); ?></label>
			</td>
			<td>
				<?php 
				do_action('acf/create_field', array(
					'type' => 'radio',
					'name'	=>	'fields['.$key.'][thumbnail_size]',
					'value'	=>	$field['thumbnail_size'],
					'layout' =>	'horizontal',
					'choices' => array(
						'default'			=>	__("default",'acf-youtube'),
						'hqdefault'			=>	__("hqdefault",'acf-youtube'),
						'mqdefault'			=>	__("mqdefault",'acf-youtube'),
						'sddefault'			=>	__("sddefault",'acf-youtube'),
						'maxresdefault'		=>	__("maxresdefault",'acf-youtube'),
						'0'					=>	__("0",'acf-youtube'),
						'1'					=>	__("1",'acf-youtube'),
						'2'					=>	__("2",'acf-youtube'),
						'3'					=>	__("3",'acf-youtube')
					)
				));
				?>
				
				<script>
				
					jQuery('#acf-youtube-return-value-<?php echo $key; ?> input').on('change', function()
					{
						var input_checked = jQuery('#acf-youtube-return-value-<?php echo $key; ?> input:checked').val();
					
						if( input_checked == "thumbnail" )
						{
							jQuery('#acf-youtube-thumbnail-size-<?php echo $key; ?>').show();
						}
						else
						{
							jQuery('#acf-youtube-thumbnail-size-<?php echo $key; ?>').hide();
						}
					});
				
				</script>
				
			</td>
		</tr>
	<?php 
	}
	
	function create_field( $field )
	{
		// VARS
		$e = '';
		$o = array( 'id', 'class', 'name', 'value' );
	
		$e .= '<div class="acf-input-wrap">';
		$e .= '<input type="text"';
		
		foreach( $o as $k )
		{
			$e .= ' ' . $k . '="' . esc_attr( $field[ $k ] ) . '"';	
		}
		
		$e .= ' />';
		$e .= '</div>';
		
		$e .= '<div id="acf-youtube-preview-' . $field['id'] . '" style="padding-top: 10px;">';

		if( $field['value'] )
		{
			$current_youtube_id = acf_youtube_parse_youtube_id( $field['value'] );
			if($current_youtube_id)
			{
				$e .= "<iframe width=\"300\" style=\"max-width: 100%;\" height=\"169\" src=\"//www.youtube.com/embed/{$current_youtube_id}\" frameborder=\"0\" allowfullscreen></iframe>";
			}
		}
		
		$e .= '</div>';
		
		$e .= "
		
			<script>	
			
				// FUNCTION FROM THIS CRAZY EXCHANGE: http://stackoverflow.com/questions/3452546/javascript-regex-how-to-get-youtube-video-id-from-url/4811367
			
				function youtube_parser(url)
				{
					var regExp = {$this->reg_ex};
					var match = url.match(regExp);
					if (match && match[2].length == {$this->youtube_video_id_length})
					{
					    return match[2];
					}
					else
					{
					    return false;
					}
				}
			

				jQuery('input#" . $field['id'] . "').on('input', function() 
				{

					var acf_youtube_field_input = jQuery(this).val();
					var acf_check_for_video = youtube_parser( acf_youtube_field_input );
				
					
					// CHECK IF SOMEONE JUST USED THE YOUTUBE ID 
					if (!acf_check_for_video && acf_youtube_field_input.length == {$this->youtube_video_id_length})
					{
						acf_check_for_video = acf_youtube_field_input;
					}
				
					if( acf_check_for_video )
					{
						 jQuery('#acf-youtube-preview-" . $field['id'] . "').html('<iframe width=\"300\" style=\"max-width: 100%;\" height=\"169\" src=\"//www.youtube.com/embed/' + acf_check_for_video + '\" frameborder=\"0\" allowfullscreen></iframe>');
					}
					else
					{
						jQuery('#acf-youtube-preview-" . $field['id'] . "').html('');
					}			
				
				});
		
			</script>
		
		";
		
		echo $e;
	}
	
	function format_value_for_api( $value, $post_id, $field )
	{
		$youtube_id = acf_youtube_parse_youtube_id( $value );
	
	
		// IF NO VIDEO ID RETURN NOTHING
		if( !$youtube_id ) { return false; }
		
		
		// RETURN URL
		if( $field['return_format'] == "url" )
		{
			return 'http://www.youtube.com/watch?v=' . $youtube_id; 
		}
		
		
		// RETURN EMBED
		if( $field['return_format'] == "embed" )
		{
			return '<iframe width="560" height="315" src="//www.youtube.com/embed/' . $youtube_id . '" frameborder="0" allowfullscreen></iframe>'; 
		}


		// RETURN THUMBNAIL
		if( $field['return_format'] == "thumbnail" )
		{
			return "http://i1.ytimg.com/vi/" . $youtube_id . "/" . $field['thumbnail_size'] . ".jpg";
		}
		
		
		// RETURN THUMBNAIL
		if( $field['return_format'] == "thumbnail-object" )
		{
			return acf_youtube_thumbnail_object( $youtube_id );  
		}


		// RETURN JUST YOUTUBE ID
		if( $field['return_format'] == "id" )
		{
			return $youtube_id; 
		}
		
		
		// RETURN A YOUTUBE OBJECT
		if( $field['return_format'] == "object" )
		{
			
			// CHECK FOR TRANSIENT
			$transient_name = "acf_youtube_" . $youtube_id;

			if( get_transient( $transient_name ) AND !isset($_GET['refresh'] ))
 			{
				// SET TRANSIENT
    			$value = get_transient( $transient_name );
  			}
  			else
  			{
			    // GET YOUTUBE DATA
			    $value = acf_youtube_build_video_object( $youtube_id );

    			// SET TRANSIENT
				set_transient( $transient_name, $value, $this->transient_time );
			}	
		}
		
		return $value;
	}
	
}


// CREATE FIELD
new acf_field_youtube();
?>