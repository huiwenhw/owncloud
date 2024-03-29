var Templates = {
	inviteModal: function() {
		return '\
		<div id="inviteModal" class="inviteModalClass">\
		<div class="modal-content">\
		<div class="modal-header">\
		<span class="close" id="closeinvitemodal">&times;</span>\
		</div>\
		<div class="modal-body">\
		<p id="modal_append_user">Click on a user to invite them to comment!</p>\
		<p><input id="submitinvite" type="submit" value="Invite"></input></p>\
		</div>\
		</div>\
		</div>\
		';
	}
}

function setEditorSize(){
	// Sets the size of the text editor window.
	fillWindow($('#editor'));
}

function getFileExtension(file){
	var parts=file.split('.');
	return parts[parts.length-1];
}

function setSyntaxMode(ext){
	// Loads the syntax mode files and tells the editor
	var filetype = new Array();
	// add file extensions like this: filetype["extension"] = "filetype":
	filetype["h"] = "c_cpp";
	filetype["c"] = "c_cpp";
	filetype["clj"] = "clojure";
	filetype["coffee"] = "coffee"; // coffescript can be compiled to javascript
	filetype["coldfusion"] = "cfc";
	filetype["cpp"] = "c_cpp";
	filetype["cs"] = "csharp";
	filetype["css"] = "css";
	filetype["groovy"] = "groovy";
	filetype["haxe"] = "hx";
	filetype["htm"] = "html";
	filetype["html"] = "html";
	filetype["java"] = "java";
	filetype["js"] = "javascript";
	filetype["jsm"] = "javascript";
	filetype["json"] = "json";
	filetype["latex"] = "latex";
	filetype["less"] = "less";
	filetype["ly"] = "latex";
	filetype["ily"] = "latex";
	filetype["lua"] = "lua";
	filetype["markdown"] = "markdown";
	filetype["md"] = "markdown";
	filetype["mdown"] = "markdown";
	filetype["mdwn"] = "markdown";
	filetype["mkd"] = "markdown";
	filetype["ml"] = "ocaml";
	filetype["mli"] = "ocaml";
	filetype["pl"] = "perl";
	filetype["php"] = "php";
	filetype["powershell"] = "ps1";
	filetype["py"] = "python";
	filetype["rb"] = "ruby";
	filetype["scad"] = "scad"; // seems to be something like 3d model files printed with e.g. reprap
	filetype["scala"] = "scala";
	filetype["scss"] = "scss"; // "sassy css"
	filetype["sh"] = "sh";
	filetype["sql"] = "sql";
	filetype["svg"] = "svg";
	filetype["textile"] = "textile"; // related to markdown
	filetype["xml"] = "xml";

	if(filetype[ext]!=null){
		// Then it must be in the array, so load the custom syntax mode
		// Set the syntax mode
		OC.addScript('files_texteditor','aceeditor/mode-'+filetype[ext], function(){
			var SyntaxMode = require("ace/mode/"+filetype[ext]).Mode;
			window.aceEditor.getSession().setMode(new SyntaxMode());
		});
	}
}

function showControls(filename,writeperms){
	// Loads the control bar at the top.
	// Load the new toolbar.
	var editorbarhtml = '<div id="editorcontrols" style="display: none;"><div class="crumb svg last" id="breadcrumb_file" style="background-image:url(&quot;'+OC.imagePath('core','breadcrumb.png')+'&quot;)"><p>'+filename.replace(/</, "&lt;").replace(/>/, "&gt;")+'</p></div>';
	if(writeperms=="true"){
		editorbarhtml += '<button id="editor_save">'+t('files_texteditor','Save')+'</button><div class="separator"></div>';
	}
	editorbarhtml += '<label for="editorseachval">Search:</label><input type="text" name="editorsearchval" id="editorsearchval"><div class="separator"></div><button id="editor_close">'+t('files_texteditor','Close')+'</button></div>';
	//editorbarhtml += '<label for="editorseachval">Search:</label><input type="text" name="editorsearchval" id="editorsearchval"><div class="separator"></div><button id="editor_close">'+t('files_texteditor','Close')+'</button><button id="editor_comment">'+t('files_texteditor','Invite to Comment')+'</button></div>';
	// Change breadcrumb classes
	$('#controls .last').removeClass('last');
	$('#controls').append(editorbarhtml);
	$('#editorcontrols').fadeIn('slow');
}

