<?php

function get_today_date( $local = 'utc', $format = 'Y-m-d H:i' ) {

	$server_timezone = wp_timezone_string();
	$local_time_zone = date_default_timezone_get();
	$am_pm           = 'Y-m-d g:i a';

	if ( 'utc' === $local ) {
		$today = gmdate( $format );
	} else {
		$date_gmt = new \DateTime( gmdate( $format ) );
		$hours    = get_option( 'gmt_offset' );
		$today    = $date_gmt->add( \DateInterval::createFromDateString( "{$hours} hours" ) );
		$today    = $today->format( $format );
	}

	// error_log( $server_timezone . '===UTC=>' . $today . '===LOCAL AM/PM=>' . $today . '===LOCAL=>' . $today );
	// error_log( $today );
	return $today;
}

function get_date_du_jour( $tz = 'client' ) {
	// https://www.iplocate.io/api/lookup/31.128.157.75 Volgograd
	// https://api.findip.net/' . $ip_address . '/?token=e21d68c353324af0af206c907e77ff97'
	// 125.164.23.150 Indonésie
	// 68.53.78.4 USA
	// 199.231.248.34 USA
	// 177.245.100.90 Mexique
	// 158.62.43.107 Philippines
	// 103.129.201.233 Bangladesh ??
	// 126.54.117.232 Japan
	// 212.77.220.109 Quatar
	// 213.245.168.184 mon IP
	$ip = '158.62.43.107';

	if ( empty( $ip ) ) {
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && filter_var( $_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && filter_var( $_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) && filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
	}

	if ( empty( $ip ) || '127.0.0.1' === $ip || 'localhost' === $ip ) {
		return '';
	}

	$current_timezone = wp_timezone_string();
	$offset           = null;

	if ( 'client' === $tz ) {
		$get_geoplugin = wp_safe_remote_get( "http://www.geoplugin.net/json.gp?ip=$ip" );

		if ( ! is_wp_error( $get_geoplugin ) && ! empty( wp_remote_retrieve_body( $get_geoplugin ) ) ) {
			$ip_geo = json_decode( wp_remote_retrieve_body( $get_geoplugin ), true );

			if ( 200 === $ip_geo['geoplugin_status'] ) {
				$current_timezone = isset( $ip_geo['geoplugin_timezone'] ) ? $ip_geo['geoplugin_timezone'] : '';
			} else {
				var_dump( $ip . '::' . $_SERVER['REMOTE_ADDR'] . '::' . wp_json_encode( $get_geoplugin ) );
			}
		}
	}

	if ( ! empty( $current_timezone ) ) {
		$this_tz = new \DateTimeZone( $current_timezone );
		// Date heure de Greenwich
		$now_utc = new \DateTime( 'now', new \DateTimeZone( 'UTC' ) );
		// nombre d'heures de décallage entre la timezone et Greenwich
		$offset = $this_tz->getOffset( $now_utc ) / 3600;
		// Ajoute à Greenwich le décallage
		$today = $now_utc->add( \DateInterval::createFromDateString( "{$offset} hours" ) );

		var_dump( $current_timezone . '::' . $offset . '::' . $today->format( 'Y-m-d g:i a' ) );

		return $today->format( 'Y-m-d' );
	}
	return '';
}
