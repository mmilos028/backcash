<?php
$config = Zend_Registry::get('config');
$translate = Zend_Registry::get("translate");
?>
<script type="text/javascript">
    var flagOpenSessionTimeoutDialogBox = false;
    var countSessionExpireTimeout;
    function openSessionTimeoutDialogBox(session_timeout_time_left, remaining_seconds){
        flagOpenSessionTimeoutDialogBox = true;
        countSessionExpireTimeout = setTimeout(function(){
            window.location = "<?php echo $this->url(array('controller'=>'auth', 'action'=>'terminate'));?>";
        }, session_timeout_time_left * 1000);

        var countdownElement = $("#dialog-session-expire-time-remaining");
        var second = session_timeout_time_left;
        var intervalCountdown = setInterval(function() {
            if (second == 0) {
                clearInterval(intervalCountdown);
            }
            second--;
            countdownElement.text('Time remaining ' + second  + ' seconds.');
        }, 1000);

        $('#dialog-session-expire').modal(
            {
                keyboard: false,
                backdrop: false
            }
        );
    }
    function ajaxPing(){
        if(!flagOpenSessionTimeoutDialogBox) {
            $.ajax(
                {
                    type: "POST",
                    data: "ping=1",
                    dataType: "json",
                    global: false,
                    url: "<?php echo $this->url(array('controller'=>'session-validation', 'action'=>'ping-session'));?>",
                    async: true,
                    success: function (data) {
                        if(!data.valid_session){
                           window.location = "<?php echo $this->url(array('controller'=>'auth', 'action'=>'terminate'));?>";
                        }else{
                            if(data.show_dialog) {
                                openSessionTimeoutDialogBox(data.session_timeout_time, data.remaining_seconds);
                            }
                        }

                    },
                    error: function (data) {
                        window.location = "<?php echo $this->url(array('controller'=>'auth', 'action'=>'terminate'));?>";
                    }
                }
            );
        }
    }
    setInterval("ajaxPing()", <?php echo $config->sessionTimeout; ?>);
    $('#dialog-session-expire').modal(
            {
                keyboard: false,
                show: false,
                backdrop: false
            }
        );

    function extendSession(){
        $.ajax(
            {
                type: "POST",
                dataType: "json",
                global: false,
                url: "<?php echo $this->url(array('controller'=>'session-validation', 'action'=>'extend-backoffice-session'));?>",
                async: true,
                success: function (data) {
                    if(data.status != "<?php echo OK; ?>"){
                       window.location = "<?php echo $this->url(array('controller'=>'auth', 'action'=>'terminate'));?>";
                    }else{
                        flagOpenSessionTimeoutDialogBox = false;
                        window.clearTimeout(countSessionExpireTimeout);
                        $("#dialog-session-expire").modal("hide");
                    }
                },
                error: function (data) {
                    window.location = "<?php echo $this->url(array('controller'=>'auth', 'action'=>'terminate'));?>";
                }
            }
        );
    }

    function logoutSessionTimeout(){
        window.location = "<?php echo $this->url(array('controller'=>'auth', 'action'=>'terminate'));?>";
    }
</script>

<div class="modal fade" tabindex="-1" role="dialog" id="dialog-session-expire">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><?php echo $translate->_("Session is about to expire"); ?></h4>
      </div>
      <div class="modal-body">
        <p>
            <span class="ui-icon ui-icon-alert" style="float:left; text-align: left !important; margin:12px 12px 20px 0;"></span>
            <?php echo $translate->_("Your session is about to expire. Would you like to extend your session ?"); ?>
            <span style="font-weight: bold; color: red;" id="dialog-session-expire-time-remaining"> </span>
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="extendSession()"><?php echo $translate->_("Extend session"); ?></button>
        <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="logoutSessionTimeout()"><?php echo $translate->_("Logout"); ?></button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->