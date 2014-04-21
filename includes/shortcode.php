<?php


add_shortcode('dite_schedule', 'dite_schedule_shortcode');
/*
 * @function project_page_shortcode
 * @desc	 Show project details in page/post
 * @param	 integer post_id
 * @return 	 none
 */

function dite_schedule_shortcode($atts) { 
	global $wpdb;
	if(!is_user_logged_in()){
		//echo "<p>You must <a href='".get_option('siteurl')."/wp-login.php'>login</a> to view this content , click <a href='".get_option('siteurl')."/wp-login.php'>here</a> for login</p>";
		//exit;
	}
	?>
	<html><head>
	<?php
        wp_deregister_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker' , dite_schedule_pluginurl('js') . '/jquery.datepicker.js');
        wp_enqueue_style('dailog.css', dite_schedule_pluginurl('css') . '/dailog.css');
        wp_enqueue_style('calendar-frontend.css', dite_schedule_pluginurl('css') . '/calendar-frontend.css');
        wp_enqueue_style('dp.css', dite_schedule_pluginurl('css') . '/dp.css');
        wp_enqueue_style('dropdown.css', dite_schedule_pluginurl('css') . '/dropdown.css');
        wp_enqueue_style('main-frontend.css', dite_schedule_pluginurl('css') . '/main-frontend.css');
				
        wp_enqueue_script('Common.js',  dite_schedule_pluginurl('js') . '/Common.js');
        wp_enqueue_script('datepicker_lang_US.js',  dite_schedule_pluginurl('js') . '/datepicker_lang_US.js');
        wp_enqueue_script('jquery.alert.js',  dite_schedule_pluginurl('js') . '/jquery.alert.js');
        wp_enqueue_script('jquery.ifrmdailog_frontend.js',  dite_schedule_pluginurl('js') . '/jquery.ifrmdailog_frontend.js');
        wp_enqueue_script('wdCalendar_lang_US.js',  dite_schedule_pluginurl('js') . '/wdCalendar_lang_US.js');
        wp_enqueue_script('jquery.calendar_frontend.js',  dite_schedule_pluginurl('js') . '/jquery.calendar_frontend.js');
        wp_enqueue_script('jquery.dropdown.js',  dite_schedule_pluginurl('js') . '/jquery.dropdown.js');
        wp_head(); 
	?>
	</head>
	<body>
	<?php dite_schedule_shortcode_script();?>
	<?php dite_schedule_shortcode_body(); ?>
	</body>
	</html>
	<?php
}

