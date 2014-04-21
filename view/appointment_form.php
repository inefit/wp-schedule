<?php
	if($_GET['id']){
		$sql = "select * from ".$wpdb->prefix."dite_appointment where `post_id` = " . $_GET['id'];
		//echo $sql;
		$event = $wpdb->get_row($wpdb->prepare($sql));
		if($_REQUEST['method'] != 'remove')$_REQUEST['method'] = 'edit';
		
		if($event){
			$sql = "select * from ".$wpdb->prefix."dite_appointment_meta where `post_id` = " . $_GET['id'];
			$events = $wpdb->get_results($wpdb->prepare($sql));
			foreach($events as $ev){
				$meta_key = $ev->meta_key;
				$event->$meta_key = $ev->meta_value;
			}
		}
	}
	else
		$_REQUEST['method'] = 'add';
	
	if($_POST){
		$start_date = dite_date_time_mysql($_POST['stpartdate'],$_POST['stparttime']);
		$end_date = dite_date_time_mysql($_POST['etpartdate'],$_POST['etparttime']);
		if($_REQUEST['method'] == 'add'){
			$sql = "select * from ".$wpdb->prefix."dite_appointment where 
					(`start_time` between '".$start_date."' and '".$end_date."') OR 
					(`end_time` between '".$start_date."' and '".$end_date."') OR
					(`start_time` >= '".$start_date."' AND `end_time` <='".$start_date."' ) OR
					(`start_time` >= '".$end_date."' AND `end_time` <='".$end_date."' )";
			$results = $wpdb->get_results($sql);
			if(count($results) < 1){
				$data = array(
						'start_time'=> $start_date,
						'end_time' => $end_date,
						'post_content' => $_POST['Description'],
						'post_title' => $_POST['Subject'],
						'status' => 'appointment',
						'color' => 0
					);
				$new_post_id = $wpdb->insert( $wpdb->prefix.'dite_appointment', $data );
				
				dite_schedule_update_meta($new_post_id,'client_name',$_POST['client_name']);
				dite_schedule_update_meta($new_post_id,'client_email',$_POST['client_email']);
				dite_schedule_update_meta($new_post_id,'client_telp',$_POST['client_telp']);
				dite_schedule_update_meta($new_post_id,'client_address',$_POST['client_address']);
				
				echo '<div>Schedule added succesfully</div>';
			}
			else{
				echo '<div>Failed, Schedule Conflict</div>';
			}
		}
		else if($_REQUEST['method'] == 'edit'){
			$sql = "select * from ".$wpdb->prefix."dite_appointment where 
					((`start_time` between '".$start_date."' and '".$end_date."') OR 
					(`end_time` between '".$start_date."' and '".$end_date."') OR
					(`start_time` >= '".$start_date."' AND `end_time` <='".$start_date."' ) OR
					(`start_time` >= '".$end_date."' AND `end_time` <='".$end_date."' )) AND (post_id != '".$_POST['id']."')";
			$results = $wpdb->get_results($sql);
			if(count($results) < 1){
				$data = array(
						'start_time'=> $start_date,
						'end_time' => $end_date,
						'post_content' => $_POST['Description'],
						'post_title' => $_POST['Subject'],
						'status' => 'appointment',
						'color' => 0
					);
				$where = array('post_id'=>$_POST['id']);
				$wpdb->update( $wpdb->prefix.'dite_appointment', $data ,$where);
				
				dite_schedule_update_meta($_POST['id'],'client_name',$_POST['client_name']);
				dite_schedule_update_meta($_POST['id'],'client_email',$_POST['client_email']);
				dite_schedule_update_meta($_POST['id'],'client_telp',$_POST['client_telp']);
				dite_schedule_update_meta($_POST['id'],'client_address',$_POST['client_address']);
				//$sql = "UPDATE ".$wpdb->prefix."dite_appointment SET ";
				//$wpdb->query( $wpdb->prepare( $sql ));
				echo '<div>Schedule updated succesfully</div>';
			}
			else{
				echo '<div>Failed, Schedule Conflict</div>';
			}
		}	
		else if($_REQUEST['method'] == 'remove'){
			try{
				$sql = "DELETE FROM ".$wpdb->prefix."dite_appointment WHERE post_id='".$_GET['id']."'";
				$wpdb->query( $wpdb->prepare( $sql ));
				
				dite_schedule_remove_meta($_GET['id']);
				
				$ret['IsSuccess'] = true;
				$ret['Msg'] = 'Schedule Removed';
			}
			catch(Exception $e){
				$ret['IsSuccess'] = false;
				$ret['Msg'] = 'Schedule not found';
			}
			echo json_encode($ret); 
			die();
		}
	}