function bindControlEvents(){
	$("#editor_save").die('click',doFileSave).live('click',doFileSave);
	$('#editor_close').die('click',hideFileEditor).live('click',hideFileEditor);
	//$('#editor_comment').die('click',showInviteModal).live('click',showInviteModal);
	$('#editorsearchval').die('keyup', doSearch).live('keyup', doSearch);
	$('#clearsearchbtn').die('click', resetSearch).live('click', resetSearch);
	$('#nextsearchbtn').die('click', nextSearchResult).live('click', nextSearchResult);
}

function addInviteModal() {
	console.log("add invite modal");
	var InviteModalHtml = $(Templates.inviteModal());
	$('#controls').append(InviteModalHtml);

	// To get users 
	$.ajax({
		url: OC.filePath('files_texteditor','ajax','getuser.php'),
		data: "",
		success: function(data) {
			var users = JSON.parse(data);
			console.log(users);

			var currentuser = users[0];
			for(var i=1 ; i<users.length ;i++) {
				if(users[i] != currentuser) {
					var template = '<p><input class="inviteusers" type="checkbox" name="inviteusers" value="' + users[i] + '">' + users[i] + '</input></p>';
					$('#modal_append_user').append(template);
				}
			}
		}
	});
}

function showInviteModal() {
	console.log("show invite modal");
	var showInviteModal = document.getElementById("inviteModal");
	showInviteModal.style.display = "block";

	// Get the elements that close the modal
	$("#closeinvitemodal").on( "click", function() {
		showInviteModal.style.display = "none";
	});

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
		if (event.target == showInviteModal) {
			console.log("close");
			showInviteModal.style.display = "none";
		}
	}
}

// returns true or false if the editor is in view or not
function editorIsShown(){
	// Not working as intended. Always returns true.
	return is_editor_shown;
}

//resets the search
function resetSearch(){
	$('#editorsearchval').val('');
	$('#nextsearchbtn').remove();
	$('#clearsearchbtn').remove();
	window.aceEditor.gotoLine(0);
}

// moves the cursor to the next search resukt
function nextSearchResult(){
	window.aceEditor.findNext();
}
// Performs the initial search
function doSearch(){
	// check if search box empty?
	if($('#editorsearchval').val()==''){
		// Hide clear button
		window.aceEditor.gotoLine(0);
		$('#nextsearchbtn').remove();
		$('#clearsearchbtn').remove();
	} else {
		// New search
		// Reset cursor
		window.aceEditor.gotoLine(0);
		// Do search
		window.aceEditor.find($('#editorsearchval').val(),{
			backwards: false,
			wrap: false,
			caseSensitive: false,
			wholeWord: false,
			regExp: false
		});
		// Show next and clear buttons
		// check if already there
		if($('#nextsearchbtn').length==0){
			var nextbtnhtml = '<button id="nextsearchbtn">Next</button>';
			var clearbtnhtml = '<button id="clearsearchbtn">Clear</button>';
			$('#editorsearchval').after(nextbtnhtml).after(clearbtnhtml);
		}
	}
}

