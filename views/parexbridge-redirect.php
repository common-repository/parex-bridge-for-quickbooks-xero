<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
$maskEmail = getUserDataPXB()['user_email'];
$mailData = explode('@',$maskEmail);
$dotexplode = explode('.',$mailData[1]); 
$maskEmail = substr($mailData[0], 0, 3).'****'.'@'.substr($mailData[1], 0, 3).'****'.'.'.end($dotexplode);
if (isset($_REQUEST['submit'])) {
    do_action('wp_ajax_pxb_action', $_REQUEST);
}else if(isset($_REQUEST['resend'])){
  do_action('wp_ajax_pxb_sendEmail');
}
?>

<div class="pxb-canvas">
<center>
  <div>
    <img src="<?php echo plugin_dir_url(__DIR__) . 'assets/img/parexlogo.png'; ?>" alt="Parex-Bridge Logo">
    <h3>Parex Bridge for QuickBook - Xero </h3>
  </div>

<?php
$results = getPXBDataFromServer();

if (!empty($results['guid']) && !empty($results['userId']) && !empty($results['module'])) {
    ?>
    <!-- <div class="container">-->
    <div class="container"> 
      <div class="text-center" style="margin: 0px;">
        <div class="box box-solid">
          <div class="box-body">
            <?php 
            $module = '';
            if(strtolower($results['module']) == 'quickbooks'){ $module = 'QuickBooks'; ?>
              <div style="margin:0px 0px 0px 0px;">
                <img class="img-connect" src="<?php echo plugin_dir_url(__DIR__) . 'assets/img/wc-qb-connection.png'; ?>" alt="ParexBridge Logo" />
              </div>
            <?php }else if(strtolower($results['module']) == 'xero'){ $module = 'Xero'; ?>
              <div style="margin:0px 0px 0px 0px;">
                <img class="img-connect" src="<?php echo plugin_dir_url(__DIR__) . 'assets/img/wc-xo-connection.png'; ?>" alt="ParexBridge Logo" />
              </div>
            <?php } ?>
            <div>
              <h5 style="margin:5px;">Sync your WooCommerce sales data with your <?php echo $module; ?> Online account automatically.</h5>
            </div>
            <div style="margin-top:20px;">
              <button class="btn btn-primary btn-lg" onclick="window.open('<?php echo 'https://apps.parextech.com/user/auth/' . md5($results['guid']) . '@@' . $results['userId']; ?>','_blank')"> Login to Parex Bridge </button>
            </div>
          </div>
        </div>
      </div>
     </div>
 <!-- </div> -->
  
  <?php } else if (!empty($results['guid']) && !empty($results['userId'])) {?>
  
  <div class="container">
  
    <?php if (isset($_REQUEST['module']) && !empty($_REQUEST['module'])) { 
      $module = sanitize_text_field( $_REQUEST['module'] );
      ?>
      <div class="row" style="display:inherit;">
      <div class="text-center">
        <div class="box box-solid">
          <div class="box-body">
            <form action="" method="post">
            <div>
              <h6 style="margin:5px;">You will receive the authorization code at your email address registered with us. Please check your email <b><?php echo $maskEmail; ?></b> for the code and enter it below.</h6>
              <div style="margin-top:20px;">
                <input type="hidden" id="code" name="code" maxlength="6" pattern="[0-9]{6}" style="text-align: center;font-size: x-large;letter-spacing: 15px;"></input>
                <div class="">
                  <div class="">
                    <div class="mm-number-input">
                      <div class="mm-number-input-container animated">
                        <div class="mm-number-input-item">
                          <input type="number" pattern="\d*" class="animated" maxlength="1" placeholder="X">
                        </div>
                        <div class="mm-number-input-item">
                          <input type="number" pattern="\d*" class="animated" maxlength="1" placeholder="X">
                        </div>
                        <div class="mm-number-input-item">
                          <input type="number" pattern="\d*" class="animated" maxlength="1" placeholder="X">
                        </div>
                        <div class="mm-number-input-item">
                          <input type="number" pattern="\d*" class="animated" maxlength="1" placeholder="X">
                        </div>
                        <div class="mm-number-input-item">
                          <input type="number" pattern="\d*" class="animated" maxlength="1" placeholder="X">
                        </div>
                        <div class="mm-number-input-item">
                          <input type="number" pattern="\d*" class="animated" maxlength="1" placeholder="X">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <input type="hidden" name="module" value="<?php echo $module; ?>"></input>
              </div>
              
            </div>
            <div style="margin-top:20px;">
              <button class="btn btn-primary btn-md" name="submit"> Verify </button>
              <button class="btn btn-link btn-md" name="resend"> Resend Link </button>
              <?php if(get_option('pxb_verificaitonEmail_status') == '1') { ?>
                <span id="email-notify"><h6>Email sent successfully.</h6></span>
              <?php } ?>
            </div>
          </form>
          </div>
        </div>
      </div>
    <?php } else { ?>
    <div class="row">
    <form action="" method="post">
      <div class="col col-5 float-left text-center">
        <div class="box box-solid">
          <div class="box-body">
            <h4 class="integration-title">Integration with QuickBooks</h4>
            <div style="margin:20px 0px 0px 0px;">
              <img src="
                <?php echo plugin_dir_url(__DIR__) . 'assets/img/wcqb.png'; ?>" alt="wcqb" />
              </div>
              <div class="description">
                <p>
                  Sync your WooCommerce sales data with your QuickBooks Online account automatically.<br/>

                  Our app will sync your Customers, Products, and Orders from WooCommerce to QuickBooks Online. You can choose to do it automatically or initiate it yourself.
                </p>
              </div>
              <div class="action">
                <button class="btn btn-success btn-lg" name="module" value="Quickbooks"> Connect </button>
              </div>
            </div>
          </div>
        </div>
        <!-- <div class="col-2 text-center"></div> -->
        <div class="col col-5 float-right text-center" >
          <div class="box box-solid">
            <div class="box-body">
              <h4 class="integration-title">Integration with Xero</h4>
              <div style="margin:20px 0px 0px 0px;">
                <img src="
                  <?php echo plugin_dir_url(__DIR__) . 'assets/img/wcxo.png'; ?>" alt="wcxo" />
                </div>
                <div class="description">
                  <p>
                    Sync your WooCommerce sales data with your Xero Online account automatically.<br/>

                    Our app will sync your Customers, Products, and Orders from WooCommerce to Xero Online. You can choose to do it automatically or initiate it yourself.
                  </p>
                </div>
                <div class="action">
                  <button class="btn btn-info btn-lg" style="background-color: #20c0e7;" name="module" value="Xero"> Connect </button>
                </div>
              </div>
            </div>
          </div>
        </div>
    </form>
    <?php } ?>
    </div> <!-- raw div end -->
  </div> <!-- container div end -->

  <?php
} else {
    setErrorMessagePXB();
}

?>
</center>
</div> <!-- pxb-canvas class div end -->