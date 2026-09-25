<?php
/**
 * Openingstijden: bepaalt per datum de geldende tijden (afwijkende dag > seizoen > buiten seizoen)
 * en levert teksten voor "Vandaag geopend", het volledige overzicht en Schema.org.
 *
 * @package Valkenisse
 */

defined( 'ABSPATH' ) || exit;

final class Valkenisse_Hours {

	public static function now(): DateTimeImmutable {
		return new DateTimeImmutable( 'now', wp_timezone() );
	}

	/** Paaszondag (Gregoriaans, anonieme methode) – geen afhankelijkheid van de calendar-extensie. */
	public static function easter( int $year ): DateTimeImmutable {
		$a = $year % 19;
		$b = intdiv( $year, 100 );
		$c = $year % 100;
		$d = intdiv( $b, 4 );
		$e = $b % 4;
		$f = intdiv( $b + 8, 25 );
		$g = intdiv( $b - $f + 1, 3 );
		$h = ( 19 * $a + $b - $d - $g + 15 ) % 30;
		$i = intdiv( $c, 4 );
		$k = $c % 4;
		$l = ( 32 + 2 * $e + 2 * $i - $h - $k ) % 7;
		$m = intdiv( $a + 11 * $h + 22 * $l, 451 );
		$month = intdiv( $h + $l - 7 * $m + 114, 31 );
		$day   = ( ( $h + $l - 7 * $m + 114 ) % 31 ) + 1;
		return new DateTimeImmutable( sprintf( '%04d-%02d-%02d', $year, $month, $day ), wp_timezone() );
	}

	/**
	 * Begin- en einddatum van een seizoen, gezien vanuit de datum $date.
	 * Ondersteunt periodes over de jaargrens (bv. 01-11 t/m 31-03).
	 *
	 * @return array{0:string,1:string} Y-m-d begin en eind.
	 */
	public static function season_range( array $season, DateTimeImmutable $date ): array {
		$year = (int) $date->format( 'Y' );
		$ymd  = $date->format( 'Y-m-d' );
		$make = static function ( int $y ) use ( $season ): array {
			$start = ! empty( $season['pasen'] ) ? self::easter( $y )->format( 'Y-m-d' ) : $y . '-' . $season['van'];
			$end   = $y . '-' . $season['tot'];
			if ( $season['tot'] < $season['van'] && empty( $season['pasen'] ) ) {
				$end = ( $y + 1 ) . '-' . $season['tot'];
			}
			return array( $start, $end );
		};
		$current = $make( $year );
		if ( $ymd < $current[0] ) {
			$previous = $make( $year - 1 );
			if ( $ymd <= $previous[1] ) {
				return $previous;
			}
		}
		return $current;
	}

	public static function season_for( DateTimeImmutable $date ): ?array {
		$ymd = $date->format( 'Y-m-d' );
		foreach ( valkenisse_get( 'seizoenen', array() ) as $season ) {
			[ $start, $end ] = self::season_range( $season, $date );
			if ( $ymd >= $start && $ymd <= $end ) {
				return $season;
			}
		}
		return null;
	}

	/**
	 * Tijden voor één dag.
	 *
	 * @return array{status:string,open:string,sluit:string,keuken_van:string,keuken_tot:string,opmerking:string,seizoen:string}
	 */
	public static function day( DateTimeImmutable $date ): array {
		$result = array(
			'status'     => 'onbekend',
			'open'       => '',
			'sluit'      => '',
			'keuken_van' => '',
			'keuken_tot' => '',
			'opmerking'  => '',
			'seizoen'    => '',
		);
		$ymd    = $date->format( 'Y-m-d' );
		$season = self::season_for( $date );
		if ( $season ) {
			$d                    = $season['dagen'][ (int) $date->format( 'N' ) ] ?? array( 'dicht' => true );
			$result['seizoen']    = $season['label'];
			$result['status']     = ! empty( $d['dicht'] ) ? 'gesloten' : 'open';
			$result['open']       = $d['open'] ?? '';
			$result['sluit']      = $d['sluit'] ?? '';
			$result['keuken_van'] = $season['keuken_van'];
			$result['keuken_tot'] = $season['keuken_tot'];
		}
		foreach ( valkenisse_get( 'uitzonderingen', array() ) as $e ) {
			if ( $e['datum'] === $ymd ) {
				$closed              = ! empty( $e['dicht'] ) || ( '' === $e['open'] && '' === $e['sluit'] );
				$result['status']    = $closed ? 'gesloten' : 'open';
				$result['open']      = $closed ? '' : $e['open'];
				$result['sluit']     = $closed ? '' : $e['sluit'];
				$result['opmerking'] = $e['opmerking'];
				if ( $closed ) {
					$result['keuken_van'] = '';
					$result['keuken_tot'] = '';
				}
			}
		}
		return $result;
	}

