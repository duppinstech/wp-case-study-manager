<?php
$root = $argv[1];
$file = $root . '/includes/class-case-study-manager-plugin.php';
$lines = file( $file );
$found = array();
foreach ( $lines as $number => $line ) {
	if ( ! preg_match_all( "/(?:__|esc_html_e|esc_attr_e|translate)\\(\\s*'((?:\\\\'|[^'])*)'/", $line, $matches ) ) {
		continue;
	}
	foreach ( $matches[1] as $text ) {
		$text = str_replace( "\\'", "'", $text );
		if ( ! isset( $found[ $text ] ) ) {
			$found[ $text ] = array();
		}
		$found[ $text ][] = $number + 1;
	}
}
ksort( $found );
$out = array();
$out[] = '# Copyright (C) 2026 Odell Duppins / Duppins Technology';
$out[] = 'msgid ""';
$out[] = 'msgstr ""';
$out[] = '"Project-Id-Version: Case Study Manager 1.2.0\n"';
$out[] = '"Report-Msgid-Bugs-To: odell@duppinstech.com\n"';
$out[] = '"POT-Creation-Date: 2026-10-14 22:45-0400\n"';
$out[] = '"MIME-Version: 1.0\n"';
$out[] = '"Content-Type: text/plain; charset=UTF-8\n"';
$out[] = '"Content-Transfer-Encoding: 8bit\n"';
$out[] = '"X-Domain: case-study-manager\n"';
$out[] = '';
foreach ( $found as $text => $line_numbers ) {
	$refs = array();
	foreach ( $line_numbers as $line_number ) {
		$refs[] = 'includes/class-case-study-manager-plugin.php:' . $line_number;
	}
	$out[] = '#: ' . implode( ' ', $refs );
	$out[] = 'msgid ' . json_encode( $text, JSON_UNESCAPED_UNICODE );
	$out[] = 'msgstr ""';
	$out[] = '';
}
file_put_contents( $root . '/languages/case-study-manager.pot', implode( "\n", $out ) );
echo 'strings ' . count( $found ) . PHP_EOL;
