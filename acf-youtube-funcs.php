<?php

define('ACF_YOUTUBE_VIDEO_LENGTH', 11);
define('ACF_YOUTUBE_REG_EX', apply_filters( 'acf_youtube_reg_ex', "/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/" ));


// EXTRACT THE YOUTUBE ID FROM THE STRING
function acf_youtube_parse_youtube_id( $data )
{
	// IF 11 CHARS
	if ( strlen($data) == ACF_YOUTUBE_VIDEO_LENGTH)
	{
		return $data;
	}
	
	preg_match( ACF_YOUTUBE_REG_EX, $data, $matches);
	return isset($matches[2]) ? $matches[2] : false;
}


// MAKE A VIDEO OBJECT, IF WE CAN ACCESS THE YOUTUBE API WE'LL PULL IN SOME EXTRA DETAILS
function acf_youtube_build_video_object( $youtube_id )
{
	// GET YOUTUBE VIDEO FROM YOUTUBE API

	$video = new stdClass();
	$video->data_source = "acf";
	
	$content_parts = implode(",", get_option('acf-youtube-api-parts'));
	
	// IF WE HAVE DATA FROM YOUTUBE API USE ADD IT TO THE OBJECT
	// V2: $request = wp_remote_get( "http://gdata.youtube.com/feeds/api/videos/" . $youtube_id . "?v=2&alt=json" );
	$request = wp_remote_get("https://www.googleapis.com/youtube/v3/videos?part=" . $content_parts . "&id=" . $youtube_id . "&key=" . get_option( 'acf-youtube-api-key' ));

	if( !is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200 )
	{
		$request = json_decode( wp_remote_retrieve_body( $request ) );

		if( isset( $request->error ) AND current_user_can( 'manage_options' ) )
		{
			$video->title = $request->error->errors[0]->reason;
			$video->desc = $request->error->errors[0]->message;
		}
		else if( $request->pageInfo->totalResults )
		{
			$v = reset($request->items);
			
			if( $v->snippet )
			{
				$video_info 	= $v->snippet;
				$video_stats 	= $v->statistics;
			
				$video->data_source 	= "youtube-api";
				$video->title 			= $video_info->title;
				$video->desc 			= $video_info->description;
				$video->category_id 	= $video_info->categoryId;
				$video->localized 		= $video_info->localized;
				$video->status 			= $v->status;
				$video->details 		= $v->contentDetails;
			
				
				$video->duration 		= acf_youtube_parse_yt_textdate( $video->details->duration );
				$video->length 			= acf_youtube_seconds_to_time( $video->duration );
				$video->author_name		= $video_info->channelTitle;
				$video->author_id 		= $video_info->channelId;
				$video->views 			= $video_stats->viewCount;
				$video->favorites 		= $video_stats->favoriteCount;
				$video->likes 			= $video_stats->likeCount;
				$video->dislikes 		= $video_stats->dislikeCount;
				$video->comment_count 	= $video_stats->commentCount;
				$video->published 		= $video_info->publishedAt;
			}
			


		}
	}
		
	
	// DEFAULTS 
	$video->youtube_id 		= $youtube_id;	
	$video->embed 			= '<iframe width="560" height="315" src="//www.youtube.com/embed/' . $youtube_id . '" frameborder="0" allowfullscreen></iframe>';
	$video->image 			= "http://i1.ytimg.com/vi/" . $youtube_id . "/hqdefault.jpg";
	$video->image_med 		= "http://i1.ytimg.com/vi/" . $youtube_id . "/mqdefault.jpg";
	$video->image_sd 		= "http://i1.ytimg.com/vi/" . $youtube_id . "/sddefault.jpg";
	$video->image_max 		= "http://i1.ytimg.com/vi/" . $youtube_id . "/maxresdefault.jpg";

	return $video;
}


function acf_youtube_parse_yt_textdate( $d ) 
{
	// FROM: http://stackoverflow.com/questions/19562195/converting-youtube-data-api-v3-video-duration-format-to-standard-time-in-php/26178914#26178914
    preg_match_all('/[0-9]+[HMS]/',$d,$matches);
    $duration=0;
    foreach($matches as $match)
    {
        foreach($match as $portion)
        {        
            $unit = substr( $portion, strlen($portion)-1 );
            switch($unit)
            {
                case 'H':
                    $duration += substr($portion,0,strlen($portion)-1)*60*60;  
                    break;         
                case 'M':               
                    $duration += substr($portion,0,strlen($portion)-1)*60;           
					break;             
                case 'S':                 
                    $duration += substr($portion,0,strlen($portion)-1);          
					break;
            }
        }
    }
     return $duration;
}

// CONVERTS SECONDS TO UNIX TIME
function acf_youtube_seconds_to_time($time)
{
	if( is_numeric($time) )
	{
		$value = array("years" => 0,"days" => 0,"hours" => 0,"minutes" => 0,"seconds" => 0);
		if($time >= 31556926){ $value["years"] = floor($time/31556926); $time = ($time%31556926); }
		if($time >= 86400){ $value["days"] = floor($time/86400); $time = ($time%86400); }
		if($time >= 3600){ $value["hours"] = floor($time/3600); $time = ($time%3600); }
		if($time >= 60){ $value["minutes"] = floor($time/60); $time = ($time%60); }
		$value["seconds"] = floor($time);
		return (array) $value;
	}
	else
	{
		return (bool) FALSE;
	}
}


// CONVERTS SECONDS TO A NICELY FORMATED TIME LIKE 12:34:56
function acf_youtube_seconds_to_duration($seconds)
{
	$length_array = acf_youtube_seconds_to_time($seconds);
	
	$length = "";
	if($length_array['hours'] != "") { $length = $length_array['hours'] . ":"; }
	if($length_array['minutes'] < 1) $length_array['minutes'] = "0";
	$length .= $length_array['minutes'] . ":";
	if($length_array['seconds'] < 10) { $length_array['seconds'] = "0" . $length_array['seconds']; }
	$length .= $length_array['seconds'];
	
	return $length;
}


// BUILD THUMBNAIL OBJECT
function acf_youtube_thumbnail_object( $youtube_id )
{
	$thumbnails = new stdClass();
	$thumbnails->default 			= "http://i1.ytimg.com/vi/" . $youtube_id . "/default.jpg";
	$thumbnails->hqdefault 			= "http://i1.ytimg.com/vi/" . $youtube_id . "/hqdefault.jpg";
	$thumbnails->mqdefault 			= "http://i1.ytimg.com/vi/" . $youtube_id . "/mqdefault.jpg";
	$thumbnails->sddefault 			= "http://i1.ytimg.com/vi/" . $youtube_id . "/sddefault.jpg";
	$thumbnails->maxresdefault 		= "http://i1.ytimg.com/vi/" . $youtube_id . "/maxresdefault.jpg";
	
	$thumbnails->small = array();
	$thumbnails->small[0]			= "http://i1.ytimg.com/vi/" . $youtube_id . "/0.jpg";
	$thumbnails->small[1]			= "http://i1.ytimg.com/vi/" . $youtube_id . "/1.jpg";
	$thumbnails->small[2]			= "http://i1.ytimg.com/vi/" . $youtube_id . "/2.jpg";
	$thumbnails->small[3] 			= "http://i1.ytimg.com/vi/" . $youtube_id . "/3.jpg";
	
	return $thumbnails; 
}