	public static function format_range( string $open, string $close ): string {
		if ( $open && $close ) {
			return $open . ' – ' . $close;
		}
		if ( $open ) {
			return 'vanaf ' . $open;
		}
		if ( $close ) {
			return 'tot ' . $close;
		}
		return '';
	}

	public static function today_sentence( array $day ): string {
		if ( 'open' === $day['status'] ) {
			return trim( 'Vandaag geopend ' . self::format_range( $day['open'], $day['sluit'] ) );
		}
		if ( 'gesloten' === $day['status'] ) {
			return 'Vandaag gesloten' . ( $day['opmerking'] ? ' – ' . $day['opmerking'] : '' );
		}
		return (string) valkenisse_get( 'buiten_seizoen' );
	}

	/** Eerstvolgende open dag na $date (max. 60 dagen vooruit). */
	public static function next_open( DateTimeImmutable $date ): ?array {
		for ( $i = 1; $i <= 60; $i++ ) {
			$d   = $date->modify( "+{$i} day" );
			$day = self::day( $d );
			if ( 'open' === $day['status'] ) {
				return array( 'date' => $d, 'day' => $day );
			}
		}
		return null;
	}

	/**
	 * Tijden voor de komende dagen, zodat de "vandaag"-melding ook klopt
	 * als de pagina uit een cache komt (wordt in de browser opnieuw gekozen).
	 */
	public static function upcoming( int $days = 14 ): array {
		$out   = array();
		$start = self::now()->setTime( 0, 0 );
		for ( $i = 0; $i < $days; $i++ ) {
			$d                         = $start->modify( "+{$i} day" );
			$day                       = self::day( $d );
			$out[ $d->format( 'Y-m-d' ) ] = array(
				't' => self::today_sentence( $day ),
				'k' => self::kitchen_sentence( $day ),
				's' => $day['status'],
			);
		}
		return $out;
	}

	public static function kitchen_sentence( array $day ): string {
		if ( 'open' !== $day['status'] || ( ! $day['keuken_van'] && ! $day['keuken_tot'] ) ) {
			return '';
		}
		return 'Keuken ' . self::format_range( $day['keuken_van'], $day['keuken_tot'] );
	}

	/**
	 * Dagen met dezelfde tijden samenvoegen: "Dinsdag t/m zondag 11:00 – 21:00".
	 *
	 * @return array<int,array{dagen:string,tijden:string,dicht:bool}>
	 */
	public static function grouped_days( array $season ): array {
		$groups = array();
		foreach ( VALKENISSE_DAY_NAMES as $num => $name ) {
			$d   = $season['dagen'][ $num ] ?? array( 'dicht' => true );
			$key = ! empty( $d['dicht'] ) ? 'dicht' : self::format_range( $d['open'] ?? '', $d['sluit'] ?? '' );
			$last = count( $groups ) - 1;
			if ( $last >= 0 && $groups[ $last ]['key'] === $key ) {
				$groups[ $last ]['to'] = $name;
			} else {
				$groups[] = array( 'key' => $key, 'from' => $name, 'to' => $name );
			}
		}
		$out = array();
		foreach ( $groups as $g ) {
			if ( 'Maandag' === $g['from'] && 'Zondag' === $g['to'] ) {
				$label = 'Dagelijks';
			} elseif ( $g['from'] === $g['to'] ) {
				$label = $g['from'];
			} else {
				$label = $g['from'] . ' t/m ' . strtolower( $g['to'] );
			}
			$out[] = array(
				'dagen'  => $label,
				'tijden' => 'dicht' === $g['key'] ? 'Gesloten' : $g['key'],
				'dicht'  => 'dicht' === $g['key'],
			);
		}
		return $out;
	}

	/** Leesbare periode, bv. "1 september – 30 september" of "Vanaf Pasen t/m 30 juni". */
	public static function season_dates_label( array $season ): string {
		$months = array( '', 'januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december' );
		$fmt    = static function ( string $md ) use ( $months ): string {
			[ $m, $d ] = array_map( 'intval', explode( '-', $md ) );
			return $d . ' ' . ( $months[ $m ] ?? '' );
		};
		$start = ! empty( $season['pasen'] ) ? 'Pasen' : $fmt( $season['van'] );
		return ( ! empty( $season['pasen'] ) ? 'Vanaf ' : '' ) . $start . ' t/m ' . $fmt( $season['tot'] );
	}
}
