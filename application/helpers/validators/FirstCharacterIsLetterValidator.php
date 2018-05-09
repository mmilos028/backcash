<?php
class FirstCharacterLetterValidator extends Zend_Validate_Abstract
{
    const MSG_FIRST_LETTER_IS_CHARACTER = 'msgFirstLetterIsCharacter';

    protected $_messageTemplates = array(
        self::MSG_FIRST_LETTER_IS_CHARACTER => "In '%value%' value first character must start with a letter",
    );

    public function isValid($value)
    {
        $this->_setValue($value);
        //check if value first character is string
        if(!ctype_alpha(substr($value, 0, 1)) && strlen($value) > 0){
            $this->_error(self::MSG_FIRST_LETTER_IS_CHARACTER);
            return false;
        }
        return true;
    }
}