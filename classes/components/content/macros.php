<?php

	/**
	 * Класс макросов, то есть методов, доступных в шаблоне
	 */
	class ContentMacros {
		/**
		 * @var content $module
		 */
		public $module;

		/**
		 * Генерирует и возвращает карту сайта
		 * @param string $template имя шаблона (для tpl шаблонизатора)
		 * @param bool|int $max_depth уровень вложенности
		 * @param bool|int $root_id идентификатор корневой страницы
		 * @return mixed
		 */
		public function sitemap($template = "default", $max_depth = false, $root_id = false) {
			/**
			 * @var content|ContentMacros $this
			 */
			$hierarchy = umiHierarchy::getInstance();
			$cmsController = cmsController::getInstance();

			if (!$max_depth) {
				$max_depth = getRequest('param0');
			}
			if (!$max_depth) {
				$max_depth = 4;
			}

			if (!$root_id) {
				$root_id = (int) getRequest('param1');
			}
			if (!$root_id) {
				$root_id = 0;
			}

			if ($cmsController->getCurrentMethod() == "sitemap") {
				$this->module->setHeader("%content_sitemap%");
			}

			$site_tree = $hierarchy->getChildrenTree($root_id, false, false, $max_depth - 1);
			return $this->gen_sitemap($template, $site_tree, $max_depth - 1);
		}

		/**
		 * Возвращает контент страницы.
		 * Если не передан идентификатор - возьмет текущую страницу.
		 * @param bool|int $elementId идентификатор страницы
		 * @return mixed
		 */
		public function content($elementId = false) {
			$cmsController = cmsController::getInstance();
			if (!$elementId) {
				$elementId = $cmsController->getCurrentElementId();
			}

			$hierarchy = umiHierarchy::getInstance();
			$element = $hierarchy->getElement($elementId);

			if ($element instanceof iUmiHierarchyElement) {
				$this->module->pushEditable("content", "", $elementId);
				return $element->getValue('content');
			}

			return $this->gen404();
		}

		/**
		 * Устанавливает статус 404 и возвращает содержимое
		 * отсутствующей страницы
		 * @param string $template имя шаблона для tpl шаблонизатора
		 * @return mixed
		 * @throws coreException
		 */
		public function gen404($template = 'default') {
			if (!$template) {
				$template = 'default';
			}

			/**
			 * @var HTTPOutputBuffer $buffer
			 */
			$buffer = outputBuffer::current();
			$buffer->status('404 Not Found');

			$this->module->setHeader('%content_error_404_header%');

			list($tpl_block) = content::loadTemplates("content/not_found/" . $template, 'block');
			$template = $tpl_block ? $tpl_block : '%content_usesitemap%';

			return content::parseTemplate($template, []);
		}

		/**
		 * Выводит список элементов типа "Страница контента"
		 * @param string $template имя шаблона (для TPL-шаблонизатора)
		 * @param int|string $path ID элемента или его адрес
		 * @param int $maxDepth максимальная глубина вложенности иерархии поиска элементов (во вложенных подразделах)
		 * @param int $perPage количество элементов на странице (при постраничной навигации)
		 * @param bool $ignorePaging игнорировать постраничную навигацию
		 * @param string $sortField имя поля, по которому нужно произвести сортировку элементов
		 * @param string $sortDirection направление сортировки ('asc' или 'desc')
		 * @return array
		 * @throws publicException
		 * @throws selectorException
		 */
		public function getList($template = 'default', $path = 0, $maxDepth = 1, $perPage = 0,
				$ignorePaging = false, $sortField = '', $sortDirection = 'asc') {

			$elements = new selector('pages');
			$elements->types('hierarchy-type')->name('content', 'page');

			$parentId = $this->module->analyzeRequiredPath($path);

			if (!$parentId && $parentId !== 0 && $path !== KEYWORD_GRAB_ALL) {
				throw new publicException(getLabel('error-page-does-not-exist', null, $path));
			}

			if ($path !== KEYWORD_GRAB_ALL) {
				$maxDepthNum = intval($maxDepth) > 0 ? intval($maxDepth) : 1;
				$elements->where('hierarchy')->page($parentId)->childs($maxDepthNum);
			}

			$perPageNumber = intval($perPage);
			$limit = $perPageNumber > 0 ? $perPageNumber : $this->module->perPage;

			if (!$ignorePaging) {
				$currentPage = intval(getRequest('p'));
				$offset = $currentPage * $limit;
				$elements->limit($offset, $limit);
			}

			if ($sortField) {
				$direction = 'asc';
				if (in_array($sortDirection, ['asc', 'desc', 'rand'])) {
					$direction = $sortDirection;
				}

				try {
					$elements->order($sortField)->$direction();
				} catch (selectorException $e) {
					throw new publicException(getLabel('error-prop-not-found', null, $sortField));
				}
			}

			selectorHelper::detectFilters($elements);

			$elements->option('load-all-props')->value(true);
			$elements->option('exclude-nested', false);

			$result = $elements->result();

			list($templateBlock, $templateBlockEmpty, $templateItem) =
					content::loadTemplates('content/' . $template, 'get_list_block', 'get_list_block_empty', 'get_list_item');

			$total = $elements->length();

			$data = [
					'items' => [
							'nodes:item' => null
					],
					'total' => $total,
					'per_page' => $limit,
					'parent_id' => $parentId
			];

			if ($total === 0) {
				return content::parseTemplate($templateBlockEmpty, $data, $parentId);
			}

			$linksHelper = umiLinksHelper::getInstance();
			$umiHierarchy = umiHierarchy::getInstance();

			$items = [];
			/** @var iUmiHierarchyElement|iUmiEntinty $page */
			foreach ($result as $page) {
				if (!$page instanceof iUmiHierarchyElement) {
					continue;
				}

				$itemData = [];

				$itemData['@id'] = $page->getId();
				$itemData['name'] = $page->getName();
				$itemData['@link'] = $linksHelper->getLinkByParts($page);
				$itemData['@xlink:href'] = 'upage://' . $page->getId();
				$itemData['@visible_in_menu'] = $page->getIsVisible();
				$items[] = content::parseTemplate($templateItem, $itemData, $page->getId());
				$umiHierarchy->unloadElement($page->getId());
			}

			$data['items']['nodes:item'] = content::parseTemplate($templateBlock, $items);
			return content::parseTemplate($templateBlock, $data, $parentId);
		}

		/**
		 * Гененирует результатирующий массив с данными карты сайта для последующей шаблонизации
		 * @param string $template
		 * @param $site_tree
		 * @param $max_depth
		 * @return mixed
		 */
		public function gen_sitemap($template = "default", $site_tree, $max_depth) {
			$hierarchy = umiHierarchy::getInstance();

			list($template_block, $template_item) = content::loadTemplates("content/sitemap/" . $template, "block", "item");

			$block_arr = [];
			$items = [];
			if (is_array($site_tree)) {
				foreach ($site_tree as $elementId => $childs) {
					if ($element = $hierarchy->getElement($elementId)) {
						$item_arr = [
								'attribute:id' => $elementId,
								'attribute:link' => $element->link,
								'attribute:name' => $element->getName(),
								'xlink:href' => ("upage://" . $elementId)
						];

						if (($max_depth > 0) && $element->show_submenu) {
							$item_arr['nodes:items'] = $item_arr['void:sub_items'] = (sizeof($childs) && is_array($childs)) ? $this->gen_sitemap($template, $childs, ($max_depth - 1)) : "";
						} else {
							$item_arr['sub_items'] = "";
						}
						$items[] = content::parseTemplate($template_item, $item_arr, $elementId);
						$hierarchy->unloadElement($elementId);
					} else {
						continue;
					}
				}
			}

			$block_arr['subnodes:items'] = $items;
			return content::parseTemplate($template_block, $block_arr, 0);
		}

		/**
		 * Возвращает адрес страницы по ее идентификатору
		 * @param int $element_id идентификатор страницы
		 * @param bool $ignore_lang игнорировать языковой префикс в адресе
		 * @return string
		 */
		public function get_page_url($element_id, $ignore_lang = false) {
			$ignore_lang = (bool) $ignore_lang;
			return umiHierarchy::getInstance()->getPathById($element_id, $ignore_lang);
		}

		/**
		 * Возвращает идентификатор страницы по ее адресу
		 * @param $url
		 * @return int
		 * @throws publicException
		 */
		public function get_page_id($url) {
			$hierarchy = umiHierarchy::getInstance();
			$elementId = $hierarchy->getIdByPath($url);

			if ($elementId) {
				return $elementId;
			}

			throw new publicException(getLabel('error-page-does-not-exist', null, $url));
		}

		/**
		 * Возвращает контент страницы
		 * @param int|string $elementId идентификатор или адрес страницы
		 * @return bool|Mixed|null|string
		 */
		public function insert($elementId) {
			$hierarchy = umiHierarchy::getInstance();
			$cmsController = cmsController::getInstance();
			$currentElementId = $cmsController->getCurrentElementId();
			$elementId = trim($elementId);

			if (!$elementId) {
				return "%content_error_insert_null%";
			}

			$elementId = (int) is_numeric($elementId) ? $elementId : $hierarchy->getIdByPath($elementId);
			if ($elementId == $currentElementId) {
				return "%content_error_insert_recursy%";
			}
			if (!$elementId) {
				return "%content_error_insert_null%";
			}

			if ($element = $hierarchy->getElement($elementId)) {
				$this->module->pushEditable("content", "", $elementId);
				return $element->content;
			}

			return "%content_error_insert_null%";
		}

		/**
		 * Возвращает список последних просмотренных страниц
		 * @param string $template Шаблон для вывода
		 * @param string $scope Тэг(группировка страниц), без пробелов и запятых
		 * @param bool $showCurrentElement Если false - текущая страница не будет включена в результат
		 * @param int|null $limit Количество выводимых элементов
		 * @return mixed
		 */
		public function getRecentPages($template = "default", $scope = "default", $showCurrentElement = false, $limit = null) {
			if (!$scope) {
				$scope = "default";
			}

			$hierarchy = umiHierarchy::getInstance();
			$currentElementId = cmsController::getInstance()->getCurrentElementId();
			list($itemsTemplate, $itemTemplate) = content::loadTemplates("content/" . $template, "items", "item");
			$recentPages = \UmiCms\Service::Session()->get('content:recent_pages');
			$recentPages = (is_array($recentPages)) ? $recentPages : [];
			$items = [];

			if (!isset($recentPages[$scope])) {
				return content::parseTemplate($itemsTemplate, ["subnodes:items" => []]);
			}

			$pageIdList = [];

			foreach ($recentPages[$scope] as $pageId => $time) {
				$pageIdList[] = $pageId;
			}

			$hierarchy->loadElements($pageIdList);

			foreach ($recentPages[$scope] as $pageId => $time) {
				$element = $hierarchy->getElement($pageId, true);

				if (!($element instanceOf umiHierarchyElement)) {
					continue;
				}

				if (!$showCurrentElement && $element->getId() == $currentElementId) {
					continue;
				}

				if (!is_null($limit) && $limit <= 0) {
					break;
				}

				if (!is_null($limit)) {
					$limit--;
				}

				$items[] = content::parseTemplate($itemTemplate, [
						'@id' => $element->getId(),
						'@link' => $element->link,
						'@name' => $element->getName(),
						'@alt-name' => $element->getAltName(),
						'@xlink:href' => "upage://" . $element->getId(),
						'@last-view-time' => $time,
						'node:text' => $element->getName()
				], $element->getId());
			}

			return content::parseTemplate($itemsTemplate, ["subnodes:items" => $items]);
		}

		/**
		 * Добавляет страницу к списку последних просмотреных страниц
		 * @param int $elementId Текущая страница
		 * @param string $scope Тэг(группировка страниц)
		 * @return null
		 */
		public function addRecentPage($elementId, $scope = "default") {
			if (!$scope) {
				$scope = "default";
			}

			if ($elementId != cmsController::getInstance()->getCurrentElementId()) {
				return null;
			}

			$limit = mainConfiguration::getInstance()->get("modules", "content.recent-pages.max-items");
			$limit = $limit ? $limit : 100;

			$session = \UmiCms\Service::Session();
			$recentPages = $session->get('content:recent_pages');
			$recentPages = (is_array($recentPages)) ? $recentPages : [];

			if (!isset($recentPages[$scope])) {
				$recentPages[$scope] = [];
			}

			$recentPages[$scope][$elementId] = time();
			asort($recentPages[$scope]);
			$recentPages[$scope] = array_reverse($recentPages[$scope], true);
			$recentPages[$scope] = array_slice($recentPages[$scope], 0, $limit, true);

			$session->set('content:recent_pages', $recentPages);

			return null;
		}

		/**
		 * Удаляет страницу из списка последних использований
		 * @param int|bool $elementId Id страницы
		 * @param string $scope Тэг
		 * @return bool
		 */
		public function delRecentPage($elementId = false, $scope = "default") {
			if ($elementId === false) {
				$elementId = getRequest('param0');
			}

			if (!$scope) {
				$scope = "default";
			}

			$session = \UmiCms\Service::Session();
			$recentPages = $session->get('content:recent_pages');
			$recentPages = (is_array($recentPages)) ? $recentPages : [];

			if (isset($recentPages[$scope][$elementId])) {
				unset($recentPages[$scope][$elementId]);
				$session->set('content:recent_pages', $recentPages);

			}

			$this->module->redirect(getServer('HTTP_REFERER'));
		}

		/**
		 * Получает список режимов отображения
		 * Текущий помечается как current
		 * @param string $template TPL шаблон
		 * @return mixed
		 */
		public function getMobileModesList($template = "default") {
			$isMobile = (bool) system_is_mobile();
			$modes = [
					"is_mobile" => 1,
					"is_desktop" => 0
			];

			$items = [];
			foreach ($modes as $mode => $value) {
				$itemArray = [
						"@name" => $mode,
						"@link" => '/content/setMobileMode/' . ($value ? 0 : 1),
				];

				if ($value == $isMobile) {
					$itemArray["@status"] = "active";
					$items[] = content::renderTemplate("content/mobile/" . $template, $mode, $itemArray);
				} else {
					$items[] = content::parseTemplate("", $itemArray);;
				}
			}

			return content::renderTemplate("content/mobile/" . $template, "modes", [
					"subnodes:items" => $items
			]);
		}

		/**
		 * Устанавливает режим отображения сайта
		 * @internal
		 * @param bool $isMobile Режим
		 */
		public function setMobileMode($isMobile = null) {
			if (is_null($isMobile)) {
				$isMobile = getRequest('param0');
			}

			$cookieJar = \UmiCms\Service::CookieJar();

			if ($isMobile == 1) {
				$cookieJar->set('is_mobile', 1);
			} elseif ($isMobile == 0) {
				$cookieJar->set('is_mobile', 0);
			}

			$this->module->redirect(getServer('HTTP_REFERER'));
		}
	}