// Tries to save the file.
function doFileSave(){
	if(editorIsShown()){
		// Changed contents?
		if($('#editor').attr('data-edited')=='true'){
			// Get file path
			var path = $('#editor').attr('data-dir')+'/'+$('#editor').attr('data-filename');
			// Get original mtime
			var mtime = $('#editor').attr('data-mtime');
			// Show saving spinner
			$("#editor_save").die('click',doFileSave);
			$('#save_result').remove();
			$('#editor_save').text(t('files_texteditor','Saving...'));
			// Get the data
			var filecontents = window.aceEditor.getSession().getValue();
			// Send the data
			$.post(OC.filePath('files_texteditor','ajax','savefile.php'), { filecontents: filecontents, path: path, mtime: mtime },function(jsondata){
				if(jsondata.status!='success'){
					// Save failed
					$('#editor_save').text(t('files_texteditor','Save'));
					$('#editor_save').after('<p id="save_result" style="float: left">Failed to save file</p>');
					$("#editor_save").live('click',doFileSave);
				} else {
					// Save OK
					// Update mtime
					$('#editor').attr('data-mtime',jsondata.data.mtime);
					$('#editor_save').text(t('files_texteditor','Save'));
					$("#editor_save").live('click',doFileSave);
					// Update titles
					$('#editor').attr('data-edited', 'false');
					$('#breadcrumb_file').text($('#editor').attr('data-filename'));
					document.title = $('#editor').attr('data-filename')+' - ownCloud';
				}
			},'json');
		}
	}
	giveEditorFocus();
};

// Gives the editor focus
function giveEditorFocus(){
	window.aceEditor.focus();
};

// Loads the file editor. Accepts two parameters, dir and filename.
function showFileEditor(dir,filename){
	// Delete any old editors
	$('#editor').remove();
	if(!editorIsShown()){
		// Loads the file editor and display it.
		$('#content').append('<div id="editor"></div>');
		var data = $.getJSON(
			OC.filePath('files_texteditor','ajax','loadfile.php'),
			{file:filename,dir:dir},
			function(result){
				if(result.status == 'success'){
					// Save mtime
					$('#editor').attr('data-mtime', result.data.mtime);
					// Initialise the editor
					$('.actions,#file_action_panel').fadeOut('slow');
					$('table').fadeOut('slow', function() {
						// Show the control bar
						showControls(filename,result.data.write);
						// Update document title
						document.title = filename+' - ownCloud';
						$('#editor').text(result.data.filecontents);
						$('#editor').attr('data-dir', dir);
						$('#editor').attr('data-filename', filename);
						$('#editor').attr('data-edited', 'false');
						window.aceEditor = ace.edit("editor");
						aceEditor.setShowPrintMargin(false);
						aceEditor.getSession().setUseWrapMode(true);
						if(result.data.write=='false'){
							aceEditor.setReadOnly(true);
						}
						setEditorSize();
						setSyntaxMode(getFileExtension(filename));
						OC.addScript('files_texteditor','aceeditor/theme-clouds', function(){
							window.aceEditor.setTheme("ace/theme/clouds");
						});
						window.aceEditor.getSession().on('change', function(){
							if($('#editor').attr('data-edited')!='true'){
								$('#editor').attr('data-edited', 'true');
								$('#breadcrumb_file').text($('#breadcrumb_file').text()+' *');
								document.title = $('#editor').attr('data-filename')+' * - ownCloud';
							}
						});
						// Add the ctrl+s event
						window.aceEditor.commands.addCommand({
							name: "save",
							bindKey: {
								win: "Ctrl-S",
								mac: "Command-S",
								sender: "editor"
							},
							exec: function(){
								doFileSave();	
							}
						});
					});

					console.log(`dir: ${dir}, path: ${dir}/${filename}`);
					// Starting comment section 
					var addcomment = "";
					addcomment += '<div id="comment_container">';
					addcomment += '<div id="comments">';

					// Adding comments into comment container 
					$.ajax({
						url: OC.filePath('files_texteditor','ajax','getcomments.php'),
						type: "POST",
						data: {file:filename},
						success: function(jsondata) {
							var data = JSON.parse(jsondata);
							//console.log(`data ${jsondata}`);
							for(var i in data) {
								//console.log(`data[${i}]: ${data[i]} data[${i}][0]: ${data[i][0]} `);
								var addcomment = "";
								addcomment += '<div class="comment">';
								addcomment += '<input data-id="' + data[i]['commentid'] + '"class="deletecomment"  onclick="deletecomment(this)" type="submit" value="&times;"></input>';
								addcomment += '<div class="comment_uid">User: ';
								addcomment += data[i]['user'];
								addcomment += '</div>';		// for div class=comment_uid
								addcomment += '<div class="comment_text">';
								addcomment += data[i]['content'];
								addcomment += '</div>';		// for div class=comment_text
								addcomment += '<hr>';
								addcomment += '</div>';		// for div class=comment 
								$('#comments').append(addcomment);
							}
							savecomment(dir, filename);
						}
					});

					addcomment += '</div>';		// for div id=comments, to append comment  
					// Textbox for users to input comments 
					addcomment += '<div id="textbox">';
					addcomment += '<input id="textbox_comment" type="textarea" placeholder="Comments go here! ..."></input>';
					addcomment += '<input id="saveusercomment" type="submit" value="Comment" />';
					addcomment += '</div>'; 	// for div id=textbox
					addcomment += '</div>';		// for div id=comment_container 
					$('#content').append(addcomment);
					checkcommentpermissions(filename);
				} else {
					// Failed to get the file.
					OC.dialogs.alert(result.data.message, t('files_texteditor','An error occurred!'));
				}
			// End success
		}
		// End ajax
		);
		is_editor_shown = true;
	}
}