function dite_schedule_shortcode_script(){
	?>
	<script type="text/javascript">
        jQuery(document).ready(function($) {     
			var view="week";          
			var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
            var DATA_FEED_URL = ajaxurl+"?action=dite_show_calendar";
			//var DATA_FEED_URL = "http://localhost/example/jquery/calendar/wdCalendar/php/datafeed.php";
            var op = {
                view: view,
                theme:3,
                showday: new Date(),
                EditCmdhandler:Edit,
                DeleteCmdhandler:Delete,
                ViewCmdhandler:View,    
                onWeekOrMonthToDay:wtd,
                onBeforeRequestData: cal_beforerequest,
                onAfterRequestData: cal_afterrequest,
                onRequestDataError: cal_onerror, 
                autoload:true,
                url: DATA_FEED_URL + "&method=list",  
                quickAddUrl: DATA_FEED_URL + "&method=add", 
                quickUpdateUrl: DATA_FEED_URL + "&method=update",
                quickDeleteUrl: DATA_FEED_URL + "&method=remove"        
            };
            var $dv = $("#calhead");
            var _MH = document.documentElement.clientHeight;
            var dvH = $dv.height() + 2;
            op.height = _MH - dvH;
            op.eventItems =[];

            var p = $("#gridcontainer").bcalendar(op).BcalGetOp();
            if (p && p.datestrshow) {
                $("#txtdatetimeshow").text(p.datestrshow);
            }
            $("#caltoolbar").noSelect();
            
            $("#hdtxtshow").datepicker({ picker: "#txtdatetimeshow", showtarget: $("#txtdatetimeshow"),
            onReturn:function(r){                          
                            var p = $("#gridcontainer").gotoDate(r).BcalGetOp();
                            if (p && p.datestrshow) {
                                $("#txtdatetimeshow").text(p.datestrshow);
                            }
                     } 
            });
            function cal_beforerequest(type)
            {
                var t="Loading data...";
                switch(type)
                {
                    case 1:
                        t="Loading data...";
                        break;
                    case 2:                      
                    case 3:  
                    case 4:    
                        t="The request is being processed ...";                                   
                        break;
                }
                $("#errorpannel").hide();
                $("#loadingpannel").html(t).show();    
            }
            function cal_afterrequest(type)
            {
                switch(type)
                {
                    case 1:
                        $("#loadingpannel").hide();
                        break;
                    case 2:
                    case 3:
                    case 4:
                        $("#loadingpannel").html("Success!");
                        window.setTimeout(function(){ $("#loadingpannel").hide();},2000);
                    break;
                }              
               
            }
            function cal_onerror(type,data)
            {
                $("#errorpannel").show();
            }
            function Edit(data)
            {
				//var eurl="http://localhost/example/jquery/calendar/wdCalendar/edit.php?id={0}&start={2}&end={3}&isallday={4}&title={1}";   
				var eurl = ajaxurl+"?action=dite_action_shortcode&method=edit&id={0}&start={2}&end={3}&isallday={4}&title={1}";
                if(data)
                {
                    var url = StrFormat(eurl,data);
                    OpenModelWindow(url,{ width: 600, height: 400, caption:"Manage  The Calendar",onclose:function(){
                       $("#gridcontainer").reload();
                    }});
                }
            }
            function View(data)
            {
                var str = "";
                $.each(data, function(i, item){
                    str += "[" + i + "]: " + item + "\n";
                });
                //alert(str);               
            }    
            function Delete(data,callback)
            {           
                
                $.alerts.okButton="Ok";  
                $.alerts.cancelButton="Cancel";  
                hiConfirm("Are You Sure to Delete this Event", 'Confirm',function(r){ r && callback(0);});           
            }
            function wtd(p)
            {
               if (p && p.datestrshow) {
                    $("#txtdatetimeshow").text(p.datestrshow);
                }
                $("#caltoolbar div.fcurrent").each(function() {
                    $(this).removeClass("fcurrent");
                })
                $("#showdaybtn").addClass("fcurrent");
            }
            //to show day view
            $("#showdaybtn").click(function(e) {
                //document.location.href="#day";
                $("#caltoolbar div.fcurrent").each(function() {
                    $(this).removeClass("fcurrent");
                })
                $(this).addClass("fcurrent");
                var p = $("#gridcontainer").swtichView("day").BcalGetOp();
                if (p && p.datestrshow) {
                    $("#txtdatetimeshow").text(p.datestrshow);
                }
            });
            //to show week view
            $("#showweekbtn").click(function(e) {
                //document.location.href="#week";
                $("#caltoolbar div.fcurrent").each(function() {
                    $(this).removeClass("fcurrent");
                })
                $(this).addClass("fcurrent");
                var p = $("#gridcontainer").swtichView("week").BcalGetOp();
                if (p && p.datestrshow) {
                    $("#txtdatetimeshow").text(p.datestrshow);
                }

            });
            //to show month view
            $("#showmonthbtn").click(function(e) {
                //document.location.href="#month";
                $("#caltoolbar div.fcurrent").each(function() {
                    $(this).removeClass("fcurrent");
                })
                $(this).addClass("fcurrent");
                var p = $("#gridcontainer").swtichView("month").BcalGetOp();
                if (p && p.datestrshow) {
                    $("#txtdatetimeshow").text(p.datestrshow);
                }
            });
            
            $("#showreflashbtn").click(function(e){
                $("#gridcontainer").reload();
            });
            
            //Add a new event
            $("#faddbtn").click(function(e) {
                var url = ajaxurl+"?action=dite_action_shortcode&method=add";
                OpenModelWindow(url,{ width: 500, height: 400, caption: "Create New Calendar Listing"});
            });
            //go to today
            $("#showtodaybtn").click(function(e) {
                var p = $("#gridcontainer").gotoDate().BcalGetOp();
                if (p && p.datestrshow) {
                    $("#txtdatetimeshow").text(p.datestrshow);
                }


            });
            //previous date range
            $("#sfprevbtn").click(function(e) {
                var p = $("#gridcontainer").previousRange().BcalGetOp();
                if (p && p.datestrshow) {
                    $("#txtdatetimeshow").text(p.datestrshow);
                }

            });
            //next date range
            $("#sfnextbtn").click(function(e) {
                var p = $("#gridcontainer").nextRange().BcalGetOp();
                if (p && p.datestrshow) {
                    $("#txtdatetimeshow").text(p.datestrshow);
                }
            });
            
        });
    </script>
	<style>
		
	</style>
	<?php
}

