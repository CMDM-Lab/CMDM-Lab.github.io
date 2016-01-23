<?php
/**
 * Custom function excerpt
 *
 * Usage --- oprum_excerpt( 'text=$text&maxchar=50' );
 *
 * @package Oprum
 */

function oprum_excerpt($args=''){
	global $post;
		parse_str($args, $i);
		$maxchar 	 = isset($i['maxchar']) ?  (int)trim($i['maxchar'])	: 350;
		$text = isset($i['text']) ? 	trim($i['text'])	: '';
		$save_format = isset($i['save_format']) ?	trim($i['save_format']): false;
		$echo = isset($i['echo']) ? 	false	: true;

	if (!$text){
		$out = $post->post_excerpt ? $post->post_excerpt : $post->post_content;
		$out = preg_replace ("!\[/?.*\]!U", '', $out );
		if( !$post->post_excerpt && strpos($post->post_content, '<!--more-->') ){
			preg_match ('/(.*)<!--more-->/s', $out, $match);
			$out = str_replace("\r", '', trim($match[1], "\n"));
			$out = preg_replace( "!\n\n+!s", "</p><p>", $out );
			$out = '<p>'. str_replace( "\n", '<br />', $out ) .' <a href="'. get_permalink($post->ID) .'#more-'. $post->ID.'">...</a></p>';
			if ($echo)
				return print $out;
			return $out;
		}
	}

	$out = $text.$out;
	if (!$post->post_excerpt)
		$out = strip_tags($out, $save_format);

	if ( iconv_strlen($out, 'utf-8') > $maxchar ){
		$out = iconv_substr( $out, 0, $maxchar, 'utf-8' );
		$out = preg_replace('@(.*)\s[^\s]*$@s', '\\1 ...', $out);
	}

	if($save_format){
		$out = str_replace( "\r", '', $out );
		$out = preg_replace( "!\n\n+!", "</p><p>", $out );
		$out = "<p>". str_replace ( "\n", "<br />", trim($out) ) ."</p>";
	}

	if($echo) return print $out;
	return $out;
}