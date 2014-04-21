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
		else{

		}
	}
	else{
		$_REQUEST['method'] = 'add';
	}
	
	
?>	
	<html 	xmlns="http://www.w3.org/1999/xhtml"
  			xmlns:fb="https://www.facebook.com/2008/fbml">
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
					var DATA_FEED_URL = ajaxurl+"?action=dite_action_shortcode&id=<?php echo $_GET['id']?>&";
					var arrT = [];
					var tt = "{0}:{1}";
					for (var i = 0; i < 24; i++) {
						//arrT.push({ text: StrFormat(tt, [i >= 10 ? i : "0" + i, "00"]) }, { text: StrFormat(tt, [i >= 10 ? i : "0" + i, "30"]) });
						var menit1 = '';
						var menit2 = '';
						var ampm = '';
						j = i;
						if(i<12){ ampm = "AM"; }
						else{
							ampm = "PM";
							j = i-12;
						}

						if(j==0){
							menit1 = ["12","00"+ampm];
							menit2 = ["12","30"+ampm];
						}
						else if(j<10){
							menit1 = ["0"+j,"00"+ampm];
							menit2 = ["0"+j,"30"+ampm];
						}
						else if(j<12){
							menit1 = [j,"00"+ampm];
							menit2 = [j,"30"+ampm];
						}

						arrT.push({text:StrFormat(tt,menit1)} , {text:StrFormat(tt,menit2)});
					}
					$("#timezone").val(new Date().getTimezoneOffset()/60 * -1);
					$("#stparttime").dropdown({
						dropheight: 200,
						dropwidth:70,
						selectedchange: function() { },
						items: arrT
					});
					$("#etparttime").dropdown({
						dropheight: 200,
						dropwidth:70,
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
				<?php
					global $current_user;
					if($event){
						if($event->user_id != $current_user->ID){
							?>alert('Permission denied');CloseModelWindow(null,true);<?
						}
					}
				?>
			</script>
		</head>
	<body>
		<div id='fb-root'></div>
    	<script src='http://connect.facebook.net/en_US/all.js'></script>
  
<?php
	if($_POST){

		$start_date = dite_date_time_mysql($_POST['stpartdate'],dite_schedule_fulltime($_POST['stparttime']));
		$end_date = dite_date_time_mysql($_POST['etpartdate'],dite_schedule_fulltime($_POST['etparttime']));
		
		
		if($_REQUEST['method'] == 'add'){
			$sql = "select * from ".$wpdb->prefix."dite_appointment where 
					(`start_time` between '".$start_date."' and '".$end_date."') OR 
					(`end_time` between '".$start_date."' and '".$end_date."') OR
					(`start_time` >= '".$start_date."' AND `end_time` <='".$start_date."' ) OR
					(`start_time` >= '".$end_date."' AND `end_time` <='".$end_date."' )";
			$results = $wpdb->get_results($sql);
			//if(count($results) < 1){
				$data = array(
						'start_time'=> $start_date,
						'end_time' => $end_date,
						'post_content' => $_POST['Description'],
						'post_title' => $_POST['Subject'],
						'status' => $_POST['category'],
						'user_id' => $current_user->ID,
						'color' => 0,
						'location_name' => $_POST['location_name'],
						'cord_x' => $_POST['cord_x'],
						'cord_y' => $_POST['cord_y']
				);
				$new_post_id = $wpdb->insert( $wpdb->prefix.'dite_appointment', $data );
				
				echo '<div>Schedule added succesfully</div>';
				if($_POST['facebook'] == 'facebook') : 
				?>
					<script> 
						postToFeed();
				      	FB.init({appId: "148283008668420", status: true, cookie: true});

				      	function postToFeed() {

					        // calling the API ...
					        var obj = {
					          method: 'feed',
					          app_id : '148283008668420',
					          redirect_uri: '<?php echo site_url() ?>/art-calendar/',
					          link: '<?php echo site_url() ?>/event-detail/?id='+'<?php echo $new_post_id ?>',
					          picture: '<?php echo site_url()?>/wp-content/themes/divartist/images/facebook-icon-black.png',
					          name: 'Ultimate Artist Gallery Event',
					          caption: '<?php echo $_POST[post_title] ?>',
					          description: '<?php echo $_POST[post_content]?>'
					        };

					        function callback(response) {
					          document.getElementById('msg').innerHTML = "Post ID: " + response['post_id'];
					        }

					        FB.ui(obj, callback);
				      }
				    
				    </script>
				<?php
				endif;
			//}
				/*
			else{
				echo '<div>Failed, Schedule Conflict</div>';
			} */
		}
		else if($_REQUEST['method'] == 'edit'){
			$sql = "select * from ".$wpdb->prefix."dite_appointment where `post_id` = " . $_POST['id'];
			$datas = $wpdb->get_row($wpdb->prepare($sql));
			
			$d1 = new DateTime($start_date);
			$d2 = new DateTime($end_date);

			$e1 = new DateTime($datas->start_time);
			$e2 = new DateTime($datas->end_time);
			
			$sql = "select * from ".$wpdb->prefix."dite_appointment_meta where `post_id` = " . $_POST['id'];
			$events = $wpdb->get_results($wpdb->prepare($sql));
				/** Rule 1
				 * 1. Jika tanggal dan waktunya sama, tinggal ditiban saja
				 *
				 */
			$data = array(
					'start_time'=> $start_date,
					'end_time' => $end_date,
					'post_content' => $_POST['Description'],
					'post_title' => $_POST['Subject'],
					'status' => $_POST['category'],
					'color' => 0,
					'location_name' => $_POST['location_name'],
					'cord_x' => $_POST['cord_x'],
					'cord_y' => $_POST['cord_y']
			);
			$where = array('post_id'=>$_POST['id']);
			$wpdb->update( $wpdb->prefix.'dite_appointment', $data ,$where);
				
			echo '<div>Schedule updated succesfully</div>';
			/*
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
			*/

				if($_POST['facebook'] == 'facebook') : 
				?>
					<script> 
						FB.init({appId: "148283008668420", status: true, cookie: true});
						postToFeed();
				      	

				      	function postToFeed() {

					        // calling the API ...
					        var obj = {
					          method: 'feed',
					          redirect_uri: '<?php echo site_url() ?>/art-calendar/',
					          link: '<?php echo site_url() ?>/event-detail/?id='+'<?php echo $new_post_id ?>',
					          picture: '<?php echo site_url()?>/wp-content/themes/divartist/images/facebook-icon-black.png',
					          name: 'Ultimate Artist Gallery Event',
					          caption: '<?php echo $_POST[Subject] ?>',
					          description: '<?php echo $_POST[Description]?>'
					        };

					        function callback(response) {
					          document.getElementById('msg').innerHTML = "Post ID: " + response['post_id'];
					        }

					        FB.ui(obj, callback);
				      }
				    
				    </script>
				<?php
				endif;
		}	
		else if($_REQUEST['method'] == 'remove'){
			try{
				$data = array(
						'post_content' => '',
						'post_title' => 'Free Time',
						'status' => 'free',
						'color' => 3
					);
				$where = array('post_id'=>$_GET['id']);
				$wpdb->update( $wpdb->prefix.'dite_appointment', $data ,$where);
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

		<div>      
			<div class="toolBotton">           
				<a id="Savebtn" class="imgbtn" href="javascript:void(0);">                
					<span class="Save"  title="Save the calendar">Save(<u>S</u>)</span>          
				</a>                           
				<?php if(isset($event)){ ?>
				<a id="Deletebtn" class="imgbtn" href="javascript:void(0);">                    
					<span class="Delete" title="Cancel the calendar">Delete(<u>D</u>)</span>                
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

					<label>
						<input type="checkbox" name="facebook" value="facebook" />Post to facebook
					</label>         
					<label><span>*Subject:</span>                    
						<div id="calendarcolor">
						</div>
						<input MaxLength="200" class="required safe" id="Subject" name="Subject" style="width:85%;" type="text" value="<?php echo $event->post_title ?>" />                     
						<input id="colorvalue" name="colorvalue" type="hidden" value="" />                
					</label>
					<label>                    
						<span>Category:</span>                    
						<select name="category">
							<option value="Events">Events</option>
							<option value="Exhibitions">Exhibitions</option>
							<option value="Openings">Openings</option>
						</select>       
					</label>      
					<label>                    
						<span>*Time:</span>  
						<?php if(isset($event)){
							$sarr = explode(" ", dite_schedule_php2jstime(dite_schedule_mysql2phptime($event->start_time)));
							$earr = explode(" ", dite_schedule_php2jstime(dite_schedule_mysql2phptime($event->end_time)));
							
							$st = dite_schedule_halftime($event->start_time);
							$en = dite_schedule_halftime($event->end_time);
						}?>  						
						<div>  
							<input MaxLength="10" class="required date" id="stpartdate" name="stpartdate" style="padding-left:2px;width:90px;" type="text" value="<?php echo isset($event)?$sarr[0]:""; ?>" />                       
							<input MaxLength="5" class="required time" id="stparttime" name="stparttime" style="width:60px;" type="text" value="<?php echo isset($event)?$st:""; ?>" />To                       
							<input MaxLength="10" class="required date" id="etpartdate" name="etpartdate" style="padding-left:2px;width:90px;" type="text" value="<?php echo isset($event)?$earr[0]:""; ?>" />                       
							<input MaxLength="50" class="required time" id="etparttime" name="etparttime" style="width:60px;" type="text" value="<?php echo isset($event)?$en:""; ?>" />                                            
							<label style="display:none;" class="checkp"> 
								<input id="IsAllDayEvent" name="IsAllDayEvent" type="checkbox" value="<?php echo $event->whole_day ?>" />          Whole Day                       
							</label>                    
						</div>                
					</label>                 
					<label>                    
						<span>Description:</span>                    
						<textarea cols="20" id="Description" name="Description" rows="2" style="width:95%; height:70px"><?php echo $event->post_content ?>
						</textarea>                
					</label>
					<label>
						<span>Location</span>
					</label>
					<label>                    
						<span>Location Name:</span>
						<input style="width:85%;"  name="location_name" value="<?php echo $event->location_name ?>" type="text" />
					</label>
					<label>                    
						<span>Cord X:</span>                    
						<input name="cord_x" value="<?php echo $event->cord_x ?>" type="text" />
					</label>
					<label>                    
						<span>Cord Y:</span>                    
						<input name="cord_y" value="<?php echo $event->cord_y ?>" type="text" />
					</label>
					<input id="id" name="id" type="hidden" value="<?php echo $event->post_id?>" />           
				</form>         
			</div>         
		</div>
		</body>
	</html>