function checkcommentpermissions(filename) {
	$.ajax({
		url: OC.filePath('files_texteditor', 'ajax', 'usercommentpermissions.php'),
		type: 'POST',
		data: {file:filename},
		success: function(jsondata) {
			var data = JSON.parse(jsondata);
			console.log(`check user comment perms: ${data['cancomment']} perm: ${data['perm']} ownfileperm: ${data['ownfileperm']}`);
			if(data['cancomment'] == 'success') {
				//$('#textbox_comment').removeAttr('readonly');
			} else {
				$('#textbox').remove();
				console.log('user cannot comment');
				//$('#textbox_comment').attr('readonly', 'true');
			}
		} 
	});
}

function savecomment(dir, filename) {
	$('#textbox_comment').keyup(function(event) {
		if(event.keyCode == 13) {
			$('#saveusercomment').click();
		}
	});

	$('#saveusercomment').on('click', function() {
		var content = $('#textbox_comment').val();
		var user = '';
		if(content != "") {
			$.ajax({
				url: OC.filePath('files_texteditor','ajax','savecomment.php'),
				type: "POST",
				data: {dir:dir, file:filename, content:content},
				success: function(jsondata) {
					var data = JSON.parse(jsondata);
					console.log(`save comment: user ${data['user']} commentid: ${data['commentid']}`);
					var addcomment = '';
					addcomment += '<div class="comment">';
					addcomment += '<input data-id="' + data['commentid'] + '"class="deletecomment" onclick="deletecomment(this)" type="submit" value="&times;"></input>';
					addcomment += '<div class="comment_uid">User: ';
					addcomment += data['user'];
					addcomment += '</div>';		// for div class=comment_uid
					addcomment += '<div class="comment_text">';
					addcomment += content;
					addcomment += '</div>';		// for div class=comment_text
					addcomment += '<hr>';
					addcomment += '</div>';		// for div class=comment 

					$('#comments').prepend(addcomment);
					$('#textbox_comment').val('');
				}
			});
		}
	});
}

