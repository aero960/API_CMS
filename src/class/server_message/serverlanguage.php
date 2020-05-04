<?php
namespace language;

use AvaiableLanguages;
use Helper\Helper;
use LanguageEnglish;
use LanguagePolish;
use ServerDefault;


interface Languager{
     public function choosePhrase(string $Phrase);
     public function getError();
}

class Serverlanguage
{
    private static $instance;
    private $language;
    private Languager $languager;
    private Languager $importantServerLanguager;

    public function __construct()
    {

        $this->changeLanguage(AvaiableLanguages::ENG);
        $this->importantServerLanguager = new ServerDefault();

    }

    public static function getInstance(): Serverlanguage
    {
        return static::$instance ?: static::$instance = new static();
    }

    public function GetMessage(string $Phrase){
     return $this->languager->choosePhrase($Phrase);
    }
    public function ImportAndServerMessage(string $Phrase){
        return $this->importantServerLanguager->choosePhrase($Phrase);
    }

    private function ChooseLanguage(Languager $Languager, string $Language ): void
    {
        $this->language = $Language;
        $this->languager = $Languager;
    }

    public function changeLanguage(string $language): void
    {
        //do ogarniecia
        switch($language){
            case AvaiableLanguages::PL :
                $this->ChooseLanguage(new LanguagePolish(),AvaiableLanguages::PL);
                break;
            default:
                $this->ChooseLanguage(new LanguageEnglish(),AvaiableLanguages::ENG);
                break;
        }

    }

}