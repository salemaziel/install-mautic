<?php $view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('headerTitle',$view['translator']->trans('PLUGIN.GAUTIT.TITLE.BACKUP') );
//echo $view['assets']->includeScript('plugins/GautitBackupBundle/Assets/js/script.js');
?>
<script>
	var gautit = {};
	gautit.params = new Array();
	<?php if(isset($defaultTabMenu)){ ?>
		gautit.params['defaultTabMenu'] = '<?php echo $defaultTabMenu;?>';
	<?php } ?>
</script>
<script src='<?php echo $view['assets']->getUrl('plugins/GautitBackupBundle/Assets/js/script.js'); ?>' >
</script>
<link rel="stylesheet" href="<?php echo $view['assets']->getUrl('plugins/GautitBackupBundle/Assets/css/gautitbackup.css'); ?>" type="text/css"/>
<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="backup" data-toggle="tab" href="#backup-tab" role="tab" aria-controls="home" aria-selected="true"><?php echo $view['translator']->trans('PLUGIN.GAUTIT.LABEL.BACKUP'); ?></a>
  </li>
    <?php if($licensed): ?>

  <li class="nav-item">
    <a class="nav-link" id="settings" data-toggle="tab" href="#settings-tab" role="tab" aria-controls="profile" aria-selected="false"><?php echo $view['translator']->trans('PLUGIN.GAUTIT.LABEL.SETTINGS'); ?></a>
  </li>
  <?php endif; ?>