?>	
	<html>
		<head>
			<?php 
				wp_deregister_script('jquery-ui-datepicker');
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-datepicker' , dite_schedule_pluginurl('js') . '/jquery.datepicker.js');
				wp_enqueue_style('dailog.css', dite_schedule_pluginurl('css') . '/dailog.css');
				wp_enqueue_style('calendar.css', dite_schedule_pluginurl('css') . '/calendar.css');
				wp_enqueue_style('dp.css', dite_schedule_pluginurl('css') . '/dp.css');
				wp_enqueue_style('dropdown.css', dite_schedule_pluginurl('css') . '/dropdown.css');
				wp_enqueue_style('main-original.css', dite_schedule_pluginurl('css') . '/main-original.css');
				
				wp_enqueue_script('Common.js',  dite_schedule_pluginurl('js') . '/Common.js');
				wp_enqueue_script('datepicker_lang_US.js',  dite_schedule_pluginurl('js') . '/datepicker_lang_US.js');
				wp_enqueue_script('jquery.alert.js',  dite_schedule_pluginurl('js') . '/jquery.alert.js');
				wp_enqueue_script('jquery.ifrmdailog.js',  dite_schedule_pluginurl('js') . '/jquery.ifrmdailog.js');
				wp_enqueue_script('wdCalendar_lang_US.js',  dite_schedule_pluginurl('js') . '/wdCalendar_lang_US.js');
				wp_enqueue_script('jquery.calendar.js',  dite_schedule_pluginurl('js') . '/jquery.calendar.js');
				wp_enqueue_script('jquery.dropdown.js',  dite_schedule_pluginurl('js') . '/jquery.dropdown.js');
				wp_head(); 
			
			?>
			<script type="text/javascript">
				if (!DateAdd || typeof (DateDiff) != "function") {
					var DateAdd = function(interval, number, idate) {
						number = parseInt(number);
						var date;
						if (typeof (idate) == "string") {
							date = idate.split(/\D/);
							eval("var date = new Date(" + date.join(",") + ")");
						}
						if (typeof (idate) == "object") {
							date = new Date(idate.toString());
						}
						switch (interval) {
							case "y": date.setFullYear(date.getFullYear() + number); break;
							case "m": date.setMonth(date.getMonth() + number); break;
							case "d": date.setDate(date.getDate() + number); break;
							case "w": date.setDate(date.getDate() + 7 * number); break;
							case "h": date.setHours(date.getHours() + number); break;
							case "n": date.setMinutes(date.getMinutes() + number); break;
							case "s": date.setSeconds(date.getSeconds() + number); break;
							case "l": date.setMilliseconds(date.getMilliseconds() + number); break;
						}
						return date;
					}
				}
				function getHM(date)
				{
					 var hour =date.getHours();
					 var minute= date.getMinutes();
					 var ret= (hour>9?hour:"0"+hour)+":"+(minute>9?minute:"0"+minute) ;
					 return ret;
				}
				jQuery(document).ready(function($) {
					//debugger;
					var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
					var DATA_FEED_URL = ajaxurl+"?action=appointment_form&id=<?php echo $_GET['id']?>&";
					var arrT = [];
					var tt = "{0}:{1}";
					for (var i = 0; i < 24; i++) {
						arrT.push({ text: StrFormat(tt, [i >= 10 ? i : "0" + i, "00"]) }, { text: StrFormat(tt, [i >= 10 ? i : "0" + i, "30"]) });
					}
					$("#timezone").val(new Date().getTimezoneOffset()/60 * -1);
					$("#stparttime").dropdown({
						dropheight: 200,
						dropwidth:60,
						selectedchange: function() { },
						items: arrT
					});
					$("#etparttime").dropdown({
						dropheight: 200,
						dropwidth:60,
						selectedchange: function() { },
						items: arrT
					});
					var check = $("#IsAllDayEvent").click(function(e) {
						if (this.checked) {
							$("#stparttime").val("00:00").hide();
							$("#etparttime").val("00:00").hide();
						}
						else {
							var d = new Date();
							var p = 60 - d.getMinutes();
							if (p > 30) p = p - 30;
							d = DateAdd("n", p, d);
							$("#stparttime").val(getHM(d)).show();
							$("#etparttime").val(getHM(DateAdd("h", 1, d))).show();
						}
					});
					if (check[0].checked) {
						$("#stparttime").val("00:00").hide();
						$("#etparttime").val("00:00").hide();
					}
					$("#Savebtn").click(function() { $("#fmEdit").submit(); });
					$("#Closebtn").click(function() { CloseModelWindow(null,true); });
					$("#Deletebtn").click(function() {
						 if (confirm("Are you sure to remove this event")) {  
							var param = [{ "name": "calendarId", value: 8}];                
							$.post(DATA_FEED_URL + "method=remove",
								param,
								function(data){
									  if (data.IsSuccess) {
											alert(data.Msg); 
											CloseModelWindow(null,true);                            
										}
										else {
											alert("Error occurs.\r\n" + data.Msg);
										}
								}
							,"json");
						}
					});
					
				   $("#stpartdate,#etpartdate").datepicker({ picker: "<button class='calpick'></button>"});    
					var cv =$("#colorvalue").val() ;
					if(cv=="")
					{
						cv="-1";
					}
					$("#calendarcolor").colorselect({ title: "Color", index: cv, hiddenid: "colorvalue" });
					//to define parameters of ajaxform
					var options = {
						beforeSubmit: function() {
							return true;
						},
						dataType: "json",
						success: function(data) {
							alert(data.Msg);
							if (data.IsSuccess) {
								CloseModelWindow(null,true);  
							}
						}
					};
					$.validator.addMethod("date", function(value, element) {                             
						var arrs = value.split(i18n.datepicker.dateformat.separator);
						var year = arrs[i18n.datepicker.dateformat.year_index];
						var month = arrs[i18n.datepicker.dateformat.month_index];
						var day = arrs[i18n.datepicker.dateformat.day_index];
						var standvalue = [year,month,day].join("-");
						return this.optional(element) || /^(?:(?:1[6-9]|[2-9]\d)?\d{2}[\/\-\.](?:0?[1,3-9]|1[0-2])[\/\-\.](?:29|30))(?: (?:0?\d|1\d|2[0-3])\:(?:0?\d|[1-5]\d)\:(?:0?\d|[1-5]\d)(?: \d{1,3})?)?$|^(?:(?:1[6-9]|[2-9]\d)?\d{2}[\/\-\.](?:0?[1,3,5,7,8]|1[02])[\/\-\.]31)(?: (?:0?\d|1\d|2[0-3])\:(?:0?\d|[1-5]\d)\:(?:0?\d|[1-5]\d)(?: \d{1,3})?)?$|^(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])[\/\-\.]0?2[\/\-\.]29)(?: (?:0?\d|1\d|2[0-3])\:(?:0?\d|[1-5]\d)\:(?:0?\d|[1-5]\d)(?: \d{1,3})?)?$|^(?:(?:16|[2468][048]|[3579][26])00[\/\-\.]0?2[\/\-\.]29)(?: (?:0?\d|1\d|2[0-3])\:(?:0?\d|[1-5]\d)\:(?:0?\d|[1-5]\d)(?: \d{1,3})?)?$|^(?:(?:1[6-9]|[2-9]\d)?\d{2}[\/\-\.](?:0?[1-9]|1[0-2])[\/\-\.](?:0?[1-9]|1\d|2[0-8]))(?: (?:0?\d|1\d|2[0-3])\:(?:0?\d|[1-5]\d)\:(?:0?\d|[1-5]\d)(?:\d{1,3})?)?$/.test(standvalue);
					}, "Invalid date format");
					$.validator.addMethod("time", function(value, element) {
						return this.optional(element) || /^([0-1]?[0-9]|2[0-3]):([0-5][0-9])$/.test(value);
					}, "Invalid time format");
					$.validator.addMethod("safe", function(value, element) {
						return this.optional(element) || /^[^$\<\>]+$/.test(value);
					}, "$<> not allowed");
					$("#fmEdit").validate({
						submitHandler: function(form) { $("#fmEdit").ajaxSubmit(options); },
						errorElement: "div",
						errorClass: "cusErrorPanel",
						errorPlacement: function(error, element) {
							showerror(error, element);
						}
					});
					function showerror(error, target) {
						var pos = target.position();
						var height = target.height();
						var newpos = { left: pos.left, top: pos.top + height + 2 }
						var form = $("#fmEdit");             
						error.appendTo(form).css(newpos);
					}
				});
			</script>      
			<style type="text/css">     
			.calpick     {        
				width:16px;   
				height:16px;     
				border:none;        
				cursor:pointer;        
				background:url("<?php echo dite_schedule_pluginurl('css') ?>/images/cal.gif") no-repeat center 2px;        
				margin-left:-22px;    
			}      
			</style>

		</head>
		<body>
		<div>      
			<div class="toolBotton">           
				<a id="Savebtn" class="imgbtn" href="javascript:void(0);">                
					<span class="Save"  title="Save the calendar">Save(<u>S</u>)</span>          
				</a>                           
				<?php if(isset($event)){ ?>
				<a id="Deletebtn" class="imgbtn" href="javascript:void(0);">                    
					<span class="Delete" title="Cancel the calendar">Delete(<u>D</u>)
					</span>                
				</a>             
				<?php } ?>      		
				<a id="Closebtn" class="imgbtn" href="javascript:void(0);">                
					<span class="Close" title="Close the window" >Close</span>
				</a>   
			</div>                  
			<div style="clear: both">         
			</div>        
			<div class="infocontainer">            
				<form action="" class="fform" id="fmEdit" method="post">                 
					<label><span>*Subject:</span>                    
						<div id="calendarcolor">
						</div>
						<input MaxLength="200" class="required safe" id="Subject" name="Subject" style="width:85%;" type="text" value="<?php echo $event->post_title ?>" />                     
						<input id="colorvalue" name="colorvalue" type="hidden" value="" />                
					</label>                 
					<label>                    
						<span>*Time:</span>  
						<?php if(isset($event)){
							$sarr = explode(" ", dite_schedule_php2jstime(dite_schedule_mysql2phptime($event->start_time)));
							$earr = explode(" ", dite_schedule_php2jstime(dite_schedule_mysql2phptime($event->end_time)));
						}?>  						
						<div>  
							<input MaxLength="10" class="required date" id="stpartdate" name="stpartdate" style="padding-left:2px;width:90px;" type="text" value="<?php echo isset($event)?$sarr[0]:""; ?>" />                       
							<input MaxLength="5" class="required time" id="stparttime" name="stparttime" style="width:60px;" type="text" value="<?php echo isset($event)?$sarr[1]:""; ?>" />To                       
							<input MaxLength="10" class="required date" id="etpartdate" name="etpartdate" style="padding-left:2px;width:90px;" type="text" value="<?php echo isset($event)?$earr[0]:""; ?>" />                       
							<input MaxLength="50" class="required time" id="etparttime" name="etparttime" style="width:60px;" type="text" value="<?php echo isset($event)?$earr[1]:""; ?>" />                                            
							<label style="display:none;" class="checkp"> 
								<input id="IsAllDayEvent" name="IsAllDayEvent" type="checkbox" value="<?php echo $event->whole_day ?>" />Whole Day                       
							</label>                    
						</div>                
					</label>                 
					<label>                    
						<span>Description:</span>                    
						<textarea cols="20" id="Description" name="Description" rows="2" style="width:95%; height:70px"><?php echo $event->post_content ?></textarea>                
					</label>    
					<h4>Client Information:</h4>
					<label>                    
						<span>Name:</span>                    
						<input MaxLength="200" id="client_name" name="client_name" style="width:65%;" type="text" value="<?php echo $event->client_name ?>" />                 
					</label>
					<label>                    
						<span>Email:</span>                    
						<input MaxLength="200" id="client_email" name="client_email" style="width:65%;" type="text" value="<?php echo $event->client_email ?>" />                 
					</label>
					<label>                    
						<span>Telp:</span>                    
						<input MaxLength="200" id="client_telp" name="client_telp" style="width:65%;" type="text" value="<?php echo $event->client_telp ?>" />                 
					</label>               
					<label>                    
						<span>Address:</span>                    
						<textarea cols="20" id="client_address" name="client_address" rows="2" style="width:95%; height:70px">
							<?php echo $event->client_address ?>
						</textarea>                
					</label>   
					<input id="id" name="id" type="hidden" value="<?php echo $event->post_id?>" />           
				</form>         
			</div>         
		</div>
		</body>
	</html>