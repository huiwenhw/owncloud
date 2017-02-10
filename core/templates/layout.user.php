<!DOCTYPE html>
<html>
<head>
	<title><?php echo isset($_['application']) && !empty($_['application'])?$_['application'].' | ':'' ?>ownCloud <?php echo OC_User::getUser()?' ('.OC_User::getUser().') ':'' ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="<?php echo image_path('', 'favicon.png'); ?>" /><link rel="apple-touch-icon-precomposed" href="<?php echo image_path('', 'favicon-touch.png'); ?>" />
	<?php foreach($_['cssfiles'] as $cssfile): ?>
		<link rel="stylesheet" href="<?php echo $cssfile; ?>" type="text/css" media="screen" />
	<?php endforeach; ?>
		<script type="text/javascript">
		var oc_webroot = '<?php echo OC::$WEBROOT; ?>';
		var oc_appswebroot = '<?php echo OC::$APPSWEBROOT; ?>';
		var oc_current_user = '<?php echo OC_User::getUser() ?>';
	</script>
	<script type="text/javascript" src="/owncloud/core/js/intro.js"></script>
	<?php foreach($_['jsfiles'] as $jsfile): ?>
		<script type="text/javascript" src="<?php echo $jsfile; ?>"></script>
	<?php endforeach; ?>
	<link rel="stylesheet" href="/owncloud/core/css/introjs.css" type="text/css" media="screen" />
	<?php foreach($_['headers'] as $header): ?>
		<?php
		echo '<'.$header['tag'].' ';
		foreach($header['attributes'] as $name=>$value){
			echo "$name='$value' ";
		};
		echo '/>';
		?>
	<?php endforeach; ?>
</head>

<body id="<?php echo $_['bodyid'];?>">
	<header>
		<div id="header">
			<a href="<?php echo link_to('', 'index.php'); ?>" title="" id="owncloud"><img class="svg" src="<?php echo image_path('', 'logo-wide.svg'); ?>" alt="ownCloud" /></a>
			<form class="searchbox" action="#" method="post">
				<input id="searchbox" class="svg" type="search" name="query" value="<?php if(isset($_POST['query'])){echo htmlentities($_POST['query']);};?>" autocomplete="off" />
			</form>
			<a id="logout" href="<?php echo link_to('', 'index.php'); ?>?logout=true"><img class="svg" alt="<?php echo $l->t('Log out');?>" title="<?php echo $l->t('Log out');?>" src="<?php echo image_path('', 'actions/logout.svg'); ?>" /></a>
		</div>
	</header>

	<nav>
		<div id="navigation">
			<ul id="apps" class="svg">
				<?php foreach($_['navigation'] as $entry): ?>
					<li><a style="background-image:url(<?php echo $entry['icon']; ?>)" href="<?php echo $entry['href']; ?>" title="" <?php if( $entry['active'] ): ?> class="active"<?php endif; ?>><?php echo $entry['name']; ?></a>
					</li>
				<?php endforeach; ?>
				<li><a id="guidebtn" title="Guide">Guide</a>
				</li>
			</ul>

			<ul id="settings" class="svg">
				<img role=button tabindex=0 id="expand" class="svg" alt="<?php echo $l->t('Settings');?>" src="<?php echo image_path('', 'actions/settings.svg'); ?>" />
				<span><?php echo $l->t('Settings');?></span>
				<div id="expanddiv" <?php if($_['bodyid'] == 'body-user') echo 'style="display:none;"'; ?>>
					<?php foreach($_['settingsnavigation'] as $entry):?>
						<li><a style="background-image:url(<?php echo $entry['icon']; ?>)" href="<?php echo $entry['href']; ?>" title="" <?php if( $entry["active"] ): ?> class="active"<?php endif; ?>><?php echo $entry['name'] ?></a></li>
					<?php endforeach; ?>
				</div>
			</ul>
		</div>
	</nav>

	<div id="content">
		<?php echo $_['content']; ?>
	</div>

	<!-- The Modal -->
	<div id="introModal" class="modal">
		<!-- Modal content -->
		<div class="modal-content">
			<div class="modal-header">
				<span class="close closebtn">&times;</span>
			</div>
			<div class="modal-body">
				<p>Hello, do you need assistance?</p>
				<p>
					<button class="closebtn" id="needassist">I need assistance!</button>
					<button class="closebtn" id="closemodal_f">Never show this in the future</button>
				</p>
			</div>
		</div>
	</div>

	<script type="text/javascript">
	var modal = document.getElementById('introModal');
	var checkli = $('#apps li:first')[0].textContent.replace(/\s/g, '');

	$(document).ready(function() {
		showmodal();
		$("#needassist").on( "click", function() {
			showguide(); 
		});

		closemodal();
		$('#closemodal_f').on('click', function() {
			closemodal_f();
		});

		// When the user clicks the button, open the modal 
		$('#guidebtn').on('click', function() {
			console.log('guide');
			$("#needassist").click();
		});
	});

	function showmodal() {
		$.ajax({
			url: OC.filePath('files','ajax','showpopup.php'),
			data: "",
			success: function(data) {
				console.log(data);
				if(checkli == "Files" && $('#apps li a:first').hasClass('active') && data == 1) {
					modal.style.display = "block";
				}
			}
		});
	}

	function showguide() {
		// Enabling fileactions on hover & adding attributes for introjs 
		$('#fileList tr').mouseover();
		$('#sharebtn').attr("data-step", "3");
		$('#sharebtn').attr("data-intro", "Share a file by hovering over the file! More actions available.");
		$('#sharebtn').attr("data-position", "bottom-middle-aligned");
		$('.delete').attr("data-step", "4");
		$('.delete').attr("data-intro", "Delete a file by hovering over the file & clicking on this button!");
		$('.delete').attr("data-position", "bottom-right-aligned");

		// Starting tooltip guide & removing fileactions when done with guide 
		var intro = introJs();
		intro.setOption('showProgress', true).start();
		intro.oncomplete(function() {
			console.log('complete');
			$('#fileList tr').mouseleave();
		});
		intro.onexit(function() {
			console.log('exit');
			$('#fileList tr').mouseleave();
		});
	}

	function closemodal() {
		// Get the elements that close the modal
		$(".closebtn").on( "click", function() {
			modal.style.display = "none";
		});

		// When the user clicks anywhere outside of the modal, close it
		window.onclick = function(event) {
			if (event.target == modal) {
				modal.style.display = "none";
			}
		}
	}

	function closemodal_f() {
		$.ajax({
			url: OC.filePath('files','ajax','setpopup.php'),
			data: "",
			success: function(data) {
				console.log(data);
			}
		});
		console.log('close modal forever');
	}
</script>
</body>
</html>
