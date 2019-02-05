<?php defined('_JEXEC') or die('Restricted access'); // no direct access ?>

<?php

// Make sure jQuery is loaded!
JHtml::_('jquery.framework');
JHtml::stylesheet( $cofl_basepath."mod_cellonefilelister.css" );

?>

<script language="javascript" type="text/javascript">

var curPageURL = window.location.href;
if (curPageURL.indexOf(".php?") > 0) {
	curPageURL += "&";
} else {
	curPageURL += "?";
}
var curBrowseDir = "<?php echo $cofl_dirlocation; ?>";

( function(jQuery) {
// wait till the DOM is loaded

jQuery(document).ready(function() {

jQuery('#cofl_ARefresh').live('click', function() {

	var params = '&coflDir=' + curBrowseDir;

	jQuery('#div_coflcontent').css('text-align', 'center');
	jQuery('#div_coflcontent').html('')
		.append('<img style="position: relative; top: 50px;" src="<?php echo JURI::root().$cofl_basepath; ?>images/ajax-loader.gif" />')
		.fadeIn(700, function() {
			//$('#div_coflcontent').append("DONE!");
		});

	jQuery.ajax({
		type: 'GET',
		url: curPageURL,
		data: 'coflaction=dir' + params,
		cache: false,
		success: function(data) {
			jQuery('#div_coflcontent').css('text-align', 'left');
			jQuery('#div_coflcontent').html('').append(data);
		}
	});

	return false;

});

jQuery('.cofl_btnBrowseDir').live('click', function() {

	var dir = this.rel;

	var params = '&coflDir=' + dir;
	curBrowseDir = dir;

	jQuery('#div_coflcontent').css('text-align', 'center');
	jQuery('#div_coflcontent').html('')
		.append('<img style="position: relative; top: 50px;" src="<?php echo JURI::root().$cofl_basepath; ?>images/ajax-loader.gif" />')
		.fadeIn(700, function() {
			//$('#div_coflcontent').append("DONE!");
		});

	jQuery.ajax({
		type: 'GET',
		url: curPageURL,
		data: 'coflaction=dir' + params,
		cache: false,
		success: function(data) {
			jQuery('#div_coflcontent').css('text-align', 'left');
			jQuery('#div_coflcontent').html('').append(data);
		}
	});

	return false;

});

<?php if ($cofl_allowdelete === "1") { ?>

jQuery('.cofl_btnDelete').live('click', function() {

	var del = this.rel;
	var params = '&coflDelete=' + del;

	var msg = del.split('**');

	if (confirm('<?php echo JText::_('DELETE_MSG'); ?>\n(' + msg[1] + ')')) {

		jQuery('#div_coflcontent').css('text-align', 'center');
		jQuery('#div_coflcontent').html('')
			.append('<img style="position: relative; top: 50px;" src="<?php echo JURI::root().$cofl_basepath; ?>images/ajax-loader.gif" />')
			.fadeIn(700, function() {
			  //$('#div_coflcontent').append("DONE!");
			});

		jQuery.ajax({
			type: 'GET',
			url: curPageURL,
			data: 'coflaction=delete' + params,
			cache: false,
			success: function(data) {
				alert(data);
				//$('#div_coflcontent').html('').append(data);

				params = '&coflDir=' + curBrowseDir;
				jQuery.ajax({
					type: 'GET',
					url: curPageURL,
					data: 'coflaction=dir' + params,
					cache: false,
					success: function(data) {
						jQuery('#div_coflcontent').css('text-align', 'left');
						jQuery('#div_coflcontent').html('').append(data);
					}
				});
			}
		});

	}
	return false;

});
<?php } ?>

jQuery('#cofl_ASortDesc').live('click', function() {
	var params = '&coflSort=desc&coflDir=' + curBrowseDir;

	if (document.getElementById("coflSortDesc").className == "") return false;

	document.getElementById("coflSortAsc").className = "cofl_shadow";
	document.getElementById("coflSortDesc").className = "";

	jQuery('#div_coflcontent').css('text-align', 'center');

	jQuery('#div_coflcontent').html('')
		.append('<img style="position: relative; top: 50px;" src="<?php echo JURI::root().$cofl_basepath; ?>images/ajax-loader.gif" />')
		.fadeIn(700, function() {
			//$('#div_coflcontent').append("DONE!");
		});

	jQuery.ajax({
		type: 'GET',
		url: curPageURL,
		data: 'coflaction=sort' + params,
		cache: false,
		success: function(data) {
			jQuery('#div_coflcontent').css('text-align', 'left');
			jQuery('#div_coflcontent').html('').append(data);
		}
	});
	return false;

});

jQuery('#cofl_ASortAsc').live('click', function() {
	var params = '&coflSort=asc&coflDir=' + curBrowseDir;

	if (document.getElementById("coflSortAsc").className == "") return false;

	document.getElementById("coflSortAsc").className = "";
	document.getElementById("coflSortDesc").className = "cofl_shadow";

	jQuery('#div_coflcontent').css('text-align', 'center');

	jQuery('#div_coflcontent').html('')
		.append('<img style="position: relative; top: 50px;" src="<?php echo JURI::root().$cofl_basepath; ?>images/ajax-loader.gif" />')
		.fadeIn(700, function() {
			//$('#div_coflcontent').append("DONE!");
		});

	jQuery.ajax({
		type: 'GET',
		url: curPageURL,
		data: 'coflaction=sort' + params,
		cache: false,
		success: function(data) {
			jQuery('#div_coflcontent').css('text-align', 'left');
			jQuery('#div_coflcontent').html('').append(data);
		}
	});
	return false;

});

jQuery('#cofl_btnNext').live('click', function() {

	var nextVal = document.getElementById('coflNextVal').value;
	var params = '&coflNext=' + nextVal + '&coflDir=' + curBrowseDir;

	jQuery('#div_coflcontent').css('text-align', 'center');

	jQuery('#div_coflcontent').html('')
		.append('<img style="position: relative; top: 50px;" src="<?php echo JURI::root().$cofl_basepath; ?>images/ajax-loader.gif" />')
		.fadeIn(700, function() {
			//$('#div_coflcontent').append("DONE!");
		});

	jQuery.ajax({
		type: 'GET',
		url: curPageURL,
		data: 'coflaction=next' + params,
		cache: false,
		success: function(data) {
			jQuery('#div_coflcontent').css('text-align', 'left');
			jQuery('#div_coflcontent').html('').append(data);
		}
	});
	return false;

});

jQuery('#cofl_btnPrev').live('click', function() {

	var params = '';

	var prevVal = document.getElementById('coflPrevVal').value;
	if (prevVal+0 > -1) params = '&coflPrevious=' + prevVal + '&coflDir=' + curBrowseDir;

	jQuery('#div_coflcontent').css('text-align', 'center');
	jQuery('#div_coflcontent').html('')
		.append('<img style="position: relative; top: 50px;" src="<?php echo JURI::root().$cofl_basepath; ?>images/ajax-loader.gif" />')
		.fadeIn(700, function() {
		  //$('#div_coflcontent').append("DONE!");
		});

	jQuery.ajax({
		type: 'GET',
		url: curPageURL,
		data: 'coflaction=prev' + params,
		cache: false,
		success: function(data) {
			jQuery('#div_coflcontent').html('').append(data);
		}
	});
	return false;

});

});
} ) ( jQuery );

</script>

<style>
.cofldel:hover {
	border: solid 1px #CCC;
	-moz-box-shadow: 1px 1px 5px #999;
	-webkit-box-shadow: 1px 1px 5px #999;
    box-shadow: 1px 1px 5px #999;
}
.cofldel {
	height: 12px;
	position: relative;
	top: 2px;
}
</style>
<?php

if ($cofl_maxheight > 0) {
	// We're gonna have a fixed height DIV
?>

	<div id="div_coflwrapper" style="position: relative; height: <?php echo $cofl_maxheight; ?>px; overflow: auto; background: <?php echo $cofl_bgcolor ?>;">

<?php
}
?>

	<div id="div_coflcontent" class="cofl_content" style="background: <?php echo $cofl_bgcolor ?>; left: <?php echo $cofl_boxleft ?>px;">
		<span style="display: none"><a id="cofl_ARefresh" class="cofl_ARefresh" href="javascript:void(0);">Refresh</a></span>
		<?php echo $results; ?>
	</div>

<?php

if ($cofl_maxheight > 0) echo "</div>";
?>
