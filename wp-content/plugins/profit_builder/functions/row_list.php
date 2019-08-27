<?php
$pbuilder_rows = array(
	0 => array(
	 	'value' => 'row-full',
		'label' => 'Full width',
		'image' => $this->url .'images/row_icons/row-full.png',
		'html' => '<div data-rowtype="0" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-1-1">%3$s</div><div style="clear:both;"></div></div></div>'
	),
	1 => array(
	 	'value' => 'row-2-1-2',
		'label' => 'Two halves',
		'image' => $this->url .'images/row_icons/row-2-1-2.png',
		'html' => '<div data-rowtype="1" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-1-2">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-1-2">%4$s</div><div style="clear:both;"></div></div></div>'
	),
	2 => array(
		'value' => 'row-3-1-3',
		'label' => 'Three thirds',
		'image' => $this->url .'images/row_icons/row-3-1-3.png',
		'html' => '<div data-rowtype="2" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-1-3">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-1-3">%4$s</div><div data-colnumber="2" class="pbuilder_column pbuilder_column-1-3">%5$s</div><div style="clear:both;"></div></div></div>'
	),
	3 => array(
		'value' =>'row-1-3-2-3',
		'label' => 'One third + Two thirds',
		'image' => $this->url .'images/row_icons/row-1-3-2-3.png',
		'html' => '<div data-rowtype="3" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-1-3">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-2-3">%4$s</div><div style="clear:both;"></div></div></div>'
	),
	4 => array(
		'value' => 'row-2-3-1-3',
		'label' => 'Two thirds + One third',
		'image' => $this->url .'images/row_icons/row-2-3-1-3.png',
		'html' => '<div data-rowtype="4" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-2-3">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-1-3">%4$s</div><div style="clear:both;"></div></div></div>'
	),
	5 => array(
		'value' => 'row-4-1-4',
		'label' => 'Four quarters',
		'image' => $this->url .'images/row_icons/row-4-1-4.png',
		'html' => '<div data-rowtype="5" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-1-4">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-1-4">%4$s</div><div data-colnumber="2" class="pbuilder_column pbuilder_column-1-4">%5$s</div><div data-colnumber="3" class="pbuilder_column pbuilder_column-1-4">%6$s</div><div style="clear:both;"></div></div></div>'
	),
	6 => array(
		'value' => 'row-1-4-3-4',
		'label' => 'One quarter + Three quarters',
		'image' => $this->url .'images/row_icons/row-1-4-3-4.png',
		'html' => '<div data-rowtype="6" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-1-4">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-3-4">%4$s</div><div style="clear:both;"></div></div></div>'
	),
	7 => array(
		'value' => 'row-3-4-1-4',
		'label' => 'Three quarters + One quarter',
		'image' => $this->url .'images/row_icons/row-3-4-1-4.png',
		'html' => '<div data-rowtype="7" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-3-4">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-1-4">%4$s</div><div style="clear:both;"></div></div></div>'
	),
	8 => array(
		'value' => 'row-2-1-4-1-2',
		'label' => 'Two quarters + One half',
		'image' => $this->url .'images/row_icons/row-2-1-4-1-2.png',
		'html' => '<div data-rowtype="8" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-1-4">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-1-4">%4$s</div><div data-colnumber="2" class="pbuilder_column pbuilder_column-1-2">%5$s</div><div style="clear:both;"></div></div></div>'
	),
	9 => array(
		'value' => 'row-1-2-2-1-4',
		'label' => 'One half + Two quarters',
		'image' => $this->url .'images/row_icons/row-1-2-2-1-4.png',
		'html' => '<div data-rowtype="9" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-1-2">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-1-4">%4$s</div><div data-colnumber="2" class="pbuilder_column pbuilder_column-1-4">%5$s</div><div style="clear:both;"></div></div></div>'
	),
	10 => array(
		'value' => 'row-5-1-5',
		'label' => 'Five fifths',
		'image' => $this->url .'images/row_icons/row-5-1-5.png',
		'html' => '<div data-rowtype="10" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-1-5">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-1-5">%4$s</div><div data-colnumber="2" class="pbuilder_column pbuilder_column-1-5">%5$s</div><div data-colnumber="3" class="pbuilder_column pbuilder_column-1-5">%6$s</div><div data-colnumber="4" class="pbuilder_column pbuilder_column-1-5">%7$s</div><div style="clear:both;"></div></div></div>'
	),
	11 => array(
		'value' => 'row-1-4-1-2-1-4',
		'label' => 'One quarter + One half + One quarter',
		'image' => $this->url .'images/row_icons/row-1-4-1-2-1-4.png',
		'html' => '<div data-rowtype="11" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-1-4">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-1-2">%4$s</div><div data-colnumber="2" class="pbuilder_column pbuilder_column-1-4">%5$s</div><div style="clear:both;"></div></div></div>'
	),
	12 => array(
		'value' => 'row-1-5-3-5-1-5',
		'label' => 'One fifth + Three fifths + One fifth',
		'image' => $this->url .'images/row_icons/row-1-5-3-5-1-5.png',
		'html' => '<div data-rowtype="12" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-1-5">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-3-5">%4$s</div><div data-colnumber="2" class="pbuilder_column pbuilder_column-1-5">%5$s</div><div style="clear:both;"></div></div></div>'
	),
	13 => array(
		'value' => 'row-1-5-4-5',
		'label' => 'One fifth + Four fifths',
		'image' => $this->url .'images/row_icons/row-1-5-4-5.png',
		'html' => '<div data-rowtype="13" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-1-5">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-4-5">%4$s</div><div style="clear:both;"></div></div></div>'
	),
	14 => array(
		'value' => 'row-4-5-1-5',
		'label' => 'One fifth + Four fifths',
		'image' => $this->url .'images/row_icons/row-4-5-1-5.png',
		'html' => '<div data-rowtype="14" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-4-5">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-1-5">%4$s</div><div style="clear:both;"></div></div></div>'
	),
	15 => array(
		'value' => 'row-1-5-1-5-3-5',
		'label' => 'One fifth + One fifth + Three fifths',
		'image' => $this->url .'images/row_icons/row-1-5-1-5-3-5.png',
		'html' => '<div data-rowtype="15" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-1-5">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-1-5">%4$s</div><div data-colnumber="2" class="pbuilder_column pbuilder_column-3-5">%5$s</div><div style="clear:both;"></div></div></div>'
	),
	16 => array(
		'value' => 'row-3-5-1-5-1-5',
		'label' => 'Three fifth + One fifth + One fifths',
		'image' => $this->url .'images/row_icons/row-3-5-1-5-1-5.png',
		'html' => '<div data-rowtype="16" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-3-5">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-1-5">%4$s</div><div data-colnumber="2" class="pbuilder_column pbuilder_column-1-5">%5$s</div><div style="clear:both;"></div></div></div>'
	),
	17 => array(
		'value' => 'row-2-5-3-5',
		'label' => 'Two fifths + Three fifths',
		'image' => $this->url .'images/row_icons/row-2-5-3-5.png',
		'html' => '<div data-rowtype="17" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-2-5">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-3-5">%4$s</div><div style="clear:both;"></div></div></div>'
	),
	18 => array(
		'value' => 'row-3-5-2-5',
		'label' => 'Three fifths + Two fifths',
		'image' => $this->url .'images/row_icons/row-3-5-2-5.png',
		'html' => '<div data-rowtype="18" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-3-5">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-2-5">%4$s</div><div style="clear:both;"></div></div></div>'
	),
	19 => array(
		'value' => 'row-2-5-1-5-2-5',
		'label' => 'Two fifth + One fifth + Two fifths',
		'image' => $this->url .'images/row_icons/row-2-5-1-5-2-5.png',
		'html' => '<div data-rowtype="19" data-rowid="%1$s" class="pbuilder_row">%2$s<div class="pbuilder_row_colwrapper"><div data-colnumber="0" class="pbuilder_column pbuilder_column-2-5">%3$s</div><div data-colnumber="1" class="pbuilder_column pbuilder_column-1-5">%4$s</div><div data-colnumber="2" class="pbuilder_column pbuilder_column-2-5">%5$s</div><div style="clear:both;"></div></div></div>'
	)
);
$output = $pbuilder_rows;
?>
