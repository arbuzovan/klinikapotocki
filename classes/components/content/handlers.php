<?php

	/** Класс обработчиков событий */
	class ContentHandlers {

		/** @var content $module */
		public $module;

		/**
		 * Обработчик событий редактирования и перемещения страницы.
		 * Проверяет не изменился ли адрес страницы, если изменился
		 * - добавляет редирект со старого адрес на новый
		 * @param umiEventPoint $e
		 * @return bool
		 */
		public function onModifyPageWatchRedirects(umiEventPoint $e) {
			static $links = [];

			$redirects = UmiCms\Service::Redirects();
			$hierarchy = umiHierarchy::getInstance();

			/** @var umiHierarchyElement|iUmiEntinty $element */
			$element = $e->getRef('element');

			if ($element instanceof umiHierarchyElement == false) {
				return false;
			}

			$elementId = $element->getId();
			$link = $hierarchy->getPathById($elementId, false, false, true);

			if ($e->getMode() == 'before') {
				$links[$elementId] = $link;
				return true;
			}

			if ($links[$elementId] != $link) {
				$redirects->add($links[$elementId], $link, 301);
			}

			return true;
		}

		/**
		 * Проверяет тестовое сообщение
		 * @param iUmiEventPoint $event
		 */
		public function testMessages(iUmiEventPoint $event) {
			$userId = $event->getParam('user_id');
			$umiPermissions = permissionsCollection::getInstance();

			if (!$umiPermissions->isAdmin($userId)) {
				return;
			}

			$umiRegistry = regedit::getInstance();
			$lastTestTime = (int) $umiRegistry->getVal('//settings/last_mess_time');

			if (time() < $lastTestTime + 2592000) {
				return;
			}

			$umiMessages = umiMessages::getInstance();
			$umiMessages->testMessages();
		}

		/**
		 * Обработчик события начала редактирования страницы.
		 * Блокирует редактирования страницы для других пользователей
		 * @param iUmiEventPoint $eEvent события начала редактирования
		 */
		public function systemLockPage(iUmiEventPoint $eEvent) {
			/** @var iUmiHierarchyElement|iUmiEntinty $ePage */
			if ($ePage = $eEvent->getRef("element")) {
				$userId = $eEvent->getParam("user_id");
				$lockTime = $eEvent->getParam("lock_time");
				/** @var iUmiObject|iUmiEntinty $oPage */
				$oPage = &$ePage->getObject();
				$oPage->setValue("locktime", $lockTime);
				$oPage->setValue("lockuser", $userId);
				$oPage->commit();
			}
		}

		/**
		 * Обработчик события сохранения отредактированной страницы
		 * Снимает блокировку редактирования страницы для других пользователей
		 * @param iUmiEventPoint $eEvent событие сохранения
		 */
		public function systemUnlockPage(iUmiEventPoint $eEvent) {
			/** @var iUmiHierarchyElement|iUmiEntinty $ePage */
			if ($ePage = $eEvent->getRef("element")) {
				/** @var iUmiObject|iUmiEntinty $oPage */
				$oPage = $ePage->getObject();
				$oPage->setValue("locktime", null);
				$oPage->setValue("lockuser", null);
				$oPage->commit();
			}
		}

		/**
		 * Обработчик события срабатывания системного крона.
		 * Получает список страниц, которые скоро будут отключены
		 * по истечению времени актуальности контента.
		 * Устанавливает им статус предварительного отключения.
		 * Уведомляет их авторов об этом.
		 * @param iUmiEventPoint $oEvent событие срабатывания крона
		 */
		public function cronSendNotification(iUmiEventPoint $oEvent) {
			$pages = new selector('pages');
			$pages->where('is_active')->equals(1);
			$pages->where('is_deleted')->equals([0, 1]);
			$pages->where('notification_date')->less(time());
			$pages->where('notification_date')->notequals(0);
			$pages->where('expiration_date')->more(time());
			$pages->option('no-permissions')->value(true);

			$umiRegistry = regedit::getInstance();
			$lastCheckDate = (int) $umiRegistry->getVal("//modules/content/last-notification-date");

			/** @var umiHierarchyElement $page */
			foreach ($pages as $page) {
				if ($page instanceof umiHierarchyElement == false) {
					continue;
				}

				$notificationDateObject = $page->getValue('notification_date');
				if (!$notificationDateObject instanceof umiDate) {
					continue;
				}

				$notificationDate = $notificationDateObject->getDateTimeStamp();
				if ($lastCheckDate && ($lastCheckDate - (3600 * 24) < time()) && ($lastCheckDate > $notificationDate)) {
					continue;
				}

				$this->sendMailFor($page, 'cronSendNotification');
			}

			$umiRegistry->setVal("//modules/content/last-notification-date", time());
		}

		/**
		 * Обработчик события срабатывания системного крона.
		 * Снимает с публикации страницы, у которых истекло время актуальности
		 * и уведомляет их авторов об этом
		 * @param iUmiEventPoint $oEvent события срабатывания системного крона
		 */
		public function cronUnpublishPage(iUmiEventPoint $oEvent) {
			$pages = new selector('pages');
			$pages->where('is_active')->equals(1);
			$pages->where('expiration_date')->less(time());
			$pages->where('expiration_date')->notequals(0);
			$pages->option('no-permissions')->value(true);

			/** @var umiHierarchyElement $page */
			foreach ($pages as $page) {
				if (!$page instanceof iUmiHierarchyElement) {
					continue;
				}

				$page->setIsActive(false);
				$this->sendMailFor($page, 'cronUnpublishPage');
			}
		}

		/**
		 * Вспомогательный метод отправки писем по крону.
		 * @see $this->cronUnpublishPage()
		 * @see $this->cronSendNotification()
		 *
		 * @param umiHierarchyElement $page страница
		 * @param string $methodName название вызванного по крону метода
		 */
		protected function sendMailFor($page, $methodName) {
			$umiHierarchy = umiHierarchy::getInstance();
			$umiObjects = umiObjectsCollection::getInstance();
			$umiRegistry = regedit::getInstance();
			$umiDomains = domainsCollection::getInstance();

			$map = [
				'cronUnpublishPage' => [
					'publish-status' => 'page_status_unpublish',
					'notify-header' => getLabel('label-notification-expired-mail-header'),
					'notification' => 'notification-content-unpublish-page',
					'subject-mail-template' => 'content-unpublish-page-subject',
					'content-mail-template' => 'content-unpublish-page-content',
					'content-template' => 'mail/expired',
					'subject' => getLabel('label-notification-expired-mail-header'),
				],

				'cronSendNotification' => [
					'publish-status' => 'page_status_preunpublish',
					'notify-header' => getLabel('label-notification-mail-header'),
					'notification' => 'notification-content-expiration-date',
					'subject-mail-template' => 'content-expiration-date-subject',
					'content-mail-template' => 'content-expiration-date-content',
					'content-template' => 'mail/notify',
					'subject' => getLabel('label-notification-mail-header'),
				],
			];

			$page->setValue("publish_status", $this->module->getPageStatusIdByFieldGUID(
				$map[$methodName]['publish-status']
			));
			$page->commit();

			/** @var iUmiObject $pageObject */
			$pageObject = $page->getObject();
			$userId = $pageObject->getOwnerId();
			$user = $umiObjects->getObject($userId);

			if (!$user instanceof umiObject) {
				return;
			}

			$userEmail = $user->getValue("e-mail");

			if (!umiMail::checkEmail($userEmail)) {
				return;
			}

			if (!$publishComments = $page->getValue("publish_comments")) {
				$publishComments = getLabel('no-publish-comments');
			}

			$domain = $umiDomains->getDomain($page->getDomainId());
			$pageId = $page->getId();
			$pageLink = getSelectedServerProtocol($domain) . "://" . $domain->getHost() . $umiHierarchy->getPathById($pageId);

			$variables = [
				'notify_header' => $map[$methodName]['notify-header'],
				'page_header' => $page->getName(),
				'publish_comments' => $publishComments,
				'page_link' => $pageLink,
			];

			$subject = null;
			$content = null;

			if ($this->module->isUsingUmiNotifications()) {
				$mailNotifications = UmiCms\Service::MailNotifications();
				$notification = $mailNotifications->getCurrentByName($map[$methodName]['notification']);

				if ($notification instanceof MailNotification) {
					$subjectTemplate = $notification->getTemplateByName($map[$methodName]['subject-mail-template']);
					$contentTemplate = $notification->getTemplateByName($map[$methodName]['content-mail-template']);

					if ($subjectTemplate instanceof MailTemplate) {
						$subject = $subjectTemplate->getProcessedContent($variables);
					}

					if ($contentTemplate instanceof MailTemplate) {
						$content = $contentTemplate->getProcessedContent($variables);
					}
				}
			} else {
				try {
					list($contentTemplate) = content::loadTemplatesForMail(
						$map[$methodName]['content-template'], 'body'
					);
					$subject = $map[$methodName]['subject'];
					$content = content::parseTemplateForMail($contentTemplate, $variables, $pageId);
				} catch (Exception $e) {
					// nothing
				}
			}

			if ($subject === null || $content === null) {
				return;
			}

			$mail = new umiMail();
			$mail->setFrom($umiRegistry->getVal("//settings/email_from"));
			$mail->setPriorityLevel("high");
			$mail->addRecipient($userEmail);
			$mail->setSubject($subject);
			$mail->setContent($content);
			$mail->commit();
			$mail->send();
		}

		/**
		 * Обработчик события сохранения изменений страницы.
		 * Запускает переключение активности страницы, в зависимости от актуальности контента
		 * @param iUmiEventPoint $event события сохранения изменений
		 */
		public function pageCheckExpiration(iUmiEventPoint $event) {
			if ($inputData = $event->getRef("inputData")) {
				$page = getArrayKey($inputData, "element");
				$this->module->saveExpiration($page);
			}
		}

		/**
		 * Обработчик события создания страницы.
		 * Запускает переключение активности страницы, в зависимости от актуальности контента
		 * @param iUmiEventPoint $event событие создания страницы
		 */
		public function pageCheckExpirationAdd(iUmiEventPoint $event) {
			if ($page = $event->getRef("element")) {
				$this->module->saveExpiration($page);
			}
		}

		/**
		 * Обработчик события сохранения изменений поля сущности.
		 * Проверяет сущность на предмет содержания спама
		 * @param iUmiEventPoint $event событие сохранения изменений поля сущности
		 */
		public function onModifyPropertyAntiSpam(iUmiEventPoint $event) {
			/** @var iUmiEntinty|iUmiHierarchyElement $entity */
			$entity = $event->getRef("entity");
			if (($entity instanceof iUmiHierarchyElement) && ($event->getParam("property") == "is_spam")) {
				$type = umiHierarchyTypesCollection::getInstance()->getTypeByName("faq", "question");
				$contentField = ($type->getId() == $entity->getTypeId()) ? 'question' : 'content';
				antiSpamHelper::report($entity->getId(), $contentField);
			}
		}

		/**
		 * Обработчик события сохранения изменений страницы.
		 * Проверяет страницу на предмет содержания спама
		 * @param iUmiEventPoint $event событие сохранения изменений страницы
		 */
		public function onModifyElementAntiSpam(iUmiEventPoint $event) {
			static $cache = [];
			/** @var iUmiEntinty|iUmiHierarchyElement $element */
			$element = $event->getRef("element");

			if (!$element) {
				return;
			}

			if ($event->getMode() == "before") {
				$data = getRequest("data");
				if (isset($data[$element->getId()])) {
					$oldValue = getArrayKey($data[$element->getId()], 'is_spam');
					if ($oldValue != $element->getValue("is_spam")) {
						$cache[$element->getId()] = true;
					}
				}
			} else {
				if (isset($cache[$element->getId()])) {
					$type = umiHierarchyTypesCollection::getInstance()->getTypeByName("faq", "question");
					$contentField = ($type->getId() == $element->getTypeId()) ? 'question' : 'content';
					antiSpamHelper::report($element->getId(), $contentField);
				}
			}
		}
	}
