<?php

class SimpleFormComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        CModule::IncludeModule('iblock');
		$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();


        $post = array_merge($_POST, $_FILES);
        if (!empty($_REQUEST['ID'])) {
            $res = CIBlockElement::GetByID($_REQUEST['ID']);
            if ($ar_res = $res->GetNext())
                $this->arResult['ITEM'] = $ar_res;
        }

		//if (!empty($post) && RequestHelper::isAjax()) {
		if (!empty($post) && $request->isAjaxRequest()) {

            foreach ($this->arParams['REQUIRED'] as $key => $field) {
                if (empty($post[$key]))
                    $this->arResult['ERRORS'][$key] = 'Необходимо заполнить поле ' . $field;
            }
            $PROP = array();
            //загрузка файлов
            if (!empty($this->arParams['PROPERTY_FILES'])) {
                foreach ($this->arParams['PROPERTY_FILES'] as $file) {
                    if (!empty($_FILES[$file])) {
                        if(is_array($_FILES[$file][name])) {

                            foreach($_FILES[$file][name] as $key => $name) {
                                $arr_file = array(
                                    "name" => $name,
                                    "size" => $_FILES[$file][size][$key],
                                    "tmp_name" => $_FILES[$file][tmp_name][$key],
                                    "type" => "",
                                    "old_file" => "",
                                    "del" => "Y",
                                    "MODULE_ID" => "iblock"
                                );
                                $fid = CFile::SaveFile($arr_file, "iblock");
                                if ($fid > 0) {
                                    $PROP[$file][] = $fid;
                                    $PROP[$file.'_LINK'] = '<a href="http://'.$_SERVER['NAME'].CFile::GetPath($fid).'">Открыть файл</a>';
                                }
                            }

                        } else {
                            $arr_file = array(
                                "name" => $_FILES[$file][name],
                                "size" => $_FILES[$file][size],
                                "tmp_name" => $_FILES[$file][tmp_name],
                                "type" => "",
                                "old_file" => "",
                                "del" => "Y",
                                "MODULE_ID" => "iblock");
                            $fid = CFile::SaveFile($arr_file, "iblock");
                            if ($fid > 0) {
                                $PROP[$file] = $fid;
                            }
                        }
                    }
                }
            }

            if ($this->arParams['USE_CAPTCHA'] == 'Y') {
                global $APPLICATION;
                if (!$APPLICATION->CaptchaCheckCode($_POST["captcha_word"], $_POST["captcha_code"])) {
                    $this->arResult['ERRORS']['captcha'] = 'Неверно указан код защиты от автоматических сообщений';
                }
                if (empty($_POST['captcha_word'])) {
                    $this->arResult['ERRORS']['captcha'] = 'Не указан код защиты от автоматических сообщений';
                }
            }


            if (empty($this->arResult['ERRORS'])) {
                $el = new CIBlockElement;
                if (!empty($this->arParams['PROPERTY'])) {
                    foreach ($this->arParams['PROPERTY'] as $prop) {
                        $PROP[$prop] = $post[$prop];
                    }
                }
                $saveArr = Array(
                    "MODIFIED_BY" => 1, // элемент изменен текущим пользователем
                    "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
                    "IBLOCK_ID" => $this->arParams['IBLOCK_ID'],
                    "PROPERTY_VALUES" => $PROP,
                    "DATE_ACTIVE_FROM" => date('d.m.Y H:i:s'),
                    "PREVIEW_TEXT" => $post[$this->arParams['MESSAGE_FIELD']],
                    "NAME" => $post[$this->arParams['NAME_FIELD']] ? $post[$this->arParams['NAME_FIELD']] : date('d.m.Y H:i'),
                    "ACTIVE" => "N",
                );


                $el->Add($saveArr);

                if ($this->arParams['EMAIL_EVENT']) {
                    $params = array_merge($_POST, $PROP);
                    $d = CEvent::sendimmediate($this->arParams['EMAIL_EVENT'], SITE_ID, $params);
                }

                $this->arResult['SUCCESS'] = 'Y';
            }
        }


        if ($this->arParams['USE_CAPTCHA'] == 'Y') {
            include_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/captcha.php");
            $cpt = new CCaptcha();
            $captchaPass = COption::GetOptionString("main", "captcha_password", "");
            if (strlen($captchaPass) <= 0) {
                $captchaPass = randString(10);
                COption::SetOptionString("main", "captcha_password", $captchaPass);
            }
            $cpt->SetCodeCrypt($captchaPass);
            $this->arResult['captcha_code'] = $cpt->GetCodeCrypt();
        }

        if ($_REQUEST['IS_AJAX'] == 'Y') {
//            $GLOBALS['APPLICATION']->RestartBuffer();
        }

//        return;

        $this->includeComponentTemplate();
    }

}