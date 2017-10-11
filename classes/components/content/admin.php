<?php

	/**
	 * Класс функционала административной панели
	 */
	class ContentAdmin {

		use baseModuleAdmin;
		/**
		 * @var content $module
		 */
		public $module;

		/**
		 * Возвращает список объектов по идентификатору объектного типа
		 * @param int $type_id идентификатор объектного типа
		 * @return array
		 * @throws coreException
		 */
		public function getObjectsByTypeList($type_id) {
			$objectsCollection = umiObjectsCollection::getInstance();
			$objects = $objectsCollection->getGuidedItems($type_id);

			$items = [];

			foreach ($objects as $item_id => $item_name) {
				$items[] = [
					'attribute:id' => $item_id,
					'node:name' => $item_name
				];
			}

			return [
				'items' => [
					'nodes:item' => $items
				]
			];
		}

		/**
		 * Возвращает список объектов по имени и расширению
		 * иерархического типа
		 * @param string $module имя иерархического типа
		 * @param string $method расширение иерархического типа
		 * @return array
		 * @throws coreException
		 */
		public function getObjectsByBaseTypeList($module, $method) {
			$objectTypesCollection = umiObjectTypesCollection::getInstance();
			$objectsCollection = umiObjectsCollection::getInstance();

			$type_id = $objectTypesCollection->getTypeIdByHierarchyTypeName($module, $method);

			$objects = $objectsCollection->getGuidedItems($type_id);

			$items = [];
			foreach ($objects as $item_id => $item_name) {
				$items[] = [
					'attribute:id' => $item_id,
					'node:name' => $item_name
				];
			}
			return ['items' => ['nodes:item' => $items]];
		}

		/**
		 * Возвращает список страниц по имени и расширению
		 * иерархического типа
		 * @param string $module имя иерархического типа
		 * @param string $method расширение иерархического типа
		 * @return array
		 * @throws publicException
		 */
		public function getPagesByBaseTypeList($module, $method) {
			$hierarchyTypesCollection = umiHierarchyTypesCollection::getInstance();
			$type = $hierarchyTypesCollection->getTypeByName($module, $method);

			/* @var iUmiHierarchyType|iUmiEntinty $type */
			if ($type instanceof iUmiHierarchyType) {
				$typeId = $type->getId();
			} else {
				throw new publicException("Hierarchy type {$module}::{$method} doesn't exist");
			}

			$sel = new selector('pages');
			$sel->types('hierarchy-type')->id($typeId);
			$result = $sel->result();
			$pages = [];

			foreach ($result as $element) {
				if ($element instanceof umiHierarchyElement) {
					$pages[] = $element;
				}
			}

			return ["pages" => ["nodes:page" => $pages]];
		}

		/**
		 * Возвращает список шаблонов сайта
		 */
		public function domainTemplates() {
			$domains = domainsCollection::getInstance();
			$langs = langsCollection::getInstance();
			$templates = templatesCollection::getInstance();

			$data = [];
			foreach ($domains->getList() as $domain) {
				/** @var domain $domain */
				$domainId = $domain->getId();

				foreach ($langs->getList() as $lang) {
					/** @var lang $lang */
					$langId = $lang->getId();

					foreach ($templates->getTemplatesList($domainId, $langId) as $template) {
						$data['templates']['nodes:template'][] = $template;
					}
				}
			}

			foreach ($domains->getList() as $domain) {
				$data['domains']['nodes:domain'][] = $domain;
			}

			foreach ($langs->getList() as $lang) {
				$data['langs']['nodes:lang'][] = $lang;
			}

			$this->setDataType("list");
			$this->setActionType("view");

			$this->setData($data);
			$this->doData();
		}

		/**
		 * Возвращает список доменов системы для построения деревьев
		 * @throws coreException
		 */
		public function sitetree() {
			$domains = domainsCollection::getInstance()->getList();
			$permissions = permissionsCollection::getInstance();
			$auth = UmiCms\Service::Auth();
			$user_id = $auth->getUserId();

			$this->setDataType("list");
			$this->setActionType("view");

			foreach ($domains as $i => $domain) {
				/** @var domain $domain */
				$domain_id = $domain->getId();

				if (!$permissions->isAllowedDomain($user_id, $domain_id)) {
					unset($domains[$i]);
				}
			}

			$data = $this->prepareData($domains, "domains");

			$this->setData($data, sizeof($domains));
			$this->doData();
		}

		/**
		 * Возвращает данные списка страниц контента для административной панели
		 * @return bool
		 * @throws coreException
		 * @throws selectorException
		 */
		public function tree() {
			$this->setDataType("list");
			$this->setActionType("view");

			if ($this->module->ifNotXmlMode()) {
				$this->setDirectCallError();
				$this->doData();
				return true;
			}

			$limit = getRequest('per_page_limit');
			$currentPage = getRequest('p');
			$offset = $currentPage * $limit;

			$sel = new selector('pages');
			$sel->types('hierarchy-type')->name('content', 'page');

			if (is_array(getRequest('rel')) && regedit::getInstance()->getVal('//modules/comments')) {
				$sel->types('hierarchy-type')->name('comments', 'comment');
			}

			$sel->limit($offset, $limit);
			selectorHelper::detectFilters($sel);

			$data = $this->prepareData($sel->result(), "pages");

			$this->setData($data, $sel->length());
			$this->setDataRangeByPerPage($limit, $currentPage);
			$this->doData();

			return true;
		}

		/**
		 * Возвращает форму создания страницы.
		 * Если передан ключевой параметр $_REQUEST['param2'] = do,
		 * то метод запустит добавление страницы
		 * @throws coreException
		 * @throws expectElementException
		 * @throws wrongElementTypeAdminException
		 */
		public function add() {
			$parent = $this->expectElement("param0");
			$type = (string) getRequest("param1");
			$mode = (string) getRequest("param2");

			$inputData = [
				'type' => $type,
				'parent' => $parent,
				'type-id' => getRequest('type-id'),
				'allowed-element-types' => ['page', '']
			];

			if ($mode == "do") {
				$this->saveAddedElementData($inputData);
				$this->chooseRedirect();
			}

			$this->setDataType("form");
			$this->setActionType("create");

			$data = $this->prepareData($inputData, "page");

			$this->setData($data);
			$this->doData();
		}

		/**
		 * Возвращает форму редактирования страницы.
		 * Если передан ключевой параметр $_REQUEST['param1'] = do,
		 * то метод запустит сохранение страницы
		 * @throws coreException
		 * @throws expectElementException
		 * @throws wrongElementTypeAdminException
		 */
		public function edit() {
			$element = $this->expectElement("param0");
			$mode = (string) getRequest('param1');

			$inputData = ["element" => $element,
				"allowed-element-types" => ['page', '']
			];

			if ($mode == "do") {
				$this->saveEditedElementData($inputData);
				$this->chooseRedirect();
			}

			$this->setDataType("form");
			$this->setActionType("modify");

			$data = $this->prepareData($inputData, "page");

			$this->setData($data);
			$this->doData();
		}

		/**
		 * Возвращает настройки системы.
		 * Если передан ключевой параметр $_REQUEST['param0'] = do,
		 * то метод запустит сохранение настроек
		 * @throws coreException
		 */
		public function config() {
			$domains = domainsCollection::getInstance()->getList();
			$langId = cmsController::getInstance()->getCurrentLang()->getId();

			$mode = (string) getRequest('param0');
			$result = [];

			/**
			 * @var domain|umiEntinty $domain
			 */
			foreach ($domains as $domain) {
				$host = $domain->getHost();
				$domainId = $domain->getId();
				$result[$host] = [];
				$templates = templatesCollection::getInstance()->getTemplatesList($domainId, $langId);

				foreach ($templates as $template) {
					$result[$host][] = $template;
				}
			}

			if ($mode == "do") {
				$this->saveEditedList("templates", $result);
				$this->chooseRedirect();
			}

			$this->setDataType("list");
			$this->setActionType("modify");

			$data = $this->prepareData($result, "templates");

			$this->setData($data);
			$this->doData();
		}

		/**
		 * Удаляет страницу
		 * @throws coreException
		 * @throws expectElementException
		 * @throws wrongElementTypeAdminException
		 */
		public function del() {
			$element = $this->expectElement('param0');

			$params = [
				"element" => $element,
				"allowed-element-types" => ['page', '']
			];

			$this->deleteElement($params);
			$this->chooseRedirect();
		}

		/**
		 * Возвращает форму редактирования шаблона.
		 * Если передан ключевой параметр $_REQUEST['param1'] = do,
		 * то метод запустит сохранение шаблона
		 * @throws coreException
		 */
		public function tpl_edit() {
			$tpl_id = (int) getRequest('param0');
			$template = templatesCollection::getInstance()->getTemplate($tpl_id);

			$mode = (string) getRequest('param1');

			if ($mode == "do") {
				$this->saveEditedTemplateData($template);
				$this->chooseRedirect();
			}

			$this->setDataType('form');
			$this->setActionType('modify');

			$data = $this->prepareData($template, 'template');

			$this->setData($data);
			$this->doData();
		}

		/**
		 * Валидирует текущего пользователя и отключает блокировку у страницы
		 * @throws publicAdminException
		 */
		public function unlock_page() {
			$pageId = getRequest("param0");

			if (permissionsCollection::getInstance()->isSv()) {
				throw new publicAdminException(getLabel('error-can-unlock-not-sv'));
			}

			$this->module->unlockPage($pageId);
		}

		/**
		 * Возвращает настройки управления контентом.
		 * Если передан ключевой параметр $_REQUEST['param0'] = do,
		 * то метод запустит сохранение настроек.
		 * @throws coreException
		 */
		public function content_control() {
			$mode = getRequest("param0");
			$regedit = regedit::getInstance();
			$umiNotificationInstalled = cmsController::getInstance()
				->isModule('umiNotifications');

			$params = [
				"content_config" => [
					'bool:lock_pages' => false,
					'int:lock_duration' => 0,
					'bool:expiration_control' => false
				],
				'output_options' => [
					'int:elements_count_per_page' => null
				]
			];

			if ($umiNotificationInstalled) {
				$params['notifications']['boolean:use-umiNotifications'] = null;
			}

			if ($mode == "do") {
				$params = $this->expectParams($params);
				$regedit->setVar("//settings/lock_pages", $params['content_config']['bool:lock_pages']);
				$regedit->setVar("//settings/lock_duration", $params['content_config']['int:lock_duration']);
				$regedit->setVar("//settings/expiration_control", $params['content_config']['bool:expiration_control']);
				$regedit->setVar("//settings/elements_count_per_page", $params['output_options']['int:elements_count_per_page']);

				if ($umiNotificationInstalled) {
					$regedit->setVar("//settings/use-umiNotifications", $params['notifications']['boolean:use-umiNotifications']);
				}

				$this->switchGroupsActivity('svojstva_publikacii', (bool) $params['content_config']['bool:expiration_control']);
				$this->chooseRedirect();
			}

			$params['content_config']['bool:lock_pages'] = $regedit->getVal("//settings/lock_pages");
			$params['content_config']['int:lock_duration'] = $regedit->getVal("//settings/lock_duration");
			$params['content_config']['bool:expiration_control'] = $regedit->getVal("//settings/expiration_control");
			$params['output_options']['int:elements_count_per_page'] = $regedit->getVal("//settings/elements_count_per_page");

			if ($umiNotificationInstalled) {
				$params['notifications']['boolean:use-umiNotifications'] = $regedit->getVal("//settings/use-umiNotifications");
			}

			$this->setDataType("settings");
			$this->setActionType("modify");

			$data = $this->prepareData($params, "settings");
			$this->setData($data);
			$this->doData();
		}

		/**
		 * Возвращает настройки табличного контрола
		 * @param string $param контрольный параметр
		 * @return array
		 */
		public function getDatasetConfiguration($param = '') {
			$loadMethod = 'load_tree_node';
			$deleteMethod = 'tree_delete_element';
			$activityMethod = 'tree_set_activity';

			$types = [];
			if ($param == 'tree') {
				$types = [
					'types' => [
						[
							'common' => 'true',
							'id' => 'page'
						]
					]
				];
				$loadMethod = $param;
			}

			$result = [
				'methods' => [
					[
						'title' => getLabel('smc-load'),
						'forload' => true,
						'module' => 'content',
						'#__name' => $loadMethod
					],
					[
						'title' => getLabel('smc-delete'),
						'module' => 'content',
						'#__name' => $deleteMethod,
						'aliases' => 'tree_delete_element,delete,del'
					],
					[
						'title' => getLabel('smc-activity'),
						'module' => 'content',
						'#__name' => $activityMethod,
						'aliases' => 'tree_set_activity,activity'
					],
					[
						'title' => getLabel('smc-copy'),
						'module' => 'content',
						'#__name' => 'tree_copy_element'
					],
					[
						'title' => getLabel('smc-move'),
						'module' => 'content',
						'#__name' => 'move'
					],
					[
						'title' => getLabel('smc-change-template'),
						'module' => 'content',
						'#__name' => 'change_template'
					],
					[
						'title' => getLabel('smc-change-lang'),
						'module' => 'content',
						'#__name' => 'move_to_lang'
					],
					[
						'title' => getLabel('smc-change-lang'),
						'module' => 'content',
						'#__name' => 'copy_to_lang_old'
					]
				],
				'default' => 'name[400px]'
			];

			if (!empty($types)) {
				$result += $types;
			}

			return $result;
		}

		/**
		 * Возвращает данные для включения быстрого
		 * редактирования поля в табличном контроле
		 * @throws coreException
		 * @throws publicAdminException
		 */
		public function get_editable_region() {
			$itemId = getRequest('param0');
			$propName = getRequest('param1');
			$isObject = (bool) getRequest('is_object');

			$objects = umiObjectsCollection::getInstance();
			$hierarchy = umiHierarchy::getInstance();
			$oEntity = ($isObject) ? $objects->getObject($itemId) : $hierarchy->getElement($itemId);

			// Checking rights
			$bDisallowed = false;
			$mainConfiguration = mainConfiguration::getInstance();
			$objectEditionAllowed = (bool) $mainConfiguration->get('system', 'allow-object-editing');
			$permissions = permissionsCollection::getInstance();
			$auth = UmiCms\Service::Auth();
			$userId = $auth->getUserId();
			$groupIds = $objects->getObject($userId)->getValue('groups');

			$systemUsersPermissions = UmiCms\Service::SystemUsersPermissions();
			$svGroupId = $systemUsersPermissions->getSvGroupId();
			$svId = $systemUsersPermissions->getSvUserId();

			if ($userId != $svId && !in_array($svGroupId, $groupIds)) {
				if ($isObject) {
					$bDisallowed = !($oEntity->getOwnerId() == $userId);
					if ($bDisallowed) {
						$module = $oEntity->getModule();
						$method = $oEntity->getMethod();
						switch (true) {
							case ($module && $method): {
								$bDisallowed = !$permissions->isAllowedMethod($userId, $module, $method);
								break;
							}
							case $objectEditionAllowed: {
								$bDisallowed = false;
								break;
							}
							default: {
								throw new publicAdminException(getLabel('error-no-permissions'));
							}
						}
					}
				} else {
					list ($r, $w) = $permissions->isAllowedObject($userId, $itemId);
					if (!$w) {
						$bDisallowed = true;
					}
				}
			}

			if ($bDisallowed) {
				throw new publicAdminException(getLabel('error-no-permissions'));
			}

			$result = false;
			if ($oEntity) {
				switch ($propName) {
					case "name":
						$result = ['name' => $oEntity->name];
						break;

					default:
						$oObject = (!$isObject) ? $oEntity->getObject() : $oEntity;
						$prop = $oObject->getPropByName($propName);
						if (!$prop instanceof umiObjectProperty) {
							throw new publicAdminException(getLabel('error-property-not-exists'));
						}
						$result = ['property' => $prop];
						translatorWrapper::get($oObject->getPropByName($propName));
						umiObjectPropertyWrapper::$showEmptyFields = true;
				}
			}

			if (!is_array($result)) {
				throw new publicAdminException(getLabel('error-entity-not-exists'));
			}

			$this->setData($result);
			$this->doData();
		}

		/**
		 * Сохраняет значения поля,
		 * используется в быстром редактирования полей
		 * в табличном контроле
		 * @throws coreException
		 * @throws publicAdminException
		 */
		public function save_editable_region() {
			$iEntityId = getRequest('param0');
			$sPropName = getRequest('param1');
			$content = getRequest('data');
			$bIsObject = (bool) getRequest('is_object');

			if (is_array($content) && count($content) == 1) {
				$content = $content[0];
			} else {
				if (is_array($content) && isset($content[0])) {
					$temp = [];
					foreach ($content as $item) {
						$temp[] = is_array($item) ? $item[0] : $item;
					}
					$content = $temp;
				}
			}

			$oEntity = ($bIsObject) ? umiObjectsCollection::getInstance()->getObject($iEntityId) : umiHierarchy::getInstance()->getElement($iEntityId);

			// Checking rights
			$bDisallowed = false;
			$mainConfiguration = mainConfiguration::getInstance();
			$objectEditionAllowed = (bool) $mainConfiguration->get('system', 'allow-object-editing');
			$permissions = permissionsCollection::getInstance();
			$auth = UmiCms\Service::Auth();
			$userId = $auth->getUserId();

			if (!$permissions->isSv($userId)) {
				if ($bIsObject) {
					$bDisallowed = !($oEntity->getOwnerId() == $userId);
					if ($bDisallowed) {
						//Check module permissions
						$module = $oEntity->getModule();
						$method = $oEntity->getMethod();

						switch (true) {
							case ($module && $method): {
								$bDisallowed = !$permissions->isAllowedMethod($userId, $module, $method);
								break;
							}
							case $objectEditionAllowed: {
								$bDisallowed = false;
								break;
							}
							default: {
								throw new publicAdminException(getLabel('error-no-permissions'));
							}
						}
					}
				} else {
					list($r, $w) = $permissions->isAllowedObject($userId, $iEntityId);
					if (!$w) {
						$bDisallowed = true;
					}
				}
			}

			if ($bDisallowed) {
				throw new publicAdminException(getLabel('error-no-permissions'));
			}

			$event = new umiEventPoint("systemModifyPropertyValue");
			$event->addRef("entity", $oEntity);
			$event->setParam("property", $sPropName);
			$event->addRef("newValue", $content);
			$event->setMode("before");

			try {
				$event->call();
			} catch (wrongValueException $e) {
				throw new publicAdminException($e->getMessage());
			}

			/**
			 * @var iUmiEntinty|umiHierarchyElement|iUmiObject $oEntity
			 */
			if ($oEntity instanceof iUmiHierarchyElement) {
				$backupModel = backupModel::getInstance();
				$backupModel->addLogMessage($oEntity->getId());
			}

			if ($bIsObject && !$this->module->checkAllowedColumn($oEntity, $sPropName)) {
				throw new publicAdminException(getLabel('error-no-permissions'));
			}

			if ($bIsObject && $sPropName == 'is_activated') {
				$systemUsersPermissions = UmiCms\Service::SystemUsersPermissions();
				$guestId = $systemUsersPermissions->getGuestUserId();
				$svUserId = $systemUsersPermissions->getSvUserId();

				if ($iEntityId == $svUserId) {
					throw new publicAdminException(getLabel('error-users-swtich-activity-sv'));
				}

				if ($iEntityId == $guestId) {
					throw new publicAdminException(getLabel('error-users-swtich-activity-guest'));
				}

				if ($iEntityId == $userId) {
					throw new publicAdminException(getLabel('error-users-swtich-activity-self'));
				}
			}

			$sPropValue = "";
			if ($oEntity) {
				$bOldVal = umiObjectProperty::$IGNORE_FILTER_INPUT_STRING;
				umiObjectProperty::$IGNORE_FILTER_INPUT_STRING = true;
				/**
				 * @var iUmiEntinty|umiObject $oObject
				 */
				$oObject = (!$bIsObject) ? $oEntity->getObject() : $oEntity;
				$oldValue = null;

				try {
					if ($sPropName == 'name') {
						if (is_string($content) && strlen($content)) {
							$oldValue = $oEntity->getName();
							$oEntity->setName($content);
							if ($oEntity instanceof iUmiHierarchyElement) {
								$oEntity->h1 = $content;
							}
						}
						$result = ['name' => $content];
					} else {
						/**
						 * @var iUmiObjectProperty $property
						 */
						$property = $oObject->getPropByName($sPropName);

						switch ($property->getDataType()) {
							case 'date' : {
								$date = new umiDate();
								$date->setDateByString($content);
								$content = $date;
								break;
							}
							case 'img_file' :
							case 'swf_file' :
							case 'video_file' :
							case 'file' : {
								$file = new umiFile('.' . $content);
								$content = $file;
								break;
							}
						}

						$oldValue = $oObject->getValue($sPropName);
						$oObject->setValue($sPropName, $content);

						if ($oObject->getIsUpdated() && $oObject->getId() != $oEntity->getId()) {
							$oEntity->setIsUpdated(true, true);
						}

						if ($oEntity instanceof iUmiHierarchyElement && $sPropName == 'h1') {
							$oEntity->setName($content);
						}
						$result = ['property' => $property];

						translatorWrapper::get($property);
						umiObjectPropertyWrapper::$showEmptyFields = true;
					}
				} catch (fieldRestrictionException $e) {
					throw new publicAdminException($e->getMessage());
				}
				$oEntity->commit();
				umiObjectProperty::$IGNORE_FILTER_INPUT_STRING = $bOldVal;

				$oObject->update();
				$oEntity->update();

				if ($oEntity instanceof umiEntinty) {
					$oEntity->commit();
				}

				$event->setParam("oldValue", $oldValue);
				$event->setParam("newValue", $content);
				$event->setMode("after");
				$event->call();

				$this->setData($result);
				$this->doData();
			}
		}

		/**
		 * Возвращает ветвь контрола типа "Дерево" или "Таблица"
		 * @throws coreException
		 */
		public function load_tree_node() {
			$this->setDataType("list");
			$this->setActionType("view");

			$limit = getRequest('per_page_limit');
			$curr_page = getRequest('p');
			$offset = $curr_page * $limit;

			list($rel) = getRequest('rel');
			$sel = new selector('pages');
			if ($rel !== 0) {
				$sel->limit($offset, $limit);
			}
			selectorHelper::detectFilters($sel);

			$result = $sel->result();
			$length = $sel->length();
			$templatesData = getRequest('templates');

			if ($templatesData) {
				$templatesList = explode(',', $templatesData);
				$result = $this->module->getPagesByTemplatesIdList($templatesList, $limit, $offset);
				$length = $this->module->getTotalPagesByTemplates($templatesList);
			}

			$data = $this->prepareData($result, "pages");
			$this->setData($data, $length);
			$this->setDataRange($limit, $offset);

			if ($rel != 0) {
				$this->setDataRangeByPerPage($limit, $curr_page);
			}

			$this->doData();
		}

		/**
		 * Переключает активность у страниц
		 * @throws expectElementException
		 * @throws publicAdminException
		 * @throws requreMoreAdminPermissionsException
		 * @throws wrongElementTypeAdminException
		 */
		public function tree_set_activity() {
			$elementIdList = (array) getRequest('element');
			$active = getRequest('active');

			if (is_null($active)) {
				throw new publicAdminException(getLabel('error-expect-action'));
			}

			$active = (bool) $active;
			$throwExceptionIfNotElement = true;
			$useFirstArgumentAsElementId = true;

			foreach ($elementIdList as $elementId) {

				$element = $this->expectElement(
					$elementId, $throwExceptionIfNotElement, $useFirstArgumentAsElementId
				);

				$params = [
					'element' => $element,
					'activity' => $active
				];

				$this->switchActivity($params);
			}

			$this->setDataType('list');
			$this->setActionType('view');
			$data = $this->prepareData($elementIdList, 'pages');
			$this->setData($data);
			$this->doData();
		}

		/**
		 * Перемещает страницу и|или объект в административной панели
		 * @throws expectElementException
		 * @throws expectObjectException
		 * @throws publicAdminException
		 */
		public function move() {
			$element = $this->expectElement("element");
			$elementParent = $this->expectElement("rel");

			if ($element instanceof iUmiHierarchyElement && ($elementParent instanceof iUmiHierarchyElement || getRequest("rel") == 0)) {
				return $this->tree_move_element();
			}

			$object = $this->expectObject("element");
			$objectParent = $this->expectObject("rel");

			if ($object instanceof iUmiObject && $objectParent instanceof iUmiObject) {
				return $this->table_move_object($object, $objectParent);
			}

			if (($element instanceof iUmiHierarchyElement || $object instanceof iUmiObject) && ($elementParent instanceof iUmiHierarchyElement || $objectParent instanceof iUmiObject)) {
				return $this->table_mixed_move();
			}

			$this->setDataType("list");
			$this->setActionType("view");

			$this->setData([]);
			$this->doData();
		}

		/**
		 * Метод-заглушка для смешанного перемещения страниц и объектов
		 */
		public function table_mixed_move() {
			$this->setDataType("list");
			$this->setActionType("view");
			$this->setData(['node' => 'mixed']);
			$this->doData();
		}

		/**
		 * Перемещает объект
		 * @param iUmiObject $object объект который перемещают
		 * @param iUmiObject $objectParent объект в который перемещают
		 */
		public function table_move_object(iUmiObject $object, iUmiObject $objectParent) {
			$this->setDataType("list");
			$this->setActionType("view");

			$moveMode = getRequest('moveMode');

			$umiObjects = umiObjectsCollection::getInstance();
			$orderChanged = $umiObjects->changeOrder($objectParent, $object, $moveMode);

			if ($orderChanged) {
				$this->setDataRange(2, 0);
				$data = $this->prepareData([$object, $objectParent], 'objects');
				$this->setData($data, 2);
			} else {
				$this->setDataRange(0, 0);
				$data = $this->prepareData([], 'objects');
				$this->setData($data, 0);
			}

			$this->doData();
		}

		/**
		 * Перемещает выбранные страницы
		 */
		public function tree_move_element() {
			$selectedItems = getRequest('selected_list');
			$newParentId = (int) getRequest("rel");
			$domain = getRequest('domain');
			$asSibling = (int) getRequest('as-sibling');
			$beforeId = getRequest('before');

			$umiHierarchy = umiHierarchy::getInstance();
			$newParentParentsIds = $umiHierarchy->getAllParents($newParentId);
			$page = null;
			$movedPages = [];

			if (count($selectedItems) == 0 && isset($_REQUEST['element']) && getRequest('return_copies')) {
				$selectedItems[] = getRequest('element');
			}

			foreach ($selectedItems as $pageId) {
				if (in_array($pageId, $newParentParentsIds)) {
					continue;
				}

				/**
				 * @var iUmiHierarchyElement|iUmiEntinty $page
				 */
				$page = $this->expectElement($pageId, false, true);

				if (!$page instanceof iUmiHierarchyElement) {
					throw new publicAdminException(getLabel('error-expect-element'));
				}

				switch (true) {
					case ($asSibling) : {
						$needToDoAnything = true;
						break;
					}
					case (!$asSibling && $newParentId != $page->getParentId() && $pageId != $newParentId) : {
						$needToDoAnything = true;
						break;
					}
					default : {
						$needToDoAnything = false;
					}
				}

				if (!$needToDoAnything) {
					continue;
				}

				$movingParams = [
					'element' => $page,
					'parent-id' => $newParentId,
					'domain' => $domain,
					'as-sibling' => $asSibling,
					'before-id' => $beforeId
				];

				if ($this->moveElement($movingParams)) {
					$movedPages[] = $page->getId();
				}
			}

			if (getRequest('return_copies')) {
				$this->setDataType("form");
				$this->setActionType("modify");
				$data = $this->prepareData(['element' => $page], "page");
			} else {
				$this->setDataType("list");
				$this->setActionType("view");
				$data = $this->prepareData($movedPages, "pages");
			}

			$this->setData($data);
			$this->doData();
		}

		/**
		 * Удаляет страницы в корзину
		 * @throws coreException
		 * @throws expectElementException
		 * @throws publicAdminException
		 * @throws wrongElementTypeAdminException
		 */
		public function tree_delete_element() {
			$elements = getRequest('element');
			if (!is_array($elements)) {
				$elements = [$elements];
			}

			$parentIds = [];

			foreach ($elements as $elementId) {
				$element = $this->expectElement($elementId, false, true, true);

				if ($element instanceof umiHierarchyElement) {
					// before del event
					$element_id = $element->getId();
					$parentIds[] = $element->getParentId();
					$oEventPoint = new umiEventPoint("content_del_element");
					$oEventPoint->setMode("before");
					$oEventPoint->setParam("element_id", $element_id);
					$this->module->setEventPoint($oEventPoint);

					// try delete
					$params = [
						"element" => $element
					];

					$this->deleteElement($params);

					// after del event
					$oEventPoint->setMode("after");
					$this->module->setEventPoint($oEventPoint);
				} else {
					throw new publicAdminException(getLabel('error-expect-element'));
				}
			}

			$parentIds = array_unique($parentIds);

			// retrun parent element for update
			$this->setDataType("list");
			$this->setActionType("view");
			$data = $this->prepareData($parentIds, "pages");

			$this->setData($data);
			$this->doData();
		}

		/**
		 * Копирует страницу
		 * @throws Exception
		 * @throws coreException
		 * @throws expectElementException
		 * @throws publicAdminException
		 */
		public function tree_copy_element() {
			$element = $this->expectElement('element');
			$cloneMode = (bool) getRequest('clone_mode');
			$copyAll = (bool) getRequest('copy_all');
			$parentId = (int) getRequest('rel');
			$connection = ConnectionPool::getInstance()->getConnection();
			$new_element_id = false;

			if ($element instanceof umiHierarchyElement) {
				$element_id = $element->getId();
				if (!($parentId && umiHierarchy::getInstance()->isExists($parentId))) {
					$parentId = umiHierarchy::getInstance()->getParent($element_id);
				}

				$connection->query("START TRANSACTION");

				if ($cloneMode) {
					// create real copy
					$clone_allowed = true;

					if ($clone_allowed) {
						$event = new umiEventPoint("systemCloneElement");
						$event->addRef("element", $element);
						$event->setParam("elementId", $element_id);
						$event->setParam("parentId", $parentId);
						$event->setMode("before");
						$event->call();

						$new_element_id = umiHierarchy::getInstance()->cloneElement($element_id, $parentId, $copyAll);

						$event->setParam("newElementId", $new_element_id);
						$event->setMode("after");
						$event->call();

						$new_element = umiHierarchy::getInstance()->getElement((int) $new_element_id, false, false);

						$event = new umiEventPoint("systemCreateElementAfter");
						$event->addRef("element", $new_element);
						$event->setParam("elementId", $new_element_id);
						$event->setParam("parentId", $parentId);
						$event->setMode("after");
						$event->call();
					}
				} else {
					// create virtual copy
					$event = new umiEventPoint("systemVirtualCopyElement");
					$event->setParam("elementId", $element_id);
					$event->setParam("parentId", $parentId);
					$event->addRef("element", $element);
					$event->setMode("before");
					$event->call();

					$new_element_id = umiHierarchy::getInstance()->copyElement($element_id, $parentId, $copyAll);

					$event->setParam("newElementId", $new_element_id);
					$event->setMode("after");
					$event->call();

					$new_element = umiHierarchy::getInstance()->getElement((int) $new_element_id, false, false);

					$event = new umiEventPoint("systemCreateElementAfter");
					$event->addRef("element", $new_element);
					$event->setParam("elementId", $new_element_id);
					$event->setParam("parentId", $parentId);
					$event->setMode("after");
					$event->call();
				}

				if ($new_element_id) {
					if ((bool) getRequest('return_copies')) {
						$this->setDataType("form");
						$this->setActionType("modify");
						$data = $this->prepareData(['element' => $new_element], "page");
						$this->setData($data);
					} else {
						$this->setDataType("list");
						$this->setActionType("view");
						$data = $this->prepareData([$new_element_id], "pages");
						$this->setData($data);
					}

					$connection->query("COMMIT");
					$this->doData();
				} else {
					throw new publicAdminException(getLabel('error-copy-element'));
				}
			} else {
				throw new publicAdminException(getLabel('error-expect-element'));
			}
		}

		/**
		 * Возвращает целевой домен
		 * @return domain
		 * @throws publicAdminException
		 */
		protected function getTargetDomain() {
			$umiDomains = domainsCollection::getInstance();
			$domainId = (int) getRequest('domain-id');
			$targetDomain = $umiDomains->getDomain($domainId);

			if (!$targetDomain instanceof domain) {
				throw new publicAdminException('Wrong domain id given');
			}

			return $targetDomain;
		}

		/**
		 * Возвращает целевой язык
		 * @return lang
		 * @throws publicAdminException
		 */
		protected function getTargetLanguageId() {
			$umiLanguages = langsCollection::getInstance();
			$languageId = (int) getRequest('lang-id');
			$targetLanguage = $umiLanguages->getLang($languageId);

			if (!$targetLanguage instanceof lang) {
				throw new publicAdminException('Wrong language id given');
			}

			return $targetLanguage;
		}

		/**
		 * Устанавливает пустой ответ для метода copy_to_lang_old()
		 * @throws coreException
		 */
		protected function setCopyToLangEmptyResponse() {
			$this->setDataType("list");
			$this->setActionType("view");
			$data = $this->prepareData([], "pages");
			$this->setData($data);
			$this->doData();
		}

		/**
		 * Устанавливает ответ с ошибкой о нехватке шаблона, для метода copy_to_lang_old()
		 * @param string $languageSuffix суффикс целевого языка
		 * @throws coreException
		 */
		protected function setCopyToLangNoTemplatesResponse($languageSuffix) {
			$this->setDataType("list");
			$this->setActionType("view");
			$data = $this->prepareData([], "pages");
			$data['error'] = [];
			$data['error']['type'] = "__template_not_exists__";
			$data['error']['text'] = sprintf(getLabel('error-no-template-in-domain'), $languageSuffix);
			$this->setData($data);
			$this->doData();
		}

		/**
		 * Устанавливает ответ с ошибкой о совпадении адресов копируемых страниц, для метода copy_to_lang_old()
		 * @param domain $targetDomain целевой домен
		 * @param string $languageSuffix суффикс целевого языка
		 * @param array (id => array(существующий адрес, пример корректного адреса)) $pagesWithExistingAltNames страницы,
		 * у которых совпадет адрес после копирования
		 * @throws coreException
		 */
		protected function setCopyToLangPreventAltNamesDoublesResponse(
			domain $targetDomain, $languageSuffix, array $pagesWithExistingAltNames
		) {
			$this->setDataType("list");
			$this->setActionType("view");
			$data = [
				'error' => []
			];

			$data['error']['nodes:item'] = [];
			$data['error']['type'] = '__alias__';

			$path = getSelectedServerProtocol($targetDomain) . "://" . $targetDomain->getHost() . "/";
			$path .= $languageSuffix;

			foreach ($pagesWithExistingAltNames as $pageId => $altNames) {
				$data['error']['nodes:item'][] = [
					'attribute:id' => $pageId,
					'attribute:path' => $path,
					'attribute:alias' => array_shift($altNames),
					'attribute:alt_name_normal' => array_shift($altNames)
				];
			}

			$this->setData($data);
			$this->doData();
		}

		/**
		 * Возвращает список адресов страниц, которые требуется переименовать при копировании,
		 * для метода copy_to_lang_old()
		 * @return array(id => altName)
		 */
		protected function getCopyToLangPagesForRename() {
			$pagesToRename = (array) getRequest('alias');

			foreach ($pagesToRename as $pageId => $pageAltName) {
				$pagesToRename[$pageId] = umiHierarchy::convertAltName($pageAltName);
			}

			return $pagesToRename;
		}

		/**
		 * Возвращает страницы, у которых совпадет адрес после копирования, для метода copy_to_lang_old()
		 * @param array(# => id) $pages проверяемый страницы
		 * @param array(id => 1) $pagesToReplace страницы, которыми будут заменены страницы с совпадающими адресами
		 * @param array(id => новый адрес) $pagesToRename страницы, адреса которых будут изменены после копирования
		 * @param int $domainId идентификатор целевого домена
		 * @param int $languageId идентификатор целевого языка
		 * @return array(id => array(существующий адрес, пример корректного адреса))
		 * @throws expectElementException
		 */
		protected function getCopyToLangPagesFromTargetDomainAndLanguageWithSameAltNames(
			array $pages, array $pagesToReplace, array $pagesToRename, $domainId, $languageId
		) {
			$umiHierarchy = umiHierarchy::getInstance();
			$pagesWithExistingAltNames = [];

			foreach ($pages as $pagesId) {

				if (isset($pagesToReplace[$pagesId])) {
					continue;
				}

				$page = $this->expectElement($pagesId, false, true);

				if (!$page instanceof umiHierarchyElement) {
					continue;
				}

				$pageAltName = (isset($pagesToRename[$pagesId])) ? $pagesToRename[$pagesId] : $page->getAltName();

				$errorCount = 0;
				$pageWithSameAltNameId = $umiHierarchy->getIdByPath(
					$pageAltName, false, $errorCount, $domainId, $languageId
				);

				$pageWithSameAltName = $this->expectElement($pageWithSameAltNameId, false, true);

				if (!$pageWithSameAltName instanceof umiHierarchyElement) {
					continue;
				}

				if ($pageWithSameAltName->getAltName() != $pageAltName) {
					continue;
				}

				$normalizedAltName = $umiHierarchy->getRightAltName(
					$pageAltName, $pageWithSameAltName, false, true
				);

				$pagesWithExistingAltNames[$pagesId] = [
					$pageAltName,
					$normalizedAltName
				];
			}

			return $pagesWithExistingAltNames;
		}

		/**
		 * Копирует страницу в другую языковую версию и/или другой домен
		 * @return void
		 * @throws coreException
		 * @throws expectElementException
		 * @throws publicAdminException
		 */
		public function copy_to_lang_old() {
			$targetPages = (array) getRequest('element');

			if (count($targetPages) == 0) {
				$this->setCopyToLangEmptyResponse();
				return;
			}

			$targetDomain = $this->getTargetDomain();
			$newDomainId = $targetDomain->getId();
			$targetLanguage = $this->getTargetLanguageId();
			$newLanguageId = $targetLanguage->getId();
			$languageSuffix = (!$targetLanguage->getIsDefault()) ? $targetLanguage->getPrefix() . '/' : '';
			$umiTemplates = templatesCollection::getInstance();

			$templatesFromTargetDomainAndLanguage = $umiTemplates->getTemplatesList($newDomainId, $newLanguageId);

			if (count($templatesFromTargetDomainAndLanguage) == 0) {
				$this->setCopyToLangNoTemplatesResponse($languageSuffix);
				return;
			}

			$pagesToRename = $this->getCopyToLangPagesForRename();
			$umiHierarchy = umiHierarchy::getInstance();
			$pagesToReplaces = (array) getRequest('move');

			$pagesWithExistingAltNames = $this->getCopyToLangPagesFromTargetDomainAndLanguageWithSameAltNames(
				$targetPages, $pagesToReplaces, $pagesToRename, $newDomainId, $newLanguageId
			);

			if (count($pagesWithExistingAltNames) > 0) {
				$this->setCopyToLangPreventAltNamesDoublesResponse(
					$targetDomain, $languageSuffix, $pagesWithExistingAltNames
				);
				return;
			}

			$defaultTemplateFromTargetDomainAndLanguage = $umiTemplates->getDefaultTemplate(
				$newDomainId, $newLanguageId
			);

			if (!$defaultTemplateFromTargetDomainAndLanguage instanceof template) {
				throw new publicAdminException('Cannot get default template');
			}

			$newPages = [];

			foreach ($targetPages as $targetPageId) {
				$targetPage = $this->expectElement($targetPageId, false, true);

				if (!$targetPage instanceof umiHierarchyElement) {
					continue;
				}

				$targetPageTemplate = $umiTemplates->getTemplate($targetPage->getTplId());
				$newPageTemplateId = $defaultTemplateFromTargetDomainAndLanguage->getId();

				foreach ($templatesFromTargetDomainAndLanguage as $templateFromTargetDomainAndLanguage) {
					if ($templateFromTargetDomainAndLanguage->getFilename() == $targetPageTemplate->getFilename()) {
						$newPageTemplateId = $templateFromTargetDomainAndLanguage->getId();
					}
				}

				$newPageId = $umiHierarchy->cloneElement($targetPageId, 0, true);
				$newPage = $this->expectElement($newPageId, false, true);

				if (!$newPage instanceof umiHierarchyElement) {
					continue;
				}

				$newPage->setLangId($newLanguageId);
				$newPage->setDomainId($newDomainId);

				$targetPageAlt = $targetPage->getAltName();
				$newPageAltName = $newPage->getAltName();
				$altsAreDifferent = $targetPageAlt !== $newPageAltName;
				$altsHaveSameBase = strpos($newPageAltName, $targetPageAlt) === 0;
				$newAltAutoChanged = (bool) preg_match('/^.*\d{1,}$/', $newPageAltName);

				if ($altsAreDifferent && $altsHaveSameBase && $newAltAutoChanged) {
					$newPageAltName = $targetPageAlt;
				}

				$newPageAltName = (isset($pagesToRename[$newPageId])) ? $pagesToRename[$newPageId] : $newPageAltName;

				if (isset($pagesToReplaces[$targetPageId])) {
					$errorCount = 0;

					$pageWithSameAltNameId = $umiHierarchy->getIdByPath(
						$newPageAltName, false, $errorCount, $newDomainId, $newLanguageId
					);

					$pageWithSameAltName = $this->expectElement($pageWithSameAltNameId, false, true);

					if ($pageWithSameAltName instanceof umiHierarchyElement) {
						$umiHierarchy->delElement($pageWithSameAltName->getId());
					}
				}

				$newPage->setAltName($newPageAltName);
				$newPage->setTplId($newPageTemplateId);
				$newPage->commit();

				$children = $umiHierarchy->getChildrenTree($newPageId);
				$this->module->changeChildsLang($children, $newLanguageId, $newDomainId);
				$newPages[] = $newPage;
			}

			$this->setDataType("list");
			$this->setActionType("view");
			$data = $this->prepareData($newPages, "pages");
			$this->setData($data);
			$this->doData();
		}

		/**
		 * Копирует страницы в другю языковую версию
		 * @throws coreException
		 * @throws expectElementException
		 */
		public function copy_to_lang() {
			$langId = (int) getRequest('lang-id');
			$elements = getRequest('element');
			if (!is_array($elements)) {
				$elements = [$elements];
			}

			if (!is_null($langId)) {
				$hierarchy = umiHierarchy::getInstance();

				foreach ($elements as $elementId) {
					$element = $this->expectElement($elementId, false, true);
					if ($element->getLangId() != $langId || true) {
						$copyElementId = $hierarchy->cloneElement($element->getId(), 0, true);
						$copyElement = $hierarchy->getElement($copyElementId);
						if ($copyElement instanceof umiHierarchyElement) {
							$copyElement->setLangId($langId);
							$copyElement->commit();

							$childs = $hierarchy->getChildrenTree($copyElementId);
							$this->module->changeChildsLang($childs, $langId);
						}
					}
				}
			}

			$this->setDataType("list");
			$this->setActionType("view");
			$data = $this->prepareData([], "pages");
			$this->setData($data);
			$this->doData();
		}

		/**
		 * Алиас для copy_to_lang()
		 */
		public function move_to_lang() {
			$_REQUEST['mode'] = 'move';
			$this->copy_to_lang();
		}

		/**
		 * Устанавливает шаблону флаг "основной"
		 * @param int $templateId ид шаблона, который требуется изменить
		 * @param bool|int $domainId ид домена, к которому относится шаблон. Если не передать - возьмет текущий.
		 * @param bool|int $languageId ид языка, к которому относится шаблон. Если не передать - возьмет текущий.
		 * @return true
		 * @throws publicAdminException если $templateId не является числом
		 * @throws publicAdminException если не удалось получить шаблон по id
		 * @throws publicAdminException если не удалось получить текущий домен
		 * @throws publicAdminException если не удалось получить домен по id
		 * @throws publicAdminException если не удалось получить текущий язык
		 * @throws publicAdminException если не удалось получить язык по id
		 * @throws publicAdminException если не удалось сделать шаблон основным
		 */
		public function setBaseTemplate($templateId = null, $domainId = false, $languageId = false) {
			$templateId = (is_null($templateId)) ? getRequest('param0') : $templateId;

			if (!is_numeric($templateId)) {
				throw new publicAdminException(__METHOD__ . ': wrong template id given: ' . $templateId);
			}

			$templateCollection = templatesCollection::getInstance();
			$template = $templateCollection->getTemplate($templateId);

			if (!$template instanceof template) {
				throw new publicAdminException(__METHOD__ . ': template with id = ' . $templateId . ' was not found');
			}

			$cmsController = cmsController::getInstance();
			$domainId = (is_bool($domainId)) ? getRequest('param1') : $domainId;

			if (!is_numeric($domainId)) {
				$currentDomain = $cmsController->getCurrentDomain();

				if (!$currentDomain instanceof domain) {
					throw new publicAdminException(__METHOD__ . ':  cant get current domain');
				}

				$domainId = $currentDomain->getId();
			}

			$domainsCollection = domainsCollection::getInstance();
			$domain = $domainsCollection->getDomain($domainId);

			if (!$domain instanceof domain) {
				throw new publicAdminException(__METHOD__ . ':  cant get domain by id: ' . $domainId);
			}

			$languageId = (is_bool($languageId)) ? getRequest('param2') : $languageId;

			if (!is_numeric($languageId)) {
				$currentLang = $cmsController->getCurrentLang();

				if (!$currentLang instanceof lang) {
					throw new publicAdminException(__METHOD__ . ':  cant get current language');
				}

				$languageId = $currentLang->getId();
			}

			$languagesCollection = langsCollection::getInstance();
			$language = $languagesCollection->getLang($languageId);

			if (!$language instanceof lang) {
				throw new publicAdminException(__METHOD__ . ':  cant get language by id: ' . $languageId);
			}

			$baseTemplateChanged = $templateCollection->setDefaultTemplate($templateId, $domainId, $languageId);

			if (!$baseTemplateChanged) {
				throw new publicAdminException(__METHOD__ . ':  cant change base template');
			}

			return true;
		}
	}