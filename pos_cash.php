<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;
jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.filesystem.file');
jimport( 'joomla.html.parameter' );

JLoader::registerPrefix('Phocacart', JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart');

class plgPCPPos_Cash extends JPlugin
{
	function __construct(& $subject, $config) {
		parent :: __construct($subject, $config);
	}

	/**
	 * Proceed to payment - some method do not have proceed to payment gateway like e.g. cash on delivery
	 *
	 * @param   integer	$proceed  Proceed or not proceed to payment gateway
	 * @param   string	$message  Custom message array set by plugin to override standard messages made by component
	 *
	 * @return  boolean  True
	 */

	function PCPonDisplayPaymentPos(&$output, $t) {

		$total 			= $t['total'];
		$autocomplete 	= $t['pos_input_autocomplete_output'];
		$price = new PhocacartPrice();

		$s 	= array();
		$s[] = 'function phChangeAmountTendered(typeValue) {';
		$s[] = '	var phTotalAmount = jQuery("#phTotalAmount").val();';
		$s[] = '	var phChange = Math.round((typeValue - phTotalAmount)*100)/100;';
		$s[] = '	phChange = phGetPriceFormat(phChange);';
		$s[] = '	jQuery("#phAmountChange").text(phChange);';
		$s[] = '}';
		$s[] = ' ';
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '	var phChAT = null;';
	    $s[] = '	jQuery(document).on("keyup", "#phAmountTendered", function() {';
	    $s[] = '		clearTimeout(phChAT);';
	    $s[] = '		var $this = jQuery(this); phChAT = setTimeout(function(){phChangeAmountTendered($this.val())}, 500);';
	    $s[] = '	});';
		$s[] = '})';

	// 	Loaded BY AJAX, needs to be set inside body
	//	JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));

		$totalAmount = 0;
		if ($total[0]['brutto_currency'] !== 0) {
			$totalAmount = $total[0]['brutto_currency'];
		} else if ($total[0]['brutto'] !== 0) {
			$totalAmount = $total[0]['brutto'];
		}

		$o = array();

		$o[] = '<div class="row row-vac">';

		$o[] = '<div class="row-item col-sm-3 col-md-3">';
		$o[] = '</div>';


		$o[] = '<div class="row-item col-sm-3 col-md-3">';
		$o[] = '<div class="ph-pos-payment-item-txt">' . JText::_('COM_PHOCACART_AMOUNT_TENDERED').'</div>';
		$o[] = '</div>';

		$o[] = '<div class="row-item col-sm-3 col-md-3">';
		$o[] = '<div class="ph-right">';

		$o[] = '<input type="hidden" value="'.$totalAmount.'" name="phTotalAmount" id="phTotalAmount" />';
		$o[] = '<input type="number" class="ph-pos-amount-tendered" value="" name="phAmountTendered" id="phAmountTendered" '.$autocomplete.' />';
		$o[] = '</div>';
		$o[] = '</div>';


		$o[] = '<div class="row-item col-sm-3 col-md-3">';
		$o[] = '</div>';

		$o[] = '</div>'; // end row


		$o[] = '<div class="row row-vac">';

		$o[] = '<div class="row-item col-sm-3 col-md-3">';
		$o[] = '</div>';


		$o[] = '<div class="row-item col-sm-3 col-md-3">';
		$o[] = '<div class="ph-pos-payment-item-txt">' . JText::_('COM_PHOCACART_CHANGE') . '</div>';
		$o[] = '</div>';

		$o[] = '<div class="row-item col-sm-3 col-md-3">';
		$o[] = '<div class="ph-pos-amount-change ph-right" id="phAmountChange">'.$price->getPriceFormat(0).'</div>';
		$o[] = '</div>';


		$o[] = '<div class="row-item col-sm-3 col-md-3">';
		$o[] = '</div>';

		$o[] = '</div>'; // end row


		// Inline Javascript becasuse of combination of Plugin and AJAX
		$o[] = '<script type="text/javascript">'.implode("\n", $s).'</script>';

		$output = implode("\n", $o);
	}


	function PCPbeforeSaveOrder(&$statusId, $pid) {


        // Status set by payment method in case of order (pending, confirmed, completed)
        // In case of POS cash, it is always completed
        $paymentTemp		= new PhocacartPayment();
        $paymentOTemp 		= $paymentTemp->getPaymentMethod((int)$pid );
        $paramsPaymentTemp	= $paymentOTemp->params;
        $statusId		    = $paramsPaymentTemp->get('default_order_status', 6);

		//$statusId = 6;

		return true;
	}

}
?>
