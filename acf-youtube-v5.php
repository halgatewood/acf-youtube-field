<?php

class acf_field_youtube extends acf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() 
	{
		$this->name = 'youtube';
		$this->label = __('YouTube Video', 'acf-youtube');
		$this->category = 'basic';
		$this->reg_ex 							= ACF_YOUTUBE_REG_EX;
		$this->transient_time 					= apply_filters( 'acf_youtube_transient_time', 28800 );
		$this->youtube_video_id_length 			= ACF_YOUTUBE_VIDEO_LENGTH;
		
		
		$this->defaults = array( 
									'return_format' 	=> 'embed', 
									'thumbnail_size' 	=> 'hqdefault' 
								);
		
		$this->l10n = array();
		
				
		// do not delete!
    	parent::__construct();
	}
	
	
	/*
	*  render_field_options()
	*
	*  Create extra options for your field. These are visible when editing a field.
	*  All parameters of `acf_render_field_option` can be changed except 'prefix'
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	
	function render_field_settings( $field ) 
	{
		
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Value','acf-youtube'),
			'instructions'	=> __('Type of data returned when using the_field()','acf-youtube'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'value'			=> $field['return_format'],
			'prepend'		=> '',
			'prefix'		=> $field['prefix'],
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
		
		acf_render_field_setting( $field, array(
			'label'			=> __('Thumbnail Size','acf-youtube'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'thumbnail_size',
			'value'			=> $field['thumbnail_size'],
			'prepend'		=> '',
			'prefix'		=> $field['prefix'],
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
		
			jQuery('tr.acf-field.field_type-radio[data-name="return_format"] input[type=radio]').on('change', function()
			{
				var input_checked = jQuery('tr.acf-field.field_type-radio[data-name="return_format"] input[type=radio]:checked').val();
			
				if( input_checked == "thumbnail" )
				{
					jQuery('tr.acf-field.field_type-radio[data-name="thumbnail_size"]').show();
				}
				else
				{
					jQuery('tr.acf-field.field_type-radio[data-name="thumbnail_size"]').hide();
				}
			});
			
			jQuery('tr.acf-field.field_type-radio[data-name="return_format"] input[type=radio]').trigger('change');
		
		</script>
	<?php 

	}
	
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	
	function render_field( $field ) 
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

		
	
	/*
	*  format_value()
	*
	*  This filter is applied to the $value after it is loaded from the db and before it is passed to the render_field() function
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @param	$template (boolean) true if value requires formatting for front end template function
	*  @return	$value
	*/
	
	function format_value( $value, $post_id, $field ) 
	{
		
		$youtube_id = acf_youtube_parse_youtube_id( $value );
		
		
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
			
			return $value;
		}

		
		return $youtube_id;
	}
	
}


// create field
new acf_field_youtube();

?>