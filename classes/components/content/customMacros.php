<?php
	/**
	 * Класс пользовательских макросов
	 */
	class ContentCustomMacros {
		/**
		 * @var content $module
		 */
		public $module;
                
            public function beforeAfter($imageOnePath = false, $imageTwoPath = false){
                
                $htmlCode = "<div class='ba-slider'>";
                $htmlCode .= "<img src='{$imageOnePath}'>";
                $htmlCode .= "<div class='resize'>";
                $htmlCode .= "<img src='{$imageTwoPath}'>";
                $htmlCode .= "</div>";
                $htmlCode .= "<span class='handle'></span>";
                $htmlCode .= "</div>";
                
                return $htmlCode;
            }
	}
?>