<?php
	/**
	 * Класс функционала eip
	 */
	class EditInPlace {
		/**
		 * @var content $module
		 */
		public $module;

		/**
		 * Валидирует параметры редактирования поля, запускает
		 * процесс сохранения, возвращает данные сохраненного поля
		 * @return array
		 * @throws coreException
		 * @throws publicException
		 */
		public function editValue() {
			UmiCms\System\Protection\Security::getInstance()->checkCsrf();

			$this->module->flushAsXml('editValue');
			$hierarchy = umiHierarchy::getInstance();
			$objects = umiObjectsCollection::getInstance();
			$auth = UmiCms\Service::Auth();

			$mode = getRequest('param0');
			$elementId = getRequest('element-id');
			$objectId = getRequest('object-id');
			$element = null; $object = null;

			if ($elementId) {
				$permissions = permissionsCollection::getInstance();
				list($r, $w) = $permissions->isAllowedObject($auth->getUserId(), $elementId);

				if (!$w) {
					throw new publicException(getLabel('eip-no-permissions'));
				}

				$element = $hierarchy->getElement($elementId);

				if (!$element instanceof iUmiHierarchyElement) {
					throw new publicException(getLabel('eip-no-element') . ": #{$elementId}");
				}

				$object = $element->getObject();
			} elseif($objectId) {
				$pages  = $hierarchy->getObjectInstances($objectId);

				if (!empty($pages)) {
					$permissions = permissionsCollection::getInstance();
					$userId = $auth->getUserId();
					$allow  = false;

					foreach($pages as $elementId) {
						list($r, $w) = $permissions->isAllowedObject($userId, $elementId);
						if ($w) {
							$allow = true;
							break;
						}
					}

					if (!$allow) {
						throw new publicException(getLabel('eip-no-permissions'));
					}
				}

				$object = $objects->getObject($objectId);

				if ($object instanceof iUmiObject == false) {
					throw new publicException(getLabel('eip-no-object') . ": #{$elementId}");
				}

			} else {
				throw new publicException(getLabel('eip-nothing-found'));
			}

			$target = $element ? $element : $object;
			$fieldName = getRequest('field-name');
			$value = getRequest('value');

			$result = array();
			if (is_array($fieldName)) {
				$properties = array();
				for ($i = 0; $i < count($fieldName); $i++) {
					$properties[] = self::saveFieldValue($fieldName[$i], $value[$i], $target, ($mode == 'save'));
				}
				$result['nodes:property'] = $properties;
			} else {
				$property = self::saveFieldValue($fieldName, $value, $target, ($mode == 'save'));
				$result['property'] = $property;
			}

			return $result;
		}

		/**
		 * Возвращает данные поля, при необходимости сохраняет значение поля
		 * @param string $name имя поля
		 * @param mixed $value значение поля
		 * @param iUmiHierarchyElement|iUmiObject $target объект или страницы, чье поле редактируется
		 * @param bool $save сохранять ли значение
		 * @return array
		 * @throws coreException
		 * @throws publicException
		 */
		protected static function saveFieldValue($name, $value, $target, $save = false) {
			/**
			 * @var iUmiEntinty|iUmiHierarchyElement|iUmiObject|umiHierarchyElement|umiObject $target
			 */
			UmiCms\System\Protection\Security::getInstance()->checkCsrf();

			$hierarchy = umiHierarchy::getInstance();
			$optionParams = null;

			if ($i = strpos($name, '[')) {
				if (preg_match_all("/\[([^\[^\]]+)\]/", substr($name, $i), $out)) {
					$optionParams = array(
						'filter' => array(),
						'field-type' => null
					);

					foreach ($out[1] as $param) {
						if (strpos($param, ':')) {
							list($seekType, $seekValue) = explode(':', $param);
							$optionParams['filter'][$seekType] = $seekValue;
						} else {
							$optionParams['field-type'] = $param;
						}
					}
				}
				$name = substr($name, 0, $i);
			}

			$field = null;

			if ($name != 'name' && $name != 'alt_name') {
				$object = ($target instanceof iUmiHierarchyElement) ? $target->getObject() : $target;
				$property = $object->getPropByName($name);
				/* @var iUmiObjectProperty|umiObjectProperty $property */
				if ($property instanceof iUmiObjectProperty == false) {
					throw new publicException(getLabel('eip-no-field') . ": \"{$name}\"");
				}
				$field = $property->getField();
			}

			$type = null;

			if ($name == 'name' || $name == 'alt_name') {
				$type = 'string';
			} elseif ($field instanceof iUmiField) {
				$type = $field->getDataType();
			}

			if (is_string($value)) {
				$value = self::filterStringValue($value);
			}

			$oldLink = null; $newLink = null;

			if ($save) {
				umiObjectProperty::$IGNORE_FILTER_INPUT_STRING = true;
				if ($name == 'h1' || $name == 'name') {
					$value = strip_tags($value);
					$value = str_replace(array('&nbsp;', '&amp;'), array(' ', '&'), $value);

					if ($name === 'name' ) {
						// При изменении name: если name==h1, name=h1=new_value
						// При изменении name: если name!=h1, name=new_value.
						if ( $target->getName() == (string)$target->getValue('h1') ) {
							$target->setValue('h1', $value);
						}
						$target->setName($value);
					} else {
						// При изменении h1: если h1 == name && name=='', name=h1=new_value
						// При изменении h1: если h1 == name и name != '', h1=new_value
						// При изменении h1: если h1 != name, h1=new_value

						if ( $target->getName() == (string)$target->getValue('h1') && ($target->getName() == '') ) {
							$target->setName($value);
						}
						$target->setValue('h1', $value);
					}

					if ($target instanceof iUmiHierarchyElement) {
						$oldLink = $hierarchy->getPathById($target->getId());

						$altName = $target->getAltName();

						if (!$altName || substr($altName, 0, 1) == '_') {
							$target->setAltName($value);
							$target->commit();
						}

						$newLink = $hierarchy->getPathById($target->getId(), false, false, true);
					}
				} elseif ($name == 'alt_name'){
					if ($target instanceof iUmiHierarchyElement) {
						$target->setAltName($value);
						$target->commit();
						$newLink = $hierarchy->getPathById($target->getId(), false, false, true);
					}
				} else {
					if ($type == 'date') {
						$date = new umiDate();
						$date->setDateByString($value);
						$value = $date; unset($date);
						$value = $value->getFormattedDate('U');
					}

					if ($type == 'optioned') {
						$seekType = getArrayKey($optionParams, 'field-type');
						$filter = getArrayKey($optionParams, 'filter');
						$oldValue = $target->getValue($name);
						foreach ($oldValue as $i => $v) {
							foreach ($filter as $t => $s) {
								if (getArrayKey($v, $t) != $s) {
									continue 2;
								}
								$oldValue[$i][$seekType] = $value;
							}
						}
						$value = $oldValue;
						unset($oldValue);
					}

					if ($type == 'wysiwyg') {
						$out = array();
						if (preg_match_all("/href=[\"']?([^ ^\"^']+)[\"']?/i", $value, $out)) {
							foreach ($out[1] as $link) {
								$id = $hierarchy->getIdByPath($link);
								if ($id) {
									$value = preg_replace("/(href=[\"']?)" .  preg_quote($link, '/') . "([\"']?)/i", "\\1%content get_page_url({$id})%\\2", $value);
								}
							}
						}
					} else {
						$value = str_replace(array('&nbsp;', '&amp;'), array(' ', '&'), $value);
					}

					if (in_array($type, array('text', 'string', 'int', 'float', 'price', 'date', 'tags', 'counter'))) {
						$value = preg_replace("/<br ?\/?>/i", "\n", $value);
						$value = strip_tags($value);
					}

					if (in_array($type, array('img_file', 'swf_file', 'file', 'video_file')) && $value) {
						if (substr($value, 0, 1) != '.') {
							$value = '.' . $value;
						}
					}

					$object = ($target instanceof iUmiHierarchyElement) ? $target->getObject() : $target;
					$object->setValue($name, $value);

					if ($object->getIsUpdated() && $object->getId() != $target->getId()) {
						$target->setIsUpdated(true, true);
					}
				}
				$target->commit();
				umiObjectProperty::$IGNORE_FILTER_INPUT_STRING = false;

				if ($target instanceof iUmiHierarchyElement) {
					$backup = backupModel::getInstance();
					$backup->fakeBackup($target->getId());
				}

				$oEventPoint = new umiEventPoint("eipSave");
				$oEventPoint->setMode("after");
				$oEventPoint->setParam("field_name", $name);
				$oEventPoint->setParam("obj", $target);
				def_module::setEventPoint($oEventPoint);
			}

			if ($name == 'name') {
				$value = $target->getName();
			} else {
				$value = $target->getValue($name, $optionParams);
			}

			if ($save) {
				$value = xmlTranslator::executeMacroses($value);
			}

			if ($type == 'date') {
				if ($value) {
					$date = new umiDate();
					$date->setDateByString($value);
					$value = $date->getFormattedDate('Y-m-d H:i');
				} else {
					$value = '';
				}
			}

			if ($type == 'tags' && is_array($value)) {
				$value = implode(', ', $value);
			}

			if ($type == 'optioned' && !is_null($optionParams)) {
				$value = isset($value[0]) ? $value[0] : '';
				$type = getArrayKey($optionParams, 'field-type');
			}

			$result = array(
				'attribute:name'		=> $name,
				'attribute:type'		=> $type
			);

			if ($type == 'relation') {
				$items_arr = array();
				if ($value) {
					if (!is_array($value)) {
						$value = array($value);
					}
					$objects = umiObjectsCollection::getInstance();
					foreach ($value as $objectId) {
						$object = $objects->getObject($objectId);
						$items_arr[] = $object;
					}
				}

				$result['attribute:guide-id'] = $field->getGuideId();

				if ($field->getFieldType()->getIsMultiple()) {
					$result['attribute:multiple'] = 'multiple';
				}

				/**
				 * @var iUmiObjectType $objectType
				 */
				$objectType = selector::get('object-type')->id($field->getGuideId());

				if ($objectType instanceof iUmiObjectType && $objectType->getIsPublic()) {
					$result['attribute:public'] = 'public';
				}

				$result['nodes:item'] = $items_arr;
			} elseif($type == 'symlink') {
				$result['nodes:page'] = is_array($value) ? $value : array();
			} else {
				$result['node:value'] = $value;
			}

			if ($oldLink != $newLink) {
				$result['attribute:old-link'] = $oldLink;
				$result['attribute:new-link'] = $newLink;
			}

			return $result;
		}

		/**
		 * Возвращает идентификатор наиболее часто
		 * встречаемого типа данных среди страниц,
		 * дочерних определенному разделу.
		 * @return int
		 */
		public function getTypeAdding() {
			$parent_id = getRequest('param0');
			$this->module->flushAsXml('getTypeAdding');
			return umiHierarchy::getInstance()->getDominantTypeId($parent_id);
		}

		/**
		 * Возвращает список имен полей типа данных
		 * @return array
		 * @throws coreException
		 */
		public function getTypeFields() {
			$typeId = getRequest('param0');
			$this->module->flushAsXml('getTypeFields');
			$elementFieldNames = array();
			$objectType = umiObjectTypesCollection::getInstance()->getType($typeId);

			if (!$objectType instanceof iUmiObjectType) {
				return $elementFieldNames;
			}

			$elementFields = $objectType->getAllFields();

			/**
			 * @var iUmiField $field
			 */
			foreach ($elementFields as $field) {
				array_push($elementFieldNames, $field->getName());
			}

			return $elementFieldNames;
		}

		/**
		 * Создает страницу в "быстром" режиме
		 * @throws coreException
		 * @throws publicAdminException
		 */
		public function eip_quick_add() {
			UmiCms\System\Protection\Security::getInstance()->checkCsrf();

			$this->module->setDataType("form");
			$this->module->setActionType("create");

			$parentElementId = (int) getRequest('param0');
			$objectTypeId = (int) getRequest('type-id');
			$forceHierarchy = (int) getRequest('force-hierarchy');

			$objectType = selector::get('object-type')->id($objectTypeId);

			if (!$forceHierarchy && $objectType instanceof iUmiObjectType) {
				$objects = umiObjectsCollection::getInstance();
				$objectId = $objects->addObject(null, $objectTypeId);

				$data = array(
					'attribute:object-id' => $objectId,
					'status' => 'ok'
				);
			} else {
				$permissions = permissionsCollection::getInstance();
				if ($parentElementId) {
					$auth = UmiCms\Service::Auth();
					$userId = $auth->getUserId();
					$allow = $permissions->isAllowedObject($userId, $parentElementId);
					if (!$allow[2]) {
						throw new publicAdminException(getLabel("error-require-add-permissions"));
					}
				}

				$hierarchy = umiHierarchy::getInstance();
				$objectTypes = umiObjectTypesCollection::getInstance();

				if (!$objectTypeId) {
					$objectTypeId = $hierarchy->getDominantTypeId($parentElementId);
				}

				if (!$objectTypeId) {
					throw new publicAdminException("No dominant object type found");
				}

				$objectType = $objectTypes->getType($objectTypeId);
				$hierarchyTypeId = $objectType->getHierarchyTypeId();

				$elementId = $hierarchy->addElement($parentElementId, $hierarchyTypeId, '', '', $objectTypeId);
				$permissions->setInheritedPermissions($elementId);
				$element = $hierarchy->getElement($elementId);
				$element->setIsActive(true);
				$element->setIsVisible(true);
				$element->setValue('show_submenu', true);
				$element->commit();

				$event = new umiEventPoint('eipQuickAdd');
				$event->setParam('objectTypeId', $objectTypeId);
				$event->setParam('elementId', $elementId);
				$event->setMode('after');
				$event->call();

				$data = array(
					'attribute:element-id' => $elementId,
					'status' => 'ok'
				);
			}

			cacheFrontend::getInstance()->flush();
			$this->module->setData($data);
			$this->module->doData();
		}

		/**
		 * Создает страницу в поэтапном режиме
		 * @throws coreException
		 * @throws publicException
		 */
		public function eip_add_page() {
			UmiCms\System\Protection\Security::getInstance()->checkCsrf();

			$csrf = getRequest('csrf');
			$mode = (string) getRequest('param0');
			$parent = $this->module->expectElement("param1");
			$module = (string) getRequest('param2');
			$method = (string) getRequest('param3');

			$permissions = permissionsCollection::getInstance();

			/**
			 * @var iUmiHierarchyElement|iUmiEntinty $parent
			 */
			if ($parent) {
				$this->module->checkElementPermissions($parent->getId());
			} else {
				$auth = UmiCms\Service::Auth();
				$permissions->isAllowedModule($auth->getUserId(), $module);
			}

			$hierarchy = umiHierarchy::getInstance();
			$hierarchyTypes = umiHierarchyTypesCollection::getInstance();
			$objectTypes = umiObjectTypesCollection::getInstance();

			if ($mode == 'choose') {
				$types = self::prepareTypesList($module, $parent);

				if (sizeof($types) >= 1) { //Show type choose list
					if ($hierarchyTypeId = getRequest('hierarchy-type-id')) {
						$hierarchyType = $hierarchyTypes->getType($hierarchyTypeId);
						/**
						 * @var iUmiHierarchyType|umiHierarchyType $hierarchyType
						 */
						if ($hierarchyType instanceof iUmiHierarchyType) {
							$module = $hierarchyType->getModule();
							$method = $hierarchyType->getMethod();

							if ($module == 'content' && !$method) {
								$method = 'page';
							}

							$parentId = $parent ? $parent->getId() : '0';

							$url = $this->module->pre_lang . "/admin/content/eip_add_page/form/{$parentId}/{$module}/{$method}/?0&csrf={$csrf}";

							if (isset($_REQUEST['object-type'][$hierarchyTypeId])) {
								$url .= '&type-id=' . $_REQUEST['object-type'][$hierarchyTypeId];
							}

							if ($hierarchyTypeId = getRequest('hierarchy-type-id')) {
								$url .= '&hierarchy-type-id=' . $hierarchyTypeId;
							}
							$this->module->chooseRedirect($url);
						}
					}

					$this->module->setDataType("list");
					$this->module->setActionType("view");

					$data = array(
						'nodes:hierarchy-type' => $types
					);
					$this->module->setData($data, sizeof($types));
					$this->module->doData();
					return;
				}

				if (sizeof($types) == 0) { //Display and error
					/**
					 * @var HTTPOutputBuffer $buffer
					 */
					$buffer = outputBuffer::current();
					$buffer->contentType('text/html');
					$buffer->clear();
					$buffer->push("An error (temp message)");
					$buffer->end();
				}
			}

			$inputData = array(
				'type'		=> $method,
				'parent'	=> $parent,
				'module'	=> $module
			);

			if ($objectTypeId = getRequest('type-id')) {
				$inputData['type-id'] = $objectTypeId;
			} elseif ($hierarchyTypeId = getRequest('hierarchy-type-id')) {
				$inputData['type-id'] = $objectTypes->getTypeIdByHierarchyTypeId($hierarchyTypeId);
			}

			if (getRequest('param4') == "do") {
				$elementId = $this->module->saveAddedElementData($inputData);
				/**
				 * @var iUmiHierarchyElement|IumiEntinty $element
				 */
				$element = $hierarchy->getElement($elementId, true);

				if (!$element instanceof iUmiHierarchyElement) {
					throw new publicException("Can't get create umiHierarchyElement");
				}

				$element->setIsActive();
				$element->commit();

				$permissions->setInheritedPermissions($elementId);
				cacheFrontend::getInstance()->flush();

				$buffer = outputBuffer::current();
				$buffer->contentType('text/html');
				$buffer->clear();
				$buffer->push("<script>window.parent.location.reload();</script>");
				$buffer->end();
			}

			$this->module->setDataType("form");
			$this->module->setActionType("create");

			$data = $this->module->prepareData($inputData, "page");

			$this->module->setData($data);
			$this->module->doData();
		}

		/**
		 * Удаляет страницу и возвращает результат операции
		 * @return array
		 * @throws coreException
		 */
		public function eip_del_page() {
			UmiCms\System\Protection\Security::getInstance()->checkCsrf();

			$this->module->flushAsXml('eip_del_page');

			$config = mainConfiguration::getInstance();
			$permissions = permissionsCollection::getInstance();
			$hierarchy = umiHierarchy::getInstance();
			$objects = umiObjectsCollection::getInstance();
			$auth = UmiCms\Service::Auth();

			$userId = $auth->getUserId();
			$elementId = (int) getRequest('element-id');
			$objectId = (int) getRequest('object-id');

			$fakeDelete = $config->get('system', 'eip.fake-delete');

			if ($objectId) {
				if ($permissions->isSv() || $permissions->isAdmin() || $permissions->isOwnerOfObject($objectId, $auth->getUserId())) {
					$objects->delObject($objectId);
					cacheFrontend::getInstance()->flush();
					return array(
						'status'	=> 'ok'
					);
				} else {
					return array(
						'error' => getLabel('error-require-delete-permissions')
					);
				}
			} else {
				$allow = $permissions->isAllowedObject($userId, $elementId);
				if ($allow[3]) {
					/**
					 * @var iUmiHierarchyElement|IumiEntinty $element
					 */
					$element = $hierarchy->getElement($elementId);
					if ($element instanceof iUmiHierarchyElement) {
						if (!$element->getName() && !trim($element->getAltName(), '_0123456789') || !$fakeDelete) {

							$oEventPoint = new umiEventPoint("systemDeleteElement");
							$oEventPoint->setMode("before");
							$oEventPoint->addRef("element", $element);
							content::setEventPoint($oEventPoint);

							$hierarchy->delElement($elementId);

							// after del event
							$oEventPoint2 = new umiEventPoint("systemDeleteElement");
							$oEventPoint2->setMode("after");
							$oEventPoint2->addRef("element", $element);
							content::setEventPoint($oEventPoint2);

						} else {
							// fake delete
							$oEventPoint = new umiEventPoint("systemSwitchElementActivity");
							$oEventPoint->setMode("before");
							$oEventPoint->addRef("element", $element);
							content::setEventPoint($oEventPoint);

							$element->setIsActive(false);
							$element->commit();

							$oEventPoint2 = new umiEventPoint("systemSwitchElementActivity");
							$oEventPoint2->setMode("after");
							$oEventPoint2->addRef("element", $element);
							content::setEventPoint($oEventPoint2);
						}
						cacheFrontend::getInstance()->flush();
					}
					return array(
						'status'	=> 'ok'
					);
				} else {
					return array(
						'error' => getLabel('error-require-delete-permissions')
					);
				}
			}
		}

		/**
		 * Меняет иерархию страницы и возвращает результат операции
		 * @return array
		 * @throws coreException
		 * @throws publicAdminException
		 */
		public function eip_move_page() {
			UmiCms\System\Protection\Security::getInstance()->checkCsrf();

			$this->module->flushAsXml('eip_move_page');

			$permissions = permissionsCollection::getInstance();
			$hierarchy = umiHierarchy::getInstance();
			$auth = UmiCms\Service::Auth();
			$userId = $auth->getUserId();
			$elementId = (int) getRequest('param0');
			$nextElementId = (int) getRequest('param1');

			$parentElementId = getRequest('parent-id');
			if (is_null($parentElementId)) {
				if ($nextElementId) {
					$parentElementId = $hierarchy->getParent($nextElementId);
				} else {
					$parentElementId = $hierarchy->getParent($elementId);
				}
			}

			$parents = $hierarchy->getAllParents($parentElementId);
			if (in_array($elementId, $parents)) {
				throw new publicAdminException(getLabel('error-illegal-moving'));
			}

			$allow = $permissions->isAllowedObject($userId, $elementId);
			if ($allow[4]) {
				if (is_null(getRequest('check'))) {
					$element = $hierarchy->getElement($elementId);
					$oldParentId = null;

					if ($element instanceof iUmiHierarchyElement) {
						$oldParentId = $element->getRel();
					}

					$event = new umiEventPoint('systemMoveElement');
					$event->setParam('parentElementId', $parentElementId);
					$event->setParam('elementId', $elementId);
					$event->setParam('beforeElementId', $nextElementId);
					$event->setParam("old-parent-id", $oldParentId);
					$event->setMode('before');
					$event->call();

					$hierarchy->moveBefore($elementId, $parentElementId, $nextElementId ? $nextElementId : false);
					cacheFrontend::getInstance()->flush();

					$event2 = new umiEventPoint('systemMoveElement');
					$event2->setParam('parentElementId', $parentElementId);
					$event2->setParam('elementId', $elementId);
					$event2->setParam('beforeElementId', $nextElementId);
					$event2->setParam("old-parent-id", $oldParentId);
					$event2->setMode('after');
					$event2->call();
				}
				return array(
					'status'	=> 'ok'
				);
			} else {
				return array(
					'error' => getLabel('error-require-move-permissions')
				);
			}
		}

		/**
		 * Возвращает данные для построения панели редактирования
		 * @return array
		 * @throws coreException
		 * @throws selectorException
		 */
		public function frontendPanel() {
			$permissions = permissionsCollection::getInstance();
			$maxRecentPages = 5;

			$this->module->flushAsXml('frontendPanel');

			$modules = array();
			$modulesSortedPriorityList = $this->module->getSortedModulesList();

			foreach ($modulesSortedPriorityList as $moduleInfo) {
				$modules[] = array(
					'attribute:label'	=> $moduleInfo['label'],
					'attribute:type'	=> $moduleInfo['type'],
					'node:name'			=> $moduleInfo['name']
				);
			}

			$hierarchy = umiHierarchy::getInstance();
			$key = md5(getServer('HTTP_REFERER'));
			$currentIds = \UmiCms\Service::Session()->get($key);
			$currentIds = is_array($currentIds) ? $currentIds : [];

			foreach ($currentIds as $i => $id) {
				$currentIds[$i] = $id[2];
			}

			$currentIds = array_unique($currentIds);
			$current = array();

			foreach ($currentIds as $id) {
				$current[] = $hierarchy->getElement($id);
			}

			$recent = new selector('pages');
			$recent->where('is_deleted')->equals(0);
			$recent->where('is_active')->equals(1);
			$recent->where('lang')->equals(langsCollection::getInstance()->getList());
			$recent->order('updatetime')->desc();
			$recent->limit(0, $maxRecentPages);

			$auth = UmiCms\Service::Auth();

			if (sizeof($currentIds) && $permissions->isAllowedModule($auth->getUserId(), 'backup')) {
				$changelog = backupModel::getInstance()->getChanges($currentIds[0]);
			} else {
				$changelog = null;
			}

			$referer = getRequest('referer') ? getRequest('referer') : getServer('HTTP_REFERER');

			$tickets = new selector('objects');
			$tickets->types('object-type')->name('content', 'ticket');
			$tickets->where('url')->equals($referer);
			$tickets->limit(0, 100);

			$ticketsColorField = 'tickets_color';

			$ticketsResult = array();
			/**
			 * @var iUmiObject|iUmiEntinty $ticket
			 */
			foreach ($tickets as $ticket) {
				$ticketOwner = selector::get('object')->id($ticket->getValue('user_id'));

				if (!$ticketOwner instanceof iUmiObject)  {
					continue;
				}

				$ticketsResult[] = array(
					'attribute:id' => $ticket->getId(),
					'author' => array(
						'attribute:fname' => $ticketOwner->getValue('fname'),
						'attribute:lname' => $ticketOwner->getValue('lname'),
						'attribute:login' => $ticketOwner->getValue('login'),
						'attribute:ticketsColor' => $ticketOwner->getValue($ticketsColorField)
					),
					'position' => array(
						'attribute:x' => $ticket->getValue('x'),
						'attribute:y' => $ticket->getValue('y'),
						'attribute:width' => $ticket->getValue('width'),
						'attribute:height' => $ticket->getValue('height')
					),
					'message' => $ticket->getValue('message')
				);
			}

			/**
			 * @var iUmiObject|iUmiEntinty $user
			 */
			$user = selector::get('object')->id($auth->getUserId());

			if (!$user instanceof iUmiObject) {
				return array();
			}

			$result = array(
				'user'		=> array(
					'attribute:id' => $user->getId(),
					'attribute:fname' => $user->getValue('fname'),
					'attribute:lname' => $user->getValue('lname'),
					'attribute:login' => $user->getValue('login'),
					'attribute:ticketsColor' => $user->getValue($ticketsColorField)
				),
				'tickets' => array(
					'nodes:ticket' => $ticketsResult
				),
				'modules'	=> array('nodes:module' => $modules),
				'documents'		=> array(
					'editable'		=> array('nodes:page' => $current),
					'recent'		=> array('nodes:page' => $recent->result())
				)
			);

			if (!$permissions->isAllowedMethod($auth->getUserId(), 'tickets', 'manage')) {
				unset($result['tickets']);
			}

			if ($changelog && sizeof($changelog['nodes:revision'])) {
				$result['changelog'] = $changelog;
			}

			$event = new umiEventPoint('eipFrontendPanelGet');
			$event->setParam("id", getArrayKey($currentIds, 0));
			$event->addRef("result", $result);
			$event->setMode('after');
			$event->call();

			return $result;
		}

		/**
		 * Очищает строку от нежелательных символов
		 * @param string $value
		 * @return string
		 */
		public static function filterStringValue($value) {
			$trims = array('&nbsp;', ' ', '\n');
			foreach($trims as $trim) {
				if (substr($value, 0, strlen($trim)) == $trim) {
					$value = substr($value, strlen($trim));
				}

				if (substr($value, strlen($value) - strlen($trim)) == $trim) {
					$value = substr($value, 0, strlen($value) - strlen($trim));
				}
			}
			return $value;
		}

		/**
		 * Возвращает путь до файла, записанного
		 * в заданное поле заданного объекта
		 * @param int $elementId идентификатор объекта
		 * @param string $fieldName гуид поля
		 * @return string
		 */
		public function getImageUrl($elementId, $fieldName) {
			if (empty($elementId) || empty ($fieldName)) {
				return "";
			}

			$oElement = umiHierarchy::getInstance()->getElement($elementId);

			if (!$oElement) {
				return "";
			}

			$oImgFile = $oElement->getValue($fieldName);

			if ($oImgFile instanceof umiFile === false) {
				return "";
			}

			return $oImgFile->getFilePath(true);
		}

		/**
		 * Возвращает путь до директории, куда
		 * редактор изображений сохраняет результат
		 * @param bool $bFullPath нужно сформировать полный путь
		 * @return string
		 */
		public function getIeditorImagesPath($bFullPath = false) {
			$sPath = $bFullPath ? realpath(USER_IMAGES_PATH) : USER_IMAGES_PATH;
			return $sPath . '/cms/data/.ieditor';
		}

		/**
		 * Возвращает разделитель параметров,
		 * применяется в именах файлов
		 * @return string
		 */
		public function getParametersSeparator() {
			return '##';
		}

		/**
		 * Является ли изображение миниатюрой
		 * @param string $sImagePath путь до изображения
		 * @return bool
		 */
		public function isThumb($sImagePath) {
			return strpos($sImagePath, '/cms/autothumbs/') !== false || strpos($sImagePath, '/cms/thumbs/') !== false;
		}

		/**
		 * Было ли изображения создано редактиром изображений
		 * @param string $sImagePath путь до изображения
		 * @return bool
		 */
		public function isIeditorImage($sImagePath) {
			return strpos($sImagePath, $this->getIeditorImagesPath()) !== false;
		}

		/**
		 * Возвращает информацию об изображении
		 * @param string $sImagePath путь до изображения
		 * @return array
		 * @throws publicAdminException
		 */
		public function getImageData($sImagePath = '') {
            $this->module->flushAsXml('getImageData');
			if (empty($sImagePath) && getRequest('image_url')) {
				$sImagePath = getRequest('image_url');
			}

			$sImagePath = preg_replace('/\?[0-9]+$/', '', $sImagePath);

			if ($this->isThumb($sImagePath)) {
				if (getRequest('id') && getRequest('field_name')) {
					$sImagePath = $this->getImageUrl(getRequest('id'), getRequest('field_name'));
				}
			}

			if (empty($sImagePath)) {
				return array('result' => false);
			}

			if (strpos($sImagePath, CURRENT_WORKING_DIR) === false) {
				$sImagePath = CURRENT_WORKING_DIR . $sImagePath;
			}

			$arOriginalImages = $this->findOriginalImages($sImagePath);
			if (!empty($arOriginalImages)) {
				$sImagePath = $arOriginalImages[0];
			}

			if (!file_exists($sImagePath)) {
				throw new publicAdminException(getLabel('ieditor-invalid-filename', 'content'));
			}
			$arResult = array(
				'path' => '',	// path to original image
				'width' => 0,	// [optional] width of selection
				'height' => 0,	// [optional] height of selection
				'left' => 0,	// [optional] left offset of selection in crop operation
				'top' => 0,	// [optional] top offset of selection in crop operation
				'naturalWidth' => 0, // Natural width of image
				'naturalHeight' => 0 // Natural height of image
			);

			$arFileInfo = pathinfo($sImagePath);
			$sImagePath = str_replace(CURRENT_WORKING_DIR, '', $sImagePath);
			if ($this->isIeditorImage($sImagePath)) {
				$sFileName = $arFileInfo['filename'];
				$sOriginalImageData = base64_decode($sFileName);
				$arOriginalImageData = explode($this->getParametersSeparator(), $sOriginalImageData);
				$arResult['width'] = isset($arOriginalImageData[1]) ? $arOriginalImageData[1] : 0;
				$arResult['height'] = isset($arOriginalImageData[2]) ? $arOriginalImageData[2] : 0;
				$arResult['left'] = isset($arOriginalImageData[3]) ? $arOriginalImageData[3] : 0;
				$arResult['top'] = isset($arOriginalImageData[4]) ? $arOriginalImageData[4] : 0;
			}
			$arResult['path'] = $sImagePath;
			$oImage = new umiImageFile(CURRENT_WORKING_DIR . $sImagePath);
			$arResult['naturalWidth'] = $oImage->getWidth();
			$arResult['naturalHeight'] = $oImage->getHeight();

			return $arResult;
		}

		/**
		 * Генерирует путь до исходного изображения до его обрезки
		 * @param array $arPathInfo информация об изображении
		 * @param array $arAdditionalInfo дополнительные настройки
		 * @return string
		 */
		public function generateOriginalImagePath($arPathInfo, $arAdditionalInfo = array()) {
			$sIeditorImagesFolder = $this->getIeditorImagesPath();
			if (!is_dir($sIeditorImagesFolder)) {
				mkdir($sIeditorImagesFolder);
			}
			$sSeparator = $this->getParametersSeparator();
			return $sIeditorImagesFolder . '/' . base64_encode(str_replace(CURRENT_WORKING_DIR, '', $arPathInfo['dirname']) . '/' . $arPathInfo['basename'] . $sSeparator . join($sSeparator, $arAdditionalInfo)) . '.' . $arPathInfo['extension'];
		}

		/**
		 * Возвращает список путей изображений, которые
		 * были исходными версиями заданного изображения
		 * @param string $sImagePath путь до изображения
		 * @return array
		 */
		public function findOriginalImages($sImagePath) {
			$sImagePath = preg_replace('/\?[0-9]+$/', '', $sImagePath);
			$arPathInfo = pathinfo($sImagePath);
			clearstatcache();
			$sSearchString = base64_encode(str_replace(CURRENT_WORKING_DIR, '', $arPathInfo['dirname']) . '/' . $arPathInfo['basename'] . $this->getParametersSeparator());
			$sSearchString = preg_replace('/[=]+$/', '', $sSearchString);
			$sSearchString = substr($sSearchString, 0, -1);
			$sSearchString = $this->getIeditorImagesPath() . '/' . $sSearchString . "*." . $arPathInfo['extension'];
			$result = glob($sSearchString);
			if (!is_array($result)) {
				return array();
			}
			return $result;
		}

		/**
		 * Удаляет все исходные версии заданного изображения
		 * @param string $sImagePath путь до изображения
		 */
		public function deleteOriginalImages($sImagePath) {
			$arOriginalImages = $this->findOriginalImages($sImagePath);
			foreach ($arOriginalImages as $sFilePath) {
				unlink($sFilePath);
			}
		}

		/**
		 * Удаляет все миниатюры изображения
		 * @param string $sImagePath путь до изображения
		 */
		public function deleteThumbnail($sImagePath) {
			@unlink(USER_IMAGES_PATH . '/cms/data/.tmb/' . md5($sImagePath) . '.png');
		}


		/**
		 * Редактирует изображение, выступает маршрутизатором
		 * для операций над изображениями.
		 * Возвращает результат операции.
		 * @param string|bool $sAction операция над изображением (rotate|upload|crop|resize)
		 * @return string
		 * @throws publicAdminException
		 */
		public function ieditor($sAction = false) {
			if (!$sAction) {
				$sAction = getRequest('action');
			}

			$sImagePath = CURRENT_WORKING_DIR . getRequest('image_url');
			$sImagePath = preg_replace('/\?[0-9]+$/', '', $sImagePath);

			if ($this->isThumb($sImagePath)) {
				$sImagePath = CURRENT_WORKING_DIR . $this->getImageUrl(getRequest('element_id'), getRequest('field_name'));
			}

			if (!file_exists($sImagePath)) {
				return "";
			}

			if (str_replace(CURRENT_WORKING_DIR, '', $sImagePath) == getRequest('empty_url') && $sAction != 'upload') {
				throw new publicAdminException(getLabel("ieditor-uneditable-image", 'content'));
			}

			$this->deleteThumbnail($sImagePath);

			switch ($sAction) {
				case 'rotate':
					return $this->ieditor_rotate($sImagePath);
				case 'upload':
					return $this->ieditor_upload();
				case 'crop':
					return $this->ieditor_crop($sImagePath);
				case 'resize':
					return $this->ieditor_resize($sImagePath);
			}

			return "";
		}

		/**
		 * Изменяет размер изображения
		 * @param string $sImagePath путь до изображения
		 * @return mixed|string
		 */
		public function ieditor_resize($sImagePath){

			$iWidth = intval(getRequest('width'));
			$iHeight = intval(getRequest('height'));

			$processor = imageUtils::getImageProcessor();

			if (!$processor->resize($sImagePath,$iWidth,$iHeight)){
				return '';
			}

			$this->deleteOriginalImages($sImagePath);
			return str_replace(CURRENT_WORKING_DIR, '', $sImagePath);
		}

		/**
		 * Обрезает изображение
		 * @param string $sImagePath путь до изображения
		 * @return mixed|string
		 */
		public function ieditor_crop($sImagePath){
			clearstatcache();
			$iSelectionLeft = intval(getRequest('x1')) ? intval(getRequest('x1')) : 0;
			$iSelectionTop = intval(getRequest('y1')) ? intval(getRequest('y1')) : 0;
			$iSelectionWidth = intval(getRequest('width'));
			$iSelectionHeight = intval(getRequest('height'));
			$iScale = floatval(getRequest('scale')) ? floatval(getRequest('scale')) : 1;

			if ($iScale < 1) {
				$iSelectionLeft = round($iSelectionLeft / $iScale);
				$iSelectionTop = round($iSelectionTop / $iScale);
				$iSelectionWidth = round($iSelectionWidth / $iScale);
				$iSelectionHeight = round($iSelectionHeight / $iScale);
			}

			$sNewOriginalImagePath = $this->generateOriginalImagePath(pathinfo($sImagePath), array($iSelectionWidth, $iSelectionHeight, $iSelectionLeft, $iSelectionTop));

			$arOriginalImages = $this->findOriginalImages($sImagePath);

			if (empty($arOriginalImages)) {
				$bCopyResult = @copy($sImagePath, $sNewOriginalImagePath);
				if (!$bCopyResult || !file_exists($sNewOriginalImagePath)) {
					return "";
				}
			} else {
				$bRenameResult = @rename($arOriginalImages[0], $sNewOriginalImagePath);
				if (!$bRenameResult || !file_exists($sNewOriginalImagePath)) {
					return "";
				}
			}

			$sTmpImagePath = $this->getIeditorImagesPath(true) . '/__ieditor_tmp';
			if (file_exists($sTmpImagePath)) {
				@unlink($sTmpImagePath);
			}

			@copy($sNewOriginalImagePath, $sTmpImagePath);
			if (!file_exists($sTmpImagePath)) {
				return "";
			}

			$processor = imageUtils::getImageProcessor();

			if (!$processor->crop($sNewOriginalImagePath,$iSelectionTop,$iSelectionLeft,$iSelectionWidth,$iSelectionHeight)){
				return "";
			}

			@unlink($sImagePath);
			@copy($sNewOriginalImagePath, $sImagePath);
			@unlink($sNewOriginalImagePath);
			@rename($sTmpImagePath, $sNewOriginalImagePath);

			return str_replace(CURRENT_WORKING_DIR, '', $sImagePath);
		}

		/**
		 * Поворачивает изображение
		 * @param string $sImagePath путь до изображения
		 * @return mixed|string
		 */
		public function ieditor_rotate ($sImagePath) {
			$processor = imageUtils::getImageProcessor();

			if (!$processor->rotate($sImagePath)){
				return "";
			}

			$this->deleteOriginalImages($sImagePath);
			return str_replace(CURRENT_WORKING_DIR, '', $sImagePath);
		}

		/**
		 * Загружает изображение на сервер
		 */
		public function ieditor_upload() {
			/** @var HTTPOutputBuffer $buffer */
			$buffer = outputBuffer::current('HTTPOutputBuffer');
			$buffer->clear();

			if (!empty($_FILES)) {
				/**
				 * @var umiFile $oUploadedFile
				 */
				$uploadedFile = umiImageFile::upload('eip-ieditor-upload-fileinput', 0, USER_IMAGES_PATH . '/cms/data');

				if ($uploadedFile instanceof umiImageFile) {
					$buffer->push($uploadedFile->getFilePath(true));
				}

			}

			$buffer->end();
		}

		/**
		 * Возвращает имен иерархических типов данных,
		 * страницы которых можно добавлять через eip
		 * @return array
		 */
		protected static function loadEiPTypes() {
			static $types;
			if (is_array($types)) {
				return $types;
			}

			$config = mainConfiguration::getInstance();
			$types = array();
			$rules = $config->get('edit-in-place', 'allowed-types');

			foreach ($rules as $rule) {
				list($type, $parents) = preg_split("/ ?<\- ?/", $rule);
				list($module, $method) = explode("::", $type);
				$types[$module][$method] = $parents;
			}

			return $types;
		}

		/**
		 * Возвращает список иерархических типов данных,
		 * страницы|объеты которых можно добавлять в рамках заданного модуля,
		 * либо в рамках заданной родительской страницы
		 * @param string $targetModule имя модуля
		 * @param null|iUmiHierarchyElement $parent родительская страницы
		 * @return array
		 * @throws coreException
		 */
		protected static function prepareTypesList($targetModule, $parent = null) {
			$types = self::loadEiPTypes();
			$hierarchyTypes = umiHierarchyTypesCollection::getInstance();
			$cmsController = cmsController::getInstance();
			$modulesList = $cmsController->getModulesList();

			if ($parent instanceof iUmiHierarchyElement) {
				$targetModule = $parent->getModule();
			}

			$matched = array();
			foreach ($types as $module => $stypes) {
				if ($parent && ($module != $targetModule && $targetModule != 'content')) {
					continue;
				}

				asort($stypes, true);

				foreach ($stypes as $method => $rule) {
					if ($rule != '*' && $rule != '@') {
						if (!$parent) {
							continue;
						}

						$arr = explode('::', $rule);
						if (sizeof($arr) != 2) {
							continue;
						}
						list($seekModule, $seekMethod) = $arr;
						if ($parent->getModule() != $seekModule || $parent->getMethod() != $seekMethod) {
							continue;
						}
					}

					if ($rule == '@' && $parent) {
						continue;
					}

					$hierarchyType = $hierarchyTypes->getTypeByName($module, $method);

					if ($hierarchyType instanceof iUmiHierarchyType) {
						//Compare with installed modules list
						if (!in_array($module, $modulesList)) {
							continue;
						}
						$matched[] = $hierarchyType;
					}
				}
			}

			$event = new umiEventPoint("eipPrepareTypesList");
			$event->setParam("targetModule", $targetModule);
			$event->setParam("parent", $parent);
			$event->addRef("types", $matched);
			$event->setMode("after");
			$event->call();

			return $matched;
		}
	}
