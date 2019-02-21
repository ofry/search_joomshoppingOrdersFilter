<?php
/**
 * @package    filterMultiple
 *
 * @author     ofryak <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
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
					$hidden_input = '<input type="hidden" name="' . $select_origname . '"
					 id="' . $select_origname . '"  />';
					$view->lists['changestatus'] = $select_dom->ownerDocument->saveHTML()
						. $hidden_input;
					/*
					 * Добавляем скрипт для обработки множественного выбора
					 */
					$view->_tmp_order_list_html_end = '
	<script type="text/javascript">
		(function($){
		    var origName = "'. $select_origname . '";
		    var newName = origName + "_multi";
		    $("#" + newName).change(function(event) {
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
		        console.log(form.find("#" + newName).find("option").filter(":selected"));
		        return false;
		    })
		}(jQuery));
	</script>';

				}
			}
		}

	}

	public function onBeforeQueryGetCountAllOrders(&$params, array &$filters)
	{
		//var_dump($params);
	}

	public function onBeforeQueryGetAllOrders(&$params, array &$filters, &$filter_order, &$filter_order_Dir)
	{
		//var_dump($params);
	}
}
