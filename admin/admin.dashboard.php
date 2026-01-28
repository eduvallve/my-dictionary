<?php
require_once "admin.functions.php";

function mydictionary_admin_page(){
  // check user capabilities
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  // Get the active tab from the $_GET param
  $default_tab = null;
  $tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

  ?>
  <!-- Our admin page content should all be inside .wrap -->
  <div class="wrap">
    <!-- Print the page title -->
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <!-- Here are our tabs -->
    <nav class="nav-tab-wrapper">
      <a href="?page=my-dictionary" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">General</a>
      <a href="?page=my-dictionary&tab=translate" class="nav-tab <?php if($tab==='translate'):?>nav-tab-active<?php endif; ?>">Translate</a>
      <a href="?page=my-dictionary&tab=settings" class="nav-tab <?php if($tab==='settings'):?>nav-tab-active<?php endif; ?>">Settings</a>
    </nav>

    <div class="tab-content">
    <?php switch($tab) :
      case 'settings':
        require_once 'tabs/settings/settings.php';
        break;
      case 'translate':
        require_once 'tabs/translate/translate.php';
        break;
      default:
        require_once 'tabs/general/general.php';
        break;
    endswitch; ?>
    </div>
  </div>
  <?php
}
?>