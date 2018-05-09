var blockUIRunning = false;

function blockUIForExtJS(){
    if(!blockUIRunning) {
        jQuery.blockUI({
            timeout: 60000,
            message: jQuery('#loadingPage'),
            css: {
                cursor: 'wait',
                backgroundColor: '#000000',
                opacity: 0.60,
                border: '2px solid',
                paddingTop: '20px',
                paddingBottom: '20px'
            },
            onBlock: function () {
                blockUIRunning = true;
            },
            onUnblock: function () {
                blockUIRunning = false;
            }
        });
    }else{
        //setTimeout(blockUIForExtJS, 200);
    }
}

function cancelBlockUIForExtJS(){
    $.unblockUI();
    blockUIRunning = false;
}

function blockUI(){
    if(!blockUIRunning) {
        jQuery.blockUI({
            message: jQuery('#loadingPage'),
            css: {
                cursor: 'wait',
                backgroundColor: '#000000',
                opacity: 0.60,
                border: '2px solid',
                paddingTop: '20px',
                paddingBottom: '20px'
            },
            onBlock: function () {
                blockUIRunning = true;
            },
            onUnblock: function () {
                blockUIRunning = false;
            }
        });
    }else{
        setTimeout(blockUI, 200);
    }
}
function blockUITimeout(){
    jQuery.blockUI({
        timeout: 3000,
        message: $('#loadingPage'),
        css: {
            cursor: 'wait',
            backgroundColor: '#000000',
            opacity: 0.60,
            border: '2px solid',
            paddingTop: '20px',
            paddingBottom: '20px'
        }
    });
}
	
function closeChildWindows(){	
	jQuery("#dialog-affiliate-tree").dialog().dialog("close");
}
function cancelBlockUI(){
    $.unblockUI();
    blockUIRunning = false;
}
$(document).ready(function(){
	$("a:not(.noblockui)").click(blockUITimeout);
	$("input:button").click(blockUI);
	$("input:submit").click(blockUI);
	$("input:reset").click(blockUI);
	$(document).ajaxStart(blockUI).ajaxStop($.unblockUI);
	$("#limit").change(function(){
		$("#page").val("1")
	});

    //iframe resizing and current width/height
    if ($('#iframe_id').length) {
        $("#iframe_id").height($("#content_content").height());
        $("#iframe_id").width($("#content_content").width());
        $(window).resize(function () {
            //$("#iframe_id").height($("#content_content").height());
            $("#iframe_id").height("87vh");
            $("#iframe_id").width($("#content_content").width());
        });
    }
});