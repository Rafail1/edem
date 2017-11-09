<?
class CCustomTypeElementDate {

//описываем поведение пользовательского свойства
	function GetUserTypeDescription() {
		return array(
			'PROPERTY_TYPE'			=> 'E',
			'USER_TYPE'				=> 'skill',
			'DESCRIPTION'			=> 'Состав букета',
			'GetPropertyFieldHtml'	=> array('CCustomTypeElementDate', 'GetPropertyFieldHtml'),
			'ConvertToDB'			=> array('CCustomTypeElementDate', 'ConvertToDB'),
			'ConvertFromDB'			=> array('CCustomTypeElementDate', 'ConvertToDB')
		);
	}

	function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName) {
        return (CIBlockPropertyXmlID::GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)) .
            "<input name='".$strHTMLControlName["DESCRIPTION"]."' value='".$value['DESCRIPTION']."'/>";
	}

	function ConvertToDB($arProperty, $value){
		return $value;
	}

	function ConvertFromDB($arProperty, $value){
		return $value;
	}
}