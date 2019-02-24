<?php
/**
 * @package    filterMultiple
 *
 * @author     ofry <tim4job@bmail.ru>
 * @copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://github.com/ofry
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\CMSPlugin;


defined('_JEXEC') or die;

/**
 * filterMultiple plugin.
 *
 * @package  filterMultiple
 * @since    1.0
 */
class plgJshoppingorderFilterMultiple extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  1.0
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  1.0
	 */
	protected $db;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $autoloadLanguage = true;

	public function onBeforeShowOrderListView(JshoppingViewOrders $view)
	{
		if (property_exists($view, 'lists')) {
			if (isset($view->lists['changestatus'])) {
				libxml_use_internal_errors(true);
				$dom = new DOMDocument();
				$dom->loadHTML($view->lists['changestatus'], LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
				$select = simplexml_import_dom($dom);

				/*
				 * Добавляем возможность множественного выбора
				 */
				if (!isset($select->multiple)) {
					$select->addAttribute('multiple', 'multiple');
					$select_dom = dom_import_simplexml($select);
					$select_origname = $select_dom->getAttribute('name');
					$select_newname = $select_origname . '_multi';
					$select_dom->setAttribute('name',
						$select_newname . '[]');
					$select_dom->setAttribute('id', $select_newname);
					/*
					 * Создаем скрытое поле
					 */
					$select_status = isset($view->filter['status_id']) ?
						$view->filter['status_id'] : '';
					$hidden_input = '<input type="hidden" name="' . $select_origname . '"
					 id="' . $select_origname . '" value="' . $select_status . '" />';
					$view->lists['changestatus'] = $select_dom->ownerDocument->saveHTML()
						. $hidden_input;
					/*
					 * Добавляем скрипт для обработки множественного выбора
					 */
					$view->_tmp_order_list_html_end = '
	<script type="text/javascript">
		(function($){
		    function contains(arr, elem) {
    			return arr.indexOf(elem) !== -1;
			}
		    var origName = "'. $select_origname . '";
		    var newName = origName + "_multi";
		    
		    var defaultValuesString = $("#" + origName)
		    						.val()
		    						.trim();
		    var defaultValues = (defaultValuesString.length > 0) ?
		    						defaultValuesString
		    						.split(";")
		    						.map(function (value, index, array) {
		        						return parseInt(value);
		      						})
		      						: [];
		   
	        $("#" + newName).find("option").each(function(index, element) {
	            if (contains(defaultValues, parseInt($(element).val()))) {
	                $(element).prop("selected", true);
	            }
	        });
	        $("#" + newName).trigger("change").trigger("liszt:updated").trigger("chosen:updated");
	        
		    $("#" + newName)
		    	.change(function(event) {
		        var element = $(this);
		        if (element.find("option").filter(":selected").length === 0) {
		            element.find(":first").prop("selected", true);
		            element.trigger("change").trigger("liszt:updated").trigger("chosen:updated");
		        }
		        else if ((element.find("option").filter(":selected").length > 1)
		         && (element.find(":first").filter(":selected").length !== 0)){
		            element.find(":first").removeAttr("selected").prop("selected", false);
		            element.trigger("change").trigger("liszt:updated").trigger("chosen:updated");
		        }
		    });
		    $("form#adminForm").submit(function(event) {
		        var form = $(this);
		        var multiValues = form.find("#" + newName)
		        					.find("option")
		        					.filter(":selected")
		        					.map(function() {
		        					    return this.value;
		        					})
		        					.get()
		        					.join(";");
		        form.find("#" + origName).val(multiValues);
		    })
		}(jQuery));
	</script>';

				}
			}
		}

	}

	public function onBeforeQueryGetCountAllOrders(&$query, array &$filters)
	{
		if ($filters['status_id']) {
			$status_values = explode(';', $filters['status_id']);
			foreach ($status_values as &$status_value) {
				$status_value = "'" . $this->db->escape($status_value) . "'";
			}
			unset($status_value);

			$pattern = "/order_status\s*=\s*'[0-9;]*'/ui";
			$in = implode(', ', $status_values);

			$replace = "order_status in (" . $in . ") ";

			$query = preg_replace($pattern, $replace, $query);
		}
	}

	public function onBeforeQueryGetAllOrders(&$query, array &$filters, &$filter_order, &$filter_order_Dir)
	{
		if ($filters['status_id']) {
			$status_values = explode(';', $filters['status_id']);
			foreach ($status_values as &$status_value) {
				$status_value = "'" . $this->db->escape($status_value) . "'";
			}
			unset($status_value);

			$pattern = "/order_status\s*=\s*'[0-9;]*'/ui";
			$in = implode(', ', $status_values);

			$replace = "order_status in (" . $in . ") ";


			$query = preg_replace($pattern, $replace, $query);
		}
	}
}
