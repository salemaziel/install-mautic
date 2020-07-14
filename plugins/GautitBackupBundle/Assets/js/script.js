var processStarted = false;
jQuery(document).ready(function(){
	jQuery('#gautit-backup-btn').click(function(){
		if(processStarted == false){
			preStart();
			processStarted = true;
		}
	});
	jQuery('#myTab a[href="#backup-tab"]').tab('show'); // Select tab by name
	jQuery('#gautit-existing-backup table tbody .input-group-btn .dropdown-menu li a span span').html('Delete local copy');

function preStart(){
	jQuery('.loading-bar').addClass('active');
	var locations = [];
	var backupName = jQuery('input#backup-name').val();
	jQuery('input[name="backup-location[]"]:checked').each(function(index,val){	
		locations.push(jQuery(this).val());
	});
	jQuery.ajax({
		url:jQuery('#gautit-js-data').data('url-prestart'),
		type:'POST',
		datatype:'json',
		data:{'location':locations,'backup_name':backupName},
		success:function(resp){
			if(resp){
				jQuery('#gautit-backup-status').html("Backup Process started....");
				doPoll(resp);
				startBackUp(resp);
			}
		}
	});

}
function startBackUp(data){
	jQuery('.loading-bar').addClass('active');
	jQuery.ajax({
		url:jQuery('#gautit-js-data').data('urlstart'),
		type:'POST',
		datatype:'json',
		data:data,
		success:function(resp){
			processStarted= false;
			if(resp){
			}
		},error:function(){
			processStarted= false;
		},complete:function(){
			processStarted= false;			
		}
	});

}
function doPoll(rest){
	jQuery('.loading-bar').addClass('active');
	jQuery.ajax({
		url:jQuery('#gautit-js-data').data('urlstatus'),
		type:'POST',
		datatype:'json',
		data:rest,
		complete:function(){
			jQuery('.loading-bar').addClass('active');
		},
		success:function(resp){
			if(resp){
				if(resp.messsage !=''){
					jQuery('#gautit-backup-status').html(resp.message);
					if(resp.message == 'The backup apparently succeeded and is now complete.'){
						jQuery('.loading-bar').removeClass('active');

						setTimeout(() => window.location.reload(), 1200);
					}
				}
				setTimeout(function(){doPoll(rest)},1000);
			}
		
		}
	});

}
	if(gautit && gautit.params['defaultTabMenu'] && gautit.params['defaultTabMenu'] != ''){
		var defaultTabMenu = document.getElementById(gautit.params['defaultTabMenu']);
		if(defaultTabMenu){
			defaultTabMenu.click();
		}
	}else{
		
		document.getElementById("dropbox").click();
	}
	selectMenuTab();
});

function selectMenuTab(){
	var hash = window.location.hash;
	jQuery('#myTab a[href="' + hash + '"]').tab('show');

  jQuery('#myTab a').click(function (e) {
	jQuery(this).tab('show');
	var scrollmem = jQuery('body').scrollTop();
	window.location.hash = this.hash;
	jQuery('html,body').scrollTop(scrollmem);
  });

}


function openSettingsTab(evt, cityName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(cityName).style.display = "block";
  evt.currentTarget.className += " active";
}
