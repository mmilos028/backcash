var blockUIRunning = false;

function blockUIForExtJS(){
    if(!blockUIRunning) {
        jQuery.blockUI({
            timeout: 60000,
            message: jQuery('#loadingPage'),
            css: {
                cursor: 'wait',
                backgroundColor: '#000000',
                color: 'white',
                opacity: 0.60,
                border: '1px solid',
                borderColor: 'black',
                paddingTop: '20px',
                paddingBottom: '20px',
                paddingLeft: '5px',
                paddingRight: '5px',
                width: '100%'
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
                color: 'white',
                opacity: 0.60,
                border: '1px solid',
                borderColor: 'black',
                paddingTop: '20px',
                paddingBottom: '20px',
                paddingLeft: '5px',
                paddingRight: '5px',
                width: '100%'
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
        timeout: 5000,
        message: $('#loadingPage'),
        css: {
            cursor: 'wait',
            backgroundColor: '#000000',
            color: 'white',
            opacity: 0.60,
            border: '1px solid',
            borderColor: 'black',
            paddingTop: '20px',
            paddingBottom: '20px',
            paddingLeft: '5px',
            paddingRight: '5px',
            width: '100%'
        }
    });
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
	$("#LIMIT").change(function(){
		$("#PAGE").val("1")
	});
});