function dite_schedule_shortcode_body(){
	?>
	<div class="calwrap">
		<div id="calhead" style="margin-top:10px;padding-left:1px;padding-right:1px;">          
			<div class="cHead">
				<div class="ftitle">Art Event Calendar</div>
				<div id="loadingpannel" class="ptogtitle loadicon" style="display: none;">Loading data...</div>
				<div id="errorpannel" class="ptogtitle loaderror" style="display: none;">Sorry, could not load your data, please try again later</div>
            </div>          
            
            <div id="caltoolbar" class="ctoolbar">
				<div id="faddfreetime" style="display:none;" class="fbutton">
					<div><span title='Click to Create New Free Time' class="addcal">New Free Time</span></div>
				</div>
				<div id="faddbtn" class="fbutton">
					<div><span title='Click to Create New Event' class="addcal">New Event</span></div>
				</div>
				<div class="btnseparator"></div>
				<div id="showtodaybtn" class="fbutton">
					<div><span title='Click to back to today' class="showtoday">Today</span></div>
				</div>
				<div class="btnseparator"></div>
				<div id="showdaybtn" class="fbutton">
					<div><span title='Day' class="showdayview">Day</span></div>
				</div>
				<div id="showweekbtn" class="fbutton fcurrent">
					<div><span title='Week' class="showweekview">Week</span></div>
				</div>
				<div id="showmonthbtn" class="fbutton">
					<div><span title='Month' class="showmonthview">Month</span></div>
				</div>
				<div class="btnseparator"></div>
				<div  id="showreflashbtn" class="fbutton">
					<div><span title='Refresh view' class="showdayflash">Refresh</span></div>
                </div>
				<div class="btnseparator"></div>
				<div id="sfprevbtn" title="Prev"  class="fbutton">
					<span class="fprev"></span>
				</div>
				<div id="sfnextbtn" title="Next" class="fbutton">
					<span class="fnext"></span>
				</div>
				<div class="fshowdatep fbutton">
                    <div>
                        <input type="hidden" name="txtshow" id="hdtxtshow" />
                        <span id="txtdatetimeshow">Loading</span>
                    </div>
				</div>
            
            <div class="clear"></div>
            </div>
		</div>
		<div style="padding:1px;">
			<div class="t1 chromeColor">&nbsp;</div>
			<div class="t2 chromeColor">&nbsp;</div>
			<div id="dvCalMain" class="calmain printborder">
				<div id="gridcontainer" style="overflow-y: visible;">
				</div>
			</div>
			<div class="t2 chromeColor">&nbsp;</div>
			<div class="t1 chromeColor">&nbsp;</div>   
        </div>
	</div>
<?php
}
?>