function deletecomment(obj) {
	var commentid = $(obj).attr('data-id');
	var commentparent = $(obj).parent();
	console.log($(obj).parent());

	$.ajax({
		url: OC.filePath('files_texteditor', 'ajax', 'deletecomment.php'),
		type: "POST",
		data: {commentid:commentid},
		success: function(jsondata) {
			var data = JSON.parse(jsondata);
			console.log(`deletecomment: uid ${data['uid']} file_owner ${data['file_owner']} result ${data['result']} commentid ${commentid}`);
			if(data['result'] == "success") {
				$(obj).parent().remove();
			} else {
				alert('Sorry, you are not allowed to remove this comment!');
			}
		}
	});
}

// Fades out the editor.
function hideFileEditor(){
	if($('#editor').attr('data-edited') == 'true'){
		// Hide, not remove
		$('#editorcontrols').fadeOut('slow',function(){
			// Check if there is a folder in the breadcrumb
			if($('.crumb.ui-droppable').length){
				$('.crumb.ui-droppable:last').addClass('last');
			}
		});
		// Fade out editor
		$('#editor').fadeOut('slow', function(){
			// Reset document title
			document.title = "ownCloud";
			$('.actions,#file_access_panel').fadeIn('slow');
			$('table').fadeIn('slow');
		});
		$('#notification').text(t('files_texteditor','There were unsaved changes, click here to go back'));
		$('#notification').data('reopeneditor',true);
		$('#notification').fadeIn();
		is_editor_shown = false;
	} else {
		// Remove editor
		$('#editorcontrols').fadeOut('slow',function(){
			$(this).remove();
			$(".crumb:last").addClass('last');
		});
		// Fade out editor
		$('#editor').fadeOut('slow', function(){
			$(this).remove();
			// Reset document title
			document.title = "ownCloud";
			$('.actions,#file_access_panel').fadeIn('slow');
			$('table').fadeIn('slow');
		});
		is_editor_shown = false;
	}
	$('#comment_container').remove();
}

// Reopens the last document
function reopenEditor(){
	$('.actions,#file_action_panel').fadeOut('slow');
	$('table').fadeOut('slow', function(){
		$('#controls .last').not('#breadcrumb_file').removeClass('last');
		$('#editor').fadeIn('fast');
		$('#editorcontrols').fadeIn('fast', function(){

		});
	});
	is_editor_shown  = true;
}

// resizes the editor window
$(window).resize(function() {
	setEditorSize();
});
var is_editor_shown = false;
$(document).ready(function(){
	if(typeof FileActions!=='undefined'){
		//addInviteModal();
		FileActions.register('text','Edit','',function(filename){
			showFileEditor($('#dir').val(),filename);
		});
		FileActions.setDefault('text','Edit');
		FileActions.register('application/xml','Edit','',function(filename){
			showFileEditor($('#dir').val(),filename);
		});
		FileActions.setDefault('application/xml','Edit');
	}
	OC.search.customResults.Text=function(row,item){
		var text=item.link.substr(item.link.indexOf('?file=')+6);
		var a=row.find('a');
		a.data('file',text);
		a.attr('href','#');
		a.click(function(){
			var pos=text.lastIndexOf('/')
			var file=text.substr(pos + 1);
			var dir=text.substr(0,pos);
			showFileEditor(dir,file);
		});
	};
	savecomment();
	$('#saveusercomment').click();
	// Binds the file save and close editor events, and gotoline button
	bindControlEvents();
	$('#editor').remove();
	$('#notification').click(function(){
		if($('#notification').data('reopeneditor'))
		{
			reopenEditor();
		}
		$('#notification').fadeOut();
	});

	/*
	// Add comment function
	$('#submitinvite').on('click', function() {
		var checkedusers = [];
		$('.inviteusers:checked').each(function() {
			checkedusers.push($(this).val());
		});
		console.log(checkedusers);

		var jsonString = JSON.stringify(checkedusers);
		$.ajax({
			url: OC.filePath('files_texteditor','ajax','sendemail.php'),
			type: "POST",
			data: {data : jsonString},
			success: function(data) {
				console.log(data);
			}
		});
		$('#closeinvitemodal').click();
	});
	*/
});