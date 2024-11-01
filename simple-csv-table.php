<?php
/**
 * Simple CSV Table
 *
 * @package           simple-csv-table
 * @author            Marcin Pietrzak
 * @copyright         2017-2022 Marcin Pietrzak (marcin@iworks.pl)
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Simple CSV Table
 * Plugin URI:        http://iworks.pl/en/plugins/fleet/
 * Description:       Convert CSV file to table.
 * Version:           1.0.0
 * Requires at least: 3.0
 * Requires PHP:      7.4
 * Author:            Marcin Pietrzak
 * Author URI:        http://iworks.pl/
 * Text Domain:       simple-revision-control
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Simple_CSV_Table {

	function __construct() {
		add_shortcode( 'csv', array( $this, 'shortcode_center' ) );
		add_action( 'wp_head', array( $this, 'wp_head' ) );
	}

	public function shortcode_center( $atts ) {
		extract(
			shortcode_atts(
				array(
					'header'      => true,
					'href'        => false,
					'showcontent' => true,
					'showlink'    => true,
					'skipcolumn'  => '',
					'title'       => false,
				),
				$atts
			)
		);
		$skipcolumn = preg_split( '/,/', preg_replace( '/[^0-9^,]/', '', $skipcolumn ) );
		$upload_dir = wp_upload_dir();
		$file       = dirname( dirname( $upload_dir['basedir'] ) ) . $href;
		if ( ! is_file( $file ) ) {
			return;
		}
		$row = 0;
		$d   = array();
		if ( ( $handle = fopen( $file, 'r' ) ) !== false ) {
			while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) {
				$num = count( $data );
				for ( $c = 0; $c < $num; $c++ ) {
					$d[ $row ][ $c ] = $data[ $c ];
				}
				$row++;
			}
			fclose( $handle );
		}
		$content = '';
		if ( $title ) {
			$content .= apply_filters( 'simple_csv_table_title', sprintf( '<h2>%s</h2>%s', apply_filters( 'the_title', $title ), "\n" ) );
		}
		if ( $showcontent ) {
			$thead = '';
			if ( $header ) {
				$data   = array_shift( $d );
				$thead .= apply_filters( 'simple_csv_table_thead_tag', '<thead>' . "\n" );
				$thead .= '<tr>' . "\n";
				$num    = count( $data );
				for ( $c = 0; $c < $num; $c++ ) {
					if ( in_array( $c, $skipcolumn ) ) {
						continue;
					}
					$thead .= sprintf( '<th>%s</th>%s', $data[ $c ], "\n" );
				}
				$thead .= '</tr>' . "\n" . '</thead>' . "\n";
			}
			$tbody = '';
			$i     = 0;
			foreach ( $d as $data ) {
				$row      = '';
				$has_data = false;
				$num      = count( $data );
				for ( $c = 0; $c < $num; $c++ ) {
					if ( in_array( $c, $skipcolumn ) ) {
						continue;
					}
					$row .= sprintf( '<td%s>%s</td>%s', preg_match( '/^\d+$/', $data[ $c ] ) ? ' class="alignright"' : '', $data[ $c ], "\n" );
					if ( ! empty( $data[ $c ] ) ) {
						$has_data = true;
					}
				}
				if ( $has_data ) {
					$tbody .= apply_filters( 'simple_csv_table_tbody_tr', sprintf( '<tr%s>%s', $i++ % 2 ? ' class="alternate"' : '', "\n" ), $i, $data );
					$tbody .= $row;
					$tbody .= '</tr>' . "\n";
				}
			}
			if ( $tbody ) {
				$content .= '<table>' . "\n";
				if ( $title ) {
					$content .= apply_filters( 'simple_csv_table_caption', sprintf( '<caption>%s</caption>%s', $title, "\n" ) );
				}
				if ( $thead ) {
					$content .= apply_filters( 'simple_csv_table_thead', $thead );
				}
				$content .= apply_filters( 'simple_csv_table_tbody', sprintf( '<tbody>%s%s</tbody>%s', "\n", $tbody, "\n" ) );
				$content .= '</table>' . "\n";
			}
		}
		if ( $showlink ) {
			$link     = sprintf(
				'<div class="file"><a href="%s" title="%s">%s</a></div>',
				$href,
				wptexturize( $title ),
				basename( $href )
			);
			$content .= apply_filters( 'simple_csv_table_link', $link );
		}
		return apply_filters( 'simple_csv_table_all', '<div class="simple_csv_table">' . $content . '</div>' );
	}
	public function wp_head() {
		?>
<style type="text/css">
table td.alignright {
clear: none;
float: none;
text-align: right;
}
</style>
		<?php
	}
}
new Simple_CSV_Table;

