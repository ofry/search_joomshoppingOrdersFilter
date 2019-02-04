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
				$doc = $this->app->getDocument();

				libxml_use_internal_errors(true);
				$dom = new DOMDocument();
				$dom->loadHTML($view->lists['changestatus'], LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
				$select = simplexml_import_dom($dom);
				/*
				 * Добавляем возможность множественного выбора
				 */
				if (!isset($select->multiple)) {
					$select->addAttribute('multiple', 'multiple');
					$select->name .= '[]';
				}
				$select_dom = dom_import_simplexml($select);
				$view->lists['changestatus'] = $select_dom->ownerDocument->saveHTML();
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