</ul>
<div class="tab-content" id="myTabContent">
  <div class="tab-pane fade  active" id="backup-tab" role="tabpanel" aria-labelledby="backup-tab">
		<!-- backtab content -->
		<div id="gautit-js-data" data-urlstatus="<?php echo $backup_status; ?>" data-urlstart="<?php echo $backup_start; ?>" data-url-prestart="<?php echo $backup_pre_start;  ?>" style="display: none;"></div>

		  <!--<div class="alert alert-info"><?php //echo $view['translator']->trans('mautic.plugin.clearbit.submit'); ?></div> -->
		<div class="panel panel-default panel-back bdr-t-wdh-0 mb-0 ml-10 mr-10 mt-10 mb-10 pl-20">
			<div class='left-panel'>
				<div class='row pt-10'>
					<div class='form-group col-xs-9'>
						<label class='control-label '><?php echo $view['translator']->trans('PLUGIN.GAUTIT.LABEL.BACKUP.NAME'); ?> </label>
						<input type='text' id='backup-name' name='backup-name' class='form-control' />
					</div>
					<div class='form-group col-xs-9'>
						<label class='control-label '>
							<?php echo $view['translator']->trans('PLUGIN.GAUTIT.LABEL.BACKUP.LOCATION'); ?> 
						</label>
						<div class='checkbox mt-0'>
							<label class='control-label '>
								<input checked='checked' value='local' disabled type='checkbox' name='backup-location[]' class='' />
								<?php echo $view['translator']->trans('PLUGIN.GAUTIT.LABEL.BACKUP.LOCAL'); ?> 
							</label>
							<?php if($licensed): ?>
							<label class='control-label '>
								<input type='checkbox' value='amazons3' name='backup-location[]' class='' />
								<?php echo $view['translator']->trans('PLUGIN.GAUTIT.LABEL.BACKUP.AMAZONS3'); ?> 
							</label>
							<label class='control-label '>
								<input type='checkbox' value='dropbox' name='backup-location[]' class='' />
								<?php echo $view['translator']->trans('PLUGIN.GAUTIT.LABEL.BACKUP.DROPBOX'); ?> 
							</label>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<div class='right-panel'>
				<a href='javascript:void(0);' id="gautit-backup-btn"  ><?php echo $view['translator']->trans('PLUGIN.GAUTIT.BUTTON.BACKUP'); ?></a>
			</div>
		</div>
		<!--
		<div class="panel panel-default panel-back-progress bdr-t-wdh-0 mb-0 ml-10 mr-10 mt-10 mb-10 pl-20 pr-20 pt-20 pb-20">
			<div class='row'
			<div id='progressbar'>
				<div></div>
			</div>
		</div>-->
		<div class='mb-20 '></div>
		<h3 class='ml-10'><?php echo $view['translator']->trans('PLUGIN.GAUTIT.LOG.BACKUP');  ?></h3>
		<div class='mb-20'></div>
		<div class="panel panel-default panel-log bdr-t-wdh-0 mb-0 ml-10 mr-10">
			<div id='gautit-backup-status' class='pl-20 pt-10'>
			</div>
		</div>
		<div class='mb-20 '></div>
		<h3 class='ml-10'><?php echo $view['translator']->trans('PLUGIN.GAUTIT.EXISTING.BACKUP');  ?></h3>
		<div class='mb-20'></div>
		<div class="bdr-t-wdh-0 mb-0 ml-10 mr-10">
			<div id='gautit-existing-backup' class=' pt-10'>

		<?php if (count($existing_backups)): ?>
		<div class="panel panel-default">

			<div class="table-responsive">
				<table class="table  table-striped table-bordered tweet-list" id="tweetTable">
					<thead>
					<tr>
						<?php
						echo $view->render(
							'MauticCoreBundle:Helper:tableheader.html.php',
							[
								'checkall'        => 'true',
								'target'          => '#tweetTable',
								'langVar'         => 'mautic.social.tweets',
								'routeBase'       => 'backup_delete',
								 'templateButtons' => [
									'delete' => true,
								],
							]
						);
						echo $view->render(
							'MauticCoreBundle:Helper:tableheader.html.php',
							[
								'text'       => 'PLUGIN.GAUTIT.LABEL.BACKUP.NAME'   ,
								'class'      => 'col-tweet-name',
								'default'    => true,
							]
						);
						echo $view->render(
							'MauticCoreBundle:Helper:tableheader.html.php',
							[
								'text'       => 'PLUGIN.GAUTIT.LABEL.BACKUPDATA'   ,
								'class'      => 'col-tweet-name',
								'default'    => true,
							]
						);
						echo $view->render(
							'MauticCoreBundle:Helper:tableheader.html.php',
							[
								'sessionVar' => 'social.tweet',
								 'orderBy'    => '',
								'text'       =>'PLUGIN.GAUTIT.LABEL.BACKUPDATE',
								'class'      => 'col-tweet-name',
								'default'    => true,
							]
						);
						echo $view->render(
							'MauticCoreBundle:Helper:tableheader.html.php',
							[
								'sessionVar' => 'backup',
								'text'       => 'mautic.core.id',
								'class'      => 'visible-md visible-lg col-asset-id',
							]
						);

						?>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($existing_backups as $k => $item): ?>
						<tr>
							<td>
								<?php
								echo $view->render(
									'MauticCoreBundle:Helper:list_actions.html.php',
									[
										'item'            => $item,
										'templateButtons' => [
										   'delete'=>true
										],
										'routeBase'  => 'backup_delete',
										'langVar'    => 'PLUGIN.GAUTIT.BACKUP.DELETE',
										'nameGetter' => 'getName',
									]
								);
								?>
							</td>
							<td>
								<?php echo $item->getName(); ?>
							</td>
							
							<td class=""><?php $files =  $item->getFiles();
								if(!empty($files)){
									$files = json_decode($files);
									if(!empty($files->files)){
							?>		
									<a href='<?php 
									echo $view['router']->path(
										'mautic_backup_download_action',
										['backId' => $item->getId(),'objectAction'=> 'files']
									);  ?>' class='btn btn-default btn-nospin'> <?php echo
									$view['translator']->trans('PLUGIN.GAUTIT.LABEL.FILES'); ?></a>
						<?php
									}
									if(!empty($files->db)){
							?>		
									<a href='<?php 
									echo $view['router']->path(
										'mautic_backup_download_action',
										['backId' => $item->getId(),'objectAction'=> 'db']
									);  ?>' class='btn btn-default btn-nospin'><?php echo
									$view['translator']->trans('PLUGIN.GAUTIT.LABEL.DATABASE'); ?></a>
						<?php
									}
									if(!empty($files->log)){?>
										<a 
										href=<?php echo $view['router']->path(
										'mautic_backup_download_action',
										['backId' => $item->getId(),'objectAction'=> 'log']
									);  ?> class='btn btn-default btn-nospin'><?php echo
									$view['translator']->trans('PLUGIN.GAUTIT.LABEL.LOG'); ?></a>
								<?php	}
								}
							?></td>
							<td class=""><?php echo $item->getBackupDate()->format('m-d-Y h:i:s'); ?></td>
							<td>
								<?php echo $item->getId(); ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div class="panel-footer">
				<?php /*echo $view->render(
					'MauticCoreBundle:Helper:pagination.html.php',
					[
						'totalItems' => count($items),
						'page'       => $page,
						'limit'      => $limit,
						'menuLinkId' => 'mautic_tweet_index',
						'baseUrl'    => $view['router']->path('mautic_tweet_index'),
						'sessionVar' => 'social.tweet',
						'routeBase'  => 'tweet',
					]
				); */ ?>
			</div>
		</div>
		<?php else: ?>
			<?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
		<?php endif; ?>
			</div>
		</div>
		<!-- backuptab content -->

  </div>
  <?php if($licensed): ?>
  <div class="tab-pane fade  " id="settings-tab" role="settings-tab" aria-labelledby="settings-tab">
		<!-- settings tab start -->
	<div class="tab">
	  <a href='javascript:void(0);' id='dropbox' class="tablinks" onclick="openSettingsTab(event, 'dropbox-tab')" id="defaultOpen"><span class='dropbox'></span><span class='text'>Dropbox</span></a>
	  <a href='javascript:void(0);' id='amazons3' class="tablinks" onclick="openSettingsTab(event, 'amazons3-tab')"><span class='s3'></span><span class='text'>Amazons S3</span></a>
	  <a href='javascript:void(0);' id='googledrive' class="tablinks" onclick="openSettingsTab(event, 'google-drive-tab')"><span class='googledrive'></span><span class='text'>Google Drive</span></a>
	  <a href='javascript:void(0);' id='onedrive' class="tablinks" onclick="openSettingsTab(event, 'one-drive-tab')"><span class='onedrive'></span><span class='text'>One Drive</span></a>
	</div>

	<div id="dropbox-tab" class="tabcontent pt-10 pb-10">
	  <h4 class="pt-15 pb-15">For instructions on setting this step up, <a target='__blank' href='https://gautit.com/gautitbackupdoc#dropbox'>click here</a>.</h4> 
		<?php   if(isset($settingsSavedDrop) && $settingsSavedDrop == true){ ?>
			<div class="alert alert-success " role="alert">
			Settings saved successfully.
			</div>
		<?php } ?>
		<?php echo $view['form']->form($dropbox_form); ?>
	</div>

	<div id="amazons3-tab" class="tabcontent pt-10 pb-10">
	  <h4 class="pt-15 pb-15">For instructions on setting this step up, <a target='__blank' href='https://gautit.com/gautitbackupdoc#a3'>click here</a>.</h4>
		<?php   if(isset($settingsSaved	) && $settingsSaved == true){ ?>
			<div class="alert alert-success " role="alert">
			Settings saved successfully.
			</div>
		<?php } ?>
	 <?php echo $view['form']->form($amazons3_form); ?>
	</div>

	<div id="google-drive-tab" class="tabcontent pt-10 pb-10">
	  <h3></h3>
	  <p>Coming soon</p>
	</div>
	<div id="one-drive-tab" class="tabcontent pt-10 pb-10">
	  <h3></h3>
	  <p>Coming soon</p>
	</div>
		<!-- settings tab end -->
  </div>
  <?php endif; ?>
</div>
