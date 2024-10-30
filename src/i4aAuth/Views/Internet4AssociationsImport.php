<?php
namespace i4aAuth\Views; 
use WP\Views\View; 
use WP\Views\Page; 
class Internet4AssociationsImport extends View {
	protected $fields = array(); 
	
	public function __construct() { 
		$i4aWpOption = get_option('internet4associations');
		
		if (!is_array($i4aWpOption) || ! isset($i4aWpOption['single_sign_on']['enable_membercontacttypes'])) {
			// either no settings have been configured for the plugin or they have not enabled the sync of i4a member and contact types
			$this->createImportPageDisabled();
		} else {
			// the have  enabled the sync of i4a member and contact types
			$this->createImportPage();
		}
		
		
	} 

	private function createImportPage() { 
		include_once Page::getTemplatesPath(__DIR__) . '/import.tpl'; 
		?>
		
		<script type="text/javascript">
			jQuery(document).ready(function($) {
			
				$('#submit').on('click', function(e) {
					$('#i4aImportResultMessage').hide();
					$(this).hide();
					$('#i4aImportLoadingMessage').show();
				});
			
			});
		</script>
		
		<style type="text/css">
			.i4aImportLoadingMessageStyle {
				display:none; 
				font-weight: bold; 
				color: #2271b1;
			}
			.i4aImportLoadingMessageStyle div {padding-top:20px;}
			
			.i4aImportSuccessMessage {
				background: #fff; 
				border: 1px solid #c3c4c7; 
				border-left-width: 4px; 
				box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04); 
				padding: 1px 12px; 
				margin: 5px 0 15px; 
				border-left-color: #00a32a;
			}
			
			.i4aImportErrorMessage {
				background: #fff; 
				border: 1px solid #d63638; 
				border-left-width: 4px; 
				box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04); 
				padding: 1px 12px; 
				margin: 5px 0 15px; 
				border-left-color: #d63638;
			}
			
			ul.i4aImportResults {
				list-style: 'square' !important;
			}
		</style>
		
		<?php
		
		
		
		// this is a WordPress security feature - see: https://codex.wordpress.org/WordPress_Nonces
		wp_nonce_field('i4a_import_button_clicked');
		echo '<input type="hidden" value="true" name="i4a_import_button" />';
		echo '<div id="i4aImportLoadingMessage" class="i4aImportLoadingMessageStyle"><div>Please wait... Importing...<br>This make take a minute or two...</div></div>';
		submit_button('Run Import Now');
		echo '<p>&nbsp;</p><p>&nbsp;</p>';
		
		// Check whether the button has been pressed AND also check the nonce
		if (isset($_POST['i4a_import_button']) && check_admin_referer('i4a_import_button_clicked')) {
			// the button has been pressed AND we've passed the security check
			i4a_import_roles_action();
		}
	} 
	
	private function createImportPageDisabled() {
		include_once Page::getTemplatesPath(__DIR__) . '/importDisabled.tpl';	
	}
	
}